@extends('layouts.app')
@section('meta_title', $product->reference.' | C.E.S. Container')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->localizedDescription() ?: $product->reference), 155))
@section('og_type', 'product')
@section('og_image', $product->image_principale ? asset($product->image_principale) : asset('images/image_logo.jpeg'))

@section('content')
@php
    $images = collect([$product->image_principale])->merge($product->images_secondaires ?? [])->filter()->values();
    $condition = match (mb_strtolower(trim((string) $product->etat))) {
        'neuf', 'nuovo', 'nuova', 'new' => 'https://schema.org/NewCondition',
        'occasion', 'usato', 'usata', 'used', 'seconda mano' => 'https://schema.org/UsedCondition',
        'reconditionne', 'reconditionné', 'ricondizionato', 'ricondizionata', 'refurbished' => 'https://schema.org/RefurbishedCondition',
        default => null,
    };
    $productJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => trim($product->reference.' '.($product->category->nom ?? '')),
        'description' => strip_tags((string) $product->localizedDescription()),
        'sku' => (string) $product->reference,
        'image' => $images->map(fn ($image) => asset($image))->all(),
        'offers' => $product->prix_vente > 0 ? [
            '@type' => 'Offer',
            'url' => route('shop.show', $product),
            'priceCurrency' => 'EUR',
            'price' => number_format((float) $product->prix_vente * (1 + $vatRate / 100), 2, '.', ''),
            'availability' => $product->quantite_stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ...($condition ? ['itemCondition' => $condition] : []),
        ] : null,
    ];
@endphp
@push('structured-data')<script type="application/ld+json">{!! json_encode($productJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>@endpush
@push('head')
    @if($images->isNotEmpty())<link rel="preload" as="image" href="{{ asset($images->first()) }}" fetchpriority="high">@endif
@endpush

<main id="main-content">
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
                @php($mainImage = $images->isNotEmpty() ? asset($images->first()) : asset('images/optimized/container_sale.webp'))
                <div class="product-gallery" data-product-gallery>
                    <div class="product-gallery-stage">
                        <img src="{{ $mainImage }}" class="main-image" alt="{{ $product->reference }}" width="1200" height="900" fetchpriority="high" decoding="async" data-gallery-main>
                        @if($images->count() > 1)
                            <button class="gallery-nav gallery-nav-previous" type="button" aria-label="{{ __('previous') }}" data-gallery-step="-1"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                            <button class="gallery-nav gallery-nav-next" type="button" aria-label="{{ __('next') }}" data-gallery-step="1"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                        @endif
                    </div>
                @if($images->count() > 1)
                    <div class="thumbnail-rail">
                        <div class="thumbnail-container" role="group" aria-label="{{ __('gallery') }}" tabindex="0" data-gallery-thumbnails>
                        @foreach($images as $img)
                            <button class="thumbnail-button {{ $loop->first ? 'is-active' : '' }}" type="button" aria-label="{{ $product->reference }} - {{ __('gallery') }} {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" data-gallery-image="{{ asset($img) }}">
                                <img src="{{ asset($img) }}" class="thumbnail" alt="" width="240" height="180" loading="lazy" decoding="async">
                            </button>
                        @endforeach
                        </div>
                    </div>
                @endif
                </div>
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
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('click', (event) => {
    const gallery = event.target.closest('[data-product-gallery]');
    if (!gallery) return;

    const mainImage = gallery.querySelector('[data-gallery-main]');
    const thumbnails = [...gallery.querySelectorAll('[data-gallery-image]')];
    if (!mainImage || thumbnails.length < 2) return;

    const stepButton = event.target.closest('[data-gallery-step]');
    const thumbnailButton = event.target.closest('[data-gallery-image]');
    let target = thumbnailButton;

    if (stepButton) {
        const currentIndex = Math.max(0, thumbnails.findIndex((button) => button.dataset.galleryImage === mainImage.src));
        const nextIndex = (currentIndex + Number(stepButton.dataset.galleryStep) + thumbnails.length) % thumbnails.length;
        target = thumbnails[nextIndex];
    }

    if (!target) return;
    mainImage.src = target.dataset.galleryImage;
    thumbnails.forEach((button) => {
        const selected = button === target;
        button.classList.toggle('is-active', selected);
        button.setAttribute('aria-pressed', String(selected));
    });
    target.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
});
</script>
@endpush
