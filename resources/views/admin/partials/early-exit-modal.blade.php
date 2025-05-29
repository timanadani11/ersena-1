<!-- Modal para Salidas Anticipadas -->
<div id="early-exit-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Salida anticipada</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div class="alert alert-warning">
                <p>El aprendiz está intentando salir antes de completar su jornada establecida.</p>
                <p><strong>Hora actual:</strong> <span id="hora-actual"></span></p>
                <p><strong>Hora de salida permitida:</strong> <span id="hora-salida"></span></p>
            </div>
            
            <form id="early-exit-form" enctype="multipart/form-data">
                <input type="hidden" id="documento-hidden" name="documento_identidad">
                <input type="hidden" name="tipo" value="salida">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                
                <div class="form-group">
                    <label for="motivo-salida">Motivo de salida anticipada:</label>
                    <select id="motivo-salida" name="motivo" class="form-control" required>
                        <option value="">Seleccione un motivo</option>
                        <option value="coordinacion">Autorización de Coordinación</option>
                        <option value="entrenamiento">Entrenamiento Deportivo</option>
                        <option value="otro">Otro motivo</option>
                    </select>
                </div>
                
                <div id="observaciones-container" class="form-group">
                    <label for="observaciones">Observaciones:</label>
                    <textarea id="observaciones" name="observaciones" class="form-control" rows="3" placeholder="Detalle el motivo de la salida anticipada"></textarea>
                </div>
                
                <div id="autorizacion-container" class="form-group">
                    <label for="foto-autorizacion">Foto de autorización:</label>
                    <div class="file-upload-container">
                        <input type="file" id="foto-autorizacion" name="foto_autorizacion" class="file-input" accept="image/*" capture="camera">
                        <div class="file-upload-button">
                            <i class="fas fa-camera"></i> Tomar foto
                        </div>
                        <span class="file-name">Ningún archivo seleccionado</span>
                    </div>
                    <div id="preview-container" style="display: none; margin-top: 10px;">
                        <img id="image-preview" src="" alt="Vista previa" style="max-width: 100%; height: auto; border-radius: 8px;">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="btn-cancelar">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-autorizar">
                        <span class="loader"></span>
                        <i class="fas fa-check"></i> Autorizar salida
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 0;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    width: 90%;
    max-width: 500px;
    animation: modalFadeIn 0.3s;
}

@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-50px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
    border-radius: 12px 12px 0 0;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 10px;
    color: #dc3545;
}

.modal-body {
    padding: 20px;
}

.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #000;
}

.form-group {
    margin-bottom: 20px;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 16px;
}

.file-upload-container {
    position: relative;
    overflow: hidden;
    display: inline-block;
    width: 100%;
}

.file-input {
    position: absolute;
    font-size: 100px;
    opacity: 0;
    right: 0;
    top: 0;
    cursor: pointer;
    height: 100%;
    width: 100%;
}

.file-upload-button {
    display: inline-block;
    padding: 10px 15px;
    background-color: #4CAF50;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    margin-right: 10px;
}

.file-name {
    font-size: 14px;
    color: #6c757d;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 6px;
}

.alert-warning {
    background-color: #fff3cd;
    border: 1px solid #ffecb5;
    color: #856404;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('early-exit-modal');
    const closeBtn = document.querySelector('.close');
    const cancelBtn = document.getElementById('btn-cancelar');
    const fileInput = document.getElementById('foto-autorizacion');
    const fileName = document.querySelector('.file-name');
    const imagePreview = document.getElementById('image-preview');
    const previewContainer = document.getElementById('preview-container');
    const form = document.getElementById('early-exit-form');
    const motivoSelect = document.getElementById('motivo-salida');
    const autorizacionContainer = document.getElementById('autorizacion-container');
    
    // Cerrar modal
    function closeModal() {
        modal.style.display = "none";
    }
    
    closeBtn.onclick = closeModal;
    cancelBtn.onclick = closeModal;
    
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
    
    // Mostrar nombre del archivo seleccionado
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            fileName.textContent = this.files[0].name;
            
            // Mostrar vista previa de la imagen
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                previewContainer.style.display = "block";
            }
            reader.readAsDataURL(this.files[0]);
        } else {
            fileName.textContent = "Ningún archivo seleccionado";
            previewContainer.style.display = "none";
        }
    });
    
    // Cambiar requisitos según el motivo seleccionado
    motivoSelect.addEventListener('change', function() {
        if (this.value === 'entrenamiento') {
            autorizacionContainer.style.display = 'none';
            document.getElementById('foto-autorizacion').removeAttribute('required');
        } else {
            autorizacionContainer.style.display = 'block';
            if (this.value === 'coordinacion') {
                document.getElementById('foto-autorizacion').setAttribute('required', 'required');
            } else {
                document.getElementById('foto-autorizacion').removeAttribute('required');
            }
        }
    });
    
    // Enviar formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        mostrarCargando('btn-autorizar', true);
        
        $.ajax({
            url: '{{ route("admin.registrar-asistencia") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                mostrarCargando('btn-autorizar', false);
                closeModal();
                showNotification('Salida autorizada correctamente', 'success');
                
                // Actualizar interfaz
                document.getElementById('scan-status').textContent = 'Salida autorizada correctamente';
                document.getElementById('scan-status').classList.add('success');
                
                // Ocultar botón de salida
                document.getElementById('btn-salida').style.display = 'none';
                
                // Actualizar datos locales
                if (typeof currentUserData !== 'undefined') {
                    currentUserData.puede_registrar_salida = false;
                }
            },
            error: function(error) {
                mostrarCargando('btn-autorizar', false);
                const errorMsg = error.responseJSON?.error || 'Error al autorizar salida';
                showNotification(errorMsg, 'error');
            }
        });
    });
});

// Función para abrir el modal de salida anticipada
function abrirModalSalidaAnticipada(documento) {
    const modal = document.getElementById('early-exit-modal');
    document.getElementById('documento-hidden').value = documento;
    
    // Mostrar hora de salida si disponible
    let horaSalida = 'No definida';
    if (currentUserData?.user?.jornada?.hora_salida) {
        horaSalida = currentUserData.user.jornada.hora_salida;
    }
    document.getElementById('hora-salida').textContent = horaSalida;
    
    // Mostrar hora actual
    const ahora = new Date();
    document.getElementById('hora-actual').textContent = ahora.toLocaleTimeString('es-CO', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
    
    // Resetear el formulario
    document.getElementById('early-exit-form').reset();
    document.getElementById('preview-container').style.display = 'none';
    document.querySelector('.file-name').textContent = 'Ningún archivo seleccionado';
    document.getElementById('autorizacion-container').style.display = 'block';
    
    modal.style.display = 'block';
}
</script> 