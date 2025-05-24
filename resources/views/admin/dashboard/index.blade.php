@extends('layouts.admin')

@section('title', 'Dashboard - SENA Control de Asistencia')

@section('page-title', 'Dashboard Administrativo')

@section('content')
<div class="dashboard-content fadeIn">
    <!-- Stats Cards -->
    <div class="stats-cards">
        <div class="stat-card delay-100">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-title">Total Aprendices</div>
                    <div class="stat-card-value">{{ $estadisticas['total_aprendices'] }}</div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card delay-200">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-title">Asistencias Hoy</div>
                    <div class="stat-card-value">{{ $estadisticas['asistencias_hoy'] }}</div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
            <div class="stat-card-trend">
                <i class="fas fa-circle" style="font-size: 8px; margin-right: 4px;"></i>
                <span>{{ $estadisticas['porcentaje_asistencia_hoy'] }}% de asistencia</span>
            </div>
        </div>
        
        <div class="stat-card delay-300">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-title">Tardanzas (Este mes)</div>
                    <div class="stat-card-value">{{ $estadisticas['porcentaje_tardanzas'] }}%</div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card delay-400">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-title">Tendencia Semanal</div>
                    <div class="stat-card-value">{{ $estadisticas['tendencia_semanal'] > 0 ? '+' : '' }}{{ $estadisticas['tendencia_semanal'] }}%</div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="stat-card-trend {{ $estadisticas['tendencia_semanal'] >= 0 ? 'trend-up' : 'trend-down' }}">
                <i class="fas fa-{{ $estadisticas['tendencia_semanal'] >= 0 ? 'arrow-up' : 'arrow-down' }}" style="margin-right: 4px;"></i>
                <span>vs. semana anterior</span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-container">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Asistencias Diarias (Últimos 7 días)</div>
            </div>
            <div class="chart-wrapper">
                <canvas id="asistenciasChart"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-title">Distribución por Programa</div>
            </div>
            <div class="chart-wrapper">
                <canvas id="programasChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Puntualidad Chart -->
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">Puntualidad (Este mes)</div>
        </div>
        <div class="chart-wrapper" style="display: flex; align-items: center; justify-content: center;">
            <div style="width: 50%; max-width: 300px;">
                <canvas id="puntualidadChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar estadísticas para gráficos
        cargarEstadisticasGraficos();
    });

    // Cargar estadísticas para gráficos
    function cargarEstadisticasGraficos() {
        $.ajax({
            url: '{{ route("admin.estadisticas.graficos") }}',
            method: 'GET',
            success: function(response) {
                // Inicializar los gráficos con los datos recibidos
                inicializarGraficoAsistenciasDiarias(response.asistencias_por_dia);
                inicializarGraficoProgramas(response.asistencias_por_programa);
                inicializarGraficoPuntualidad(response.puntualidad);
            },
            error: function(error) {
                console.error('Error al cargar estadísticas:', error);
                showNotification('Error al cargar estadísticas de gráficos', 'error');
            }
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