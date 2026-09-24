<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model
{
    protected $table='commandes'; protected $guarded=[];
    public function client(){return $this->belongsTo(Client::class,'client_id');}
    public function lines(){return $this->hasMany(OrderLine::class,'commande_id');}
    public function payments(){return $this->hasMany(Payment::class,'commande_id');}
}
