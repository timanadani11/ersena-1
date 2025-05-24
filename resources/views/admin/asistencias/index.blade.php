@extends('layouts.admin')

@section('title', 'Registro de Asistencias - SENA Control de Asistencia')

@section('page-title', 'Registro de Asistencias')

@section('content')
<div class="card fadeIn">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-calendar-check"></i> Registro de Asistencias
        </div>
        <div class="card-actions">
            <div class="search-form">
                <form method="GET" action="{{ route('admin.asistencias.index') }}" class="search-form-inner">
                    <input type="text" name="search" placeholder="Buscar por nombre o documento..." class="form-control form-control-sm" value="{{ request()->search }}">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="export-buttons">
                <a href="#" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="#" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.asistencias.index') }}" class="filter-form">
                <div class="filter-grid">
                    <div class="filter-item">
                        <label>Fecha inicio:</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="{{ request()->fecha_inicio }}">
                    </div>
                    <div class="filter-item">
                        <label>Fecha fin:</label>
                        <input type="date" name="fecha_fin" class="form-control form-control-sm" value="{{ request()->fecha_fin }}">
                    </div>
                    <div class="filter-item">
                        <label>Programa:</label>
                        <select name="programa_id" class="form-control form-control-sm">
                            <option value="">Todos los programas</option>
                            <!-- @foreach($programas ?? [] as $programa)
                                <option value="{{ $programa->id }}" {{ request()->programa_id == $programa->id ? 'selected' : '' }}>
                                    {{ $programa->nombre_programa }}
                                </option>
                            @endforeach -->
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>Tipo:</label>
                        <select name="tipo" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="entrada" {{ request()->tipo == 'entrada' ? 'selected' : '' }}>Entrada</option>
                            <option value="salida" {{ request()->tipo == 'salida' ? 'selected' : '' }}>Salida</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                        <a href="{{ route('admin.asistencias.index') }}" class="btn btn-sm btn-secondary">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Documento</th>
                        <th>Aprendiz</th>
                        <th>Programa</th>
                        <th>Ficha</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asistencias as $asistencia)
                    <tr>
                        <td>{{ $asistencia->fecha_hora->format('d/m/Y H:i') }}</td>
                        <td>{{ $asistencia->user->documento_identidad }}</td>
                        <td>{{ $asistencia->user->nombres_completos }}</td>
                        <td>{{ $asistencia->user->programaFormacion->nombre_programa ?? 'N/A' }}</td>
                        <td>{{ $asistencia->user->programaFormacion->numero_ficha ?? 'N/A' }}</td>
                        <td>
                            <span class="badge {{ $asistencia->tipo === 'entrada' ? 'bg-success' : 'bg-info' }}">
                                {{ $asistencia->tipo === 'entrada' ? 'Entrada' : 'Salida' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $estado = 'A tiempo';
                                $badgeClass = 'bg-success';
                                
                                if ($asistencia->tipo === 'entrada' && $asistencia->es_tarde) {
                                    $estado = 'Tarde';
                                    $badgeClass = 'bg-danger';
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $estado }}</span>
                        </td>
                        <td>{{ $asistencia->registradoPor->nombres_completos ?? 'Sistema' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay registros de asistencia</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container">
            {{ $asistencias->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<style>
.card-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.search-form {
    flex: 1;
}

.search-form-inner {
    display: flex;
    gap: 0.5rem;
}

.export-buttons {
    display: flex;
    gap: 0.5rem;
}

.filter-section {
    margin-bottom: 1.5rem;
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filter-item label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
    align-items: flex-end;
}

.badge {
    padding: 0.35em 0.65em;
    font-size: 0.75em;
    font-weight: 600;
    color: #fff;
    border-radius: 4px;
}

.bg-success {
    background-color: #10b981;
}

.bg-danger {
    background-color: #ef4444;
}

.bg-info {
    background-color: #3b82f6;
}
</style>
@endsection 