<?php

namespace App\Http\Requests;

use App\Core\Tenancy\TenantManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicBookRequest extends FormRequest
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
            'start_time' => ['required', 'date_format:H:i'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/', 'max:16'],
            'notes' => ['nullable', 'string', 'max:2000'],
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
            'start_time.required' => 'Le créneau est obligatoire.',
            'start_time.date_format' => 'Le format du créneau est invalide.',
            'customer_name.required' => 'Le nom client est obligatoire.',
            'customer_name.max' => 'Le nom client est trop long.',
            'customer_email.required' => 'L’email client est obligatoire.',
            'customer_email.email' => 'Veuillez entrer un email valide.',
            'customer_email.max' => 'L’email est trop long.',
            'customer_phone.max' => 'Le numéro de téléphone est trop long.',
            'customer_phone.regex' => 'Utilisez le format international, par exemple +14185550123.',
            'notes.max' => 'Les notes sont trop longues.',
        ];
    }

    public function attributes(): array
    {
        return [
            'service_id' => 'service',
            'staff_id' => 'prestataire',
            'date' => 'date',
            'start_time' => 'créneau',
            'customer_name' => 'nom',
            'customer_email' => 'email',
            'customer_phone' => 'téléphone',
            'notes' => 'notes',
        ];
    }
}
