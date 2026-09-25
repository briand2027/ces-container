@extends('admin.layouts.app')

@section('content')
<div class="admin-page-head">
    <div>
        <span class="admin-kicker">Relation client</span>
        <h1>Messages reçus</h1>
        <p>Les nouveaux messages apparaissent ici et déclenchent l’alerte du navigateur.</p>
    </div>
</div>

<div class="admin-stat-grid mb-4">
    @foreach([['Tous',$stats['total'],'bi-inbox','text-success'],['Non lus',$stats['non_lu'],'bi-envelope-exclamation','text-danger'],['Lus',$stats['lu'],'bi-envelope-open','text-primary'],['Répondus',$stats['repondu'],'bi-reply','text-warning']] as [$label,$value,$icon,$color])
        <div class="admin-stat-card"><i class="bi {{ $icon }} {{ $color }}"></i><div><small>{{ $label }}</small><strong>{{ $value }}</strong></div></div>
    @endforeach
</div>

<form method="get" class="card p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-6"><label for="message-search" class="form-label small fw-bold">Recherche</label><input id="message-search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nom, e-mail, sujet ou contenu"></div>
        <div class="col-md-3"><label for="message-status" class="form-label small fw-bold">État</label><select id="message-status" name="statut" class="form-select"><option value="">Tous les messages</option>@foreach(['non_lu'=>'Non lus','lu'=>'Lus','repondu'=>'Répondus'] as $value=>$label)<option value="{{ $value }}" @selected(request('statut')===$value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-auto"><button class="btn btn-success"><i class="bi bi-funnel"></i> Filtrer</button></div>
        <div class="col-auto"><a class="btn btn-outline-secondary" href="{{ route('admin.messages.index') }}" title="Réinitialiser"><i class="bi bi-arrow-counterclockwise"></i></a></div>
    </div>
</form>

<form method="post" action="{{ route('admin.messages.bulk-destroy') }}" class="card border-0 shadow-sm" onsubmit="return confirm('Supprimer les messages sélectionnés ?')">
    @csrf
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><strong>{{ $items->total() }}</strong> message(s)</span>
        <button id="deleteSelectedMessages" class="btn btn-sm btn-outline-danger" disabled><i class="bi bi-trash"></i> Supprimer la sélection</button>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th><input id="selectAllMessages" class="form-check-input" type="checkbox" aria-label="Tout sélectionner sur cette page"></th><th>Expéditeur</th><th>Sujet et message</th><th>Reçu le</th><th>État</th><th></th></tr></thead>
            <tbody>
                @forelse($items as $message)
                    <tr class="{{ $message->statut==='non_lu'?'message-row-unread':'' }}">
                        <td><input class="form-check-input message-check" type="checkbox" name="selected_ids[]" value="{{ $message->id }}" aria-label="Sélectionner le message de {{ $message->prenom }} {{ $message->nom }}"></td>
                        <td><strong>{{ $message->prenom }} {{ $message->nom }}</strong><a class="small d-block" href="mailto:{{ $message->email }}">{{ $message->email }}</a>@if($message->telephone)<small class="text-muted">{{ $message->telephone }}</small>@endif</td>
                        <td><a class="message-subject" href="{{ route('admin.messages.show',$message) }}">{{ $message->sujet }}</a><small class="message-preview">{{ \Illuminate\Support\Str::limit($message->message,100) }}</small></td>
                        <td>{{ optional($message->date_creation)->format('d/m/Y H:i') }}</td>
                        <td><span class="status-pill {{ $message->statut==='non_lu'?'status-unread':($message->statut==='repondu'?'status-replied':'') }}">{{ $message->statut==='non_lu'?'Non lu':($message->statut==='repondu'?'Répondu':'Lu') }}</span></td>
                        <td class="text-end"><div class="d-inline-flex gap-1"><button type="button" class="btn btn-sm btn-outline-secondary" title="{{ $message->statut==='non_lu'?'Marquer comme lu':'Marquer comme non lu' }}" onclick="document.getElementById('toggle-read-{{ $message->id }}').submit()"><i class="bi {{ $message->statut==='non_lu'?'bi-envelope-open':'bi-envelope' }}"></i></button><a class="btn btn-sm btn-outline-success" href="{{ route('admin.messages.show',$message) }}" title="Ouvrir"><i class="bi bi-arrow-up-right"></i></a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">Aucun message trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

@if($items->hasPages())
    <nav class="admin-pagination" aria-label="Pagination des messages">
        <div class="admin-pagination-summary">Affichage de <strong>{{ $items->firstItem() }}–{{ $items->lastItem() }}</strong> sur <strong>{{ $items->total() }}</strong> messages</div>
        <div class="admin-pagination-links">
            @if($items->onFirstPage())<span class="page-control disabled"><i class="bi bi-chevron-left"></i> Précédent</span>@else<a class="page-control" href="{{ $items->previousPageUrl() }}"><i class="bi bi-chevron-left"></i> Précédent</a>@endif
            @foreach($items->getUrlRange(max(1,$items->currentPage()-2),min($items->lastPage(),$items->currentPage()+2)) as $page=>$url)<a class="page-number {{ $page===$items->currentPage()?'active':'' }}" href="{{ $url }}" @if($page===$items->currentPage()) aria-current="page" @endif>{{ $page }}</a>@endforeach
            @if($items->hasMorePages())<a class="page-control" href="{{ $items->nextPageUrl() }}">Suivant <i class="bi bi-chevron-right"></i></a>@else<span class="page-control disabled">Suivant <i class="bi bi-chevron-right"></i></span>@endif
        </div>
    </nav>
@endif

@foreach($items as $message)
    <form id="toggle-read-{{ $message->id }}" method="post" action="{{ route('admin.messages.toggle-read',$message) }}" class="d-none">@csrf</form>
@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const all = document.querySelector('#selectAllMessages');
    const boxes = [...document.querySelectorAll('.message-check')];
    const button = document.querySelector('#deleteSelectedMessages');
    const sync = () => {
        button.disabled = !boxes.some((box) => box.checked);
        all.checked = boxes.length > 0 && boxes.every((box) => box.checked);
    };
    all?.addEventListener('change', () => {
        boxes.forEach((box) => { box.checked = all.checked; });
        sync();
    });
    boxes.forEach((box) => box.addEventListener('change', sync));
});
</script>
@endpush
@endsection
