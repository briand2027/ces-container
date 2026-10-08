<?php

namespace App\Http\Controllers;

use App\Mail\QuoteRequestMail;
use App\Models\Order;
use App\Models\QuoteRequest;
use App\Models\SystemSetting;
use App\Services\CartService;
use App\Support\IncidentLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function store(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'type_client' => 'required|in:particulier,entreprise',
            'nom' => 'required|string|max:100',
            'prenom' => 'nullable|string|max:100',
            'nom_entreprise' => 'nullable|string|max:200',
            'email' => 'required|email|max:255',
            'telephone' => 'nullable|string|max:30',
            'adresse' => 'required|string|max:1000',
            'ville' => 'required|string|max:100',
            'code_postal' => 'required|string|max:30',
            'pays' => 'required|string|max:100',
            'numero_tva' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:3000',
            'duree_location_jours' => 'nullable|integer|min:1|max:3650',
        ]);

        $items = $cart->items();
        if ($items->isEmpty()) {
            return back()->withErrors(['cart' => 'Votre panier ne contient aucun article disponible.'])->withInput();
        }

        $quote = DB::transaction(function () use ($data, $items) {
            do {
                $reference = 'DEV-'.now()->format('Y').'-'.Str::upper(Str::random(6));
            } while (QuoteRequest::where('reference', $reference)->exists());

            $quote = QuoteRequest::create($data + [
                'reference' => $reference,
                'duree_location_jours' => $data['duree_location_jours'] ?? null,
                'statut' => 'nouveau',
            ]);

            foreach ($items as $item) {
                $container = $item['container'];
                $quote->lines()->create([
                    'conteneur_id' => $container->id,
                    'reference_conteneur' => $container->reference,
                    'type_conteneur' => $container->type_conteneur ?? null,
                    'type_ligne' => $item['type'],
                    'quantite' => $item['quantity'],
                    'prix_unitaire_indicatif' => $item['unit_price'] > 0 ? $item['unit_price'] : null,
                    'duree_location_jours' => $item['type'] === 'location' ? ($data['duree_location_jours'] ?? null) : null,
                ]);
            }

            return $quote;
        });

        $recipients = array_values(array_unique(array_filter(array_map('trim', [
            SystemSetting::getValue('email_expediteur_principal') ?: SystemSetting::getValue('entreprise_email'),
            SystemSetting::getValue('email_notification_contact'),
            SystemSetting::getValue('email_notification_contact_2'),
            SystemSetting::getValue('email_notification_commande'),
        ]), fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))));

        $emailFailed = false;
        foreach ($recipients as $recipient) {
            try {
                Mail::mailer('smtp')->to($recipient)->send(new QuoteRequestMail($quote, false, app()->getLocale()));
            } catch (\Throwable $exception) {
                $emailFailed = true;
                IncidentLogger::exception($exception, 'Quote request admin notification email failed', [
                    'operation' => 'quote.notification',
                    'quote_id' => $quote->id,
                ]);
            }
        }

        if ($recipients === []) {
            $emailFailed = true;
            IncidentLogger::warning('Quote request has no valid admin notification recipients', [
                'operation' => 'quote.notification',
                'quote_id' => $quote->id,
            ]);
        }

        try {
            Mail::mailer('smtp')->to($quote->email)->send(new QuoteRequestMail($quote, true, app()->getLocale()));
        } catch (\Throwable $exception) {
            $emailFailed = true;
            IncidentLogger::exception($exception, 'Quote request customer confirmation email failed', [
                'operation' => 'quote.confirmation',
                'quote_id' => $quote->id,
            ]);
        }

        $cart->clear();

        return redirect()->route('quote.success')->with([
            'quote_request_reference' => $quote->reference,
            'quote_email_warning' => $emailFailed,
        ]);
    }

    public function quoteSuccess(Request $request)
    {
        $reference = $request->session()->get('quote_request_reference');
        abort_unless($reference, 404);

        return view('public.cart.quote-success', [
            'reference' => $reference,
            'emailWarning' => (bool) $request->session()->get('quote_email_warning'),
        ]);
    }

    public function success(Order $order)
    {
        abort_unless(session('order_created') && session('order_created') === true, 403);
        $bank = [
            'banque_nom' => SystemSetting::getValue('banque_nom', ''), 'banque_titulaire' => SystemSetting::getValue('banque_titulaire', ''),
            'banque_iban' => SystemSetting::getValue('banque_iban', ''), 'banque_bic' => SystemSetting::getValue('banque_bic', ''),
            'banque_adresse' => SystemSetting::getValue('banque_adresse', ''), 'banque_instructions' => SystemSetting::getValue('banque_instructions', ''),
        ];
        $proofEmail = SystemSetting::getValue('entreprise_email', 'contact@containerequipmentservices.lt');

        return view('public.cart.success', ['order' => $order->load('client', 'lines.container'), 'bank' => $bank, 'proofEmail' => $proofEmail]);
    }
}
