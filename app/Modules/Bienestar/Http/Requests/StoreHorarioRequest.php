<?php

namespace App\Modules\Bienestar\Http\Requests;

use App\Modules\Bienestar\Models\Cita;
use App\Modules\Bienestar\Models\Especialista;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('gestionarHorarios', Cita::class);
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'id_especialista' => [
                'nullable',
                'integer',
                Rule::exists(Especialista::class, 'id_especialista'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.after_or_equal' => 'La fecha debe ser igual o posterior a hoy.',
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'hora_inicio.date_format' => 'El formato de hora de inicio debe ser HH:MM.',
            'hora_fin.required' => 'La hora de fin es obligatoria.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'hora_fin.date_format' => 'El formato de hora de fin debe ser HH:MM.',
            'id_especialista.exists' => 'El especialista seleccionado no existe.',
        ];
    }
}
