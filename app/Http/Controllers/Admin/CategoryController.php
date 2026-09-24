<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query=Category::withCount('containers')->orderBy('ordre_affichage')->orderBy('nom');
        if($request->filled('search'))$query->where('nom','like','%'.$request->string('search')->toString().'%');
        if($request->filled('statut'))$query->where('statut',$request->string('statut'));
        return view('admin.categories.index',['items'=>$query->paginate(20)->withQueryString(),'stats'=>['total'=>Category::count(),'actives'=>Category::where('statut','actif')->count(),'inactives'=>Category::where('statut','inactif')->count(),'products'=>Category::has('containers')->count()]]);
    }
    public function create(){return view('admin.categories.form',['item'=>new Category]);}
    public function store(Request $request)
    {
        $data=$this->validated($request);$data['image_url']=$this->storeImage($request);Category::create($data);
        return redirect()->route('admin.categories.index')->with('success','Catégorie créée.');
    }
    public function edit(Category $category){return view('admin.categories.form',['item'=>$category]);}
    public function update(Request $request,Category $category)
    {
        $data=$this->validated($request,$category);$uploaded=$this->storeImage($request);
        if($uploaded){$this->deleteImage($category->image_url);$data['image_url']=$uploaded;}
        elseif($request->boolean('delete_image')){$this->deleteImage($category->image_url);$data['image_url']=null;}
        $category->update($data);return redirect()->route('admin.categories.index')->with('success','Catégorie mise à jour.');
    }
    public function toggleStatus(Category $category)
    {
        $category->update(['statut'=>$category->statut==='actif'?'inactif':'actif']);
        return back()->with('success','Statut de la catégorie modifié.');
    }
    public function destroy(Category $category)
    {
        if($category->containers()->exists())return back()->withErrors(['category'=>'Cette catégorie contient des articles. Désactivez-la au lieu de la supprimer.']);
        $this->deleteImage($category->image_url);$category->delete();return back()->with('success','Catégorie supprimée.');
    }
    private function validated(Request $request,?Category $category=null):array
    {
        $data=$request->validate(['nom'=>['required','string','max:100',\Illuminate\Validation\Rule::unique('categories','nom')->ignore($category?->id)],'description'=>'nullable|string','ordre_affichage'=>'required|integer|min:0','statut'=>'required|in:actif,inactif','image'=>'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048','delete_image'=>'nullable|boolean']);
        unset($data['image'],$data['delete_image']);return $data;
    }
    private function storeImage(Request $request):?string
    {
        return $request->hasFile('image')?'storage/'.$request->file('image')->store('categories','public'):null;
    }
    private function deleteImage(?string $path):void
    {
        if($path&&str_starts_with($path,'storage/'))Storage::disk('public')->delete(substr($path,8));
    }
}
