<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\SystemSetting;
use App\Mail\QuoteRequestMail;
use App\Support\IncidentLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QuoteRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = QuoteRequest::withCount('lines')->latest();

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut')->toString());
        }

        if ($request->filled('search')) {
            $term = '%'.$request->string('search')->toString().'%';
            $query->where(fn ($builder) => $builder
                ->where('reference', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhere('nom', 'like', $term)
                ->orWhere('prenom', 'like', $term)
                ->orWhere('nom_entreprise', 'like', $term));
        }

        return view('admin.quotes.index', [
            'quotes' => $query->paginate(20)->withQueryString(),
            'stats' => [
                'total' => QuoteRequest::count(),
                'new' => QuoteRequest::where('statut', 'nouveau')->count(),
                'processing' => QuoteRequest::whereIn('statut', ['en_cours', 'devis_envoye'])->count(),
                'closed' => QuoteRequest::where('statut', 'sans_suite')->count(),
            ],
        ]);
    }

    public function show(QuoteRequest $quote)
    {
        return view('admin.quotes.show', ['quote' => $quote->load('lines')]);
    }

    public function status(Request $request, QuoteRequest $quote)
    {
        $data = $request->validate(['statut' => 'required|in:nouveau,en_cours,devis_envoye,sans_suite']);
        $quote->update($data);

        return back()->with('success', 'Suivi de la commande mis à jour.');
    }

    public function remind(QuoteRequest $quote)
    {
        $sender = SystemSetting::getValue('email_expediteur_principal') ?: SystemSetting::getValue('entreprise_email');
        if (! $sender) {
            return back()->withErrors(['reminder' => 'Définissez l’adresse e-mail principale dans les paramètres avant de relancer le client.']);
        }

        try {
            Mail::mailer('smtp')->to($quote->email)->send(new QuoteRequestMail($quote->load('lines'), true, app()->getLocale(), true));
        } catch (\Throwable $exception) {
            IncidentLogger::exception($exception, 'Quote request follow-up email failed', [
                'operation' => 'quote.follow_up',
                'quote_id' => $quote->id,
            ]);

            return back()->withErrors(['reminder' => 'La relance n’a pas pu être envoyée. Vérifiez le SMTP puis réessayez.']);
        }

        Log::channel('activity')->info('Quote request follow-up sent', [
            'method' => request()->method(),
            'path' => '/'.ltrim(request()->path(), '/'),
            'route' => request()->route()?->getName(),
            'action' => request()->route()?->getActionName(),
            'admin_id' => session('admin_id'),
            'operation' => 'quote.follow_up',
            'quote_id' => $quote->id,
            'quote_reference' => $quote->reference,
        ]);

        return back()->with('success', 'Relance envoyée à '.$quote->email.' avec le récapitulatif PDF en pièce jointe.');
    }
}
