<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $customer ? 'Confirmation de commande' : 'Nouvelle commande' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f2f5f4;font-family:Arial,Helvetica,sans-serif;color:#24312d;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background-color:#f2f5f4;padding:28px 12px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;border-collapse:separate;border-spacing:0;background-color:#ffffff;border:1px solid #dfe7e3;border-radius:10px;overflow:hidden;">
                <tr><td style="padding:24px 30px;background-color:#173b32;color:#ffffff;">
                    <p style="margin:0 0 7px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:#b8ded0;">{{ $company }}</p>
                    <h1 style="margin:0;font-size:24px;line-height:1.3;color:#ffffff;">{{ $customer ? 'Commande confirmée' : 'Nouvelle commande reçue' }}</h1>
                    <p style="margin:8px 0 0;font-size:14px;line-height:1.6;color:#e2eeea;">{{ $customer ? 'Nous avons bien enregistré votre demande.' : 'Une nouvelle commande vient d’être enregistrée sur le site.' }}</p>
                </td></tr>
                <tr><td style="padding:26px 30px 10px;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background-color:#f5f8f6;border:1px solid #e2eae5;border-radius:7px;">
                        <tr>
                            <td style="padding:14px 16px;width:50%;vertical-align:top;"><span style="display:block;font-size:11px;text-transform:uppercase;color:#66766f;">Référence</span><strong style="display:block;margin-top:5px;font-size:16px;color:#173b32;">{{ $order->numero_commande }}</strong></td>
                            <td style="padding:14px 16px;width:50%;vertical-align:top;"><span style="display:block;font-size:11px;text-transform:uppercase;color:#66766f;">Date</span><strong style="display:block;margin-top:5px;font-size:14px;color:#24312d;">{{ optional($order->created_at)->format('d/m/Y à H:i') }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding:0 16px 14px;vertical-align:top;"><span style="display:block;font-size:11px;text-transform:uppercase;color:#66766f;">Type</span><strong style="display:block;margin-top:5px;font-size:14px;color:#24312d;">{{ ucfirst($order->type_commande) }}</strong></td>
                            <td style="padding:0 16px 14px;vertical-align:top;"><span style="display:block;font-size:11px;text-transform:uppercase;color:#66766f;">Paiement</span><strong style="display:block;margin-top:5px;font-size:14px;color:#24312d;">Virement bancaire · {{ ucfirst(str_replace('_',' ',$order->statut_paiement)) }}</strong></td>
                        </tr>
                    </table>
                </td></tr>

                @if(!$customer)
                    <tr><td style="padding:18px 30px 8px;">
                        <h2 style="margin:0 0 12px;font-size:17px;color:#173b32;">Informations du client</h2>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:14px;line-height:1.65;">
                            <tr><td style="padding:3px 0;width:145px;color:#66766f;">Nom</td><td style="padding:3px 0;font-weight:bold;">{{ trim(($order->client->prenom ?? '').' '.($order->client->nom ?? '')) }}</td></tr>
                            @if($order->client->nom_entreprise)<tr><td style="padding:3px 0;color:#66766f;">Entreprise</td><td style="padding:3px 0;">{{ $order->client->nom_entreprise }}</td></tr>@endif
                            <tr><td style="padding:3px 0;color:#66766f;">E-mail</td><td style="padding:3px 0;"><a href="mailto:{{ $order->client->email }}" style="color:#176b4b;text-decoration:none;">{{ $order->client->email }}</a></td></tr>
                            @if($order->client->telephone)<tr><td style="padding:3px 0;color:#66766f;">Téléphone</td><td style="padding:3px 0;">{{ $order->client->telephone }}</td></tr>@endif
                            <tr><td style="padding:3px 0;color:#66766f;vertical-align:top;">Adresse de livraison</td><td style="padding:3px 0;white-space:pre-line;">{{ $order->adresse_livraison }}</td></tr>
                            @if($order->client->numero_tva)<tr><td style="padding:3px 0;color:#66766f;">N° TVA</td><td style="padding:3px 0;">{{ $order->client->numero_tva }}</td></tr>@endif
                            @if($order->notes_commande)<tr><td style="padding:3px 0;color:#66766f;vertical-align:top;">Notes</td><td style="padding:3px 0;white-space:pre-line;">{{ $order->notes_commande }}</td></tr>@endif
                        </table>
                    </td></tr>
                @else
                    <tr><td style="padding:18px 30px 8px;font-size:14px;line-height:1.7;">Bonjour {{ $order->client->prenom ?: $order->client->nom }},<br>Voici le récapitulatif de votre commande et les informations nécessaires à son règlement.</td></tr>
                @endif

                <tr><td style="padding:18px 30px 8px;">
                    <h2 style="margin:0 0 12px;font-size:17px;color:#173b32;">Détail de la commande</h2>
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:13px;">
                        <thead><tr style="background-color:#edf3f0;color:#53645d;text-align:left;">
                            <th style="padding:10px 8px;font-weight:bold;">Article</th><th style="padding:10px 8px;text-align:center;font-weight:bold;">Qté</th><th style="padding:10px 8px;text-align:right;font-weight:bold;">Prix unitaire</th><th style="padding:10px 8px;text-align:right;font-weight:bold;">Total</th>
                        </tr></thead>
                        <tbody>
                            @foreach($order->lines as $line)
                                @php($itemName = $line->container ? trim(($line->container->reference ?? '').' · '.ucfirst(str_replace('_',' ',$line->container->type_conteneur ?? 'Conteneur'))) : ($line->service->nom ?? 'Prestation'))
                                <tr>
                                    <td style="padding:11px 8px;border-bottom:1px solid #e7ece9;"><strong>{{ $itemName }}</strong>@if(($line->type_ligne ?? '')==='location')<br><span style="color:#66766f;">Location · {{ $line->duree_location_jours }} jour(s)</span>@endif</td>
                                    <td style="padding:11px 8px;border-bottom:1px solid #e7ece9;text-align:center;">{{ $line->quantite }}</td>
                                    <td style="padding:11px 8px;border-bottom:1px solid #e7ece9;text-align:right;">{{ number_format((float)$line->prix_unitaire,2,',',' ') }} €</td>
                                    <td style="padding:11px 8px;border-bottom:1px solid #e7ece9;text-align:right;font-weight:bold;">{{ number_format((float)$line->total_ligne,2,',',' ') }} €</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:12px;border-collapse:collapse;font-size:14px;">
                        <tr><td style="padding:4px 8px;text-align:right;color:#66766f;">Sous-total HT</td><td style="padding:4px 8px;width:130px;text-align:right;">{{ number_format((float)($order->total_ht ?? $order->sous_total),2,',',' ') }} €</td></tr>
                        <tr><td style="padding:4px 8px;text-align:right;color:#66766f;">TVA</td><td style="padding:4px 8px;text-align:right;">{{ number_format((float)$order->tva,2,',',' ') }} €</td></tr>
                        <tr><td style="padding:11px 8px 4px;text-align:right;border-top:2px solid #173b32;font-size:15px;font-weight:bold;color:#173b32;">Total TTC</td><td style="padding:11px 8px 4px;text-align:right;border-top:2px solid #173b32;font-size:17px;font-weight:bold;color:#173b32;">{{ number_format((float)$order->total_ttc,2,',',' ') }} €</td></tr>
                    </table>
                </td></tr>

                @if($customer)
                    <tr><td style="padding:18px 30px 26px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background-color:#f5f8f6;border-left:4px solid #2b8059;">
                            <tr><td style="padding:16px 18px;">
                                <h2 style="margin:0 0 10px;font-size:16px;color:#173b32;">Règlement par virement bancaire</h2>
                                <p style="margin:0 0 10px;font-size:13px;line-height:1.6;">Merci d’indiquer la référence <strong>{{ $order->numero_commande }}</strong> dans le motif du virement.</p>
                                <table role="presentation" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:13px;line-height:1.7;">
                                    @if($bank['banque_nom'])<tr><td style="padding-right:12px;color:#66766f;">Banque</td><td><strong>{{ $bank['banque_nom'] }}</strong></td></tr>@endif
                                    @if($bank['banque_titulaire'])<tr><td style="padding-right:12px;color:#66766f;">Titulaire</td><td><strong>{{ $bank['banque_titulaire'] }}</strong></td></tr>@endif
                                    @if($bank['banque_iban'])<tr><td style="padding-right:12px;color:#66766f;">IBAN</td><td><strong>{{ $bank['banque_iban'] }}</strong></td></tr>@endif
                                    @if($bank['banque_bic'])<tr><td style="padding-right:12px;color:#66766f;">BIC / SWIFT</td><td><strong>{{ $bank['banque_bic'] }}</strong></td></tr>@endif
                                    @if($bank['banque_adresse'])<tr><td style="padding-right:12px;color:#66766f;vertical-align:top;">Adresse</td><td style="white-space:pre-line;">{{ $bank['banque_adresse'] }}</td></tr>@endif
                                </table>
                                @if($bank['banque_instructions'])<p style="margin:10px 0 0;font-size:13px;line-height:1.6;white-space:pre-line;">{{ $bank['banque_instructions'] }}</p>@endif
                            </td></tr>
                        </table>
                    </td></tr>
                @endif

                <tr><td style="padding:16px 30px;background-color:#f5f8f6;border-top:1px solid #e2eae5;font-size:12px;line-height:1.6;color:#66766f;">
                    {{ $customer ? 'Merci pour votre confiance.' : 'Notification automatique de commande du site.' }}<br>{{ $company }}
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
