<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
class OrderController extends Controller
{
    public function index(Request $request){$q=Order::with('client')->latest(); if($request->filled('statut_commande'))$q->where('statut_commande',$request->statut_commande); if($request->filled('statut_paiement'))$q->where('statut_paiement',$request->statut_paiement); if($request->filled('search'))$q->where(function($w)use($request){$term='%'.$request->string('search')->toString().'%';$w->where('numero_commande','like',$term)->orWhereHas('client',fn($c)=>$c->where('email','like',$term)->orWhere('nom','like',$term)->orWhere('prenom','like',$term)->orWhere('nom_entreprise','like',$term));}); return view('admin.orders.index',['orders'=>$q->paginate(20)->withQueryString(),'stats'=>['total'=>Order::count(),'pending'=>Order::where('statut_commande','en_attente')->count(),'processing'=>Order::whereIn('statut_commande',['confirmee','en_preparation'])->count(),'unpaid'=>Order::whereIn('statut_paiement',['en_attente','partiel'])->count()]]);}
    public function show(Order $order){return view('admin.orders.show',['order'=>$order->load('client','lines.container','payments')]);}
    public function markPaid(Request $request, Order $order, OrderService $service){$data=$request->validate(['reference'=>'nullable|string|max:100']);$service->markPaid($order,$data['reference']??null);return back()->with('success','Paiement marqué comme reçu et commande confirmée.');}
    public function status(Request $request, Order $order, OrderService $service){$data=$request->validate(['statut_commande'=>'required|in:en_attente,confirmee,en_preparation,expediee,livree,annulee']);try{$service->updateStatus($order,$data['statut_commande']);}catch(\RuntimeException $e){return back()->withErrors(['statut_commande'=>$e->getMessage()]);}return back()->with('success','Statut de commande mis à jour.');}
}
