<?php

namespace App\Modules\Bienestar\Http\Requests;

use App\Modules\Bienestar\Models\Estudiante;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEstudianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', Estudiante::class);
    }

    public function rules(): array
    {
        return [
            'codigo_universitario' => ['required', 'string', 'size:10', 'regex:/^[0-9]{10}$/', Rule::unique(Estudiante::class, 'codigo_universitario')],
            'dni'                  => ['required', 'string', 'size:8', 'regex:/^[0-9]{8}$/', Rule::unique(Estudiante::class, 'dni')],
            'nombres'              => ['required', 'string', 'max:80'],
            'apellidos'            => ['required', 'string', 'max:80'],
            'correo'               => ['required', 'email', 'max:100', Rule::unique(Usuario::class, 'correo')],
            'sexo'                 => ['required', 'in:M,F'],
            'fecha_nacimiento'     => ['required', 'date', 'before:today'],
            'telefono'             => ['nullable', 'string', 'max:15'],
            'direccion_actual'     => ['nullable', 'string', 'max:255'],
            'zona_procedencia'     => ['required', 'string', 'max:50'],
            'lugar_procedencia'    => ['required', 'string', 'max:100'],
            'ciclo_actual'         => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalizarEspacios = fn (?string $v) => $v !== null ? preg_replace('/\s+/', ' ', trim($v)) : null;
        $normalizarTitulo = fn (?string $v) => $v !== null ? mb_convert_case(mb_strtolower(preg_replace('/\s+/', ' ', trim($v)), 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : null;
        $soloDigitos = fn (?string $v) => $v !== null ? preg_replace('/\D/', '', $v) : null;

        $this->merge([
            'nombres'              => $normalizarTitulo($this->input('nombres')),
            'apellidos'            => $normalizarTitulo($this->input('apellidos')),
            'correo'               => $this->input('correo') !== null ? strtolower(trim($this->input('correo'))) : null,
            'codigo_universitario' => $soloDigitos($this->input('codigo_universitario')),
            'dni'                  => $soloDigitos($this->input('dni')),
            'telefono'             => $soloDigitos($this->input('telefono')),
            'direccion_actual'     => $normalizarEspacios($this->input('direccion_actual')),
            'zona_procedencia'     => $normalizarEspacios($this->input('zona_procedencia')),
            'lugar_procedencia'    => $normalizarEspacios($this->input('lugar_procedencia')),
        ]);
    }

    public function messages(): array
    {
        return [
            'codigo_universitario.required' => 'El código universitario es obligatorio.',
            'codigo_universitario.unique'   => 'El código universitario ya se encuentra registrado.',
            'codigo_universitario.max'      => 'El código universitario no puede superar los 10 caracteres.',
            'dni.required'                  => 'El DNI es obligatorio.',
            'dni.size'                      => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.regex'                     => 'El DNI debe contener únicamente 8 dígitos numéricos.',
            'dni.unique'                    => 'El DNI ingresado ya se encuentra registrado.',
            'nombres.required'              => 'Los nombres del estudiante son obligatorios.',
            'apellidos.required'            => 'Los apellidos del estudiante son obligatorios.',
            'correo.required'               => 'El correo institucional es obligatorio.',
            'correo.email'                  => 'Debe ingresar un formato de correo institucional válido.',
            'correo.unique'                 => 'El correo electrónico ya está registrado para otro usuario.',
            'sexo.required'                 => 'Debe seleccionar el sexo.',
            'sexo.in'                       => 'El sexo seleccionado es inválido.',
            'fecha_nacimiento.required'     => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date'         => 'Debe ingresar una fecha de nacimiento válida.',
            'fecha_nacimiento.before'       => 'La fecha de nacimiento debe ser anterior a hoy.',
            'zona_procedencia.required'     => 'La zona de procedencia es obligatoria.',
            'lugar_procedencia.required'    => 'El lugar de procedencia es obligatorio.',
            'ciclo_actual.required'         => 'El ciclo actual es obligatorio.',
            'ciclo_actual.integer'          => 'El ciclo debe ser un número entero.',
            'ciclo_actual.min'              => 'El ciclo mínimo es 1.',
            'ciclo_actual.max'              => 'El ciclo máximo permitido es 12.',
        ];
    }
}
