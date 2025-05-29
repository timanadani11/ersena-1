<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'ERSENA') }}</title>
        <link rel="stylesheet" href="{{ asset('css/common.css') }}">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
        <!-- icono -->
        <link rel="icon" href="{{ asset('img/icon/logoSena.png') }}" type="image/png">
        <!-- GSAP for smooth animations -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
        <!-- Chart.js para los gráficos -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body>
        <div class="top-bar">
            <div class="logo">
                <img src="{{ asset('img/logo/logoSena.png') }}" alt="ERSENA Logo">
            </div>
            <div id="anuncio-container">
                <div class="ticker-wrapper">
                    <!-- Messages will be inserted here dynamically -->
                </div>
                <div class="ticker-progress"></div>
            </div>
            <a href="{{ route('login') }}">
                <button class="btn-login">Iniciar Sesión</button>
            </a>
        </div>

        <!-- Vista principal - Solo Tabla de asistencias -->
        <div class="dashboard-view active" id="main-view">
            <div class="table-dashboard">
                <div class="header-widget">
                    <div class="header-content-left">
                        <h1>SENA Regional Caquetá</h1>
                        <div class="subtitle-container">
                            <h2>Control de entradas de aprendices</h2>
                            <div class="counter-box">
                                <span class="counter-label">Total Asistencias:</span>
                                <span class="counter-value" id="total-count">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="update-time-container">
                        <i class="fas fa-clock"></i>
                        <span id="update-time"></span>
                    </div>
                </div>
                
                <div class="main-table-widget" style="flex-grow: 1; display: flex; flex-direction: column; background: white; border-radius: 12px; box-shadow: var(--shadow-card); overflow: hidden;">
                    <div class="table-header">
                        <h3 class="widget-title"><i class="fas fa-users"></i> Registro de Asistencias</h3>
                        <div class="view-tabs">
                            <div class="view-tab" data-view="all">Todos</div>
                            <div class="view-tab active" data-view="entrada">Entradas</div>
                            <div class="view-tab" data-view="salida">Salidas</div>
                        </div>
                    </div>
                    <div class="table-scroll-container" style="flex-grow: 1; overflow-y: auto; padding: 0 16px;">
                        <div id="asistencias-simple">
                                <!-- Los datos serán cargados dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Vista de actividad reciente -->
        <div class="dashboard-view" id="activity-view">
            <div class="table-dashboard">
                <div class="header-widget">
                    <div class="header-content-left">
                        <h1>SENA Regional Caquetá</h1>
                        <div class="subtitle-container">
                            <h2>Actividad Reciente</h2>
                        </div>
                    </div>
                    <div class="update-time-container">
                        <i class="fas fa-clock"></i>
                        <span id="update-time-activity"></span>
                    </div>
                </div>
                
                <div class="main-table-widget" style="flex-grow: 1; display: flex; flex-direction: column; background: white; border-radius: 12px; box-shadow: var(--shadow-card); overflow: hidden;">
                    <div class="table-header">
                        <h3 class="widget-title"><i class="fas fa-history"></i> Últimas Actividades</h3>
                        </div>
                    <div class="timeline" id="activity-timeline" style="padding: 20px; overflow-y: auto; height: calc(100vh - 240px);">
                        <!-- Se llenará dinámicamente con datos de actividad reciente -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botón para alternar entre vistas -->
        <div class="view-toggle" id="view-toggle">
            <i class="fas fa-exchange-alt"></i>
        </div>

        <script>
            let asistenciasInterval;
            let currentView = 'entrada'; // Por defecto mostrar solo entradas
            let programsChart = null;
            let jornadasChart = null;
            let weeklyChart = null;
            let horasChart = null;
            let asistenciasDiarias = [];
            let activeView = 'main-view';
            let viewRotationInterval = null;
            
            // Inicializar cuando se carga el documento
            document.addEventListener('DOMContentLoaded', function() {
                // Cargar asistencias inmediatamente
                loadAsistencias();
                
                // Configurar actualización automática cada 1 segundo
                asistenciasInterval = setInterval(loadAsistencias, 1000);
                
                // Configurar tabs de vista
                setupViewTabs();
            });
            
            function setupViewTabs() {
                document.querySelectorAll('.view-tab').forEach(tab => {
                    tab.addEventListener('click', function() {
                        document.querySelectorAll('.view-tab').forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        currentView = this.dataset.view;
                        if (asistenciasDiarias.length > 0) {
                            updateTable(asistenciasDiarias);
                        }
                    });
                });
            }
            
            function formatTime(dateString) {
                return new Date(dateString).toLocaleTimeString('es-CO', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }

            function isRecent(dateString) {
                const entryTime = new Date(dateString);
                const now = new Date();
                return (now - entryTime) < 60000; // 1 minuto
            }

            function updateCounter(asistencias) {
                if (!asistencias) return;
                
                const usuariosUnicos = new Set(asistencias.map(a => a.user_id)).size;
                const counterElement = document.getElementById('total-count');
                
                if (counterElement) {
                    const currentValue = parseInt(counterElement.textContent) || 0;
                    if (currentValue !== usuariosUnicos) {
                        animateCounter(currentValue, usuariosUnicos, counterElement);
                    }
                }
            }

            function animateCounter(start, end, element) {
                const duration = 1000;
                const steps = 20;
                const increment = (end - start) / steps;
                let current = start;
                const stepTime = duration / steps;

                const timer = setInterval(() => {
                    current += increment;
                    if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                        clearInterval(timer);
                        element.textContent = end;
                    } else {
                        element.textContent = Math.round(current);
                    }
                }, stepTime);
            }
            
            function updateAllCharts(asistencias) {
                // No hay gráficos para actualizar
            }

            function loadAsistencias() {
                fetch('/api/asistencias/diarias')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            asistenciasDiarias = data.data;
                            updateTable(data.data);
                            updateCounter(data.data);
                            updateAllCharts(data.data);
                            
                            const currentTime = new Date().toLocaleTimeString('es-CO', { 
                                hour: '2-digit', 
                                minute: '2-digit',
                                second: '2-digit',
                                hour12: true 
                            });
                            
                            document.getElementById('update-time').textContent = currentTime;
                        } else {
                            console.error('Error en los datos:', data);
                            throw new Error(data.message || 'Error al cargar las asistencias');
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar asistencias:', error);
                        document.getElementById('asistencias-simple').innerHTML = `
                            <div class="error-message" style="padding: 32px 0;">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Error al cargar las asistencias: ${error.message}
                                    </div>
                        `;
                    });
            }

            function updateTable(asistencias) {
                const tableContainer = document.getElementById('asistencias-simple');
                
                if (!asistencias || asistencias.length === 0) {
                    tableContainer.innerHTML = `
                        <div class="empty-message" style="padding: 32px 0;">
                            <i class="fas fa-info-circle"></i>
                            No hay asistencias registradas para el día de hoy
                        </div>
                    `;
                    return;
                }

                // Filtrar las asistencias según la vista seleccionada
                const asistenciasFiltradas = asistencias.filter(asistencia => {
                    if (currentView === 'all') return true;
                    if (currentView === 'entrada') return asistencia.tipo === 'entrada';
                    if (currentView === 'salida') return asistencia.tipo === 'salida';
                    return true;
                });
                
                // Ordenar por fecha más reciente
                const asistenciasOrdenadas = asistenciasFiltradas.sort((a, b) => {
                    return new Date(b.fecha_hora) - new Date(a.fecha_hora);
                });

                tableContainer.innerHTML = '';
                
                // Mostrar cada asistencia individualmente
                asistenciasOrdenadas.forEach(asistencia => {
                    if (!asistencia.user) return;
                    
                    const user = asistencia.user;
                    const element = document.createElement('div');
                    element.className = 'simple-entry';
                    
                    if (isRecent(asistencia.fecha_hora)) {
                        element.classList.add('new-entry');
                    }
                    
                    element.innerHTML = `
                        <div class="entry-icon ${asistencia.tipo}">
                            <i class="fas fa-sign-${asistencia.tipo === 'entrada' ? 'in' : 'out'}-alt"></i>
                        </div>
                        <div class="entry-details">
                            <div class="entry-user-info">
                                <div class="entry-user">${user.nombres_completos || 'N/A'}</div>
                                <div class="entry-program">${user.programa_formacion?.nombre_programa || 'Sin programa'}</div>
                            </div>
                            
                            <div class="entry-ficha">
                                <div class="entry-label">Ficha</div>
                                <div class="entry-value">${user.programa_formacion?.numero_ficha || 'N/A'}</div>
                            </div>
                            
                            <div class="entry-ambiente">
                                <div class="entry-label">Ambiente</div>
                                <div class="entry-value">${user.programa_formacion?.numero_ambiente || 'N/A'}</div>
                            </div>
                            
                            <div class="entry-device">
                                <div class="entry-label">Equipo</div>
                                <div class="entry-value">
                                    ${user.devices && user.devices.length > 0 && user.devices[0].marca 
                                        ? `${user.devices[0].marca} ${user.devices[0].serial || ''}` 
                                        : 'No registrado'}
                                </div>
                            </div>
                            
                            <div class="entry-jornada">
                                <div class="entry-label">Jornada</div>
                                ${user.jornada?.nombre?.toUpperCase() || 'N/A'}
                            </div>
                            
                            <div class="entry-time">
                                ${formatTime(asistencia.fecha_hora)}
                            </div>
                        </div>
                    `;
                    
                    tableContainer.appendChild(element);
                });
            }

            class MessageTicker {
                constructor() {
                    this.wrapper = document.querySelector('.ticker-wrapper');
                    this.progressBar = document.querySelector('.ticker-progress');
                    this.currentMessages = [];
                    this.nextMessages = [];
                    this.currentIndex = 0;
                    this.messageInterval = 5000; // 5 seconds per message
                    this.isAnimating = false;
                    this.lastFetchTime = 0;
                    this.fetchInterval = 30000; // Fetch new messages every 30 seconds
                    
                    this.init();
                }

                async init() {
                    await this.fetchMessages();
                    this.startTicker();
                    this.setupPolling();
                }

                async fetchMessages() {
                    try {
                        const response = await fetch('/api/ticker-messages');
                        const data = await response.json();
                        const messages = data.messages || [];
                        
                        // If this is our first fetch, set both current and next
                        if (this.currentMessages.length === 0) {
                            this.currentMessages = messages.slice(0, 10);
                            this.nextMessages = messages.slice(10, 20);
                        } else {
                            // Otherwise, update the next batch
                            this.nextMessages = messages.slice(0, 10);
                        }
                        
                        this.lastFetchTime = Date.now();
                    } catch (error) {
                        console.error('Error fetching ticker messages:', error);
                        // Use fallback messages if fetch fails
                        this.currentMessages = ["⚠️ Actualizando información..."];
                    }
                }

                setupPolling() {
                    setInterval(async () => {
                        if (Date.now() - this.lastFetchTime >= this.fetchInterval) {
                            await this.fetchMessages();
                        }
                    }, 1000);
                }

                startTicker() {
                    if (this.currentMessages.length === 0) return;
                    
                    this.showNextMessage();
                }

                showNextMessage() {
                    if (this.isAnimating) return;
                    this.isAnimating = true;

                    // Create and prepare the message element
                    const messageEl = document.createElement('div');
                    messageEl.className = 'ticker-message';
                    messageEl.textContent = this.currentMessages[this.currentIndex];
                    this.wrapper.appendChild(messageEl);

                    // Reset progress bar
                    gsap.set(this.progressBar, { width: 0 });

                    // Animate message in
                    const timeline = gsap.timeline({
                        onComplete: () => {
                            this.isAnimating = false;
                            this.advanceToNext();
                        }
                    });

                    timeline
                        .fromTo(messageEl, 
                            { x: '100%', opacity: 0 },
                            { x: '0%', opacity: 1, duration: 0.5, ease: 'power2.out' }
                        )
                        .to(this.progressBar, 
                            { width: '100%', duration: this.messageInterval / 1000, ease: 'none' },
                            '-=0.5'
                        )
                        .to(messageEl, 
                            { x: '-100%', opacity: 0, duration: 0.5, ease: 'power2.in' },
                            `+=${this.messageInterval / 1000 - 1}`
                        )
                        .add(() => {
                            messageEl.remove();
                        });
                }

                advanceToNext() {
                    this.currentIndex++;
                    
                    // If we've shown all current messages, switch to next batch
                    if (this.currentIndex >= this.currentMessages.length) {
                        this.currentIndex = 0;
                        if (this.nextMessages.length > 0) {
                            this.currentMessages = [...this.nextMessages];
                            this.nextMessages = [];
                        }
                    }
                    
                    this.showNextMessage();
                }
            }

            // Initialize the ticker when the document is ready
            document.addEventListener('DOMContentLoaded', () => {
                new MessageTicker();
            });
        </script>
    </body>
</html>