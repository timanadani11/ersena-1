@extends('layouts.admin')

@section('title', 'Dashboard - SENA Control de Asistencia')

@section('page-title', 'Dashboard Administrativo')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-500 transform transition duration-300 hover:scale-105">
            <div class="flex justify-between items-center">
                <div>
                    <div class="text-gray-500 font-medium">Total Aprendices</div>
                    <div class="text-2xl font-bold mt-1">{{ $estadisticas['total_aprendices'] }}</div>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-green-500 transform transition duration-300 hover:scale-105">
            <div class="flex justify-between items-center">
                <div>
                    <div class="text-gray-500 font-medium">Asistencias Hoy</div>
                    <div class="text-2xl font-bold mt-1">{{ $estadisticas['asistencias_hoy'] }}</div>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center mt-4 text-sm text-gray-500">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                <span>{{ $estadisticas['porcentaje_asistencia_hoy'] }}% de asistencia</span>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-yellow-500 transform transition duration-300 hover:scale-105">
            <div class="flex justify-between items-center">
                <div>
                    <div class="text-gray-500 font-medium">Tardanzas (Este mes)</div>
                    <div class="text-2xl font-bold mt-1">{{ $estadisticas['porcentaje_tardanzas'] }}%</div>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-purple-500 transform transition duration-300 hover:scale-105">
            <div class="flex justify-between items-center">
                <div>
                    <div class="text-gray-500 font-medium">Tendencia Semanal</div>
                    <div class="text-2xl font-bold mt-1">{{ $estadisticas['tendencia_semanal'] > 0 ? '+' : '' }}{{ $estadisticas['tendencia_semanal'] }}%</div>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center mt-4 text-sm {{ $estadisticas['tendencia_semanal'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M{{ $estadisticas['tendencia_semanal'] >= 0 ? '13 7l5 5m0 0l-5 5m5-5H6' : '11 17l-5-5m0 0l5-5m-5 5h12' }}"></path>
                </svg>
                <span>vs. semana anterior</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-700">Asistencias Diarias (Últimos 7 días)</h3>
            </div>
            <div class="h-64">
                <canvas id="asistenciasChart"></canvas>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-700">Distribución por Programa</h3>
            </div>
            <div class="h-64">
                <canvas id="programasChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Puntualidad Chart -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-700">Puntualidad (Este mes)</h3>
        </div>
        <div class="flex justify-center">
            <div class="w-full max-w-md h-64">
                <canvas id="puntualidadChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // AJAX with Fetch API
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar estadísticas para gráficos
        cargarEstadisticasGraficos();
    });

    // Cargar estadísticas para gráficos
    function cargarEstadisticasGraficos() {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch('{{ route("admin.estadisticas.graficos") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            // Inicializar los gráficos con los datos recibidos
            inicializarGraficoAsistenciasDiarias(data.asistencias_por_dia);
            inicializarGraficoProgramas(data.asistencias_por_programa);
            inicializarGraficoPuntualidad(data.puntualidad);
        })
        .catch(error => {
            console.error('Error al cargar estadísticas:', error);
            showNotification('Error al cargar estadísticas de gráficos', 'error');
        });
    }

    function inicializarGraficoAsistenciasDiarias(datos) {
        const ctx = document.getElementById('asistenciasChart').getContext('2d');
        
        // Preparar los datos para el gráfico
        const labels = datos.map(item => {
            // Formatear la fecha para mostrar solo el día de la semana
            const fecha = new Date(item.fecha);
            return fecha.toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric' });
        });
        
        const values = datos.map(item => item.total);
        
        // Crear el gráfico
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Asistencias',
                    data: values,
                    backgroundColor: 'rgba(57, 169, 0, 0.6)',
                    borderColor: 'rgba(57, 169, 0, 1)',
                    borderWidth: 1,
                    borderRadius: 5,
                    barThickness: 25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                return 'Fecha: ' + datos[tooltipItems[0].dataIndex].fecha;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Número de asistencias'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function inicializarGraficoProgramas(datos) {
        const ctx = document.getElementById('programasChart').getContext('2d');
        
        // Preparar los datos para el gráfico
        const labels = datos.map(item => item.nombre_programa ? item.nombre_programa : 'Sin programa');
        const values = datos.map(item => item.total);
        
        // Generar colores para cada programa
        const generateColors = (count) => {
            const baseColors = [
                'rgba(57, 169, 0, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 159, 64, 0.7)',
                'rgba(255, 99, 132, 0.7)',
                'rgba(255, 205, 86, 0.7)'
            ];
            
            const colors = [];
            for (let i = 0; i < count; i++) {
                colors.push(baseColors[i % baseColors.length]);
            }
            return colors;
        };
        
        // Crear el gráfico
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: generateColors(labels.length),
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 15,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.formattedValue;
                                const dataset = context.dataset;
                                const total = dataset.data.reduce((acc, data) => acc + data, 0);
                                const percentage = Math.round((context.raw / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function inicializarGraficoPuntualidad(datos) {
        const ctx = document.getElementById('puntualidadChart').getContext('2d');
        
        // Crear el gráfico
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['A tiempo', 'Tarde'],
                datasets: [{
                    data: [datos.a_tiempo, datos.tarde],
                    backgroundColor: [
                        'rgba(57, 169, 0, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ],
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.formattedValue;
                                const dataset = context.dataset;
                                const total = dataset.data.reduce((acc, data) => acc + data, 0);
                                const percentage = Math.round((context.raw / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endsection 