@extends('layouts.app')
@section('meta_title', __('search').' | C.E.S. Container')
@section('meta_description', __('search_intro'))
@section('robots', 'noindex,follow')

@section('content')
<section class="page-hero" style="--hero-image: url('{{ asset('images/arriere.jpg') }}')">
    <div class="container">
        <div class="page-hero-content text-white">
            <span class="eyebrow">{{ __('search') }}</span>
            <h1 class="hero-title">{{ __('search') }}</h1>
            <p class="hero-subtitle">{{ __('search_intro') }}</p>
        </div>
    </div>
</section>

<div class="container py-5">
    <form class="filter-section mb-5" method="get" action="{{ route('search') }}">
        <div class="row g-3">
            <div class="col-md-9"><input name="q" value="{{ $term }}" class="form-control form-control-lg" placeholder="{{ __('search_placeholder') }}"></div>
            <div class="col-md-3"><button class="btn btn-success btn-lg w-100">{{ __('search_button') }}</button></div>
        </div>
    </form>

    <div class="row g-4">
        @forelse($products as $p)
            <div class="col-md-6 col-lg-4">
                <article class="product-card h-100">
                    <div class="product-image"><img src="{{ $p->image_principale ? asset($p->image_principale) : asset('images/container_sale.jpg') }}" alt="{{ $p->reference }}"></div>
                    <div class="product-content">
                        <div class="text-muted small">{{ $p->category->nom ?? '' }}</div>
                        <h3>{{ $p->reference }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($p->localizedDescription()),100) }}</p>
                        <span class="price-main">{{ number_format((float)$p->prix_vente * (1 + $vatRate / 100),2,',',' ') }} &euro;</span>
                        <small class="d-block">{{ __('tax_included') }}</small>
                        <a href="{{ route('shop.show',$p) }}" class="btn btn-success float-end">{{ __('view_details') }}</a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-info">{{ __('no_search_result', ['term' => $term]) }}</div></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</div>
@endsection
