<?php

namespace App\Filament\Resources\SubmissionResource\Pages;

use App\Filament\Resources\SubmissionResource;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\SubmissionReview;
use App\Notifications\SubmissionApproved;
use App\Notifications\SubmissionChangesRequested;
use App\Notifications\SubmissionRejected;
use App\Services\Submissions\SongMaterialiser;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\ViewRecord;

class ViewSubmission extends ViewRecord
{
    protected static string $resource = SubmissionResource::class;
    protected static string $view = 'filament.submissions.view';

    protected function getHeaderActions(): array
    {
        /** @var Submission $record */
        $record = $this->getRecord();

        $canModerate = in_array($record->status, [
            Submission::STATUS_UNDER_REVIEW,
            Submission::STATUS_PAID,
        ], true);

        return [
            Action::make('approve')
                ->label('Approve & publish')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible($canModerate)
                ->action(function () use ($record) {
                    /** @var SongMaterialiser $materialiser */
                    $materialiser = app(SongMaterialiser::class);
                    $song = $materialiser->publish($record);

                    SubmissionReview::create([
                        'submission_id' => $record->id,
                        'reviewer_id' => auth()->id(),
                        'action' => SubmissionReview::ACTION_APPROVED,
                    ]);

                    $record->user?->notify(new SubmissionApproved($record->fresh()));

                    FilamentNotification::make()
                        ->title('Approved')
                        ->body('Published as "'.$song->title.'".')
                        ->success()
                        ->send();

                    $this->redirect(SubmissionResource::getUrl('view', ['record' => $record]));
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible($canModerate)
                ->form([
                    Textarea::make('reason')->label('Reason')->required()->minLength(10)->rows(4),
                ])
                ->action(function (array $data) use ($record) {
                    $record->update([
                        'status' => Submission::STATUS_REJECTED,
                        'rejection_reason' => $data['reason'],
                        'reviewer_id' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);
                    SubmissionReview::create([
                        'submission_id' => $record->id,
                        'reviewer_id' => auth()->id(),
                        'action' => SubmissionReview::ACTION_REJECTED,
                        'reason' => $data['reason'],
                    ]);
                    $record->user?->notify(new SubmissionRejected($record, $data['reason']));

                    FilamentNotification::make()->title('Rejected')->danger()->send();
                    $this->redirect(SubmissionResource::getUrl('view', ['record' => $record]));
                }),

            Action::make('request_changes')
                ->label('Request changes')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->visible($canModerate)
                ->form([
                    Textarea::make('note')->label('Note')->required()->minLength(10)->rows(4),
                ])
                ->action(function (array $data) use ($record) {
                    $record->update([
                        'status' => Submission::STATUS_CHANGES_REQUESTED,
                        'reviewer_notes' => $data['note'],
                        'reviewer_id' => auth()->id(),
                        'reviewed_at' => now(),
                    ]);
                    SubmissionReview::create([
                        'submission_id' => $record->id,
                        'reviewer_id' => auth()->id(),
                        'action' => SubmissionReview::ACTION_CHANGES_REQUESTED,
                        'notes' => $data['note'],
                    ]);
                    $record->user?->notify(new SubmissionChangesRequested($record, $data['note']));

                    FilamentNotification::make()->title('Changes requested')->warning()->send();
                    $this->redirect(SubmissionResource::getUrl('view', ['record' => $record]));
                }),
        ];
    }

    protected function getViewData(): array
    {
        /** @var Submission $r */
        $r = $this->record;
        $r->load(['user', 'files', 'categories', 'occasions', 'moods', 'language', 'genre', 'region', 'district', 'reviewer', 'reviews.reviewer']);

        $files = $r->files->keyBy('kind');
        return [
            'submission' => $r,
            'audio' => $files->get(SubmissionFile::KIND_AUDIO),
            'artwork' => $files->get(SubmissionFile::KIND_ARTWORK),
            'permission' => $files->get(SubmissionFile::KIND_PERMISSION),
        ];
    }
}
