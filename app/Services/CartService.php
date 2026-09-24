<?php
namespace App\Services;
use App\Models\Container;
use App\Models\SystemSetting;
use Illuminate\Support\Collection;
class CartService
{
    private const KEY='cart';
    public function raw(): array { return session(self::KEY, []); }
    public function add(int $id,int $quantity=1,string $type='vente'): void {
        $cart=$this->raw(); $key=(string)$id;
        $cart[$key]=['quantity'=>min(100,($cart[$key]['quantity']??0)+$quantity),'type'=>$type];
        session([self::KEY=>$cart]);
    }
    public function update(int $id,int $quantity): void {
        $cart=$this->raw(); if(isset($cart[(string)$id])){$cart[(string)$id]['quantity']=$quantity;session([self::KEY=>$cart]);}
    }
    public function remove(int $id): void {$cart=$this->raw();unset($cart[(string)$id]);session([self::KEY=>$cart]);}
    public function clear(): void {session()->forget(self::KEY);}
    public function items(): Collection {
        $cart=$this->raw(); if(!$cart)return collect();
        $products=Container::whereIn('id',array_keys($cart))->where('statut','disponible')->get()->keyBy(fn($p)=>(string)$p->id);
        return collect($cart)->map(function($row,$id)use($products){
            $p=$products->get((string)$id); if(!$p)return null;
            $type=($row['type']??'vente')==='location'?'location':'vente';
            $unit=$type==='location'?(float)$p->prix_location_jour:(float)$p->prix_vente;
            return ['container'=>$p,'quantity'=>max(1,(int)$row['quantity']),'type'=>$type,'unit_price'=>$unit,'total'=>$unit*max(1,(int)$row['quantity'])];
        })->filter()->values();
    }
    public function validatedLines(): Collection {
        $items=$this->items();
        if($items->isEmpty()) throw new \RuntimeException('Votre panier est vide.');
        foreach($items as $item){
            if($item['quantity']>$item['container']->quantite_stock) throw new \RuntimeException('Stock insuffisant pour '.$item['container']->reference);
            if($item['type']==='vente' && (float)$item['container']->prix_vente <= 0) throw new \RuntimeException('Le prix de vente de '.$item['container']->reference.' est indisponible.');
            if($item['type']==='location' && (float)$item['container']->prix_location_jour <= 0) throw new \RuntimeException('Le tarif de location de '.$item['container']->reference.' est indisponible.');
        }
        return $items;
    }
    public function totals(int $durationDays=1): array {
        $durationDays=max(1,$durationDays); $ht=0;
        foreach($this->items() as $item){$mult=$item['type']==='location'?$durationDays:1;$ht += $item['unit_price']*$item['quantity']*$mult;}
        $vat=(float)SystemSetting::getValue('taux_tva',20); $tva=round($ht*$vat/100,2);
        return ['ht'=>round($ht,2),'tva'=>$tva,'ttc'=>round($ht+$tva,2),'vat_rate'=>$vat,'duration_days'=>$durationDays];
    }
}
