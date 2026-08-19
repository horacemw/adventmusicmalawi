<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlbumResource\Pages;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Church;
use App\Models\Language;
use App\Models\MusicGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlbumResource extends Resource
{
    protected static ?string $model = Album::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 15;
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Album details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Textarea::make('description')->rows(3)->maxLength(2000)->columnSpanFull(),
                    Forms\Components\TextInput::make('label')->maxLength(255),
                    Forms\Components\TextInput::make('release_year')
                        ->numeric()->minValue(1900)->maxValue(now()->year + 1),
                ]),

            Forms\Components\Section::make('Performer')
                ->description('Pick the artist or the group. If both are relevant, prefer the one that leads the album.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('artist_id')
                        ->label('Solo artist')
                        ->relationship('artist', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required()->maxLength(255),
                            Forms\Components\TextInput::make('stage_name')->maxLength(255),
                            Forms\Components\Select::make('church_id')
                                ->label('Church')
                                ->relationship('church', 'name')
                                ->searchable(),
                        ])
                        ->createOptionUsing(fn (array $data) => Artist::create($data)->id),
                    Forms\Components\Select::make('music_group_id')
                        ->label('Music group / choir')
                        ->relationship('musicGroup', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required()->maxLength(255),
                            Forms\Components\Select::make('type')
                                ->options([
                                    'choir' => 'Choir', 'quartet' => 'Quartet', 'acapella' => 'Acapella',
                                    'youth' => 'Youth', 'children' => "Children's",
                                ])->default('choir'),
                            Forms\Components\Select::make('church_id')
                                ->label('Church')
                                ->relationship('church', 'name')
                                ->searchable(),
                        ])
                        ->createOptionUsing(fn (array $data) => MusicGroup::create($data)->id),
                    Forms\Components\Select::make('church_id')
                        ->label('Affiliated church')
                        ->relationship('church', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Usually inherited from the group — set this only for compilations'),
                    Forms\Components\Select::make('primary_language_id')
                        ->label('Primary language')
                        ->options(Language::orderBy('sort_order')->pluck('name', 'id')),
                ]),

            Forms\Components\Section::make('Cover artwork')
                ->schema([
                    Forms\Components\FileUpload::make('artwork_path')
                        ->image()
                        ->imageEditor()
                        ->directory('albums/artwork')
                        ->disk('public')
                        ->maxSize(5 * 1024),
                ]),

            Forms\Components\Section::make('Publication')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('is_published')->default(true)->inline(false),
                    Forms\Components\Toggle::make('is_featured')->inline(false),
                    Forms\Components\DatePicker::make('released_at')->native(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('artwork_path')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->size(48)
                    ->defaultImageUrl(url('/favicon.ico')),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('musicGroup.name')
                    ->label('Group')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('artist.name')
                    ->label('Artist')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('release_year')->sortable(),
                Tables\Columns\TextColumn::make('songs_count')->counts('songs')->label('Tracks')->badge()->color('success'),
                Tables\Columns\IconColumn::make('is_featured')->label('Featured')->boolean()->trueIcon('heroicon-s-star')->trueColor('warning'),
                Tables\Columns\IconColumn::make('is_published')->label('Published')->boolean(),
            ])
            ->defaultSort('release_year', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('music_group_id')->label('Group')->relationship('musicGroup', 'name')->searchable(),
                Tables\Filters\SelectFilter::make('artist_id')->label('Artist')->relationship('artist', 'name')->searchable(),
                Tables\Filters\TernaryFilter::make('is_published'),
                Tables\Filters\TernaryFilter::make('is_featured'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->icon('heroicon-o-check-circle')->color('success')
                        ->action(fn ($records) => $records->each->update(['is_published' => true])),
                    Tables\Actions\BulkAction::make('feature')
                        ->icon('heroicon-o-star')->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_featured' => true])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No albums yet');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\AlbumResource\RelationManagers\SongsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlbums::route('/'),
            'create' => Pages\CreateAlbum::route('/create'),
            'edit' => Pages\EditAlbum::route('/{record}/edit'),
        ];
    }
}
