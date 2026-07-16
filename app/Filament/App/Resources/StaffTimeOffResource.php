<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\StaffTimeOffResource\Pages;
use App\Models\Staff;
use App\Models\StaffTimeOff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StaffTimeOffResource extends Resource
{
    protected static ?string $model = StaffTimeOff::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Absences';

    protected static ?string $modelLabel = 'absence';

    protected static ?string $pluralModelLabel = 'absences';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('staff_id')
                    ->label('Collaborateur')
                    ->required()
                    ->options(fn (): array => Staff::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray())
                    ->searchable(),
                Forms\Components\DatePicker::make('date')
                    ->label('Date')
                    ->required(),
                Forms\Components\TimePicker::make('start_time')
                    ->label('Début')
                    ->seconds(false)
                    ->helperText('Laissez les heures vides pour bloquer toute la journée.'),
                Forms\Components\TimePicker::make('end_time')
                    ->label('Fin')
                    ->seconds(false),
                Forms\Components\TextInput::make('reason')
                    ->label('Motif interne')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('Collaborateur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Début')
                    ->placeholder('Journée complète'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Fin')
                    ->placeholder('Journée complète'),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Motif')
                    ->placeholder('—')
                    ->limit(40),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Modifier'),
                Tables\Actions\DeleteAction::make()->label('Supprimer'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffTimeOff::route('/'),
            'create' => Pages\CreateStaffTimeOff::route('/create'),
            'edit' => Pages\EditStaffTimeOff::route('/{record}/edit'),
        ];
    }
}
