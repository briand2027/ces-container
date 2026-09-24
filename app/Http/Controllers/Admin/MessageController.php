<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message as MailMessage;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::query()->orderByDesc('date_creation');
        if ($request->filled('statut')) $query->where('statut',$request->string('statut'));
        if ($request->filled('search')) {
            $term='%'.$request->string('search')->toString().'%';
            $query->where(fn($q)=>$q->where('nom','like',$term)->orWhere('prenom','like',$term)->orWhere('email','like',$term)->orWhere('sujet','like',$term)->orWhere('message','like',$term));
        }
        return view('admin.messages.index',[
            'items'=>$query->paginate(20)->withQueryString(),
            'stats'=>['total'=>ContactMessage::count(),'non_lu'=>ContactMessage::where('statut','non_lu')->count(),'lu'=>ContactMessage::where('statut','lu')->count(),'repondu'=>ContactMessage::where('statut','repondu')->count()],
        ]);
    }

    public function unreadCount()
    {
        $unread=ContactMessage::where('statut','non_lu');
        return response()->json(['count'=>(clone $unread)->count(),'latest_id'=>(clone $unread)->max('id')]);
    }

    public function show(ContactMessage $message)
    {
        if ($message->statut==='non_lu') $message->update(['statut'=>'lu']);
        return view('admin.messages.show',compact('message'));
    }

    public function toggleRead(ContactMessage $message)
    {
        $message->update(['statut'=>$message->statut==='non_lu'?'lu':'non_lu']);
        return back()->with('success','État de lecture modifié.');
    }

    public function reply(Request $request, ContactMessage $message)
    {
        $data=$request->validate(['reponse'=>'required|string|max:5000']);
        $sender=SystemSetting::getValue('email_expediteur_principal') ?: SystemSetting::getValue('entreprise_email');
        if (!$sender) return back()->withErrors(['reponse'=>'Définissez l’adresse e-mail principale dans les paramètres avant d’envoyer une réponse.'])->withInput();
        $company=SystemSetting::getValue('entreprise_nom','C.E.S. Container');
        try {
            Mail::mailer('smtp')->raw($data['reponse'],function(MailMessage $mail)use($message,$sender,$company){
                $mail->from($sender,$company)->to($message->email)->replyTo($sender,$company)->subject('Re: '.$message->sujet);
            });
        } catch (\Throwable $e) {
            Log::error('Réponse au message non envoyée',['message_id'=>$message->id,'error'=>$e->getMessage()]);
            return back()->withErrors(['reponse'=>'L’e-mail n’a pas pu être envoyé. Vérifiez la configuration SMTP et réessayez.'])->withInput();
        }
        $message->update(['reponse'=>$data['reponse'],'statut'=>'repondu','admin_id'=>session('admin_id'),'date_reponse'=>now()]);
        return back()->with('success','Réponse envoyée à '.$message->email.' depuis '.$sender.'.');
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();
        return redirect()->route('admin.messages.index')->with('success','Message supprimé.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids=collect($request->input('selected_ids',[]))->filter(fn($id)=>ctype_digit((string)$id))->map(fn($id)=>(int)$id)->values();
        if ($ids->isEmpty()) return back()->withErrors(['messages'=>'Sélectionnez au moins un message.']);
        $deleted=ContactMessage::whereIn('id',$ids)->delete();
        return back()->with('success',$deleted.' message(s) supprimé(s).');
    }
}
