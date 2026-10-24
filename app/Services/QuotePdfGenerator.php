<?php

namespace App\Services;

class QuotePdfGenerator
{
    public function generate(array $quote, array $lines, string $company, string $locale = 'it', float $vatRate = 20): string
    {
        $labels = $this->labels($locale);
        $content = [];
        $content[] = ['text' => $labels['title'], 'size' => 19, 'bold' => true, 'gap' => 8];
        $content[] = ['text' => $labels['reference'].': '.$quote['reference'], 'size' => 12, 'bold' => true, 'gap' => 3];
        $content[] = ['text' => $labels['created'].': '.$quote['created_at'], 'size' => 9, 'gap' => 13];
        $content[] = ['text' => $labels['status'], 'size' => 10, 'bold' => true, 'gap' => 12];
        $content[] = ['text' => $labels['customer'], 'size' => 13, 'bold' => true, 'gap' => 5];

        foreach (array_filter([
            trim(($quote['prenom'] ?? '').' '.($quote['nom'] ?? '')),
            $quote['nom_entreprise'] ?? '',
            $quote['email'] ?? '',
            $quote['telephone'] ?? '',
            $quote['adresse'] ?? '',
            trim(($quote['code_postal'] ?? '').' '.($quote['ville'] ?? '').' '.($quote['pays'] ?? '')),
            !empty($quote['numero_tva']) ? $labels['vat'].': '.$quote['numero_tva'] : '',
        ]) as $detail) {
            $content[] = ['text' => $detail, 'size' => 10, 'gap' => 3];
        }

        $content[] = ['text' => $labels['items'], 'size' => 13, 'bold' => true, 'gap' => 7];
        $subtotal = 0.0;
        foreach ($lines as $line) {
            $kind = ($line['type_ligne'] ?? '') === 'location' ? $labels['rental'] : $labels['purchase'];
            $unitPrice = (float) ($line['prix_unitaire_indicatif'] ?? 0);
            $quantity = max(1, (int) ($line['quantite'] ?? 1));
            $duration = ($line['type_ligne'] ?? '') === 'location' ? max(1, (int) ($line['duree_location_jours'] ?? 1)) : 1;
            $subtotal += $unitPrice * $quantity * $duration;
            $unit = ($line['prix_unitaire_indicatif'] ?? null) !== null
                ? number_format($unitPrice, 2, ',', ' ').' EUR'.($kind === $labels['rental'] ? ' / '.$labels['day'] : '')
                : $labels['to_confirm'];
            $content[] = ['text' => ($line['reference_conteneur'] ?? '').' | '.($line['type_conteneur'] ?? '').' | '.$kind, 'size' => 10, 'bold' => true, 'gap' => 2];
            $content[] = ['text' => $labels['quantity'].': '.($line['quantite'] ?? 1).' | '.$labels['unit_price'].': '.$unit.(!empty($line['duree_location_jours']) ? ' | '.$labels['duration'].': '.$line['duree_location_jours'].' '.$labels['days'] : ''), 'size' => 9, 'gap' => 8];
        }

        $tax = round($subtotal * $vatRate / 100, 2);
        $content[] = ['text' => $labels['subtotal'].': '.number_format($subtotal, 2, ',', ' ').' EUR', 'size' => 10, 'gap' => 3];
        $content[] = ['text' => $labels['tax'].' ('.number_format($vatRate, 2, ',', '').'%): '.number_format($tax, 2, ',', ' ').' EUR', 'size' => 10, 'gap' => 3];
        $content[] = ['text' => $labels['total'].': '.number_format($subtotal + $tax, 2, ',', ' ').' EUR', 'size' => 12, 'bold' => true, 'gap' => 8];

        if (!empty($quote['notes'])) {
            $content[] = ['text' => $labels['notes'], 'size' => 13, 'bold' => true, 'gap' => 5];
            foreach (preg_split('/\R/u', $quote['notes']) as $noteLine) {
                $content[] = ['text' => $noteLine, 'size' => 10, 'gap' => 3];
            }
        }

        $content[] = ['text' => $labels['delivery'], 'size' => 10, 'bold' => true, 'gap' => 5];
        $content[] = ['text' => $labels['disclaimer'], 'size' => 9, 'gap' => 3];
        $content[] = ['text' => $company, 'size' => 10, 'bold' => true, 'gap' => 3];

        $pages = [];
        $page = [];
        $remaining = 682;
        foreach ($content as $entry) {
            $size = (int) ($entry['size'] ?? 10);
            $lineWidth = max(36, (int) floor(500 / max(1, $size * 0.62)));
            $linesForEntry = $this->wrap((string) $entry['text'], $lineWidth);
            $lineHeight = $size + 4;
            $gap = (int) ($entry['gap'] ?? 3);

            while ($linesForEntry !== []) {
                if ($remaining < $lineHeight + $gap) {
                    $pages[] = $page;
                    $page = [];
                    $remaining = 682;
                }

                $capacity = max(1, (int) floor(($remaining - $gap) / $lineHeight));
                $chunk = array_splice($linesForEntry, 0, $capacity);
                $chunkGap = $linesForEntry === [] ? $gap : 0;
                $chunkEntry = $entry;
                $chunkEntry['gap'] = $chunkGap;
                $page[] = [$chunkEntry, $chunk];
                $remaining -= count($chunk) * $lineHeight + $chunkGap;

                if ($linesForEntry !== []) {
                    $pages[] = $page;
                    $page = [];
                    $remaining = 682;
                }
            }
        }
        if ($page !== []) {
            $pages[] = $page;
        }

        return $this->buildPdf($pages, $company, $labels);
    }

    private function wrap(string $text, int $width): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        return $text === '' ? [''] : explode("\n", wordwrap($text, $width, "\n", true));
    }

    private function buildPdf(array $pages, string $company, array $labels): string
    {
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $pageIds = [];
        $nextId = 5;

        foreach ($pages as $index => $entries) {
            $pageId = $nextId++;
            $streamId = $nextId++;
            $pageIds[] = $pageId;
            $commands = "q\n0.055 0.24 0.20 rg\n0 752 595 90 re f\nQ\n";
            $commands .= $this->textCommand($company, 42, 807, 11, true, '1 1 1');
            $commands .= $this->textCommand($labels['document'].'  |  '.($index + 1), 42, 785, 9, false, '0.88 0.94 0.92');
            $y = 725;
            foreach ($entries as [$entry, $linesForEntry]) {
                $size = (int) ($entry['size'] ?? 10);
                foreach ($linesForEntry as $line) {
                    $commands .= $this->textCommand($line, 44, $y, $size, (bool) ($entry['bold'] ?? false), '0.11 0.17 0.16');
                    $y -= $size + 4;
                }
                $y -= (int) ($entry['gap'] ?? 3);
            }
            $commands .= "0.82 0.86 0.85 RG\n42 42 m 553 42 l S\n";
            $commands .= $this->textCommand($labels['page'].' '.($index + 1).' / '.count($pages), 44, 26, 8, false, '0.35 0.40 0.39');
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents '.$streamId.' 0 R >>';
            $objects[$streamId] = '<< /Length '.strlen($commands)." >>\nstream\n".$commands."endstream";
        }

        $kids = implode(' ', array_map(static fn (int $id): string => $id.' 0 R', $pageIds));
        $objects[2] = '<< /Type /Pages /Kids ['.$kids.'] /Count '.count($pageIds).' >>';
        ksort($objects);
        $pdf = "%PDF-1.4\n% CES quote summary\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$object."\nendobj\n";
        }
        $xref = strlen($pdf);
        $size = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        for ($id = 1; $id < $size; $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id] ?? 0);
        }
        $pdf .= "trailer\n<< /Size {$size} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function textCommand(string $text, int $x, int $y, int $size, bool $bold, string $color): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        $text = $encoded === false ? $text : $encoded;
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        return "BT /F".($bold ? '2' : '1')." {$size} Tf {$color} rg 1 0 0 1 {$x} {$y} Tm ({$escaped}) Tj ET\n";
    }

    private function labels(string $locale): array
    {
        $labels = [
            'it' => ['title'=>'Riepilogo dell’ordine','document'=>'Conferma ordine','created'=>'Data','status'=>'Ordine registrato - pagamento in attesa','customer'=>'Cliente','vat'=>'Partita IVA','items'=>'Articoli ordinati','purchase'=>'Acquisto','rental'=>'Noleggio','quantity'=>'Quantità','unit_price'=>'Prezzo unitario','to_confirm'=>'Da confermare','duration'=>'Durata','day'=>'giorno','days'=>'giorni','notes'=>'Note','delivery'=>'Trasporto e consegna','disclaimer'=>'I prezzi degli articoli e le imposte indicati nel riepilogo si applicano all’ordine e non saranno rinegoziati. Gli eventuali costi di trasporto sono comunicati separatamente. Paga solo dopo aver ricevuto le coordinate bancarie ufficiali. Puoi annullare l’ordine rispondendo chiaramente all’e-mail prima del pagamento.','page'=>'Pagina'],
            'fr' => ['title'=>'Récapitulatif de commande','document'=>'Confirmation de commande','created'=>'Date','status'=>'Commande enregistrée - paiement en attente','customer'=>'Client','vat'=>'N° TVA','items'=>'Articles commandés','purchase'=>'Achat','rental'=>'Location','quantity'=>'Quantité','unit_price'=>'Prix unitaire','to_confirm'=>'À confirmer','duration'=>'Durée','day'=>'jour','days'=>'jours','notes'=>'Précisions','delivery'=>'Transport et livraison','disclaimer'=>'Les prix des articles et taxes indiqués dans le récapitulatif s’appliquent à la commande et ne seront pas renégociés. Les éventuels frais de transport sont communiqués séparément. Payez uniquement après réception des coordonnées bancaires officielles. Vous pouvez annuler en répondant clairement à l’e-mail avant le paiement.','page'=>'Page'],
            'en' => ['title'=>'Order summary','document'=>'Order confirmation','created'=>'Date','status'=>'Order registered - payment pending','customer'=>'Customer','vat'=>'VAT number','items'=>'Ordered items','purchase'=>'Purchase','rental'=>'Rental','quantity'=>'Quantity','unit_price'=>'Unit price','to_confirm'=>'To be confirmed','duration'=>'Duration','day'=>'day','days'=>'days','notes'=>'Notes','delivery'=>'Transport and delivery','disclaimer'=>'The item prices and taxes shown in this summary apply to the order and will not be renegotiated. Any transport charges are communicated separately. Pay only after receiving the official bank details. You may cancel by clearly replying to the email before payment.','page'=>'Page'],
            'es' => ['title'=>'Resumen del pedido','document'=>'Confirmación del pedido','created'=>'Fecha','status'=>'Pedido registrado - pago pendiente','customer'=>'Cliente','vat'=>'N.º de IVA','items'=>'Artículos pedidos','purchase'=>'Compra','rental'=>'Alquiler','quantity'=>'Cantidad','unit_price'=>'Precio unitario','to_confirm'=>'Por confirmar','duration'=>'Duración','day'=>'día','days'=>'días','notes'=>'Notas','delivery'=>'Transporte y entrega','disclaimer'=>'Los precios de los artículos y los impuestos de este resumen se aplican al pedido y no se renegociarán. Los posibles gastos de transporte se comunican por separado. Paga solo después de recibir los datos bancarios oficiales. Puedes cancelar respondiendo claramente al correo antes de pagar.','page'=>'Página'],
            'de' => ['title'=>'Bestellübersicht','document'=>'Bestellbestätigung','created'=>'Datum','status'=>'Bestellung registriert - Zahlung ausstehend','customer'=>'Kunde','vat'=>'USt-IdNr.','items'=>'Bestellte Artikel','purchase'=>'Kauf','rental'=>'Miete','quantity'=>'Menge','unit_price'=>'Stückpreis','to_confirm'=>'Noch zu bestätigen','duration'=>'Dauer','day'=>'Tag','days'=>'Tage','notes'=>'Anmerkungen','delivery'=>'Transport und Lieferung','disclaimer'=>'Die Artikelpreise und Steuern in dieser Übersicht gelten für die Bestellung und werden nicht neu verhandelt. Eventuelle Transportkosten werden separat mitgeteilt. Zahlen Sie erst nach Erhalt der offiziellen Bankdaten. Sie können vor der Zahlung per eindeutiger E-Mail-Antwort stornieren.','page'=>'Seite'],
            'nl' => ['title'=>'Besteloverzicht','document'=>'Bestelbevestiging','created'=>'Datum','status'=>'Bestelling geregistreerd - betaling in afwachting','customer'=>'Klant','vat'=>'Btw-nummer','items'=>'Bestelde artikelen','purchase'=>'Aankoop','rental'=>'Huur','quantity'=>'Aantal','unit_price'=>'Prijs per stuk','to_confirm'=>'Nog te bevestigen','duration'=>'Duur','day'=>'dag','days'=>'dagen','notes'=>'Opmerkingen','delivery'=>'Transport en levering','disclaimer'=>'De artikelprijzen en belastingen in dit overzicht gelden voor de bestelling en worden niet heronderhandeld. Eventuele transportkosten worden apart meegedeeld. Betaal pas na ontvangst van de officiële bankgegevens. U kunt vóór betaling annuleren door duidelijk op de e-mail te antwoorden.','page'=>'Pagina'],
            'fi' => ['title'=>'Tilausyhteenveto','document'=>'Tilauksen vahvistus','created'=>'Päivämäärä','status'=>'Tilaus rekisteröity - maksu odottaa','customer'=>'Asiakas','vat'=>'ALV-tunniste','items'=>'Tilatut tuotteet','purchase'=>'Osto','rental'=>'Vuokraus','quantity'=>'Määrä','unit_price'=>'Yksikköhinta','to_confirm'=>'Vahvistettava','duration'=>'Kesto','day'=>'päivä','days'=>'päivää','notes'=>'Lisätiedot','delivery'=>'Kuljetus ja toimitus','disclaimer'=>'Yhteenvedon tuotehinnat ja verot koskevat tilausta, eikä niitä neuvotella uudelleen. Mahdolliset kuljetuskulut ilmoitetaan erikseen. Maksa vasta saatuasi viralliset pankkitiedot. Voit peruuttaa ennen maksua vastaamalla selkeästi sähköpostiin.','page'=>'Sivu'],
            'ch' => ['title'=>'Bestellübersicht','document'=>'Bestellbestätigung','created'=>'Datum','status'=>'Bestellung registriert - Zahlung ausstehend','customer'=>'Kunde','vat'=>'MWST-Nr.','items'=>'Bestellte Artikel','purchase'=>'Kauf','rental'=>'Miete','quantity'=>'Menge','unit_price'=>'Stückpreis','to_confirm'=>'Noch zu bestätigen','duration'=>'Dauer','day'=>'Tag','days'=>'Tage','notes'=>'Anmerkungen','delivery'=>'Transport und Lieferung','disclaimer'=>'Die Artikelpreise und Steuern in dieser Übersicht gelten für die Bestellung und werden nicht neu verhandelt. Eventuelle Transportkosten werden separat mitgeteilt. Zahlen Sie erst nach Erhalt der offiziellen Bankdaten. Sie können vor der Zahlung per eindeutiger E-Mail-Antwort stornieren.','page'=>'Seite'],
        ];

        $selected = $labels[$locale] ?? $labels['en'];
        $selected['subtotal'] = match ($locale) {
            'it' => 'Subtotale IVA esclusa', 'fr' => 'Sous-total HT', 'es' => 'Subtotal sin impuestos',
            'de', 'ch' => 'Zwischensumme ohne MwSt.', 'nl' => 'Subtotaal excl. btw', 'fi' => 'Välisummaa ilman veroa', default => 'Subtotal before tax',
        };
        $selected['tax'] = match ($locale) {
            'it' => 'IVA', 'fr' => 'TVA', 'es' => 'Impuestos', 'de', 'ch' => 'MwSt.', 'nl' => 'Btw', 'fi' => 'ALV', default => 'VAT',
        };
        $selected['total'] = match ($locale) {
            'it' => 'Totale IVA inclusa', 'fr' => 'Total TTC', 'es' => 'Total con impuestos',
            'de', 'ch' => 'Gesamtbetrag inkl. MwSt.', 'nl' => 'Totaal incl. btw', 'fi' => 'Yhteensä veroineen', default => 'Total including tax',
        };
        $selected['reference'] = match ($locale) {
            'it' => 'Riferimento', 'fr' => 'Référence', 'es' => 'Referencia',
            'de', 'ch' => 'Referenz', 'nl' => 'Referentie', 'fi' => 'Viite',
            default => 'Reference',
        };

        return $selected;
    }
}
