@extends('layouts.admin')

@section('title', 'Registro de Asistencias - SENA Control de Asistencia')

@section('page-title', 'Monitoreo de Asistencias')

@section('content')
<div class="container-fluid py-4">
    <!-- Filtros flotantes minimalistas -->
    <div class="filtros-container mb-4">
        <form action="{{ route('admin.asistencias.index') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center w-100">
            <div class="dropdown me-2 mb-2">
                <button class="btn btn-filter dropdown-toggle" type="button" id="programaDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-book-open me-1"></i> Programa
                </button>
                <div class="dropdown-menu p-3 shadow-sm" style="width: 300px;">
                    <select class="form-select" name="programa_id" onchange="this.form.submit()">
                        <option value="">Todos los programas</option>
                        @foreach($programas as $programa)
                            <option value="{{ $programa->id }}" {{ ($filtros['programa_id'] ?? '') == $programa->id ? 'selected' : '' }}>
                                {{ $programa->nombre_programa }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="dropdown me-2 mb-2">
                <button class="btn btn-filter dropdown-toggle" type="button" id="jornadaDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-clock me-1"></i> Jornada
                </button>
                <div class="dropdown-menu shadow-sm p-3">
                    <select class="form-select" name="jornada_id" onchange="this.form.submit()">
                        <option value="">Todas las jornadas</option>
                        @foreach($jornadas as $jornada)
                            <option value="{{ $jornada->id }}" {{ ($filtros['jornada_id'] ?? '') == $jornada->id ? 'selected' : '' }}>
                                {{ $jornada->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="search-container me-2 mb-2 flex-grow-1">
                <div class="search-box">
                    <input type="text" name="search" class="search-input" placeholder="Buscar aprendiz..." value="{{ $filtros['search'] ?? '' }}">
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <a href="{{ route('admin.asistencias.index') }}" class="btn btn-outline-secondary btn-sm mb-2 ms-auto">
                <i class="fas fa-sync-alt"></i>
            </a>
        </form>
    </div>

    <!-- Calendario de días (últimos 7 días) -->
    <div class="calendar-days mb-4">
        <div class="day-scroller">
            @php
                $today = now();
                $dates = [];
                for ($i = 0; $i < 7; $i++) {
                    $date = $today->copy()->subDays($i);
                    $dates[] = $date;
                }
            @endphp

            @foreach($dates as $date)
                <a href="{{ route('admin.asistencias.index', ['fecha' => $date->format('Y-m-d')]) }}" 
                   class="day-item {{ ($filtros['fecha'] ?? $today->format('Y-m-d')) == $date->format('Y-m-d') ? 'active' : '' }}">
                    <span class="day-name">{{ $date->locale('es')->dayName }}</span>
                    <span class="day-number">{{ $date->format('d') }}</span>
                    <span class="month-name">{{ $date->locale('es')->monthName }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Tarjetas de asistencia agrupadas por fecha -->
    @php
        // Group by date first
        $groupedByDate = $asistencias->groupBy(function($asistencia) {
            return $asistencia->fecha_hora->format('Y-m-d');
        });
    @endphp

    @forelse($groupedByDate as $date => $asistenciasPorDia)
        <div class="date-section mb-4">
            <div class="date-header">
                <h4>
                    <i class="fas fa-calendar-day"></i> 
                    {{ \Carbon\Carbon::parse($date)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </h4>
                <span class="badge bg-info">{{ $asistenciasPorDia->count() }} registros</span>
            </div>

            <div class="asistencias-grid">
                @php
                    // Group by user within this date
                    $groupedByUser = $asistenciasPorDia->groupBy('user_id');
                @endphp

                @foreach($groupedByUser as $userId => $asistenciasUsuario)
                    @php
                        $user = $asistenciasUsuario->first()->user;
                        $entrada = $asistenciasUsuario->firstWhere('tipo', 'entrada');
                        $salida = $asistenciasUsuario->firstWhere('tipo', 'salida');
                    @endphp

                    <div class="asistencia-card">
                        <div class="user-avatar">
                            <div class="avatar-circle">
                                {{ substr($user->nombres_completos, 0, 1) }}
                            </div>
                        </div>
                        <div class="user-info">
                            <h5 class="user-name">{{ $user->nombres_completos }}</h5>
                            <div class="user-details">
                                <span class="user-doc">{{ $user->documento_identidad }}</span>
                                <span class="dot-separator"></span>
                                <span class="user-program">{{ $user->programaFormacion->nombre_programa ?? 'N/A' }}</span>
                            </div>
                            <div class="user-ficha">
                                Ficha: <span class="badge ficha-badge">{{ $user->programaFormacion->numero_ficha ?? 'N/A' }}</span>
                                <span class="dot-separator"></span>
                                {{ $user->jornada->nombre ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="asistencia-times">
                            <div class="time-column entrada">
                                <div class="time-label">Entrada</div>
                                <div class="time-value {{ $entrada && $entrada->fuera_de_horario ? 'text-danger' : '' }}">
                                    @if($entrada)
                                        {{ $entrada->fecha_hora->format('H:i') }}
                                        @if($entrada->fuera_de_horario)
                                            <span class="status-icon late-icon" title="Llegada tarde">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </span>
                                        @else
                                            <span class="status-icon ontime-icon" title="A tiempo">
                                                <i class="fas fa-check-circle"></i>
                                            </span>
                                        @endif
                                    @else
                                        <span class="no-registro">Sin registro</span>
                                    @endif
                                </div>
                            </div>
                            <div class="time-divider"></div>
                            <div class="time-column salida">
                                <div class="time-label">Salida</div>
                                <div class="time-value {{ $salida && $salida->salida_anticipada ? 'text-warning' : '' }}">
                                    @if($salida)
                                        {{ $salida->fecha_hora->format('H:i') }}
                                        @if($salida->salida_anticipada)
                                            <span class="status-icon early-icon" title="Salida anticipada">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </span>
                                        @else
                                            <span class="status-icon ontime-icon" title="A tiempo">
                                                <i class="fas fa-check-circle"></i>
                                            </span>
                                        @endif
                                    @else
                                        <span class="no-registro">Sin registro</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h3>No hay registros de asistencia</h3>
            <p>No se encontraron registros para los filtros seleccionados.</p>
        </div>
    @endforelse

    <!-- Paginación elegante -->
    @if(isset($asistencias) && $asistencias->hasPages())
        <div class="pagination-container">
            {{ $asistencias->links() }}
        </div>
    @endif
</div>

<style>
/* Contenedor de filtros flotantes */
.filtros-container {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    background-color: white;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.btn-filter {
    background-color: white;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 50px;
    padding: 8px 16px;
    font-size: 14px;
    transition: all 0.3s;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    white-space: nowrap;
}

.btn-filter:hover {
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.search-container {
    position: relative;
    max-width: 100%;
}

.search-box {
    display: flex;
    align-items: center;
    width: 100%;
}

.search-input {
    padding: 8px 16px;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 50px;
    font-size: 14px;
    width: 100%;
    transition: all 0.3s;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 3px 8px rgba(57, 169, 0, 0.15);
}

.search-button {
    background-color: var(--primary-color);
    color: white;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    margin-left: -40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    flex-shrink: 0;
}

.search-button:hover {
    background-color: var(--accent-color);
    transform: rotate(90deg);
}

/* Calendario de días */
.calendar-days {
    background-color: white;
    border-radius: 15px;
    padding: 16px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    overflow: hidden;
}

.day-scroller {
    display: flex;
    overflow-x: auto;
    padding-bottom: 8px;
    scrollbar-width: thin;
    scrollbar-color: var(--primary-color) #f0f0f0;
    gap: 15px;
    -webkit-overflow-scrolling: touch;
}

.day-scroller::-webkit-scrollbar {
    height: 6px;
}

.day-scroller::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 10px;
}

.day-scroller::-webkit-scrollbar-thumb {
    background-color: var(--primary-color);
    border-radius: 10px;
}

.day-item {
    min-width: 100px;
    height: 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
    border: 1px solid #eee;
    background: #f9f9f9;
    padding: 10px;
    flex-shrink: 0;
}

.day-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-color: var(--primary-color);
}

.day-item.active {
    background: var(--primary-color);
    color: white;
    box-shadow: 0 5px 15px rgba(57, 169, 0, 0.3);
}

.day-name {
    text-transform: capitalize;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 5px;
}

.day-number {
    font-size: 26px;
    font-weight: bold;
    line-height: 1;
}

.month-name {
    font-size: 12px;
    text-transform: capitalize;
    margin-top: 5px;
}

/* Secciones de fecha */
.date-section {
    background-color: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    margin-bottom: 30px;
    animation: fadeIn 0.5s ease-out;
}

.date-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.date-header h4 {
    font-size: 18px;
    color: #333;
    font-weight: 600;
    margin: 0;
}

.asistencias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

/* Tarjetas de asistencia */
.asistencia-card {
    background-color: #fafafa;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-wrap: wrap;
    position: relative;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: all 0.3s;
    border-left: 4px solid transparent;
}

.asistencia-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transform: translateY(-3px);
    border-left: 4px solid var(--primary-color);
}

.user-avatar {
    margin-right: 15px;
    flex-shrink: 0;
}

.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: bold;
}

.user-info {
    flex: 1;
    min-width: 0; /* Allow text to truncate */
}

.user-name {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 5px 0;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-details, .user-ficha {
    font-size: 13px;
    color: #666;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-doc {
    font-family: monospace;
    font-weight: 600;
}

.dot-separator {
    display: inline-block;
    width: 4px;
    height: 4px;
    background-color: #ccc;
    border-radius: 50%;
    margin: 0 8px;
    vertical-align: middle;
}

.ficha-badge {
    background-color: var(--info);
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 500;
}

.asistencia-times {
    display: flex;
    align-items: center;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed #eee;
    width: 100%;
}

.time-column {
    text-align: center;
    flex: 1;
}

.time-divider {
    width: 1px;
    height: 40px;
    background-color: #eee;
    margin: 0 15px;
}

.time-label {
    font-size: 12px;
    color: #888;
    margin-bottom: 5px;
}

.time-value {
    font-size: 18px;
    font-weight: 700;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-icon {
    margin-left: 5px;
    font-size: 14px;
}

.ontime-icon {
    color: var(--success);
}

.late-icon {
    color: var(--danger);
}

.early-icon {
    color: var(--warning);
}

.no-registro {
    font-size: 14px;
    color: #999;
    font-style: italic;
}

/* Estado vacío */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background-color: white;
    border-radius: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
}

.empty-icon {
    font-size: 60px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 24px;
    color: #555;
    margin-bottom: 10px;
}

.empty-state p {
    color: #888;
    max-width: 400px;
    margin: 0 auto;
}

/* Animaciones */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsividad */
@media (max-width: 992px) {
    .asistencias-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}

@media (max-width: 768px) {
    .asistencias-grid {
        grid-template-columns: 1fr;
    }
    
    .day-item {
        min-width: 80px;
        height: 80px;
    }
    
    .filtros-container form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-container {
        width: 100%;
    }
    
    .btn-filter {
        width: 100%;
        margin-right: 0;
    }
}

@media (max-width: 576px) {
    .date-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .date-header .badge {
        margin-top: 10px;
    }
    
    .asistencia-card {
        padding: 12px;
    }
    
    .user-avatar {
        margin-right: 10px;
    }
    
    .avatar-circle {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
}
</style>

<script>
// Script para autoseleccionar la fecha en el scroller
document.addEventListener('DOMContentLoaded', function() {
    // Encuentra el elemento activo
    const activeDay = document.querySelector('.day-item.active');
    if (activeDay) {
        // Scroller hasta el elemento activo
        activeDay.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }
});
</script>
@endsection 