@extends('layouts.app')
@section('meta_title', __('about_title').' | C.E.S. Container')
@section('meta_description', __('about_intro'))
@push('head')<link rel="preload" as="image" href="{{ asset('images/optimized/ccc.webp') }}" fetchpriority="high">@endpush

@section('content')
<main id="main-content">
<section class="page-hero bg-slideshow" data-hero-image-2="{{ asset('images/optimized/images_co.webp') }}" data-hero-image-3="{{ asset('images/optimized/toptainer.webp') }}" style="--hero-image: url('{{ asset('images/optimized/ccc.webp') }}')">
    <div class="container">
        <div class="page-hero-content text-white">
            <span class="eyebrow">{{ __('about') }}</span>
            <h1 class="hero-title">{{ __('about_title') }}</h1>
            <p class="hero-subtitle">{{ __('about_intro') }}</p>
        </div>
    </div>
</section>

<section class="container py-5 section-reveal">
    <div class="about-grid">
        <div class="content-panel animated-card">
            <span class="eyebrow green">{{ __('about') }}</span>
            <h2>{{ __('why_choose_us') }}</h2>
            <p class="lead">{{ __('about_intro') }}</p>
            <p>{{ __('about_text') }}</p>
            <p>{{ __('why_choose_us_text') }}</p>
        </div>
        <div class="stats-panel animated-card">
            <div><strong>{{ $productsCount }}</strong><span>{{ __('visible_products') }}</span></div>
            <div><strong>{{ $categories->count() }}</strong><span>{{ __('categories') }}</span></div>
            <div><strong>{{ $services->count() }}+</strong><span>{{ __('services') }}</span></div>
        </div>
    </div>
</section>

<section class="products-section py-5 section-reveal">
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow green">{{ __('categories') }}</span>
            <h2 class="section-title">{{ __('project_categories_title') }}</h2>
            <p class="section-subtitle">{{ __('project_categories_text') }}</p>
        </div>
        <div class="balanced-grid">
            @foreach($categories as $category)
                <article class="service-card animated-card">
                    <div class="service-icon">{{ $category->containers_count }}</div>
                    <h3>{{ $category->localizedName() }}</h3>
                    <p>{{ $category->localizedDescription() }}</p>
                    <a class="service-link" href="{{ route('shop', ['categorie' => $category->id]) }}">{{ __('discover') }}</a>
                </article>
            @endforeach
        </div>
    </div>
</section>
</main>
@endsection
