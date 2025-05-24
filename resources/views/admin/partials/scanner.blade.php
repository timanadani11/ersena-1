<!-- Scanner Section -->
<div class="scanner-container fadeIn">
    <!-- Scanner QR -->
    <div class="card scanner-card slideInLeft">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-qrcode"></i> Escanear código QR</h3>
        </div>
        <div class="card-body">
            <div id="reader" class="qr-reader"></div>
            <div id="scan-status" class="scan-status">Esperando código QR...</div>
        </div>
    </div>

    <!-- Búsqueda manual e Información del aprendiz -->
    <div class="scanner-info-container slideInRight">
        <div class="card search-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Buscar por documento</h3>
            </div>
            <div class="card-body">
                <div class="search-box">
                    <input type="text" id="documento" class="form-control" placeholder="Número de documento" inputmode="numeric" pattern="[0-9]*">
                    <button onclick="buscarAprendiz()" class="btn btn-primary" id="btn-buscar">
                        <span class="loader"></span>
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Información del aprendiz -->
        <div id="aprendiz-info" class="card" style="display: none;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user"></i> Información del Aprendiz</h3>
            </div>
            <div class="card-body">
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
    </div>
</div>

@section('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8"></script>
<script>
    // Variables globales
    let html5QrCode;
    let lastScanned = null;
    let scanCooldown = false;
    let scanActive = true;
    const COOLDOWN_TIME = 5000; // 5 segundos de espera entre escaneos del mismo QR

    // Configuración del escáner QR
    const qrConfig = {
        fps: 10,
        qrbox: {
            width: 250,
            height: 250
        },
        aspectRatio: 1.0,
        disableFlip: false,
        formatsToSupport: [
            Html5QrcodeSupportedFormats.QR_CODE,
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
        console.log("Iniciando escáner QR...");
        
        // Verificar si ya hay una instancia del escáner
        if (html5QrCode && html5QrCode instanceof Html5Qrcode) {
            try {
                html5QrCode.stop();
            } catch(e) {
                console.error("Error al detener el escáner existente:", e);
            }
        }
        
        // Crear una nueva instancia
        html5QrCode = new Html5Qrcode("reader");
        
        // Verificar si getCameras está disponible
        if (typeof Html5Qrcode.getCameras !== 'function') {
            showNotification('Error: La API de cámaras no está disponible en este navegador', 'error');
            reproducirSonido('error');
            document.getElementById('scan-status').textContent = 'La cámara no está disponible';
            document.getElementById('scan-status').classList.add('error');
            return;
        }

        Html5Qrcode.getCameras()
            .then(devices => {
                if (devices && devices.length) {
                    // Intenta usar la cámara trasera primero
                    const camaraTrasera = devices.find(device => /(back|rear)/i.test(device.label));
                    const camaraId = camaraTrasera ? camaraTrasera.id : devices[0].id;
                    
                    console.log("Cámaras disponibles:", devices);
                    console.log("Seleccionando cámara:", camaraId);

                    html5QrCode.start(
                        camaraId, 
                        qrConfig,
                        onScanSuccess,
                        (errorMessage) => {
                            // Maneja los errores silenciosamente durante el escaneo
                            console.log("Error durante el escaneo (normal):", errorMessage);
                        }
                    ).catch((err) => {
                        console.error(`Error al iniciar el escáner: ${err}`);
                        showNotification('No se pudo acceder a la cámara. Verifique los permisos.', 'error');
                        reproducirSonido('error');
                        document.getElementById('scan-status').textContent = 'Error en la cámara';
                        document.getElementById('scan-status').classList.add('error');
                    });
                } else {
                    console.error("No se encontraron dispositivos de cámara");
                    showNotification('No se detectaron cámaras en el dispositivo', 'error');
                    reproducirSonido('error');
                    document.getElementById('scan-status').textContent = 'No se detectaron cámaras';
                    document.getElementById('scan-status').classList.add('error');
                }
            }).catch(err => {
                console.error(`Error al enumerar cámaras: ${err}`);
                showNotification('Error al acceder a las cámaras', 'error');
                reproducirSonido('error');
                document.getElementById('scan-status').textContent = 'Error al acceder a la cámara';
                document.getElementById('scan-status').classList.add('error');
            });
    }

    // Función para detener el escáner
    function detenerEscanerQR() {
        if (html5QrCode) {
            html5QrCode.stop().catch(err => {
                console.error(`Error al detener el escáner: ${err}`);
            });
        }
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

    function onScanSuccess(decodedText, decodedResult) {
        console.log("Código escaneado:", decodedText);
        
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
        
        // Vibrar el dispositivo si está disponible
        if (navigator.vibrate) {
            navigator.vibrate(200);
        }
        
        // Reproducir sonido de escaneo
        reproducirSonido('scan');
        
        // Mostrar notificación de escaneo
        showNotification('Código QR escaneado, procesando...', 'info');
        
        // Procesar después de un momento
        setTimeout(() => {
            buscarAprendizPorQR(decodedText);
        }, 500);
        
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
            showNotification('Ingrese un número de documento', 'error');
            reproducirSonido('error');
            return;
        }
        
        mostrarCargando('btn-buscar', true);
        verificarAsistencia(documento);
    }

    // Función para buscar aprendiz por QR
    function buscarAprendizPorQR(qrCode) {
        $.ajax({
            url: '{{ route("admin.buscar-por-qr") }}',
            method: 'POST',
            data: {
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
                showNotification('Error: ' + (error.responseJSON?.error || 'Código QR no válido'), 'error');
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
            showNotification('Ya se registraron todas las asistencias para hoy', 'info');
            document.getElementById('scan-status').textContent = 'Sin acciones pendientes';
        }
    }

    // Verificar asistencia y mostrar botones correspondientes
    function verificarAsistencia(documento) {
        $.ajax({
            url: '{{ route("admin.verificar-asistencia") }}',
            method: 'POST',
            data: {
                documento_identidad: documento
            },
            success: function(response) {
                mostrarCargando('btn-buscar', false);
                mostrarInformacionAprendiz(response);
            },
            error: function(error) {
                mostrarCargando('btn-buscar', false);
                showNotification('Error: ' + (error.responseJSON?.error || 'Aprendiz no encontrado'), 'error');
                reproducirSonido('error');
            }
        });
    }

    // Mostrar información del aprendiz y los botones correspondientes
    function mostrarInformacionAprendiz(data) {
        const aprendizInfo = document.getElementById('aprendiz-info');
        aprendizInfo.style.display = 'block';
        
        // Añadir clase para animación
        aprendizInfo.classList.remove('fadeIn');
        void aprendizInfo.offsetWidth; // Trigger reflow
        aprendizInfo.classList.add('fadeIn');
        
        document.getElementById('nombre-aprendiz').textContent = data.user.nombres_completos;
        document.getElementById('documento-aprendiz').textContent = data.user.documento_identidad;
        
        // Información del programa
        if (data.user.programa_formacion) {
            document.getElementById('programa-aprendiz').textContent = data.user.programa_formacion.nombre_programa;
            document.getElementById('nivel-formacion').textContent = data.user.programa_formacion.nivel_formacion || 'N/A';
            document.getElementById('ficha-aprendiz').textContent = data.user.programa_formacion.numero_ficha;
        } else {
            document.getElementById('programa-aprendiz').textContent = 'N/A';
            document.getElementById('nivel-formacion').textContent = 'N/A';
            document.getElementById('ficha-aprendiz').textContent = 'N/A';
        }
        
        // Jornada
        if (data.user.jornada) {
            document.getElementById('jornada-aprendiz').textContent = data.user.jornada.nombre;
        } else {
            document.getElementById('jornada-aprendiz').textContent = 'N/A';
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
        
        // Scroll a la información del aprendiz
        aprendizInfo.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Registrar asistencia
    function registrarAsistencia(tipo) {
        let documento = document.getElementById('documento-aprendiz').textContent;
        const btnId = tipo === 'entrada' ? 'btn-entrada' : 'btn-salida';
        mostrarCargando(btnId, true);
        
        $.ajax({
            url: '{{ route("admin.registrar-asistencia") }}',
            method: 'POST',
            data: {
                documento_identidad: documento,
                tipo: tipo
            },
            success: function(response) {
                mostrarCargando(btnId, false);
                const mensaje = tipo === 'entrada' ? 'Entrada registrada correctamente' : 'Salida registrada correctamente';
                showNotification(mensaje, 'success');
                reproducirSonido(tipo);
                
                // Actualizar estado del escáner
                document.getElementById('scan-status').textContent = tipo === 'entrada' ? 
                    'Entrada registrada correctamente' : 'Salida registrada correctamente';
                document.getElementById('scan-status').classList.add('success');
                
                // Ocultar el botón correspondiente con animación
                const btn = document.getElementById(btnId);
                btn.classList.add('fadeOut');
                setTimeout(() => {
                    btn.style.display = 'none';
                }, 300);
            },
            error: function(error) {
                mostrarCargando(btnId, false);
                showNotification(error.responseJSON?.error || 'Error al registrar asistencia', 'error');
                reproducirSonido('error');
                
                document.getElementById('scan-status').textContent = 'Error al registrar asistencia: ' + (error.responseJSON?.error || 'Error desconocido');
                document.getElementById('scan-status').classList.add('error');
            }
        });
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
    
    // Iniciar escáner automáticamente cuando se cargue esta vista
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            iniciarEscanerQR();
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
</script>
@endsection 