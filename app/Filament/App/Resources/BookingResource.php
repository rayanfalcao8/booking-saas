<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\BookingResource\Pages;
use App\Domain\Booking\Actions\UpdateBookingStatusAction;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Staff;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Bookings';

    protected static ?string $navigationLabel = 'Rendez-vous';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Détails')
                ->schema([
                    Forms\Components\Select::make('service_id')
                        ->label('Service')
                        ->required()
                        ->options(fn () => Service::query()
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                        )
                        ->searchable(),

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

                    Forms\Components\DatePicker::make('date')
                        ->label('Date')
                        ->required(),

                    Forms\Components\TimePicker::make('start_time')
                        ->label('Heure de début')
                        ->required()
                        ->seconds(false),

                    Forms\Components\TimePicker::make('end_time')
                        ->label('Heure de fin')
                        ->required()
                        ->seconds(false),

                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->required()
                        ->disabled()
                        ->options([
                            'confirmed' => 'Confirmé',
                            'canceled' => 'Annulé',
                            'no_show' => 'No-show',
                        ])
                        ->default('confirmed'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Client')
                ->schema([
                    Forms\Components\TextInput::make('customer_name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('customer_email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255)
                        ->nullable(),

                    Forms\Components\TextInput::make('customer_phone')
                        ->label('Téléphone')
                        ->maxLength(50)
                        ->nullable(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->maxLength(2000)
                        ->columnSpanFull()
                        ->nullable(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Début')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Fin')
                    ->sortable(),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('staff.name')
                    ->label('Employé')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Client')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'success' => 'confirmed',
                        'danger' => 'canceled',
                        'warning' => 'no_show',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'confirmed' => 'Confirmé',
                        'canceled' => 'Annulé',
                        'no_show' => 'No-show',
                        default => $state,
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_no_show')
                    ->label('Marquer no-show')
                    ->color('warning')
                    ->visible(fn (Booking $record) => $record->status === 'confirmed')
                    ->requiresConfirmation()
                    ->action(function (Booking $record): void {
                        try {
                            app(UpdateBookingStatusAction::class)->run($record, 'no_show');

                            Notification::make()
                                ->title('Réservation marquée no-show.')
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title(collect($exception->errors())->flatten()->first() ?? 'Transition impossible.')
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('cancel_booking')
                    ->label('Annuler')
                    ->color('danger')
                    ->visible(fn (Booking $record) => in_array($record->status, ['confirmed', 'no_show'], true))
                    ->requiresConfirmation()
                    ->action(function (Booking $record): void {
                        try {
                            app(UpdateBookingStatusAction::class)->run($record, 'canceled');

                            Notification::make()
                                ->title('Réservation annulée.')
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title(collect($exception->errors())->flatten()->first() ?? 'Annulation impossible.')
                                ->danger()
                                ->send();
                        }
                    }),

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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
