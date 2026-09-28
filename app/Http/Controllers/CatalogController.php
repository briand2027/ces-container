<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Container;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'categorie' => ['nullable', 'integer', 'min:1', 'exists:categories,id'],
            'type' => ['nullable', 'string', 'max:50'],
            'pieds' => ['nullable', 'string', 'max:50'],
            'type_pied' => ['nullable', 'string', 'max:50'],
            'q' => ['nullable', 'string', 'max:120'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $query=Container::with('category')->where('statut','disponible');
        if(!empty($filters['categorie'])) $query->where('categorie_id',(int) $filters['categorie']);
        if(!empty($filters['type'])) $query->where('type_conteneur',$filters['type']);
        if(!empty($filters['pieds'])) $query->where('pieds',$filters['pieds']);
        if(!empty($filters['type_pied'])) $query->where('type_pied',$filters['type_pied']);
        if(!empty($filters['q'])) $query->where(function($q) use($filters){$term='%'.trim($filters['q']).'%';$q->where('reference','like',$term)->orWhere('description','like',$term)->orWhere('description_fr','like',$term)->orWhere('description_en','like',$term);});
        $categories = Category::where('statut','actif')
            ->withCount(['containers as available_count' => fn ($q) => $q->where('statut', 'disponible')])
            ->orderBy('ordre_affichage')
            ->get();
        $categoryTree = $categories->map(function ($category) {
            $products = Container::where('statut', 'disponible')
                ->where('categorie_id', $category->id)
                ->get(['pieds', 'type_pied']);

            $sizes = $products->groupBy(fn ($product) => $product->pieds ?: 'autre')
                ->map(function ($group, $size) {
                    return [
                        'label' => $size,
                        'count' => $group->count(),
                        'types' => $group->filter(fn ($product) => filled($product->type_pied))
                            ->groupBy('type_pied')
                            ->map->count()
                            ->sortKeys(),
                    ];
                })
                ->sortKeysUsing(fn ($a, $b) => ((int) $a) <=> ((int) $b));

            return ['category' => $category, 'sizes' => $sizes];
        });

        return view('public.shop.index',[
            'products'=>$query->latest()->paginate(12)->withQueryString(),
            'categories'=>$categories,
            'categoryTree'=>$categoryTree,
            'types'=>Container::where('statut','disponible')->whereNotNull('type_conteneur')->distinct()->orderBy('type_conteneur')->pluck('type_conteneur'),
            'sizes'=>Container::where('statut','disponible')->whereNotNull('pieds')->where('pieds','<>','')->distinct()->orderByRaw('CAST(pieds AS UNSIGNED)')->pluck('pieds'),
            'footTypes'=>Container::where('statut','disponible')->whereNotNull('type_pied')->where('type_pied','<>','')->distinct()->orderBy('type_pied')->pluck('type_pied'),
            'selectedCategory'=>isset($filters['categorie']) ? $categories->firstWhere('id', (int) $filters['categorie']) : null,
            'vatRate'=>(float) SystemSetting::getValue('taux_tva',20),
        ]);
    }
    public function show(Container $container)
    {
        abort_unless($container->statut==='disponible',404);
        return view('public.shop.show',['product'=>$container->load('category'),'vatRate'=>(float) SystemSetting::getValue('taux_tva',20)]);
    }
}
