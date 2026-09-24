@extends('layouts.app')
@section('meta_title', __('services').' | C.E.S. Container')
@section('meta_description', __('services_intro'))

@section('content')
<section class="page-hero services-hero bg-slideshow" style="--hero-image: url('{{ asset('images/transport.jpg') }}'); --hero-image-2: url('{{ asset('images/Container_transport.jpg') }}'); --hero-image-3: url('{{ asset('images/Special_equipment.jpg') }}')">
    <div class="container">
        <div class="page-hero-content text-white text-center">
            <span class="eyebrow">{{ __('services') }}</span>
            <h1 class="hero-title">{{ __('our_services') }}</h1>
            <p class="hero-subtitle">{{ __('services_intro') }}</p>
            <div class="hero-buttons justify-content-center">
                <a href="#services-list" class="btn btn-light btn-lg">{{ __('view_all_services') }}</a>
                <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg">{{ __('request_quote') }}</a>
            </div>
        </div>
    </div>
</section>

<div class="section-wave"></div>

<section class="services-stats-section section-reveal">
    <div class="container">
        <div class="services-stats-grid">
            <div class="service-stat animated-card"><strong>{{ $services->count() }}+</strong><span>{{ __('our_services') }}</span></div>
            <div class="service-stat animated-card"><strong>30+</strong><span>{{ __('years_experience') }}</span></div>
            <div class="service-stat animated-card"><strong>8</strong><span>{{ __('different_countries') }}</span></div>
            <div class="service-stat animated-card"><strong>100%</strong><span>{{ __('support') }}</span></div>
        </div>
    </div>
</section>

<section class="services-page section-reveal" id="services-list">
    <div class="services-bg" style="background-image: url('{{ asset('images/arriere.jpg') }}')"></div>
    <div class="container">
        <div class="section-heading">
            <span class="eyebrow green">{{ __('service_support_title') }}</span>
            <h2 class="section-title">{{ __('our_services') }}</h2>
            <p class="section-subtitle">{{ __('services_intro') }}</p>
        </div>
    <div class="services-grid">
        @foreach($services as $service)
            <div>
                @php
                    $description = trim((string) $service->description);
                    $isLong = \Illuminate\Support\Str::length($description) > 190;
                @endphp
                <article class="service-card service-media-card service-equal-card animated-card h-100">
                    <div class="service-card-img-wrap">
                        <img src="{{ $service->image_url ? asset($service->image_url) : asset('images/transport.jpg') }}" alt="{{ $service->nom }}" class="service-image">
                        <span class="service-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="service-card-body">
                        <div class="service-icon">{{ $loop->iteration }}</div>
                        <h3>{{ $service->nom }}</h3>
                        <p class="service-description-text {{ $isLong ? 'is-clamped' : '' }}">{{ $description }}</p>
                        @if($isLong)
                            <button type="button" class="service-read-more" data-more="{{ __('read_more') }}" data-less="{{ __('show_less') }}">{{ __('read_more') }}</button>
                        @endif
                        <div class="service-card-footer">
                            @if($service->prix)
                                <strong class="price-main">{{ number_format((float)$service->prix,2,',',' ') }} &euro;</strong>
                            @else
                                <strong class="price-main">{{ __('price_on_request') }}</strong>
                            @endif
                            <a class="btn btn-outline-success" href="{{ route('contact') }}">{{ __('request_quote') }}</a>
                        </div>
                    </div>
                </article>
            </div>
        @endforeach
        @if($services->count() % 2 !== 0)
            <div>
                <article class="service-card cta-card service-equal-card animated-card h-100">
                    <div class="service-icon">+</div>
                    <h3>{{ __('contact_us') }}</h3>
                    <p>{{ __('contact_help') }}</p>
                    <a class="btn btn-success mt-auto" href="{{ route('contact') }}">{{ __('request_information') }}</a>
                </article>
            </div>
        @endif
    </div>
    </div>
</section>

<section class="split-showcase section-reveal" style="--section-bg: url('{{ asset('images/transports.avif') }}')">
    <div class="container">
        <div class="showcase-content">
            <span class="eyebrow">{{ __('categories') }}</span>
            <h2>{{ __('project_categories_title') }}</h2>
            <p>{{ __('project_categories_text') }}</p>
            <a href="{{ route('shop') }}" class="btn btn-light">{{ __('section_products_all') }}</a>
        </div>
    </div>
</section>

<section class="cta-section home-cta-section section-reveal">
    <div class="container text-center py-5">
        <h2 class="text-white fw-bold display-6">{{ __('ready_to_start') }}</h2>
        <p class="text-white-50 mx-auto mb-4" style="max-width:700px">{{ __('cta_description') }}</p>
        <div class="hero-buttons justify-content-center">
            <a href="{{ route('contact') }}" class="btn btn-light btn-lg">{{ __('get_quote') }}</a>
            <a href="{{ route('shop') }}" class="btn btn-outline-light btn-lg">{{ __('view_shop') }}</a>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('click', (event) => {
    const button = event.target.closest('.service-read-more');
    if (!button) return;

    const card = button.closest('.service-equal-card');
    const description = card?.querySelector('.service-description-text');
    if (!description) return;

    const expanded = description.classList.toggle('is-expanded');
    button.textContent = expanded ? button.dataset.less : button.dataset.more;
});
</script>
@endpush
@endsection
