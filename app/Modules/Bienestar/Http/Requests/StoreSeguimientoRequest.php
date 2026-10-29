<?php

namespace App\Modules\Bienestar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeguimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $derivacion = $this->route('derivacion');
        if (is_numeric($derivacion)) {
            $derivacion = \App\Modules\Bienestar\Models\Derivacion::find($derivacion);
        }
        return $this->user() && $derivacion && $this->user()->can('registrarSeguimiento', $derivacion);
    }

    public function rules(): array
    {
        return [
            'fecha_sesion' => ['nullable', 'date'],
            'nota_avance' => ['required', 'string', 'min:10', 'max:3000'],
            'acuerdos' => ['nullable', 'string', 'max:2000'],
            'proxima_fecha' => ['nullable', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'nota_avance.required' => 'La nota de avance o evolución de la sesión es obligatoria.',
            'nota_avance.min' => 'La nota de avance debe contener al menos :min caracteres.',
            'nota_avance.max' => 'La nota de avance no puede exceder los :max caracteres.',
            'acuerdos.max' => 'Los acuerdos no pueden exceder los :max caracteres.',
            'proxima_fecha.after' => 'La próxima fecha de sesión debe ser posterior a hoy.',
        ];
    }
}
