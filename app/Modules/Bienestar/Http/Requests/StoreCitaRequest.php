<?php

namespace App\Modules\Bienestar\Http\Requests;

use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\HorarioDisponible;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('agendar', Cita::class);
    }

    public function rules(): array
    {
        return [
            'id_horario' => [
                'required',
                'integer',
                Rule::exists(HorarioDisponible::class, 'id_horario')->where(function ($query) {
                    $query->where('estado', 'disponible');
                }),
            ],
            'tipo_cita' => [
                'required',
                'string',
                Rule::in([
                    'psicologia',
                    'tutoria_academica',
                    'apoyo_social',
                    'adaptada_tea',
                ]),
            ],
            'motivo' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_horario.required' => 'Debe seleccionar un horario de atención disponible.',
            'id_horario.exists' => 'El horario seleccionado ya no se encuentra disponible o no existe.',
            'tipo_cita.required' => 'El tipo de cita es obligatorio.',
            'tipo_cita.in' => 'El tipo de cita seleccionado no es válido.',
            'motivo.required' => 'El motivo de la cita es obligatorio.',
            'motivo.min' => 'El motivo debe contener al menos :min caracteres.',
            'motivo.max' => 'El motivo no puede exceder los :max caracteres.',
        ];
    }
}
