# Audit des descriptions de conteneurs

Source: `u951490661_conteneur (2).sql` (export du 23 octobre 2026).

## Resume

- 141 enregistrements de conteneurs dans cet export.
- 7 descriptions italiennes principales sont vides: IDs 30, 125, 144, 146, 151, 157 et 169.
- Parmi les 134 descriptions principales non vides, 33 comportent du Markdown, des emojis ou une mention de traduction automatique.
- Plusieurs traductions sont deja renseignees: les champs existants devront etre conserves.
- `description_ch` correspond a l'allemand suisse dans `config/locales.php`; ce n'est pas du chinois.
- Le proprietaire confirme que les fiches similaires ne sont pas des doublons: aucune fiche ne doit etre fusionnee ou supprimee.

Les traductions manquantes ont ete preparees a partir des descriptions italiennes, sans inventer de caracteristiques. Les traductions deja presentes sont conservees. Les affirmations de stockage gratuit des anciennes fiches sont remplacees par une mention conditionnelle a confirmer dans le devis ecrit; les modalites et frais de livraison sont renvoyes au devis.

## Descriptions italiennes vides

| ID | Reference | Action proposee |
|---:|---|---|
| 30 | `20DC Storage container 0188961 – TALLINN` | Creer la description uniquement a partir des specifications confirmees de la fiche. |
| 125 | `CONT-REEF-001` | Verifier les donnees produit et les specifications du conteneur reefer avant redaction. |
| 144 | `MIIU2252414` | Verifier les donnees produit avant redaction. |
| 146 | `MIIU2252444` | Verifier les donnees produit avant redaction. |
| 151 | `MIIU7187766` | Verifier les donnees produit avant redaction. |
| 157 | `CASA MOBILE CON 1 CAMERA DA LETTO – SOGGIORNO, CUCINA` | Verifier les donnees produit avant redaction. |
| 169 | `Conteneur Modulaire Jocsan 8x3m – 24m²` | Verifier les donnees produit avant redaction. |

## Textes a nettoyer / verifier

| ID | Reference | Anomalies detectees |
|---:|---|---|
| 27 | `8DC Storage container – 2252412` | Mention de traduction automatique. |
| 57 | `CABINA UFFICIO` | Emojis. |
| 58 | `Lucchetti per container` | Mention de traduction automatique. |
| 59 | `CABINA UFFICIO CON WC` | Mention de traduction automatique. |
| 66 | `BUNGALOW` | Emojis. |
| 39 | `Conteneur de stockage 20DC – TALLINN` | Source en francais et generique; claims de garantie et stockage a confirmer. |
| 43 | `Conteneur de stockage 20DC – TALLINN (2020)` | Titre annonce un conteneur d'occasion alors que le texte parle de conteneurs neufs one-way. Verification requise. |
| 69 | `CABINA DA UFFICIO CON AMPIA FINESTRA SCORREVOLE` | Le texte decrit un conteneur snack, pas la cabine de bureau du titre. Ne pas importer sans verification produit. |
| 70 | `CABINA UFFICIO CONTAINEX BASIC ANTRACITE` | Le texte source decrit une sauna, pas la cabine de bureau du titre. Ne pas importer sans verification produit. |
| 73 | `CABINA DA UFFICIO PROTEZIONE DI GUARDIA SICUREZZA` | Markdown et emojis. |
| 74 | `CABINA UFFICIO / SALOTTO CON WC` | Markdown et emojis. |
| 75 | `BUNGALOW MODULARE STANDARD` | Markdown et emojis. |
| 76 | `CABINA DI TOILETTE` | Markdown et emojis. |
| 77 | `CABINA DA UFFICIO – Cantiere – Base di vita – Uso` | Markdown et emojis. |
| 78 | `CABINA DA UFFICIO MODULARE` | Markdown et emojis. |
| 79 | `CABINA PER UFFICIO, ABITAZIONE O MAGAZZINO | PRONT` | Markdown et emojis. |
| 80 | `CABINA PER UFFICIO, ABITAZIONE O MAGAZZINO` | Description proche de l'ID 79; conserver cette fiche distincte. |
| 81 | `CABINA DA UFFICIO PRONTA ALL'USO` | Description proche des IDs 79-80; conserver cette fiche distincte. |
| 82 | `CABINA COMMERCIALE O EVENTISTICA` | Markdown et emojis. |
| 83 | `CABINA UFFICIO MODELLO` | Markdown et emojis. |
| 84 | `CABINA DA UFFICIO MODELLO` | Markdown et emojis. |
| 87 | `CABINA UFFICIO STANDARD` | Markdown et emojis. |
| 88 | `CABINA DA UFFICIO ISOLATA` | Markdown et emojis. |
| 89 | `CABINA UFFICIO ISOLATA` | Markdown et emojis. |
| 90 | `CABINA UFFICIO ISOLATA DI GUARDIA, PORTINERIA` | Markdown et emojis; conserver cette fiche distincte. |
| 91 | `CASA MODULARE` | Markdown et emojis. |
| 92 | `CABINA UFFICIO/ABITAZIONE` | Markdown et emojis. |
| 93 | `CABINA UFFICIO PREMIUM` | Markdown et emojis. |
| 94 | `CABINA DA UFFICIO / CASA / CASETTA DA GIARDINO` | Markdown et emojis. |
| 95 | `ECOMODUS-HAUS.DE – CASE MODULARI CHIAVI IN MANO` | Marque/fabricant tiers, prix au m2 et delai de production a verifier avant publication. |
| 96 | `CONTAINER ABITABILE DA 40 FT CAMERA DA LETTO, SOGG` | Description attribuee a un vendeur tiers; provenance et specs a verifier. |
| 97 | `Modulo Ufficio Mobile` | Description attribuee a un vendeur tiers; provenance et specs a verifier. |
| 98 | `MODULO MIJPL / CONTENITORE 285-LPL` | Markdown et emojis. |
| 167 | `BUNGALOW SANITARIO CON WC, DOCCIA E LAVANDINO` | Markdown et emojis. |
| 168 | `CONTENITORE MODULARE EDISON 24 M² – ABITAZIONE` | Markdown et emojis. |
| 103 | `MIIU1393567-20DC` | La description cite MIIU1393467, reference attribuee a une autre fiche. Verifier reference, couleur et caracteristiques. |
| 110 | `MIIU3222247` | Texte source tronque apres l'intitule relatif aux modes de paiement; les conditions absentes n'ont pas ete inventees. |
| 112 | `MIIU4032848` | La description source mentionne MIIU1147123; reference produit a confirmer. |
| 117 | `MIIU9650319- 40HCPW` | La description reprend le numero MIIU1147123. Identite du produit a confirmer. |
| 120 | `MIIU1147123-40HC` | Description identique a celle de l'ID 117; conserver les deux fiches, verifier la reference de l'ID 117. |
| 106 | `MIIU3980165` | La description melange des caracteristiques generales de produits neufs et d'occasion; verifier ce qui s'applique a cette unite. |

## Resultat

- Fichier d'import assemble: `clean-container-descriptions.sql`.
- Le script de verification a rejoue le SQL sur une base SQLite temporaire: 141 lignes conservees, 134 descriptions italiennes non vides, sept langues renseignees pour chacune, aucun marqueur Markdown/emoji/DeepL ni promesse non confirmee de stockage/livraison gratuite apres nettoyage.
- Les sept descriptions italiennes vides sont restees inchangees et leurs lignes ne sont ni supprimees ni fusionnees.
- Les traductions deja presentes sont conservees par `COALESCE(NULLIF(..., ''), ...)`; le nettoyage cible uniquement les champs dont le contenu correspond encore au dump source simule.
- Le SQL n'a pas ete importe dans une base de donnees. Faire une sauvegarde puis tester le fichier sur une copie avant l'import de production.
- Importer uniquement `clean-container-descriptions.sql`, en UTF-8, dans la base `ces_container` via phpMyAdmin. Ne pas importer les lots intermediaires individuellement. Sauvegarder la base et tester d'abord sur une copie.

## Descriptions italiennes a completer manuellement

| ID | Reference |
|---:|---|
| 30 | `20DC Storage container 0188961 – TALLINN` |
| 125 | `CONT-REEF-001` |
| 144 | `MIIU2252414` |
| 146 | `MIIU2252444` |
| 151 | `MIIU7187766` |
| 157 | `CASA MOBILE CON 1 CAMERA DA LETTO – SOGGIORNO, CUCINA` |
| 169 | `Conteneur Modulaire Jocsan 8x3m – 24m²` |

Cet audit et les fichiers de maintenance n'ont pas modifie le dump SQL d'origine ni la base de donnees reelle.

Cet audit n'a modifie ni le dump SQL ni la base de donnees.
