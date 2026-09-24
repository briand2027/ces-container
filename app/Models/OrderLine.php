<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderLine extends Model
{
    protected $table='ligne_commandes'; public $timestamps=false; protected $guarded=[];
    public function order(){return $this->belongsTo(Order::class,'commande_id');}
    public function container(){return $this->belongsTo(Container::class,'conteneur_id');}
    public function service(){return $this->belongsTo(Service::class,'service_id');}
}
