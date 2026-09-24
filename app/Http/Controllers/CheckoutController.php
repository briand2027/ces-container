<?php
namespace App\Http\Controllers;

use App\Mail\OrderCreatedMail;
use App\Models\SystemSetting;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function store(Request $request, CartService $cart, OrderService $orders)
    {
        $data=$request->validate([
            'type_client'=>'required|in:particulier,entreprise','nom'=>'required|string|max:100','prenom'=>'required|string|max:100',
            'nom_entreprise'=>'nullable|string|max:200','email'=>'required|email|max:255','telephone'=>'nullable|string|max:20',
            'adresse'=>'required|string|max:500','ville'=>'required|string|max:100','code_postal'=>'required|string|max:20','pays'=>'required|string|max:100',
            'numero_tva'=>'nullable|string|max:50','notes'=>'nullable|string|max:2000','duree_location_jours'=>'nullable|integer|min:1|max:3650',
        ]);
        try { $order=$orders->create($data,$cart); } catch (\RuntimeException $e) { return back()->withErrors(['cart'=>$e->getMessage()])->withInput(); }
        $cart->clear();
        $notification=SystemSetting::getValue('email_notification_commande');
        try {
            if ($notification) Mail::to($notification)->send(new OrderCreatedMail($order));
            Mail::to($order->client->email)->send(new OrderCreatedMail($order, true));
        } catch (\Throwable $e) {
            Log::error('Commande créée mais notification email échouée', ['order_id'=>$order->id,'error'=>$e->getMessage()]);
        }
        return redirect()->route('checkout.success',$order)->with('order_created',true);
    }

    public function success(\App\Models\Order $order)
    {
        abort_unless(session('order_created') && session('order_created') === true, 403);
        $bank=[
            'banque_nom'=>SystemSetting::getValue('banque_nom',''), 'banque_titulaire'=>SystemSetting::getValue('banque_titulaire',''),
            'banque_iban'=>SystemSetting::getValue('banque_iban',''), 'banque_bic'=>SystemSetting::getValue('banque_bic',''),
            'banque_adresse'=>SystemSetting::getValue('banque_adresse',''), 'banque_instructions'=>SystemSetting::getValue('banque_instructions',''),
        ];
        $proofEmail=SystemSetting::getValue('entreprise_email','contact@containerequipmentservices.lt');
        return view('public.cart.success',['order'=>$order->load('client','lines.container'),'bank'=>$bank,'proofEmail'=>$proofEmail]);
    }
}
