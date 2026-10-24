<?php

declare(strict_types=1);

$directory = __DIR__;
$rows = json_decode((string) file_get_contents($directory . '/container-description-data.json'), true, 512, JSON_THROW_ON_ERROR);
$locales = ['en', 'fr', 'es', 'de', 'nl', 'fi', 'ch'];
$fields = ['description', ...array_map(static fn (string $locale): string => 'description_' . $locale, $locales)];
$missingItalianIds = [30, 125, 144, 146, 151, 157, 169];

function cleanDescription(string $value, string $field = 'description'): string
{
    $value = str_replace('\\n', "\n", $value);
    $value = preg_replace('/Tradotto con DeepL[^\r\n]*/iu', '', $value) ?? $value;
    $value = preg_replace('/^\s*#{1,6}\s*/mu', '', $value) ?? $value;
    $value = preg_replace('/^\s*[*+]\s+/mu', '- ', $value) ?? $value;
    $value = str_replace(['**', '__', '*'], '', $value);
    $value = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0E}\x{FE0F}\x{200D}\x{20E3}\x{1F1E6}-\x{1F1FF}]/u', '', $value) ?? $value;
    $value = preg_replace('/^\s*[—–-]{3,}\s*$/mu', '', $value) ?? $value;

    $value = preg_replace('/(?:Free storage at our terminal for up to six months after purchase(?:; longer periods can be agreed)?|Free storage: up to six months after purchase at the seller\x27s terminal, with the possibility of extension\.|Free storage (?:at our terminal )?for up to six months after purchase(?: if the delivery site is not yet ready)?\.|Free storage(?: at the terminal)? for up to six months after purchase(?:, if necessary)?\.|Free storage in our terminal up to six months after purchase\.)/i', 'The original source mentions storage after purchase; availability and terms must be confirmed in the written quote.', $value) ?? $value;
    $value = preg_replace('/(?:Stockage gratuit[^\r\n.]*|Stoccaggio gratuito[^\r\n.]*|Almacenamiento gratuito[^\r\n.]*)/iu', 'La source d’origine mentionne un stockage après l’achat ; sa disponibilité et ses conditions doivent être confirmées dans le devis écrit.', $value) ?? $value;
    $value = preg_replace('/Dopo l\x27acquisto, conserviamo gratuitamente il tuo container presso il nostro terminal fino a sei mesi\./iu', 'La fonte originale menziona la possibilità di stoccaggio dopo l’acquisto; disponibilità e condizioni devono essere confermate nel preventivo scritto.', $value) ?? $value;
    $value = preg_replace('/Trasporto e logistica: consegna possibile tramite camion con gru o side-loader\. Stoccaggio gratuito fino a 6 mesi dopo l\x27acquisto se il sito di consegna non è ancora pronto\./iu', 'Trasporto e logistica: modalità e costi di consegna sono definiti nel preventivo scritto. La fonte originale menziona anche un possibile stoccaggio dopo l’acquisto, soggetto a conferma scritta.', $value) ?? $value;
    $value = preg_replace('/Transport and logistics: delivery possible by truck with crane or side loader\. Free storage for up to 6 months after purchase if the delivery site is not yet ready\./i', 'Transport and logistics: delivery method and costs are defined in the written quote. The original source also mentions possible storage after purchase; availability and terms must be confirmed in writing.', $value) ?? $value;
    $value = preg_replace('/Transport et logistique : livraison possible par camion avec grue ou side-loader\. Stockage gratuit jusqu\x27à 6 mois après l\x27achat si le site de livraison n\x27est pas encore prêt\./iu', 'Transport et logistique : le mode et le coût de livraison sont définis dans le devis écrit. La source d’origine mentionne aussi un éventuel stockage après l’achat ; disponibilité et conditions doivent être confirmées par écrit.', $value) ?? $value;
    $value = preg_replace('/Transporte y logística: entrega posible mediante camión con grúa o side-loader\. Almacenamiento gratuito hasta 6 meses después de la compra si el lugar de entrega aún no está listo\./iu', 'Transporte y logística: el método y el coste de entrega se definen en el presupuesto escrito. La fuente original también menciona un posible almacenamiento después de la compra; su disponibilidad y condiciones deben confirmarse por escrito.', $value) ?? $value;

    $locale = $field === 'description' ? 'it' : substr($field, strlen('description_'));
    $notes = [
        'it' => ['La disponibilità e le condizioni di eventuale stoccaggio devono essere confermate nel preventivo scritto.', 'Le modalità e i costi di consegna sono definiti nel preventivo scritto.'],
        'en' => ['Availability and terms of any storage must be confirmed in the written quote.', 'Delivery arrangements and costs are specified in the written quote.'],
        'fr' => ['La disponibilité et les conditions d’un éventuel stockage doivent être confirmées dans le devis écrit.', 'Les modalités et frais de livraison sont précisés dans le devis écrit.'],
        'es' => ['La disponibilidad y las condiciones de cualquier almacenamiento deben confirmarse en el presupuesto escrito.', 'Las condiciones y los costes de entrega se especifican en el presupuesto escrito.'],
        'de' => ['Verfügbarkeit und Bedingungen einer möglichen Lagerung müssen im schriftlichen Angebot bestätigt werden.', 'Lieferbedingungen und -kosten werden im schriftlichen Angebot festgelegt.'],
        'nl' => ['Beschikbaarheid en voorwaarden van eventuele opslag moeten in de schriftelijke offerte worden bevestigd.', 'Leveringsvoorwaarden en -kosten worden in de schriftelijke offerte vastgelegd.'],
        'fi' => ['Mahdollisen varastoinnin saatavuus ja ehdot on vahvistettava kirjallisessa tarjouksessa.', 'Toimitustapa ja -kulut määritellään kirjallisessa tarjouksessa.'],
        'ch' => ['Verfügbarkeit und Bedingungen einer allfälligen Lagerung müssen in der schriftlichen Offerte bestätigt werden.', 'Lieferbedingungen und -kosten werden in der schriftlichen Offerte festgelegt.'],
    ];
    [$storageNote, $deliveryNote] = $notes[$locale] ?? $notes['en'];
    $storageClaim = '/[^.!?\\r\\n]*(?:(?:free|gratis|gratuit\\w*|gratuitamente|kostenlos\\w*|kostenfreie\\w*|kostenlose\\w*|grateloos|ilmais\\w*)[^.!?\\r\\n]*(?:storage|stockage|stoccaggio|deposit\\w*|almacen\\w*|lagerung|opslag|varastoin\\w*)|(?:storage|stockage|stoccaggio|deposit\\w*|almacen\\w*|lagerung|opslag|varastoin\\w*)[^.!?\\r\\n]*(?:free|gratis|gratuit\\w*|gratuitamente|kostenlos\\w*|kostenfreie\\w*|kostenlose\\w*|grateloos|ilmais\\w*))[^.!?\\r\\n]*[.!?]?/iu';
    $value = preg_replace($storageClaim, $storageNote, $value) ?? $value;
    $deliveryClaim = '/[^.!?\\r\\n]*(?:(?:free|gratis|gratuit\\w*|gratuitamente|kostenlos\\w*|kostenfreie\\w*|kostenlose\\w*|grateloos|ilmais\\w*)[^.!?\\r\\n]*(?:delivery|livraison|consegna|entrega|lieferung|levering|toimitus)|(?:delivery|livraison|consegna|entrega|lieferung|levering|toimitus)[^.!?\\r\\n]*(?:free|gratis|gratuit\\w*|gratuitamente|kostenlos\\w*|kostenfreie\\w*|kostenlose\\w*|grateloos|ilmais\\w*))[^.!?\\r\\n]*[.!?]?/iu';
    $value = preg_replace($deliveryClaim, $deliveryNote, $value) ?? $value;

    $lines = array_map(static fn (string $line): string => rtrim($line), explode("\n", $value));
    $value = implode("\n", $lines);
    $value = preg_replace('/\n{3,}/', "\n\n", $value) ?? $value;

    return trim($value);
}

function mysqlString(string $value): string
{
    $value = str_replace('\\', '\\\\', $value);
    $value = str_replace(["\0", "\n", "\r", "\x1a"], ['\\0', '\\n', '\\r', '\\Z'], $value);

    return "'" . str_replace("'", "''", $value) . "'";
}

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

$batchFiles = glob($directory . '/container-descriptions-batch-*.sql') ?: [];
sort($batchFiles, SORT_STRING);
$batches = [];
foreach ($batchFiles as $batchFile) {
    $sql = (string) file_get_contents($batchFile);
    $sql = preg_replace('/^START TRANSACTION;\s*/m', '', $sql);
    $sql = preg_replace('/\s*COMMIT;\s*$/', '', (string) $sql);
    $batches[] = '-- ' . basename($batchFile) . "\n" . trim((string) $sql);

    $sqliteSql = str_replace('\\n', "\n", (string) file_get_contents($batchFile));
    $sqliteSql = preg_replace('/^START TRANSACTION;\s*/m', 'BEGIN TRANSACTION;', $sqliteSql);
    $db->exec($sqliteSql);
}

$cleanupStatements = [];
$select = $db->query('SELECT * FROM conteneurs ORDER BY id');
foreach ($select as $row) {
    foreach ($fields as $field) {
        $original = (string) $row[$field];
        if (trim($original) === '') {
            continue;
        }

        $cleaned = cleanDescription($original, $field);
        if ($cleaned === $original) {
            continue;
        }

        $cleanupStatements[] = sprintf(
            "UPDATE `conteneurs` SET `%s` = %s WHERE `id` = %d AND `reference` = %s AND `%s` = %s;",
            $field,
            mysqlString($cleaned),
            (int) $row['id'],
            mysqlString((string) $row['reference']),
            $field,
            mysqlString($original),
        );
        $update = $db->prepare(sprintf('UPDATE conteneurs SET `%s` = ? WHERE id = ?', $field));
        $update->execute([$cleaned, $row['id']]);
    }
}

$missingLocales = [];
$formattingIssues = [];
$unconfirmedLogisticsClaims = [];
foreach ($db->query('SELECT * FROM conteneurs ORDER BY id') as $row) {
    foreach ($fields as $field) {
        $value = (string) $row[$field];
        if (preg_match('/(?:\*|^\s*#{1,6}\s|Tradotto con DeepL|[\x{1F000}-\x{1FAFF}]|[\x{2600}-\x{27BF}])/mu', $value)) {
            $formattingIssues[] = sprintf('%d %s: %s', $row['id'], $row['reference'], $field);
        }
        if ($field !== 'description' && trim((string) $row['description']) !== '' && trim($value) === '') {
            $missingLocales[] = sprintf('%d %s: %s', $row['id'], $row['reference'], $field);
        }
        if (preg_match('/[^.!?\r\n]*(?:(?:free|gratis|gratuit\w*|gratuitamente|kostenlos\w*|kostenfreie\w*|kostenlose\w*|grateloos|ilmais\w*)[^.!?\r\n]*(?:storage|stockage|stoccaggio|deposit\w*|almacen\w*|lagerung|opslag|varastoin\w*)|(?:storage|stockage|stoccaggio|deposit\w*|almacen\w*|lagerung|opslag|varastoin\w*)[^.!?\r\n]*(?:free|gratis|gratuit\w*|gratuitamente|kostenlos\w*|kostenfreie\w*|kostenlose\w*|grateloos|ilmais\w*))[^.!?\r\n]*[.!?]?/iu', $value)
            || preg_match('/[^.!?\r\n]*(?:(?:free|gratis|gratuit\w*|gratuitamente|kostenlos\w*|kostenfreie\w*|kostenlose\w*|grateloos|ilmais\w*)[^.!?\r\n]*(?:delivery|livraison|consegna|entrega|lieferung|levering|toimitus)|(?:delivery|livraison|consegna|entrega|lieferung|levering|toimitus)[^.!?\r\n]*(?:free|gratis|gratuit\w*|gratuitamente|kostenlos\w*|kostenfreie\w*|kostenlose\w*|grateloos|ilmais\w*))[^.!?\r\n]*[.!?]?/iu', $value)) {
            $unconfirmedLogisticsClaims[] = sprintf('%d %s: %s', $row['id'], $row['reference'], $field);
        }
    }
}

$count = (int) $db->query('SELECT COUNT(*) FROM conteneurs')->fetchColumn();
if ($count !== count($rows) || $missingLocales !== [] || $formattingIssues !== [] || $unconfirmedLogisticsClaims !== []) {
    throw new RuntimeException(sprintf(
        "Validation failed: rows=%d/%d; missing locales=%d; formatting issues=%d; unconfirmed logistics claims=%d",
        $count,
        count($rows),
        count($missingLocales),
        count($formattingIssues),
        count($unconfirmedLogisticsClaims),
    ));
}

$contents = "-- Clean and translate container descriptions from the supplied database export.\n";
$contents .= "-- Review the accompanying audit before import; take a database backup first.\n";
$contents .= "SET NAMES utf8mb4;\nSTART TRANSACTION;\n\n";
$contents .= implode("\n\n", $batches) . "\n\n";
$contents .= "-- Formatting cleanup for existing non-empty descriptions and translations.\n";
$contents .= implode("\n", $cleanupStatements) . "\n\nCOMMIT;\n";

$outputPath = $directory . '/clean-container-descriptions.sql';
if (file_put_contents($outputPath, $contents) === false) {
    throw new RuntimeException('Could not write final SQL file.');
}

$importDb = new PDO('sqlite::memory:');
$importDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$importDb->exec('CREATE TABLE conteneurs (
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
$insert = $importDb->prepare('INSERT INTO conteneurs VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
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
$importSql = str_replace('SET NAMES utf8mb4;', '', $contents);
$importSql = preg_replace('/START TRANSACTION;/', 'BEGIN TRANSACTION;', $importSql);
$importSql = str_replace('\\n', "\n", (string) $importSql);
$importDb->exec($importSql);

$finalCount = (int) $importDb->query('SELECT COUNT(*) FROM conteneurs')->fetchColumn();
$finalMissing = [];
foreach ($importDb->query('SELECT * FROM conteneurs ORDER BY id') as $row) {
    if (trim((string) $row['description']) === '') {
        continue;
    }
    foreach ($locales as $locale) {
        if (trim((string) $row['description_' . $locale]) === '') {
            $finalMissing[] = sprintf('%d:%s', $row['id'], $locale);
        }
    }
}
$finalEmptySourceChanges = [];
foreach ($missingItalianIds as $id) {
    $current = $importDb->prepare('SELECT description FROM conteneurs WHERE id = ?');
    $current->execute([$id]);
    $original = null;
    foreach ($rows as $row) {
        if ((int) $row['id'] === $id) {
            $original = $row['description'];
            break;
        }
    }
    if ($current->fetchColumn() !== $original) {
        $finalEmptySourceChanges[] = $id;
    }
}
if ($finalCount !== count($rows) || $finalMissing !== [] || $finalEmptySourceChanges !== []) {
    throw new RuntimeException(sprintf(
        "Final import simulation failed: rows=%d/%d; missing locales=%d; empty-source changes=%d",
        $finalCount,
        count($rows),
        count($finalMissing),
        count($finalEmptySourceChanges),
    ));
}

printf("Output: %s\n", $outputPath);
printf("Rows retained: %d\n", $count);
printf("Italian descriptions translated or preserved: %d\n", count(array_filter($rows, static fn (array $row): bool => trim((string) $row['description']) !== '')));
printf("Formatting cleanup statements: %d\n", count($cleanupStatements));
printf("Missing locales after simulation: %d\n", count($missingLocales));
printf("Remaining Markdown/emoji/DeepL markers: %d\n", count($formattingIssues));
printf("Unconfirmed free storage/delivery claims remaining: %d\n", count($unconfirmedLogisticsClaims));
printf("Final SQL import simulation: passed (%d rows retained; seven empty Italian sources unchanged)\n", $finalCount);
printf("SQL size: %d bytes\n", strlen($contents));
