<?php

namespace App\Filament\Resources\ChurchResource\RelationManagers;

use App\Models\MusicGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MusicGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'musicGroups';
    protected static ?string $title = 'Choirs & groups';
    protected static ?string $icon = 'heroicon-o-user-group';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
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
            Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
            Forms\Components\TextInput::make('founded_year')->numeric()->minValue(1900)->maxValue(now()->year),
            Forms\Components\Toggle::make('is_verified'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->weight('semibold'),
                Tables\Columns\BadgeColumn::make('type'),
                Tables\Columns\TextColumn::make('songs_count')->counts('songs')->label('Songs')->badge()->color('success'),
                Tables\Columns\IconColumn::make('is_verified')->boolean()->label('Verified'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
