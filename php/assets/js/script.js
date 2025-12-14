// Manejo del formulario de carga
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('document');
    const fileInfo = document.getElementById('fileInfo');
    const uploadForm = document.getElementById('uploadForm');

    if (!uploadArea || !fileInput) return;

    // Click en el área de carga
    uploadArea.addEventListener('click', function(e) {
        if (e.target !== fileInput) {
            fileInput.click();
        }
    });

    // Drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function() {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');

        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect();
        }
    });

    // Selección de archivo
    fileInput.addEventListener('change', handleFileSelect);

    function handleFileSelect() {
        const file = fileInput.files[0];
        if (file) {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            const fileName = file.name;
            const fileExt = fileName.split('.').pop().toLowerCase();

            let icon = '📄';
            if (fileExt === 'tex') icon = '📝';
            if (fileExt === 'doc' || fileExt === 'docx') icon = '📘';

            fileInfo.innerHTML = `
                <strong>${icon} Archivo seleccionado:</strong><br>
                ${fileName} (${fileSize} MB)
            `;
            fileInfo.classList.add('show');
        }
    }

    // Validación del formulario
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const apellidos = document.getElementById('apellidos').value.trim();
            const email = document.getElementById('email').value.trim();
            const file = fileInput.files[0];

            if (!nombre || !apellidos || !email || !file) {
                e.preventDefault();
                alert('Por favor completa todos los campos y selecciona un archivo');
                return false;
            }

            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Por favor ingresa un email válido');
                return false;
            }

            // Validar tamaño de archivo (50 MB)
            if (file.size > 50 * 1024 * 1024) {
                e.preventDefault();
                alert('El archivo es demasiado grande (máximo 50 MB)');
                return false;
            }

            // Validar extensión
            const allowedExts = ['doc', 'docx', 'tex'];
            const fileExt = file.name.split('.').pop().toLowerCase();
            if (!allowedExts.includes(fileExt)) {
                e.preventDefault();
                alert('Tipo de archivo no permitido. Solo se aceptan: ' + allowedExts.join(', '));
                return false;
            }

            // Deshabilitar botón para evitar doble envío
            const submitBtn = uploadForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Procesando...';
            }
        });
    }
});
