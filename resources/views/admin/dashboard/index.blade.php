@extends('admin.layouts.app')

@section('content')
<div class="admin-page-head">
    <div>
        <span class="admin-kicker">Vue d'ensemble</span>
        <h1>Tableau de bord</h1>
        <p>Suivez l'activite commerciale, les paiements et les demandes clients.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-success" href="{{ route('admin.conteneurs.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nouveau conteneur</a>
        <a class="btn btn-outline-secondary" href="{{ route('admin.orders.index') }}">Voir les commandes</a>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['Produits', $stats['products'], 'Catalogue disponible', 'bi-box-seam'],
        ['Commandes', $stats['orders'], 'Toutes commandes', 'bi-receipt'],
        ['Messages non lus', $stats['messages'], 'A traiter', 'bi-envelope-exclamation'],
        ['Paiements en attente', $stats['pending'], 'Virements a verifier', 'bi-bank'],
    ] as [$label, $value, $hint, $icon])
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 admin-stat-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="text-muted fw-semibold">{{ $label }}</span>
                        <span class="admin-stat-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                    </div>
                    <div class="display-6 fw-bold mt-3">{{ $value }}</div>
                    <div class="small text-muted">{{ $hint }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h5 mb-1">Dernieres commandes</h2>
                <p class="small text-muted mb-0">Les 8 dernieres commandes enregistrees.</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-success">Tout afficher</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>N°</th><th>Client</th><th>Total TTC</th><th>Paiement</th><th>Commande</th></tr>
            </thead>
            <tbody>
            @forelse($orders as $o)
                <tr>
                    <td><a class="fw-semibold text-decoration-none" href="{{ route('admin.orders.show',$o) }}">{{ $o->numero_commande }}</a></td>
                    <td>{{ trim(($o->client->prenom ?? '').' '.($o->client->nom ?? '')) ?: ($o->client->email ?? '—') }}</td>
                    <td class="fw-semibold">{{ number_format($o->total_ttc,2,',',' ') }} €</td>
                    <td><span class="badge rounded-pill {{ $o->statut_paiement==='paye'?'text-bg-success':'text-bg-warning' }}">{{ $o->statut_paiement }}</span></td>
                    <td><span class="badge rounded-pill text-bg-light border">{{ $o->statut_commande }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center py-5 text-muted">Aucune commande.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
