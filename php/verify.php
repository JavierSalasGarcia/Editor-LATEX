<?php
/**
 * Verificación de email
 */

require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$token = $_GET['token'] ?? '';
$success = false;
$error = null;

if (empty($token)) {
    $error = "Token de verificación no válido";
} else {
    $success = verifyEmail($token);
    if (!$success) {
        $error = "Token de verificación inválido o expirado";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Email</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="verify-container">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <h2>✓ Email Verificado</h2>
                    <p>Tu correo electrónico ha sido verificado exitosamente.</p>
                    <p>Ahora recibirás notificaciones cuando tus documentos estén procesados.</p>
                    <a href="index.php" class="btn btn-primary">Ir al Inicio</a>
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    <h2>✗ Error de Verificación</h2>
                    <p><?php echo htmlspecialchars($error); ?></p>
                    <a href="index.php" class="btn btn-primary">Ir al Inicio</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
