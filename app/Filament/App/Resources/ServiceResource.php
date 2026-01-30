<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Catalogue';
    protected static ?string $navigationLabel = 'Services';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nom du service')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('duration_min')
                ->label('Durée')
                ->required()
                ->options([
                    15 => '15 min',
                    30 => '30 min',
                    45 => '45 min',
                    60 => '60 min',
                    90 => '90 min',
                    120 => '120 min',
                ]),

            Forms\Components\Select::make('buffer_min')
                ->label('Buffer (marge)')
                ->required()
                ->default(0)
                ->options([
                    0 => '0 min',
                    5 => '5 min',
                    10 => '10 min',
                    15 => '15 min',
                    20 => '20 min',
                    30 => '30 min',
                ])
                ->helperText('Temps ajouté entre deux rendez-vous.'),

            Forms\Components\Toggle::make('is_active')
                ->label('Actif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Service')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_min')
                    ->label('Durée')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('buffer_min')
                    ->label('Buffer')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Modifier'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Supprimer'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
