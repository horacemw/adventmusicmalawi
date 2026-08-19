<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MusicGroupResource\Pages;
use App\Filament\Resources\MusicGroupResource\RelationManagers;
use App\Models\Church;
use App\Models\District;
use App\Models\MusicGroup;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MusicGroupResource extends Resource
{
    protected static ?string $model = MusicGroup::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Choirs & groups';
    protected static ?int $navigationSort = 30;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Group details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\Select::make('type')
                        ->options([
                            'choir' => 'Choir',
                            'quartet' => 'Quartet',
                            'acapella' => 'Acapella',
                            'youth' => 'Youth',
                            'children' => "Children's",
                            'pathfinder' => 'Pathfinder',
                            'adventurer' => 'Adventurer',
                            'men' => "Men's",
                            'women' => "Women's",
                            'ministry' => 'Ministry',
                            'other' => 'Other',
                        ])
                        ->default('choir')
                        ->required(),
                    Forms\Components\TextInput::make('founded_year')->numeric()->minValue(1900)->maxValue(now()->year),
                    Forms\Components\Textarea::make('description')->rows(3)->maxLength(2000)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Home')
                ->description('Where the group is based. You can create a new church inline if it does not exist yet.')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('church_id')
                        ->label('Church')
                        ->relationship('church', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required()->maxLength(255),
                            Forms\Components\Select::make('region_id')
                                ->label('Region')
                                ->options(Region::orderBy('name')->pluck('name', 'id'))
                                ->searchable(),
                            Forms\Components\Textarea::make('description')->rows(2),
                        ])
                        ->createOptionUsing(fn (array $data) => Church::create($data)->id),
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
                        ->searchable(),
                ]),

            Forms\Components\Section::make('Media')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Profile image')
                        ->image()
                        ->imageEditor()
                        ->directory('groups/images')
                        ->disk('public')
                        ->maxSize(5 * 1024),
                    Forms\Components\FileUpload::make('cover_path')
                        ->label('Cover image')
                        ->image()
                        ->imageEditor()
                        ->directory('groups/covers')
                        ->disk('public')
                        ->maxSize(5 * 1024),
                ]),

            Forms\Components\Section::make('Contact')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('phone')->tel()->maxLength(32),
                    Forms\Components\TextInput::make('email')->email()->maxLength(255),
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
                Tables\Columns\BadgeColumn::make('type'),
                Tables\Columns\TextColumn::make('church.name')->label('Church')->searchable(),
                Tables\Columns\TextColumn::make('songs_count')->counts('songs')->label('Songs')->badge()->color('success'),
                Tables\Columns\IconColumn::make('is_verified')->label('Verified')->boolean(),
                Tables\Columns\IconColumn::make('is_featured')->label('Featured')->boolean()->trueIcon('heroicon-s-star')->trueColor('warning'),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'choir' => 'Choir', 'quartet' => 'Quartet', 'acapella' => 'Acapella',
                        'youth' => 'Youth', 'children' => "Children's", 'pathfinder' => 'Pathfinder',
                        'adventurer' => 'Adventurer', 'men' => "Men's", 'women' => "Women's",
                        'ministry' => 'Ministry', 'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('church_id')
                    ->label('Church')
                    ->relationship('church', 'name')
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('is_verified'),
                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('verify')
                        ->icon('heroicon-o-check-badge')->color('success')
                        ->action(fn ($records) => $records->each->update(['is_verified' => true])),
                    Tables\Actions\BulkAction::make('feature')
                        ->icon('heroicon-o-star')->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_featured' => true])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No groups yet')
            ->emptyStateDescription('Add the choirs, youth groups and music ministries that will submit songs to the platform.');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMusicGroups::route('/'),
            'create' => Pages\CreateMusicGroup::route('/create'),
            'edit' => Pages\EditMusicGroup::route('/{record}/edit'),
        ];
    }
}
