<?php

namespace App\Modules\Bienestar\Http\Requests;

use App\Modules\Bienestar\Models\CasoBienestar;
use App\Modules\Bienestar\Models\Derivacion;
use App\Modules\Bienestar\Models\Especialista;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDerivacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', Derivacion::class);
    }

    public function rules(): array
    {
        return [
            'id_caso' => [
                'required',
                'integer',
                Rule::exists(CasoBienestar::class, 'id_caso'),
            ],
            'id_especialista' => [
                'required',
                'integer',
                Rule::exists(Especialista::class, 'id_especialista')->where(function ($query) {
                    $query->where('estado', 'activo');
                }),
            ],
            'motivo' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_caso.required' => 'Debe indicar el expediente a derivar.',
            'id_caso.exists' => 'El caso seleccionado no existe.',
            'id_especialista.required' => 'Debe seleccionar un especialista.',
            'id_especialista.exists' => 'El especialista seleccionado no está activo o no existe.',
            'motivo.required' => 'El motivo de la derivación es obligatorio.',
            'motivo.min' => 'El motivo debe contener al menos :min caracteres.',
            'motivo.max' => 'El motivo no puede exceder los :max caracteres.',
        ];
    }
}
