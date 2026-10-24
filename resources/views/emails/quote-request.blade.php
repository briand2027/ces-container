<!doctype html>
<html lang="{{ $messageLocale === 'ch' ? 'de-CH' : $messageLocale }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f2f5f7;font-family:Arial,Helvetica,sans-serif;color:#1b2730">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:28px 12px;background:#f2f5f7"><tr><td align="center">
<table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#fff;border-radius:8px;overflow:hidden">
    <tr><td style="background:#123d35;padding:26px 32px;color:#fff"><div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;opacity:.8">{{ $company }}</div><h1 style="font-size:23px;line-height:1.3;margin:9px 0 0">{{ __($customer ? 'order.email_customer_title' : 'order.email_admin_title') }}</h1></td></tr>
    <tr><td style="padding:28px 32px">
        <p style="margin:0 0 18px;line-height:1.6">{{ __($customer ? 'order.email_customer_intro' : 'order.email_admin_intro') }}</p>
        @if($customer)<p style="margin:0 0 18px;line-height:1.6">{{ __('order.pdf_attached') }}</p>@endif
        <p style="margin:0 0 22px">{{ __('quote.reference') }}: <strong style="color:#14715e">{{ $quote->reference }}</strong></p>
        @unless($customer)
        <h2 style="font-size:16px;margin:22px 0 10px">{{ __('quote.requester_details') }}</h2>
        <table role="presentation" width="100%" cellspacing="0" cellpadding="6" style="font-size:14px;border-collapse:collapse">
            <tr><td style="color:#607078;width:34%">{{ __('quote.last_name') }} / {{ __('quote.first_name') }}</td><td>{{ trim(($quote->prenom ?? '').' '.$quote->nom) }}</td></tr>
            @if($quote->nom_entreprise)<tr><td style="color:#607078">{{ __('quote.company') }}</td><td>{{ $quote->nom_entreprise }}</td></tr>@endif
            <tr><td style="color:#607078">{{ __('quote.client_type') }}</td><td>{{ $quote->type_client === 'entreprise' ? __('quote.business') : __('quote.individual') }}</td></tr>
            <tr><td style="color:#607078">{{ __('quote.email') }}</td><td><a href="mailto:{{ $quote->email }}" style="color:#14715e">{{ $quote->email }}</a></td></tr>
            @if($quote->telephone)<tr><td style="color:#607078">{{ __('quote.phone') }}</td><td>{{ $quote->telephone }}</td></tr>@endif
            <tr><td style="color:#607078">{{ __('quote.delivery_address') }}</td><td>{{ $quote->adresse }}, {{ $quote->code_postal }} {{ $quote->ville }}, {{ $quote->pays }}</td></tr>
            @if($quote->numero_tva)<tr><td style="color:#607078">{{ __('quote.vat_number') }}</td><td>{{ $quote->numero_tva }}</td></tr>@endif
        </table>
        @endunless
        <h2 style="font-size:16px;margin:24px 0 10px">{{ __('quote.requested_items') }}</h2>
        <table role="presentation" width="100%" cellspacing="0" cellpadding="9" style="font-size:14px;border-collapse:collapse;border:1px solid #e4e9eb">
            <thead><tr style="background:#f4f7f6;text-align:left"><th>{{ __('quote.item_reference') }}</th><th>{{ __('quote.item_type') }}</th><th>{{ __('quote.quantity') }}</th><th>{{ __('order.unit_price') }}</th><th>{{ __('quote.duration') }}</th></tr></thead>
            <tbody>@foreach($quote->lines as $line)<tr><td style="border-top:1px solid #e4e9eb">{{ $line->reference_conteneur }}<br><span style="font-size:12px;color:#607078">{{ $line->type_conteneur }}</span></td><td style="border-top:1px solid #e4e9eb">{{ $line->type_ligne === 'location' ? __('quote.rental') : __('quote.purchase') }}</td><td style="border-top:1px solid #e4e9eb">{{ $line->quantite }}</td><td style="border-top:1px solid #e4e9eb">{{ $line->prix_unitaire_indicatif !== null ? number_format($line->prix_unitaire_indicatif,2,',',' ').' €'.($line->type_ligne === 'location' ? ' '.__('quote.per_day') : '') : __('quote.to_confirm') }}</td><td style="border-top:1px solid #e4e9eb">{{ $line->duree_location_jours ? $line->duree_location_jours.' '.__('quote.days') : '—' }}</td></tr>@endforeach</tbody>
        </table>
        @php
            $subtotalHt = $quote->lines->sum(fn ($line) => (float) ($line->prix_unitaire_indicatif ?? 0) * (int) $line->quantite * ($line->type_ligne === 'location' ? max(1, (int) ($line->duree_location_jours ?? 1)) : 1));
            $vatAmount = round($subtotalHt * $vatRate / 100, 2);
        @endphp
        <table role="presentation" width="100%" cellspacing="0" cellpadding="5" style="margin-top:12px;font-size:14px">
            <tr><td>{{ __('order.subtotal') }}</td><td align="right">{{ number_format($subtotalHt, 2, ',', ' ') }} €</td></tr>
            <tr><td>{{ __('order.tax') }} ({{ number_format($vatRate, 2, ',', '') }}%)</td><td align="right">{{ number_format($vatAmount, 2, ',', ' ') }} €</td></tr>
            <tr><td style="border-top:1px solid #d7dfdc;font-weight:bold">{{ __('order.total') }}</td><td align="right" style="border-top:1px solid #d7dfdc;font-weight:bold">{{ number_format($subtotalHt + $vatAmount, 2, ',', ' ') }} €</td></tr>
        </table>
        <p style="font-size:12px;color:#607078">{{ __('quote.cart_delivery_notice') }}</p>
        @if($quote->notes)<h2 style="font-size:16px;margin:24px 0 8px">{{ __('quote.specifications') }}</h2><p style="margin:0;line-height:1.6;white-space:pre-line">{{ $quote->notes }}</p>@endif
        @if($customer)<div style="margin-top:24px;padding:16px;background:#f1f7f5;border-left:3px solid #14715e;font-size:14px;line-height:1.6">{{ __('order.customer_disclaimer') }}</div>@else<div style="margin-top:24px;padding:14px;background:#fff8e8;font-size:13px;line-height:1.6">{{ __('order.admin_instruction') }}</div>@endif
        <p style="margin:26px 0 0;line-height:1.6">{{ $customer ? __('quote.email_signoff') : __('quote.email_reply_instruction') }}<br><strong>{{ $company }}</strong></p>
    </td></tr>
    <tr><td style="padding:17px 32px;background:#f4f7f6;color:#6b777d;font-size:12px">{{ __('quote.automatic_email', ['company' => $company]) }}</td></tr>
</table>
</td></tr></table>
</body></html>
