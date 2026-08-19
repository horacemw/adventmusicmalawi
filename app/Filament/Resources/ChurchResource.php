<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChurchResource\Pages;
use App\Filament\Resources\ChurchResource\RelationManagers;
use App\Models\Church;
use App\Models\District;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChurchResource extends Resource
{
    protected static ?string $model = Church::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 40;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->rows(3)
                        ->maxLength(2000)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('region_id')
                        ->label('Region')
                        ->options(Region::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('district_id', null)),
                    Forms\Components\Select::make('district_id')
                        ->label('District')
                        ->options(fn (Forms\Get $get) => $get('region_id')
                            ? District::where('region_id', $get('region_id'))->orderBy('name')->pluck('name', 'id')
                            : District::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('address')->maxLength(255)->columnSpanFull(),
                    Forms\Components\TextInput::make('phone')->tel()->maxLength(32),
                    Forms\Components\TextInput::make('email')->email()->maxLength(255),
                    Forms\Components\TextInput::make('website')->url()->prefix('https://')->maxLength(255)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Media')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Profile image')
                        ->image()
                        ->imageEditor()
                        ->directory('churches/images')
                        ->disk('public')
                        ->maxSize(5 * 1024),
                    Forms\Components\FileUpload::make('cover_path')
                        ->label('Cover image')
                        ->image()
                        ->imageEditor()
                        ->directory('churches/covers')
                        ->disk('public')
                        ->maxSize(5 * 1024),
                ]),

            Forms\Components\Section::make('Status')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('is_active')->default(true)->inline(false),
                    Forms\Components\Toggle::make('is_verified')->inline(false),
                    Forms\Components\Toggle::make('is_featured')->inline(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(url('/favicon.ico')),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('district.name')->label('District')->sortable(),
                Tables\Columns\TextColumn::make('region.name')->label('Region')->sortable(),
                Tables\Columns\TextColumn::make('music_groups_count')->counts('musicGroups')->label('Groups')->badge(),
                Tables\Columns\TextColumn::make('songs_count')->counts('songs')->label('Songs')->badge()->color('success'),
                Tables\Columns\IconColumn::make('is_verified')->label('Verified')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('region_id')
                    ->label('Region')
                    ->options(Region::orderBy('name')->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_verified')->label('Verified'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('verify')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_verified' => true])),
                    Tables\Actions\BulkAction::make('feature')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_featured' => true])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No churches yet')
            ->emptyStateDescription('Add the first church to start linking choirs and songs to real congregations.')
            ->emptyStateIcon('heroicon-o-building-library');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MusicGroupsRelationManager::class,
            RelationManagers\ArtistsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChurches::route('/'),
            'create' => Pages\CreateChurch::route('/create'),
            'edit' => Pages\EditChurch::route('/{record}/edit'),
        ];
    }
}
