<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionChangesRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Submission $submission,
        public readonly string $note,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Changes needed on your submission')
            ->greeting("Hello {$this->submission->submitter_name},")
            ->line('A moderator has requested changes to your submission "'.$this->submission->song_title.'".')
            ->line('Note from moderator: '.$this->note)
            ->action('View submission', url('/submissions'))
            ->salutation('— Malawi Adventist Music');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'submission.changes_requested',
            'submission_id' => $this->submission->id,
            'song_title' => $this->submission->song_title,
            'note' => $this->note,
        ];
    }
}
