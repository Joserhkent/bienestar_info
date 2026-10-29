@extends('bienestar::layout')

@section('title', 'Expediente del Estudiante')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $estudiante->nombre_completo }}</h4>
        <span class="text-muted small">
            Código: <strong>{{ $estudiante->codigo_universitario }}</strong> | DNI: {{ $estudiante->dni }} | Ciclo: {{ $estudiante->ciclo_actual }}
        </span>
    </div>
    <div class="d-flex gap-2">
        @can('viewAny', App\Modules\Bienestar\Models\Estudiante::class)
            <a href="{{ route('bienestar.estudiantes.index') }}" class="btn btn-outline-secondary btn-sm">
                Volver al padrón
            </a>
        @endcan
        @can('update', $estudiante)
            <a href="{{ route('bienestar.estudiantes.edit', $estudiante->id_estudiante) }}" class="btn btn-outline-primary btn-sm">
                Editar estudiante
            </a>
        @endcan
        @can('delete', $estudiante)
            <form action="{{ route('bienestar.estudiantes.destroy', $estudiante->id_estudiante) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este estudiante y su cuenta asociada?');" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    Eliminar
                </button>
            </form>
        @endcan
    </div>
</div>

@if(session('password_temporal'))
    <div class="alert alert-warning border-warning shadow-sm mb-4">
        <h6 class="alert-heading fw-bold mb-1">Credencial Temporal Generada</h6>
        <p class="mb-2 small">
            La cuenta de acceso institucional ha sido configurada. Comunique esta contraseña temporal al estudiante. Por seguridad, el sistema le exigirá actualizarla en su primer inicio de sesión.
        </p>
        <div class="p-2 bg-white rounded border border-warning font-monospace text-dark d-inline-block fw-bold fs-6">
            {{ session('password_temporal') }}
        </div>
        <div class="small text-muted mt-1">
            Correo de acceso: <strong>{{ session('correo_generado') }}</strong> (Esta clave se muestra únicamente en esta pantalla).
        </div>
    </div>
@endif

<div class="row g-4">
    <!-- 1. Datos Personales y Académicos -->
    <div class="col-lg-6">
        <div class="card shadow-sm border h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-dark">Información General</h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-3 small">
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Código Universitario</span>
                        <span class="fw-semibold">{{ $estudiante->codigo_universitario }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Documento de Identidad (DNI)</span>
                        <span class="fw-semibold">{{ $estudiante->dni }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Sexo</span>
                        <span class="fw-semibold">{{ $estudiante->sexo === 'M' ? 'Masculino' : 'Femenino' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Fecha de Nacimiento</span>
                        <span class="fw-semibold">{{ $estudiante->fecha_nacimiento ? $estudiante->fecha_nacimiento->format('d/m/Y') : 'No registrada' }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Ciclo Académico</span>
                        <span class="badge bg-light text-dark border">Ciclo {{ $estudiante->ciclo_actual }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Teléfono / Celular</span>
                        <span class="fw-semibold">{{ $estudiante->telefono ?? 'No registrado' }}</span>
                    </div>
                    <div class="col-12">
                        <span class="text-muted d-block">Zona y Lugar de Procedencia</span>
                        <span class="fw-semibold">{{ $estudiante->lugar_procedencia }} ({{ $estudiante->zona_procedencia }})</span>
                    </div>
                    <div class="col-12">
                        <span class="text-muted d-block">Dirección Actual</span>
                        <span class="fw-semibold">{{ $estudiante->direccion_actual ?? 'No registrada' }}</span>
                    </div>
                    <div class="col-12 border-top pt-2">
                        <span class="text-muted d-block">Cuenta de Usuario Asociada</span>
                        <span class="fw-semibold">{{ $estudiante->usuario->correo ?? 'Sin usuario vinculado' }}</span>
                        @if($estudiante->usuario)
                            <span class="badge bg-{{ $estudiante->usuario->estado === 'activo' ? 'success' : 'secondary' }} ms-1">
                                {{ ucfirst($estudiante->usuario->estado) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Perfil Socioeconómico (Fase B) -->
    <div class="col-lg-6" id="perfil-socioeconomico-seccion">
        <div class="card shadow-sm border h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold text-dark">Perfil Socioeconómico</h6>
                @can('gestionarPerfil', $estudiante)
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPerfil">
                        {{ $estudiante->perfilSocioeconomico ? 'Actualizar perfil' : 'Registrar perfil' }}
                    </button>
                @endcan
            </div>
            <div class="card-body p-3">
                @if($estudiante->perfilSocioeconomico)
                    <div class="row g-3 small">
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Clasificación SISFOH</span>
                            <span class="badge bg-light text-dark border fw-bold">
                                {{ ucfirst(str_replace('_', ' ', $estudiante->perfilSocioeconomico->clasificacion_sisfoh)) }}
                            </span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Sustento Familiar</span>
                            <span>{{ $estudiante->perfilSocioeconomico->es_sustento_familiar ? 'Sí' : 'No' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Situación de Orfandad</span>
                            <span>{{ ucfirst($estudiante->perfilSocioeconomico->situacion_orfandad) }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Violencia Familiar</span>
                            <span>{{ $estudiante->perfilSocioeconomico->violencia_familiar ? 'Registrada' : 'No reportada' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Carga Familiar / Dependientes</span>
                            <span>{{ $estudiante->perfilSocioeconomico->nro_dependientes }} personas</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block">Última Actualización</span>
                            <span>{{ $estudiante->perfilSocioeconomico->fecha_actualizacion ? $estudiante->perfilSocioeconomico->fecha_actualizacion->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                        @can('verObservacionSocioeconomica', $estudiante)
                            @if($estudiante->perfilSocioeconomico->observacion)
                                <div class="col-12 border-top pt-2">
                                    <span class="text-muted d-block small fw-semibold">Observación Social Confidencial</span>
                                    <div class="p-2 bg-light rounded text-dark mt-1">
                                        {{ $estudiante->perfilSocioeconomico->observacion }}
                                    </div>
                                </div>
                            @endif
                        @endcan
                    </div>
                @else
                    <div class="text-center py-4 text-muted small">
                        No cuenta con perfil socioeconómico registrado.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 3. Condiciones e Inclusión (Fase C) -->
    <div class="col-lg-6" id="condiciones-seccion">
        <div class="card shadow-sm border h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold text-dark">Condiciones e Inclusión Académica</h6>
                @can('gestionarCondicion', $estudiante)
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCondicion">
                        Asignar condición
                    </button>
                @endcan
            </div>
            <div class="card-body p-3">
                @if($estudiante->tiposCondicion->isNotEmpty())
                    <ul class="list-group list-group-flush small">
                        @foreach($estudiante->tiposCondicion as $condicion)
                            <li class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="text-dark">{{ $condicion->nombre }}</strong>
                                        <span class="badge bg-light text-dark border ms-1">{{ ucfirst($condicion->pivot->grado) }}</span>
                                    </div>
                                    @can('gestionarCondicion', $estudiante)
                                        <form action="{{ route('bienestar.estudiantes.condiciones.eliminar', [$estudiante->id_estudiante, $condicion->id_tipo_condicion]) }}" method="POST" onsubmit="return confirm('¿Remover esta condición del expediente?');" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1 border-0" title="Remover condición">
                                                &times;
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                                <div class="text-muted mt-1 small">
                                    <span>Aula accesible: {{ $condicion->pivot->requiere_aula_accesible ? 'Sí (1er piso / rampa)' : 'No' }}</span>
                                    | <span>Visible docente: {{ $condicion->pivot->visible_docente ? 'Sí' : 'No' }}</span>
                                    @if($condicion->pivot->certificado_conadis)
                                        | <span>CONADIS: {{ $condicion->pivot->certificado_conadis }}</span>
                                    @endif
                                </div>
                                @can('verPautaPedagogica', $estudiante)
                                    @if($condicion->pivot->pauta_pedagogica)
                                        <div class="mt-2 p-2 bg-light rounded text-dark small">
                                            <span class="text-muted d-block small fw-semibold">Pauta pedagógica confidencial:</span>
                                            {{ $condicion->pivot->pauta_pedagogica }}
                                        </div>
                                    @endif
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4 text-muted small">
                        No presenta condiciones de discapacidad ni necesidades de inclusión registradas.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 4. Actividades Deportivas (Base para Fase D) -->
    <div class="col-lg-6" id="deportes-seccion">
        <div class="card shadow-sm border h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold text-dark">Actividades Deportivas</h6>
            </div>
            <div class="card-body p-3">
                @if($estudiante->deportes->isNotEmpty())
                    <ul class="list-group list-group-flush small">
                        @foreach($estudiante->deportes as $deporte)
                            <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $deporte->nombre }}</strong>
                                    <span class="text-muted ms-2">Nivel: {{ ucfirst($deporte->pivot->nivel) }}</span>
                                </div>
                                <span class="badge bg-{{ $deporte->pivot->disponible_seleccion ? 'primary' : 'secondary' }}">
                                    {{ $deporte->pivot->disponible_seleccion ? 'Selección' : 'Formativo' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4 text-muted small">
                        No registra participación deportiva institucional.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@can('gestionarPerfil', $estudiante)
    <!-- Modal Actualizar Perfil Socioeconómico -->
    <div class="modal fade" id="modalPerfil" tabindex="-1" aria-labelledby="modalPerfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border">
                <form action="{{ route('bienestar.estudiantes.perfil.store', $estudiante->id_estudiante) }}" method="POST">
                    @csrf
                    <div class="modal-header py-3 bg-light">
                        <h6 class="modal-title fw-bold text-dark" id="modalPerfilLabel">Actualizar Perfil Socioeconómico</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body p-4 small">
                        <div class="mb-3">
                            <label for="clasificacion_sisfoh" class="form-label fw-semibold">Clasificación SISFOH <span class="text-danger">*</span></label>
                            <select name="clasificacion_sisfoh" id="clasificacion_sisfoh" class="form-select form-select-sm" required>
                                <option value="pobre_extremo" {{ old('clasificacion_sisfoh', $estudiante->perfilSocioeconomico->clasificacion_sisfoh ?? '') === 'pobre_extremo' ? 'selected' : '' }}>Pobreza Extrema</option>
                                <option value="pobre" {{ old('clasificacion_sisfoh', $estudiante->perfilSocioeconomico->clasificacion_sisfoh ?? '') === 'pobre' ? 'selected' : '' }}>Pobreza No Extrema</option>
                                <option value="no_pobre" {{ old('clasificacion_sisfoh', $estudiante->perfilSocioeconomico->clasificacion_sisfoh ?? '') === 'no_pobre' ? 'selected' : '' }}>No Pobre</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="situacion_orfandad" class="form-label fw-semibold">Situación de Orfandad <span class="text-danger">*</span></label>
                            <select name="situacion_orfandad" id="situacion_orfandad" class="form-select form-select-sm" required>
                                <option value="ninguna" {{ old('situacion_orfandad', $estudiante->perfilSocioeconomico->situacion_orfandad ?? 'ninguna') === 'ninguna' ? 'selected' : '' }}>Ninguna</option>
                                <option value="padre" {{ old('situacion_orfandad', $estudiante->perfilSocioeconomico->situacion_orfandad ?? '') === 'padre' ? 'selected' : '' }}>Orfandad de Padre</option>
                                <option value="madre" {{ old('situacion_orfandad', $estudiante->perfilSocioeconomico->situacion_orfandad ?? '') === 'madre' ? 'selected' : '' }}>Orfandad de Madre</option>
                                <option value="ambos" {{ old('situacion_orfandad', $estudiante->perfilSocioeconomico->situacion_orfandad ?? '') === 'ambos' ? 'selected' : '' }}>Orfandad Total (Ambos Padres)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="nro_dependientes" class="form-label fw-semibold">Número de Dependientes / Carga Familiar <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="nro_dependientes" 
                                   id="nro_dependientes" 
                                   min="0" 
                                   max="50" 
                                   class="form-control form-control-sm" 
                                   value="{{ old('nro_dependientes', $estudiante->perfilSocioeconomico->nro_dependientes ?? 0) }}" 
                                   required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="es_sustento_familiar" 
                                           id="es_sustento_familiar" 
                                           class="form-check-input" 
                                           value="1" 
                                           {{ old('es_sustento_familiar', $estudiante->perfilSocioeconomico->es_sustento_familiar ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="es_sustento_familiar">
                                        Es sustento económico principal
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="violencia_familiar" 
                                           id="violencia_familiar" 
                                           class="form-check-input" 
                                           value="1" 
                                           {{ old('violencia_familiar', $estudiante->perfilSocioeconomico->violencia_familiar ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="violencia_familiar">
                                        Violencia familiar registrada
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="observacion" class="form-label fw-semibold">Observación Social Confidencial (Cifrada)</label>
                            <textarea name="observacion" 
                                      id="observacion" 
                                      rows="3" 
                                      maxlength="1000" 
                                      class="form-control form-control-sm" 
                                      placeholder="Detalles confidenciales de evaluación socioeconómica...">{{ old('observacion', $estudiante->perfilSocioeconomico->observacion ?? '') }}</textarea>
                            <div class="form-text small">La observación se almacena con cifrado y no es visible para el Administrador ni docentes.</div>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">Guardar perfil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@can('gestionarCondicion', $estudiante)
    <!-- Modal Asignar Condición de Inclusión -->
    <div class="modal fade" id="modalCondicion" tabindex="-1" aria-labelledby="modalCondicionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border">
                <form action="{{ route('bienestar.estudiantes.condiciones.store', $estudiante->id_estudiante) }}" method="POST">
                    @csrf
                    <div class="modal-header py-3 bg-light">
                        <h6 class="modal-title fw-bold text-dark" id="modalCondicionLabel">Asignar Condición / Inclusión Académica</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body p-4 small">
                        <div class="mb-3">
                            <label for="id_tipo_condicion" class="form-label fw-semibold">Tipo de Condición / Necesidad <span class="text-danger">*</span></label>
                            <select name="id_tipo_condicion" id="id_tipo_condicion" class="form-select form-select-sm" required>
                                <option value="">Seleccione condición...</option>
                                @foreach($tiposCondicionCatalogo ?? [] as $tipo)
                                    <option value="{{ $tipo->id_tipo_condicion }}" {{ (int) old('id_tipo_condicion') === $tipo->id_tipo_condicion ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <label for="grado" class="form-label fw-semibold">Grado de Severidad <span class="text-danger">*</span></label>
                                <select name="grado" id="grado" class="form-select form-select-sm" required>
                                    <option value="leve" {{ old('grado') === 'leve' ? 'selected' : '' }}>Leve</option>
                                    <option value="moderado" {{ old('grado', 'moderado') === 'moderado' ? 'selected' : '' }}>Moderado</option>
                                    <option value="severo" {{ old('grado') === 'severo' ? 'selected' : '' }}>Severo</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="certificado_conadis" class="form-label fw-semibold">Nro. Certificado CONADIS</label>
                                <input type="text" 
                                       name="certificado_conadis" 
                                       id="certificado_conadis" 
                                       maxlength="50" 
                                       class="form-control form-control-sm" 
                                       value="{{ old('certificado_conadis') }}" 
                                       placeholder="Opcional">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="requiere_aula_accesible" 
                                           id="requiere_aula_accesible" 
                                           class="form-check-input" 
                                           value="1" 
                                           {{ old('requiere_aula_accesible') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requiere_aula_accesible">
                                        Requiere aula accesible (1er piso)
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="visible_docente" 
                                           id="visible_docente" 
                                           class="form-check-input" 
                                           value="1" 
                                           {{ old('visible_docente', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="visible_docente">
                                        Visible para docentes
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="pauta_pedagogica" class="form-label fw-semibold">Pauta Pedagógica de Inclusión (Cifrada)</label>
                            <textarea name="pauta_pedagogica" 
                                      id="pauta_pedagogica" 
                                      rows="3" 
                                      maxlength="3000" 
                                      class="form-control form-control-sm" 
                                      placeholder="Pautas psicopedagógicas para los docentes asignados...">{{ old('pauta_pedagogica') }}</textarea>
                            <div class="form-text small">La pauta pedagógica se almacena con cifrado y solo es visible para el docente si se marca la casilla correspondiente.</div>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">Asignar condición</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
@endsection
