<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model
{
    protected $table='paiements'; protected $guarded=[];
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;
    protected $casts=['date_paiement'=>'datetime'];
    public function order(){return $this->belongsTo(Order::class,'commande_id');}
}
