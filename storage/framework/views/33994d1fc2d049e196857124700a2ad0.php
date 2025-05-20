<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo e(config('app.name', 'ERSENA')); ?></title>
        <link rel="stylesheet" href="<?php echo e(asset('css/common.css')); ?>">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="<?php echo e(asset('css/welcome.css')); ?>">
        <!-- icono -->
        <link rel="icon" href="<?php echo e(asset('img/icon/icono.ico')); ?>" type="image/x-ico">
        <!-- GSAP for smooth animations -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
        <style>
            /* #anuncio-container {
                position: relative;
                flex: 1;
                height: 60px;
                overflow: hidden;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                margin: 0 20px;
            }

            .ticker-wrapper {
                position: absolute;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
            }

            .ticker-message {
                position: absolute;
                left: 0;
                white-space: nowrap;
                padding: 0 20px;
                opacity: 0;
                transform: translateX(100%);
                color: #fff;
                font-size: 1.1em;
                font-weight: 500;
                text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            } */

            .ticker-message.active {
                opacity: 1;
            }

            .ticker-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 2px;
                background: linear-gradient(90deg, #4CAF50, #8BC34A);
                width: 0%;
            }
        </style>
    </head>
    <body>
        <div class="top-bar">
            <div class="logo">
                <img src="<?php echo e(asset('img/logo/logo.webp')); ?>" alt="ERSENA Logo">
            </div>
            <div id="anuncio-container">
                <div class="ticker-wrapper">
                    <!-- Messages will be inserted here dynamically -->
                </div>
                <div class="ticker-progress"></div>
            </div>
            <a href="<?php echo e(route('login')); ?>">
                <button class="btn-login">Iniciar Sesión</button>
            </a>
        </div>

        <div class="main-content">
            <div class="container">
                <div class="header">
                    <h1>SENA Regional Caquetá</h1>
                    <h2>Control de entradas de aprendices</h2>
                    <div class="update-time-container">
                        <i class="fas fa-clock"></i>
                        <span id="update-time"></span>
                    </div>
                </div>

                <div class="sidebar">
                    <div class="counter-box">
                        <div class="counter-label">Total Asistencias</div>
                        <div class="counter-value" id="total-count">0</div>
                    </div>

                    <div class="ranking-box">
                        <div class="ranking-title">Top 5 - Puntualidad</div>
                        <ul class="ranking-list" id="ranking-list">
                            <!-- El ranking se cargará dinámicamente -->
                        </ul>
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Aprendiz</th>
                                <th>Programa</th>
                                <th>Jornada</th>
                                <th>Registro</th>
                            </tr>
                        </thead>
                        <tbody id="asistencias-body">
                            <!-- Los datos serán cargados dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            let asistenciasInterval;

            // Inicializar cuando se carga el documento
            document.addEventListener('DOMContentLoaded', function() {
                // Cargar asistencias inmediatamente
                loadAsistencias();
                
                // Configurar actualización automática cada 1 segundo
                asistenciasInterval = setInterval(loadAsistencias, 1000);
            });

            function loadAsistencias() {
                fetch('/api/asistencias/diarias')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Datos recibidos:', data); // Debug
                        if (data.status === 'success') {
                            updateTable(data.data);
                            updateCounter(data.data);
                            document.getElementById('update-time').textContent = 
                                new Date().toLocaleTimeString('es-CO', { 
                                    hour: '2-digit', 
                                    minute: '2-digit',
                                    second: '2-digit',
                                    hour12: true 
                                });
                        } else {
                            console.error('Error en los datos:', data);
                            throw new Error(data.message || 'Error al cargar las asistencias');
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar asistencias:', error);
                        document.getElementById('asistencias-body').innerHTML = `
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="error-message">
                                        <i class="fas fa-exclamation-circle"></i>
                                        Error al cargar las asistencias: ${error.message}
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
            }

            function updateTable(asistencias) {
                const tableBody = document.getElementById('asistencias-body');
                
                if (!asistencias || asistencias.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="empty-message">
                                    <i class="fas fa-info-circle"></i>
                                    No hay asistencias registradas para el día de hoy
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }

                // Agrupar asistencias por usuario
                const asistenciasPorUsuario = {};
                asistencias.forEach(asistencia => {
                    if (!asistenciasPorUsuario[asistencia.user_id]) {
                        asistenciasPorUsuario[asistencia.user_id] = {
                            user: asistencia.user,
                            entrada: null,
                            salida: null
                        };
                    }
                    if (asistencia.tipo === 'entrada') {
                        asistenciasPorUsuario[asistencia.user_id].entrada = asistencia;
                    } else if (asistencia.tipo === 'salida') {
                        asistenciasPorUsuario[asistencia.user_id].salida = asistencia;
                    }
                });

                // Convertir a array y ordenar por hora de entrada más reciente
                const registrosOrdenados = Object.values(asistenciasPorUsuario)
                    .sort((a, b) => {
                        const fechaA = a.entrada ? new Date(a.entrada.fecha_hora) : new Date(0);
                        const fechaB = b.entrada ? new Date(b.entrada.fecha_hora) : new Date(0);
                        return fechaB - fechaA;
                    });

                tableBody.innerHTML = '';
                registrosOrdenados.forEach(registro => {
                    const user = registro.user;
                    if (!user) return;

                    const horaEntrada = registro.entrada ? formatTime(registro.entrada.fecha_hora) : '---';
                    const horaSalida = registro.salida ? formatTime(registro.salida.fecha_hora) : '---';
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                            <td>
                                <div class="user-info">
                                    <div class="user-name">${user.nombres_completos || 'N/A'}</div>
                                    <div class="user-details">
                                        <div class="user-doc">Doc: ${user.documento_identidad || 'N/A'}</div>
                                        ${user.devices && user.devices.length > 0 ? `
                                            <div class="device-info">
                                                <i class="fas fa-laptop"></i>
                                                ${user.devices[0].marca} - ${user.devices[0].serial}
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="program-info">
                                    <div class="program-name">${user.programa_formacion?.nombre_programa || 'N/A'}</div>
                                    <div class="program-details">
                                        <div class="program-nivel">
                                            <i class="fas fa-graduation-cap"></i>
                                            ${user.programa_formacion?.nivel_formacion?.toUpperCase() || 'N/A'}
                                        </div>
                                        <div>Ficha: ${user.programa_formacion?.numero_ficha || 'N/A'}</div>
                                        <div>Ambiente: ${user.programa_formacion?.numero_ambiente || 'N/A'}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="jornada-info">
                                    <span class="badge badge-jornada">
                                        <i class="fas fa-clock"></i>
                                        ${user.jornada?.nombre?.toUpperCase() || 'N/A'}
                                    </span>
                                    <div class="jornada-details">
                                        <div>Entrada: ${user.jornada?.hora_entrada || 'N/A'}</div>
                                        <div>Tolerancia: ${user.jornada?.tolerancia || '5 min'}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="time-info">
                                    <div class="registro-tiempo ${registro.entrada ? 'presente' : ''}">
                                        <span class="badge badge-entrada">
                                            <i class="fas fa-sign-in-alt"></i>
                                            ${horaEntrada}
                                        </span>
                                    </div>
                                    <div class="registro-tiempo ${registro.salida ? 'presente' : ''}">
                                        <span class="badge badge-salida">
                                            <i class="fas fa-sign-out-alt"></i>
                                            ${horaSalida}
                                        </span>
                                    </div>
                                </div>
                            </td>`;

                    // Efecto de nueva entrada
                    if (registro.entrada && isRecent(registro.entrada.fecha_hora)) {
                        row.classList.add('new-entry');
                        setTimeout(() => row.classList.remove('new-entry'), 5000);
                    }

                    tableBody.appendChild(row);
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
</html><?php /**PATH C:\laragon\www\ersena\resources\views/welcome.blade.php ENDPATH**/ ?>