<?php
/**
 * Descarga de PDF procesado
 */

require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$jobId = $_GET['job'] ?? '';

if (empty($jobId)) {
    http_response_code(400);
    die("ID de trabajo no proporcionado");
}

$job = getJob($jobId);

if (!$job) {
    http_response_code(404);
    die("Trabajo no encontrado");
}

if ($job['status'] !== 'completed') {
    http_response_code(400);
    die("El documento aún no está listo para descargar");
}

if (empty($job['pdf_data'])) {
    http_response_code(404);
    die("PDF no encontrado");
}

// Preparar nombre del archivo
$filename = pathinfo($job['filename_original'], PATHINFO_FILENAME);
$downloadName = $filename . '_procesado.pdf';

// Enviar headers para descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . $job['pdf_size']);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enviar contenido del PDF
echo $job['pdf_data'];
exit;
