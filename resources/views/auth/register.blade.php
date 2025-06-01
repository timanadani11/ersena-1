<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#39A900">
    <title>Registro de Aprendiz - SENA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('img/icon/logoSena.png') }}" type="image/png">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex flex-col items-center justify-center min-h-screen py-6 px-4 sm:px-6 lg:px-8">
        <div 
            x-data="{ 
                currentStep: 1, 
                totalSteps: 3,
                showPassword: false,
                showConfirmPassword: false,
                programasData: [],
                progress() {
                    return ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
                },
                validateSection() {
                    const section = document.querySelector(`.step-section[data-section='${this.currentStep}']`);
                    const inputs = section.querySelectorAll('input[required], select[required]');
                    let isValid = true;
                    
                    inputs.forEach(input => {
                        if (input.id === 'password_confirmation' && input.value !== document.getElementById('password').value) {
                            isValid = false;
                            input.classList.add('border-red-500', 'ring-red-500');
                            return;
                        }
                        
                        if (!input.value) {
                            isValid = false;
                            input.classList.add('border-red-500', 'ring-red-500');
                        } else {
                            input.classList.remove('border-red-500', 'ring-red-500');
                        }
                    });
                    
                    return isValid;
                },
                nextStep() {
                    if (this.validateSection()) {
                        if (this.currentStep < this.totalSteps) {
                            this.currentStep++;
                        }
                    }
                },
                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                    }
                },
                goToStep(step) {
                    if (step <= this.currentStep || this.validateSection()) {
                        this.currentStep = step;
                    }
                },
                init() {
                    fetch('/js/programas.json')
                        .then(response => response.json())
                        .then(data => {
                            this.programasData = data.programas;
                        })
                        .catch(error => console.error('Error cargando programas:', error));
                        
                    this.$watch('currentStep', () => {
                        const firstInput = document.querySelector(`.step-section[data-section='${this.currentStep}'] input:first-of-type`);
                        if (firstInput) setTimeout(() => firstInput.focus(), 300);
                    });
                }
            }"
            class="w-full max-w-2xl bg-white rounded-lg shadow-lg overflow-hidden"
        >
            <div class="bg-green-600 text-white p-6">
                <div class="flex items-center justify-center mb-6">
                    <img src="{{ asset('img/logo/logoSena.png') }}" alt="Logo SENA" class="h-16">
                </div>
                <h2 class="text-2xl font-bold text-center">Registro de Aprendiz</h2>
                
                <!-- Progress bar -->
                <div class="w-full bg-green-800 h-2 rounded-full mt-6">
                    <div class="bg-white h-2 rounded-full transition-all duration-300 ease-in-out" 
                        :style="`width: ${progress()}%`"></div>
                </div>
                
                <!-- Steps indicators -->
                <div class="flex justify-between mt-4">
                    <template x-for="step in totalSteps" :key="step">
                        <div class="flex flex-col items-center cursor-pointer" @click="goToStep(step)">
                            <div 
                                class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 ease-in-out"
                                :class="{
                                    'bg-white text-green-600 font-bold': currentStep === step,
                                    'bg-green-500 text-white': currentStep > step,
                                    'bg-green-800 text-white': currentStep < step
                                }"
                            >
                                <span x-show="currentStep > step">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </span>
                                <span x-show="currentStep <= step" x-text="step"></span>
                            </div>
                            <span class="text-sm mt-2" x-text="
                                step === 1 ? 'Datos' : 
                                step === 2 ? 'Programa' : 
                                'Dispositivo'
                            "></span>
                        </div>
                    </template>
                </div>
            </div>

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="p-6" id="registerForm" autocomplete="off">
                @csrf
                
                <!-- Sección 1: Datos Personales -->
                <div class="step-section" data-section="1" x-show="currentStep === 1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="nombres_completos" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Nombres Completos
                            </label>
                            <input type="text" id="nombres_completos" name="nombres_completos" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                value="{{ old('nombres_completos') }}" placeholder="Ingresa tus nombres completos" 
                                autocomplete="off">
                        </div>

                        <div class="space-y-1">
                            <label for="documento_identidad" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                </svg>
                                Documento de Identidad
                            </label>
                            <input type="text" id="documento_identidad" name="documento_identidad" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                value="{{ old('documento_identidad') }}" placeholder="Ingresa tu documento" 
                                pattern="[0-9]*" inputmode="numeric">
                        </div>

                        <div class="space-y-1">
                            <label for="correo" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Correo Electrónico
                            </label>
                            <input type="email" id="correo" name="correo" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                value="{{ old('correo') }}" placeholder="correo@ejemplo.com"
                                inputmode="email">
                        </div>

                        <div class="space-y-1">
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Contraseña
                            </label>
                            <div class="relative">
                                <input 
                                    :type="showPassword ? 'text' : 'password'" 
                                    id="password" 
                                    name="password" 
                                    required
                                    placeholder="Ingresa tu contraseña"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                    autocomplete="new-password">
                                <button 
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-700"
                                    tabindex="-1">
                                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1 md:col-span-2">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Confirmar Contraseña
                            </label>
                            <div class="relative">
                                <input 
                                    :type="showConfirmPassword ? 'text' : 'password'" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    required
                                    placeholder="Confirma tu contraseña"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                    autocomplete="new-password">
                                <button 
                                    type="button"
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-700"
                                    tabindex="-1">
                                    <svg x-show="!showConfirmPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="showConfirmPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección 2: Datos del Programa -->
                <div class="step-section" data-section="2" x-show="currentStep === 2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="nombre_programa" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path>
                                </svg>
                                Nombre del Programa
                            </label>
                            <input type="text" id="nombre_programa" name="nombre_programa" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                value="{{ old('nombre_programa') }}" placeholder="Ej: Análisis y Desarrollo de Software"
                                autocomplete="off" list="programas-list">
                            <datalist id="programas-list">
                                <template x-for="programa in programasData" :key="programa.nombre">
                                    <option :value="programa.nombre"></option>
                                </template>
                            </datalist>
                        </div>

                        <div class="space-y-1">
                            <label for="nivel_formacion" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Nivel de Formación
                            </label>
                            <select id="nivel_formacion" name="nivel_formacion" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Selecciona un nivel</option>
                                <option value="tecnico" {{ old('nivel_formacion') == 'tecnico' ? 'selected' : '' }}>Técnico</option>
                                <option value="tecnologo" {{ old('nivel_formacion') == 'tecnologo' ? 'selected' : '' }}>Tecnólogo</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label for="numero_ficha" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                                Número de Ficha
                            </label>
                            <input type="text" id="numero_ficha" name="numero_ficha" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                value="{{ old('numero_ficha') }}" placeholder="Ej: 2557631"
                                pattern="[0-9]*" inputmode="numeric">
                        </div>

                        <div class="space-y-1">
                            <label for="numero_ambiente" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Número de Ambiente
                            </label>
                            <input type="text" id="numero_ambiente" name="numero_ambiente" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                value="{{ old('numero_ambiente') }}" placeholder="Ej: 301"
                                pattern="[0-9]*" inputmode="numeric">
                        </div>

                        <div class="space-y-1 md:col-span-2">
                            <label for="jornada_id" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Jornada
                            </label>
                            <select id="jornada_id" name="jornada_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
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
                <div class="step-section" data-section="3" x-show="currentStep === 3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="marca" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Marca del Equipo
                            </label>
                            <input type="text" id="marca" name="marca" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                value="{{ old('marca') }}" placeholder="Ej: Lenovo, HP, Dell">
                        </div>

                        <div class="space-y-1">
                            <label for="serial" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Número de Serial
                            </label>
                            <input type="text" id="serial" name="serial" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                value="{{ old('serial') }}" placeholder="Ingresa el número de serial">
                        </div>

                        <div class="space-y-1 md:col-span-2" x-data="{ preview: null }">
                            <label for="foto_serial" class="block text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5 inline mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Foto del Serial
                            </label>
                            <div 
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md cursor-pointer hover:bg-gray-50 transition-colors"
                                @click="document.getElementById('foto_serial').click()"
                            >
                                <div class="space-y-1 text-center">
                                    <div x-show="!preview">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="text-sm text-gray-500">Haz clic para subir una imagen</p>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF hasta 10MB</p>
                                    </div>
                                    <div x-show="preview" class="relative">
                                        <img :src="preview" alt="Vista previa" class="max-h-48 mx-auto">
                                        <button 
                                            type="button" 
                                            class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1"
                                            @click.stop="preview = null; document.getElementById('foto_serial').value = ''">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input id="foto_serial" name="foto_serial" type="file" accept="image/*" required
                                class="sr-only" @change="const file = $event.target.files[0]; 
                                if(file) { 
                                    const reader = new FileReader();
                                    reader.onload = e => preview = e.target.result;
                                    reader.readAsDataURL(file);
                                }">
                        </div>
                    </div>
                </div>
                
                @if ($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mt-6" role="alert">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="flex justify-between mt-8">
                    <button 
                        type="button" 
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 transition-colors"
                        x-show="currentStep > 1"
                        @click="prevStep()"
                    >
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Anterior
                    </button>
                    
                    <button 
                        type="button" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors ml-auto"
                        x-show="currentStep < totalSteps"
                        @click="nextStep()"
                    >
                        Siguiente
                        <svg class="w-5 h-5 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors"
                        x-show="currentStep === totalSteps"
                    >
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Registrar Aprendiz
                    </button>
                </div>
                
                <div class="mt-6 text-center">
                    <p class="text-gray-600">¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="text-green-600 hover:text-green-800 font-medium">Iniciar Sesión</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>