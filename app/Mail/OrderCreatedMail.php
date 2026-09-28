<?php
namespace App\Mail;
use App\Models\Order;
use App\Models\SystemSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class OrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Order $order, public bool $customer=false) {}
    public function build()
    {
        $this->order->loadMissing('client', 'lines.container', 'lines.service', 'payments');
        $company=SystemSetting::getValue('entreprise_nom','C.E.S. Container');
        $sender=SystemSetting::getValue('email_expediteur_principal') ?: SystemSetting::getValue('entreprise_email');
        $mail=$this->subject(($this->customer?'Confirmation de votre commande ':'Nouvelle commande reçue ').$this->order->numero_commande)
            ->view('emails.order-created')
            ->with([
                'company'=>$company,
                'bank'=>[
                    'banque_nom'=>SystemSetting::getValue('banque_nom',''),
                    'banque_titulaire'=>SystemSetting::getValue('banque_titulaire',''),
                    'banque_iban'=>SystemSetting::getValue('banque_iban',''),
                    'banque_bic'=>SystemSetting::getValue('banque_bic',''),
                    'banque_adresse'=>SystemSetting::getValue('banque_adresse',''),
                    'banque_instructions'=>SystemSetting::getValue('banque_instructions',''),
                ],
            ]);

        if ($sender) {
            $mail->from($sender,$company)->replyTo($sender,$company);
        }

        return $mail;
    }
}
