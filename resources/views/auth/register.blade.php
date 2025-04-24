<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Registro de Aprendiz - SENA</title>
    <meta name="theme-color" content="#39A900">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link href="{{ asset('css/register.css') }}" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/icon/icon.ico') }}" type="image/x-icon">
</head>
<body class="register-page">
    <div class="register-container">
        <div class="register-card">
            <div class="register-header-content">
            <div class="register-logo">
                <img src="{{ asset('img/logo/logo.png') }}" alt="Logo SENA" loading="lazy">
            </div>
                <h2 class="register-title">Registro de Aprendiz</h2>
                
                <div class="progress-container">
                    <div class="progress-bar" id="progress-bar"></div>
                </div>

                <div class="steps-container">
                    <div class="step-item active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-text">Datos</div>
                    </div>
                    <div class="step-item" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-text">Programa</div>
                    </div>
                    <div class="step-item" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-text">Dispositivo</div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="register-form" id="registerForm" autocomplete="off">
                @csrf
                <div class="sections-container">
                    <!-- Sección 1: Datos Personales -->
                    <div class="form-section active" data-section="1">
                        <div class="form-grid-compact">
                            <div class="register-form-group">
                                <label for="nombres_completos">
                                    <i class="fas fa-user"></i>
                                    Nombres Completos
                                </label>
                                <input type="text" id="nombres_completos" name="nombres_completos" required 
                                    value="{{ old('nombres_completos') }}" class="auto-next" 
                                    placeholder="Ingresa tus nombres completos" autocomplete="off">
                            </div>

                            <div class="register-form-group">
                                <label for="documento_identidad">
                                    <i class="fas fa-id-card"></i>
                                    Documento de Identidad
                                </label>
                                <input type="text" id="documento_identidad" name="documento_identidad" required 
                                    value="{{ old('documento_identidad') }}" class="auto-next" 
                                    placeholder="Ingresa tu documento" pattern="[0-9]*" inputmode="numeric">
                            </div>

                            <div class="register-form-group">
                                <label for="correo">
                                    <i class="fas fa-envelope"></i>
                                    Correo Electrónico
                                </label>
                                <input type="email" id="correo" name="correo" required 
                                    value="{{ old('correo') }}" class="auto-next" 
                                    placeholder="correo@ejemplo.com" inputmode="email">
                            </div>

                            <div class="register-form-group">
                                <label for="password">
                                    <i class="fas fa-lock"></i>
                                    Contraseña
                                </label>
                                <div class="password-field">
                                    <input type="password" id="password" name="password" required class="auto-next" 
                                        placeholder="Ingresa tu contraseña" autocomplete="new-password">
                                    <i class="fas fa-eye toggle-password" tabindex="-1"></i>
                                </div>
                            </div>

                            <div class="register-form-group">
                                <label for="password_confirmation">
                                    <i class="fas fa-lock"></i>
                                    Confirmar Contraseña
                                </label>
                                <div class="password-field">
                                    <input type="password" id="password_confirmation" name="password_confirmation" required 
                                        class="auto-next" data-match="password" 
                                        placeholder="Confirma tu contraseña" autocomplete="new-password">
                                    <i class="fas fa-eye toggle-password" tabindex="-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Datos del Programa -->
                    <div class="form-section" data-section="2">
                        <div class="form-grid-compact">
                            <div class="register-form-group">
                                <label for="nombre_programa">
                                    <i class="fas fa-graduation-cap"></i>
                                    Nombre del Programa
                                </label>
                                <input type="text" id="nombre_programa" name="nombre_programa" required 
                                    value="{{ old('nombre_programa') }}" class="auto-next" 
                                    placeholder="Ej: Análisis y Desarrollo de Software"
                                    autocomplete="off"
                                    list="programas-list">
                                <datalist id="programas-list"></datalist>
                            </div>

                            <div class="register-form-group">
                                <label for="nivel_formacion">
                                    <i class="fas fa-layer-group"></i>
                                    Nivel de Formación
                                </label>
                                <select id="nivel_formacion" name="nivel_formacion" required class="auto-next">
                                    <option value="">Selecciona un nivel</option>
                                    <option value="tecnico" {{ old('nivel_formacion') == 'tecnico' ? 'selected' : '' }}>Técnico</option>
                                    <option value="tecnologo" {{ old('nivel_formacion') == 'tecnologo' ? 'selected' : '' }}>Tecnólogo</option>
                                </select>
                            </div>

                            <div class="register-form-group">
                                <label for="numero_ficha">
                                    <i class="fas fa-hashtag"></i>
                                    Número de Ficha
                                </label>
                                <input type="text" id="numero_ficha" name="numero_ficha" required 
                                    value="{{ old('numero_ficha') }}" class="auto-next" 
                                    placeholder="Ej: 2557631" pattern="[0-9]*" inputmode="numeric">
                            </div>

                            <div class="register-form-group">
                                <label for="numero_ambiente">
                                    <i class="fas fa-door-open"></i>
                                    Número de Ambiente
                                </label>
                                <input type="text" id="numero_ambiente" name="numero_ambiente" required 
                                    value="{{ old('numero_ambiente') }}" class="auto-next" 
                                    placeholder="Ej: 301" pattern="[0-9]*" inputmode="numeric">
                            </div>

                            <div class="register-form-group">
                                <label for="jornada_id">
                                    <i class="fas fa-clock"></i>
                                    Jornada
                                </label>
                                <select id="jornada_id" name="jornada_id" required class="auto-next">
                                    <option value="">Selecciona una jornada</option>
                                    @foreach(\App\Models\Jornada::all() as $jornada)
                                        <option value="{{ $jornada->id }}" {{ old('jornada_id') == $jornada->id ? 'selected' : '' }}>
                                            {{ ucfirst($jornada->nombre) }} ({{ \Carbon\Carbon::parse($jornada->hora_entrada)->format('h:i A') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Datos del Dispositivo -->
                    <div class="form-section" data-section="3">
                        <div class="form-grid-compact">
                            <div class="register-form-group">
                                <label for="marca">
                                    <i class="fas fa-laptop"></i>
                                    Marca del Equipo
                                </label>
                                <input type="text" id="marca" name="marca" required 
                                    value="{{ old('marca') }}" class="auto-next" 
                                    placeholder="Ej: Lenovo, HP, Dell">
                            </div>

                            <div class="register-form-group">
                                <label for="serial">
                                    <i class="fas fa-barcode"></i>
                                    Número de Serial
                                </label>
                                <input type="text" id="serial" name="serial" required 
                                    value="{{ old('serial') }}" class="auto-next" 
                                    placeholder="Ingresa el número de serial">
                            </div>

                            <div class="file-upload">
                                <label for="foto_serial" tabindex="0" role="button">
                                    <i class="fas fa-camera"></i>
                                    <span>Subir Foto del Serial</span>
                                </label>
                                <input type="file" id="foto_serial" name="foto_serial" 
                                    accept="image/*" required capture="environment">
                                <div class="file-preview" id="file-preview"></div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="register-alert register-alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li><i class="fas fa-exclamation-circle"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-navigation">
                    <button type="button" class="nav-button btn-prev" id="prevBtn" style="display: none;">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>
                    <button type="button" class="nav-button btn-next" id="nextBtn">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="register-submit" id="submitBtn" style="display: none;">
                        <i class="fas fa-user-plus"></i> Registrar Aprendiz
                    </button>
                </div>

                <div class="register-links">
                    <p>¿Ya tienes una cuenta? <a href="{{ route('login') }}">Iniciar Sesión</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentStep = 1;
            const totalSteps = 3;
            const form = document.getElementById('registerForm');
            const nextBtn = document.getElementById('nextBtn');
            const prevBtn = document.getElementById('prevBtn');
            const submitBtn = document.getElementById('submitBtn');
            const progressBar = document.getElementById('progress-bar');
            const passwordFields = document.querySelectorAll('.toggle-password');
            const fileInput = document.getElementById('foto_serial');
            const filePreview = document.getElementById('file-preview');

            // Mostrar vista previa de la imagen
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        filePreview.innerHTML = `<img src="${e.target.result}" alt="Vista previa">`;
                        filePreview.style.display = 'block';
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            // Toggle para mostrar/ocultar contraseñas
            passwordFields.forEach(icon => {
                icon.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        input.type = 'password';
                        this.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                });
            });

            // Configurar búsqueda y autocompletado de programas
            const nombreProgramaInput = document.getElementById('nombre_programa');
            const numeroFichaInput = document.getElementById('numero_ficha');
            const nivelFormacionSelect = document.getElementById('nivel_formacion');
            const programasList = document.getElementById('programas-list');
            let programasData = [];

            // Cargar datos de programas
            fetch('/js/programas.json')
                .then(response => response.json())
                .then(data => {
                    programasData = data.programas;
                })
                .catch(error => console.error('Error cargando programas:', error));

            let typingTimer;
            const doneTypingInterval = 300;

            nombreProgramaInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                if (this.value) {
                    typingTimer = setTimeout(() => filterPrograms(this.value), doneTypingInterval);
                }
            });

            function filterPrograms(query) {
                query = query.toLowerCase();
                programasList.innerHTML = '';
                
                programasData.forEach(programa => {
                    if (programa.nombre.toLowerCase().includes(query)) {
                        const option = document.createElement('option');
                        option.value = programa.nombre;
                        option.dataset.nivel = programa.nivel;
                        option.dataset.fichas = JSON.stringify(programa.fichas);
                        programasList.appendChild(option);
                    }
                });
            }

            nombreProgramaInput.addEventListener('change', function() {
                const selectedOption = Array.from(programasList.options)
                    .find(option => option.value === this.value);
                
                if (selectedOption) {
                    const programa = programasData.find(p => p.nombre === this.value);
                    if (programa) {
                        nivelFormacionSelect.value = programa.nivel;
                        // Establecer la primera ficha disponible
                        if (programa.fichas && programa.fichas.length > 0) {
                            numeroFichaInput.value = programa.fichas[0];
                        }
                    }
                }
            });

            // Actualizar la barra de progreso
            function updateProgress() {
                const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
                progressBar.style.width = `${progress}%`;
            }

            // Mostrar sección actual
            function showSection(step) {
                document.querySelectorAll('.form-section').forEach(section => {
                    section.classList.remove('active');
                });
                document.querySelector(`.form-section[data-section="${step}"]`).classList.add('active');

                // Actualizar estados de los pasos
                document.querySelectorAll('.step-item').forEach(item => {
                    const itemStep = parseInt(item.dataset.step);
                    item.classList.remove('active', 'completed');
                    if (itemStep === step) {
                        item.classList.add('active');
                    } else if (itemStep < step) {
                        item.classList.add('completed');
                    }
                });

                // Mostrar/ocultar botones
                prevBtn.style.display = step > 1 ? 'block' : 'none';
                nextBtn.style.display = step < totalSteps ? 'block' : 'none';
                submitBtn.style.display = step === totalSteps ? 'block' : 'none';

                // Enfocar el primer campo de la sección actual
                const firstInput = document.querySelector(`.form-section[data-section="${step}"] input:first-of-type`);
                if (firstInput) setTimeout(() => firstInput.focus(), 300);

                updateProgress();
            }

            // Validar campos de la sección actual
            function validateSection(step) {
                const section = document.querySelector(`.form-section[data-section="${step}"]`);
                const inputs = section.querySelectorAll('input[required], select[required]');
                let isValid = true;

                inputs.forEach(input => {
                    // Si es un campo de confirmación de contraseña, validar que coincida
                    if (input.dataset.match) {
                        const matchInput = document.getElementById(input.dataset.match);
                        if (input.value !== matchInput.value) {
                            isValid = false;
                            input.classList.add('error');
                            showTooltip(input, "Las contraseñas no coinciden");
                            return;
                        }
                    }
                    
                    if (!input.value) {
                        isValid = false;
                        input.classList.add('error');
                        showTooltip(input, "Este campo es requerido");
                    } else {
                        input.classList.remove('error');
                    }
                });

                return isValid;
            }
            
            // Mostrar tooltip de error
            function showTooltip(element, message) {
                let tooltip = document.createElement('div');
                tooltip.className = 'error-tooltip';
                tooltip.textContent = message;
                
                element.parentNode.style.position = 'relative';
                element.parentNode.appendChild(tooltip);
                
                setTimeout(() => {
                    if (tooltip.parentNode) {
                        tooltip.parentNode.removeChild(tooltip);
                    }
                }, 3000);
            }

            // Avanzar al siguiente paso
            function nextStep() {
                if (validateSection(currentStep)) {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        showSection(currentStep);
                    } else {
                        validateAndSubmit();
                    }
                }
            }

            // Validar y enviar formulario
            function validateAndSubmit() {
                if (validateSection(totalSteps)) {
                    submitBtn.classList.add('submitting');
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                    setTimeout(() => form.submit(), 800);
                }
            }

            // Event listeners
            nextBtn.addEventListener('click', nextStep);
            prevBtn.addEventListener('click', () => {
                currentStep--;
                showSection(currentStep);
            });

            // Auto-avance al llenar campos
            document.querySelectorAll('.auto-next').forEach(input => {
                let timeoutId;
                
                input.addEventListener('input', function() {
                    clearTimeout(timeoutId);
                    
                    const isLastInSection = Array.from(document.querySelectorAll(`.form-section[data-section="${currentStep}"] .auto-next`))
                        .pop() === this;
                    
                    if (isLastInSection && this.value.trim() !== '') {
                        timeoutId = setTimeout(() => {
                            if (validateSection(currentStep)) {
                                nextStep();
                            }
                        }, 800);
                    }
                });
                
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        nextStep();
                    }
                });
            });

            // Click en los pasos
            document.querySelectorAll('.step-item').forEach(item => {
                item.addEventListener('click', () => {
                    const step = parseInt(item.dataset.step);
                    if (step <= currentStep) {
                        currentStep = step;
                        showSection(currentStep);
                    } else if (validateSection(currentStep)) {
                        currentStep = step;
                        showSection(currentStep);
                    }
                });
            });

            // Inicializar
            showSection(1);

            // Prevenir el zoom en móviles al hacer focus en inputs
            const metas = document.getElementsByTagName('meta');
            for (let i = 0; i < metas.length; i++) {
                if (metas[i].name === 'viewport') {
                    let content = metas[i].content;
                    if (content.indexOf('maximum-scale') === -1) {
                        metas[i].content = content + ', maximum-scale=1';
                    }
                    break;
                }
            }
            // No permitir zoom en inputs en iOS
            document.addEventListener('gesturestart', function(e) {
                e.preventDefault();
            });
        });
    </script>
</body>
</html>