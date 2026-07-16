<?php

namespace App\Filament\App\Pages;

use App\Core\Tenancy\TenantManager;
use App\Models\Feedback;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SendFeedback extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Aide';

    protected static ?string $navigationLabel = 'Donner mon avis';

    protected static ?string $title = 'Aidez-nous à améliorer Reservix';

    protected static string $view = 'filament.app.pages.send-feedback';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'category' => 'improvement',
            'contact_email' => Filament::auth()->user()?->email,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category')
                    ->label('Type de retour')
                    ->required()
                    ->options([
                        'improvement' => 'Idée d’amélioration',
                        'problem' => 'Problème rencontré',
                        'question' => 'Question',
                        'other' => 'Autre',
                    ]),
                Forms\Components\Select::make('rating')
                    ->label('Satisfaction globale')
                    ->placeholder('Non renseignée')
                    ->options([
                        1 => '1 — Très insatisfait',
                        2 => '2 — Insatisfait',
                        3 => '3 — Correct',
                        4 => '4 — Satisfait',
                        5 => '5 — Très satisfait',
                    ]),
                Forms\Components\Textarea::make('message')
                    ->label('Votre retour')
                    ->helperText('Expliquez ce que vous cherchiez à faire et ce qui devrait être amélioré.')
                    ->required()
                    ->minLength(10)
                    ->maxLength(5000)
                    ->rows(7)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('contact_email')
                    ->label('Email de suivi')
                    ->email()
                    ->required()
                    ->maxLength(255),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $business = TenantManager::get() ?? abort(403);
        $user = Filament::auth()->user();

        Feedback::query()->create([
            ...$data,
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'status' => 'new',
        ]);

        $this->form->fill([
            'category' => 'improvement',
            'contact_email' => $user?->email,
        ]);

        Notification::make()
            ->title('Merci, votre retour a été envoyé')
            ->body('Il est maintenant visible dans l’administration Reservix.')
            ->success()
            ->send();
    }
}
