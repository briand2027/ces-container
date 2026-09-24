@extends('admin.layouts.app')

@section('content')
<div class="admin-page-head">
    <div><span class="admin-kicker">Catalogue / Conteneurs</span><h1>{{ $item->exists ? 'Modifier le conteneur' : 'Ajouter un conteneur' }}</h1><p>Informations, descriptions traduites, tarifs et photos du produit.</p></div>
    <a href="{{ route('admin.conteneurs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Retour à la liste</a>
</div>

<form method="post" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.conteneurs.update',$item) : route('admin.conteneurs.store') }}" id="containerForm">
    @csrf @if($item->exists) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-xl-8">
            <section class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3"><h2 class="h5 mb-0"><i class="bi bi-info-circle text-success me-2"></i>Informations générales</h2></div>
                <div class="card-body"><div class="row g-3">
                    <div class="col-md-6"><label for="reference" class="form-label">Référence <span class="text-danger">*</span></label><input id="reference" name="reference" class="form-control" value="{{ old('reference',$item->reference) }}" placeholder="Ex. CONT-2026-001" required></div>
                    <div class="col-md-6"><label for="categorie_id" class="form-label">Catégorie <span class="text-danger">*</span></label><select id="categorie_id" name="categorie_id" class="form-select" required><option value="">Choisir une catégorie</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('categorie_id',$item->categorie_id)==$category->id)>{{ $category->nom }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label for="type_conteneur" class="form-label">Type de conteneur <span class="text-danger">*</span></label><select id="type_conteneur" name="type_conteneur" class="form-select" required>@foreach(['standard'=>'Standard','reefer'=>'Frigorifique (Reefer)','open_top'=>'Open Top','flat_rack'=>'Flat Rack','tank'=>'Tank','mobil_home'=>'Mobil-Home','batimoduli'=>'Batimoduli','bungalow'=>'Bungalow','mobil_bar'=>'Mobil-bar'] as $value=>$label)<option value="{{ $value }}" @selected(old('type_conteneur',$item->type_conteneur ?: 'standard')===$value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label for="etat" class="form-label">État</label><select id="etat" name="etat" class="form-select"><option value="">À préciser</option>@foreach(['neuf'=>'Neuf','occasion'=>'Occasion','reconditionne'=>'Reconditionné'] as $value=>$label)<option value="{{ $value }}" @selected(old('etat',$item->etat)===$value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label for="pieds" class="form-label">Dimension en pieds</label><select id="pieds" name="pieds" class="form-select"><option value="">À préciser</option>@for($feet=1;$feet<=45;$feet++)<option value="{{ $feet }}" @selected((string)old('pieds',$item->pieds)===(string)$feet)>{{ $feet }} pieds</option>@endfor<option value="autre" @selected(old('pieds',$item->pieds)==='autre')>Autre dimension</option></select></div>
                    <div class="col-md-6"><label for="type_pied" class="form-label">Type / configuration</label><select id="type_pied" name="type_pied" class="form-select"><option value="">À préciser</option>@foreach(['DC'=>'DC — Double Container','DD'=>'DD — Double Door','HC'=>'HC — High Cube','STD'=>'STD — Standard','OT'=>'OT — Open Top','FR'=>'FR — Flat Rack','REF'=>'REF — Reefer','PW'=>'PW — Pallet Wide','autre'=>'Autre'] as $value=>$label)<option value="{{ $value }}" @selected(old('type_pied',$item->type_pied)===$value)>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label for="dimensions" class="form-label">Dimensions (format libre)</label><input id="dimensions" name="dimensions" class="form-control" value="{{ old('dimensions',$item->dimensions) }}" placeholder="Ex. 20HC, 40HC"></div>
                    <div class="col-md-6"><label for="annee_fabrication" class="form-label">Année de fabrication</label><input id="annee_fabrication" type="number" min="1900" max="2100" name="annee_fabrication" class="form-control" value="{{ old('annee_fabrication',$item->annee_fabrication) }}"></div>
                    <div class="col-sm-4"><label for="longueur" class="form-label">Longueur (m)</label><input id="longueur" type="number" step="0.01" min="0" name="longueur" class="form-control" value="{{ old('longueur',$item->longueur) }}"></div>
                    <div class="col-sm-4"><label for="largeur" class="form-label">Largeur (m)</label><input id="largeur" type="number" step="0.01" min="0" name="largeur" class="form-control" value="{{ old('largeur',$item->largeur) }}"></div>
                    <div class="col-sm-4"><label for="hauteur" class="form-label">Hauteur (m)</label><input id="hauteur" type="number" step="0.01" min="0" name="hauteur" class="form-control" value="{{ old('hauteur',$item->hauteur) }}"></div>
                    <div class="col-md-6"><label for="poids" class="form-label">Poids (kg)</label><input id="poids" type="number" step="0.01" min="0" name="poids" class="form-control" value="{{ old('poids',$item->poids) }}"></div>
                    <div class="col-md-6"><label for="capacite" class="form-label">Capacité (m³)</label><input id="capacite" type="number" step="0.01" min="0" name="capacite" class="form-control" value="{{ old('capacite',$item->capacite) }}"></div>
                </div></div>
            </section>

            <section class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2"><h2 class="h5 mb-0"><i class="bi bi-translate text-success me-2"></i>Description par langue</h2><span class="translation-count"><span id="translationCount">0</span> / {{ count(config('locales.available')) }} langues renseignées</span></div>
                <div class="card-body">
                    <ul class="nav nav-pills translation-tabs mb-3" id="descriptionTabs" role="tablist">
                        @foreach(config('locales.available') as $locale=>$meta)
                            <li class="nav-item" role="presentation"><button class="nav-link {{ $locale==='it'?'active':'' }}" id="tab-{{ $locale }}" data-bs-toggle="tab" data-bs-target="#description-{{ $locale }}" type="button" role="tab" aria-controls="description-{{ $locale }}" aria-selected="{{ $locale==='it'?'true':'false' }}"><span class="translation-code">{{ $meta['flag'] }}</span><span>{{ $meta['name'] }}</span>@if($locale==='it')<i class="bi bi-asterisk text-danger translation-required" title="Obligatoire"></i>@endif</button></li>
                        @endforeach
                    </ul>
                    <div class="tab-content translation-content" id="descriptionTabContent">
                        @foreach(config('locales.available') as $locale=>$meta)
                            @php($field=$locale==='it'?'description':'description_'.$locale)
                            <div class="tab-pane fade {{ $locale==='it'?'show active':'' }}" id="description-{{ $locale }}" role="tabpanel" aria-labelledby="tab-{{ $locale }}" tabindex="0">
                                <label for="{{ $field }}" class="form-label">Description en {{ $meta['name'] }} @if($locale==='it')<span class="text-danger">*</span>@else<span class="text-muted fw-normal">(facultatif)</span>@endif</label>
                                <textarea id="{{ $field }}" name="{{ $field }}" class="form-control translation-textarea" rows="7" @required($locale==='it') placeholder="{{ $locale==='it'?'Inserisci una descrizione dettagliata del prodotto…':'Ajoutez la traduction dans cette langue…' }}">{{ old($field,$item->{$field}) }}</textarea>
                                @if($locale==='it')<div class="form-text">L’italien sert de description principale et de solution de repli lorsqu’une traduction manque.</div>@endif
                            </div>
                        @endforeach
                    </div>
                    <div class="translation-progress mt-3"><div class="d-flex justify-content-between small text-muted mb-1"><span>Descriptions renseignées</span><span id="translationPercent">0%</span></div><div class="progress" role="progressbar" aria-label="Progression des traductions"><div class="progress-bar bg-success" id="translationProgressBar" style="width:0%"></div></div></div>
                </div>
            </section>

            <section class="card border-0 shadow-sm">
                <div class="card-header py-3"><h2 class="h5 mb-0"><i class="bi bi-list-check text-success me-2"></i>Caractéristiques</h2></div>
                <div class="card-body"><div class="row g-3">@foreach(['matiere'=>'Matière','couleur'=>'Couleur','portes'=>'Portes'] as $key=>$label)<div class="col-md-4"><label for="carac-{{ $key }}" class="form-label">{{ $label }}</label><input id="carac-{{ $key }}" name="caracteristiques[{{ $key }}]" class="form-control" value="{{ old('caracteristiques.'.$key,data_get($item->caracteristiques,$key)) }}" placeholder="{{ $key==='matiere'?'Ex. acier Corten':($key==='portes'?'Ex. 2 portes coulissantes':$label) }}"></div>@endforeach</div></div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="card border-0 shadow-sm mb-4"><div class="card-header py-3"><h2 class="h5 mb-0"><i class="bi bi-currency-euro text-success me-2"></i>Prix et stock</h2></div><div class="card-body">
                <label for="prix_achat" class="form-label">Prix d’achat (€)</label><input id="prix_achat" type="number" step="0.01" min="0" name="prix_achat" class="form-control mb-3" value="{{ old('prix_achat',$item->prix_achat) }}">
                <label for="prix_vente" class="form-label">Prix de vente (€) <span class="text-danger">*</span></label><input id="prix_vente" type="number" step="0.01" min="0" name="prix_vente" class="form-control mb-3" value="{{ old('prix_vente',$item->prix_vente) }}" required>
                <label for="prix_location_jour" class="form-label">Location par jour (€)</label><input id="prix_location_jour" type="number" step="0.01" min="0" name="prix_location_jour" class="form-control mb-3" value="{{ old('prix_location_jour',$item->prix_location_jour) }}">
                <div class="row g-3"><div class="col-6"><label for="quantite_stock" class="form-label">Stock <span class="text-danger">*</span></label><input id="quantite_stock" type="number" min="0" name="quantite_stock" class="form-control" value="{{ old('quantite_stock',$item->quantite_stock ?? 0) }}" required></div><div class="col-6"><label for="quantite_min_alerte" class="form-label">Seuil d’alerte</label><input id="quantite_min_alerte" type="number" min="0" name="quantite_min_alerte" class="form-control" value="{{ old('quantite_min_alerte',$item->quantite_min_alerte ?? 0) }}"></div></div>
            </div></section>

            <section class="card border-0 shadow-sm mb-4"><div class="card-header py-3"><h2 class="h5 mb-0"><i class="bi bi-globe2 text-success me-2"></i>Publication</h2></div><div class="card-body"><label for="statut" class="form-label">Statut</label><select id="statut" name="statut" class="form-select mb-3">@foreach(['disponible'=>'Disponible','vendu'=>'Vendu','loue'=>'Loué','maintenance'=>'Maintenance'] as $value=>$label)<option value="{{ $value }}" @selected(old('statut',$item->statut ?: 'disponible')===$value)>{{ $label }}</option>@endforeach</select><div class="form-check"><input id="certificat_csc" class="form-check-input" type="checkbox" name="certificat_csc" value="1" @checked(old('certificat_csc',$item->certificat_csc))><label class="form-check-label" for="certificat_csc">Certificat CSC disponible</label></div></div></section>

            <section class="card border-0 shadow-sm"><div class="card-header py-3"><h2 class="h5 mb-0"><i class="bi bi-images text-success me-2"></i>Photos</h2></div><div class="card-body">
                <label for="image_principale" class="form-label">Image principale @unless($item->exists)<span class="text-danger">*</span>@endunless</label><input id="image_principale" type="file" name="image_principale" class="form-control mb-2" accept="image/jpeg,image/png,image/webp,image/avif" @required(!$item->exists)><div class="form-text mb-3">JPEG, PNG, WebP ou AVIF, 5 Mo maximum.</div>
                <div id="mainImagePreview" class="container-form-preview mb-3">@if($item->image_principale)<img src="{{ asset($item->image_principale) }}" alt="Image principale actuelle">@else<div class="container-form-placeholder"><i class="bi bi-image"></i><span>Aperçu de l’image principale</span></div>@endif</div>
                <label for="images_secondaires" class="form-label">Images secondaires @unless($item->exists)<span class="text-danger">(1 à 4 requises)</span>@endunless</label><input id="images_secondaires" type="file" name="images_secondaires[]" class="form-control mb-2" accept="image/jpeg,image/png,image/webp,image/avif" multiple @required(!$item->exists)><div class="form-text mb-3">Jusqu’à 4 images. À l’ajout, sélectionnez au moins une image.</div>
                @if($item->exists && count((array)$item->images_secondaires))<div class="row g-2 mb-3">@foreach((array)$item->images_secondaires as $image)<div class="col-6"><img src="{{ asset($image) }}" class="img-fluid container-existing-image" alt="Photo secondaire existante"></div>@endforeach</div>@endif
                <div id="secondaryImagePreview" class="row g-2"></div>
            </div></section>
        </div>
    </div>
    <div class="admin-form-actions mt-4"><a href="{{ route('admin.conteneurs.index') }}" class="btn btn-outline-secondary">Annuler</a><button type="submit" class="btn btn-success"><i class="bi bi-check2-circle me-1"></i>{{ $item->exists ? 'Enregistrer les modifications' : 'Ajouter le conteneur' }}</button></div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const textareas = [...document.querySelectorAll('.translation-textarea')];
    const count = document.querySelector('#translationCount');
    const percent = document.querySelector('#translationPercent');
    const bar = document.querySelector('#translationProgressBar');
    const updateProgress = () => {
        const filled = textareas.filter(field => field.value.trim().length > 0).length;
        const ratio = Math.round(filled * 100 / textareas.length);
        count.textContent = filled; percent.textContent = ratio + '%'; bar.style.width = ratio + '%';
    };
    textareas.forEach(field => field.addEventListener('input', updateProgress)); updateProgress();

    const previewFiles = (input, target, limit) => {
        const files = [...input.files];
        target.replaceChildren();
        if (files.length > limit) { alert(`Sélectionnez ${limit} images maximum.`); input.value = ''; return; }
        files.forEach(file => {
            if (!file.type.startsWith('image/')) return;
            const column = document.createElement('div'); column.className = 'col-6';
            const image = document.createElement('img'); image.className = 'img-fluid container-existing-image'; image.alt = 'Aperçu de l’image sélectionnée'; image.src = URL.createObjectURL(file);
            column.append(image); target.append(column);
        });
    };
    document.querySelector('#images_secondaires').addEventListener('change', event => previewFiles(event.currentTarget, document.querySelector('#secondaryImagePreview'), 4));
    document.querySelector('#image_principale').addEventListener('change', event => {
        const preview = document.querySelector('#mainImagePreview'), file = event.currentTarget.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) { event.currentTarget.value = ''; return; }
        const image = document.createElement('img'); image.alt = 'Aperçu de l’image principale'; image.src = URL.createObjectURL(file); preview.replaceChildren(image);
    });
});
</script>
@endsection
