<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\SystemSetting;
use Illuminate\Http\Response;

class MerchantFeedController extends Controller
{
    public function index(): Response
    {
        $products = Container::query()
            ->with('category')
            ->where('statut', 'disponible')
            ->where('quantite_stock', '>', 0)
            ->where('prix_vente', '>', 0)
            ->whereNotNull('image_principale')
            ->orderBy('id')
            ->get();

        return response()
            ->view('seo.merchant-feed', ['products'=>$products,'vatRate'=>(float) SystemSetting::getValue('taux_tva',20)])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
