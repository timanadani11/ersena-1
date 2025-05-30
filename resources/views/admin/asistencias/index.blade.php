@extends('layouts.admin')

@section('title', 'Registro de Asistencias - SENA Control de Asistencia')

@section('page-title', 'Monitoreo de Asistencias')

@section('content')
<div class="container-fluid py-4" x-data="{ activeDate: '{{ $filtros['fecha'] ?? now()->format('Y-m-d') }}' }">
    <!-- Filtros flotantes minimalistas -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-5 flex flex-wrap items-center gap-2">
        <form action="{{ route('admin.asistencias.index') }}" method="GET" class="w-full flex flex-wrap gap-2 items-center">
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center shadow-sm transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Programa
                </button>
                <div x-show="open" @click.away="open = false" class="absolute z-10 mt-1 bg-white rounded-md shadow-lg p-3 w-72">
                    <select class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50" name="programa_id" onchange="this.form.submit()">
                        <option value="">Todos los programas</option>
                        @foreach($programas as $programa)
                            <option value="{{ $programa->id }}" {{ ($filtros['programa_id'] ?? '') == $programa->id ? 'selected' : '' }}>
                                {{ $programa->nombre_programa }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center shadow-sm transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Jornada
                </button>
                <div x-show="open" @click.away="open = false" class="absolute z-10 mt-1 bg-white rounded-md shadow-lg p-3 w-64">
                    <select class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50" name="jornada_id" onchange="this.form.submit()">
                        <option value="">Todas las jornadas</option>
                        @foreach($jornadas as $jornada)
                            <option value="{{ $jornada->id }}" {{ ($filtros['jornada_id'] ?? '') == $jornada->id ? 'selected' : '' }}>
                                {{ $jornada->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="relative flex-grow">
                <div class="flex">
                    <input type="text" name="search" class="w-full px-4 py-2 border border-gray-200 rounded-full text-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50" placeholder="Buscar aprendiz..." value="{{ $filtros['search'] ?? '' }}">
                    <button type="submit" class="absolute right-1 top-1 bg-green-600 text-white p-1.5 rounded-full hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <a href="{{ route('admin.asistencias.index') }}" class="ml-auto px-2 py-2 text-gray-500 border border-gray-200 rounded-full hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </a>
        </form>
    </div>

    <!-- Calendario de días (últimos 7 días) -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6 overflow-hidden">
        <div class="flex overflow-x-auto pb-2 space-x-4 scrollbar-thin scrollbar-thumb-green-500 scrollbar-track-gray-100" x-ref="dayScroller">
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
                   class="flex-shrink-0 min-w-[100px] h-[100px] flex flex-col items-center justify-center rounded-xl border transition-all hover:-translate-y-1 hover:shadow-md {{ ($filtros['fecha'] ?? $today->format('Y-m-d')) == $date->format('Y-m-d') ? 'bg-green-600 text-white border-green-600 shadow-md' : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-green-500' }}"
                   x-init="if('{{ $date->format('Y-m-d') }}' === activeDate) $nextTick(() => { $refs.dayScroller.scrollLeft = $el.offsetLeft - $refs.dayScroller.offsetWidth / 2 + $el.offsetWidth / 2 })">
                    <span class="text-sm font-semibold capitalize">{{ $date->locale('es')->dayName }}</span>
                    <span class="text-2xl font-bold">{{ $date->format('d') }}</span>
                    <span class="text-xs capitalize">{{ $date->locale('es')->monthName }}</span>
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
        <div class="bg-white rounded-lg shadow-sm p-5 mb-6 animate-fadeIn">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-100 pb-4 mb-4">
                <h4 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ \Carbon\Carbon::parse($date)->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </h4>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-2 sm:mt-0">
                    {{ $asistenciasPorDia->count() }} registros
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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

                    <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-transparent hover:border-l-4 hover:border-green-500 transition-all hover:-translate-y-1 hover:shadow-md">
                        <div class="flex">
                            <div class="flex-shrink-0 mr-3">
                                <div class="w-12 h-12 rounded-full bg-green-600 text-white flex items-center justify-center text-lg font-bold">
                                    {{ substr($user->nombres_completos, 0, 1) }}
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h5 class="text-sm font-semibold text-gray-800 truncate">{{ $user->nombres_completos }}</h5>
                                <div class="text-xs text-gray-600 truncate flex items-center">
                                    <span class="font-mono font-medium">{{ $user->documento_identidad }}</span>
                                    <span class="inline-block w-1 h-1 rounded-full bg-gray-300 mx-2"></span>
                                    <span class="truncate">{{ $user->programaFormacion->nombre_programa ?? 'N/A' }}</span>
                                </div>
                                <div class="text-xs text-gray-600 mt-1">
                                    <span>Ficha: </span>
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded">{{ $user->programaFormacion->numero_ficha ?? 'N/A' }}</span>
                                    <span class="inline-block w-1 h-1 rounded-full bg-gray-300 mx-2"></span>
                                    <span>{{ $user->jornada->nombre ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex mt-3 pt-3 border-t border-dashed border-gray-200">
                            <div class="flex-1 text-center">
                                <div class="text-xs text-gray-500 mb-1">Entrada</div>
                                <div class="{{ $entrada && $entrada->fuera_de_horario ? 'text-red-600' : 'text-gray-800' }} font-bold text-base flex items-center justify-center">
                                    @if($entrada)
                                        {{ $entrada->fecha_hora->format('H:i') }}
                                        @if($entrada->fuera_de_horario)
                                            <svg class="w-4 h-4 ml-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 ml-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-400 italic">Sin registro</span>
                                    @endif
                                </div>
                            </div>
                            <div class="w-px h-10 bg-gray-200 mx-4"></div>
                            <div class="flex-1 text-center">
                                <div class="text-xs text-gray-500 mb-1">Salida</div>
                                <div class="{{ $salida && $salida->salida_anticipada ? 'text-amber-600' : 'text-gray-800' }} font-bold text-base flex items-center justify-center">
                                    @if($salida)
                                        {{ $salida->fecha_hora->format('H:i') }}
                                        @if($salida->salida_anticipada)
                                            <svg class="w-4 h-4 ml-1 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 ml-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-400 italic">Sin registro</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <div class="text-gray-300 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay registros de asistencia</h3>
            <p class="text-gray-500 max-w-md mx-auto">No se encontraron registros para los filtros seleccionados.</p>
        </div>
    @endforelse

    <!-- Paginación -->
    @if(isset($asistencias) && $asistencias->hasPages())
        <div class="flex justify-center mt-6">
            {{ $asistencias->links() }}
        </div>
    @endif
</div>

<style>
/* Animaciones personalizadas */
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

.animate-fadeIn {
    animation: fadeIn 0.5s ease-out;
}

/* Estilos para scrollbar */
.scrollbar-thin::-webkit-scrollbar {
    height: 6px;
}

.scrollbar-thumb-green-500::-webkit-scrollbar-thumb {
    background-color: #10b981;
    border-radius: 10px;
}

.scrollbar-track-gray-100::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 10px;
}
</style>
@endsection 