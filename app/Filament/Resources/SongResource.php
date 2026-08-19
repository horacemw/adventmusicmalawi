<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SongResource\Pages;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Category;
use App\Models\Church;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Mood;
use App\Models\MusicGroup;
use App\Models\Occasion;
use App\Models\Song;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SongResource extends Resource
{
    protected static ?string $model = Song::class;
    protected static ?string $navigationIcon = 'heroicon-o-musical-note';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Music library';
    protected static ?int $navigationSort = 10;
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $c = static::getModel()::published()->count();
        return $c > 0 ? (string) $c : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Song')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->label('Lyrics (optional)')
                        ->rows(6)
                        ->maxLength(5000)
                        ->placeholder('Paste the lyrics here — or leave blank if lyrics are not available')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('release_year')->numeric()->minValue(1900)->maxValue(now()->year + 1),
                    Forms\Components\DatePicker::make('released_at')->native(false)->label('Release date'),
                    Forms\Components\TextInput::make('track_number')->numeric()->minValue(1),
                ]),

            Forms\Components\Section::make('Performers')
                ->description('Pick the artist, choir or music group. Selecting a group auto-links its affiliated church. Featured artists are optional collaborators.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('music_group_id')
                        ->label('Music group / choir')
                        ->options(fn () => MusicGroup::with('church')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn ($g) => [
                                $g->id => $g->name.($g->church ? ' — '.$g->church->name : ''),
                            ]))
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state && ($group = MusicGroup::find($state)) && $group->church_id) {
                                $set('church_id', $group->church_id);
                            }
                        })
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required()->maxLength(255),
                            Forms\Components\Select::make('type')
                                ->options([
                                    'choir' => 'Choir', 'quartet' => 'Quartet', 'acapella' => 'Acapella',
                                    'youth' => 'Youth', 'children' => "Children's",
                                    'pathfinder' => 'Pathfinder', 'adventurer' => 'Adventurer',
                                    'men' => "Men's", 'women' => "Women's",
                                    'ministry' => 'Ministry', 'other' => 'Other',
                                ])->default('choir')->required(),
                            Forms\Components\Select::make('church_id')
                                ->label('Church')
                                ->relationship('church', 'name')
                                ->searchable()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                ])
                                ->createOptionUsing(fn (array $data) => Church::create($data)->id),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            $existing = MusicGroup::whereRaw('LOWER(name) = ?', [strtolower(trim($data['name']))])->first();
                            if ($existing) {
                                Notification::make()
                                    ->title('Matched an existing group — using that one')
                                    ->body($existing->name.' already exists in the database. Using the existing record to avoid duplicates.')
                                    ->warning()->send();
                                return $existing->id;
                            }
                            return MusicGroup::create($data)->id;
                        }),

                    Forms\Components\Select::make('artist_id')
                        ->label('Primary solo artist')
                        ->helperText('Use this when the song is by an individual, not a group')
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
                        ->createOptionUsing(function (array $data): int {
                            $existing = Artist::whereRaw('LOWER(name) = ?', [strtolower(trim($data['name']))])->first();
                            if ($existing) {
                                Notification::make()
                                    ->title('Matched an existing artist')
                                    ->body($existing->name.' is already in the database. Using the existing record.')
                                    ->warning()->send();
                                return $existing->id;
                            }
                            return Artist::create($data)->id;
                        }),

                    Forms\Components\Select::make('featured_artist_ids')
                        ->label('Featured artists')
                        ->multiple()
                        ->options(Artist::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),

                    Forms\Components\Select::make('church_id')
                        ->label('Affiliated church')
                        ->helperText('Auto-filled from the group above. Override only if this song is a compilation.')
                        ->relationship('church', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required()->maxLength(255),
                            Forms\Components\Select::make('region_id')
                                ->label('Region')
                                ->options(\App\Models\Region::orderBy('name')->pluck('name', 'id'))
                                ->searchable(),
                        ])
                        ->createOptionUsing(fn (array $data) => Church::create($data)->id),

                    Forms\Components\Select::make('album_id')
                        ->label('Album')
                        ->helperText('Optional — leave blank for single releases')
                        ->relationship('album', 'title')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('title')->required()->maxLength(255),
                            Forms\Components\TextInput::make('release_year')->numeric(),
                        ])
                        ->createOptionUsing(fn (array $data) => Album::create($data)->id),
                ]),

            Forms\Components\Section::make('Categorisation')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('language_id')
                        ->label('Language')
                        ->options(Language::orderBy('sort_order')->pluck('name', 'id'))
                        ->searchable(),
                    Forms\Components\Select::make('genre_id')
                        ->label('Genre')
                        ->options(Genre::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                    Forms\Components\Select::make('category_ids')
                        ->label('Categories')
                        ->multiple()
                        ->options(Category::orderBy('sort_order')->pluck('name', 'id'))
                        ->searchable(),
                    Forms\Components\Select::make('occasion_ids')
                        ->label('Occasions')
                        ->multiple()
                        ->options(Occasion::orderBy('sort_order')->pluck('name', 'id'))
                        ->searchable(),
                    Forms\Components\Select::make('mood_ids')
                        ->label('Moods')
                        ->multiple()
                        ->options(Mood::orderBy('sort_order')->pluck('name', 'id'))
                        ->searchable()
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Media')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('audio_path')
                        ->label('Audio file')
                        ->required()
                        ->disk('public')
                        ->directory('songs/audio')
                        ->acceptedFileTypes(['audio/mpeg', 'audio/mp4', 'audio/aac', 'audio/wav', 'audio/x-wav'])
                        ->maxSize(50 * 1024)
                        ->helperText('MP3, AAC, or WAV. Up to 50 MB.'),
                    Forms\Components\FileUpload::make('artwork_path')
                        ->label('Artwork')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('songs/artwork')
                        ->maxSize(5 * 1024),
                ]),

            Forms\Components\Section::make('Copyright')
                ->columns(2)
                ->collapsible()
                ->collapsed(false)
                ->schema([
                    Forms\Components\TextInput::make('copyright_owner')->maxLength(255),
                    Forms\Components\TextInput::make('rights_holder')->maxLength(255),
                    Forms\Components\Select::make('permission_status')
                        ->options([
                            'owned' => 'Owned',
                            'licensed' => 'Licensed',
                            'permission_granted' => 'Permission granted',
                            'public_domain' => 'Public domain',
                            'unknown' => 'Unknown',
                        ])
                        ->default('owned'),
                    Forms\Components\Select::make('license_type')
                        ->options([
                            'all_rights_reserved' => 'All rights reserved',
                            'cc_by' => 'CC BY', 'cc_by_sa' => 'CC BY-SA',
                            'cc0' => 'CC0 (public domain)', 'custom' => 'Custom',
                        ]),
                    Forms\Components\Toggle::make('distribution_allowed')->default(true),
                    Forms\Components\Toggle::make('monetization_allowed'),
                    Forms\Components\Textarea::make('copyright_notes')->rows(2)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Publication')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('publish_immediately')
                        ->default(true)
                        ->helperText('When on, the song appears on the public site right after save')
                        ->inline(false),
                    Forms\Components\Toggle::make('is_featured')
                        ->helperText('Show in Featured / homepage rows')
                        ->inline(false),
                    Forms\Components\Toggle::make('allow_download')->inline(false),
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
                Tables\Columns\TextColumn::make('musicGroup.name')->label('Group / Artist')
                    ->formatStateUsing(function ($record) {
                        if ($record->musicGroup) return $record->musicGroup->name;
                        if ($record->artist) return $record->artist->name;
                        return $record->church?->name ?? '—';
                    })
                    ->searchable(query: fn ($q, $s) => $q
                        ->orWhereHas('musicGroup', fn ($q2) => $q2->where('name', 'like', "%$s%"))
                        ->orWhereHas('artist', fn ($q2) => $q2->where('name', 'like', "%$s%"))),
                Tables\Columns\TextColumn::make('church.name')->label('Church')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('album.title')->label('Album')->placeholder('—')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => Song::STATUS_DRAFT,
                        'warning' => Song::STATUS_PENDING,
                        'success' => Song::STATUS_PUBLISHED,
                        'danger' => Song::STATUS_REJECTED,
                    ])
                    ->formatStateUsing(fn (string $s) => ucwords($s)),
                Tables\Columns\TextColumn::make('stream_count')->label('Plays')->sortable()->numeric(),
                Tables\Columns\IconColumn::make('is_featured')->label('★')->boolean()->trueIcon('heroicon-s-star')->trueColor('warning'),
                Tables\Columns\TextColumn::make('published_at')->label('Published')->dateTime('d M Y')->sortable()->toggleable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Song::STATUS_DRAFT => 'Draft',
                        Song::STATUS_PENDING => 'Pending',
                        Song::STATUS_PUBLISHED => 'Published',
                        Song::STATUS_REJECTED => 'Rejected',
                        Song::STATUS_WITHDRAWN => 'Withdrawn',
                        Song::STATUS_SUSPENDED => 'Suspended',
                    ]),
                Tables\Filters\SelectFilter::make('music_group_id')->label('Group')->relationship('musicGroup', 'name')->searchable(),
                Tables\Filters\SelectFilter::make('artist_id')->label('Artist')->relationship('artist', 'name')->searchable(),
                Tables\Filters\SelectFilter::make('church_id')->label('Church')->relationship('church', 'name')->searchable(),
                Tables\Filters\TernaryFilter::make('is_featured'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_publish')
                    ->label(fn (Song $r) => $r->status === Song::STATUS_PUBLISHED ? 'Unpublish' : 'Publish')
                    ->icon(fn (Song $r) => $r->status === Song::STATUS_PUBLISHED ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Song $r) => $r->status === Song::STATUS_PUBLISHED ? 'gray' : 'success')
                    ->action(function (Song $record) {
                        if ($record->status === Song::STATUS_PUBLISHED) {
                            $record->update(['status' => Song::STATUS_WITHDRAWN]);
                        } else {
                            $record->update(['status' => Song::STATUS_PUBLISHED, 'published_at' => $record->published_at ?? now()]);
                        }
                    }),
                Tables\Actions\Action::make('toggle_feature')
                    ->label(fn (Song $r) => $r->is_featured ? 'Unfeature' : 'Feature')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(fn (Song $r) => $r->update(['is_featured' => !$r->is_featured])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->icon('heroicon-o-check-circle')->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => Song::STATUS_PUBLISHED, 'published_at' => now()])),
                    Tables\Actions\BulkAction::make('unpublish')
                        ->icon('heroicon-o-eye-slash')->color('gray')
                        ->action(fn ($records) => $records->each->update(['status' => Song::STATUS_WITHDRAWN])),
                    Tables\Actions\BulkAction::make('feature')
                        ->icon('heroicon-o-star')->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_featured' => true])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No songs yet')
            ->emptyStateDescription('Upload the first song directly, or approve a paid submission from Moderation.')
            ->emptyStateActions([
                Tables\Actions\Action::make('add')
                    ->label('Add song')
                    ->icon('heroicon-o-plus')
                    ->url(fn () => SongResource::getUrl('create'))
                    ->button(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSongs::route('/'),
            'create' => Pages\CreateSong::route('/create'),
            'edit' => Pages\EditSong::route('/{record}/edit'),
        ];
    }
}
