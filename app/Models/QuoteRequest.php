<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteRequest extends Model
{
    protected $table = 'demandes_devis';
    protected $guarded = [];

    public function lines(): HasMany
    {
        return $this->hasMany(QuoteRequestLine::class, 'demande_devis_id');
    }
}
