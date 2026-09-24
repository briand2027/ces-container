<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Container;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
    public function index(Request $request){
        $query = Container::with('category')->latest();
        if ($request->filled('search')) { $term = '%'.$request->string('search')->toString().'%'; $query->where(fn($q) => $q->where('reference','like',$term)->orWhere('type_conteneur','like',$term)->orWhere('etat','like',$term)); }
        if ($request->filled('categorie')) $query->where('categorie_id',$request->integer('categorie'));
        if ($request->filled('statut')) $query->where('statut',$request->string('statut'));
        return view('admin.containers.index', ['items'=>$query->paginate(20)->withQueryString(), 'categories'=>Category::where('statut','actif')->orderBy('ordre_affichage')->get(), 'stats'=>['total'=>Container::count(),'disponibles'=>Container::where('statut','disponible')->count(),'alerte'=>Container::whereColumn('quantite_stock','<=','quantite_min_alerte')->count(),'maintenance'=>Container::where('statut','maintenance')->count()]]);
    }
    public function gallery(Request $request){
        $query = Container::with('category')->whereNotNull('image_principale')->where('image_principale','!=','')->latest();
        if ($request->filled('search')) { $term='%'.$request->string('search')->toString().'%'; $query->where(fn($q)=>$q->where('reference','like',$term)->orWhere('type_conteneur','like',$term)); }
        if ($request->filled('categorie')) $query->where('categorie_id',$request->integer('categorie'));
        return view('admin.gallery.index', ['items'=>$query->paginate(24)->withQueryString(),'categories'=>Category::orderBy('ordre_affichage')->get(),'total'=>Container::whereNotNull('image_principale')->where('image_principale','!=','')->count()]);
    }
    public function create(){
        return view('admin.containers.form',['item'=>new Container,'categories'=>Category::where('statut','actif')->orderBy('ordre_affichage')->get()]);
    }
    public function store(Request $r, ImageUploadService $images){
        $data=$this->data($r,true);
        $data=$this->images($r,$data,$images);
        Container::create($data);
        return redirect()->route('admin.conteneurs.index')->with('success','Conteneur créé.');
    }
    public function edit(Container $container){
        return view('admin.containers.form',['item'=>$container,'categories'=>Category::where('statut','actif')->orderBy('ordre_affichage')->get()]);
    }
    public function update(Request $r, Container $container, ImageUploadService $images){
        $data=$this->data($r);
        $data=$this->images($r,$data,$images,$container);
        $container->update($data);
        return back()->with('success','Conteneur mis à jour.');
    }
    public function destroy(Container $container, ImageUploadService $images){
        $images->delete($container->image_principale);
        foreach ((array)$container->images_secondaires as $path) $images->delete($path);
        $container->delete();
        return back()->with('success','Conteneur supprimé.');
    }
    public function bulkDestroy(Request $request, ImageUploadService $images){
        $ids = collect($request->input('selected_ids', []))->filter(fn($id) => ctype_digit((string)$id))->map(fn($id)=>(int)$id)->values();
        if ($ids->isEmpty()) return back()->with('error','Sélectionnez au moins un conteneur.');
        $items = Container::whereIn('id',$ids)->get();
        foreach ($items as $item) { $images->delete($item->image_principale); foreach ((array)$item->images_secondaires as $path) $images->delete($path); $item->delete(); }
        return back()->with('success',$items->count().' conteneur(s) supprimé(s).');
    }
    private function data(Request $r, bool $creating=false): array {
        $data = $r->validate([
            'reference'=>'required|string|max:50', 'categorie_id'=>'required|integer|exists:categories,id',
            'type_conteneur'=>'required|string|max:50', 'dimensions'=>'nullable|string|max:255',
            'pieds'=>'nullable|string|max:50', 'type_pied'=>'nullable|string|max:50',
            'dimension_pieds'=>'nullable|string|max:50', 'dimension_metres'=>'nullable|string|max:50',
            'longueur'=>'nullable|string|max:100', 'largeur'=>'nullable|string|max:100', 'hauteur'=>'nullable|string|max:100',
            'poids'=>'nullable|string|max:100', 'capacite'=>'nullable|string|max:100',
            'prix_achat'=>'nullable|numeric|min:0', 'prix_vente'=>($creating?'required':'nullable').'|numeric|min:0', 'prix_location_jour'=>'nullable|numeric|min:0',
            'quantite_stock'=>'required|integer|min:0', 'quantite_min_alerte'=>'nullable|integer|min:0',
            'etat'=>'nullable|string|max:100', 'annee_fabrication'=>'nullable|integer|min:1900|max:2100',
            'certificat_csc'=>'nullable|boolean', 'image_principale'=>($creating?'required':'nullable').'|file|mimes:jpg,jpeg,png,webp,avif|max:5120', 'images_secondaires'=>($creating?'required|array|min:1':'nullable|array').'|max:4', 'images_secondaires.*'=>'file|mimes:jpg,jpeg,png,webp,avif|max:5120',
            'description'=>'required|string', 'description_en'=>'nullable|string', 'description_fr'=>'nullable|string', 'description_es'=>'nullable|string',
            'description_de'=>'nullable|string', 'description_nl'=>'nullable|string', 'description_fi'=>'nullable|string', 'description_ch'=>'nullable|string',
            'caracteristiques'=>'nullable|array', 'caracteristiques.*'=>'nullable|string|max:255', 'statut'=>'required|in:disponible,vendu,loue,maintenance',
        ]);
        // The legacy form submitted an empty string for this NOT NULL column.
        $data['type_pied'] = $data['type_pied'] ?? '';
        return $data;
    }
    private function images(Request $r,array $data,ImageUploadService $images,?Container $existing=null): array {
        $type=$data['type_conteneur'] ?? $existing?->type_conteneur ?? 'standard';
        if ($r->hasFile('image_principale')) {
            if ($existing) $images->delete($existing->image_principale);
            $data['image_principale']=$images->storePrincipal($r->file('image_principale'),$type);
        }
        if ($r->hasFile('images_secondaires')) {
            if ($existing) foreach ((array)$existing->images_secondaires as $path) $images->delete($path);
            $data['images_secondaires']=$images->storeSecondaries($r->file('images_secondaires'),$type);
        }
        if (isset($data['caracteristiques'])) $data['caracteristiques'] = array_filter($data['caracteristiques'], fn($value) => filled($value));
        return $data;
    }
}
