<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Monetisation';
    protected static ?string $navigationLabel = 'Payments & revenue';
    protected static ?int $navigationSort = 10;
    protected static ?string $recordTitleAttribute = 'reference';

    public static function canCreate(): bool
    {
        return false; // read-only — payments flow in via PayChangu
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('Ref')->fontFamily('mono')->limit(12)->searchable()->copyable(),
                Tables\Columns\TextColumn::make('provider_reference')->label('PayChangu tx')->fontFamily('mono')->limit(12)->searchable()->copyable()->toggleable(),
                Tables\Columns\TextColumn::make('user.name')->label('User')->searchable(),
                Tables\Columns\TextColumn::make('amount')->money('MWK')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => Payment::STATUS_PENDING,
                        'warning' => Payment::STATUS_PROCESSING,
                        'success' => Payment::STATUS_SUCCESSFUL,
                        'danger' => [Payment::STATUS_FAILED, Payment::STATUS_CANCELLED],
                        'primary' => Payment::STATUS_REFUNDED,
                    ])
                    ->formatStateUsing(fn (string $s) => ucwords($s)),
                Tables\Columns\TextColumn::make('provider')->badge(),
                Tables\Columns\TextColumn::make('payable_type')
                    ->label('For')
                    ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '—'),
                Tables\Columns\TextColumn::make('completed_at')->label('Completed')->dateTime('d M Y H:i')->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('Initiated')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    Payment::STATUS_PENDING => 'Pending',
                    Payment::STATUS_PROCESSING => 'Processing',
                    Payment::STATUS_SUCCESSFUL => 'Successful',
                    Payment::STATUS_FAILED => 'Failed',
                    Payment::STATUS_CANCELLED => 'Cancelled',
                    Payment::STATUS_REFUNDED => 'Refunded',
                ]),
                Tables\Filters\SelectFilter::make('provider')->options([
                    Payment::PROVIDER_PAYCHANGU => 'PayChangu',
                    Payment::PROVIDER_MANUAL => 'Manual',
                    Payment::PROVIDER_CREDIT => 'Credit',
                ]),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->native(false),
                        \Filament\Forms\Components\DatePicker::make('until')->native(false),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->actions([Tables\Actions\ViewAction::make()])
            ->emptyStateHeading('No payments recorded');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Payment')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('reference')->fontFamily('mono'),
                    Infolists\Components\TextEntry::make('provider_reference')->label('PayChangu reference')->fontFamily('mono'),
                    Infolists\Components\TextEntry::make('provider'),
                    Infolists\Components\TextEntry::make('status')->badge(),
                    Infolists\Components\TextEntry::make('amount')->money('MWK'),
                    Infolists\Components\TextEntry::make('currency'),
                    Infolists\Components\TextEntry::make('initiated_at')->dateTime(),
                    Infolists\Components\TextEntry::make('completed_at')->dateTime(),
                    Infolists\Components\TextEntry::make('failed_at')->dateTime(),
                    Infolists\Components\TextEntry::make('failure_reason'),
                ]),
            Infolists\Components\Section::make('Payable')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('payable_type')->formatStateUsing(fn ($state) => class_basename($state)),
                    Infolists\Components\TextEntry::make('payable_id'),
                ]),
            Infolists\Components\Section::make('User')
                ->schema([
                    Infolists\Components\TextEntry::make('user.name'),
                    Infolists\Components\TextEntry::make('user.email'),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
