<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Submission $submission) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $songUrl = $this->submission->song?->slug
            ? url('/songs/'.$this->submission->song->slug)
            : url('/');

        return (new MailMessage)
            ->subject('Your song is live on Malawi Adventist Music')
            ->greeting("Great news, {$this->submission->submitter_name}!")
            ->line('Your song "'.$this->submission->song_title.'" has been approved and is now live.')
            ->action('Listen to your song', $songUrl)
            ->salutation('— Malawi Adventist Music');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'submission.approved',
            'submission_id' => $this->submission->id,
            'song_id' => $this->submission->song_id,
            'song_title' => $this->submission->song_title,
        ];
    }
}
