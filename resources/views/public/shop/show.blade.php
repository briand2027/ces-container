@extends('layouts.app')
@section('meta_title', $product->reference.' | C.E.S. Container')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->localizedDescription() ?: $product->reference), 155))
@section('og_type', 'product')
@section('og_image', $product->image_principale ? asset($product->image_principale) : asset('images/image_logo.jpeg'))

@section('content')
@php
    $images = collect([$product->image_principale])->merge($product->images_secondaires ?? [])->filter()->values();
    $productJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => trim($product->reference.' '.($product->category->nom ?? '')),
        'description' => strip_tags((string) $product->localizedDescription()),
        'sku' => (string) $product->reference,
        'image' => $images->map(fn ($image) => asset($image))->all(),
        'brand' => ['@type' => 'Brand', 'name' => 'C.E.S. Container'],
        'offers' => $product->prix_vente > 0 ? [
            '@type' => 'Offer',
            'url' => route('shop.show', $product),
            'priceCurrency' => 'EUR',
            'price' => number_format((float) $product->prix_vente * (1 + $vatRate / 100), 2, '.', ''),
            'availability' => $product->quantite_stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'itemCondition' => in_array(mb_strtolower((string) $product->etat), ['neuf', 'nuovo', 'new'], true) ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition',
        ] : null,
    ];
@endphp
@push('structured-data')<script type="application/ld+json">{!! json_encode($productJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>@endpush

<section class="page-hero compact-hero" style="--hero-image: url('{{ $product->image_principale ? asset($product->image_principale) : asset('images/container_sale.jpg') }}')">
    <div class="container">
        <div class="page-hero-content text-white">
            <span class="eyebrow">{{ $product->category->nom ?? __('shop') }}</span>
            <h1 class="hero-title">{{ $product->reference }}</h1>
            <p class="hero-subtitle">{{ \Illuminate\Support\Str::limit(strip_tags($product->localizedDescription()), 160) }}</p>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-6">
            <div class="product-detail-media">
                <img src="{{ $images->isNotEmpty() ? asset($images->first()) : asset('images/container_sale.jpg') }}" class="main-image" alt="{{ $product->reference }}">
                @if($images->count() > 1)
                    <div class="thumbnail-container">
                        @foreach($images as $img)
                            <img src="{{ asset($img) }}" class="thumbnail" alt="{{ $product->reference }}">
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="content-panel product-detail-panel">
                <p class="text-success fw-semibold">{{ $product->category->nom ?? '' }}</p>
                <h2>{{ $product->reference }}</h2>
                <p>{!! nl2br(e($product->localizedDescription())) !!}</p>
                <div class="product-price detail-price">
                    @if($product->prix_vente)<span class="price-main">{{ number_format((float)$product->prix_vente * (1 + $vatRate / 100),2,',',' ') }} &euro;</span><small class="d-block">{{ __('tax_included') }}</small>@endif
                    @if($product->prix_location_jour)<span class="price-rent">{{ number_format((float)$product->prix_location_jour * (1 + $vatRate / 100),2,',',' ') }} &euro; / {{ __('per_day') }}</span>@endif
                    <p class="small mt-2 mb-0">{{ __('available') }}: {{ $product->quantite_stock }} {{ __('stock') }}</p>
                </div>

                <form method="post" action="{{ route('cart.add',$product) }}" class="cart-form">
                    @csrf
                    <input type="number" name="quantity" value="1" min="1" max="{{ max(1,(int)$product->quantite_stock) }}" class="form-control">
                    <select name="type" class="form-select">
                        <option value="vente">{{ __('buy') }}</option>
                        <option value="location">{{ __('rent') }}</option>
                    </select>
                    <button class="btn btn-success">{{ __('add_to_cart') }}</button>
                </form>

                <dl class="spec-list">
                    @if($product->dimensions)<dt>{{ __('dimensions') }}</dt><dd>{{ $product->dimensions }}</dd>@endif
                    @if($product->pieds)<dt>{{ __('dimensions') }}</dt><dd>{{ $product->pieds }}</dd>@endif
                    @if($product->type_pied)<dt>{{ __('type') }}</dt><dd>{{ $product->type_pied }}</dd>@endif
                    @if($product->poids)<dt>{{ __('weight') }}</dt><dd>{{ $product->poids }}</dd>@endif
                    @if($product->capacite)<dt>{{ __('capacity') }}</dt><dd>{{ $product->capacite }}</dd>@endif
                    @if($product->annee_fabrication)<dt>{{ __('year') }}</dt><dd>{{ $product->annee_fabrication }}</dd>@endif
                    @if($product->type_conteneur)<dt>{{ __('type') }}</dt><dd>{{ ucfirst(str_replace('_', ' ', $product->type_conteneur)) }}</dd>@endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
