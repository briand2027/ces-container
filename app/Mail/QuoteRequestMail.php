<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use App\Models\SystemSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class QuoteRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public QuoteRequest $quote, public bool $customer = false, public string $locale = 'it') {}

    public function build(): static
    {
        $this->quote->loadMissing('lines');
        $company = SystemSetting::getValue('entreprise_nom', 'C.E.S. Container');
        $sender = SystemSetting::getValue('email_expediteur_principal') ?: SystemSetting::getValue('entreprise_email');

        $subjectKey = $this->customer ? 'quote.email_subject_customer' : 'quote.email_subject_admin';
        $subject = Lang::get($subjectKey, ['reference' => $this->quote->reference], $this->locale);
        $mail = $this->subject($subject)
            ->view('emails.quote-request')
            ->with(['company' => $company, 'customer' => $this->customer])
            ->locale($this->locale);

        if ($sender) {
            $mail->from($sender, $company)->replyTo($sender, $company);
        }

        return $mail;
    }
}
