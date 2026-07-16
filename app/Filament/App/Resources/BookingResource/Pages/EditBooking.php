<?php

namespace App\Filament\App\Resources\BookingResource\Pages;

use App\Domain\Booking\Actions\UpdateBookingDetailsAction;
use App\Filament\App\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var UpdateBookingDetailsAction $action */
        $action = app(UpdateBookingDetailsAction::class);

        try {
            return $action->run($record, $data);
        } catch (ValidationException $exception) {
            $messages = collect($exception->errors())
                ->mapWithKeys(function (array $fieldMessages, string $field): array {
                    if (str_starts_with($field, 'data.')) {
                        return [$field => $fieldMessages];
                    }

                    return ["data.{$field}" => $fieldMessages];
                })
                ->all();

            throw ValidationException::withMessages($messages);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
