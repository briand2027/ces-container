<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderLine;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query=Service::latest();
        if($request->filled('search'))$query->where(fn($q)=>$q->where('nom','like','%'.$request->string('search')->toString().'%')->orWhere('type_service','like','%'.$request->string('search')->toString().'%'));
        if($request->filled('statut'))$query->where('statut',$request->string('statut'));
        return view('admin.services.index',['items'=>$query->paginate(20)->withQueryString(),'stats'=>['total'=>Service::count(),'actifs'=>Service::where('statut','actif')->count(),'inactifs'=>Service::where('statut','inactif')->count(),'tarifes'=>Service::whereNotNull('prix_unitaire')->count()]]);
    }
    public function create(){return view('admin.services.form',['item'=>new Service]);}
    public function store(Request $request){$data=$this->validated($request,true);$data['image_url']=$this->storeImage($request);Service::create($data);return redirect()->route('admin.services.index')->with('success','Service créé.');}
    public function edit(Service $service){return view('admin.services.form',['item'=>$service]);}
    public function update(Request $request,Service $service){$data=$this->validated($request);$image=$this->storeImage($request);if($image){$this->deleteImage($service->image_url);$data['image_url']=$image;}elseif($request->boolean('delete_image')){$this->deleteImage($service->image_url);$data['image_url']=null;}$service->update($data);return redirect()->route('admin.services.index')->with('success','Service mis à jour.');}
    public function destroy(Service $service)
    {
        if(OrderLine::where('service_id',$service->id)->exists()){$service->update(['statut'=>'inactif']);return back()->with('success','Le service figure dans des commandes : il a été désactivé pour préserver leur historique.');}
        $this->deleteImage($service->image_url);$service->delete();return back()->with('success','Service supprimé.');
    }
    private function validated(Request $request,bool $creating=false):array
    {
        $data=$request->validate(['nom'=>'required|string|max:100','type_service'=>'required|in:trasporto,installazione,manutenzione,Noleggio,autre','description'=>'nullable|string','prix_unitaire'=>($creating?'required':'nullable').'|numeric|min:0','unite_mesure'=>'nullable|string|max:20','duree_estimee'=>'nullable|integer|min:0','conditions'=>'nullable|string','statut'=>'required|in:actif,inactif','image'=>'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048','delete_image'=>'nullable|boolean']);
        unset($data['image'],$data['delete_image']);return $data;
    }
    private function storeImage(Request $request):?string{return $request->hasFile('image')?'storage/'.$request->file('image')->store('services','public'):null;}
    private function deleteImage(?string $path):void{if($path&&str_starts_with($path,'storage/'))Storage::disk('public')->delete(substr($path,8));}
}
