@extends('admin.layouts.app')

@section('content')
<div class="admin-page-head">
    <div>
        <span class="admin-kicker">Surveillance du site</span>
        <h1>Journal d’activité</h1>
        <p>Actions récentes et incidents techniques enregistrés par l’application.</p>
    </div>
</div>

<div class="admin-stat-grid mb-4">
    <div class="admin-stat-card"><i class="bi bi-activity text-primary"></i><div><small>Événements consultables</small><strong>{{ $activityCount + $incidentCount }}</strong></div></div>
    <div class="admin-stat-card"><i class="bi bi-check2-circle text-success"></i><div><small>Actions</small><strong>{{ $activityCount }}</strong></div></div>
    <div class="admin-stat-card"><i class="bi bi-exclamation-triangle text-danger"></i><div><small>Incidents</small><strong>{{ $incidentCount }}</strong></div></div>
</div>

<form method="get" action="{{ route('admin.activity.index') }}" class="card p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-sm-6 col-md-4">
            <label for="activity-type" class="form-label small fw-bold">Type d’événement</label>
            <select id="activity-type" name="type" class="form-select">
                <option value="all" @selected($type === 'all')>Toutes les entrées</option>
                <option value="activity" @selected($type === 'activity')>Actions</option>
                <option value="incidents" @selected($type === 'incidents')>Incidents</option>
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-success"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrer</button></div>
        <div class="col-auto"><a class="btn btn-outline-secondary" href="{{ route('admin.activity.index') }}" title="Réinitialiser le filtre" aria-label="Réinitialiser le filtre"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></a></div>
    </div>
</form>

<section class="card border-0 shadow-sm">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <strong>{{ $logs->total() }} événement(s)</strong>
        <span class="small text-muted">14 derniers jours · limite de 5 000 événements</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Date</th><th>Événement</th><th>Action / URL</th><th>Résultat</th><th>Cause</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log['timestamp'] }}</td>
                        <td>
                            <span class="badge {{ $log['type'] === 'incidents' ? 'text-bg-danger' : 'text-bg-primary' }}">{{ $log['type'] === 'incidents' ? 'Incident' : 'Action' }}</span>
                            <small class="d-block text-muted mt-1">{{ $log['message'] }}</small>
                            @if($log['exception'])<small class="d-block text-muted">{{ $log['exception'] }}</small>@endif
                        </td>
                        <td>
                            @if($log['method'])<span class="badge text-bg-light border">{{ $log['method'] }}</span>@endif
                            <strong class="d-block mt-1">{{ $log['path'] ?: '—' }}</strong>
                            <small class="d-block text-muted">{{ $log['route'] ?: '—' }}</small>
                            @if($log['action'] && $log['action'] !== 'unmatched route')<small class="d-block text-muted">{{ $log['action'] }}</small>@endif
                            @if($log['operation'])<small class="d-block text-muted">{{ $log['operation'] }}</small>@endif
                            @if($log['message_id'])<small class="d-block text-muted">Message #{{ $log['message_id'] }}</small>@endif
                            @if($log['order_id'])<small class="d-block text-muted">Commande #{{ $log['order_id'] }}</small>@endif
                            @if($log['admin_id'])<small class="d-block text-muted">Admin #{{ $log['admin_id'] }}</small>@endif
                        </td>
                        <td>
                            @if($log['status'])
                                <span class="badge {{ $log['status'] >= 400 ? 'text-bg-danger' : 'text-bg-success' }}">HTTP {{ $log['status'] }}</span>
                            @else
                                <span class="badge text-bg-secondary">{{ $log['level'] }}</span>
                            @endif
                            <small class="d-block text-muted mt-1">{{ $log['file'] }}</small>
                            @if($log['source_file'])<small class="d-block text-muted">{{ $log['source_file'] }}:{{ $log['source_line'] }}</small>@endif
                        </td>
                        <td class="log-cause-cell">{{ $log['cause'] ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">Aucune entrée pour ce filtre.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if($logs->hasPages())
    <nav class="admin-pagination" aria-label="Pagination du journal">
        <div class="admin-pagination-summary">Affichage de <strong>{{ $logs->firstItem() }}–{{ $logs->lastItem() }}</strong> sur <strong>{{ $logs->total() }}</strong> événements</div>
        <div class="admin-pagination-links">
            @if($logs->onFirstPage())<span class="page-control disabled"><i class="bi bi-chevron-left"></i> Précédent</span>@else<a class="page-control" href="{{ $logs->previousPageUrl() }}"><i class="bi bi-chevron-left"></i> Précédent</a>@endif
            @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                <a class="page-number {{ $page === $logs->currentPage() ? 'active' : '' }}" href="{{ $url }}" @if($page === $logs->currentPage()) aria-current="page" @endif>{{ $page }}</a>
            @endforeach
            @if($logs->hasMorePages())<a class="page-control" href="{{ $logs->nextPageUrl() }}">Suivant <i class="bi bi-chevron-right"></i></a>@else<span class="page-control disabled">Suivant <i class="bi bi-chevron-right"></i></span>@endif
        </div>
    </nav>
@endif
@endsection
