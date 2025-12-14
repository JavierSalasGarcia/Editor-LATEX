<?php
/**
 * Página para verificar estado de un trabajo
 */

require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$jobId = $_GET['job'] ?? '';
$job = null;
$error = null;

if (empty($jobId)) {
    $error = "ID de trabajo no proporcionado";
} else {
    $job = getJob($jobId);
    if (!$job) {
        $error = "Trabajo no encontrado";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado del Trabajo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if ($job && $job['status'] === 'pending'): ?>
        <meta http-equiv="refresh" content="10">
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <h1>Estado del Procesamiento</h1>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="index.php" class="btn btn-primary">Volver al Inicio</a>
            </div>
        <?php elseif ($job): ?>
            <div class="job-status">
                <div class="status-header">
                    <h2><?php echo htmlspecialchars($job['filename_original']); ?></h2>
                    <p><strong>ID:</strong> <code><?php echo htmlspecialchars($job['job_id']); ?></code></p>
                    <p><strong>Revista:</strong> <?php echo htmlspecialchars($job['revista_nombre_completo']); ?></p>
                </div>

                <div class="status-badge status-<?php echo $job['status']; ?>">
                    <?php
                    $statusLabels = [
                        'pending' => '⏳ En Cola',
                        'processing' => '⚙️ Procesando',
                        'completed' => '✓ Completado',
                        'error' => '✗ Error'
                    ];
                    echo $statusLabels[$job['status']];
                    ?>
                </div>

                <div class="status-details">
                    <p><strong>Enviado:</strong> <?php echo date('d/m/Y H:i', strtotime($job['created_at'])); ?></p>

                    <?php if ($job['started_at']): ?>
                        <p><strong>Iniciado:</strong> <?php echo date('d/m/Y H:i', strtotime($job['started_at'])); ?></p>
                    <?php endif; ?>

                    <?php if ($job['completed_at']): ?>
                        <p><strong>Completado:</strong> <?php echo date('d/m/Y H:i', strtotime($job['completed_at'])); ?></p>
                    <?php endif; ?>

                    <?php if ($job['delete_at']): ?>
                        <p><strong>Disponible hasta:</strong> <?php echo date('d/m/Y', strtotime($job['delete_at'])); ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($job['status'] === 'pending'): ?>
                    <div class="alert alert-info">
                        <p><strong>⏰ Tu documento está en cola</strong></p>
                        <p>El procesamiento se realiza de 8:00 AM a 5:00 PM.</p>
                        <p>Recibirás un email cuando esté listo.</p>
                        <p><small>Esta página se actualiza automáticamente cada 10 segundos.</small></p>
                    </div>
                <?php elseif ($job['status'] === 'processing'): ?>
                    <div class="alert alert-info">
                        <p><strong>⚙️ Procesando documento...</strong></p>
                        <p>Esto puede tomar algunos minutos dependiendo del tamaño del documento.</p>
                        <p><small>Esta página se actualiza automáticamente.</small></p>
                    </div>
                <?php elseif ($job['status'] === 'completed'): ?>
                    <div class="alert alert-success">
                        <p><strong>✓ Tu documento ha sido procesado exitosamente</strong></p>
                        <a href="download.php?job=<?php echo urlencode($job['job_id']); ?>" class="btn btn-primary btn-large">
                            📥 Descargar PDF
                        </a>
                    </div>
                <?php elseif ($job['status'] === 'error'): ?>
                    <div class="alert alert-error">
                        <p><strong>✗ Error al procesar el documento</strong></p>
                        <?php if ($job['error_message']): ?>
                            <p>Detalle: <?php echo htmlspecialchars($job['error_message']); ?></p>
                        <?php endif; ?>
                        <p>Por favor, intenta subir el documento nuevamente o contacta al soporte.</p>
                    </div>
                <?php endif; ?>

                <div class="actions">
                    <a href="index.php" class="btn btn-secondary">Procesar Otro Documento</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
