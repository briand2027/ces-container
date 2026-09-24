<h2>{{ $customer ? 'Confirmation de votre commande' : 'Nouvelle commande reçue' }}</h2>
<p>Commande : <strong>{{ $order->numero_commande }}</strong></p>
<p>Montant TTC : <strong>{{ number_format($order->total_ttc,2,',',' ') }} €</strong></p>
@if($customer)<p>Le paiement se fait par virement bancaire. Merci d'utiliser le numéro de commande comme référence.</p>@endif
<p>Banque : {{ $bank['banque_nom'] ?: 'Non configurée' }}</p>
<p>Titulaire : {{ $bank['banque_titulaire'] ?: 'Non configuré' }}</p>
<p>IBAN : {{ $bank['banque_iban'] ?: 'Non configuré' }}</p>
<p>BIC : {{ $bank['banque_bic'] ?: 'Non configuré' }}</p>
@if($bank['banque_instructions'])<p style="white-space:pre-line">{{ $bank['banque_instructions'] }}</p>@endif
