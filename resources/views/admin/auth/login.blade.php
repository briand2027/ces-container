<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Connexion administration</title>
    <link rel="shortcut icon" href="{{ asset('images/image_logo.jpeg') }}" type="image/jpeg">
    <link rel="icon" href="{{ asset('images/image_logo.jpeg') }}" type="image/jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-login-page">
    <main class="admin-login-wrap">
        <section class="login-visual">
            <div class="login-brand">
                <img src="{{ asset('images/image_logo.jpeg') }}" alt="C.E.S. Container">
                <span>C.E.S. CONTAINER</span>
            </div>
            <div class="login-copy">
                <span class="admin-kicker">Administration</span>
                <h1>Pilotez votre catalogue, vos commandes et vos clients.</h1>
                <p>Un espace clair pour suivre les conteneurs, organiser les ventes et repondre aux demandes.</p>
            </div>
            <div class="login-stats">
                <div><strong>24/7</strong><span>Acces equipe</span></div>
                <div><strong>100%</strong><span>Gestion web</span></div>
                <div><strong>1</strong><span>Back-office</span></div>
            </div>
        </section>

        <section class="login-panel">
            <div class="login-card">
                <span class="admin-kicker">Connexion securisee</span>
                <h2>Bienvenue</h2>
                <p class="text-muted">Connectez-vous pour acceder a l'espace administration.</p>

                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="post" action="{{ route('admin.authenticate') }}">
                    @csrf
                    <label class="form-label" for="email">Email</label>
                    <div class="input-icon">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                    </div>

                    <label class="form-label" for="password">Mot de passe</label>
                    <div class="input-icon">
                        <i class="bi bi-lock" aria-hidden="true"></i>
                        <input id="password" type="password" name="password" class="form-control" required>
                    </div>

                    <button class="btn btn-success w-100 mt-4">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                        Se connecter
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
