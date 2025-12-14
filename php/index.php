<?php
/**
 * Página principal - Formulario de carga de documentos
 */

require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

session_start();

// Obtener configuración de la revista
$revista = getRevistaConfig();

if (!$revista) {
    die("Error: Configuración de revista no encontrada");
}

// Manejar envío del formulario
$errors = [];
$success = false;
$jobId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar datos del usuario
    $nombre = sanitize($_POST['nombre'] ?? '');
    $apellidos = sanitize($_POST['apellidos'] ?? '');
    $email = sanitize($_POST['email'] ?? '');

    if (empty($nombre)) $errors[] = "El nombre es requerido";
    if (empty($apellidos)) $errors[] = "Los apellidos son requeridos";
    if (empty($email)) $errors[] = "El email es requerido";
    if (!isValidEmail($email)) $errors[] = "Email inválido";

    // Validar archivo
    if (!isset($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Debes seleccionar un archivo";
    } else {
        $fileErrors = validateFile($_FILES['document']);
        $errors = array_merge($errors, $fileErrors);
    }

    // Si no hay errores, procesar
    if (empty($errors)) {
        try {
            // Obtener o crear usuario
            $user = getOrCreateUser($nombre, $apellidos, $email);

            // Crear trabajo
            $jobId = createJob($user['id'], $revista, $_FILES['document']);

            $success = true;

            // Si el usuario no está verificado, mostrar mensaje
            if (!$user['email_verified']) {
                $_SESSION['show_verification_message'] = true;
            }

        } catch (Exception $e) {
            error_log("Error al crear trabajo: " . $e->getMessage());
            $errors[] = "Error al procesar el archivo. Intenta nuevamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($revista['nombre']); ?> - Procesador de Documentos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><?php echo htmlspecialchars($revista['nombre']); ?></h1>
            <p class="subtitle"><?php echo htmlspecialchars($revista['nombre_completo']); ?></p>
        </header>

        <div class="info-box">
            <h3>Información de la Revista</h3>
            <p><strong>Compilador:</strong> <?php echo htmlspecialchars($revista['compilador']); ?></p>
            <p>
                <strong>Volumen:</strong> <?php echo $revista['volumen']; ?> |
                <strong>Año:</strong> <?php echo $revista['año']; ?> |
                <strong>Número:</strong> <?php echo $revista['numero']; ?>
            </p>
            <p><strong>Formatos aceptados:</strong> .doc, .docx, .tex</p>
            <p><strong>Tamaño máximo:</strong> <?php echo MAX_FILE_SIZE / 1024 / 1024; ?> MB</p>
        </div>

        <?php if ($success && $jobId): ?>
            <div class="alert alert-success">
                <h3>✓ Archivo Cargado Exitosamente</h3>
                <p>Tu documento está en cola para ser procesado.</p>
                <p><strong>ID del trabajo:</strong> <code><?php echo htmlspecialchars($jobId); ?></code></p>

                <?php if (isset($_SESSION['show_verification_message']) && $_SESSION['show_verification_message']): ?>
                    <div class="verification-notice">
                        <p><strong>⚠ Verifica tu correo electrónico</strong></p>
                        <p>Te hemos enviado un email a <strong><?php echo htmlspecialchars($email); ?></strong> con un enlace de verificación.</p>
                        <p>Por favor verifica tu email para recibir notificaciones cuando tu documento esté listo.</p>
                    </div>
                    <?php unset($_SESSION['show_verification_message']); ?>
                <?php endif; ?>

                <div class="status-check">
                    <p>Puedes verificar el estado de tu documento aquí:</p>
                    <form action="status.php" method="get" style="display: inline;">
                        <input type="hidden" name="job" value="<?php echo htmlspecialchars($jobId); ?>">
                        <button type="submit" class="btn btn-secondary">Ver Estado</button>
                    </form>
                </div>

                <p class="info-text">
                    <small>
                        ⏰ El procesamiento se realiza de 8:00 AM a 5:00 PM.<br>
                        📧 Recibirás un email cuando tu documento esté listo.<br>
                        📁 Los archivos se mantienen disponibles por 30 días.
                    </small>
                </p>

                <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">Procesar Otro Documento</a>
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <h4>Errores:</h4>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                <div class="form-section">
                    <h3>Información Personal</h3>

                    <div class="form-group">
                        <label for="nombre">Nombre *</label>
                        <input type="text" id="nombre" name="nombre" required
                               value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="apellidos">Apellidos *</label>
                        <input type="text" id="apellidos" name="apellidos" required
                               value="<?php echo htmlspecialchars($_POST['apellidos'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico *</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <small>Recibirás una notificación cuando tu documento esté listo</small>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Documento a Procesar</h3>

                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">📄</div>
                        <div class="upload-text">
                            <strong>Haz clic o arrastra tu archivo aquí</strong><br>
                            <small>Documentos Word (.doc, .docx) o LaTeX (.tex)</small>
                        </div>
                        <input type="file" id="document" name="document" accept=".doc,.docx,.tex" required>
                        <div class="file-info" id="fileInfo"></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-large" id="submitBtn">
                    Procesar Documento
                </button>
            </form>
        <?php endif; ?>

        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($revista['nombre_completo']); ?></p>
        </footer>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
