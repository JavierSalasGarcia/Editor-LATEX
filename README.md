# Editor-LATEX

Sistema web completo para procesar documentos Word y LaTeX y convertirlos a PDFs con formato específico de revista usando Gemini AI.

## 🌟 Características

- ✅ **4 revistas soportadas:** IDEAS, INFORMATICAE, ESTELAC, TECING
- ✅ **Múltiples formatos:** Word (.doc, .docx) y LaTeX (.tex)
- ✅ **Procesamiento inteligente:** Gemini AI adapta documentos a plantillas
- ✅ **Compilación automática:** pdfLaTeX o XeLaTeX según la revista
- ✅ **Sistema de notificaciones:** Email SMTP cuando el PDF está listo
- ✅ **Verificación de email:** Sistema de registro con confirmación
- ✅ **Interfaz moderna:** Frontend responsive con drag & drop
- ✅ **Cola de trabajos:** MySQL como sistema de cola
- ✅ **Horario de trabajo:** Worker solo procesa de 8am a 5pm
- ✅ **Limpieza automática:** Archivos se eliminan después de 30 días

## 🏗️ Arquitectura

```
┌─────────────┐      ┌──────────────┐      ┌─────────────────┐
│   Usuario   │─────▶│  PHP + MySQL │─────▶│  MySQL Queue    │
│   Browser   │◀─────│  (Hostinger) │      │  (jobs table)   │
└─────────────┘      └──────────────┘      └────────┬────────┘
                                                     │
                                            Polling (30s)
                                                     │
                                                     ▼
                                          ┌──────────────────┐
                                          │ Windows Worker   │
                                          │ Python + Gemini  │
                                          │ + LaTeX          │
                                          └────────┬─────────┘
                                                   │
                                            ┌──────┴──────┐
                                            │             │
                                            ▼             ▼
                                    ┌─────────────┐  ┌─────────┐
                                    │ PDF (MySQL) │  │  Email  │
                                    └─────────────┘  └─────────┘
```

## 📁 Estructura del Proyecto

```
Editor-LATEX/
├── php/                          # Aplicación web PHP
│   ├── index.php                 # Formulario de carga
│   ├── status.php                # Ver estado de trabajo
│   ├── verify.php                # Verificación de email
│   ├── download.php              # Descargar PDF
│   ├── cron_send_notifications.php  # Cron para enviar emails
│   ├── .htaccess                 # Configuración Apache
│   ├── includes/
│   │   ├── config.php            # Configuración general
│   │   ├── database.php          # Conexión MySQL
│   │   └── functions.php         # Funciones auxiliares
│   └── assets/
│       ├── css/style.css         # Estilos
│       └── js/script.js          # JavaScript
├── windows_worker/               # Worker para Windows
│   ├── worker.py                 # Procesador principal
│   └── requirements.txt          # Dependencias Python
├── sql/
│   └── schema.sql                # Esquema de base de datos
├── plantillas/                   # Plantillas LaTeX
│   ├── plantilla_ideas.tex
│   ├── plantilla_informaticae.tex
│   ├── plantilla_estelac.tex
│   └── plantilla_tecing.tex
├── INSTALL.md                    # Guía de instalación completa
└── README.md                     # Este archivo
```

## 🚀 Inicio Rápido

### Requisitos Previos

**Servidor Web (Hostinger):**
- PHP 7.4+
- MySQL 5.7+
- Cron Jobs
- Acceso remoto a MySQL

**Laptop Windows:**
- Python 3.8+
- MiKTeX o TeX Live
- API Key de Gemini
- Encendida 8am-5pm

### Instalación

📖 **Ver [INSTALL.md](INSTALL.md) para la guía completa de instalación paso a paso.**

Resumen rápido:

1. **Subir archivos PHP a Hostinger**
2. **Crear base de datos MySQL**
3. **Importar `sql/schema.sql`**
4. **Configurar `php/includes/config.php`**
5. **Configurar Cron Job para notificaciones**
6. **Habilitar acceso remoto a MySQL**
7. **Instalar Python y MiKTeX en Windows**
8. **Configurar y ejecutar worker**

## 🎯 Flujo de Trabajo

1. **Usuario accede** a la web de la revista (ej: `https://ideas.tudominio.com`)
2. **Completa formulario** (nombre, email, archivo)
3. **Recibe email** de verificación
4. **Verifica su email** haciendo clic en el enlace
5. **Archivo se guarda** en MySQL como BLOB con status `pending`
6. **Worker de Windows** detecta trabajo pendiente (polling cada 30s)
7. **Gemini AI** procesa el documento y lo adapta a la plantilla
8. **LaTeX compila** el documento a PDF
9. **PDF se guarda** en MySQL
10. **Cron Job envía email** al usuario con link de descarga
11. **Usuario descarga** el PDF procesado

## 📊 Base de Datos

### Tablas principales:

- **`users`**: Usuarios registrados con verificación de email
- **`revista_config`**: Configuración de las 4 revistas
- **`jobs`**: Cola de trabajos de procesamiento
- **`processing_logs`**: Logs de procesamiento

### Limpieza automática:

- Trabajos completados: se eliminan después de 30 días
- Trabajos con error: se eliminan después de 7 días
- Evento MySQL ejecuta limpieza diariamente

## 🛠️ Configuración por Revista

Cada sitio web debe configurar su revista en `php/includes/config.php`:

```php
// Para IDEAS
define('REVISTA_CODIGO', 'ideas');

// Para INFORMATICAE
define('REVISTA_CODIGO', 'informaticae');

// Para ESTELAC
define('REVISTA_CODIGO', 'estelac');

// Para TECING
define('REVISTA_CODIGO', 'tecing');
```

Los metadatos (volumen, año, número, página) se configuran en la base de datos:

```sql
UPDATE revista_config
SET volumen = 5, año = 2025, numero = 2, pagina_inicial = 1
WHERE codigo = 'ideas';
```

## 🔐 Seguridad

- ✅ Validación de tipos de archivo
- ✅ Límite de tamaño (50 MB)
- ✅ Verificación de email obligatoria
- ✅ Protección de archivos de configuración (.htaccess)
- ✅ Conexión MySQL segura
- ✅ HTTPS recomendado
- ✅ Sanitización de inputs
- ✅ PDO con prepared statements

## 📧 Sistema de Notificaciones

### Email de verificación:
Se envía automáticamente al registrarse un nuevo usuario.

### Email de procesamiento completado:
- Enviado por cron job de PHP (cada 5 minutos)
- Solo a usuarios con email verificado
- Incluye link de descarga directo
- En caso de error, incluye mensaje descriptivo

### Configuración SMTP:

Editar en `php/includes/config.php`:

```php
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_USER', 'noreply@tudominio.com');
define('SMTP_PASS', 'password');
```

## 🧪 Testing

### Probar sitio web:
1. Ir a tu dominio
2. Completar formulario y subir archivo de prueba
3. Verificar email
4. Verificar que aparece en MySQL

### Probar worker:
```powershell
cd C:\Editor-LATEX\windows_worker
.\start_worker.bat
```

Deberías ver:
```
✓ Conectado a MySQL
✓ Worker iniciado. Esperando trabajos...
```

### Monitorear trabajos:

```sql
-- Ver todos los trabajos
SELECT * FROM v_jobs_with_config ORDER BY created_at DESC;

-- Ver logs de un trabajo
SELECT * FROM processing_logs WHERE job_id = 'tu-job-id';
```

## 🔧 Mantenimiento

### Actualizar configuración de revista:

```sql
UPDATE revista_config
SET volumen = 6, numero = 1, año = 2025
WHERE codigo = 'ideas';
```

### Limpiar trabajos manualmente:

```sql
CALL clean_old_jobs();
```

### Ver estadísticas:

```sql
-- Trabajos por estado
SELECT status, COUNT(*) FROM jobs GROUP BY status;

-- Trabajos por revista
SELECT revista_codigo, COUNT(*) FROM jobs GROUP BY revista_codigo;
```

## 🐛 Solución de Problemas

Ver [INSTALL.md](INSTALL.md) sección "Solución de Problemas" para:
- Errores de conexión MySQL
- Problemas con LaTeX
- Worker no procesa
- Emails no llegan
- Y más...

## 📝 Licencia

MIT License - Ver archivo [LICENSE](LICENSE)

## 👥 Contribuir

Pull requests son bienvenidos. Para cambios mayores, abre un issue primero.

## 📞 Soporte

- Revisar [INSTALL.md](INSTALL.md) para guía completa
- Revisar tabla `processing_logs` para debugging
- Revisar logs del worker en Windows
- Revisar logs de PHP en cPanel

---

**¡Sistema completo y listo para producción!** 🎉
