<?php
namespace App\Http\Controllers;
use App\Models\Container;
use App\Models\SystemSetting;
use App\Services\CartService;
use Illuminate\Http\Request;
class CartController extends Controller
{
    public function index(CartService $cart){return view('public.cart.index',['items'=>$cart->items(),'totals'=>$cart->totals(),'company'=>['name'=>SystemSetting::getValue('entreprise_nom','C.E.S. Container'),'email'=>SystemSetting::getValue('entreprise_email'),'phone'=>SystemSetting::getValue('entreprise_telephone'),'address'=>SystemSetting::getValue('entreprise_adresse')]]);}
    public function add(Request $request, Container $container, CartService $cart)
    {
        $data=$request->validate(['quantity'=>'required|integer|min:1|max:100','type'=>'nullable|in:vente,location']);
        abort_unless($container->statut==='disponible',404);
        $cart->add($container->id,$data['quantity'], $data['type'] ?? 'vente');
        return redirect()->route('cart.index')->with('success','Produit ajouté au panier.');
    }
    public function update(Request $request, CartService $cart)
    {
        $data=$request->validate(['items'=>'required|array','items.*.quantity'=>'required|integer|min:1|max:100']);
        foreach($data['items'] as $id=>$row) $cart->update((int)$id,(int)$row['quantity']);
        return back()->with('success','Panier mis à jour.');
    }
    public function remove(int $id, CartService $cart){$cart->remove($id);return back();}
    public function clear(CartService $cart){$cart->clear();return back();}
}
