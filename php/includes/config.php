<?php
/**
 * Archivo de configuración principal
 * IMPORTANTE: Ajustar estos valores según tu configuración de Hostinger
 */

// Configuración de base de datos
define('DB_HOST', 'localhost');  // En Hostinger suele ser localhost
define('DB_NAME', 'editor_latex');
define('DB_USER', 'tu_usuario_mysql');
define('DB_PASS', 'tu_password_mysql');
define('DB_CHARSET', 'utf8mb4');

// Configuración de la revista (se debe setear por sitio web)
// Puedes usar un archivo .env o configurarlo directamente aquí
define('REVISTA_CODIGO', getenv('REVISTA_CODIGO') ?: 'ideas'); // ideas, informaticae, estelac, tecing

// Configuración de archivos
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50 MB
define('ALLOWED_EXTENSIONS', ['doc', 'docx', 'tex']);

// Configuración de email (SMTP)
define('SMTP_HOST', 'smtp.hostinger.com'); // Ajustar según tu proveedor
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@tudominio.com');
define('SMTP_PASS', 'tu_password_smtp');
define('SMTP_FROM_EMAIL', 'noreply@tudominio.com');
define('SMTP_FROM_NAME', 'Sistema de Procesamiento - Revista');

// URL base del sitio
define('BASE_URL', 'https://tudominio.com'); // Cambiar por tu dominio

// Zona horaria
date_default_timezone_set('America/Mexico_City'); // Ajustar según tu ubicación

// Configuración de sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Solo si usas HTTPS

// Mostrar errores (solo en desarrollo)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
