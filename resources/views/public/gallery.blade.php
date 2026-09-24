@extends('layouts.app')
@section('meta_title', __('gallery').' | C.E.S. Container')
@section('meta_description', __('gallery_intro'))

@section('content')
@php
    $paginationStart = $items->firstItem() ?? 0;
    $paginationEnd = $items->lastItem() ?? 0;
    $currentPage = $items->currentPage();
    $lastPage = $items->lastPage();
    $windowStart = max(1, $currentPage - 2);
    $windowEnd = min($lastPage, $currentPage + 2);
@endphp
<section class="page-hero bg-slideshow" style="--hero-image: url('{{ asset('images/centre.png') }}'); --hero-image-2: url('{{ asset('images/novobox.png') }}'); --hero-image-3: url('{{ asset('images/bureau.png') }}')">
    <div class="container">
        <div class="page-hero-content text-white">
            <span class="eyebrow">{{ __('gallery') }}</span>
            <h1 class="hero-title">{{ __('gallery') }}</h1>
            <p class="hero-subtitle">{{ __('gallery_intro') }}</p>
        </div>
    </div>
</section>

<div class="section-wave"></div>

<main class="container py-5 section-reveal">
    <form class="filter-section mb-4" method="get" action="{{ route('gallery') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">{{ __('gallery_filters') }}</label>
                <select name="categorie" class="form-select">
                    <option value="">{{ __('all_gallery') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('categorie') === (string) $category->id)>{{ $category->localizedName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('type') }}</label>
                <select name="type" class="form-select">
                    <option value="">{{ __('all_types') }}</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected((string) request('type') === (string) $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-success w-100">{{ __('filter') }}</button>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h2 class="catalog-title mb-0">{{ __('all_gallery') }}</h2>
        <span class="products-count">{{ __('gallery_page_count', ['count' => $items->total()]) }}</span>
    </div>

    <div class="gallery-grid">
        @forelse($items as $item)
            <a class="gallery-item animated-card" href="{{ route('shop.show', $item) }}">
                <img src="{{ asset($item->image_principale) }}" alt="{{ $item->reference }}">
                <span>{{ $item->category?->localizedName() ?? $item->reference }}</span>
            </a>
        @empty
            <div class="no-products">{{ __('no_products') }}</div>
        @endforelse
    </div>

    @if($lastPage > 1)
        <nav class="pagination-container section-reveal" aria-label="Pagination">
            <ul class="pagination catalog-pagination justify-content-center">
                <li class="page-item {{ $items->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $items->previousPageUrl() ?: '#' }}" aria-label="{{ __('previous') }}" @if($items->onFirstPage()) aria-disabled="true" @endif>&laquo;</a>
                </li>

                @if($windowStart > 1)
                    <li class="page-item"><a class="page-link" href="{{ $items->url(1) }}">1</a></li>
                    @if($windowStart > 2)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                @endif

                @for($page = $windowStart; $page <= $windowEnd; $page++)
                    <li class="page-item {{ $page === $currentPage ? 'active' : '' }}" @if($page === $currentPage) aria-current="page" @endif>
                        <a class="page-link" href="{{ $items->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor

                @if($windowEnd < $lastPage)
                    @if($windowEnd < $lastPage - 1)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                    <li class="page-item"><a class="page-link" href="{{ $items->url($lastPage) }}">{{ $lastPage }}</a></li>
                @endif

                <li class="page-item {{ $items->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $items->nextPageUrl() ?: '#' }}" aria-label="{{ __('next') }}" @unless($items->hasMorePages()) aria-disabled="true" @endunless>&raquo;</a>
                </li>
            </ul>
            <p class="pagination-info">
                {{ __('showing') }} {{ $paginationStart }} {{ __('to') }} {{ $paginationEnd }}
                {{ __('of') }} {{ $items->total() }} {{ __('total_products') }}
            </p>
        </nav>
    @endif
</main>
@endsection
