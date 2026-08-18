<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessful extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Payment $payment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment received — thank you')
            ->line('We have received your submission fee of '.number_format((float) $this->payment->amount).' '.$this->payment->currency.'.')
            ->line('Reference: '.$this->payment->reference)
            ->line('Your submission is now with our moderators and will be reviewed shortly.')
            ->action('View submission status', url('/submissions'))
            ->salutation('— Malawi Adventist Music');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment.successful',
            'payment_id' => $this->payment->id,
            'reference' => $this->payment->reference,
            'amount' => (string) $this->payment->amount,
            'currency' => $this->payment->currency,
        ];
    }
}
