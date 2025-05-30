@extends('layouts.admin')

@section('title', 'Gestión de Programas - SENA Control de Asistencia')

@section('page-title', 'Gestión de Programas')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="border-b p-4 flex justify-between items-center">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700">Programas de Formación</h3>
        </div>
        <div>
            <a href="#" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nuevo Programa
            </a>
        </div>
    </div>
    <div class="p-4">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Nombre del Programa</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Ficha</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Nivel</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Instructor</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Aprendices</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programas as $programa)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $programa->nombre_programa }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $programa->numero_ficha }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $programa->nivel_formacion }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $programa->user ? $programa->user->nombres_completos : 'Sin asignar' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $programa->aprendices_count ?? 0 }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div class="flex space-x-2">
                                <a href="#" class="inline-flex items-center p-1.5 border border-transparent rounded-md text-blue-600 hover:bg-blue-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="#" class="inline-flex items-center p-1.5 border border-transparent rounded-md text-indigo-600 hover:bg-indigo-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <a href="#" class="inline-flex items-center p-1.5 border border-transparent rounded-md text-red-600 hover:bg-red-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-3 text-center text-gray-500">No hay programas registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex justify-center">
            {{ $programas->links() }}
        </div>
    </div>
</div>
@endsection 