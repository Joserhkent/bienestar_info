<?php

namespace App\Modules\Bienestar\Http\Requests;

use App\Modules\Bienestar\Models\SolicitudApoyo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DictaminarSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        $solicitud = $this->route('solicitud');
        return $this->user() && $solicitud && $this->user()->can('dictaminar', $solicitud);
    }

    public function rules(): array
    {
        $esRechazo = $this->input('estado') === 'rechazada';

        return [
            'estado' => ['required', 'string', Rule::in(['aprobada', 'rechazada'])],
            'observacion' => [
                $esRechazo ? 'required' : 'nullable',
                'string',
                $esRechazo ? 'min:5' : 'nullable',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'El dictamen (aprobada o rechazada) es obligatorio.',
            'estado.in' => 'El estado del dictamen debe ser aprobada o rechazada.',
            'observacion.required' => 'El motivo u observación es obligatorio al rechazar una solicitud.',
            'observacion.min' => 'El motivo de rechazo debe contener al menos :min caracteres.',
            'observacion.max' => 'La observación no puede exceder los :max caracteres.',
        ];
    }
}
