@extends('layouts.app')
@section('robots', 'noindex,follow')
@section('meta_title', __('order_confirmed').' | C.E.S. Container')

@section('content')
<main class="container py-5">
    <div class="text-center mb-4">
        <i class="bi bi-check-circle-fill display-3 text-success" aria-hidden="true"></i>
        <h1 class="h2 mt-3">{{ __('order_confirmed') }}</h1>
        <p class="lead">{{ __('thank_you_order') }}</p>
        <p class="text-muted">{{ __('order_number') }}: <strong>{{ $order->numero_commande }}</strong></p>
    </div>

    <div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-labelledby="orderSuccessTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h2 class="modal-title h5" id="orderSuccessTitle"><i class="bi bi-check-circle me-2" aria-hidden="true"></i>{{ __('order_confirmed') }}</h2>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill display-4 text-success mb-3" aria-hidden="true"></i>
                        <h3 class="h4 mb-2">{{ __('thank_you_order') }}</h3>
                        <p class="text-muted">{{ __('order_number') }}: <strong>{{ $order->numero_commande }}</strong></p>
                    </div>

                    <section class="border rounded-3 p-3 p-md-4" aria-labelledby="bank-details-title">
                        <h3 class="h5 mb-3" id="bank-details-title"><i class="bi bi-bank me-2" aria-hidden="true"></i>{{ __('bank_details_title') }} 🇮🇹</h3>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><strong class="d-block">{{ __('account_holder') }}:</strong><span>{{ $bank['banque_titulaire'] ?: __('not_provided') }}</span></div>
                            <div class="col-md-6"><strong class="d-block">{{ __('bank_name') }}:</strong><span>{{ $bank['banque_nom'] ?: __('not_provided') }}</span></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-8"><strong class="d-block">IBAN:</strong><div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-1"><code id="ibanNumber" class="fs-6 user-select-all text-break">{{ $bank['banque_iban'] ?: __('not_provided') }}</code>@if($bank['banque_iban'])<button class="btn btn-sm btn-outline-success" type="button" data-copy-text="{{ $bank['banque_iban'] }}"><i class="bi bi-copy me-1" aria-hidden="true"></i><span>{{ __('copy') }}</span></button>@endif</div></div>
                            <div class="col-md-4"><strong class="d-block">BIC/SWIFT:</strong><span>{{ $bank['banque_bic'] ?: __('not_provided') }}</span></div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6"><strong class="d-block">{{ __('transfer_type') }}:</strong><span>{{ __('immediate_transfer') }}</span></div>
                            <div class="col-md-6"><strong class="d-block">{{ __('amount_due') }}:</strong><span class="h5 text-success">€{{ number_format((float) $order->total_ttc, 2, '.', ',') }}</span></div>
                        </div>
                        <div class="mt-3"><strong class="d-block">{{ __('payment_reference') }}:</strong><div class="d-flex flex-wrap align-items-center gap-2"><code id="paymentReference">{{ __('order_label') }} {{ $order->numero_commande }}</code><button class="btn btn-sm btn-outline-secondary" type="button" data-copy-text="{{ __('order_label') }} {{ $order->numero_commande }}"><i class="bi bi-copy me-1" aria-hidden="true"></i><span>{{ __('copy') }}</span></button></div></div>
                        @if($bank['banque_instructions'])<div class="alert alert-info mt-3 mb-0">{!! nl2br(e($bank['banque_instructions'])) !!}</div>@endif
                    </section>

                    <section class="mt-4" aria-labelledby="payment-conditions-title">
                        <h3 class="h6" id="payment-conditions-title"><i class="bi bi-receipt me-2" aria-hidden="true"></i>{{ __('payment_conditions') }}:</h3>
                        <ol class="mt-3 mb-0">
                            <li class="mb-2"><strong>{{ __('full_payment') }}:</strong> {{ __('full_payment_text') }}</li>
                            <li class="mb-2"><strong>{{ __('installment_payment') }}:</strong> {{ __('installment_payment_text') }}</li>
                            <li class="mb-2"><strong>{{ __('shipping_installation_costs') }}:</strong> {{ __('shipping_installation_costs_text') }}</li>
                            <li><strong>{{ __('delivery_times') }}:</strong> {{ __('delivery_times_text') }}</li>
                        </ol>
                    </section>

                    @php($proofText = __('payment_confirmation_for_order', ['number' => $order->numero_commande]))
                    <div class="alert alert-warning mt-4 mb-3">
                        <h3 class="h6"><i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>{{ __('important_instructions') }}:</h3>
                        <p class="mb-3">{!! __('payment_proof_request') !!}</p>
                        <div class="row g-3">
                            <div class="col-md-6"><strong class="d-block">{{ __('email') }}:</strong><a href="mailto:{{ $proofEmail }}?subject={{ rawurlencode($proofText) }}&body={{ rawurlencode(__('payment_proof_email_body', ['number' => $order->numero_commande])) }}" class="text-decoration-none"><i class="bi bi-envelope me-1" aria-hidden="true"></i>{{ $proofEmail }}</a></div>
                            <div class="col-md-6"><strong class="d-block">WhatsApp:</strong><a href="https://wa.me/393505747539?text={{ rawurlencode($proofText) }}" target="_blank" rel="noopener" class="text-decoration-none"><i class="bi bi-whatsapp me-1" aria-hidden="true"></i>+39 350 574 7539</a></div>
                        </div>
                        <p class="mt-3 mb-0"><small>{{ __('order_processed_after_payment_proof') }}</small></p>
                    </div>

                    <div class="alert alert-success mb-0"><h3 class="h6"><i class="bi bi-truck me-2" aria-hidden="true"></i>{{ __('delivery_information') }}:</h3><p class="mb-0">{{ __('delivery_tracking_text') }}</p></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button><a href="{{ route('shop') }}" class="btn btn-success"><i class="bi bi-shop me-1" aria-hidden="true"></i>{{ __('back_to_shopping') }}</a></div>
            </div>
        </div>
    </div>

    <div class="text-center"><button class="btn btn-success btn-lg" type="button" data-bs-toggle="modal" data-bs-target="#orderSuccessModal">{{ __('show_payment_details') }}</button></div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('orderSuccessModal');
    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();

    document.querySelectorAll('[data-copy-text]').forEach((button) => {
        button.addEventListener('click', async () => {
            const original = button.innerHTML;
            try {
                await navigator.clipboard.writeText(button.dataset.copyText);
            } catch {
                const field = document.createElement('textarea');
                field.value = button.dataset.copyText;
                field.style.position = 'fixed';
                field.style.opacity = '0';
                document.body.appendChild(field);
                field.select();
                document.execCommand('copy');
                field.remove();
            }
            button.innerHTML = '<i class="bi bi-check2 me-1" aria-hidden="true"></i>' + @json(__('copied'));
            window.setTimeout(() => { button.innerHTML = original; }, 1600);
        });
    });
});
</script>
@endpush
