<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\StaffScheduleResource\Pages;
use App\Models\Staff;
use App\Models\StaffSchedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class StaffScheduleResource extends Resource
{
    protected static ?string $model = StaffSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Catalogue';
    protected static ?string $navigationLabel = 'Horaires';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('staff_id')
                ->label('Employé')
                ->required()
                ->options(fn () => Staff::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->toArray()
                )
                ->searchable(),

            Forms\Components\Select::make('day_of_week')
                ->label('Jour')
                ->required()
                ->options(self::daysOfWeek()),

            Forms\Components\TimePicker::make('start_time')
                ->label('Début')
                ->required()
                ->seconds(false),

            Forms\Components\TimePicker::make('end_time')
                ->label('Fin')
                ->required()
                ->seconds(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('Employé')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Jour')
                    ->formatStateUsing(fn ($state) => self::daysOfWeek()[(int) $state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Début')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Fin')
                    ->sortable(),
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
            'index' => Pages\ListStaffSchedules::route('/'),
            'create' => Pages\CreateStaffSchedule::route('/create'),
            'edit' => Pages\EditStaffSchedule::route('/{record}/edit'),
        ];
    }

    private static function daysOfWeek(): array
    {
        return [
            0 => 'Dimanche',
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
        ];
    }
}
