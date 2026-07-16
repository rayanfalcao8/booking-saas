<?php

namespace App\Http\Requests;

use App\Core\Tenancy\TenantManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')->where(
                    fn ($query) => $query
                        ->where('business_id', TenantManager::id())
                        ->where('is_active', true)
                ),
            ],
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('staff', 'id')->where(
                    fn ($query) => $query
                        ->where('business_id', TenantManager::id())
                        ->where('is_active', true)
                ),
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'step_min' => ['nullable', 'integer', 'min:5', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Le service est obligatoire.',
            'service_id.exists' => 'Le service sélectionné est indisponible.',
            'staff_id.required' => 'Le prestataire est obligatoire.',
            'staff_id.exists' => 'Le prestataire sélectionné est indisponible.',
            'date.required' => 'La date est obligatoire.',
            'date.date_format' => 'Le format de la date est invalide.',
        ];
    }
}
