@extends('layouts.app')
@section('robots', 'noindex,follow')
@section('meta_title', __('quote.page_title').' | C.E.S. Container')
@section('content')
@php($checkoutItems = app(\App\Services\CartService::class)->items())
@php($hasRental = $checkoutItems->contains(fn ($item) => $item['type'] === 'location'))
<main class="container py-4 py-lg-5" style="max-width:1100px">
    <div class="mb-4"><a href="{{ route('cart.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>{{ __('quote.back_to_cart') }}</a><p class="text-success fw-bold text-uppercase small mt-3 mb-1">C.E.S. Container</p><h1 class="h2 mb-2">{{ __('request_quote') }}</h1><p class="text-muted mb-0">{{ __('quote.intro') }}</p></div>
    <div class="row g-4 align-items-start">
        <div class="col-lg-8">
            <form method="post" action="{{ route('checkout.store') }}" class="card border-0 shadow-sm">
                @csrf
                <div class="card-body p-3 p-md-4">
                    <h2 class="h5 mb-3">{{ __('quote.customer_info') }}</h2>
                    <div class="row g-3">
                        <div class="col-md-4"><label for="type_client" class="form-label">{{ __('quote.customer_type') }} *</label><select id="type_client" name="type_client" class="form-select" required><option value="particulier" @selected(old('type_client')==='particulier')>{{ __('quote.individual') }}</option><option value="entreprise" @selected(old('type_client')==='entreprise')>{{ __('quote.business') }}</option></select></div>
                        <div class="col-md-4"><label for="nom" class="form-label">{{ __('quote.last_name') }} *</label><input id="nom" name="nom" class="form-control" value="{{ old('nom') }}" autocomplete="family-name" maxlength="100" required></div>
                        <div class="col-md-4"><label for="prenom" class="form-label">{{ __('quote.first_name') }}</label><input id="prenom" name="prenom" class="form-control" value="{{ old('prenom') }}" autocomplete="given-name" maxlength="100"></div>
                        <div class="col-md-6"><label for="nom_entreprise" class="form-label">{{ __('quote.company') }}</label><input id="nom_entreprise" name="nom_entreprise" class="form-control" value="{{ old('nom_entreprise') }}" autocomplete="organization" maxlength="200"></div>
                        <div class="col-md-6"><label for="email" class="form-label">{{ __('quote.email') }} *</label><input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="email" maxlength="255" required></div>
                        <div class="col-md-6"><label for="telephone" class="form-label">{{ __('quote.phone') }}</label><input id="telephone" type="tel" name="telephone" class="form-control" value="{{ old('telephone') }}" autocomplete="tel" maxlength="30"></div>
                        <div class="col-12"><label for="adresse" class="form-label">{{ __('quote.delivery_address') }} *</label><textarea id="adresse" name="adresse" class="form-control" rows="2" autocomplete="street-address" maxlength="1000" required>{{ old('adresse') }}</textarea></div>
                        <div class="col-md-5"><label for="ville" class="form-label">{{ __('quote.city') }} *</label><input id="ville" name="ville" class="form-control" value="{{ old('ville') }}" autocomplete="address-level2" maxlength="100" required></div>
                        <div class="col-md-3"><label for="code_postal" class="form-label">{{ __('quote.postal_code') }} *</label><input id="code_postal" name="code_postal" class="form-control" value="{{ old('code_postal') }}" autocomplete="postal-code" maxlength="30" required></div>
                        <div class="col-md-4"><label for="pays" class="form-label">{{ __('quote.country') }} *</label><input id="pays" name="pays" class="form-control" value="{{ old('pays', __('quote.default_country')) }}" autocomplete="country-name" maxlength="100" required></div>
                        <div class="col-md-6"><label for="numero_tva" class="form-label">{{ __('quote.vat_number') }}</label><input id="numero_tva" name="numero_tva" class="form-control" value="{{ old('numero_tva') }}" maxlength="50"></div>
                        @if($hasRental)<div class="col-md-6"><label for="duree_location_jours" class="form-label">{{ __('quote.rental_duration') }}</label><input id="duree_location_jours" type="number" name="duree_location_jours" class="form-control" value="{{ old('duree_location_jours') }}" min="1" max="3650"></div>@endif
                        <div class="col-12"><label for="notes" class="form-label">{{ __('quote.notes') }}</label><textarea id="notes" name="notes" class="form-control" rows="4" maxlength="3000" placeholder="{{ __('quote.notes_placeholder') }}">{{ old('notes') }}</textarea></div>
                    </div>
                </div>
                <div class="card-footer bg-white p-3 p-md-4">
                    <div class="alert alert-info mb-3"><strong><i class="bi bi-info-circle me-1"></i>{{ __('quote.nonbinding_title') }}</strong><br>{{ __('quote.nonbinding_text') }}</div>
                    <button class="btn btn-success btn-lg w-100"><i class="bi bi-send me-2"></i>{{ __('quote.send_request') }}</button>
                </div>
            </form>
        </div>
        <aside class="col-lg-4"><div class="card border-0 shadow-sm"><div class="card-header bg-white py-3"><h2 class="h5 mb-0"><i class="bi bi-box-seam text-success me-2"></i>{{ __('quote.selection') }}</h2></div><div class="card-body">@foreach($checkoutItems as $item)<div class="d-flex justify-content-between gap-3 py-2 {{ !$loop->last?'border-bottom':'' }}"><div><strong>{{ $item['container']->reference }}</strong><div class="small text-muted">{{ $item['type']==='location'?__('quote.rental'):__('quote.purchase') }} · {{ __('quote.quantity') }} {{ $item['quantity'] }}</div></div><span class="text-nowrap">{{ $item['unit_price'] > 0 ? number_format($item['unit_price'],2,',',' ').' €'.($item['type']==='location'?' '.__('quote.per_day'):'') : __('quote.quote_only') }}</span></div>@endforeach<div class="small text-muted mt-3">{{ __('quote.indicative_price_note') }}</div></div></div></aside>
    </div>
</main>
@endsection
