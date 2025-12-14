<?php
/**
 * Funciones auxiliares del sistema
 */

/**
 * Genera un UUID v4
 */
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Genera un token de verificación seguro
 */
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Obtiene o crea un usuario
 */
function getOrCreateUser($nombre, $apellidos, $email) {
    $db = getDB();

    // Buscar usuario existente
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        return $user;
    }

    // Crear nuevo usuario
    $token = generateVerificationToken();
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $stmt = $db->prepare("
        INSERT INTO users (nombre, apellidos, email, verification_token, verification_expires)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nombre, $apellidos, $email, $token, $expires]);

    $userId = $db->lastInsertId();

    // Enviar email de verificación
    sendVerificationEmail($email, $nombre, $token);

    return [
        'id' => $userId,
        'nombre' => $nombre,
        'apellidos' => $apellidos,
        'email' => $email,
        'email_verified' => false,
        'verification_token' => $token
    ];
}

/**
 * Verifica un email
 */
function verifyEmail($token) {
    $db = getDB();

    $stmt = $db->prepare("
        SELECT * FROM users
        WHERE verification_token = ?
        AND verification_expires > NOW()
        AND email_verified = FALSE
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    // Marcar como verificado
    $stmt = $db->prepare("
        UPDATE users
        SET email_verified = TRUE,
            verification_token = NULL,
            verification_expires = NULL
        WHERE id = ?
    ");
    $stmt->execute([$user['id']]);

    return true;
}

/**
 * Envía email de verificación
 */
function sendVerificationEmail($email, $nombre, $token) {
    $verifyUrl = BASE_URL . "/verify.php?token=" . $token;

    $subject = "Verifica tu correo electrónico";
    $message = "
        <html>
        <head><title>Verificación de Email</title></head>
        <body>
            <h2>Hola $nombre,</h2>
            <p>Gracias por registrarte. Por favor verifica tu correo haciendo clic en el siguiente enlace:</p>
            <p><a href='$verifyUrl'>Verificar Email</a></p>
            <p>O copia este enlace en tu navegador:</p>
            <p>$verifyUrl</p>
            <p>Este enlace expira en 24 horas.</p>
            <br>
            <p>Si no solicitaste esto, ignora este email.</p>
        </body>
        </html>
    ";

    sendEmail($email, $subject, $message);
}

/**
 * Envía email de notificación de procesamiento completado
 */
function sendCompletedEmail($email, $nombre, $jobId, $status, $errorMessage = null) {
    $downloadUrl = BASE_URL . "/download.php?job=" . $jobId;

    $subject = ($status === 'completed')
        ? "Tu documento ha sido procesado"
        : "Error al procesar tu documento";

    if ($status === 'completed') {
        $message = "
            <html>
            <head><title>Documento Procesado</title></head>
            <body>
                <h2>Hola $nombre,</h2>
                <p>¡Tu documento ha sido procesado exitosamente!</p>
                <p><strong>Descargarás un archivo ZIP que contiene:</strong></p>
                <ul>
                    <li>📄 Archivos LaTeX (.tex, .cls, logos, figuras)</li>
                    <li>📕 PDF compilado listo para publicación</li>
                </ul>
                <p><a href='$downloadUrl' style='display:inline-block;padding:10px 20px;background:#4CAF50;color:white;text-decoration:none;'>Descargar ZIP (LaTeX + PDF)</a></p>
                <p>O copia este enlace en tu navegador:</p>
                <p>$downloadUrl</p>
                <p>El archivo estará disponible por 30 días.</p>
            </body>
            </html>
        ";
    } else {
        $message = "
            <html>
            <head><title>Error al Procesar</title></head>
            <body>
                <h2>Hola $nombre,</h2>
                <p>Hubo un error al procesar tu documento:</p>
                <p style='color:red;'>$errorMessage</p>
                <p>Por favor, intenta subir el documento nuevamente o contacta al soporte técnico.</p>
            </body>
            </html>
        ";
    }

    sendEmail($email, $subject, $message);
}

/**
 * Función genérica para enviar emails usando mail() de PHP
 * En Hostinger suele funcionar directamente
 */
function sendEmail($to, $subject, $htmlMessage) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";

    return mail($to, $subject, $htmlMessage, $headers);
}

/**
 * Obtiene configuración de la revista actual
 */
function getRevistaConfig() {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM revista_config WHERE codigo = ? AND activa = TRUE");
    $stmt->execute([REVISTA_CODIGO]);
    return $stmt->fetch();
}

/**
 * Crea un nuevo trabajo de procesamiento
 * Guarda archivo en filesystem (uploads/) y metadata en MySQL
 */
function createJob($userId, $revistaConfig, $file) {
    $db = getDB();

    $jobId = generateUUID();
    $filename = $file['name'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $fileSize = $file['size'];

    // Nombre del archivo en uploads/
    $uploadFilename = $jobId . '.' . $extension;
    $uploadPath = __DIR__ . '/../uploads/' . $uploadFilename;

    // Asegurar que existe la carpeta uploads/
    $uploadsDir = __DIR__ . '/../uploads';
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    // Mover archivo del temporal a uploads/
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception("Error al guardar el archivo");
    }

    // Calcular fecha de eliminación (30 días)
    $deleteAt = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Guardar metadata en MySQL (NO el archivo completo)
    $stmt = $db->prepare("
        INSERT INTO jobs (
            job_id, user_id, revista_codigo, filename_original,
            file_extension, upload_filename, file_size, delete_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $jobId,
        $userId,
        $revistaConfig['codigo'],
        $filename,
        $extension,
        $uploadFilename,
        $fileSize,
        $deleteAt
    ]);

    return $jobId;
}

/**
 * Obtiene información de un trabajo
 */
function getJob($jobId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM v_jobs_with_config WHERE job_id = ?");
    $stmt->execute([$jobId]);
    return $stmt->fetch();
}

/**
 * Valida archivo subido
 */
function validateFile($file) {
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error al subir el archivo";
        return $errors;
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        $errors[] = "El archivo es demasiado grande (máximo " . (MAX_FILE_SIZE / 1024 / 1024) . " MB)";
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        $errors[] = "Tipo de archivo no permitido. Solo: " . implode(', ', ALLOWED_EXTENSIONS);
    }

    return $errors;
}

/**
 * Sanitiza entrada de usuario
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
