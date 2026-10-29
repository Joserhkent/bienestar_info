@extends('bienestar::layout')

@section('title', 'Notificaciones — Bienestar UNT')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-dark mb-0">Notificaciones</h4>
        <span class="text-muted small">Avisos y actualizaciones de trámites</span>
    </div>
    <div class="d-flex gap-2">
        @if($totalNoLeidas > 0)
            <form action="{{ route('bienestar.notificaciones.leerTodas') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    Marcar todas como leídas
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Filtros de bandeja -->
<div class="d-flex gap-2 mb-3">
    <a href="{{ route('bienestar.notificaciones.index') }}" class="btn btn-sm {{ !$soloNoLeidas ? 'btn-primary' : 'btn-outline-secondary' }}">
        Todas
    </a>
    <a href="{{ route('bienestar.notificaciones.index', ['filtro' => 'no_leidas']) }}" class="btn btn-sm {{ $soloNoLeidas ? 'btn-primary' : 'btn-outline-secondary' }}">
        No leídas @if($totalNoLeidas > 0) ({{ $totalNoLeidas }}) @endif
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 110px;">Estado</th>
                        <th style="width: 220px;">Asunto</th>
                        <th>Mensaje</th>
                        <th style="width: 150px;">Fecha</th>
                        <th style="width: 120px;" class="text-end pe-3">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notificaciones as $n)
                        <tr class="{{ !$n->leida ? 'bg-light' : '' }}">
                            <td>
                                @if(!$n->leida)
                                    <span class="badge bg-primary text-white">No leída</span>
                                @else
                                    <span class="badge bg-light text-muted border">Leída</span>
                                @endif
                            </td>
                            <td class="fw-medium text-dark">
                                {{ $n->titulo }}
                            </td>
                            <td class="text-secondary small" style="white-space: pre-wrap;">{{ $n->mensaje }}</td>
                            <td class="text-muted small">
                                {{ $n->fecha_envio->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-end pe-3">
                                @if(!$n->leida)
                                    <form action="{{ route('bienestar.notificaciones.leer', $n->id_notificacion) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Marcar como leída">
                                            Leída
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted small">
                                No tiene notificaciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($notificaciones->hasPages())
        <div class="card-footer bg-white border-top py-2">
            {{ $notificaciones->links() }}
        </div>
    @endif
</div>
@endsection
