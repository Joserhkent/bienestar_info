<?php

namespace App\Modules\Bienestar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelarCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se valida en el controlador para redirigir con alerta
    }

    public function rules(): array
    {
        return [
            'motivo_cancelacion' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo_cancelacion.max' => 'El motivo de cancelación no puede exceder los :max caracteres.',
        ];
    }
}
