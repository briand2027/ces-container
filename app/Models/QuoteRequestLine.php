<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteRequestLine extends Model
{
    protected $table = 'lignes_demandes_devis';
    protected $guarded = [];

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class, 'demande_devis_id');
    }
}
