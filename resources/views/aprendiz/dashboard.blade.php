<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERSENA - Panel del Aprendiz</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ asset('img/icon/logoSena.png') }}" type="image/png">
</head>
<body>
    <!-- Botón de menú móvil -->
    <button class="mobile-menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <!-- Sidebar mejorado -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="{{ asset('img/logo/logoSena.png') }}" alt="ERSENA Logo">
                    <span class="logo-text">ERSENA</span>
                </div>
            </div>

            <div class="user-profile">
                <div class="profile-image-container">
                    <div class="profile-image" onclick="document.getElementById('foto_perfil').click()">
                        <img src="{{ $user->profile_photo ? asset($user->profile_photo) : asset('img/default/default.png') }}" 
                             alt="Foto de perfil" 
                             id="profileImage">
                    </div>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display: none;">
                </div>
                <div class="user-info">
                    <h3>{{ explode(' ', $user->nombres_completos)[0] }}</h3>
                    <p>Aprendiz SENA</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="#dashboard" class="nav-item active" data-section="dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#registros" class="nav-item" data-section="registros">
                    <i class="fas fa-history"></i>
                    <span>Registros</span>
                </a>
                <a href="#equipo" class="nav-item" data-section="equipo">
                    <i class="fas fa-laptop"></i>
                    <span>Equipo</span>
                </a>
                <a href="#qr" class="nav-item" data-section="qr">
                    <i class="fas fa-qrcode"></i>
                    <span>QR</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Contenido principal optimizado -->
        <main class="main-content">
            <!-- Dashboard Section -->
            <section id="dashboard" class="content-section active">
                <div class="section-header">
                    <h1>Bienvenido, {{ explode(' ', $user->nombres_completos)[0] }}</h1>
                    <p class="text-muted">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}</p>
                    @if($programa && $programa->jornada)
                        <div class="schedule-info">
                            <i class="fas fa-clock"></i>
                            Entrada: {{ \Carbon\Carbon::parse($programa->jornada->hora_entrada)->format('h:i A') }}
                            <span class="tolerance-badge" title="Tolerancia">
                                <i class="fas fa-hourglass-half"></i>
                                {{ \Carbon\Carbon::parse($programa->jornada->tolerancia)->format('i') }} min
                            </span>
                        </div>
                    @endif
                </div>

                <div class="dashboard-grid">
                    <!-- Estado Actual con Tiempo -->
                    <div class="dashboard-card status-card {{ $estadoActual === 'dentro' ? 'success' : 'neutral' }}">
                        <div class="card-header">
                            <div class="card-icon pulse">
                                <i class="fas fa-{{ $estadoActual === 'dentro' ? 'user-check' : 'user' }}"></i>
                            </div>
                            <h3>Estado Actual</h3>
                        </div>
                        <div class="status-content">
                            <div class="status-badge {{ $estadoActual === 'dentro' ? 'success' : 'neutral' }}">
                                <i class="fas fa-{{ $estadoActual === 'dentro' ? 'check-circle' : 'clock' }}"></i>
                                {{ $estadoActual === 'dentro' ? 'En el SENA' : 'Fuera del SENA' }}
                            </div>
                            @if($ultimoRegistro)
                                <div class="time-info">
                                    <span class="time-label">Desde:</span>
                                    <span class="time-value">{{ $ultimoRegistro->fecha_hora->format('h:i A') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Estadísticas de Asistencia -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3>Estadísticas</h3>
                        </div>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-value">{{ $registrosRecientes->where('tipo', 'entrada')->count() }}</div>
                                <div class="stat-label">Asistencias</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">
                                    @php
                                        $puntualidad = $registrosRecientes->where('tipo', 'entrada')->count() > 0 
                                            ? round(($registrosRecientes->where('tipo', 'entrada')->filter(function($registro) use ($programa) {
                                                if (!$programa || !$programa->jornada) return false;
                                                $horaEntrada = \Carbon\Carbon::parse($programa->jornada->hora_entrada);
                                                $tolerancia = \Carbon\Carbon::parse($programa->jornada->tolerancia);
                                                $limiteEntrada = $horaEntrada->copy()->addMinutes($tolerancia->minute);
                                                return $registro->fecha_hora->format('H:i') <= $limiteEntrada->format('H:i');
                                            })->count() / $registrosRecientes->where('tipo', 'entrada')->count()) * 100)
                                            : 0;
                                    @endphp
                                    {{ $puntualidad }}%
                                </div>
                                <div class="stat-label">Puntualidad</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $registrosRecientes->count() }}</div>
                                <div class="stat-label">Total Registros</div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Programa Mejorada -->
                    <div class="dashboard-card program-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3>Mi Programa</h3>
                        </div>
                        @if($programa)
                            <div class="program-info">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label"><i class="fas fa-book"></i> Programa</div>
                                        <div class="info-value">{{ $programa->nombre_programa }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label"><i class="fas fa-hashtag"></i> Ficha</div>
                                        <div class="info-value">{{ $programa->numero_ficha }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label"><i class="fas fa-door-open"></i> Ambiente</div>
                                        <div class="info-value">{{ $programa->numero_ambiente }}</div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-label"><i class="fas fa-layer-group"></i> Nivel</div>
                                        <div class="info-value text-capitalize">{{ $programa->nivel_formacion }}</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <p>Sin información del programa</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Registros Recientes con Mejoras -->
                <div class="records-container">
                    <div class="records-header">
                        <h2><i class="fas fa-history"></i> Registros Recientes</h2>
                        <div class="records-summary">
                            <span class="summary-item">
                                <i class="fas fa-calendar-check"></i>
                                Últimos 30 días
                            </span>
                        </div>
                    </div>
                    <div class="records-list">
                        @forelse($registrosRecientes as $registro)
                            <div class="record-item {{ $registro->tipo }}">
                                <div class="record-icon">
                                    <i class="fas fa-sign-{{ $registro->tipo === 'entrada' ? 'in' : 'out' }}-alt"></i>
                                </div>
                                <div class="record-info">
                                    <div class="record-title">
                                        {{ $registro->tipo === 'entrada' ? 'Entrada' : 'Salida' }}
                                        @php
                                            $esPuntual = false;
                                            if($registro->tipo === 'entrada' && $programa && $programa->jornada) {
                                                $horaEntrada = \Carbon\Carbon::parse($programa->jornada->hora_entrada);
                                                $tolerancia = \Carbon\Carbon::parse($programa->jornada->tolerancia);
                                                $limiteEntrada = $horaEntrada->copy()->addMinutes($tolerancia->minute);
                                                $esPuntual = $registro->fecha_hora->format('H:i') <= $limiteEntrada->format('H:i');
                                            }
                                        @endphp
                                        @if($registro->tipo === 'entrada')
                                            <span class="punctuality-badge {{ $esPuntual ? 'on-time' : 'late' }}" 
                                                  title="{{ $esPuntual ? 'Llegada puntual' : 'Llegada tarde' }}">
                                                <i class="fas fa-{{ $esPuntual ? 'check' : 'exclamation' }}"></i>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="record-subtitle">
                                        <span class="date">{{ $registro->fecha_hora->format('d/m/Y') }}</span>
                                        <span class="time">{{ $registro->fecha_hora->format('h:i A') }}</span>
                                    </div>
                                </div>
                                <div class="status-badge {{ $registro->tipo === 'entrada' ? 'success' : 'danger' }}">
                                    {{ $registro->tipo === 'entrada' ? 'Entrada' : 'Salida' }}
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <p>No hay registros recientes</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <!-- Registros Section -->
            <section id="registros" class="content-section">
                <div class="section-header">
                    <h1>Mis Registros</h1>
                    <div class="date-filters">
                        <input type="date" id="fecha_inicio" class="filter-input" value="{{ now()->subMonth()->format('Y-m-d') }}">
                        <input type="date" id="fecha_fin" class="filter-input" value="{{ now()->format('Y-m-d') }}">
                        <button onclick="filtrarRegistros()" class="btn btn-primary">
                            <i class="fas fa-filter"></i>
                            Filtrar
                        </button>
                    </div>
                </div>

                <div class="records-container">
                    <div class="records-list" id="lista-registros">
                        @foreach($registros as $registro)
                            <div class="record-item">
                                <div class="record-icon">
                                    <i class="fas fa-sign-{{ $registro->tipo === 'entrada' ? 'in' : 'out' }}-alt"></i>
                                </div>
                                <div class="record-info">
                                    <div class="record-title">{{ $registro->tipo === 'entrada' ? 'Entrada' : 'Salida' }}</div>
                                    <div class="record-subtitle">{{ $registro->fecha_hora->format('d/m/Y h:i A') }}</div>
                                </div>
                                <div class="status-badge {{ $registro->tipo === 'entrada' ? 'success' : 'danger' }}">
                                    {{ $registro->tipo === 'entrada' ? 'Entrada' : 'Salida' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- Equipo Section -->
            <section id="equipo" class="content-section">
                <div class="section-header">
                    <h1>Mi Equipo</h1>
                </div>

                <div class="dashboard-grid">
                    @forelse($devices as $device)
                        <div class="dashboard-card device-card">
                            <div class="card-header">
                                <div class="card-icon">
                                    <i class="fas fa-laptop"></i>
                                </div>
                                <h3>{{ $device->marca }}</h3>
                            </div>
                            <div class="device-info">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i class="fas fa-barcode"></i>
                                            Serial
                                        </div>
                                        <div class="info-value">{{ $device->serial }}</div>
                                    </div>
                                </div>
                                <div class="device-image-container">
                                    <div class="device-image">
                                        <img src="{{ $device->foto_serial }}" 
                                             alt="Foto Serial {{ $device->marca }}"
                                             loading="lazy"
                                             onclick="mostrarImagenAmpliada(this.src)">
                                    </div>
                                    <div class="image-hint">
                                        <i class="fas fa-search-plus"></i>
                                        Click para ampliar
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-card">
                            <div class="empty-state">
                                <i class="fas fa-laptop-code"></i>
                                <p>No tienes equipos registrados</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Modal para imagen ampliada -->
                <div id="modalImagen" class="modal" onclick="this.style.display='none'">
                    <span class="modal-close">&times;</span>
                    <img class="modal-content" id="imagenAmpliada">
                </div>
            </section>

            <!-- QR Section -->
            <section id="qr" class="content-section">
                <div class="section-header">
                    <h1>Mi Código QR</h1>
                </div>

                <div class="dashboard-card">
                    <div class="qr-container">
                        <div class="qr-image">
                            <canvas id="qrCanvas"></canvas>
                        </div>
                        <button onclick="descargarQR()" class="btn btn-primary">
                            <i class="fas fa-download"></i>
                            Descargar QR
                        </button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Agregar QR.js -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Generar QR
        const qrCode = '{{ $user->qr_code }}';
        const canvas = document.getElementById('qrCanvas');
        
        QRCode.toCanvas(canvas, qrCode, {
            width: 300,
            height: 300,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#ffffff'
            }
        }, function (error) {
            if (error) console.error(error);
        });

        // Función para descargar QR
        window.descargarQR = function() {
            const canvas = document.getElementById('qrCanvas');
            const link = document.createElement('a');
            link.download = 'mi_codigo_qr.png';
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };

        // Manejo del menú móvil
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        });

        // Cerrar menú al hacer clic fuera
        mainContent.addEventListener('click', function() {
            if (sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Navegación
        const navItems = document.querySelectorAll('.nav-item[data-section]');
        const sections = document.querySelectorAll('.content-section');

        function cambiarSeccion(seccionId) {
            sections.forEach(section => {
                section.classList.remove('active');
                if (section.id === seccionId) {
                    section.classList.add('active');
                }
            });

            navItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('data-section') === seccionId) {
                    item.classList.add('active');
                }
            });

            // Cerrar menú móvil al cambiar de sección
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        navItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const seccionId = this.getAttribute('data-section');
                cambiarSeccion(seccionId);
                history.pushState(null, '', `#${seccionId}`);
            });
        });

        // Manejo de la navegación por hash
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.substring(1);
            if (hash) cambiarSeccion(hash);
        });

        // Cargar sección inicial
        const hashInicial = window.location.hash.substring(1);
        if (hashInicial) cambiarSeccion(hashInicial);

        // Manejo de la foto de perfil
        const inputFotoPerfil = document.getElementById('foto_perfil');
        if (inputFotoPerfil) {
            inputFotoPerfil.addEventListener('change', async function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Actualizar preview instantáneamente
                const previewUrl = URL.createObjectURL(file);
                document.getElementById('profileImage').src = previewUrl;

                // Enviar al servidor como FormData
                const formData = new FormData();
                formData.append('foto_perfil', file);

                try {
                    const response = await fetch('{{ route("aprendiz.actualizar-foto") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Error al actualizar la foto');
                    }
                    
                    if (result.new_photo_url) {
                        document.getElementById('profileImage').src = result.new_photo_url;
                    } else {
                        throw new Error('No se recibió la URL de la nueva foto');
                    }

                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Error al actualizar la foto. Intenta de nuevo.');
                    // Revertir la imagen de vista previa si falla la carga
                    document.getElementById('profileImage').src = '{{ $user->profile_photo ? asset($user->profile_photo) : asset('img/default/default.png') }}';
                } finally {
                    // Revocar el Object URL para liberar memoria
                    URL.revokeObjectURL(previewUrl);
                }
            });
        }
    });

    // Filtrado de registros
    async function filtrarRegistros() {
        const fechaInicio = document.getElementById('fecha_inicio').value;
        const fechaFin = document.getElementById('fecha_fin').value;

        try {
            const response = await fetch('{{ route("aprendiz.filtrar-registros") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ fecha_inicio: fechaInicio, fecha_fin: fechaFin })
            });

            const data = await response.json();
            if (data.success) {
                const listaRegistros = document.getElementById('lista-registros');
                listaRegistros.innerHTML = data.registros.map(registro => `
                    <div class="record-item">
                        <div class="record-icon">
                            <i class="fas fa-sign-${registro.tipo === 'entrada' ? 'in' : 'out'}-alt"></i>
                        </div>
                        <div class="record-info">
                            <div class="record-title">${registro.tipo === 'entrada' ? 'Entrada' : 'Salida'}</div>
                            <div class="record-subtitle">${new Date(registro.fecha_hora).toLocaleDateString('es-ES', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            })}</div>
                        </div>
                        <div class="status-badge ${registro.tipo === 'entrada' ? 'success' : 'danger'}">
                            ${registro.tipo === 'entrada' ? 'Entrada' : 'Salida'}
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al filtrar los registros');
        }
    }

    function mostrarImagenAmpliada(src) {
        var modal = document.getElementById("modalImagen");
        var img = document.getElementById("imagenAmpliada");
        img.src = src;
        modal.style.display = "block";
    }
    </script>
</body>
</html>