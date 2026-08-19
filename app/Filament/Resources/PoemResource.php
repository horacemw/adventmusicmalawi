<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoemResource\Pages;
use App\Models\Artist;
use App\Models\Category;
use App\Models\Church;
use App\Models\Language;
use App\Models\Poem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PoemResource extends Resource
{
    protected static ?string $model = Poem::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Poems';
    protected static ?int $navigationSort = 20;
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $c = static::getModel()::published()->count();
        return $c > 0 ? (string) $c : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Poem')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Textarea::make('summary')
                        ->rows(2)
                        ->maxLength(500)
                        ->placeholder('Short description or first stanza (optional)')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('body')
                        ->required()
                        ->rows(16)
                        ->maxLength(20000)
                        ->placeholder("Paste or type the poem here.\n\nPreserve line breaks — they will be shown as-is on the public page.")
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Author')
                ->description('Attribute the poem to an artist or to a church. Anonymous poems can leave both blank.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('artist_id')
                        ->label('Author')
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
                                    ->title('Matched an existing author')
                                    ->body($existing->name.' is already in the database. Using the existing record.')
                                    ->warning()->send();
                                return $existing->id;
                            }
                            return Artist::create($data)->id;
                        }),
                    Forms\Components\Select::make('church_id')
                        ->label('Church')
                        ->relationship('church', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            $existing = Church::whereRaw('LOWER(name) = ?', [strtolower(trim($data['name']))])->first();
                            if ($existing) {
                                Notification::make()
                                    ->title('Matched an existing church')
                                    ->body($existing->name.' is already in the database. Using the existing record.')
                                    ->warning()->send();
                                return $existing->id;
                            }
                            return Church::create($data)->id;
                        }),
                ]),

            Forms\Components\Section::make('Categorisation')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('language_id')
                        ->label('Language')
                        ->relationship('language', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                        ])
                        ->createOptionUsing(fn (array $data) => Category::create(['name' => $data['name'], 'is_active' => true])->id),
                ]),

            Forms\Components\Section::make('Media')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Cover image')
                        ->image()
                        ->imageEditor()
                        ->directory('poems/covers')
                        ->disk('public')
                        ->maxSize(5 * 1024),
                    Forms\Components\FileUpload::make('document_path')
                        ->label('Original document (optional)')
                        ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->directory('poems/documents')
                        ->disk('public')
                        ->maxSize(10 * 1024),
                ]),

            Forms\Components\Section::make('Publication')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            Poem::STATUS_DRAFT => 'Draft',
                            Poem::STATUS_PENDING => 'Pending review',
                            Poem::STATUS_PUBLISHED => 'Published',
                            Poem::STATUS_REJECTED => 'Rejected',
                            Poem::STATUS_SUSPENDED => 'Suspended',
                        ])
                        ->default(Poem::STATUS_DRAFT)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state === Poem::STATUS_PUBLISHED) {
                                $set('published_at', now()->toDateTimeString());
                            }
                        }),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->native(false)
                        ->label('Published at'),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Featured'),
                    Forms\Components\Toggle::make('allow_download')
                        ->label('Allow document download')
                        ->default(true)
                        ->helperText('If enabled, listeners can download the original document (when available)'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->label('')->circular()->size(40),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('artist.name')->label('Author')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('church.name')->label('Church')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('category.name')->label('Category')->toggleable(),
                Tables\Columns\TextColumn::make('language.name')->label('Language')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => Poem::STATUS_DRAFT,
                        'warning' => Poem::STATUS_PENDING,
                        'success' => Poem::STATUS_PUBLISHED,
                        'danger' => [Poem::STATUS_REJECTED, Poem::STATUS_SUSPENDED],
                    ]),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Featured'),
                Tables\Columns\TextColumn::make('view_count')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('published_at')->dateTime('M j, Y')->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    Poem::STATUS_DRAFT => 'Draft',
                    Poem::STATUS_PENDING => 'Pending',
                    Poem::STATUS_PUBLISHED => 'Published',
                    Poem::STATUS_REJECTED => 'Rejected',
                    Poem::STATUS_SUSPENDED => 'Suspended',
                ]),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured'),
                Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'name')->label('Category'),
                Tables\Filters\SelectFilter::make('language_id')->relationship('language', 'name')->label('Language'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Poem $p) => $p->status !== Poem::STATUS_PUBLISHED)
                    ->action(function (Poem $p) {
                        $p->update([
                            'status' => Poem::STATUS_PUBLISHED,
                            'published_at' => $p->published_at ?? now(),
                        ]);
                        Notification::make()->title('Poem published')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $poem) {
                                $poem->update([
                                    'status' => Poem::STATUS_PUBLISHED,
                                    'published_at' => $poem->published_at ?? now(),
                                ]);
                            }
                            Notification::make()->title(count($records).' poems published')->success()->send();
                        }),
                    Tables\Actions\BulkAction::make('feature')
                        ->icon('heroicon-o-star')
                        ->action(fn ($records) => $records->each->update(['is_featured' => true])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPoems::route('/'),
            'create' => Pages\CreatePoem::route('/create'),
            'edit' => Pages\EditPoem::route('/{record}/edit'),
        ];
    }
}
