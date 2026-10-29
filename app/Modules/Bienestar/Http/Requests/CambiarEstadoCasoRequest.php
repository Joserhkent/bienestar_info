<?php

namespace App\Modules\Bienestar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoCasoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $caso = $this->route('caso');
        if (is_numeric($caso)) {
            $caso = \App\Modules\Bienestar\Models\CasoBienestar::find($caso);
        }
        return $this->user() && $caso && $this->user()->can('cambiarEstado', $caso);
    }

    public function rules(): array
    {
        return [
            'estado_nuevo' => ['required', 'string', 'in:registrado,en_atencion,derivado,en_seguimiento,cerrado'],
            'comentario' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'estado_nuevo.required' => 'Debe seleccionar el nuevo estado del expediente.',
            'estado_nuevo.in' => 'El estado indicado no es válido en el flujo de bienestar.',
            'comentario.required' => 'Es obligatorio registrar una justificación o nota para la auditoría de estado.',
            'comentario.min' => 'El comentario de cambio de estado debe tener al menos 5 caracteres.',
            'comentario.max' => 'El comentario no puede exceder los 2000 caracteres.',
        ];
    }
}
