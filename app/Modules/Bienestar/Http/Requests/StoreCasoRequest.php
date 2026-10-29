<?php

namespace App\Modules\Bienestar\Http\Requests;

use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\TipoCaso;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCasoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', \App\Modules\Bienestar\Models\CasoBienestar::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_estudiante' => ['required', 'integer', Rule::exists(Estudiante::class, 'id_estudiante')],
            'id_tipo_caso' => ['required', 'integer', Rule::exists(TipoCaso::class, 'id_tipo_caso')],
            'prioridad' => ['required', 'string', 'in:baja,media,alta,urgente'],
            'confidencial' => ['required', 'boolean'],
            'descripcion' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * Mensajes de error en español para la validación
     */
    public function messages(): array
    {
        return [
            'id_estudiante.required' => 'Debe seleccionar un estudiante para abrir el expediente.',
            'id_estudiante.exists' => 'El estudiante seleccionado no se encuentra registrado en el padrón.',
            'id_tipo_caso.required' => 'Debe clasificar el caso según una tipología de bienestar.',
            'id_tipo_caso.exists' => 'El tipo de caso seleccionado no es válido.',
            'prioridad.required' => 'La prioridad del caso es obligatoria.',
            'prioridad.in' => 'La prioridad debe ser: baja, media, alta o urgente.',
            'confidencial.required' => 'Debe especificar el indicador de confidencialidad del caso.',
            'confidencial.boolean' => 'El valor de confidencialidad debe ser verdadero o falso.',
            'descripcion.required' => 'El resumen descriptivo del caso es obligatorio.',
            'descripcion.min' => 'La descripción del caso debe contener al menos 10 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder los 5000 caracteres.',
        ];
    }
}
