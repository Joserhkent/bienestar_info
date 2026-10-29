<?php

namespace App\Modules\Bienestar\Http\Requests;

use App\Modules\Bienestar\Models\SolicitudApoyo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSolicitudApoyoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', SolicitudApoyo::class);
    }

    public function rules(): array
    {
        return [
            'tipo_apoyo' => [
                'required',
                'string',
                Rule::in([
                    'comedor_universitario',
                    'beca_alimentaria',
                    'subsidio_transporte',
                    'exoneracion_pago',
                    'otro',
                ]),
            ],
            'motivo' => ['required', 'string', 'min:10', 'max:2000'],
            // Regla R16: La solicitud debe contar con al menos un documento adjunto obligatorio
            'documentos' => ['required', 'array', 'min:1'],
            'documentos.*' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120', // Máximo 5MB
            ],
            'tipo_documento' => ['nullable', 'array'],
            'tipo_documento.*' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_apoyo.required' => 'El tipo de apoyo socioeconómico es obligatorio.',
            'tipo_apoyo.in' => 'El tipo de apoyo seleccionado no es válido.',
            'motivo.required' => 'El motivo de la solicitud es obligatorio.',
            'motivo.min' => 'El motivo debe contener al menos :min caracteres detallando su situación.',
            'motivo.max' => 'El motivo no puede exceder los :max caracteres.',
            'documentos.required' => 'Debe adjuntar al menos un documento sustentatorio (Regla R16).',
            'documentos.min' => 'Debe adjuntar al menos un documento sustentatorio (Regla R16).',
            'documentos.array' => 'El formato de los archivos adjuntos es inválido.',
            'documentos.*.required' => 'Cada documento adjunto es obligatorio.',
            'documentos.*.file' => 'El archivo subido no es válido.',
            'documentos.*.mimes' => 'Solo se permiten archivos en formato PDF, JPG, JPEG o PNG.',
            'documentos.*.max' => 'El tamaño máximo por documento es de 5 MB.',
        ];
    }
}
