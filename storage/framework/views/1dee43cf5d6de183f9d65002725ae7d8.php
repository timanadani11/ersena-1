<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escaneo SENA</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'public/css/common.css', 'public/css/admin.css']); ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&family=Poppins&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="<?php echo e(asset('img/icon/icono.ico')); ?>" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8"></script>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="<?php echo e(asset('img/logo/logo.webp')); ?>" alt="Logo SENA">
        </div>
        <div class="header-title">
            <h1>Escáner QR</h1>
        </div>
        <form action="<?php echo e(route('logout')); ?>" method="POST" style="margin: 0;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    </div>

    <div class="container">
        <!-- Scanner QR -->
        <div class="card scanner-card">
            <h2><i class="fas fa-qrcode"></i> Escanear código QR</h2>
            <div id="reader"></div>
            <div id="scan-status" class="scan-status">Esperando código QR...</div>
        </div>

        <!-- Búsqueda manual -->
        <div class="card search-card">
            <h2><i class="fas fa-search"></i> Buscar por documento</h2>
            <div class="search-box">
                <input type="text" id="documento" class="form-control" placeholder="Número de documento" inputmode="numeric" pattern="[0-9]*">
                <button onclick="buscarAprendiz()" class="btn" id="btn-buscar">
                    <span class="loader"></span>
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Información del aprendiz -->
        <div id="aprendiz-info" class="card">
            <h2><i class="fas fa-user"></i> Información del Aprendiz</h2>
            <div class="info-item">
                <span class="info-label">Nombre:</span>
                <span id="nombre-aprendiz" class="info-value"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Documento:</span>
                <span id="documento-aprendiz" class="info-value"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Programa:</span>
                <span id="programa-aprendiz" class="info-value"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Nivel:</span>
                <span id="nivel-formacion" class="info-value"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Ficha:</span>
                <span id="ficha-aprendiz" class="info-value"></span>
            </div>
            <div class="info-item">
                <span class="info-label">Jornada:</span>
                <span id="jornada-aprendiz" class="info-value"></span>
            </div>
            
            <div class="btn-group">
                <button id="btn-entrada" onclick="registrarAsistencia('entrada')" class="btn btn-entrada">
                    <span class="loader"></span>
                    <i class="fas fa-sign-in-alt"></i> Registrar Entrada
                </button>
                <button id="btn-salida" onclick="registrarAsistencia('salida')" class="btn btn-salida">
                    <span class="loader"></span>
                    <i class="fas fa-sign-out-alt"></i> Registrar Salida
                </button>
            </div>
        </div>
    </div>

    <!-- Notification toast -->
    <div id="notification"></div>

    <!-- Audio elements -->
    <audio id="sound-entrada" src="<?php echo e(asset('sounds/entrada.mp3')); ?>" preload="auto"></audio>
    <audio id="sound-salida" src="<?php echo e(asset('sounds/salida.mp3')); ?>" preload="auto"></audio>
    <audio id="sound-error" src="<?php echo e(asset('sounds/error.mp3')); ?>" preload="auto"></audio>
    <audio id="sound-scan" src="<?php echo e(asset('sounds/scan.mp3')); ?>" preload="auto"></audio>

    <script>
        // Variables globales
        let lastScanned = null;
        let scanCooldown = false;
        let scanActive = true;
        const COOLDOWN_TIME = 5000; // 5 segundos de espera entre escaneos del mismo QR

        // Configuración del escáner QR
        const html5QrCode = new Html5Qrcode("reader");
        const qrConfig = {
    fps: 10,
    qrbox: {
        width: 250,
        height: 250
    },
    aspectRatio: 1.0,
    disableFlip: false,
    formatsToSupport: [
        // Formatos de código QR
        Html5QrcodeSupportedFormats.QR_CODE,
        
        // Formatos de códigos de barras lineales
        Html5QrcodeSupportedFormats.EAN_13,
        Html5QrcodeSupportedFormats.EAN_8,
        Html5QrcodeSupportedFormats.CODE_39,
        Html5QrcodeSupportedFormats.CODE_93,
        Html5QrcodeSupportedFormats.CODE_128,
        Html5QrcodeSupportedFormats.ITF,
        Html5QrcodeSupportedFormats.UPC_A,
        Html5QrcodeSupportedFormats.UPC_E,
        Html5QrcodeSupportedFormats.CODABAR
    ],
    rememberLastUsedCamera: true,
    showTorchButtonIfSupported: true,
    showZoomSliderIfSupported: true
};

        function iniciarEscanerQR() {
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    // Intenta usar la cámara trasera primero
                    const camaraTrasera = devices.find(device => /(back|rear)/i.test(device.label));
                    const camaraId = camaraTrasera ? camaraTrasera.id : devices[0].id;

                    html5QrCode.start(
                        camaraId, 
                        qrConfig,
                        onScanSuccess,
                        (errorMessage) => {
                            // Maneja los errores silenciosamente durante el escaneo
                        }
                    ).catch((err) => {
                        console.error(`Error al iniciar el escáner: ${err}`);
                        mostrarNotificacion('No se pudo acceder a la cámara. Verifique los permisos.', 'error');
                        reproducirSonido('error');
                        document.getElementById('scan-status').textContent = 'Error en la cámara';
                        document.getElementById('scan-status').classList.add('error');
                    });
                } else {
                    mostrarNotificacion('No se detectaron cámaras en el dispositivo', 'error');
                    reproducirSonido('error');
                    document.getElementById('scan-status').textContent = 'No se detectaron cámaras';
                    document.getElementById('scan-status').classList.add('error');
                }
            }).catch(err => {
                console.error(`Error al enumerar cámaras: ${err}`);
                mostrarNotificacion('Error al acceder a las cámaras', 'error');
                reproducirSonido('error');
                document.getElementById('scan-status').textContent = 'Error al acceder a la cámara';
                document.getElementById('scan-status').classList.add('error');
            });
        }

        // Función para detener el escáner
        function detenerEscanerQR() {
            html5QrCode.stop().catch(err => {
                console.error(`Error al detener el escáner: ${err}`);
            });
        }

        // Pausar/reanudar el escáner
        function pausarEscaner() {
            scanActive = false;
            document.getElementById('scan-status').textContent = 'Escáner pausado';
            document.getElementById('scan-status').classList.add('paused');
        }

        function reanudarEscaner() {
            scanActive = true;
            document.getElementById('scan-status').textContent = 'Esperando código QR...';
            document.getElementById('scan-status').classList.remove('paused');
            document.getElementById('scan-status').classList.remove('error');
            document.getElementById('scan-status').classList.remove('success');
        }

        // Iniciar el escáner cuando la página esté lista
        document.addEventListener('DOMContentLoaded', () => {
            iniciarEscanerQR();
            
            // Enfocar automáticamente la cámara al cargar la página
            setTimeout(() => {
                const camaraContainer = document.getElementById('reader');
                if (camaraContainer) {
                    camaraContainer.scrollIntoView({ behavior: 'smooth' });
                }
            }, 500);
        });

        // Detener el escáner cuando la página se cierre o se oculte
        window.addEventListener('beforeunload', () => {
            detenerEscanerQR();
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                detenerEscanerQR();
            } else {
                iniciarEscanerQR();
            }
        });

        function onScanSuccess(decodedText, decodedResult) {
            // Si el escáner está pausado, no procesar el código
            if (!scanActive) {
                return;
            }
            
            // Prevenir escaneos repetidos del mismo código en un corto período
            if (scanCooldown && lastScanned === decodedText) {
                return;
            }
            
            // Registrar el código escaneado y activar el cooldown
            lastScanned = decodedText;
            scanCooldown = true;
            
            // Pausar el escáner mientras se procesa
            pausarEscaner();
            
            // Actualizar el estado del escáner
            document.getElementById('scan-status').textContent = 'Código detectado, procesando...';
            document.getElementById('scan-status').classList.add('success');
            
            // Reproducir sonido de escaneo exitoso
            reproducirSonido('scan');
            
            // Vibrar el dispositivo si está disponible
            if (navigator.vibrate) {
                navigator.vibrate(200);
            }
            
            // Mostrar notificación de escaneo
            mostrarNotificacion('Código QR escaneado, procesando...', 'info');
            
            // Procesar después de un segundo
            setTimeout(() => {
                buscarAprendizPorQR(decodedText);
            }, 1000);
            
            // Restablecer el cooldown y reanudar el escáner después del tiempo definido
            setTimeout(() => {
                scanCooldown = false;
                reanudarEscaner();
            }, COOLDOWN_TIME);
        }

        // Función para buscar aprendiz por documento
        function buscarAprendiz() {
            let documento = document.getElementById('documento').value;
            if (!documento) {
                mostrarNotificacion('Ingrese un número de documento', 'error');
                reproducirSonido('error');
                return;
            }
            
            mostrarCargando('btn-buscar', true);
            verificarAsistencia(documento);
        }

        // Función para buscar aprendiz por QR
        function buscarAprendizPorQR(qrCode) {
            $.ajax({
                url: '/admin/buscar-por-qr',
                method: 'POST',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
                    qr_code: qrCode
                },
                success: function(response) {
                    mostrarInformacionAprendiz(response);
                    
                    // Registrar automáticamente la asistencia después de 1 segundo
                    setTimeout(() => {
                        registrarAsistenciaAutomatica(response);
                    }, 1000);
                },
                error: function(error) {
                    mostrarNotificacion('Error al buscar aprendiz', 'error');
                    reproducirSonido('error');
                    document.getElementById('scan-status').textContent = 'Error: Código QR no válido';
                    document.getElementById('scan-status').classList.add('error');
                }
            });
        }

        // Registrar asistencia automáticamente basado en la respuesta del servidor
        function registrarAsistenciaAutomatica(data) {
            if (data.puede_registrar_entrada) {
                registrarAsistencia('entrada');
            } else if (data.puede_registrar_salida) {
                registrarAsistencia('salida');
            } else {
                // No puede registrar ninguna, probablemente ya registró ambas
                mostrarNotificacion('Ya se registraron todas las asistencias para hoy', 'info');
                document.getElementById('scan-status').textContent = 'Sin acciones pendientes';
            }
        }

        // Verificar asistencia y mostrar botones correspondientes
        function verificarAsistencia(documento) {
            $.ajax({
                url: '/admin/verificar-asistencia',
                method: 'POST',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
                    documento_identidad: documento
                },
                success: function(response) {
                    mostrarCargando('btn-buscar', false);
                    mostrarInformacionAprendiz(response);
                },
                error: function(error) {
                    mostrarCargando('btn-buscar', false);
                    mostrarNotificacion('Error al verificar asistencia', 'error');
                    reproducirSonido('error');
                }
            });
        }

        // Mostrar información del aprendiz y los botones correspondientes
        function mostrarInformacionAprendiz(data) {
            document.getElementById('aprendiz-info').style.display = 'block';
            document.getElementById('nombre-aprendiz').textContent = data.user.nombres_completos;
            document.getElementById('documento-aprendiz').textContent = data.user.documento_identidad;
            
            // Información del programa
            if (data.user.programa_formacion) {
                document.getElementById('programa-aprendiz').textContent = data.user.programa_formacion.nombre_programa;
                document.getElementById('ficha-aprendiz').textContent = data.user.programa_formacion.numero_ficha;
            } else {
                document.getElementById('programa-aprendiz').textContent = 'N/A';
                document.getElementById('ficha-aprendiz').textContent = 'N/A';
            }

            // Mostrar/ocultar botones según el estado de asistencia
            document.getElementById('btn-entrada').style.display = data.puede_registrar_entrada ? 'flex' : 'none';
            document.getElementById('btn-salida').style.display = data.puede_registrar_salida ? 'flex' : 'none';
            
            // Actualizar estado del escáner
            if (data.puede_registrar_entrada) {
                document.getElementById('scan-status').textContent = 'Aprendiz identificado - Registrando entrada';
            } else if (data.puede_registrar_salida) {
                document.getElementById('scan-status').textContent = 'Aprendiz identificado - Registrando salida';
            } else {
                document.getElementById('scan-status').textContent = 'Aprendiz identificado - Sin acciones pendientes';
            }
            
            // Scroll a la información si es búsqueda manual
            if (!scanActive) {
                document.getElementById('aprendiz-info').scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Registrar asistencia
        function registrarAsistencia(tipo) {
            let documento = document.getElementById('documento-aprendiz').textContent;
            const btnId = tipo === 'entrada' ? 'btn-entrada' : 'btn-salida';
            mostrarCargando(btnId, true);
            
            console.log('Intentando registrar asistencia:', {
                documento_identidad: documento,
                tipo: tipo
            });
            
            $.ajax({
                url: '/admin/registrar-asistencia',
                method: 'POST',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
                    documento_identidad: documento,
                    tipo: tipo
                },
                success: function(response) {
                    console.log('Respuesta exitosa:', response);
                    mostrarCargando(btnId, false);
                    const mensaje = tipo === 'entrada' ? 'Entrada registrada correctamente' : 'Salida registrada correctamente';
                    mostrarNotificacion(mensaje, 'success');
                    
                    // Reproducir sonido según el tipo de registro
                    reproducirSonido(tipo);
                    
                    // Actualizar estado del escáner
                    document.getElementById('scan-status').textContent = tipo === 'entrada' ? 
                        'Entrada registrada correctamente' : 'Salida registrada correctamente';
                    document.getElementById('scan-status').classList.add('success');
                    
                    // Ocultar el botón correspondiente
                    document.getElementById(btnId).style.display = 'none';
                },
                error: function(error) {
                    console.error('Error en la respuesta:', error);
                    mostrarCargando(btnId, false);
                    mostrarNotificacion(error.responseJSON?.error || 'Error al registrar asistencia', 'error');
                    reproducirSonido('error');
                    
                    document.getElementById('scan-status').textContent = 'Error al registrar asistencia: ' + (error.responseJSON?.error || 'Error desconocido');
                    document.getElementById('scan-status').classList.add('error');
                }
            });
        }
        
        // Reproducir sonido según el caso
        function reproducirSonido(tipo) {
            let audio;
            
            switch(tipo) {
                case 'entrada':
                    audio = document.getElementById('sound-entrada');
                    break;
                case 'salida':
                    audio = document.getElementById('sound-salida');
                    break;
                case 'error':
                    audio = document.getElementById('sound-error');
                    break;
                case 'scan':
                    audio = document.getElementById('sound-scan');
                    break;
                default:
                    return;
            }
            
            // Asegurarse de reiniciar el audio antes de reproducirlo
            audio.pause();
            audio.currentTime = 0;
            audio.play().catch(e => console.log('No se pudo reproducir el sonido'));
        }
        
        // Mostrar notificación
        function mostrarNotificacion(mensaje, tipo) {
            const notificacion = document.getElementById('notification');
            notificacion.textContent = mensaje;
            notificacion.style.display = 'block';
            
            // Cambiar color según el tipo
            if (tipo === 'error') {
                notificacion.style.background = '#ef4444';
            } else if (tipo === 'info') {
                notificacion.style.background = '#3b82f6';
            } else {
                notificacion.style.background = '#10b981';
            }
            
            // Ocultar después de 3 segundos
            setTimeout(() => {
                notificacion.style.opacity = '0';
                setTimeout(() => {
                    notificacion.style.display = 'none';
                    notificacion.style.opacity = '1';
                }, 300);
            }, 3000);
        }
        
        // Mostrar/ocultar indicador de carga
        function mostrarCargando(btnId, mostrar) {
            const btn = document.getElementById(btnId);
            if (mostrar) {
                btn.classList.add('loading');
                btn.setAttribute('disabled', true);
            } else {
                btn.classList.remove('loading');
                btn.removeAttribute('disabled');
            }
        }
    </script>
</body>
</html><?php /**PATH C:\laragon\www\ersena\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>