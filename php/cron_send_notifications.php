<?php
/**
 * Script para enviar notificaciones pendientes
 * Debe ejecutarse periódicamente vía cron job de Hostinger
 *
 * Configurar en cPanel > Cron Jobs:
 * */5 * * * * /usr/bin/php /home/usuario/public_html/cron_send_notifications.php
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

// Obtener trabajos completados o con error que no han sido notificados
$db = getDB();

try {
    $stmt = $db->prepare("
        SELECT j.*, u.nombre, u.apellidos, u.email
        FROM jobs j
        INNER JOIN users u ON j.user_id = u.id
        WHERE j.notified = FALSE
        AND j.status IN ('completed', 'error')
        AND u.email_verified = TRUE
        LIMIT 10
    ");

    $stmt->execute();
    $jobs = $stmt->fetchAll();

    echo "Trabajos pendientes de notificación: " . count($jobs) . "\n";

    foreach ($jobs as $job) {
        echo "Enviando notificación para job: " . $job['job_id'] . "...";

        try {
            // Enviar email
            sendCompletedEmail(
                $job['email'],
                $job['nombre'],
                $job['job_id'],
                $job['status'],
                $job['error_message']
            );

            // Marcar como notificado
            $updateStmt = $db->prepare("UPDATE jobs SET notified = TRUE WHERE id = ?");
            $updateStmt->execute([$job['id']]);

            echo " ✓\n";

        } catch (Exception $e) {
            echo " ✗ Error: " . $e->getMessage() . "\n";
        }
    }

    echo "\nNotificaciones enviadas: " . count($jobs) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
