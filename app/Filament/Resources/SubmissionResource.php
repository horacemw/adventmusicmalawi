<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubmissionResource\Pages;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\SubmissionReview;
use App\Notifications\SubmissionApproved;
use App\Notifications\SubmissionChangesRequested;
use App\Notifications\SubmissionRejected;
use App\Services\Submissions\SongMaterialiser;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class SubmissionResource extends Resource
{
    protected static ?string $model = Submission::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationGroup = 'Moderation';
    protected static ?int $navigationSort = 10;
    protected static ?string $recordTitleAttribute = 'song_title';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereIn('status', [
            Submission::STATUS_UNDER_REVIEW,
            Submission::STATUS_PAID,
        ])->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Ref')
                    ->fontFamily('mono')
                    ->limit(12)
                    ->copyable()
                    ->tooltip(fn ($record) => $record->reference),

                TextColumn::make('song_title')
                    ->label('Song')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('user.name')
                    ->label('Submitter')
                    ->searchable()
                    ->limit(20),

                BadgeColumn::make('status')
                    ->colors([
                        'gray' => [Submission::STATUS_DRAFT, Submission::STATUS_WITHDRAWN],
                        'warning' => [
                            Submission::STATUS_AWAITING_PAYMENT,
                            Submission::STATUS_PAYMENT_PENDING,
                            Submission::STATUS_CHANGES_REQUESTED,
                        ],
                        'primary' => [Submission::STATUS_PAID, Submission::STATUS_UNDER_REVIEW],
                        'success' => [Submission::STATUS_APPROVED, Submission::STATUS_PUBLISHED],
                        'danger' => [Submission::STATUS_REJECTED],
                    ])
                    ->formatStateUsing(fn (string $state) => ucwords(str_replace('_', ' ', $state))),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    Submission::STATUS_UNDER_REVIEW => 'Under review',
                    Submission::STATUS_PAID => 'Paid',
                    Submission::STATUS_APPROVED => 'Approved',
                    Submission::STATUS_REJECTED => 'Rejected',
                    Submission::STATUS_CHANGES_REQUESTED => 'Changes requested',
                    Submission::STATUS_PUBLISHED => 'Published',
                    Submission::STATUS_AWAITING_PAYMENT => 'Awaiting payment',
                    Submission::STATUS_PAYMENT_PENDING => 'Payment pending',
                    Submission::STATUS_DRAFT => 'Draft',
                    Submission::STATUS_WITHDRAWN => 'Withdrawn',
                ]),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),

                    Action::make('approve')
                        ->label('Approve & publish')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Submission $r) => in_array($r->status, [Submission::STATUS_UNDER_REVIEW, Submission::STATUS_PAID], true))
                        ->action(function (Submission $record) {
                            /** @var SongMaterialiser $materialiser */
                            $materialiser = app(SongMaterialiser::class);
                            $song = $materialiser->publish($record);

                            SubmissionReview::create([
                                'submission_id' => $record->id,
                                'reviewer_id' => auth()->id(),
                                'action' => SubmissionReview::ACTION_APPROVED,
                                'notes' => null,
                            ]);

                            $record->user?->notify(new SubmissionApproved($record->fresh()));

                            FilamentNotification::make()
                                ->title('Submission approved')
                                ->body('Published as “'.$song->title.'”.')
                                ->success()
                                ->send();
                        }),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Textarea::make('reason')
                                ->label('Reason (visible to submitter)')
                                ->required()
                                ->minLength(10)
                                ->rows(4),
                        ])
                        ->visible(fn (Submission $r) => in_array($r->status, [Submission::STATUS_UNDER_REVIEW, Submission::STATUS_PAID], true))
                        ->action(function (Submission $record, array $data) {
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
                            FilamentNotification::make()->title('Submission rejected')->danger()->send();
                        }),

                    Action::make('request_changes')
                        ->label('Request changes')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->form([
                            Textarea::make('note')
                                ->label('What needs to change? (visible to submitter)')
                                ->required()
                                ->minLength(10)
                                ->rows(4),
                        ])
                        ->visible(fn (Submission $r) => in_array($r->status, [Submission::STATUS_UNDER_REVIEW, Submission::STATUS_PAID], true))
                        ->action(function (Submission $record, array $data) {
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
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubmissions::route('/'),
            'view' => Pages\ViewSubmission::route('/{record}'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form; // read-only workflow; details are shown on the View page
    }

    public static function fileUrl(?SubmissionFile $file): ?string
    {
        if (!$file || !$file->storage_path) return null;
        return Storage::disk('public')->url($file->storage_path);
    }
}
