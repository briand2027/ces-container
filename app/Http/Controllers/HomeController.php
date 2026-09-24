<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Container;
use App\Models\Service;
use App\Models\SystemSetting;
class HomeController extends Controller
{
    public function index()
    {
        return view('public.home', [
            'categories' => Category::where('statut','actif')->orderBy('ordre_affichage')->get(),
            'products' => Container::with('category')->where('statut','disponible')->latest()->limit(6)->get(),
            'services' => Service::where('statut','actif')->latest()->limit(6)->get(),
            'vatRate' => (float) SystemSetting::getValue('taux_tva',20),
        ]);
    }
}
