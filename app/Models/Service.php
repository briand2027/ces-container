<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Service extends Model
{
    protected $table='services'; protected $guarded=[];

    public function getPrixAttribute(): mixed
    {
        return $this->attributes['prix'] ?? $this->attributes['prix_unitaire'] ?? null;
    }
}
