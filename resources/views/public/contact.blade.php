@extends('layouts.app')
@section('meta_title', __('contact').' | C.E.S. Container')
@section('meta_description', __('contact_help'))

@section('content')
<section class="page-hero bg-slideshow" style="--hero-image: url('{{ asset('images/transportes.png') }}'); --hero-image-2: url('{{ asset('images/transport.jpg') }}'); --hero-image-3: url('{{ asset('images/centre.png') }}')">
    <div class="container">
        <div class="page-hero-content text-white">
            <span class="eyebrow">{{ __('contact') }}</span>
            <h1 class="hero-title">{{ __('contact_us') }}</h1>
            <p class="hero-subtitle">{{ __('contact_help') }}</p>
        </div>
    </div>
</section>

<div class="section-wave"></div>

<div class="container py-5 section-reveal">
    <div class="contact-layout">
        <div class="contact-card animated-card">
            <span class="eyebrow green">{{ __('contact_details') }}</span>
            <h2>{{ __('contact_us') }}</h2>
            <div class="contact-lines">
                <p><strong>{{ __('address') }}</strong><span>{{ __('address_value') }}</span></p>
                <p><strong>{{ __('email_line') }}</strong><span>contact@containerequipmentservices.lt</span></p>
            </div>
            <div class="map-frame">
                <iframe title="{{ __('map_title') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=Via%20S.%20Luca%2015%2F5%2016124%20Genova%20GE%20Italia&output=embed"></iframe>
            </div>
        </div>

        <div class="content-panel animated-card">
            <form method="post" action="{{ route('contact.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6"><label class="form-label">{{ __('name') }} *</label><input name="nom" value="{{ old('nom') }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">{{ __('first_name') }} *</label><input name="prenom" value="{{ old('prenom') }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">{{ __('email') }} *</label><input type="email" name="email" value="{{ old('email') }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">{{ __('phone') }}</label><input name="telephone" value="{{ old('telephone') }}" class="form-control"></div>
                <div class="col-12"><label class="form-label">{{ __('subject') }} *</label><input name="sujet" value="{{ old('sujet') }}" class="form-control" required></div>
                <div class="col-12"><label class="form-label">{{ __('message') }} *</label><textarea name="message" rows="7" class="form-control" required>{{ old('message') }}</textarea></div>
                <div><button class="btn btn-success">{{ __('send') }}</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
