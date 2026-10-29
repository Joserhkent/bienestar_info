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

    <!-- 2. Perfil Socioeconómico (Base para Fase B) -->
    <div class="col-lg-6" id="perfil-socioeconomico-seccion">
        <div class="card shadow-sm border h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold text-dark">Perfil Socioeconómico</h6>
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
                        @if((Auth::user()->rol->nombre ?? '') === 'Personal de Bienestar' && $estudiante->perfilSocioeconomico->observacion)
                            <div class="col-12 border-top pt-2">
                                <span class="text-muted d-block">Observación Social Confidencial</span>
                                <div class="p-2 bg-light rounded text-dark">
                                    {{ $estudiante->perfilSocioeconomico->observacion }}
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4 text-muted small">
                        No cuenta con perfil socioeconómico registrado.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 3. Condiciones e Inclusión (Base para Fase C) -->
    <div class="col-lg-6" id="condiciones-seccion">
        <div class="card shadow-sm border h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold text-dark">Condiciones e Inclusión Académica</h6>
            </div>
            <div class="card-body p-3">
                @if($estudiante->tiposCondicion->isNotEmpty())
                    <ul class="list-group list-group-flush small">
                        @foreach($estudiante->tiposCondicion as $condicion)
                            <li class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $condicion->nombre }}</strong>
                                    <span class="badge bg-light text-dark border">{{ ucfirst($condicion->pivot->grado) }}</span>
                                </div>
                                <div class="text-muted mt-1">
                                    <span>Aula accesible: {{ $condicion->pivot->requiere_aula_accesible ? 'Sí (1er piso / rampa)' : 'No' }}</span>
                                    | <span>Visible docente: {{ $condicion->pivot->visible_docente ? 'Sí' : 'No' }}</span>
                                </div>
                                @if((Auth::user()->rol->nombre ?? '') === 'Personal de Bienestar' && $condicion->pivot->pauta_pedagogica)
                                    <div class="mt-2 p-2 bg-light rounded text-dark">
                                        <span class="text-muted d-block small fw-semibold">Pauta pedagógica registrada:</span>
                                        {{ $condicion->pivot->pauta_pedagogica }}
                                    </div>
                                @endif
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
@endsection
