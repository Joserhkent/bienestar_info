@extends('bienestar::layout')

@section('title', 'Acceso no autorizado — Bienestar UNT')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-6 text-center">
        <div class="card p-4 shadow-sm border-0">
            <div class="card-body">
                <span class="badge bg-light text-secondary border px-3 py-2 mb-3">403 Forbidden</span>
                <h4 class="fw-bold text-dark mb-2">Acceso restringido</h4>
                <p class="text-muted mb-4">No tienes acceso a esta sección.</p>
                <a href="{{ auth()->check() ? route('bienestar.dashboard') : route('bienestar.login') }}" class="btn btn-primary btn-sm px-4">
                    Volver al inicio
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
