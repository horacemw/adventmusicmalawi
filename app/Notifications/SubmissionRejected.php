<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Submission $submission,
        public readonly string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Submission update required')
            ->greeting("Hello {$this->submission->submitter_name},")
            ->line('Your submission "'.$this->submission->song_title.'" was not approved.')
            ->line('Reason: '.$this->reason)
            ->action('View submission', url('/submissions'))
            ->salutation('— Malawi Adventist Music');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'submission.rejected',
            'submission_id' => $this->submission->id,
            'song_title' => $this->submission->song_title,
            'reason' => $this->reason,
        ];
    }
}
