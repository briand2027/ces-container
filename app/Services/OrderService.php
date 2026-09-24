<?php
namespace App\Services;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function create(array $data, CartService $cart): Order
    {
        return DB::transaction(function () use ($data, $cart) {
            $lines = $cart->validatedLines();
            $duration = max(1, (int)($data['duree_location_jours'] ?? (int)SystemSetting::getValue('duree_location_defaut', 30)));
            $totals = $cart->totals($duration);
            $client = Client::create([
                'type_client'=>$data['type_client'], 'nom'=>$data['nom'], 'prenom'=>$data['prenom'] ?? null,
                'nom_entreprise'=>$data['nom_entreprise'] ?? null, 'email'=>$data['email'], 'telephone'=>$data['telephone'] ?? null,
                'adresse'=>$data['adresse'], 'ville'=>$data['ville'], 'code_postal'=>$data['code_postal'], 'pays'=>$data['pays'],
                'numero_tva'=>$data['numero_tva'] ?? null, 'notes'=>$data['notes'] ?? null,
            ]);
            $type = $lines->contains(fn($l)=>$l['type']==='location') ? 'location' : 'vente';
            $order = Order::create([
                'numero_commande'=>$this->number(), 'client_id'=>$client->id, 'type_commande'=>$type,
                'statut_commande'=>'en_attente','statut_paiement'=>'en_attente','mode_paiement'=>'virement',
                'sous_total'=>$totals['ht'],'remise'=>0,'tva'=>$totals['tva'],'total_ht'=>$totals['ht'],'total_ttc'=>$totals['ttc'],
                'adresse_livraison'=>$data['adresse']."\n".$data['code_postal'].' '.$data['ville']."\n".$data['pays'],
                'notes_commande'=>$data['notes'] ?? null,
            ]);
            foreach ($lines as $line) {
                $container = \App\Models\Container::whereKey($line['container']->id)->lockForUpdate()->firstOrFail();
                if ($container->statut !== 'disponible' || $container->quantite_stock < $line['quantity']) {
                    throw new \RuntimeException('Le stock a changé pour '.$container->reference.'. Veuillez vérifier votre panier.');
                }
                $order->lines()->create([
                    'conteneur_id'=>$container->id,'type_ligne'=>'conteneur','quantite'=>$line['quantity'],
                    'prix_unitaire'=>$line['unit_price'],'remise'=>0,'total_ligne'=>round($line['unit_price']*$line['quantity']*($line['type']==='location' ? $duration : 1),2),'duree_location_jours'=>$line['type']==='location' ? $duration : 1,
                ]);
                $container->decrement('quantite_stock', $line['quantity']);
            }
            $order->payments()->create([
                'reference_paiement'=>'VIR-'.$order->numero_commande,'montant'=>$totals['ttc'],'mode_paiement'=>'virement','statut_paiement'=>'en_attente',
                'details_paiement'=>json_encode(['instructions'=>'Virement bancaire'], JSON_UNESCAPED_UNICODE),
            ]);
            return $order->load('client','lines.container','payments');
        });
    }

    public function markPaid(Order $order, ?string $reference = null): void
    {
        DB::transaction(function () use ($order, $reference) {
            $payment = $order->payments()->latest('id')->firstOrFail();
            $payment->update(['statut_paiement'=>'valide','date_paiement'=>now(),'reference_paiement'=>$reference ?: $payment->reference_paiement]);
            $order->update(['statut_paiement'=>'paye','statut_commande'=>'confirmee']);
        });
    }

    public function updateStatus(Order $order, string $status): void
    {
        $allowed = ['en_attente','confirmee','en_preparation','expediee','livree','annulee'];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Statut de commande invalide.');
        }

        DB::transaction(function () use ($order, $status) {
            $order->loadMissing('lines.container');
            $old = $order->statut_commande;

            if ($old !== 'annulee' && $status === 'annulee') {
                foreach ($order->lines as $line) {
                    if ($line->conteneur_id && $line->container) {
                        $line->container->increment('quantite_stock', $line->quantite);
                    }
                }
            } elseif ($old === 'annulee' && $status !== 'annulee') {
                foreach ($order->lines as $line) {
                    if ($line->conteneur_id && $line->container) {
                        $container = $line->container->fresh();
                        if ($container->quantite_stock < $line->quantite) {
                            throw new \RuntimeException('Stock insuffisant pour réactiver cette commande.');
                        }
                        $container->decrement('quantite_stock', $line->quantite);
                    }
                }
            }

            $order->update(['statut_commande' => $status]);
        });
    }

    private function number(): string
    {
        do { $number='CMD-'.now()->format('Y').'-'.random_int(100000,999999); }
        while (Order::where('numero_commande',$number)->exists());
        return $number;
    }
}
