<!doctype html>
<html lang="{{ app()->getLocale() === 'ch' ? 'de-CH' : app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $pageTitle = trim($__env->yieldContent('meta_title'));
        $pageTitle = $pageTitle ?: match (request()->route()?->getName()) {
            'home' => __('home').' | C.E.S. Container',
            'shop' => __('shop').' | C.E.S. Container',
            'services' => __('services').' | C.E.S. Container',
            'projects' => __('projects').' | C.E.S. Container',
            'gallery' => __('gallery').' | C.E.S. Container',
            'about' => __('about_title').' | C.E.S. Container',
            'contact' => __('contact').' | C.E.S. Container',
            'cart.index', 'checkout.form', 'checkout.store' => __('cart').' | C.E.S. Container',
            'checkout.success' => __('order_confirmed').' | C.E.S. Container',
            default => 'C.E.S. Container',
        };
    @endphp
    @hasSection('meta_title')
        <title>{{ $pageTitle }}</title>
    @else
        <title>{{ $pageTitle }}</title>
    @endif
    <meta name="description" content="@yield('meta_description', __('shop_seo_intro'))">
    <link rel="canonical" href="@yield('canonical', request()->url())">
    @if(request()->routeIs('cart.*', 'checkout.*', 'search') || request()->routeIs('shop') && request()->query())
        <meta name="robots" content="noindex,follow">
    @else
        <meta name="robots" content="@yield('robots', 'index,follow')">
    @endif
    <meta property="og:site_name" content="C.E.S. Container">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="@yield('meta_description', __('shop_seo_intro'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical', request()->url())">
    <meta property="og:image" content="@yield('og_image', asset('images/image_logo.jpeg'))">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('images/image_logo.jpeg') }}" type="image/jpeg">
    <link rel="icon" href="{{ asset('images/image_logo.jpeg') }}" type="image/jpeg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('structured-data')
</head>
<body>
<nav class="navbar navbar-expand-lg custom-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('images/image_logo.jpeg') }}" alt="C.E.S. Container" class="brand-logo">
            <span class="brand-text">C.E.S. CONTAINER</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                @php($nav=[['home',__('home')],['shop',__('shop')],['services',__('services')],['projects',__('projects')],['gallery',__('gallery')],['about',__('about')],['contact',__('contact')]])
                @foreach($nav as [$route,$label])
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}">{{ $label }}</a></li>
                @endforeach
                <li class="nav-item dropdown ms-lg-2">
                    <button class="btn btn-outline-success btn-sm dropdown-toggle language-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ strtoupper(app()->getLocale()) }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach(config('locales.available') as $locale => $meta)
                            <li>
                                <a class="dropdown-item d-flex justify-content-between {{ app()->getLocale() === $locale ? 'active' : '' }}" href="{{ route('locale.switch', $locale) }}">
                                    <span>{{ $meta['name'] }}</span><span class="small opacity-75">{{ $meta['flag'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-success btn-sm px-3 cart-button" href="{{ route('cart.index') }}">
                        {{ __('cart') }}
                        <span class="badge bg-light text-success">{{ app(\App\Services\CartService::class)->items()->sum('quantity') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

@if(session('success'))<div class="container mt-3"><div class="alert alert-success shadow-sm">{{ session('success') }}</div></div>@endif
@if(session('warning'))<div class="container mt-3"><div class="alert alert-warning shadow-sm" role="status">{{ session('warning') }}</div></div>@endif
@if($errors->any())<div class="container mt-3"><div class="alert alert-danger shadow-sm"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>@endif

@yield('content')

<footer class="footer py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="footer-brand">C.E.S. CONTAINER</div>
                <p class="mt-3 mb-0">{{ __('footer_intro') }}</p>
                <div class="social-links mt-4" aria-label="Social media">
                    <a class="social-link" href="https://wa.me/393505747539" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp" aria-hidden="true"></i><span class="visually-hidden">WhatsApp</span></a>
                    <a class="social-link" href="https://www.tiktok.com/@containerequipments?_r=1&_t=ZP-93kb9rxmBVX" target="_blank" rel="noopener" aria-label="TikTok"><i class="bi bi-tiktok" aria-hidden="true"></i><span class="visually-hidden">TikTok</span></a>
                    <a class="social-link" href="https://www.facebook.com/search/top?q=container%20equipment%20services" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook" aria-hidden="true"></i><span class="visually-hidden">Facebook</span></a>
                    <a class="social-link" href="https://www.instagram.com/containerequipments/" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram" aria-hidden="true"></i><span class="visually-hidden">Instagram</span></a>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <h6>{{ __('navigation') }}</h6>
                <ul class="list-unstyled">
                    <li><a href="{{ route('shop') }}">{{ __('shop') }}</a></li>
                    <li><a href="{{ route('services') }}">{{ __('services') }}</a></li>
                    <li><a href="{{ route('projects') }}">{{ __('projects') }}</a></li>
                    <li><a href="{{ route('gallery') }}">{{ __('gallery') }}</a></li>
                    <li><a href="{{ route('contact') }}">{{ __('contact') }}</a></li>
                </ul>
            </div>
            <div class="col-sm-6 col-lg-4">
                <h6>{{ __('need_information') }}</h6>
                <p>{{ __('footer_help') }}</p>
                <p class="small mb-2">{{ __('address_value') }}</p>
                <a class="btn btn-outline-light" href="{{ route('contact') }}">{{ __('contact_us') }}</a>
            </div>
        </div>
        <hr class="border-light opacity-25 my-4">
        <div class="small opacity-75">&copy; {{ date('Y') }} C.E.S. Container. {{ __('copyright') }}</div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const items = document.querySelectorAll('.section-reveal, .animated-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    items.forEach((item) => observer.observe(item));
});
</script>
@stack('scripts')
</body>
</html>
