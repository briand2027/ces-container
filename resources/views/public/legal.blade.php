@extends('layouts.app')
@php($pageContent = __('legal.pages.'.$page))
@section('meta_title', $pageContent['title'].' | C.E.S. Container')
@push('head')<link rel="preload" as="image" href="{{ asset('images/optimized/ccc.webp') }}" fetchpriority="high">@endpush
@section('meta_description', $pageContent['intro'])
@section('content')
<section class="page-hero bg-slideshow legal-hero" data-hero-image-2="{{ asset('images/optimized/images_co.webp') }}" data-hero-image-3="{{ asset('images/optimized/toptainer.webp') }}" style="--hero-image:url('{{ asset('images/optimized/ccc.webp') }}')">
    <div class="container"><div class="page-hero-content text-white"><span class="eyebrow">C.E.S. Container · {{ __('legal.information') }}</span><h1 class="hero-title">{{ $pageContent['title'] }}</h1><p class="hero-subtitle">{{ $pageContent['intro'] }}</p></div></div>
</section>
<main id="main-content" class="container legal-page py-5 section-reveal">
    <nav class="legal-quick-links" aria-label="{{ __('legal.related_pages') }}">
        @foreach(['terms','delivery','returns','warranty','payment','privacy','legal-notice'] as $legalKey)
            <a href="{{ route('legal.'.(['terms'=>'terms','delivery'=>'delivery','returns'=>'returns','warranty'=>'warranty','payment'=>'payment','privacy'=>'privacy','legal-notice'=>'notice'][$legalKey])) }}" @if($page===$legalKey) aria-current="page" @endif>{{ __('legal.nav.'.$legalKey) }}</a>
        @endforeach
    </nav>
    @if($page==='legal-notice')
        <section class="legal-identity-panel animated-card"><div><span class="eyebrow green">{{ __('legal.identity_title') }}</span><h2>{{ $company }}</h2></div><dl class="legal-identity-list mb-0">
            @foreach(['form'=>'legal.form','registration'=>'legal.registration','vat'=>'legal.vat','address'=>'legal.address','email'=>'legal.email','phone'=>'legal.phone'] as $identityKey=>$labelKey)
                @if(!empty($identity[$identityKey]))<div><dt>{{ __($labelKey) }}</dt><dd>@if($identityKey==='email')<a href="mailto:{{ $identity[$identityKey] }}">{{ $identity[$identityKey] }}</a>@elseif($identityKey==='phone')<a href="tel:{{ preg_replace('/[^+0-9]/','',$identity[$identityKey]) }}">{{ $identity[$identityKey] }}</a>@else{{ $identity[$identityKey] }}@endif</dd></div>@endif
            @endforeach
        </dl></section>
    @endif
    <div class="legal-content">
        @foreach($pageContent['sections'] as $section)
            <section class="legal-section section-reveal"><span class="legal-section-number">{{ str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT) }}</span><div><h2>{{ $section['heading'] }}</h2>@foreach($section['paragraphs'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach
                @if(($section['link'] ?? null)==='consumer')<a class="legal-reference" href="https://europa.eu/youreurope/citizens/consumers/shopping/guarantees/index_en.htm" target="_blank" rel="noopener">{{ __('legal.consumer_reference') }} <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>@endif
                @if(($section['link'] ?? null)==='privacy')<a class="legal-reference" href="https://commission.europa.eu/law/law-topic/data-protection/information-individuals_en" target="_blank" rel="noopener">{{ __('legal.privacy_reference') }} <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>@endif
            </div></section>
        @endforeach
    </div>
    <section class="legal-contact-strip"><div><span class="eyebrow green">C.E.S. Container</span><h2>{{ __('legal.questions_title') }}</h2><p>{{ __('legal.questions_text') }}</p></div><a class="btn btn-success" href="{{ route('contact') }}"><i class="bi bi-envelope me-2" aria-hidden="true"></i>{{ __('contact_us') }}</a></section>
</main>
@endsection
