<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{HomeController,CatalogController,CartController,CheckoutController,ContactController,PageController,SearchController};
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\MerchantFeedController;
Route::get('/langue/{locale}', function (string $locale) {
    abort_unless(array_key_exists($locale, config('locales.available')), 404);
    session(['locale' => $locale]);
    return back();
})->name('locale.switch');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/google-merchant.xml', [MerchantFeedController::class, 'index'])->name('merchant.feed');
Route::redirect('/', '/boutique');
Route::get('/accueil',[HomeController::class,'index'])->name('home');
Route::get('/boutique',[CatalogController::class,'index'])->name('shop');
Route::get('/recherche',SearchController::class)->name('search');
Route::get('/conteneurs/{container}',[CatalogController::class,'show'])->name('shop.show');
Route::post('/conteneurs/{container}/panier',[CartController::class,'add'])->name('cart.add');
Route::get('/panier',[CartController::class,'index'])->name('cart.index');
Route::post('/panier',[CartController::class,'update'])->name('cart.update');
Route::delete('/panier/{id}',[CartController::class,'remove'])->name('cart.remove');
Route::delete('/panier',[CartController::class,'clear'])->name('cart.clear');
Route::get('/commande',function(\App\Services\CartService $cart){ abort_if($cart->items()->isEmpty(),404); return view('public.cart.checkout'); })->name('checkout.form');
Route::post('/commande',[CheckoutController::class,'store'])->name('checkout.store');
Route::get('/commande/{order}/succes',[CheckoutController::class,'success'])->name('checkout.success');
Route::get('/services',[PageController::class,'services'])->name('services');Route::get('/projets',[PageController::class,'projects'])->name('projects');Route::get('/a-propos',[PageController::class,'about'])->name('about');Route::get('/galerie',[PageController::class,'gallery'])->name('gallery');
Route::get('/contact',[ContactController::class,'create'])->name('contact');Route::post('/contact',[ContactController::class,'store'])->name('contact.store');
require __DIR__.'/admin.php';
