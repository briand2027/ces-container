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
    public function build(){
        return $this->subject(($this->customer?'Confirmation de votre commande ':'Nouvelle commande ').$this->order->numero_commande)
            ->view('emails.order-created')->with(['bank'=>[
                'banque_nom'=>SystemSetting::getValue('banque_nom',''),'banque_titulaire'=>SystemSetting::getValue('banque_titulaire',''),
                'banque_iban'=>SystemSetting::getValue('banque_iban',''),'banque_bic'=>SystemSetting::getValue('banque_bic',''),
                'banque_instructions'=>SystemSetting::getValue('banque_instructions','')]]);
    }
}
