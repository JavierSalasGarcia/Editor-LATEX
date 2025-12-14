<?php
/**
 * Descarga de archivo ZIP procesado
 * Contiene: archivos LaTeX + PDF
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

if (empty($job['zip_filename'])) {
    http_response_code(404);
    die("Archivo ZIP no encontrado");
}

// Ruta al archivo ZIP en processed/
$zipPath = __DIR__ . '/processed/' . $job['zip_filename'];

if (!file_exists($zipPath)) {
    http_response_code(404);
    die("Archivo ZIP no existe en el servidor");
}

// Preparar nombre del archivo para descarga
$filename = pathinfo($job['filename_original'], PATHINFO_FILENAME);
$downloadName = $filename . '_procesado.zip';

// Obtener tamaño del archivo
$fileSize = filesize($zipPath);

// Enviar headers para descarga
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Enviar contenido del archivo ZIP
readfile($zipPath);
exit;
