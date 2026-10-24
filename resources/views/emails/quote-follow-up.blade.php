<!doctype html>
<html lang="{{ app()->getLocale() === 'ch' ? 'de-CH' : app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f2f5f7;font-family:Arial,Helvetica,sans-serif;color:#1b2730">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:28px 12px;background:#f2f5f7"><tr><td align="center">
<table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#fff;border-radius:8px;overflow:hidden">
    <tr><td style="background:#123d35;padding:26px 32px;color:#fff"><div style="font-size:12px;text-transform:uppercase;opacity:.8">{{ $company }}</div><h1 style="font-size:23px;line-height:1.3;margin:9px 0 0">{{ __('order.email_customer_title') }}</h1></td></tr>
    <tr><td style="padding:28px 32px;font-size:15px;line-height:1.7">
        <p>{{ __('order.followup_intro') }}</p>
        <p><strong>{{ __('quote.reference') }}: {{ $quote->reference }}</strong></p>
        <p>{{ __('order.followup_action') }}</p>
        <p>{{ __('order.pdf_attached') }}</p>
        <p style="margin-top:28px">{{ __('quote.email_signoff') }}<br><strong>{{ $company }}</strong></p>
    </td></tr>
    <tr><td style="padding:17px 32px;background:#f4f7f6;color:#6b777d;font-size:12px">{{ __('quote.automatic_email', ['company' => $company]) }}</td></tr>
</table>
</td></tr></table>
</body></html>
