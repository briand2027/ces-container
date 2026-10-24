<?php

declare(strict_types=1);

if (($argc !== 2 && $argc !== 3) || ! is_file($argv[1])) {
    fwrite(STDERR, "Usage: php parse_container_descriptions.php <dump.sql> [output.json]\n");
    exit(2);
}

$sql = file_get_contents($argv[1]);
$insertStart = strpos($sql, 'INSERT INTO `conteneurs`');
$insertEnd = strpos($sql, 'CREATE TABLE `conteneur_services`', $insertStart ?: 0);

if ($insertStart === false || $insertEnd === false) {
    throw new RuntimeException('Could not locate the conteneurs INSERT section.');
}

$section = substr($sql, $insertStart, $insertEnd - $insertStart);
preg_match('/INSERT INTO `conteneurs` \((.*?)\) VALUES\s*/s', $section, $header);
$columns = array_map(
    static fn (string $column): string => trim($column, " `\t\r\n"),
    explode(',', $header[1] ?? '')
);

if (! in_array('description', $columns, true) || ! in_array('description_ch', $columns, true)) {
    throw new RuntimeException('The expected description columns are not in the dump.');
}

$statements = [];
$statementStart = 0;
$quoted = false;
$escaped = false;
for ($i = 0, $length = strlen($section); $i < $length; $i++) {
    $char = $section[$i];
    if ($quoted) {
        if ($escaped) {
            $escaped = false;
        } elseif ($char === "\\") {
            $escaped = true;
        } elseif ($char === "'") {
            $quoted = false;
        }
    } elseif ($char === "'") {
        $quoted = true;
    } elseif ($char === ';') {
        $statement = substr($section, $statementStart, $i - $statementStart + 1);
        if (str_contains($statement, 'INSERT INTO `conteneurs`')) {
            $statements[] = $statement;
        }
        $statementStart = $i + 1;
    }
}

$decodeMysql = static function (string $value): ?string {
    $value = trim($value);
    if (strcasecmp($value, 'NULL') === 0) {
        return null;
    }
    if (! str_starts_with($value, "'") || ! str_ends_with($value, "'")) {
        return $value;
    }

    $value = substr($value, 1, -1);
    return preg_replace_callback('/\\\\(.)/s', static function (array $match): string {
        return match ($match[1]) {
            '0' => "\0",
            'b' => "\x08",
            'n' => "\n",
            'r' => "\r",
            't' => "\t",
            'Z' => "\x1a",
            default => $match[1],
        };
    }, $value) ?? $value;
};

$records = [];
foreach ($statements as $statement) {
    $values = substr($statement, strpos($statement, 'VALUES') + strlen('VALUES'));
    $rows = [];
    $row = '';
    $quoted = false;
    $escaped = false;
    $depth = 0;
    for ($i = 0, $length = strlen($values); $i < $length; $i++) {
        $char = $values[$i];
        if ($quoted) {
            $row .= $char;
            if ($escaped) {
                $escaped = false;
            } elseif ($char === "\\") {
                $escaped = true;
            } elseif ($char === "'") {
                $quoted = false;
            }
            continue;
        }
        if ($char === "'") {
            $quoted = true;
            $row .= $char;
        } elseif ($char === '(') {
            $depth++;
            if ($depth > 1) {
                $row .= $char;
            }
        } elseif ($char === ')') {
            $depth--;
            if ($depth === 0) {
                $rows[] = $row;
                $row = '';
            } elseif ($depth > 0) {
                $row .= $char;
            }
        } elseif ($depth > 0) {
            $row .= $char;
        } elseif ($char === ';') {
            break;
        }
    }

foreach ($rows as $rawRow) {
    $cells = [];
    $cell = '';
    $quoted = false;
    $escaped = false;
    for ($i = 0, $length = strlen($rawRow); $i < $length; $i++) {
        $char = $rawRow[$i];
        if ($quoted) {
            $cell .= $char;
            if ($escaped) {
                $escaped = false;
            } elseif ($char === "\\") {
                $escaped = true;
            } elseif ($char === "'") {
                $quoted = false;
            }
        } elseif ($char === "'") {
            $quoted = true;
            $cell .= $char;
        } elseif ($char === ',') {
            $cells[] = $decodeMysql($cell);
            $cell = '';
        } else {
            $cell .= $char;
        }
    }
    $cells[] = $decodeMysql($cell);

    if (count($cells) !== count($columns)) {
        throw new RuntimeException('Column/value count mismatch in a conteneurs row.');
    }

    $record = array_combine($columns, $cells);
    $records[] = array_intersect_key($record, array_flip([
        'id', 'reference', 'description', 'description_en', 'description_fr',
        'description_es', 'description_de', 'description_nl', 'description_fi', 'description_ch',
    ]));
}
}

$json = json_encode($records, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR).PHP_EOL;
if (isset($argv[2])) {
    file_put_contents($argv[2], $json);
} else {
    echo $json;
}
