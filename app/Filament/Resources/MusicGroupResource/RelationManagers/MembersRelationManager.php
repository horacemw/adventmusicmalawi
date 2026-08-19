<?php

namespace App\Filament\Resources\MusicGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';
    protected static ?string $title = 'Members';
    protected static ?string $icon = 'heroicon-o-users';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('role')->maxLength(255)->placeholder('e.g. Choir director'),
            Forms\Components\Select::make('voice_part')
                ->options([
                    'soprano' => 'Soprano', 'alto' => 'Alto',
                    'tenor' => 'Tenor', 'bass' => 'Bass',
                    'lead' => 'Lead', 'backing' => 'Backing',
                ]),
            Forms\Components\Toggle::make('is_leader'),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\DatePicker::make('joined_at')->native(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->weight('semibold'),
                Tables\Columns\TextColumn::make('role'),
                Tables\Columns\TextColumn::make('voice_part')->badge(),
                Tables\Columns\IconColumn::make('is_leader')->boolean()->label('Leader'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
