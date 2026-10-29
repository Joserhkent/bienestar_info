<?php

namespace App\Modules\Bienestar\Http\Requests;

use App\Modules\Bienestar\Models\Especialista;
use App\Modules\Bienestar\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEspecialistaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->rol?->nombre, ['Personal de Bienestar', 'Administrador'], true);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombres'         => $this->nombres ? mb_convert_case(mb_strtolower(preg_replace('/\s+/', ' ', trim($this->nombres)), 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : null,
            'apellidos'       => $this->apellidos ? mb_convert_case(mb_strtolower(preg_replace('/\s+/', ' ', trim($this->apellidos)), 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : null,
            'correo'          => $this->correo ? strtolower(trim($this->correo)) : null,
            'especialidad'    => $this->especialidad ? preg_replace('/\s+/', ' ', trim($this->especialidad)) : null,
            'nro_colegiatura' => $this->nro_colegiatura ? strtoupper(preg_replace('/\s+/', '', trim($this->nro_colegiatura))) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nombres'         => ['required', 'string', 'max:80'],
            'apellidos'       => ['required', 'string', 'max:80'],
            'correo'          => ['required', 'email', 'max:100', Rule::unique(Usuario::class, 'correo')],
            'especialidad'    => ['required', 'string', 'max:100'],
            'nro_colegiatura' => ['required', 'string', 'max:20', Rule::unique(Especialista::class, 'nro_colegiatura')],
        ];
    }

    public function messages(): array
    {
        return [
            'nombres.required'         => 'Los nombres del especialista son obligatorios.',
            'apellidos.required'       => 'Los apellidos del especialista son obligatorios.',
            'correo.required'          => 'El correo institucional es obligatorio.',
            'correo.email'             => 'El formato del correo institucional es inválido.',
            'correo.unique'            => 'El correo institucional ya se encuentra registrado en el sistema.',
            'especialidad.required'    => 'La especialidad del profesional es obligatoria.',
            'nro_colegiatura.required' => 'El número de colegiatura profesional es obligatorio.',
            'nro_colegiatura.unique'   => 'El número de colegiatura ingresado ya se encuentra registrado.',
        ];
    }
}
