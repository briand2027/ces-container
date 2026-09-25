<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Administration C.E.S.' }}</title>
    <link rel="shortcut icon" href="{{ asset('images/image_logo.jpeg') }}" type="image/jpeg">
    <link rel="icon" href="{{ asset('images/image_logo.jpeg') }}" type="image/jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-shell">
@php($adminNav = [
    ['admin.dashboard', 'Tableau de bord', 'bi-speedometer2', 'admin.dashboard'],
    ['admin.conteneurs.index', 'Conteneurs', 'bi-box-seam', 'admin.conteneurs.*'],
    ['admin.gallery', 'Galerie', 'bi-images', 'admin.gallery'],
    ['admin.categories.index', 'Categories', 'bi-diagram-3', 'admin.categories.*'],
    ['admin.services.index', 'Services', 'bi-tools', 'admin.services.*'],
    ['admin.clients.index', 'Clients', 'bi-people', 'admin.clients.*'],
    ['admin.orders.index', 'Commandes', 'bi-receipt', 'admin.orders.*'],
    ['admin.messages.index', 'Messages', 'bi-envelope', 'admin.messages.*'],
])

<div class="admin-backdrop" data-admin-menu-close></div>
<aside class="admin-sidebar" id="adminSidebar">
    <a class="admin-brand" href="{{ route('admin.dashboard') }}">
        <img src="{{ asset('images/image_logo.jpeg') }}" alt="C.E.S.">
        <span>C.E.S.<small>Administration</small></span>
    </a>
    <nav class="admin-menu" aria-label="Administration">
        @foreach($adminNav as [$route, $label, $icon, $match])
            <a class="{{ request()->routeIs($match) ? 'active' : '' }}" href="{{ route($route) }}">
                <i class="bi {{ $icon }}" aria-hidden="true"></i>
                <span>{{ $label }}</span>
                @if($route==='admin.messages.index')<span class="admin-unread-badge" data-message-count hidden>0</span>@endif
            </a>
        @endforeach
        @if(session('admin_role') === 'super_admin')
            <a class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">
                <i class="bi bi-gear" aria-hidden="true"></i>
                <span>Parametres</span>
            </a>
        @endif
    </nav>
</aside>

<div class="admin-main">
    <header class="admin-topbar">
        <button class="admin-menu-toggle" type="button" data-admin-menu-toggle aria-label="Ouvrir le menu"><i class="bi bi-list"></i></button>
        <div>
            <span class="admin-kicker">Container Equipment Services</span>
            <strong>Gestion complete du site</strong>
        </div>
        <div class="admin-user">
            <a class="admin-message-link" href="{{ route('admin.messages.index') }}" title="Ouvrir les messages" aria-label="Messages non lus"><i class="bi bi-envelope"></i><span data-message-count hidden>0</span></a>
            <button class="admin-sound-toggle" type="button" data-sound-toggle aria-pressed="false" title="Activer le signal sonore des nouveaux messages"><i class="bi bi-bell-slash"></i><span class="visually-hidden">Activer le signal sonore des nouveaux messages</span></button>
            <span>{{ session('admin_prenom') }} {{ session('admin_nom') }}</span>
            <small>{{ session('admin_role') }}</small>
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Deconnexion</button>
            </form>
        </div>
    </header>

    <main class="admin-content">
        @if(session('success'))
            <div class="alert alert-success admin-alert">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger admin-alert">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-admin-menu-toggle]'), sidebar = document.querySelector('#adminSidebar');
    const closeMenu = () => document.body.classList.remove('admin-menu-open');
    toggle?.addEventListener('click', () => document.body.classList.toggle('admin-menu-open'));
    document.querySelector('[data-admin-menu-close]')?.addEventListener('click', closeMenu);
    sidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
    const soundButton=document.querySelector('[data-sound-toggle]');
    const unreadUrl=@json(route('admin.messages.unread-count'));
    let previousLatest=null, hasInitialSnapshot=false, audioContext=null, soundEnabled=sessionStorage.getItem('admin-message-sound')!=='off';
    const updateSoundButton=()=>{if(!soundButton)return;soundButton.setAttribute('aria-pressed',String(soundEnabled));soundButton.title=soundEnabled?'Désactiver le signal sonore':'Activer le signal sonore des nouveaux messages';soundButton.querySelector('i').className=soundEnabled?'bi bi-bell-fill':'bi bi-bell-slash';};
    const unlockAudio=async()=>{const AudioCtor=window.AudioContext||window.webkitAudioContext;if(!AudioCtor)return;audioContext??=new AudioCtor();if(audioContext.state==='suspended')await audioContext.resume();};
    const playBell=()=>{if(!soundEnabled||!audioContext||audioContext.state!=='running')return;[[740,0],[988,.17]].forEach(([frequency,delay])=>{const oscillator=audioContext.createOscillator(),gain=audioContext.createGain();oscillator.type='sine';oscillator.frequency.value=frequency;gain.gain.setValueAtTime(.0001,audioContext.currentTime+delay);gain.gain.exponentialRampToValueAtTime(.18,audioContext.currentTime+delay+.025);gain.gain.exponentialRampToValueAtTime(.0001,audioContext.currentTime+delay+.42);oscillator.connect(gain).connect(audioContext.destination);oscillator.start(audioContext.currentTime+delay);oscillator.stop(audioContext.currentTime+delay+.45);});};
    soundButton?.addEventListener('click',async()=>{soundEnabled=!soundEnabled;sessionStorage.setItem('admin-message-sound',soundEnabled?'on':'off');if(soundEnabled)await unlockAudio();updateSoundButton();});updateSoundButton();
    if(soundEnabled){document.addEventListener('pointerdown',unlockAudio,{once:true});document.addEventListener('keydown',unlockAudio,{once:true});}
    const refreshUnread=async()=>{try{const response=await fetch(unreadUrl,{headers:{'Accept':'application/json'},credentials:'same-origin'});if(!response.ok)return;const data=await response.json();document.querySelectorAll('[data-message-count]').forEach(item=>{item.textContent=data.count>99?'99+':data.count;item.hidden=data.count===0;item.setAttribute('aria-label',`${data.count} message(s) non lu(s)`)});if(hasInitialSnapshot&&data.latest_id&&(previousLatest===null||Number(data.latest_id)>Number(previousLatest))){if(soundEnabled){await unlockAudio();playBell();}if('Notification'in window&&Notification.permission==='granted')new Notification('Nouveau message reçu',{body:'Un message client attend votre réponse.'});}previousLatest=data.latest_id;hasInitialSnapshot=true;}catch(error){/* Keep the admin screen usable when polling is temporarily unavailable. */}};
    refreshUnread();window.setInterval(refreshUnread,15000);
    const animated = document.querySelectorAll('.card, .admin-page-head, .table-responsive, form.card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });
    animated.forEach((item) => observer.observe(item));
});
</script>
@stack('scripts')
</body>
</html>
