<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteRequest extends Model
{
    protected $table = 'demandes_devis';
    protected $guarded = [];
    protected $casts = ['conditions_acceptees_at' => 'datetime'];

    public function lines(): HasMany
    {
        return $this->hasMany(QuoteRequestLine::class, 'demande_devis_id');
    }
}
