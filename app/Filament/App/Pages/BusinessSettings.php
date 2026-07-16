<?php

namespace App\Filament\App\Pages;

use App\Core\Tenancy\TenantManager;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BusinessSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Paramètres de réservation';

    protected static ?string $title = 'Paramètres de réservation';

    protected static string $view = 'filament.app.pages.business-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $business = $this->business();

        $this->form->fill([
            'name' => $business->name,
            'email' => $business->email,
            'phone' => $business->phone,
            'timezone' => $business->timezone,
            'booking_url' => route('public.booking.page', ['business' => $business->slug]),
            'is_booking_enabled' => $business->is_booking_enabled,
            'booking_min_notice_minutes' => $business->booking_min_notice_minutes,
            'booking_max_advance_days' => $business->booking_max_advance_days,
            'slot_interval_minutes' => $business->slot_interval_minutes,
            'cancellation_notice_hours' => $business->cancellation_notice_hours,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Entreprise')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom public')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email de notification')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Select::make('timezone')
                            ->label('Fuseau horaire')
                            ->required()
                            ->options([
                                'America/Montreal' => 'Montréal',
                                'America/Toronto' => 'Toronto',
                                'America/Vancouver' => 'Vancouver',
                                'Europe/Paris' => 'Paris',
                                'UTC' => 'UTC',
                            ]),
                        Forms\Components\TextInput::make('booking_url')
                            ->label('Lien public')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Règles de réservation')
                    ->schema([
                        Forms\Components\Toggle::make('is_booking_enabled')
                            ->label('Accepter les réservations publiques')
                            ->helperText('Désactive immédiatement la page et les endpoints publics.'),
                        Forms\Components\Select::make('booking_min_notice_minutes')
                            ->label('Préavis minimal')
                            ->required()
                            ->options([
                                0 => 'Aucun',
                                30 => '30 minutes',
                                60 => '1 heure',
                                120 => '2 heures',
                                240 => '4 heures',
                                1440 => '24 heures',
                            ]),
                        Forms\Components\Select::make('booking_max_advance_days')
                            ->label('Réservable jusqu’à')
                            ->required()
                            ->options([
                                7 => '7 jours',
                                14 => '14 jours',
                                30 => '30 jours',
                                60 => '60 jours',
                                90 => '90 jours',
                                180 => '180 jours',
                                365 => '1 an',
                            ]),
                        Forms\Components\Select::make('slot_interval_minutes')
                            ->label('Intervalle des créneaux')
                            ->required()
                            ->options([
                                5 => '5 minutes',
                                10 => '10 minutes',
                                15 => '15 minutes',
                                20 => '20 minutes',
                                30 => '30 minutes',
                                60 => '60 minutes',
                            ]),
                        Forms\Components\Select::make('cancellation_notice_hours')
                            ->label('Annulation autorisée')
                            ->required()
                            ->options([
                                0 => 'Jusqu’au rendez-vous',
                                1 => '1 heure avant',
                                2 => '2 heures avant',
                                4 => '4 heures avant',
                                12 => '12 heures avant',
                                24 => '24 heures avant',
                                48 => '48 heures avant',
                            ]),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->business()->update($data);

        Notification::make()
            ->title('Paramètres enregistrés')
            ->success()
            ->send();
    }

    private function business(): Business
    {
        return TenantManager::get() ?? abort(403);
    }
}
