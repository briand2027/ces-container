@extends('layouts.app')
@section('meta_title', __('home').' | C.E.S. Container')
@section('meta_description', __('hero_subtitle'))

@section('content')
<section class="hero-section bg-slideshow" style="--hero-image: url('{{ asset('images/container_sale.jpg') }}'); --hero-image-2: url('{{ asset('images/transport.jpg') }}'); --hero-image-3: url('{{ asset('images/centre.png') }}')">
    <div class="container py-5">
        <div class="row align-items-center g-5 py-lg-5">
            <div class="col-lg-7">
                <span class="eyebrow">CONTAINER EQUIPMENT SERVICES</span>
                <h1 class="hero-title">{{ __('hero_title') }}</h1>
                <p class="hero-subtitle">{{ __('hero_subtitle') }}</p>
                <div class="hero-buttons">
                    <a class="btn btn-light btn-lg" href="{{ route('shop') }}">{{ __('view_shop') }}</a>
                    <a class="btn btn-outline-light btn-lg" href="{{ route('contact') }}">{{ __('talk_to_advisor') }}</a>
                </div>
                <div class="hero-stats">
                    <div><strong>{{ $products->count() }}+</strong><span>{{ __('visible_products') }}</span></div>
                    <div><strong>{{ $categories->count() }}</strong><span>{{ __('categories') }}</span></div>
                    <div><strong>100%</strong><span>{{ __('support') }}</span></div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="hero-image"><img src="{{ asset('images/images_co.webp') }}" alt="C.E.S. Container" class="img-fluid rounded-4 shadow-lg"></div>
            </div>
        </div>
    </div>
</section>

<section class="home-services-section section-reveal">
    <div class="home-section-bg" style="background-image:url('{{ asset('images/arriere.jpg') }}')"></div>
    <div class="container">
        <div class="section-heading">
            <h2 class="section-title">{{ __('home_main_services') }}</h2>
            <p class="section-subtitle">{{ __('home_services_description') }}</p>
        </div>
        @php($serviceCards = [
            ['container_sale.jpg','home_container_sales','home_container_sales_description',route('shop')],
            ['container_rental.jpg','home_container_rental','home_container_rental_description',route('services')],
            ['Container_transport.jpg','home_container_transport','home_container_transport_description',route('services')],
            ['Special_equipment.jpg','home_special_equipment','home_special_equipment_description',route('services')],
            ['transport.jpg','home_terminal_services','home_terminal_services_description',route('services')],
            ['CC.webp','home_customs_declarations','home_customs_declarations_description',route('services')],
        ])
        <div class="home-card-grid">
            @foreach($serviceCards as [$image,$title,$text,$url])
                <article class="home-service-card animated-card">
                    <div class="home-service-icon"><img src="{{ asset('images/'.$image) }}" alt="{{ __($title) }}"></div>
                    <h3>{{ __($title) }}</h3>
                    <p>{{ __($text) }}</p>
                    <a href="{{ $url }}" class="btn btn-outline-success mt-auto">{{ __('learn_more') }}</a>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="home-stats-section section-reveal">
    <div class="container">
        <div class="home-stats-grid">
            <div class="home-stat animated-card"><strong>30+</strong><span>{{ __('years_experience') }}</span></div>
            <div class="home-stat animated-card"><strong>8</strong><span>{{ __('different_countries') }}</span></div>
            <div class="home-stat animated-card"><strong>{{ max(30000, $products->sum('quantite_stock')) }}</strong><span>{{ __('containers_in_stock') }}</span></div>
            <div class="home-stat animated-card"><strong>1000+</strong><span>{{ __('satisfied_clients') }}</span></div>
        </div>
    </div>
</section>

<section class="home-categories-section section-reveal">
    <div class="container">
        <div class="section-heading">
            <h2 class="section-title">{{ __('home_categories_title') }}</h2>
            <p class="section-subtitle">{{ __('home_categories_description') }}</p>
        </div>
        <div class="home-card-grid">
            @foreach($categories as $category)
                <article class="home-category-card animated-card">
                    <img src="{{ $category->image_url ? asset($category->image_url) : asset('images/images.jpg') }}" alt="{{ $category->localizedName() }}">
                    <div class="home-category-overlay">
                        <h3>{{ $category->localizedName() }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($category->localizedDescription(), 80) }}</p>
                        <a href="{{ route('shop', ['categorie' => $category->id]) }}">{{ __('view_category_products') }}</a>
                    </div>
                </article>
            @endforeach
            @if($categories->count() % 2 !== 0)
                <article class="home-category-card home-category-more animated-card">
                    <img src="{{ asset('images/toptainer.jpg') }}" alt="{{ __('section_products_all') }}">
                    <div class="home-category-overlay">
                        <h3>{{ __('section_products_all') }}</h3>
                        <p>{{ __('home_categories_description') }}</p>
                        <a href="{{ route('shop') }}">{{ __('more_items') }}</a>
                    </div>
                </article>
            @endif
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('shop') }}" class="btn btn-success btn-lg">{{ __('all_categories') }}</a>
        </div>
    </div>
</section>

<section class="home-products-section section-reveal">
    <div class="container">
        <div class="section-heading">
            <h2 class="section-title">{{ __('featured_products') }}</h2>
            <p class="section-subtitle">{{ __('products_description') }}</p>
        </div>
        <div class="home-product-grid">
            @foreach($products as $product)
                <article class="home-product-card animated-card">
                    <div class="home-product-img" style="background-image:url('{{ $product->image_principale ? asset($product->image_principale) : asset('images/container_sale.jpg') }}')"></div>
                    <div class="home-product-overlay">
                        <a href="{{ route('shop.show', $product) }}" class="btn btn-success">{{ __('view_details') }}</a>
                    </div>
                    <div class="home-product-content">
                        <h3>{{ $product->reference }}</h3>
                        <p>{{ $product->category?->localizedName() }}</p>
                        @if($product->prix_vente)<strong>{{ __('sale_price') }}: {{ number_format((float) $product->prix_vente * (1 + $vatRate / 100), 2, ',', ' ') }} &euro;</strong><small class="d-block">{{ __('tax_included') }}</small>@endif
                        <a href="{{ route('shop.show', $product) }}" class="btn btn-outline-success">{{ __('specifications') }}</a>
                    </div>
                </article>
            @endforeach
            @if($products->count() % 2 !== 0)
                <article class="home-product-card home-product-more animated-card">
                    <div class="home-product-content">
                        <h3>{{ __('view_all_products') }}</h3>
                        <p>{{ __('products_description') }}</p>
                        <a href="{{ route('shop') }}" class="btn btn-success mt-auto">{{ __('view_all_products') }}</a>
                    </div>
                </article>
            @endif
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('shop') }}" class="btn btn-success btn-lg">{{ __('view_all_products') }}</a>
        </div>
    </div>
</section>

<section class="home-testimonials-section section-reveal" style="--section-bg: url('{{ asset('images/transport.jpg') }}')">
    <div class="container">
        <div class="section-heading text-white">
            <h2 class="section-title text-white">{{ __('what_our_clients_say') }}</h2>
            <p class="section-subtitle text-white-50">{{ __('testimonials_description') }}</p>
        </div>
        @php($testimonials = [
            ['testimonial_1','logistics_manager','LC'],
            ['testimonial_2','project_director','DP'],
            ['testimonial_3','site_manager','RC'],
            ['testimonial_4','operations_manager','RO'],
        ])
        <div class="testimonial-marquee" aria-label="{{ __('what_our_clients_say') }}">
            <div class="testimonial-track">
                @foreach(array_merge($testimonials, $testimonials) as [$quote,$role,$initials])
                    <article class="testimonial-card testimonial-marquee-card">
                        <div class="testimonial-head">
                            <span class="testimonial-avatar">{{ $initials }}</span>
                            <span class="testimonial-stars" aria-label="5/5">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        </div>
                        <p>{{ __($quote) }}</p>
                        <strong>{{ __($role) }}</strong>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

<section class="cta-section home-cta-section section-reveal">
    <div class="container text-center py-5">
        <h2 class="text-white fw-bold display-6">{{ __('ready_to_get_started') }}</h2>
        <p class="text-white-50 mx-auto mb-4" style="max-width:700px">{{ __('cta_description') }}</p>
        <div class="hero-buttons justify-content-center">
            <a href="{{ route('contact') }}" class="btn btn-light btn-lg">{{ __('request_quote') }}</a>
            <a href="{{ route('shop') }}" class="btn btn-outline-light btn-lg">{{ __('view_all_products') }}</a>
        </div>
    </div>
</section>
@endsection
