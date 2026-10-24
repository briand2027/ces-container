<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use App\Models\SystemSetting;
use App\Services\QuotePdfGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class QuoteRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public QuoteRequest $quote, public bool $customer = false, public string $messageLocale = 'it', public bool $followUp = false) {}

    public function build(): static
    {
        $this->quote->loadMissing('lines');
        $company = SystemSetting::getValue('entreprise_nom', 'C.E.S. Container');
        $sender = SystemSetting::getValue('email_expediteur_principal') ?: SystemSetting::getValue('entreprise_email');

        $subjectKey = $this->followUp ? 'order.followup_subject' : ($this->customer ? 'order.email_subject_customer' : 'order.email_subject_admin');
        $subject = Lang::get($subjectKey, ['reference' => $this->quote->reference], $this->messageLocale);
        $mail = $this->subject($subject)
            ->view($this->followUp ? 'emails.quote-follow-up' : 'emails.quote-request')
            ->with([
                'company' => $company,
                'customer' => $this->customer,
                'quote' => $this->quote,
                'vatRate' => (float) SystemSetting::getValue('taux_tva', 20),
            ])
            ->locale($this->messageLocale);

        if ($sender) {
            $mail->from($sender, $company);
            if ($this->customer) {
                $mail->replyTo($sender, $company);
            } else {
                $mail->replyTo($this->quote->email, trim(($this->quote->prenom ?? '').' '.$this->quote->nom));
            }
        }

        $pdf = app(QuotePdfGenerator::class)->generate(
            $this->quote->toArray(),
            $this->quote->lines->toArray(),
            $company,
            $this->messageLocale,
            (float) SystemSetting::getValue('taux_tva', 20),
        );
        $mail->attachData($pdf, 'Commande-'.$this->quote->reference.'.pdf', ['mime' => 'application/pdf']);

        return $mail;
    }
}
