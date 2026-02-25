<?php

namespace App\Filament\App\Resources\BookingResource\Pages;

use App\Domain\Booking\Actions\CreateBookingAction;
use App\Filament\App\Resources\BookingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        /** @var CreateBookingAction $action */
        $action = app(CreateBookingAction::class);

        try {
            return $action->run($data);
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
}
