<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Users';
    protected static ?int $navigationSort = 10;
    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Account')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                    Forms\Components\TextInput::make('username')->maxLength(255)->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('phone')->tel()->maxLength(32),
                    Forms\Components\FileUpload::make('avatar_path')
                        ->label('Avatar')
                        ->image()->imageEditor()->disk('public')->directory('avatars')->maxSize(2 * 1024)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('bio')->rows(2)->maxLength(1000)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Roles & status')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('roles')
                        ->label('Roles')
                        ->multiple()
                        ->relationship('roles', 'name')
                        ->options(Role::pluck('name', 'name'))
                        ->preload()
                        ->helperText('New listener registrations get "listener" automatically. Grant admin roles carefully.'),
                    Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
                    Forms\Components\DateTimePicker::make('email_verified_at')
                        ->label('Email verified at')
                        ->native(false)
                        ->helperText('Set this to manually mark a user as email-verified'),
                    Forms\Components\Placeholder::make('last_login_at')
                        ->label('Last login')
                        ->content(fn ($record) => $record?->last_login_at?->diffForHumans() ?? 'Never'),
                ]),

            Forms\Components\Section::make('Password')
                ->description('Only set a password if you\'re creating this user or resetting via admin. Users normally set their own password on registration.')
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->maxLength(255)
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                ])
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(36)
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&background=16a34a&color=fff'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('email')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->separator(', ')
                    ->color(fn ($state) => match ($state) {
                        'super-admin', 'admin' => 'danger',
                        'music-moderator' => 'warning',
                        'artist', 'group-manager' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) $record->email_verified_at),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
                Tables\Columns\TextColumn::make('submissions_count')->counts('submissions')->label('Subs')->badge(),
                Tables\Columns\TextColumn::make('playlists_count')->counts('playlists')->label('Playlists')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Joined')->date('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Verified')
                    ->nullable(),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verify_email')
                    ->label('Mark verified')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (User $r) => !$r->email_verified_at)
                    ->action(function (User $record) {
                        $record->forceFill(['email_verified_at' => now()])->save();
                        Notification::make()->title('Email marked verified')->success()->send();
                    }),
                Tables\Actions\Action::make('send_password_reset')
                    ->label('Send password reset')
                    ->icon('heroicon-o-key')
                    ->color('gray')
                    ->action(function (User $record) {
                        \Illuminate\Support\Facades\Password::sendResetLink(['email' => $record->email]);
                        Notification::make()->title('Password reset email sent')->success()->send();
                    }),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn (User $r) => $r->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (User $r) => $r->is_active ? 'heroicon-o-user-minus' : 'heroicon-o-user-plus')
                    ->color(fn (User $r) => $r->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['is_active' => !$record->is_active])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('verify')
                        ->icon('heroicon-o-check-badge')->color('success')
                        ->action(fn ($records) => $records->each->forceFill(['email_verified_at' => now()])->each->save()),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->icon('heroicon-o-user-minus')->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No users yet');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
