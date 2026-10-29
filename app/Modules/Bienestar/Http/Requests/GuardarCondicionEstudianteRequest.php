<?php

namespace App\Modules\Bienestar\Http\Requests;

use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\TipoCondicion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarCondicionEstudianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $estudiante = $this->route('estudiante');

        if (is_numeric($estudiante)) {
            $estudiante = Estudiante::find($estudiante);
        }

        return $this->user() && $estudiante instanceof Estudiante && $this->user()->can('gestionarCondicion', $estudiante);
    }

    public function rules(): array
    {
        return [
            'id_tipo_condicion'       => ['required', 'integer', Rule::exists(TipoCondicion::class, 'id_tipo_condicion')],
            'grado'                   => ['required', 'string', 'in:leve,moderado,severo'],
            'requiere_aula_accesible' => ['nullable', 'boolean'],
            'certificado_conadis'     => ['nullable', 'string', 'max:50'],
            'pauta_pedagogica'        => ['nullable', 'string', 'max:3000'],
            'visible_docente'         => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requiere_aula_accesible' => $this->boolean('requiere_aula_accesible'),
            'visible_docente'         => $this->boolean('visible_docente'),
        ]);
    }

    public function messages(): array
    {
        return [
            'id_tipo_condicion.required' => 'Debe seleccionar el tipo de condición o necesidad de inclusión.',
            'id_tipo_condicion.exists'   => 'El tipo de condición seleccionado no es válido.',
            'grado.required'             => 'Debe especificar el grado de la condición.',
            'grado.in'                   => 'El grado debe ser leve, moderado o severo.',
            'certificado_conadis.max'    => 'El número de certificado CONADIS no puede exceder los 50 caracteres.',
            'pauta_pedagogica.max'       => 'La pauta pedagógica no puede superar los 3000 caracteres.',
        ];
    }
}
