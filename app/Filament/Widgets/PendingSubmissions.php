<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SubmissionResource;
use App\Models\Submission;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingSubmissions extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Pending submissions';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Submission::query()
                ->whereIn('status', [Submission::STATUS_UNDER_REVIEW, Submission::STATUS_PAID])
                ->latest()
                ->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Ref')->limit(8)->fontFamily('mono'),
                Tables\Columns\TextColumn::make('song_title')->label('Song')->searchable()->weight('semibold'),
                Tables\Columns\TextColumn::make('user.name')->label('Submitter'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => Submission::STATUS_PAID, 'primary' => Submission::STATUS_UNDER_REVIEW])
                    ->formatStateUsing(fn ($s) => ucwords(str_replace('_', ' ', $s))),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Age'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Submission $record) => SubmissionResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->emptyStateHeading('No pending submissions')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
