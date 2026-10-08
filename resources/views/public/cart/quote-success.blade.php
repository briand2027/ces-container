@extends('layouts.app')
@section('robots', 'noindex,follow')
@section('meta_title', __('quote.success_page_title').' | C.E.S. Container')
@section('content')
<main class="container py-5" style="max-width:850px">
    <section class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5 text-center">
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success" style="width:76px;height:76px"><i class="bi bi-envelope-check fs-1" aria-hidden="true"></i></span>
        <p class="text-success fw-bold text-uppercase small mt-4 mb-2">C.E.S. Container</p>
        <h1 class="h2">{{ __('quote.success_title') }}</h1>
        <p class="lead text-muted">{{ __('quote.success_intro') }}</p>
        <p class="mb-4">{{ __('quote.tracking_reference') }} : <strong class="text-success">{{ $reference }}</strong></p>
        @if($emailWarning)<div class="alert alert-warning text-start" role="status">{{ __('quote.notification_failed') }}</div>@else<div class="alert alert-success text-start">{{ __('quote.receipt_sent') }}</div>@endif
        <div class="text-start border rounded p-3 p-md-4 mb-4"><h2 class="h5">{{ __('quote.next_steps') }}</h2><p class="mb-2">{{ __('quote.not_an_order') }}</p><p class="mb-0">{{ __('quote.written_agreement') }}</p></div>
        <a class="btn btn-success" href="{{ route('shop') }}"><i class="bi bi-shop me-2" aria-hidden="true"></i>{{ __('quote.back_to_shop') }}</a>
    </div></section>
</main>
@endsection
