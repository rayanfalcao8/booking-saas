<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Models\Feedback;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Produit';

    protected static ?string $navigationLabel = 'Retours utilisateurs';

    protected static ?string $modelLabel = 'retour';

    protected static ?string $pluralModelLabel = 'retours';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('business.name')
                    ->label('Entreprise')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('contact_email')
                    ->label('Email de suivi')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Select::make('category')
                    ->label('Catégorie')
                    ->disabled()
                    ->dehydrated(false)
                    ->options(self::categories()),
                Forms\Components\Select::make('rating')
                    ->label('Note')
                    ->disabled()
                    ->dehydrated(false)
                    ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
                Forms\Components\Textarea::make('message')
                    ->label('Message')
                    ->disabled()
                    ->dehydrated(false)
                    ->rows(8)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->required()
                    ->options(self::statuses()),
                Forms\Components\Textarea::make('internal_note')
                    ->label('Note interne')
                    ->maxLength(5000)
                    ->rows(5)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Entreprise')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')
                    ->formatStateUsing(fn (string $state): string => self::categories()[$state] ?? $state),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Note')
                    ->placeholder('—')
                    ->suffix('/5'),
                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(70)
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statuses()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'danger',
                        'reviewing' => 'warning',
                        'planned' => 'primary',
                        'resolved' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reçu le')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::statuses()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Traiter'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedback::route('/'),
            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }

    private static function categories(): array
    {
        return [
            'improvement' => 'Amélioration',
            'problem' => 'Problème',
            'question' => 'Question',
            'other' => 'Autre',
        ];
    }

    private static function statuses(): array
    {
        return [
            'new' => 'Nouveau',
            'reviewing' => 'En analyse',
            'planned' => 'Planifié',
            'resolved' => 'Résolu',
            'closed' => 'Fermé',
        ];
    }
}
