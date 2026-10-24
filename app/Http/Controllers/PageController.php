<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Container;
use App\Models\Service;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function legal(string $page)
    {
        abort_unless(in_array($page, ['terms', 'delivery', 'returns', 'warranty', 'privacy', 'legal-notice', 'payment'], true), 404);

        return view('public.legal', [
            'page' => $page,
            'company' => SystemSetting::getValue('entreprise_nom', 'C.E.S. Container'),
            'identity' => array_filter([
                'form' => SystemSetting::getValue('entreprise_forme'),
                'registration' => SystemSetting::getValue('entreprise_numero_registre'),
                'vat' => SystemSetting::getValue('entreprise_tva'),
                'address' => SystemSetting::getValue('entreprise_adresse'),
                'email' => SystemSetting::getValue('entreprise_email') ?: SystemSetting::getValue('email_expediteur_principal'),
                'phone' => SystemSetting::getValue('entreprise_telephone'),
            ]),
        ]);
    }

    public function services()
    {
        return view('public.services', [
            'services' => Service::where('statut', 'actif')->latest()->limit(6)->get(),
            'categories' => Category::where('statut', 'actif')->orderBy('ordre_affichage')->get(),
        ]);
    }

    public function about()
    {
        return view('public.about', [
            'categories' => Category::where('statut', 'actif')->withCount('containers')->orderBy('ordre_affichage')->get(),
            'services' => Service::where('statut', 'actif')->latest()->limit(4)->get(),
            'productsCount' => Container::where('statut', 'disponible')->count(),
        ]);
    }

    public function projects()
    {
        return view('public.projects', [
            'categories' => Category::where('statut', 'actif')->withCount('containers')->orderBy('ordre_affichage')->get(),
            'products' => Container::with('category')->where('statut', 'disponible')->latest()->limit(8)->get(),
            'services' => Service::where('statut', 'actif')->latest()->limit(4)->get(),
        ]);
    }

    public function gallery(Request $request)
    {
        $filters = $request->validate([
            'categorie' => ['nullable', 'integer', 'min:1', 'exists:categories,id'],
            'type' => ['nullable', 'string', 'max:50'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $query = Container::with('category')->where('statut', 'disponible')->whereNotNull('image_principale');

        if (!empty($filters['categorie'])) {
            $query->where('categorie_id', (int) $filters['categorie']);
        }

        if (!empty($filters['type'])) {
            $query->where('type_conteneur', $filters['type']);
        }

        return view('public.gallery', [
            'items' => $query->latest()->paginate(9)->withQueryString(),
            'categories' => Category::where('statut', 'actif')->orderBy('ordre_affichage')->get(),
            'types' => Container::where('statut', 'disponible')->whereNotNull('type_conteneur')->distinct()->orderBy('type_conteneur')->pluck('type_conteneur'),
        ]);
    }
}
