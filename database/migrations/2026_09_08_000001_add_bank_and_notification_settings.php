<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;
return new class extends Migration {
    public function up(): void {
        $defaults=[
            ['banque_nom','','string','Nom de la banque'],['banque_titulaire','','string','Titulaire du compte bancaire'],['banque_iban','','string','IBAN utilisé pour les virements'],
            ['banque_bic','','string','BIC / SWIFT'],['banque_adresse','','string','Adresse de la banque'],['banque_instructions','Indiquez le numéro de commande comme motif du virement.','string','Instructions de virement affichées au client'],
            ['email_notification_contact','','string','Email recevant les notifications du formulaire de contact'],['email_notification_commande','','string','Email recevant les notifications de nouvelles commandes'],
        ];
        foreach($defaults as [$key,$value,$type,$description]) SystemSetting::query()->updateOrCreate(['cle'=>$key],['valeur'=>$value,'type'=>$type,'description'=>$description]);
    }
    public function down(): void { SystemSetting::whereIn('cle',['banque_nom','banque_titulaire','banque_iban','banque_bic','banque_adresse','banque_instructions','email_notification_contact','email_notification_commande'])->delete(); }
};
