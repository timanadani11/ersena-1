@extends('layouts.admin')

@section('title', 'Gestión de Programas - SENA Control de Asistencia')

@section('page-title', 'Gestión de Programas')

@section('content')
<div class="card fadeIn">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-book"></i> Programas de Formación
        </div>
        <div class="card-actions">
            <a href="#" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nuevo Programa
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre del Programa</th>
                        <th>Ficha</th>
                        <th>Nivel</th>
                        <th>Instructor</th>
                        <th>Aprendices</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programas as $programa)
                    <tr>
                        <td>{{ $programa->nombre_programa }}</td>
                        <td>{{ $programa->numero_ficha }}</td>
                        <td>{{ $programa->nivel_formacion }}</td>
                        <td>{{ $programa->user ? $programa->user->nombres_completos : 'Sin asignar' }}</td>
                        <td>{{ $programa->aprendices_count ?? 0 }}</td>
                        <td>
                            <div class="btn-group">
                                <a href="#" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay programas registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="pagination-container">
            {{ $programas->links() }}
        </div>
    </div>
</div>
@endsection 