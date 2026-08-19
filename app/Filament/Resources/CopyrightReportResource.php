<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CopyrightReportResource\Pages;
use App\Models\CopyrightReport;
use App\Models\Song;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CopyrightReportResource extends Resource
{
    protected static ?string $model = CopyrightReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Moderation';
    protected static ?string $navigationLabel = 'Copyright reports';
    protected static ?int $navigationSort = 20;
    protected static ?string $recordTitleAttribute = 'reference';

    public static function getNavigationBadge(): ?string
    {
        $c = static::getModel()::whereIn('status', [CopyrightReport::STATUS_RECEIVED, CopyrightReport::STATUS_UNDER_REVIEW])->count();
        return $c > 0 ? (string) $c : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Report')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('reference')->extraInputAttributes(['class' => 'font-mono'])->disabled(),
                    Forms\Components\Select::make('status')
                        ->options([
                            CopyrightReport::STATUS_RECEIVED => 'Received',
                            CopyrightReport::STATUS_UNDER_REVIEW => 'Under review',
                            CopyrightReport::STATUS_VALID => 'Valid',
                            CopyrightReport::STATUS_INVALID => 'Invalid',
                            CopyrightReport::STATUS_RESOLVED => 'Resolved',
                            CopyrightReport::STATUS_WITHDRAWN => 'Withdrawn',
                        ]),
                    Forms\Components\TextInput::make('reporter_name')->required(),
                    Forms\Components\TextInput::make('reporter_email')->email()->required(),
                    Forms\Components\TextInput::make('reporter_phone'),
                    Forms\Components\TextInput::make('reporter_organization'),
                    Forms\Components\Textarea::make('claim')->rows(4)->required()->columnSpanFull(),
                    Forms\Components\Textarea::make('resolution_notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->fontFamily('mono')->limit(8),
                Tables\Columns\TextColumn::make('reporter_name')->searchable(),
                Tables\Columns\TextColumn::make('target_type')->formatStateUsing(fn ($state) => $state ? class_basename($state) : '—')->badge(),
                Tables\Columns\TextColumn::make('claim')->limit(60),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => [CopyrightReport::STATUS_RECEIVED, CopyrightReport::STATUS_UNDER_REVIEW],
                        'danger' => CopyrightReport::STATUS_VALID,
                        'success' => CopyrightReport::STATUS_RESOLVED,
                        'gray' => [CopyrightReport::STATUS_INVALID, CopyrightReport::STATUS_WITHDRAWN],
                    ])
                    ->formatStateUsing(fn ($s) => ucwords(str_replace('_', ' ', $s))),
                Tables\Columns\TextColumn::make('assignee.name')->label('Assigned to')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Age'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    CopyrightReport::STATUS_RECEIVED => 'Received',
                    CopyrightReport::STATUS_UNDER_REVIEW => 'Under review',
                    CopyrightReport::STATUS_VALID => 'Valid',
                    CopyrightReport::STATUS_INVALID => 'Invalid',
                    CopyrightReport::STATUS_RESOLVED => 'Resolved',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assign_me')
                    ->label('Assign to me')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn (CopyrightReport $r) => !$r->assigned_to)
                    ->action(function (CopyrightReport $record) {
                        $record->update(['assigned_to' => auth()->id(), 'status' => CopyrightReport::STATUS_UNDER_REVIEW]);
                        Notification::make()->title('Assigned to you')->success()->send();
                    }),
                Tables\Actions\Action::make('mark_valid_and_suspend')
                    ->label('Valid — suspend song')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (CopyrightReport $r) => $r->target_type === Song::class && $r->target_id)
                    ->form([Forms\Components\Textarea::make('notes')->label('Resolution notes')->required()])
                    ->action(function (CopyrightReport $record, array $data) {
                        $record->update([
                            'status' => CopyrightReport::STATUS_VALID,
                            'resolution_notes' => $data['notes'],
                            'resolved_at' => now(),
                        ]);
                        if ($record->target_type === Song::class) {
                            Song::where('id', $record->target_id)->update(['status' => Song::STATUS_SUSPENDED]);
                        }
                        Notification::make()->title('Song suspended')->success()->send();
                    }),
                Tables\Actions\Action::make('mark_invalid')
                    ->label('Dismiss')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->form([Forms\Components\Textarea::make('notes')->label('Reason')->required()])
                    ->action(function (CopyrightReport $record, array $data) {
                        $record->update([
                            'status' => CopyrightReport::STATUS_INVALID,
                            'resolution_notes' => $data['notes'],
                            'resolved_at' => now(),
                        ]);
                    }),
            ])
            ->emptyStateHeading('No copyright reports')
            ->emptyStateDescription('Reports from rights holders will appear here.')
            ->emptyStateIcon('heroicon-o-shield-check');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCopyrightReports::route('/'),
            'edit' => Pages\EditCopyrightReport::route('/{record}/edit'),
        ];
    }
}
