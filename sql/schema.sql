-- Base de datos para el sistema de procesamiento de documentos
-- Para las 4 revistas: IDEAS, INFORMATICAE, ESTELAC, TECING

CREATE DATABASE IF NOT EXISTS editor_latex CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE editor_latex;

-- Tabla de usuarios registrados
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(64),
    verification_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_verification_token (verification_token)
) ENGINE=InnoDB;

-- Tabla de configuración por revista
CREATE TABLE IF NOT EXISTS revista_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    nombre_completo VARCHAR(255) NOT NULL,
    compilador ENUM('pdflatex', 'xelatex') NOT NULL DEFAULT 'pdflatex',
    plantilla_folder VARCHAR(100) NOT NULL COMMENT 'Nombre de la carpeta en plantillas/',
    plantilla_main VARCHAR(100) NOT NULL DEFAULT 'main.tex' COMMENT 'Archivo .tex principal',
    volumen INT NOT NULL DEFAULT 1,
    año INT NOT NULL DEFAULT 2025,
    numero INT NOT NULL DEFAULT 1,
    pagina_inicial INT NOT NULL DEFAULT 1,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insertar configuración inicial de las 4 revistas
-- Nota: plantilla_folder es el nombre de la carpeta en plantillas/
-- Cada carpeta contiene: main.tex, revista.cls, logos/, figuras/
INSERT INTO revista_config (codigo, nombre, nombre_completo, compilador, plantilla_folder) VALUES
('ideas', 'IDEAS', 'Revista IDEAS', 'pdflatex', 'ideas'),
('informaticae', 'INFORMATICAE', 'Revista INFORMATICAE', 'xelatex', 'informaticae'),
('estelac', 'ESTELAC', 'Revista ESTELAC', 'pdflatex', 'estelac'),
('tecing', 'TECING', 'Revista TECING', 'xelatex', 'tecing');

-- Tabla de trabajos de procesamiento
-- Los archivos se guardan en el filesystem, no en MySQL
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id VARCHAR(36) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    revista_codigo VARCHAR(20) NOT NULL,
    filename_original VARCHAR(255) NOT NULL COMMENT 'Nombre original del archivo subido',
    file_extension VARCHAR(10) NOT NULL,
    upload_filename VARCHAR(255) NOT NULL COMMENT 'Nombre del archivo en uploads/',
    file_size INT NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'error') DEFAULT 'pending',
    error_message TEXT,
    zip_filename VARCHAR(255) COMMENT 'Nombre del archivo ZIP en processed/ (contiene LaTeX + PDF)',
    zip_size INT COMMENT 'Tamaño del ZIP en bytes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    started_at DATETIME,
    completed_at DATETIME,
    notified BOOLEAN DEFAULT FALSE,
    delete_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (revista_codigo) REFERENCES revista_config(codigo),
    INDEX idx_job_id (job_id),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_delete_at (delete_at)
) ENGINE=InnoDB;

-- Tabla de logs de procesamiento
CREATE TABLE IF NOT EXISTS processing_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id VARCHAR(36) NOT NULL,
    log_level ENUM('info', 'warning', 'error') DEFAULT 'info',
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(job_id) ON DELETE CASCADE,
    INDEX idx_job_id (job_id)
) ENGINE=InnoDB;

-- Vista para consultas comunes
CREATE VIEW v_jobs_with_config AS
SELECT
    j.*,
    u.nombre,
    u.apellidos,
    u.email,
    r.nombre as revista_nombre,
    r.nombre_completo as revista_nombre_completo,
    r.compilador,
    r.plantilla_folder,
    r.plantilla_main,
    r.volumen,
    r.año,
    r.numero,
    r.pagina_inicial
FROM jobs j
INNER JOIN users u ON j.user_id = u.id
INNER JOIN revista_config r ON j.revista_codigo = r.codigo;

-- Procedimiento para limpiar trabajos antiguos (>30 días)
-- IMPORTANTE: También debe limpiar archivos del filesystem
DELIMITER //
CREATE PROCEDURE clean_old_jobs()
BEGIN
    -- Marcar trabajos para eliminación
    -- El script de limpieza PHP debe leer estos registros ANTES de eliminarlos
    -- para borrar los archivos del filesystem

    -- Trabajos completados hace más de 30 días
    UPDATE jobs
    SET delete_at = NOW()
    WHERE status = 'completed'
    AND completed_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    AND delete_at IS NULL;

    -- Trabajos con error hace más de 7 días
    UPDATE jobs
    SET delete_at = NOW()
    WHERE status = 'error'
    AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
    AND delete_at IS NULL;

    -- Nota: Los archivos deben eliminarse manualmente por un script PHP
    -- antes de eliminar los registros de la base de datos
END //
DELIMITER ;

-- Evento para limpieza automática (se ejecuta diariamente)
CREATE EVENT IF NOT EXISTS daily_cleanup
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE + INTERVAL 1 DAY
DO CALL clean_old_jobs();
