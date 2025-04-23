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
    </head>
    <body>
        <div class="top-bar">
            <div class="logo">
                <img src="{{ asset('img/logo/logo.png') }}" alt="ERSENA Logo">
            </div>
            <div id="anuncio-container">
                <div class="anuncio visible" data-type="bienvenida">
                    ¡Bienvenidos al Sistema de Control de Asistencia! 👋
                </div>
            </div>
            <a href="{{ route('login') }}" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                <span>Iniciar Sesión</span>
            </a>
        </div>

        <div class="main-content">
            <div class="container">
                <div class="header">
                    <h1>Control de Asistencias en Tiempo Real</h1>
                    <div>Última actualización: <span id="update-time"></span></div>
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
            // Inicializar cuando se carga el documento
            // Script optimizado para el contenedor de anuncios
document.addEventListener('DOMContentLoaded', function() {
    // Mensajes de ejemplo - en producción se cargarían de la API
    let mensajes = [
        "¡Bienvenidos al Sistema de Control de Asistencia! 👋",
        "Juan Pérez llegó puntual hoy! 🌟",
        "15 aprendices han registrado asistencia esta mañana 📊",
        "El programa de Desarrollo Web tiene la mejor asistencia 🏆"
    ];
    
    let index = 0;
    let isTransitioning = false;
    const contenedor = document.getElementById('anuncio-container');
    
    // Función para actualizar mensajes (simula llamada a API)
    async function actualizarMensajes() {
        try {
            //En un entorno real, descomentar esta parte:
            const res = await fetch('/api/ticker-messages');
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.messages)) {
                if (data.messages.length > 0) {
                    mensajes = data.messages;
                }
            }
            
            // Para pruebas, usamos los mensajes de ejemplo
            console.log('Mensajes cargados:', mensajes.length);
        } catch (error) {
            console.error('Error al cargar mensajes:', error);
        }
    }

    // Función para mostrar el siguiente mensaje con animación suave
    function mostrarMensaje() {
        if (isTransitioning || mensajes.length === 0) return;
        
        isTransitioning = true;
        const anuncioActual = contenedor.querySelector('.anuncio.visible');
        
        if (anuncioActual) {
            // Ocultar mensaje actual con animación
            anuncioActual.classList.remove('visible');
            setTimeout(() => anuncioActual.remove(), 400);
        }

        setTimeout(() => {
            const mensaje = mensajes[index];
            
            // Determinar el tipo de mensaje para aplicar estilos diferentes
            const tipo = mensaje.includes('¡Bienvenidos') ? 'bienvenida'
                      : mensaje.includes('llegó') || mensaje.includes('retirado') ? 'asistencia'
                      : mensaje.includes('aprendices') ? 'estadistica'
                      : 'programa';

            // Crear nuevo elemento con animación para emojis
            const div = document.createElement('div');
            div.className = 'anuncio';
            div.setAttribute('data-type', tipo);
            
            // Convertir emojis a spans con clase para animarlos
            div.innerHTML = mensaje.replace(/([\u{1F300}-\u{1F9FF}]|[\u{2600}-\u{26FF}])/gu, '<span class="emoji">$1</span>');
            
            // Agregar al contenedor
            contenedor.appendChild(div);
            
            // Forzar reflow antes de añadir la clase visible
            void div.offsetWidth;
            
            // Mostrar con animación
            requestAnimationFrame(() => div.classList.add('visible'));
            
            // Avanzar al siguiente mensaje
            index = (index + 1) % mensajes.length;
            
            // Permitir la siguiente transición después de completar la actual
            setTimeout(() => isTransitioning = false, 400);
        }, anuncioActual ? 400 : 0);
    }

    // Inicializar
    actualizarMensajes().then(() => {
        if (mensajes.length > 0) {
            mostrarMensaje(); // Mostrar primer mensaje
        }
        
        // Mostrar siguiente mensaje cada 8 segundos
        setInterval(() => {
            if (!isTransitioning && mensajes.length > 0) {
                mostrarMensaje();
            }
        }, 8000);
        
        // Actualizar mensajes del servidor cada 5 minutos
        setInterval(actualizarMensajes, 300000);
    });
    
    // También inicializamos el contador de tiempo de actualización
    setInterval(() => {
        const tiempoElement = document.getElementById('update-time');
        if (tiempoElement) {
            tiempoElement.textContent = new Date().toLocaleTimeString();
        }
    }, 1000);
});

            function loadAsistencias() {
                fetch('/api/asistencias/diarias')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            updateTable(data.data);
                            updateCounter(data.data);
                            document.getElementById('update-time').textContent = new Date().toLocaleTimeString();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('asistencias-body').innerHTML = `
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px;">
                                    Error al cargar las asistencias. Por favor, intente nuevamente.
                                </td>
                            </tr>
                        `;
                    });
            }

            function updateTable(asistencias) {
                const tableBody = document.getElementById('asistencias-body');
                updateRanking(asistencias);
                
                if (!asistencias || asistencias.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px;">
                                No hay asistencias registradas para el día de hoy.
                            </td>
                        </tr>
                    `;
                    return;
                }

                const asistenciasPorUsuario = asistencias.reduce((acc, asistencia) => {
                    const userId = asistencia.user?.id;
                    if (!acc[userId]) {
                        acc[userId] = {
                            user: asistencia.user,
                            entrada: null,
                            salida: null
                        };
                    }
                    if (asistencia.tipo === 'entrada') {
                        acc[userId].entrada = asistencia;
                    } else if (asistencia.tipo === 'salida') {
                        acc[userId].salida = asistencia;
                    }
                    return acc;
                }, {});

                tableBody.innerHTML = '';
                Object.values(asistenciasPorUsuario).forEach(registro => {
                    const user = registro.user || {};
                    const programa = user.programa_formacion || {};
                    const jornada = user.jornada || {};
                    
                    const horaEntrada = registro.entrada ? new Date(registro.entrada.fecha_hora).toLocaleTimeString() : '---';
                    const horaSalida = registro.salida ? new Date(registro.salida.fecha_hora).toLocaleTimeString() : '---';

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <div class="user-info">
                                <div class="user-name">${user.nombres_completos || 'N/A'}</div>
                                <div class="user-doc">${user.documento_identidad || 'N/A'}</div>
                            </div>
                        </td>
                        <td>
                            <div class="program-info">
                                <div class="program-name">${programa.nombre_programa || 'N/A'}</div>
                                <div class="program-details">
                                    Ficha: ${programa.numero_ficha || 'N/A'} | Ambiente: ${programa.numero_ambiente || 'N/A'}
                                </div>
                            </div>
                        </td>
                        <td>${jornada.nombre || 'N/A'}</td>
                        <td>
                            <div class="time-info">
                                <div class="registro-tiempo">
                                    <span class="badge badge-entrada">Entrada: ${horaEntrada}</span>
                                    <span class="badge badge-salida">Salida: ${horaSalida}</span>
                                </div>
                            </div>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            }

            function updateCounter(asistencias) {
                const usuariosUnicos = new Set(asistencias.map(a => a.user?.id)).size;
                document.getElementById('total-count').textContent = usuariosUnicos;
            }

            function updateRanking(asistencias) {
                const rankingList = document.getElementById('ranking-list');
                
                const entradasPorUsuario = asistencias
                    .filter(a => a.tipo === 'entrada')
                    .reduce((acc, asistencia) => {
                        const userId = asistencia.user?.id;
                        if (!acc[userId] && asistencia.user) {
                            const horaEntrada = new Date(asistencia.fecha_hora);
                            const jornada = asistencia.user.jornada;
                            const horaJornada = jornada?.hora_entrada ? new Date(`2000-01-01T${jornada.hora_entrada}`) : null;
                            
                            let diferencia = 0;
                            if (horaJornada) {
                                const entradaMinutos = horaEntrada.getHours() * 60 + horaEntrada.getMinutes();
                                const jornadaMinutos = horaJornada.getHours() * 60 + horaJornada.getMinutes();
                                diferencia = entradaMinutos - jornadaMinutos;
                            }

                            acc[userId] = {
                                user: asistencia.user,
                                horaEntrada: horaEntrada,
                                diferencia: diferencia
                            };
                        }
                        return acc;
                    }, {});

                const ranking = Object.values(entradasPorUsuario)
                    .sort((a, b) => a.diferencia - b.diferencia)
                    .slice(0, 5);

                rankingList.innerHTML = ranking.map((item, index) => {
                    const positionClass = index < 3 ? 
                        `ranking-position-${index + 1}` : 
                        'ranking-position-other';
                    
                    return `
                        <li class="ranking-item">
                            <div class="ranking-position ${positionClass}">${index + 1}</div>
                            <div class="ranking-info">
                                <div class="ranking-name">${item.user.nombres_completos}</div>
                                <div class="ranking-details">
                                    ${item.user.jornada?.nombre || 'Sin jornada'} - 
                                    ${item.user.programa_formacion?.nombre_programa || 'Sin programa'}
                                </div>
                            </div>
                            <div class="ranking-time">
                                ${item.horaEntrada.toLocaleTimeString()}
                            </div>
                        </li>
                    `;
                }).join('');
            }
        </script>
    </body>
</html>