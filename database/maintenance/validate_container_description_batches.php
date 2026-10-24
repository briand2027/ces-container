<?php

declare(strict_types=1);

$dataPath = __DIR__ . '/container-description-data.json';
$rows = json_decode((string) file_get_contents($dataPath), true, 512, JSON_THROW_ON_ERROR);
$locales = ['en', 'fr', 'es', 'de', 'nl', 'fi', 'ch'];
$missingItalianIds = [30, 125, 144, 146, 151, 157, 169];

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE conteneurs (
    id INTEGER PRIMARY KEY,
    reference TEXT,
    description TEXT,
    description_en TEXT,
    description_fr TEXT,
    description_es TEXT,
    description_de TEXT,
    description_nl TEXT,
    description_fi TEXT,
    description_ch TEXT
)');

$insert = $db->prepare('INSERT INTO conteneurs VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
foreach ($rows as $row) {
    $insert->execute([
        $row['id'],
        $row['reference'],
        $row['description'],
        $row['description_en'],
        $row['description_fr'],
        $row['description_es'],
        $row['description_de'],
        $row['description_nl'],
        $row['description_fi'],
        $row['description_ch'],
    ]);
}

$batchFiles = glob(__DIR__ . '/container-descriptions-batch-*.sql') ?: [];
sort($batchFiles, SORT_STRING);
foreach ($batchFiles as $batchFile) {
    $sql = (string) file_get_contents($batchFile);
    $sql = preg_replace('/^START TRANSACTION;\s*/m', 'BEGIN TRANSACTION;', $sql);
    $db->exec($sql);
}

$count = (int) $db->query('SELECT COUNT(*) FROM conteneurs')->fetchColumn();
$missingLocales = [];
$formattingIssues = [];
$normalizer = static function (string $value): string {
    $value = str_replace('\\n', "\n", $value);
    $value = preg_replace('/Tradotto con DeepL[^\r\n]*/iu', '', $value) ?? $value;
    $value = preg_replace('/^\s*#{1,6}\s*/mu', '', $value) ?? $value;
    $value = preg_replace('/^\s*[*+]\s+/mu', '- ', $value) ?? $value;
    $value = str_replace(['**', '__', '*'], '', $value);
    $value = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0E}\x{FE0F}\x{200D}\x{20E3}\x{1F1E6}-\x{1F1FF}]/u', '', $value) ?? $value;
    $value = preg_replace('/^\s*[—–-]{3,}\s*$/mu', '', $value) ?? $value;
    $lines = array_map(static fn (string $line): string => rtrim($line), explode("\n", $value));
    $value = preg_replace('/\n{3,}/', "\n\n", implode("\n", $lines)) ?? implode("\n", $lines);

    return trim($value);
};
foreach ($db->query('SELECT id, * FROM conteneurs ORDER BY id') as $row) {
    foreach (['description', ...array_map(static fn (string $locale): string => 'description_' . $locale, $locales)] as $field) {
        $update = $db->prepare(sprintf('UPDATE conteneurs SET `%s` = ? WHERE id = ?', $field));
        $update->execute([$normalizer((string) $row[$field]), $row['id']]);
    }
}
foreach ($db->query('SELECT * FROM conteneurs ORDER BY id') as $row) {
    foreach (['description', ...array_map(static fn (string $locale): string => 'description_' . $locale, $locales)] as $field) {
        $value = (string) $row[$field];
        if (preg_match('/(?:\\*|^\\s*#{1,6}\\s|Tradotto con DeepL|[\\x{1F000}-\\x{1FAFF}]|[\\x{2600}-\\x{27BF}])/mu', $value)) {
            $formattingIssues[] = sprintf('%d %s: %s', $row['id'], $row['reference'], $field);
        }

        if ($field === 'description' || trim((string) $row['description']) === '') {
            continue;
        }
        if (trim($value) === '') {
            $missingLocales[] = sprintf('%d %s: %s', $row['id'], $row['reference'], substr($field, strlen('description_')));
        }
    }
}

$emptyItalianChanged = [];
$originalById = [];
foreach ($rows as $row) {
    $originalById[(int) $row['id']] = $row;
}
foreach ($missingItalianIds as $id) {
    $current = $db->prepare('SELECT description FROM conteneurs WHERE id = ?');
    $current->execute([$id]);
    if (trim((string) $current->fetchColumn()) !== trim((string) $originalById[$id]['description'])) {
        $emptyItalianChanged[] = $id;
    }
}

printf("Rows retained: %d / %d\n", $count, count($rows));
printf("Non-empty Italian source rows: %d\n", count(array_filter($rows, static fn (array $row): bool => trim((string) $row['description']) !== '')));
printf("Missing locale fields after batches: %d\n", count($missingLocales));
printf("Fields with Markdown, emoji or DeepL text: %d\n", count($formattingIssues));
printf("Empty Italian source rows changed: %d\n", count($emptyItalianChanged));

if ($missingLocales !== []) {
    echo implode("\n", $missingLocales) . "\n";
}
if ($formattingIssues !== []) {
    echo implode("\n", $formattingIssues) . "\n";
}
if ($emptyItalianChanged !== []) {
    echo 'Changed empty-source IDs: ' . implode(', ', $emptyItalianChanged) . "\n";
}

exit($count === count($rows) && $missingLocales === [] && $emptyItalianChanged === [] ? 0 : 1);
