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
        $query=Container::with('category')->where('statut','disponible');
        if($request->filled('categorie')) $query->where('categorie_id',$request->integer('categorie'));
        if($request->filled('type')) $query->where('type_conteneur',$request->string('type'));
        if($request->filled('pieds')) $query->where('pieds',$request->string('pieds'));
        if($request->filled('type_pied')) $query->where('type_pied',$request->string('type_pied'));
        if($request->filled('q')) $query->where(function($q) use($request){$term='%'.$request->string('q').'%';$q->where('reference','like',$term)->orWhere('description','like',$term)->orWhere('description_fr','like',$term)->orWhere('description_en','like',$term);});
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
            'selectedCategory'=>$categories->firstWhere('id', (int) $request->input('categorie')),
            'vatRate'=>(float) SystemSetting::getValue('taux_tva',20),
        ]);
    }
    public function show(Container $container)
    {
        abort_unless($container->statut==='disponible',404);
        return view('public.shop.show',['product'=>$container->load('category'),'vatRate'=>(float) SystemSetting::getValue('taux_tva',20)]);
    }
}
