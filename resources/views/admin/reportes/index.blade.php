@extends('layouts.admin')

@section('title', 'Reportes - SENA Control de Asistencia')

@section('page-title', 'Reportes de Asistencia')

@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
    <div class="border-b border-gray-100 px-5 py-4">
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-800">Generación de Reportes</h3>
        </div>
    </div>
    
    <div class="p-5">
        <form method="POST" action="{{ route('admin.reportes.pdf') }}" id="reportForm">
            @csrf
            <div class="space-y-6">
                <!-- Tipo de reporte -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Tipo de Reporte</h4>
                    <div class="flex flex-wrap gap-2" x-data="{ selectedType: 'diario' }">
                        <template x-for="(option, index) in [
                            {value: 'diario', label: 'Diario'}, 
                            {value: 'semanal', label: 'Semanal'}, 
                            {value: 'mensual', label: 'Mensual'}, 
                            {value: 'personalizado', label: 'Personalizado'}, 
                            {value: 'programa', label: 'Por Programa'}, 
                            {value: 'jornada', label: 'Por Jornada'}, 
                            {value: 'aprendiz', label: 'Por Aprendiz'}
                        ]">
                            <div>
                                <input type="radio" :id="'tipo_' + option.value" name="tipo_reporte" :value="option.value" 
                                       class="hidden peer" :checked="option.value === 'diario'"
                                       @change="selectedType = option.value">
                                <label :for="'tipo_' + option.value" 
                                       class="inline-block px-4 py-2 border rounded-lg text-sm font-medium transition-colors
                                              peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600
                                              border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer">
                                    <span x-text="option.label"></span>
                                </label>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Filtros para todos los tipos de reportes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ 
                     tipo_reporte: 'diario',
                     showPersonalizado: false,
                     showPrograma: false,
                     showJornada: false,
                     showAprendiz: false
                 }" 
                 x-init="$watch('tipo_reporte', value => {
                     showPersonalizado = value === 'personalizado';
                     showPrograma = value === 'programa';
                     showJornada = value === 'jornada';
                     showAprendiz = value === 'aprendiz';
                 })">
                    
                    <!-- Escuchar cambios en los radio buttons -->
                    <template x-for="type in ['diario', 'semanal', 'mensual', 'personalizado', 'programa', 'jornada', 'aprendiz']">
                        <input type="radio" :name="'tipo_' + type" :value="type" class="hidden"
                               @change="tipo_reporte = type">
                    </template>

                    <!-- Filtros personalizados -->
                    <div x-show="showPersonalizado" class="col-span-1">
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio:</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                    </div>

                    <div x-show="showPersonalizado" class="col-span-1">
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">Fecha fin:</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                    </div>

                    <!-- Filtros programa -->
                    <div x-show="showPrograma" class="col-span-2">
                        <label for="programa_id" class="block text-sm font-medium text-gray-700 mb-1">Programa de Formación:</label>
                        <select name="programa_id" id="programa_id" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                            <option value="">Todos los programas</option>
                            @foreach($programas as $programa)
                                <option value="{{ $programa->id }}">
                                    {{ $programa->nombre_programa }} ({{ $programa->numero_ficha ?? 'Sin ficha' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtros jornada -->
                    <div x-show="showJornada" class="col-span-2">
                        <label for="jornada_id" class="block text-sm font-medium text-gray-700 mb-1">Jornada:</label>
                        <select name="jornada_id" id="jornada_id" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                            <option value="">Todas las jornadas</option>
                            @foreach($jornadas as $jornada)
                                <option value="{{ $jornada->id }}">{{ $jornada->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtros aprendiz -->
                    <div x-show="showAprendiz" class="col-span-2" x-data="{ searchResults: [] }">
                        <label for="aprendiz_search" class="block text-sm font-medium text-gray-700 mb-1">Buscar aprendiz:</label>
                        <div class="flex">
                            <input type="text" id="aprendiz_search" 
                                   class="flex-1 border-gray-300 rounded-l-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                   placeholder="Nombre o documento...">
                            <button type="button" id="btn-search-aprendiz" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-r-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="aprendices-results" class="mt-2 max-h-40 overflow-y-auto rounded-md shadow-sm"></div>
                        <input type="hidden" name="aprendiz_id" id="aprendiz_id" value="">
                    </div>

                    <!-- Tipo de registro - Visible para todos -->
                    <div>
                        <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo de registro:</label>
                        <select name="tipo" id="tipo" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                            <option value="">Ambos tipos</option>
                            <option value="entrada">Solo entradas</option>
                            <option value="salida">Solo salidas</option>
                        </select>
                    </div>
                    
                    <!-- Opciones adicionales -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Opciones de reporte:</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="checkbox" name="incluir_estadisticas" id="incluir_estadisticas" checked
                                       class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                <label class="ml-2 text-sm text-gray-700" for="incluir_estadisticas">
                                    Incluir estadísticas detalladas
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="incluir_graficos" id="incluir_graficos" checked
                                       class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                <label class="ml-2 text-sm text-gray-700" for="incluir_graficos">
                                    Incluir gráficos
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="orientacion_horizontal" id="orientacion_horizontal" checked
                                       class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                <label class="ml-2 text-sm text-gray-700" for="orientacion_horizontal">
                                    Orientación horizontal (recomendado)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm transition flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Generar Reporte PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Búsqueda de aprendices
    document.getElementById('btn-search-aprendiz')?.addEventListener('click', function() {
        const query = document.getElementById('aprendiz_search').value;
        if (query.length < 3) {
            alert('Ingrese al menos 3 caracteres para buscar');
            return;
        }
        
        fetch('/admin/api/buscar-aprendices?query=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                let html = '<div class="divide-y divide-gray-200">';
                if (data.length === 0) {
                    html += '<div class="py-2 px-3 text-gray-500 text-sm">No se encontraron resultados</div>';
                } else {
                    data.forEach(function(aprendiz) {
                        html += `<button type="button" class="w-full text-left py-2 px-3 hover:bg-gray-100 text-sm select-aprendiz" 
                                  data-id="${aprendiz.id}" data-nombre="${aprendiz.nombres_completos}">
                                  <div class="font-medium text-gray-800">${aprendiz.nombres_completos}</div>
                                  <div class="text-gray-500 text-xs">${aprendiz.documento_identidad}</div>
                                 </button>`;
                    });
                }
                html += '</div>';
                document.getElementById('aprendices-results').innerHTML = html;
                
                // Agregar eventos a los botones
                document.querySelectorAll('.select-aprendiz').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const nombre = this.getAttribute('data-nombre');
                        
                        document.getElementById('aprendiz_id').value = id;
                        document.getElementById('aprendiz_search').value = nombre;
                        document.getElementById('aprendices-results').innerHTML = '';
                    });
                });
            })
            .catch(error => {
                console.error('Error al buscar aprendices:', error);
                document.getElementById('aprendices-results').innerHTML = 
                    '<div class="py-2 px-3 text-red-500 text-sm">Error al buscar aprendices</div>';
            });
    });
</script>
@endpush
@endsection 