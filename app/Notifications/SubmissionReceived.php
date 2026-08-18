<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Submission $submission) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your music submission has been received')
            ->greeting("Hello {$this->submission->submitter_name}!")
            ->line('Your music submission "'.$this->submission->song_title.'" has been received and is awaiting review by our moderators.')
            ->line('You will receive another email once a decision has been made.')
            ->action('View submission status', url('/submissions'))
            ->salutation('— Malawi Adventist Music');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'submission.received',
            'submission_id' => $this->submission->id,
            'song_title' => $this->submission->song_title,
        ];
    }
}
