<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Container;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $term = trim((string)$request->input('q',''));
        $products = Container::with('category')
            ->where('statut','disponible')
            ->when($term !== '', function($query) use ($term) {
                $like='%'.$term.'%';
                $query->where(function($q) use ($like) {
                    $q->where('reference','like',$like)
                      ->orWhere('type_conteneur','like',$like)
                      ->orWhere('description','like',$like)
                      ->orWhere('description_fr','like',$like)
                      ->orWhere('description_en','like',$like);
                });
            })
            ->latest()->paginate(12)->withQueryString();

        return view('public.search', [
            'products'=>$products,
            'categories'=>Category::where('statut','actif')->orderBy('ordre_affichage')->get(),
            'term'=>$term,
            'vatRate'=>(float) SystemSetting::getValue('taux_tva',20),
        ]);
    }
}
