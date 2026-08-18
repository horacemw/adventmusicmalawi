<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailed extends Notification implements ShouldQueue
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
            ->error()
            ->subject('Payment could not be completed')
            ->line('Your payment for the submission fee could not be completed.')
            ->line($this->payment->failure_reason ? 'Reason: '.$this->payment->failure_reason : 'You can try the payment again from your submission page.')
            ->action('Retry payment', url('/submissions'))
            ->salutation('— Malawi Adventist Music');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment.failed',
            'payment_id' => $this->payment->id,
            'reference' => $this->payment->reference,
            'reason' => $this->payment->failure_reason,
        ];
    }
}
