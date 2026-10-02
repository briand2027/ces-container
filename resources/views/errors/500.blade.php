@php
    $errorLocale = request()->hasSession() ? request()->session()->get('locale', 'it') : 'it';
    app()->setLocale(array_key_exists($errorLocale, config('locales.available', [])) ? $errorLocale : 'it');
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() === 'ch' ? 'de-CH' : app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,follow">
    <title>500 | C.E.S. Container</title>
    <link rel="icon" href="{{ asset('images/image_logo.jpeg') }}" type="image/jpeg">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 2rem; color: #17202a; background: #f6f8f7; font: 16px/1.6 Inter, system-ui, -apple-system, "Segoe UI", sans-serif; }
        main { width: min(100%, 650px); text-align: center; }
        .brand { margin: 0 0 2.5rem; color: #0b7425; font-size: .85rem; font-weight: 800; letter-spacing: .08em; }
        .code { margin: 0; color: #13a538; font-size: clamp(5rem, 18vw, 9rem); font-weight: 800; line-height: 1; }
        h1 { margin: 1.25rem 0 .5rem; font-size: clamp(1.7rem, 6vw, 2.4rem); line-height: 1.2; }
        p { margin: 0 auto 1.75rem; max-width: 520px; color: #657180; }
        a { display: inline-flex; align-items: center; justify-content: center; min-height: 48px; padding: .7rem 1.25rem; border-radius: 6px; color: #fff; background: #0b7425; font-weight: 700; text-decoration: none; }
        a:hover, a:focus-visible { background: #085b1d; }
    </style>
</head>
<body>
<main>
    <p class="brand">C.E.S. CONTAINER</p>
    <p class="code" aria-hidden="true">500</p>
    <h1>{{ __('server_error_title') }}</h1>
    <p>{{ __('server_error_message') }}</p>
    <a href="{{ url('/') }}">{{ __('error_back_home') }}</a>
</main>
</body>
</html>
