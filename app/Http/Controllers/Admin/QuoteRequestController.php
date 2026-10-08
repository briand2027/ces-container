<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;

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

        return back()->with('success', 'Suivi de la demande de devis mis à jour.');
    }
}
