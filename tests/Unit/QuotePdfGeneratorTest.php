<?php

namespace Tests\Unit;

use App\Services\QuotePdfGenerator;
use PHPUnit\Framework\TestCase;

class QuotePdfGeneratorTest extends TestCase
{
    public function test_it_generates_a_valid_multipage_pdf_for_a_quote_request(): void
    {
        $quote = [
            'reference' => 'DEV-2026-TEST01',
            'created_at' => '2026-10-24 12:30',
            'prenom' => 'Élodie',
            'nom' => 'Test',
            'email' => 'client@example.test',
            'adresse' => 'Via Roma 1',
            'ville' => 'Roma',
            'code_postal' => '00100',
            'pays' => 'Italia',
            'notes' => str_repeat('Contraintes d’accès et détails à confirmer dans le devis. ', 100),
        ];

        $pdf = (new QuotePdfGenerator())->generate($quote, [[
            'reference_conteneur' => 'CONT-01',
            'type_conteneur' => 'Standard',
            'type_ligne' => 'vente',
            'quantite' => 2,
            'prix_unitaire_indicatif' => '1200.00',
        ]], 'C.E.S. Container', 'fr');

        self::assertStringStartsWith('%PDF-1.4', $pdf);
        self::assertStringEndsWith('%%EOF', $pdf);
        self::assertGreaterThan(1, substr_count($pdf, '/Type /Page '));
    }
}
