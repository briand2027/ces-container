<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;use App\Models\SystemSetting;
class SystemSettingsSeeder extends Seeder{public function run(){foreach([['banque_nom','', 'string'],['banque_titulaire','', 'string'],['banque_iban','', 'string'],['banque_bic','', 'string'],['banque_adresse','', 'string'],['banque_instructions','Merci d’indiquer la référence de commande dans le virement.', 'string'],['email_notification_contact','', 'string'],['email_notification_commande','', 'string']] as [$k,$v,$t])SystemSetting::setValue($k,$v,$t);}}
