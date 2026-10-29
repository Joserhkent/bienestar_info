@extends('bienestar::layout')

@section('title', 'Reportes consolidados — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-dark mb-0">Reportes institucionales</h4>
        <span class="text-muted small">Métricas e indicadores consolidados para Dirección de Escuela</span>
    </div>
    <div>
        <a href="{{ route('bienestar.reportes.exportarCsv', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
            Exportar reporte CSV
        </a>
    </div>
</div>

<!-- Filtros de periodo y tipo -->
<div class="card mb-4">
    <div class="card-header bg-white py-2">
        <span class="fw-semibold text-dark">Parámetros de consulta</span>
    </div>
    <form action="{{ route('bienestar.reportes.index') }}" method="GET" class="card-body p-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="fecha_inicio" class="form-label text-muted small">Fecha de inicio</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-control-sm" value="{{ $filtros['fecha_inicio'] }}">
            </div>
            <div class="col-md-3">
                <label for="fecha_fin" class="form-label text-muted small">Fecha de fin</label>
                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" value="{{ $filtros['fecha_fin'] }}">
            </div>
            <div class="col-md-3">
                <label for="tipo_reporte" class="form-label text-muted small">Tipo de reporte</label>
                <select name="tipo_reporte" id="tipo_reporte" class="form-select form-select-sm">
                    <option value="general" {{ $filtros['tipo_reporte'] === 'general' ? 'selected' : '' }}>General (consolidado)</option>
                    <option value="casos" {{ $filtros['tipo_reporte'] === 'casos' ? 'selected' : '' }}>Casos de atención</option>
                    <option value="solicitudes" {{ $filtros['tipo_reporte'] === 'solicitudes' ? 'selected' : '' }}>Solicitudes de apoyo</option>
                    <option value="citas" {{ $filtros['tipo_reporte'] === 'citas' ? 'selected' : '' }}>Citas profesionales</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    Consultar
                </button>
                <a href="{{ route('bienestar.reportes.index') }}" class="btn btn-light btn-sm border">
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Tarjetas resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body p-3">
                <span class="text-muted small">Expedientes de bienestar</span>
                <h3 class="fw-bold my-1 text-dark">{{ $casos['total'] }}</h3>
                <span class="text-muted small">Registrados en el periodo</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body p-3">
                <span class="text-muted small">Solicitudes de apoyo</span>
                <h3 class="fw-bold my-1 text-dark">{{ $solicitudes['total'] }}</h3>
                <span class="text-muted small">Tasa de aprobación: <strong>{{ $solicitudes['tasa_aprobacion'] }}%</strong></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body p-3">
                <span class="text-muted small">Citas profesionales</span>
                <h3 class="fw-bold my-1 text-dark">{{ $citas['total'] }}</h3>
                <span class="text-muted small">Psicología y tutoría universitaria</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Métricas de Casos -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Casos por estado</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end pe-3">Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($casos['por_estado'] as $estado => $cant)
                            @php
                                $pct = $casos['total'] > 0 ? round(($cant / $casos['total']) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="text-dark">{{ ucfirst(str_replace('_', ' ', $estado)) }}</td>
                                <td class="text-end fw-semibold">{{ $cant }}</td>
                                <td class="text-end pe-3 text-muted">{{ $pct }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted small">Sin registros en el periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Métricas de Casos por Tipo -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Casos por tipología</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tipo de caso</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end pe-3">Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($casos['por_tipo'] as $tipo => $cant)
                            @php
                                $pct = $casos['total'] > 0 ? round(($cant / $casos['total']) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="text-dark">{{ $tipo }}</td>
                                <td class="text-end fw-semibold">{{ $cant }}</td>
                                <td class="text-end pe-3 text-muted">{{ $pct }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted small">Sin registros en el periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Métricas de Solicitudes -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Solicitudes de apoyo por dictamen</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Dictamen / Estado</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end pe-3">Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitudes['por_estado'] as $estado => $cant)
                            @php
                                $pct = $solicitudes['total'] > 0 ? round(($cant / $solicitudes['total']) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="text-dark">{{ ucfirst(str_replace('_', ' ', $estado)) }}</td>
                                <td class="text-end fw-semibold">{{ $cant }}</td>
                                <td class="text-end pe-3 text-muted">{{ $pct }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted small">Sin registros en el periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Métricas de Citas -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white py-2">
                <span class="fw-semibold text-dark">Citas profesionales por estado</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end pe-3">Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($citas['por_estado'] as $estado => $cant)
                            @php
                                $pct = $citas['total'] > 0 ? round(($cant / $citas['total']) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="text-dark">{{ ucfirst(str_replace('_', ' ', $estado)) }}</td>
                                <td class="text-end fw-semibold">{{ $cant }}</td>
                                <td class="text-end pe-3 text-muted">{{ $pct }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted small">Sin registros en el periodo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Historial de consultas registradas en bienestar.reporte -->
<div class="card">
    <div class="card-header bg-white py-2">
        <span class="fw-semibold text-dark">Historial de reportes emitidos</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>N° Reporte</th>
                        <th>Tipo</th>
                        <th>Periodo evaluado</th>
                        <th>Formato</th>
                        <th>Usuario solicitante</th>
                        <th class="text-end pe-3">Fecha de generación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historialReportes as $rep)
                        <tr>
                            <td class="fw-semibold">#REP-{{ str_pad($rep->id_reporte, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ ucfirst($rep->tipo_reporte) }}</td>
                            <td class="text-muted small">
                                {{ $rep->periodo_inicio->format('d/m/Y') }} — {{ $rep->periodo_fin->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ strtoupper($rep->formato) }}</span>
                            </td>
                            <td>{{ $rep->usuario?->correo }}</td>
                            <td class="text-end pe-3 text-muted small">
                                {{ $rep->fecha_generacion->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted small">No hay historial de reportes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
