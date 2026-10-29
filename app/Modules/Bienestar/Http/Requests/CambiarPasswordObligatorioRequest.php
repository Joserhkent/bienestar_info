<?php

namespace App\Modules\Bienestar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CambiarPasswordObligatorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'password_actual' => ['required', 'string', 'current_password'],
            'password'        => ['required', 'string', 'min:8', 'confirmed', 'different:password_actual'],
        ];
    }

    public function messages(): array
    {
        return [
            'password_actual.required'         => 'Debe ingresar su contraseña temporal actual.',
            'password_actual.current_password' => 'La contraseña actual ingresada es incorrecta.',
            'password.required'                => 'Debe ingresar la nueva contraseña.',
            'password.min'                     => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'               => 'La confirmación de la contraseña no coincide.',
            'password.different'               => 'La nueva contraseña debe ser distinta a la contraseña temporal actual.',
        ];
    }
}
