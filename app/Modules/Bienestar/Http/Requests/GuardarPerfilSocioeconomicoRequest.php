<?php

namespace App\Modules\Bienestar\Http\Requests;

use App\Modules\Bienestar\Models\Estudiante;
use Illuminate\Foundation\Http\FormRequest;

class GuardarPerfilSocioeconomicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $estudiante = $this->route('estudiante');

        if (is_numeric($estudiante)) {
            $estudiante = Estudiante::find($estudiante);
        }

        return $this->user() && $estudiante instanceof Estudiante && $this->user()->can('gestionarPerfil', $estudiante);
    }

    public function rules(): array
    {
        return [
            'clasificacion_sisfoh' => ['required', 'string', 'in:pobre_extremo,pobre,no_pobre'],
            'violencia_familiar'   => ['nullable', 'boolean'],
            'situacion_orfandad'   => ['required', 'string', 'in:ninguna,padre,madre,ambos'],
            'es_sustento_familiar' => ['nullable', 'boolean'],
            'nro_dependientes'     => ['required', 'integer', 'min:0', 'max:50'],
            'observacion'          => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'violencia_familiar'   => $this->boolean('violencia_familiar'),
            'es_sustento_familiar' => $this->boolean('es_sustento_familiar'),
        ]);
    }

    public function messages(): array
    {
        return [
            'clasificacion_sisfoh.required' => 'Debe seleccionar la clasificación socioeconómica SISFOH.',
            'clasificacion_sisfoh.in'       => 'La clasificación SISFOH seleccionada no es válida.',
            'situacion_orfandad.required'   => 'Debe indicar la situación de orfandad.',
            'situacion_orfandad.in'         => 'La opción de situación de orfandad seleccionada no es válida.',
            'nro_dependientes.required'     => 'El número de dependientes es obligatorio.',
            'nro_dependientes.integer'      => 'El número de dependientes debe ser un número entero.',
            'nro_dependientes.min'          => 'El número de dependientes no puede ser negativo.',
            'nro_dependientes.max'          => 'El número de dependientes no puede exceder 50.',
            'observacion.max'               => 'La observación no puede superar los 1000 caracteres.',
        ];
    }
}
