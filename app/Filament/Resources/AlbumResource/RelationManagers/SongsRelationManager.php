<?php

namespace App\Filament\Resources\AlbumResource\RelationManagers;

use App\Models\Song;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SongsRelationManager extends RelationManager
{
    protected static string $relationship = 'songs';
    protected static ?string $title = 'Tracks';
    protected static ?string $icon = 'heroicon-o-musical-note';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\TextInput::make('track_number')->numeric()->minValue(1),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('track_number')
            ->defaultSort('track_number')
            ->columns([
                Tables\Columns\TextColumn::make('track_number')->label('#')->sortable(),
                Tables\Columns\ImageColumn::make('artwork_path')->label('')->disk('public')->square()->size(36),
                Tables\Columns\TextColumn::make('title')->searchable()->weight('semibold'),
                Tables\Columns\TextColumn::make('musicGroup.name')->label('Group')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->formatStateUsing(fn ($s) => $s ? gmdate($s >= 3600 ? 'H:i:s' : 'i:s', $s) : '—'),
                Tables\Columns\TextColumn::make('stream_count')->label('Plays')->numeric()->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => Song::STATUS_DRAFT,
                        'warning' => Song::STATUS_PENDING,
                        'success' => Song::STATUS_PUBLISHED,
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('attach_existing')
                    ->label('Attach existing songs')
                    ->icon('heroicon-o-link')
                    ->form([
                        Forms\Components\Select::make('song_ids')
                            ->label('Songs')
                            ->multiple()
                            ->searchable()
                            ->options(function () {
                                $ownerId = $this->getOwnerRecord()->music_group_id;
                                $ownerArtistId = $this->getOwnerRecord()->artist_id;
                                return Song::query()
                                    ->whereNull('album_id')
                                    ->when($ownerId, fn ($q) => $q->where('music_group_id', $ownerId))
                                    ->when(!$ownerId && $ownerArtistId, fn ($q) => $q->where('artist_id', $ownerArtistId))
                                    ->orderBy('title')
                                    ->limit(200)
                                    ->pluck('title', 'id');
                            })
                            ->helperText('Only songs by this album\'s artist/group that aren\'t already in another album are shown.')
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $album = $this->getOwnerRecord();
                        $nextTrack = (int) ($album->songs()->max('track_number') ?? 0);
                        foreach ($data['song_ids'] as $songId) {
                            Song::where('id', $songId)->update([
                                'album_id' => $album->id,
                                'track_number' => ++$nextTrack,
                            ]);
                        }
                        Notification::make()
                            ->title(count($data['song_ids']).' songs attached')
                            ->success()->send();
                    }),
                Tables\Actions\Action::make('add_new')
                    ->label('Add new track')
                    ->icon('heroicon-o-plus')
                    ->url(fn () => \App\Filament\Resources\SongResource::getUrl('create')),
            ])
            ->actions([
                Tables\Actions\Action::make('detach')
                    ->label('Remove from album')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->action(fn (Song $record) => $record->update(['album_id' => null, 'track_number' => null])),
                Tables\Actions\EditAction::make()
                    ->url(fn (Song $record) => \App\Filament\Resources\SongResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('detach_all')
                    ->label('Remove from album')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->action(fn ($records) => $records->each->update(['album_id' => null, 'track_number' => null])),
            ]);
    }
}
