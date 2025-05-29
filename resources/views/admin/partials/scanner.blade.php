<!-- Scanner Section -->
<div class="scanner-container fadeIn">
    @csrf
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

<!-- Incluir el modal de salida anticipada -->
@include('admin.partials.early-exit-modal')

<!-- Modal para entrada en horario incorrecto -->
<div id="wrong-shift-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Horario incorrecto</h2>
            <span class="close" onclick="cerrarModalHorarioIncorrecto()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="alert alert-warning">
                <p>El aprendiz está intentando ingresar fuera de su jornada asignada.</p>
                <p><strong>Hora actual:</strong> <span id="hora-actual-entrada"></span></p>
                <p><strong>Jornada asignada:</strong> <span id="jornada-asignada"></span></p>
            </div>
            
            <form id="wrong-shift-form" enctype="multipart/form-data">
                <input type="hidden" id="documento-hidden-entrada" name="documento_identidad">
                <input type="hidden" name="tipo" value="entrada">
                <input type="hidden" name="fuera_de_horario" value="1">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                
                <div class="form-group">
                    <label for="motivo-entrada">Motivo de ingreso fuera de horario:</label>
                    <select id="motivo-entrada" name="motivo" class="form-control" required>
                        <option value="">Seleccione un motivo</option>
                        <option value="coordinacion">Autorización de Coordinación</option>
                        <option value="recuperacion">Recuperación de tiempo</option>
                        <option value="actividad_especial">Actividad especial</option>
                        <option value="otro">Otro motivo</option>
                    </select>
                </div>
                
                <div id="observaciones-entrada-container" class="form-group">
                    <label for="observaciones-entrada">Observaciones:</label>
                    <textarea id="observaciones-entrada" name="observaciones" class="form-control" rows="3" placeholder="Detalle el motivo del ingreso fuera de horario"></textarea>
                </div>
                
                <div id="autorizacion-entrada-container" class="form-group">
                    <label for="foto-autorizacion-entrada">Foto de autorización:</label>
                    <div class="file-upload-container">
                        <input type="file" id="foto-autorizacion-entrada" name="foto_autorizacion" class="file-input" accept="image/*" capture="camera">
                        <div class="file-upload-button">
                            <i class="fas fa-camera"></i> Tomar foto
                        </div>
                        <span class="file-name-entrada">Ningún archivo seleccionado</span>
                    </div>
                    <div id="preview-container-entrada" style="display: none; margin-top: 10px;">
                        <img id="image-preview-entrada" src="" alt="Vista previa" style="max-width: 100%; height: auto; border-radius: 8px;">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalHorarioIncorrecto()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-autorizar-entrada">
                        <span class="loader"></span>
                        <i class="fas fa-check"></i> Autorizar entrada
                    </button>
                </div>
            </form>
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
    let currentUserData = null; // Almacena los datos del usuario actual

    // Configuración del escáner QR
    const qrConfig = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0,
        disableFlip: false,
        formatsToSupport: [
            Html5QrcodeSupportedFormats.QR_CODE,
            Html5QrcodeSupportedFormats.CODE_128
        ],
        rememberLastUsedCamera: true,
        showTorchButtonIfSupported: true,
        showZoomSliderIfSupported: true
    };

    // Iniciar escáner QR
    function iniciarEscanerQR() {
        console.log("Iniciando escáner QR...");
        
        // Detener escáner existente si lo hay
        if (html5QrCode && html5QrCode instanceof Html5Qrcode) {
            try {
                html5QrCode.stop();
            } catch(e) {
                console.error("Error al detener el escáner existente:", e);
            }
        }
        
        // Crear nueva instancia
        html5QrCode = new Html5Qrcode("reader");
        
        // Verificar disponibilidad de API de cámaras
        if (typeof Html5Qrcode.getCameras !== 'function') {
            showNotification('Error: La API de cámaras no está disponible en este navegador', 'error');
            document.getElementById('scan-status').textContent = 'La cámara no está disponible';
            document.getElementById('scan-status').classList.add('error');
            return;
        }

        Html5Qrcode.getCameras()
            .then(devices => {
                if (devices && devices.length) {
                    // Preferir cámara trasera
                    const camaraTrasera = devices.find(device => /(back|rear)/i.test(device.label));
                    const camaraId = camaraTrasera ? camaraTrasera.id : devices[0].id;
                    
                    console.log("Cámaras disponibles:", devices);
                    console.log("Seleccionando cámara:", camaraId);

                    html5QrCode.start(
                        camaraId, 
                        qrConfig,
                        onScanSuccess,
                        (errorMessage) => {
                            // Errores silenciosos durante el escaneo
                            console.log("Error durante el escaneo (normal):", errorMessage);
                        }
                    ).catch((err) => {
                        console.error(`Error al iniciar el escáner: ${err}`);
                        showNotification('No se pudo acceder a la cámara. Verifique los permisos.', 'error');
                        document.getElementById('scan-status').textContent = 'Error en la cámara';
                        document.getElementById('scan-status').classList.add('error');
                    });
                } else {
                    console.error("No se encontraron dispositivos de cámara");
                    showNotification('No se detectaron cámaras en el dispositivo', 'error');
                    document.getElementById('scan-status').textContent = 'No se detectaron cámaras';
                    document.getElementById('scan-status').classList.add('error');
                }
            }).catch(err => {
                console.error(`Error al enumerar cámaras: ${err}`);
                showNotification('Error al acceder a las cámaras', 'error');
                document.getElementById('scan-status').textContent = 'Error al acceder a la cámara';
                document.getElementById('scan-status').classList.add('error');
            });
    }

    // Detener escáner
    function detenerEscanerQR() {
        if (html5QrCode) {
            html5QrCode.stop().catch(err => {
                console.error(`Error al detener el escáner: ${err}`);
            });
        }
    }

    // Pausar/reanudar escáner
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

    // Procesar código escaneado
    function onScanSuccess(decodedText, decodedResult) {
        // Si escáner pausado o cooldown activo, ignorar
        if (!scanActive || (scanCooldown && lastScanned === decodedText)) {
            return;
        }
        
        // Registrar código y activar cooldown
        lastScanned = decodedText;
        scanCooldown = true;
        pausarEscaner();
        
        // Actualizar UI
        document.getElementById('scan-status').textContent = 'Código detectado, procesando...';
        document.getElementById('scan-status').classList.add('success');
        
        // Vibrar dispositivo si disponible
        if (navigator.vibrate) {
            navigator.vibrate(200);
        }
        
        showNotification('Código QR escaneado, procesando...', 'info');
        
        // Procesar después de un momento
        setTimeout(() => buscarAprendizPorQR(decodedText), 500);
        
        // Restablecer cooldown después del tiempo definido
        setTimeout(() => {
            scanCooldown = false;
            reanudarEscaner();
        }, COOLDOWN_TIME);
    }

    // Búsqueda por documento manual
    function buscarAprendiz() {
        let documento = document.getElementById('documento').value;
        if (!documento) {
            showNotification('Ingrese un número de documento', 'error');
            return;
        }
        
        mostrarCargando('btn-buscar', true);
        verificarAsistencia(documento);
    }

    // Buscar aprendiz por QR
    function buscarAprendizPorQR(qrCode) {
        $.ajax({
            url: '{{ route("admin.buscar-por-qr") }}',
            method: 'POST',
            data: { qr_code: qrCode },
            success: function(response) {
                mostrarInformacionAprendiz(response);
                
                // Registrar asistencia automáticamente
                setTimeout(() => registrarAsistenciaAutomatica(response), 1000);
            },
            error: function(error) {
                showNotification('Error: ' + (error.responseJSON?.error || 'Código QR no válido'), 'error');
                document.getElementById('scan-status').textContent = 'Error: Código QR no válido';
                document.getElementById('scan-status').classList.add('error');
            }
        });
    }

    // Registrar asistencia automáticamente
    function registrarAsistenciaAutomatica(data) {
        if (data.puede_registrar_entrada) {
            registrarAsistencia('entrada');
        } else if (data.puede_registrar_salida) {
            registrarAsistencia('salida');
        } else {
            showNotification('Ya se registraron todas las asistencias para hoy', 'info');
            document.getElementById('scan-status').textContent = 'Sin acciones pendientes';
        }
    }

    // Verificar asistencia
    function verificarAsistencia(documento) {
        $.ajax({
            url: '{{ route("admin.verificar-asistencia") }}',
            method: 'POST',
            data: { documento_identidad: documento },
            success: function(response) {
                mostrarCargando('btn-buscar', false);
                mostrarInformacionAprendiz(response);
            },
            error: function(error) {
                mostrarCargando('btn-buscar', false);
                showNotification('Error: ' + (error.responseJSON?.error || 'Aprendiz no encontrado'), 'error');
            }
        });
    }

    // Mostrar información del aprendiz
    function mostrarInformacionAprendiz(data) {
        // Guardar datos para uso posterior
        currentUserData = data;
        
        const aprendizInfo = document.getElementById('aprendiz-info');
        aprendizInfo.style.display = 'block';
        
        // Animación
        aprendizInfo.classList.remove('fadeIn');
        void aprendizInfo.offsetWidth; // Trigger reflow
        aprendizInfo.classList.add('fadeIn');
        
        // Datos básicos
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
        document.getElementById('jornada-aprendiz').textContent = data.user.jornada ? data.user.jornada.nombre : 'N/A';

        // Botones según estado
        document.getElementById('btn-entrada').style.display = data.puede_registrar_entrada ? 'flex' : 'none';
        document.getElementById('btn-salida').style.display = data.puede_registrar_salida ? 'flex' : 'none';
        
        // Estado del escáner
        if (data.puede_registrar_entrada) {
            document.getElementById('scan-status').textContent = 'Aprendiz identificado - Puede registrar entrada';
        } else if (data.puede_registrar_salida) {
            document.getElementById('scan-status').textContent = 'Aprendiz identificado - Puede registrar salida';
        } else {
            document.getElementById('scan-status').textContent = 'Aprendiz identificado - Sin acciones pendientes';
        }
        
        // Scroll a la información
        aprendizInfo.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Registrar asistencia
    function registrarAsistencia(tipo) {
        let documento = document.getElementById('documento-aprendiz').textContent;
        const btnId = tipo === 'entrada' ? 'btn-entrada' : 'btn-salida';
        
        // Verificar si se requiere motivo
        if (currentUserData && tipo === 'entrada' && currentUserData.requiere_motivo_entrada) {
            abrirModalHorarioIncorrecto(documento);
            return;
        }
        
        if (currentUserData && tipo === 'salida' && currentUserData.requiere_motivo_salida) {
            abrirModalSalidaAnticipada(documento);
            return;
        }
        
        // Mostrar cargando
        mostrarCargando(btnId, true);
        
        // Datos básicos para la petición
        let requestData = {
            documento_identidad: documento,
            tipo: tipo,
            _token: '{{ csrf_token() }}'
        };
        
        // Realizar la petición
        $.ajax({
            url: '{{ route("admin.registrar-asistencia") }}',
            method: 'POST',
            data: requestData,
            success: function(response) {
                // Ocultar cargando
                mostrarCargando(btnId, false);
                
                // Mensaje de éxito
                const mensaje = tipo === 'entrada' ? 'Entrada registrada correctamente' : 'Salida registrada correctamente';
                showNotification(mensaje, 'success');
                
                // Actualizar interfaz
                document.getElementById('scan-status').textContent = mensaje;
                document.getElementById('scan-status').classList.add('success');
                
                // Actualizar botones
                if (tipo === 'entrada') {
                    document.getElementById('btn-entrada').style.display = 'none';
                    document.getElementById('btn-salida').style.display = 'flex';
                } else {
                    document.getElementById('btn-salida').style.display = 'none';
                }
            },
            error: function(error) {
                // Ocultar cargando
                mostrarCargando(btnId, false);
                
                // Mostrar error
                const errorMsg = error.responseJSON?.error || 'Error al registrar asistencia';
                showNotification(errorMsg, 'error');
                
                // Actualizar interfaz
                document.getElementById('scan-status').textContent = 'Error: ' + errorMsg;
                document.getElementById('scan-status').classList.add('error');
            }
        });
    }
    
    // Verificar si la hora actual está dentro del horario de la jornada
    function estaEnHorarioJornada(horaActual, jornada) {
        if (!jornada.hora_inicio || !jornada.hora_fin) {
            return true; // Si no hay horarios definidos, permitimos entrada
        }
        
        // Convertir las horas de string a objetos Date
        const [horaInicioHoras, horaInicioMinutos] = jornada.hora_inicio.split(':').map(Number);
        const [horaFinHoras, horaFinMinutos] = jornada.hora_fin.split(':').map(Number);
        
        const horaInicio = new Date();
        horaInicio.setHours(horaInicioHoras, horaInicioMinutos, 0);
        
        const horaFin = new Date();
        horaFin.setHours(horaFinHoras, horaFinMinutos, 0);
        
        // Ajustar para manejar horarios que cruzan la medianoche
        if (horaFinHoras < horaInicioHoras) {
            horaFin.setDate(horaFin.getDate() + 1);
        }
        
        // Manejar tolerancia de entrada (por ejemplo, permitir llegar hasta 15 minutos tarde)
        const toleranciaMinutos = jornada.tolerancia || 15;
        const horaInicioConTolerancia = new Date(horaInicio);
        horaInicioConTolerancia.setMinutes(horaInicioConTolerancia.getMinutes() + toleranciaMinutos);
        
        // La hora actual debe estar entre la hora de inicio (menos tolerancia) y la hora de fin
        return horaActual >= horaInicio && horaActual <= horaFin;
    }
    
    // Modal para entrada fuera de horario
    function abrirModalHorarioIncorrecto(documento) {
        const modal = document.getElementById('wrong-shift-modal');
        document.getElementById('documento-hidden-entrada').value = documento;
        
        // Mostrar información del horario si existe
        let horarioTexto = 'No definido';
        let jornadaActual = currentUserData?.user?.jornada;
        
        if (jornadaActual) {
            horarioTexto = jornadaActual.nombre;
            if (jornadaActual.hora_entrada && jornadaActual.hora_salida) {
                horarioTexto += ` (${jornadaActual.hora_entrada} - ${jornadaActual.hora_salida})`;
            }
        }
        
        document.getElementById('jornada-asignada').textContent = horarioTexto;
        
        // Mostrar hora actual
        const ahora = new Date();
        document.getElementById('hora-actual-entrada').textContent = ahora.toLocaleTimeString('es-CO', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
        
        // Resetear el formulario
        document.getElementById('wrong-shift-form').reset();
        document.getElementById('preview-container-entrada').style.display = 'none';
        document.querySelector('.file-name-entrada').textContent = 'Ningún archivo seleccionado';
        
        modal.style.display = 'block';
    }
    
    function cerrarModalHorarioIncorrecto() {
        document.getElementById('wrong-shift-modal').style.display = 'none';
    }
    
    function configurarFormularioHorarioIncorrecto() {
        // Configurar manejador de cambio de archivo
        const fileInput = document.getElementById('foto-autorizacion-entrada');
        const fileName = document.querySelector('.file-name-entrada');
        const imagePreview = document.getElementById('image-preview-entrada');
        const previewContainer = document.getElementById('preview-container-entrada');
        
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                fileName.textContent = this.files[0].name;
                
                // Mostrar vista previa
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    previewContainer.style.display = 'block';
                }
                reader.readAsDataURL(this.files[0]);
            } else {
                fileName.textContent = 'Ningún archivo seleccionado';
                previewContainer.style.display = 'none';
            }
        });
        
        // Configurar envío del formulario
        const form = document.getElementById('wrong-shift-form');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            mostrarCargando('btn-autorizar-entrada', true);
            
            $.ajax({
                url: '{{ route("admin.registrar-asistencia") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    mostrarCargando('btn-autorizar-entrada', false);
                    cerrarModalHorarioIncorrecto();
                    showNotification('Entrada autorizada correctamente', 'success');
                    
                    // Actualizar interfaz
                    document.getElementById('scan-status').textContent = 'Entrada autorizada correctamente';
                    document.getElementById('scan-status').classList.add('success');
                    
                    // Ocultar botón de entrada
                    const btnEntrada = document.getElementById('btn-entrada');
                    btnEntrada.classList.add('fadeOut');
                    setTimeout(() => {
                        btnEntrada.style.display = 'none';
                    }, 300);
                    
                    // Actualizar datos locales
                    if (typeof currentUserData !== 'undefined') {
                        currentUserData.puede_registrar_entrada = false;
                        currentUserData.puede_registrar_salida = true;
                    }
                    
                    // Mostrar botón de salida
                    document.getElementById('btn-salida').style.display = 'flex';
                },
                error: function(error) {
                    mostrarCargando('btn-autorizar-entrada', false);
                    const errorMsg = error.responseJSON?.error || 'Error al autorizar entrada';
                    showNotification(errorMsg, 'error');
                }
            });
        });
        
        // Configurar cambio de motivo
        const motivoSelect = document.getElementById('motivo-entrada');
        const autorizacionContainer = document.getElementById('autorizacion-entrada-container');
        
        motivoSelect.addEventListener('change', function() {
            if (this.value === 'recuperacion') {
                autorizacionContainer.style.display = 'none';
                document.getElementById('foto-autorizacion-entrada').removeAttribute('required');
            } else {
                autorizacionContainer.style.display = 'block';
                if (this.value === 'coordinacion') {
                    document.getElementById('foto-autorizacion-entrada').setAttribute('required', 'required');
                } else {
                    document.getElementById('foto-autorizacion-entrada').removeAttribute('required');
                }
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
    
    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(iniciarEscanerQR, 500);
        
        // Inicializar el formulario de entrada fuera de horario
        const wrongShiftForm = document.getElementById('wrong-shift-form');
        if (wrongShiftForm) {
            configurarFormularioHorarioIncorrecto();
        }
    });
    
    // Control de ciclo de vida
    window.addEventListener('beforeunload', detenerEscanerQR);
    
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            detenerEscanerQR();
        } else {
            iniciarEscanerQR();
        }
    });
</script>
@endsection 