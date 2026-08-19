<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Audit log';
    protected static ?int $navigationSort = 30;

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('causer.name')->label('Actor')->placeholder('System'),
                Tables\Columns\BadgeColumn::make('description')
                    ->colors([
                        'success' => fn ($state) => str_contains($state, 'created'),
                        'warning' => fn ($state) => str_contains($state, 'updated'),
                        'danger' => fn ($state) => str_contains($state, 'deleted'),
                    ]),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '—'),
                Tables\Columns\TextColumn::make('subject_id')->label('ID'),
                Tables\Columns\TextColumn::make('log_name')->badge()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('log_name')
                    ->options(fn () => Activity::query()->distinct()->pluck('log_name', 'log_name')->toArray()),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->options(fn () => Activity::query()->distinct()->pluck('subject_type', 'subject_type')
                        ->mapWithKeys(fn ($v) => [$v => class_basename($v)])->toArray()),
            ])
            ->emptyStateHeading('No activity logged yet');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListActivityLogs::route('/')];
    }
}
