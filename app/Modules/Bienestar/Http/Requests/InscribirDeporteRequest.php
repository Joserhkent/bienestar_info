<?php

namespace App\Modules\Bienestar\Http\Requests;

use App\Modules\Bienestar\Models\Deporte;
use App\Modules\Bienestar\Models\Estudiante;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InscribirDeporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $id = $this->route('estudiante');
        $estudiante = is_numeric($id) ? Estudiante::find($id) : $id;

        if (!$estudiante) {
            abort(404, 'Estudiante no encontrado.');
        }

        return $this->user() && $this->user()->can('gestionarDeportes', $estudiante);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'disponible_seleccion' => $this->boolean('disponible_seleccion'),
        ]);
    }

    public function rules(): array
    {
        return [
            'id_deporte'           => ['required', 'integer', Rule::exists(Deporte::class, 'id_deporte')],
            'nivel'                => ['required', 'string', 'in:Principiante,Intermedio,Avanzado'],
            'disponible_seleccion' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_deporte.required' => 'Debe seleccionar una disciplina deportiva.',
            'id_deporte.integer'  => 'El identificador de la disciplina deportiva debe ser un número.',
            'id_deporte.exists'   => 'La disciplina deportiva seleccionada no existe en el catálogo institucional.',
            'nivel.required'      => 'Debe indicar el nivel de práctica de la disciplina deportiva.',
            'nivel.in'            => 'El nivel seleccionado no es válido (debe ser Principiante, Intermedio o Avanzado).',
        ];
    }
}
