@extends('layouts.app')
@section('meta_title', __('projects').' | C.E.S. Container')
@section('meta_description', __('projects_intro'))

@section('content')
<section class="page-hero bg-slideshow" style="--hero-image: url('{{ asset('images/toptainer.jpg') }}'); --hero-image-2: url('{{ asset('images/Special_equipment.jpg') }}'); --hero-image-3: url('{{ asset('images/transports.avif') }}')">
    <div class="container">
        <div class="page-hero-content text-white">
            <span class="eyebrow">{{ __('projects') }}</span>
            <h1 class="hero-title">{{ __('projects') }}</h1>
            <p class="hero-subtitle">{{ __('projects_intro') }}</p>
        </div>
    </div>
</section>

<div class="section-wave"></div>

<section class="container py-5 section-reveal">
    <div class="section-heading">
        <span class="eyebrow green">{{ __('projects') }}</span>
        <h2 class="section-title">{{ __('project_categories_title') }}</h2>
        <p class="section-subtitle">{{ __('project_categories_text') }}</p>
    </div>
    <div class="balanced-grid">
        @foreach($categories as $category)
            <article class="service-card animated-card">
                <div class="service-icon">{{ $category->containers_count }}</div>
                <h3>{{ $category->localizedName() }}</h3>
                <p>{{ $category->localizedDescription() }}</p>
                <a href="{{ route('shop', ['categorie' => $category->id]) }}" class="service-link">{{ __('discover') }}</a>
            </article>
        @endforeach
        @if($categories->count() % 2 !== 0)
            <article class="service-card cta-card animated-card">
                <div class="service-icon">+</div>
                <h3>{{ __('section_products_all') }}</h3>
                <p>{{ __('catalog_intro') }}</p>
                <a class="btn btn-success mt-auto" href="{{ route('shop') }}">{{ __('more_items') }}</a>
            </article>
        @endif
    </div>
</section>

<section class="split-showcase section-reveal" style="--section-bg: url('{{ asset('images/Container_transport.jpg') }}')">
    <div class="container">
        <div class="showcase-content">
            <span class="eyebrow">{{ __('service_support_title') }}</span>
            <h2>{{ __('service_support_title') }}</h2>
            <p>{{ __('services_intro') }}</p>
        </div>
    </div>
</section>

<section class="products-section py-5 section-reveal">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <span class="eyebrow green">{{ __('featured_products') }}</span>
                <h2 class="section-title mb-1">{{ __('featured_products') }}</h2>
                <p class="section-subtitle text-start m-0">{{ __('catalog_intro') }}</p>
            </div>
            <a class="btn btn-outline-success" href="{{ route('shop') }}">{{ __('section_products_all') }}</a>
        </div>
        <div class="balanced-grid">
            @foreach($products as $product)
                <article class="product-card animated-card">
                    <div class="product-image">
                        <img src="{{ $product->image_principale ? asset($product->image_principale) : asset('images/container_sale.jpg') }}" alt="{{ $product->reference }}">
                        <span class="product-badge">{{ __('available') }}</span>
                    </div>
                    <div class="product-content">
                        <div class="small text-muted mb-1">{{ $product->category?->localizedName() }}</div>
                        <h3>{{ $product->reference }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($product->localizedDescription()), 90) }}</p>
                        <a class="btn btn-success mt-auto" href="{{ route('shop.show', $product) }}">{{ __('view_details') }}</a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endsection
