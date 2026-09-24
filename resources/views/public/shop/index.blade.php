@extends('layouts.app')
@section('meta_title', __('shop').' | C.E.S. Container')
@section('meta_description', __('shop_seo_intro'))
@section('canonical', route('shop'))

@section('content')
@php
    $activeCategory = request('categorie');
    $activeType = request('type');
    $activeSize = request('pieds');
    $activeFootType = request('type_pied');
    $activeSearch = request('q');
    $heroTitle = $selectedCategory?->nom ?: __('shop');
    $heroText = $selectedCategory?->description ?: __('shop_intro');
    $paginationStart = $products->firstItem() ?? 0;
    $paginationEnd = $products->lastItem() ?? 0;
    $currentPage = $products->currentPage();
    $lastPage = $products->lastPage();
    $windowStart = max(1, $currentPage - 2);
    $windowEnd = min($lastPage, $currentPage + 2);
@endphp

<section class="page-hero shop-hero bg-slideshow" style="--hero-image: url('{{ asset('images/images.jpg') }}'); --hero-image-2: url('{{ asset('images/abs.jpeg') }}'); --hero-image-3: url('{{ asset('images/Container_transport.jpg') }}')">
    <div class="container">
        <div class="page-hero-content text-center text-white">
            <span class="eyebrow">{{ __('shop') }}</span>
            <h1 class="hero-title">{{ $selectedCategory?->localizedName() ?: $heroTitle }}</h1>
            <p class="hero-subtitle">{{ $heroText }}</p>
            <div class="hero-buttons">
                <a href="#products" class="btn btn-light btn-lg">{{ __('view_product') }}</a>
                <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg">{{ __('contact_us') }}</a>
            </div>
        </div>
    </div>
</section>

<main class="catalog-page">
    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('home') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ __('shop') }}</li>
            </ol>
        </nav>

        <div class="row g-4">
            <aside class="col-lg-3">
                <div class="shop-aside-stack">
                <div class="shop-sidebar animated-card">
                    <h2 class="sidebar-title">{{ __('categories') }}</h2>
                    <ul class="category-list nested-category-list">
                        <li>
                            <a class="category-link {{ ! $activeCategory ? 'active' : '' }}" href="{{ route('shop', request()->except('categorie', 'page')) }}">
                                <span>{{ __('all_categories') }}</span>
                                <span class="category-count">{{ $categories->sum('available_count') }}</span>
                            </a>
                        </li>
                        @foreach($categoryTree as $node)
                            @php($category = $node['category'])
                            <li>
                                <details class="category-group" {{ (string) $activeCategory === (string) $category->id ? 'open' : '' }}>
                                    <summary>
                                        <span class="category-link category-toggle {{ (string) $activeCategory === (string) $category->id ? 'active' : '' }}">
                                            <span>{{ $category->localizedName() }}</span>
                                            <span class="category-count">{{ $category->available_count }}</span>
                                        </span>
                                    </summary>
                                    @if($node['sizes']->isNotEmpty())
                                        <ul class="subcategory-list">
                                            <li>
                                                <a class="subcategory-link {{ (string) $activeCategory === (string) $category->id && ! $activeSize ? 'active' : '' }}" href="{{ route('shop', array_merge(request()->except('page', 'pieds', 'type_pied'), ['categorie' => $category->id])) }}">
                                                    <span>{{ __('all') }}</span>
                                                    <span class="size-count">{{ $category->available_count }}</span>
                                                </a>
                                            </li>
                                            @foreach($node['sizes'] as $size)
                                                <li>
                                                    <details class="size-group" {{ (string) $activeCategory === (string) $category->id && (string) $activeSize === (string) $size['label'] ? 'open' : '' }}>
                                                        <summary>
                                                            <span class="subcategory-link size-toggle {{ (string) $activeSize === (string) $size['label'] && (string) $activeCategory === (string) $category->id ? 'active' : '' }}">
                                                                <span>{{ $size['label'] }}</span>
                                                                <span class="size-count">{{ $size['count'] }}</span>
                                                            </span>
                                                        </summary>
                                                        <ul class="piedtype-list">
                                                            <li>
                                                                <a class="piedtype-link {{ (string) $activeSize === (string) $size['label'] && ! $activeFootType && (string) $activeCategory === (string) $category->id ? 'active' : '' }}" href="{{ route('shop', array_merge(request()->except('page', 'type_pied'), ['categorie' => $category->id, 'pieds' => $size['label']])) }}">
                                                                    <span>{{ __('all') }}</span>
                                                                    <span>{{ $size['count'] }}</span>
                                                                </a>
                                                            </li>
                                                            @if($size['types']->isNotEmpty())
                                                                @foreach($size['types'] as $footType => $count)
                                                                    <li>
                                                                        <a class="piedtype-link {{ (string) $activeFootType === (string) $footType && (string) $activeSize === (string) $size['label'] ? 'active' : '' }}" href="{{ route('shop', array_merge(request()->except('page'), ['categorie' => $category->id, 'pieds' => $size['label'], 'type_pied' => $footType])) }}">
                                                                            <span>{{ $footType }}</span>
                                                                            <span>{{ $count }}</span>
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            @endif
                                                        </ul>
                                                    </details>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </details>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="shop-sidebar help-sidebar animated-card">
                    <h3 class="sidebar-title">{{ __('need_help') }}</h3>
                    <p>{{ __('contact_help') }}</p>
                    <a href="{{ route('contact') }}" class="btn btn-success w-100">{{ __('contact_us') }}</a>
                </div>

                <div class="shop-sidebar info-sidebar animated-card">
                    <h3 class="sidebar-title">{{ __('information') }}</h3>
                    <p>{{ $selectedCategory?->localizedDescription() ?: $heroText }}</p>
                </div>
                </div>
            </aside>

            <section class="col-lg-9" id="products">
                <div class="seo-intro-block section-reveal">{{ __('shop_seo_intro') }}</div>

                <form class="filter-section section-reveal" method="get" action="{{ route('shop') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label" for="q">{{ __('reference_search') }}</label>
                            <input id="q" name="q" value="{{ $activeSearch }}" class="form-control" placeholder="{{ __('reference_search') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="categorie">{{ __('categories') }}</label>
                            <select id="categorie" name="categorie" class="form-select">
                                <option value="">{{ __('all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) $activeCategory === (string) $category->id)>{{ $category->localizedName() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="type">{{ __('type') }}</label>
                            <select id="type" name="type" class="form-select">
                                <option value="">{{ __('all_types') }}</option>
                                @foreach($types as $type)
                                    <option value="{{ $type }}" @selected((string) $activeType === (string) $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="pieds">{{ __('dimensions') }}</label>
                            <select id="pieds" name="pieds" class="form-select">
                                <option value="">{{ __('all') }}</option>
                                @foreach($sizes as $size)
                                    <option value="{{ $size }}" @selected((string) $activeSize === (string) $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="type_pied">{{ __('type') }}</label>
                            <select id="type_pied" name="type_pied" class="form-select">
                                <option value="">{{ __('all_types') }}</option>
                                @foreach($footTypes as $footType)
                                    <option value="{{ $footType }}" @selected((string) $activeFootType === (string) $footType)>{{ $footType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <button class="btn btn-success w-100">{{ __('filter') }}</button>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <a class="btn btn-outline-success w-100" href="{{ route('shop') }}">{{ __('clear_filters') }}</a>
                        </div>
                    </div>
                </form>

                @if($activeCategory || $activeType || $activeSize || $activeFootType || $activeSearch)
                    <div class="active-filter-box section-reveal">
                        <strong>{{ __('active_filters') }}:</strong>
                        @if($activeSearch)<span class="filter-badge">{{ $activeSearch }}</span>@endif
                        @if($selectedCategory)<span class="filter-badge">{{ $selectedCategory->localizedName() }}</span>@endif
                        @if($activeType)<span class="filter-badge">{{ ucfirst(str_replace('_', ' ', $activeType)) }}</span>@endif
                        @if($activeSize)<span class="filter-badge">{{ $activeSize }}</span>@endif
                        @if($activeFootType)<span class="filter-badge">{{ $activeFootType }}</span>@endif
                    </div>
                @endif

                <div class="catalog-toolbar section-reveal">
                    <h2 class="catalog-title mb-0">{{ __('products') }}</h2>
                    <span class="products-count">{{ $products->total() }} {{ __('products_count') }}</span>
                </div>

                <div class="catalog-grid">
                    @forelse($products as $product)
                        <div>
                            <article class="product-card catalog-card animated-card" itemscope itemtype="https://schema.org/Product">
                                <meta itemprop="sku" content="{{ $product->reference }}">
                                <meta itemprop="name" content="{{ $product->reference }}">
                                @if($product->localizedDescription())
                                    <meta itemprop="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($product->localizedDescription()), 160) }}">
                                @endif
                                <a class="product-image product-img-link" href="{{ route('shop.show', $product) }}" itemprop="url">
                                    <img src="{{ $product->image_principale ? asset($product->image_principale) : asset('images/container_sale.jpg') }}" alt="{{ $product->reference }}" itemprop="image">
                                    <span class="product-badge">{{ __('available') }}</span>
                                    @if($product->quantite_stock !== null)
                                        <span class="image-chip">{{ __('stock') }}: {{ $product->quantite_stock }}</span>
                                    @endif
                                </a>
                                <div class="product-content">
                                    <div class="small text-muted mb-1">{{ $product->category?->localizedName() ?? ucfirst(str_replace('_', ' ', $product->type_conteneur)) }}</div>
                                    <h3 itemprop="name">{{ $product->reference }}</h3>
                                    <div class="product-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                        <meta itemprop="availability" content="{{ $product->quantite_stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}">
                                        <meta itemprop="priceCurrency" content="EUR">
                                        @if($product->prix_vente)
                                            <meta itemprop="price" content="{{ number_format((float) $product->prix_vente * (1 + $vatRate / 100), 2, '.', '') }}">
                                            <span class="price-main">{{ number_format((float) $product->prix_vente * (1 + $vatRate / 100), 2, ',', ' ') }} &euro;</span>
                                            <small class="d-block">{{ __('tax_included') }}</small>
                                        @endif
                                        @if($product->prix_location_jour)
                                            <span class="price-rent">{{ number_format((float) $product->prix_location_jour, 2, ',', ' ') }} &euro; / {{ __('per_day') }}</span>
                                        @endif
                                    </div>
                                    <ul class="product-features">
                                        @if($product->dimensions)<li>{{ __('dimensions') }}: {{ $product->dimensions }}</li>@endif
                                        @if($product->pieds)<li>{{ __('dimensions') }}: {{ $product->pieds }}</li>@endif
                                        @if($product->type_pied)<li>{{ __('type') }}: {{ $product->type_pied }}</li>@endif
                                        @if($product->poids)<li>{{ __('weight') }}: {{ $product->poids }}</li>@endif
                                        @if($product->capacite)<li>{{ __('capacity') }}: {{ $product->capacite }}</li>@endif
                                    </ul>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('shop.show', $product) }}" class="btn btn-success">{{ __('view_details') }}</a>
                                        <form method="post" action="{{ route('cart.add', $product) }}" class="d-grid">
                                            @csrf
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="type" value="vente">
                                            <button class="btn btn-outline-success">{{ __('add_to_cart') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div><div class="no-products">{{ __('no_products') }}</div></div>
                    @endforelse
                </div>

                @if($lastPage > 1)
                    <nav class="pagination-container section-reveal" aria-label="Pagination">
                        <ul class="pagination catalog-pagination justify-content-center">
                            <li class="page-item {{ $products->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $products->previousPageUrl() ?: '#' }}" aria-label="{{ __('previous') }}" @if($products->onFirstPage()) aria-disabled="true" @endif>&laquo;</a>
                            </li>

                            @if($windowStart > 1)
                                <li class="page-item"><a class="page-link" href="{{ $products->url(1) }}">1</a></li>
                                @if($windowStart > 2)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                            @endif

                            @for($page = $windowStart; $page <= $windowEnd; $page++)
                                <li class="page-item {{ $page === $currentPage ? 'active' : '' }}" @if($page === $currentPage) aria-current="page" @endif>
                                    <a class="page-link" href="{{ $products->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor

                            @if($windowEnd < $lastPage)
                                @if($windowEnd < $lastPage - 1)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                                <li class="page-item"><a class="page-link" href="{{ $products->url($lastPage) }}">{{ $lastPage }}</a></li>
                            @endif

                            <li class="page-item {{ $products->hasMorePages() ? '' : 'disabled' }}">
                                <a class="page-link" href="{{ $products->nextPageUrl() ?: '#' }}" aria-label="{{ __('next') }}" @unless($products->hasMorePages()) aria-disabled="true" @endunless>&raquo;</a>
                            </li>
                        </ul>
                        <p class="pagination-info">
                            {{ __('showing') }} {{ $paginationStart }} {{ __('to') }} {{ $paginationEnd }}
                            {{ __('of') }} {{ $products->total() }} {{ __('total_products') }}
                        </p>
                    </nav>
                @endif
            </section>
        </div>
    </div>
</main>
@endsection
