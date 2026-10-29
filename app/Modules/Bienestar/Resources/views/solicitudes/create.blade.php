@extends('bienestar::layout')

@section('title', 'Nueva Solicitud de Apoyo Socioeconómico — Bienestar UNT')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('bienestar.solicitudes.index') }}" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left me-1"></i>Regresar
            </a>
            <div>
                <h3 class="fw-bold mb-0 text-dark">Presentar Solicitud de Apoyo Socioeconómico</h3>
                <small class="text-muted">Proceso P2 — Bienestar Estudiantil (Informática UNT)</small>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary p-2 me-2"><i class="bi bi-person-fill"></i></span>
                    <div>
                        <h6 class="mb-0 fw-bold">{{ Auth::user()->estudiante?->nombres }} {{ Auth::user()->estudiante?->apellidos }}</h6>
                        <small class="text-muted">Código: {{ Auth::user()->estudiante?->codigo_universitario }} | DNI: {{ Auth::user()->estudiante?->dni }}</small>
                    </div>
                </div>
            </div>

            <form action="{{ route('bienestar.solicitudes.store') }}" method="POST" enctype="multipart/form-data" class="card-body p-4">
                @csrf

                <!-- Aviso de Confidencialidad y Regla R16 -->
                <div class="alert alert-primary border-0 shadow-sm d-flex align-items-start mb-4" role="alert">
                    <i class="bi bi-shield-lock-fill fs-4 me-3 text-primary mt-1"></i>
                    <div>
                        <strong>Regla de Negocio R16 (Sustento Obligatorio):</strong>
                        <p class="mb-0 small text-dark">
                            Toda solicitud debe adjuntar al menos un documento probatorio de su situación económica o familiar (recibos, constancia de ingresos familiares, certificado de SIS/Pobreza). Sus documentos se resguardan en un <strong>disco privado protegido</strong> y solo serán revisados por el equipo de Bienestar Universitario.
                        </p>
                    </div>
                </div>

                <!-- Tipo de Apoyo -->
                <div class="mb-3">
                    <label for="tipo_apoyo" class="form-label fw-bold">
                        Tipo de Apoyo Solicitado <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('tipo_apoyo') is-invalid @enderror" id="tipo_apoyo" name="tipo_apoyo" required>
                        <option value="">-- Seleccionar tipo de apoyo --</option>
                        <option value="comedor_universitario" {{ old('tipo_apoyo') === 'comedor_universitario' ? 'selected' : '' }}>Comedor Universitario</option>
                        <option value="beca_alimentaria" {{ old('tipo_apoyo') === 'beca_alimentaria' ? 'selected' : '' }}>Beca Alimentaria Integral</option>
                        <option value="subsidio_transporte" {{ old('tipo_apoyo') === 'subsidio_transporte' ? 'selected' : '' }}>Subsidio de Transporte Estudiantil</option>
                        <option value="exoneracion_pago" {{ old('tipo_apoyo') === 'exoneracion_pago' ? 'selected' : '' }}>Exoneración de Matrícula / Tasas</option>
                        <option value="otro" {{ old('tipo_apoyo') === 'otro' ? 'selected' : '' }}>Otro Tipo de Asistencia Social</option>
                    </select>
                    @error('tipo_apoyo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Motivo y Justificación -->
                <div class="mb-3">
                    <label for="motivo" class="form-label fw-bold">
                        Exposición de Motivos y Justificación <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control @error('motivo') is-invalid @enderror" id="motivo" name="motivo" rows="4" placeholder="Describa de manera clara su situación socioeconómica, carga familiar o dificultades actuales que fundamentan su pedido..." required>{{ old('motivo') }}</textarea>
                    <div class="form-text">Mínimo 10 caracteres. Sea lo más explícito posible.</div>
                    @error('motivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">

                <!-- Sección de Documentos Sustentatorios (R16) -->
                <div class="mb-4">
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-paperclip text-primary me-2"></i>Documentos Sustentatorios (Regla R16)
                    </h5>
                    <p class="text-muted small mb-3">
                        Formatos permitidos: <strong>PDF, JPG, JPEG, PNG</strong>. Tamaño máximo: <strong>5 MB por archivo</strong>.
                    </p>

                    <div id="archivos-contenedor">
                        <div class="row g-2 mb-3 archivo-fila">
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold text-muted">Tipo de Sustento</label>
                                <input type="text" name="tipo_documento[]" class="form-control" placeholder="Ej: Recibo de luz / Boleta familiar" value="Sustento socioeconómico">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label small fw-semibold text-muted">Archivo Adjunto <span class="text-danger">*</span></label>
                                <input type="file" name="documentos[]" class="form-control @error('documentos.0') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                                @error('documentos.0')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-agregar-archivo">
                        <i class="bi bi-plus-lg me-1"></i>Adjuntar otro documento
                    </button>
                    @error('documentos')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('bienestar.solicitudes.index') }}" class="btn btn-light">Cancelar</a>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="bi bi-send-check me-1"></i>Enviar Solicitud
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
        fila.className = 'row g-2 mb-3 archivo-fila align-items-end';
        fila.innerHTML = `
            <div class="col-md-5">
                <input type="text" name="tipo_documento[]" class="form-control" placeholder="Ej: Ficha SIS / Certificado médico" value="Documento complementario">
            </div>
            <div class="col-md-6">
                <input type="file" name="documentos[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100 btn-sm btn-eliminar-fila">
                    <i class="bi bi-trash"></i>
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
