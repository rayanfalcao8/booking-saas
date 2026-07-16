<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessResource\Pages;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Plateforme';

    protected static ?string $navigationLabel = 'Businesses';

    protected static ?string $modelLabel = 'business';

    protected static ?string $pluralModelLabel = 'businesses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('slug')
                    ->label('Identifiant public')
                    ->helperText('Utilisé dans l’URL de réservation publique.')
                    ->required()
                    ->alphaDash()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Select::make('timezone')
                    ->label('Fuseau horaire')
                    ->required()
                    ->searchable()
                    ->options([
                        'America/Montreal' => 'Montréal',
                        'America/Toronto' => 'Toronto',
                        'America/Vancouver' => 'Vancouver',
                        'Europe/Paris' => 'Paris',
                        'UTC' => 'UTC',
                    ])
                    ->default('America/Montreal'),

                Forms\Components\TextInput::make('email')
                    ->label('Courriel')
                    ->email()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_booking_enabled')
                    ->label('Réservation publique active')
                    ->default(true),

                Forms\Components\Select::make('booking_min_notice_minutes')
                    ->label('Préavis minimal')
                    ->options([
                        0 => 'Aucun',
                        30 => '30 minutes',
                        60 => '1 heure',
                        120 => '2 heures',
                        240 => '4 heures',
                        1440 => '24 heures',
                    ])
                    ->default(60)
                    ->required(),

                Forms\Components\Select::make('booking_max_advance_days')
                    ->label('Horizon de réservation')
                    ->options([
                        7 => '7 jours',
                        14 => '14 jours',
                        30 => '30 jours',
                        60 => '60 jours',
                        90 => '90 jours',
                        180 => '180 jours',
                        365 => '1 an',
                    ])
                    ->default(90)
                    ->required(),

                Forms\Components\Select::make('slot_interval_minutes')
                    ->label('Intervalle entre les créneaux')
                    ->options([
                        5 => '5 minutes',
                        10 => '10 minutes',
                        15 => '15 minutes',
                        20 => '20 minutes',
                        30 => '30 minutes',
                        60 => '60 minutes',
                    ])
                    ->default(15)
                    ->required(),

                Forms\Components\Select::make('cancellation_notice_hours')
                    ->label('Délai limite d’annulation')
                    ->options([
                        0 => 'Jusqu’au rendez-vous',
                        1 => '1 heure avant',
                        2 => '2 heures avant',
                        4 => '4 heures avant',
                        12 => '12 heures avant',
                        24 => '24 heures avant',
                        48 => '48 heures avant',
                    ])
                    ->default(0)
                    ->required(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Identifiant public')
                    ->searchable(),

                Tables\Columns\TextColumn::make('timezone')
                    ->label('Fuseau horaire'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Utilisateurs')
                    ->counts('users')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Modifier'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'edit' => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }
}
