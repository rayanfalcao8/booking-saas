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
                Rule::exists('services', 'id')->where(fn ($query) => $query->where('business_id', TenantManager::id())),
            ],
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('staff', 'id')->where(fn ($query) => $query->where('business_id', TenantManager::id())),
            ],
            'date' => ['required', 'date_format:Y-m-d'],
            'step_min' => ['nullable', 'integer', 'min:5', 'max:60'],
        ];
    }
}
