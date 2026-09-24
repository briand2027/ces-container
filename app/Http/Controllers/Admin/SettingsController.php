<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
class SettingsController extends Controller
{
    public function edit(){abort_unless(session('admin_role')==='super_admin',403);
        $keys=['entreprise_nom','entreprise_email','entreprise_telephone','entreprise_adresse','taux_tva','devise','seuil_alerte_stock','duree_location_defaut','banque_nom','banque_titulaire','banque_iban','banque_bic','banque_adresse','banque_instructions','email_expediteur_principal','email_notification_contact','email_notification_contact_2','email_notification_commande'];
        $settings=SystemSetting::whereIn('cle',$keys)->get()->keyBy('cle');
        $smtp=['host'=>config('mail.mailers.smtp.host'),'port'=>config('mail.mailers.smtp.port')];
        return view('admin.settings.edit',compact('settings','smtp'));
    }
    public function update(Request $request){abort_unless(session('admin_role')==='super_admin',403);
        $data=$request->validate([
            'entreprise_nom'=>'nullable|string|max:255','entreprise_email'=>'nullable|email|max:255','entreprise_telephone'=>'nullable|string|max:50','entreprise_adresse'=>'nullable|string|max:1000',
            'taux_tva'=>'required|numeric|min:0|max:100','devise'=>'required|string|max:10','seuil_alerte_stock'=>'required|integer|min:0','duree_location_defaut'=>'required|integer|min:1',
            'banque_nom'=>'nullable|string|max:255','banque_titulaire'=>'nullable|string|max:255','banque_iban'=>'nullable|string|max:100','banque_bic'=>'nullable|string|max:50','banque_adresse'=>'nullable|string|max:1000','banque_instructions'=>'nullable|string|max:3000',
            'email_expediteur_principal'=>'required|email|max:255','email_notification_contact'=>'required|email|max:255','email_notification_contact_2'=>'required|email|different:email_notification_contact|max:255','email_notification_commande'=>'nullable|email|max:255',
        ]);
        $types=['taux_tva'=>'number','seuil_alerte_stock'=>'number','duree_location_defaut'=>'number'];
        foreach($data as $key=>$value) SystemSetting::setValue($key,$value,$types[$key]??'string');
        return back()->with('success','Paramètres enregistrés.');
    }
}
