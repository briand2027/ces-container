<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query=Client::latest();
        if($request->filled('search')){$term='%'.$request->string('search')->toString().'%';$query->where(fn($q)=>$q->where('nom','like',$term)->orWhere('prenom','like',$term)->orWhere('nom_entreprise','like',$term)->orWhere('email','like',$term)->orWhere('telephone','like',$term));}
        if($request->filled('type_client'))$query->where('type_client',$request->string('type_client'));
        return view('admin.clients.index',['items'=>$query->paginate(20)->withQueryString(),'stats'=>['total'=>Client::count(),'particuliers'=>Client::where('type_client','particulier')->count(),'entreprises'=>Client::where('type_client','entreprise')->count(),'commandes'=>\App\Models\Order::count()]]);
    }
    public function create(){return view('admin.clients.form',['item'=>new Client]);}
    public function store(Request $request){Client::create($this->validated($request));return redirect()->route('admin.clients.index')->with('success','Client créé.');}
    public function show(Client $client){return view('admin.clients.show',['client'=>$client->load(['orders'=>fn($q)=>$q->latest()])]);}
    public function edit(Client $client){return view('admin.clients.form',['item'=>$client]);}
    public function update(Request $request,Client $client){$client->update($this->validated($request,$client));return redirect()->route('admin.clients.show',$client)->with('success','Fiche client mise à jour.');}
    public function destroy(Client $client)
    {
        if($client->orders()->exists())return back()->withErrors(['client'=>'Ce client a des commandes associées et ne peut pas être supprimé.']);
        $client->delete();return redirect()->route('admin.clients.index')->with('success','Client supprimé.');
    }
    private function validated(Request $request,?Client $client=null):array
    {
        return $request->validate(['type_client'=>'required|in:particulier,entreprise','nom'=>'required|string|max:100','prenom'=>'nullable|string|max:100','nom_entreprise'=>'nullable|string|max:200','email'=>['required','email','max:255',Rule::unique('clients','email')->ignore($client?->id)],'telephone'=>'nullable|string|max:20','adresse'=>'nullable|string|max:1000','ville'=>'nullable|string|max:100','code_postal'=>'nullable|string|max:20','pays'=>'nullable|string|max:100','numero_tva'=>'nullable|string|max:50','notes'=>'nullable|string|max:5000']);
    }
}
