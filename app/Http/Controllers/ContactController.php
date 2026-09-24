<?php
namespace App\Http\Controllers;
use App\Models\ContactMessage;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Message;
class ContactController extends Controller
{
    public function create(){return view('public.contact');}
    public function store(Request $request)
    {
        $data=$request->validate([
            'nom'=>'required|string|max:100', 'prenom'=>'required|string|max:100',
            'email'=>'required|email|max:255','telephone'=>'nullable|string|max:20',
            'sujet'=>'required|string|max:200','message'=>'required|string|max:5000'
        ]);
        $msg=ContactMessage::create($data+['statut'=>'non_lu']);
        $recipients=array_values(array_unique(array_filter([
            SystemSetting::getValue('email_notification_contact'),
            SystemSetting::getValue('email_notification_contact_2'),
        ])));
        $sender=SystemSetting::getValue('email_expediteur_principal') ?: SystemSetting::getValue('entreprise_email');
        if($recipients){
            try { Mail::mailer('smtp')->raw("Nouveau message de contact\n\nNom : {$msg->prenom} {$msg->nom}\nEmail : {$msg->email}\nTéléphone : {$msg->telephone}\nSujet : {$msg->sujet}\n\n{$msg->message}",
                function(Message $mail) use($recipients,$msg,$sender){
                    $mail->to($recipients)->replyTo($msg->email)->subject('Nouveau message de contact');
                    if($sender) $mail->from($sender,SystemSetting::getValue('entreprise_nom','C.E.S. Container'));
                });
            } catch (\Throwable $e) {
                Log::error('Message contact enregistré mais notification email échouée', ['message_id'=>$msg->id,'recipients'=>$recipients,'error'=>$e->getMessage()]);
                return back()->with('warning','Votre message est bien enregistré. La notification par e-mail n’a pas pu être envoyée pour le moment ; notre équipe le retrouvera dans son espace de gestion.');
            }
            return back()->with('success','Votre message a été envoyé à notre équipe.');
        }
        Log::warning('Message contact enregistré sans destinataire email configuré', ['message_id'=>$msg->id]);
        return back()->with('warning','Votre message est bien enregistré. Les adresses de notification e-mail ne sont pas encore configurées.');
    }
}
