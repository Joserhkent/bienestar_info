@extends('bienestar::layout')

@section('title', 'Nueva solicitud — Bienestar UNT')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold mb-0 text-dark">Nueva solicitud</h4>
            <a href="{{ route('bienestar.solicitudes.index') }}" class="btn btn-outline-secondary btn-sm">
                Volver
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white py-2">
                <span class="text-muted small">Estudiante:</span>
                <strong class="text-dark">{{ Auth::user()->estudiante?->nombres }} {{ Auth::user()->estudiante?->apellidos }}</strong>
                <span class="text-muted small ms-2">({{ Auth::user()->estudiante?->codigo_universitario }})</span>
            </div>

            <form action="{{ route('bienestar.solicitudes.store') }}" method="POST" enctype="multipart/form-data" class="card-body p-4">
                @csrf

                <!-- Tipo de apoyo -->
                <div class="mb-3">
                    <label for="tipo_apoyo" class="form-label">
                        Tipo de apoyo <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('tipo_apoyo') is-invalid @enderror" id="tipo_apoyo" name="tipo_apoyo" required>
                        <option value="">Seleccione tipo de apoyo...</option>
                        <option value="comedor_universitario" {{ old('tipo_apoyo') === 'comedor_universitario' ? 'selected' : '' }}>Comedor universitario</option>
                        <option value="beca_alimentaria" {{ old('tipo_apoyo') === 'beca_alimentaria' ? 'selected' : '' }}>Beca alimentaria</option>
                        <option value="subsidio_transporte" {{ old('tipo_apoyo') === 'subsidio_transporte' ? 'selected' : '' }}>Subsidio de transporte</option>
                        <option value="exoneracion_pago" {{ old('tipo_apoyo') === 'exoneracion_pago' ? 'selected' : '' }}>Exoneración de pagos</option>
                        <option value="otro" {{ old('tipo_apoyo') === 'otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('tipo_apoyo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Motivo -->
                <div class="mb-3">
                    <label for="motivo" class="form-label">
                        Motivo y justificación <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control @error('motivo') is-invalid @enderror" id="motivo" name="motivo" rows="4" placeholder="Describa su situación y fundamentación del pedido..." required>{{ old('motivo') }}</textarea>
                    <div class="form-text text-muted small">Mínimo 10 caracteres.</div>
                    @error('motivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-3">

                <!-- Documentos sustentatorios -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark mb-1">Documentos sustentatorios</label>
                    <p class="text-muted small mb-2">
                        Adjunte al menos un documento en formato PDF, JPG o PNG (máximo 5 MB por archivo).
                    </p>

                    <div id="archivos-contenedor">
                        <div class="row g-2 mb-2 archivo-fila">
                            <div class="col-md-5">
                                <input type="text" name="tipo_documento[]" class="form-control form-control-sm" placeholder="Descripción del documento" value="Sustento socioeconómico">
                            </div>
                            <div class="col-md-7">
                                <input type="file" name="documentos[]" class="form-control form-control-sm @error('documentos.0') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                                @error('documentos.0')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm mt-1" id="btn-agregar-archivo">
                        Adjuntar otro documento
                    </button>
                    @error('documentos')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('bienestar.solicitudes.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">
                        Enviar solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('btn-agregar-archivo').addEventListener('click', function() {
        const contenedor = document.getElementById('archivos-contenedor');
        const fila = document.createElement('div');
        fila.className = 'row g-2 mb-2 archivo-fila align-items-center';
        fila.innerHTML = `
            <div class="col-md-5">
                <input type="text" name="tipo_documento[]" class="form-control form-control-sm" placeholder="Descripción del documento" value="Documento complementario">
            </div>
            <div class="col-md-6">
                <input type="file" name="documentos[]" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-eliminar-fila">
                    &times;
                </button>
            </div>
        `;
        contenedor.appendChild(fila);

        fila.querySelector('.btn-eliminar-fila').addEventListener('click', function() {
            fila.remove();
        });
    });
</script>
@endpush
@endsection
