<?php

namespace App\Filament\Resources\ChurchResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ArtistsRelationManager extends RelationManager
{
    protected static string $relationship = 'artists';
    protected static ?string $title = 'Artists';
    protected static ?string $icon = 'heroicon-o-microphone';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('stage_name')->maxLength(255),
            Forms\Components\Textarea::make('bio')->rows(2)->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('stage_name')->searchable(),
                Tables\Columns\TextColumn::make('songs_count')->counts('songs')->badge()->color('success'),
                Tables\Columns\IconColumn::make('is_verified')->boolean(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
