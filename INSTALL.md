# Guía de Instalación - Editor-LATEX

Sistema completo de procesamiento de documentos para revistas académicas usando **PHP + MySQL + Worker Python**.

---

## 📋 Requisitos

### Servidor Web (Hostinger)
- ✅ Hosting con soporte PHP 7.4+
- ✅ MySQL 5.7+ o MariaDB 10.2+
- ✅ Acceso a phpMyAdmin
- ✅ Acceso a Cron Jobs
- ✅ Soporte para `mail()` de PHP o SMTP

### Laptop Windows (Worker)
- ✅ Windows 10/11
- ✅ Python 3.8+ instalado
- ✅ MiKTeX o TeX Live
- ✅ API Key de Gemini (Google AI)
- ✅ Conexión a internet estable
- ✅ Encendida de 8am a 5pm

---

## 🚀 PARTE 1: Instalación en Hostinger

### Paso 1: Subir archivos PHP

1. Conecta por FTP (FileZilla, cPanel File Manager, etc.)

2. Sube la carpeta `php/` al directorio raíz de tu dominio:
   ```
   public_html/
   ├── index.php
   ├── status.php
   ├── verify.php
   ├── download.php
   ├── cron_send_notifications.php
   ├── .htaccess
   ├── includes/
   │   ├── config.php
   │   ├── database.php
   │   └── functions.php
   └── assets/
       ├── css/
       │   └── style.css
       └── js/
           └── script.js
   ```

### Paso 2: Configurar base de datos

1. **Ir a cPanel > MySQL Databases**

2. **Crear base de datos:**
   - Nombre: `usuario_editor_latex` (o el que prefieras)
   - Crear

3. **Crear usuario MySQL:**
   - Usuario: `usuario_worker`
   - Contraseña: (genera una segura)
   - Crear

4. **Asignar permisos:**
   - Agregar usuario a la base de datos
   - Seleccionar **TODOS LOS PRIVILEGIOS**

5. **Importar esquema:**
   - Ir a phpMyAdmin
   - Seleccionar la base de datos
   - Ir a pestaña "SQL"
   - Copiar y pegar el contenido de `sql/schema.sql`
   - Ejecutar

### Paso 3: Configurar archivos PHP

Editar `includes/config.php`:

```php
// Configuración de base de datos (obtener de cPanel)
define('DB_HOST', 'localhost');  // o el host que te dé Hostinger
define('DB_NAME', 'usuario_editor_latex');
define('DB_USER', 'usuario_worker');
define('DB_PASS', 'tu_password_mysql');

// Configuración de email
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_USER', 'noreply@tudominio.com');
define('SMTP_PASS', 'password_email');
define('SMTP_FROM_EMAIL', 'noreply@tudominio.com');

// URL base del sitio
define('BASE_URL', 'https://tudominio.com');
```

### Paso 4: Configurar revista

Para cada uno de los 4 sitios web, edita `config.php`:

**Para IDEAS:**
```php
define('REVISTA_CODIGO', 'ideas');
```

**Para INFORMATICAE:**
```php
define('REVISTA_CODIGO', 'informaticae');
```

**Para ESTELAC:**
```php
define('REVISTA_CODIGO', 'estelac');
```

**Para TECING:**
```php
define('REVISTA_CODIGO', 'tecing');
```

### Paso 5: Configurar variables de revista

Editar valores en la base de datos (phpMyAdmin):

```sql
-- Actualizar configuración de IDEAS
UPDATE revista_config
SET volumen = 5, año = 2025, numero = 2, pagina_inicial = 1
WHERE codigo = 'ideas';

-- Repetir para las otras revistas
```

### Paso 6: Configurar Cron Job para notificaciones

1. **Ir a cPanel > Cron Jobs**

2. **Agregar nuevo Cron Job:**
   - Comando:
     ```bash
     /usr/bin/php /home/usuario/public_html/cron_send_notifications.php
     ```
   - Frecuencia: `*/5 * * * *` (cada 5 minutos)

3. Guardar

### Paso 7: Habilitar acceso remoto a MySQL

**IMPORTANTE:** Esto es necesario para que el worker de Windows se conecte.

1. **Ir a cPanel > Remote MySQL**

2. **Agregar Access Host:**
   - Ingresar tu IP pública (obtenerla de https://whatismyip.com)
   - O usar `%` para permitir cualquier IP (menos seguro)

3. **Guardar**

4. **Anotar datos de conexión:**
   - Host: `servidor-mysql.hostinger.com` (o el que te proporcionen)
   - Puerto: `3306`
   - Usuario: `usuario_worker`
   - Base de datos: `usuario_editor_latex`

---

## 🖥️ PARTE 2: Instalación del Worker en Windows

### Paso 1: Instalar Python

1. Descargar Python 3.11 desde https://www.python.org/downloads/
2. **IMPORTANTE:** Marcar "Add Python to PATH"
3. Instalar

### Paso 2: Instalar MiKTeX

1. Descargar MiKTeX desde https://miktex.org/download
2. Instalar con opciones por defecto
3. Al finalizar, abrir **MiKTeX Console**
4. Ir a **Settings > General**
5. Cambiar "Install missing packages" a **"Yes"**

### Paso 3: Obtener API Key de Gemini

1. Ir a https://makersuite.google.com/app/apikey
2. Iniciar sesión con cuenta de Google
3. Click en "Create API Key"
4. Copiar la API Key generada

### Paso 4: Preparar carpetas

Crear estructura de carpetas en `C:\`:

```
C:\Editor-LATEX\
├── plantillas\
│   ├── plantilla_ideas.tex
│   ├── plantilla_informaticae.tex
│   ├── plantilla_estelac.tex
│   └── plantilla_tecing.tex
├── temp\
└── windows_worker\
    ├── worker.py
    └── requirements.txt
```

Copiar las plantillas desde `plantillas/` del repositorio.

### Paso 5: Instalar dependencias Python

Abrir **PowerShell como administrador** y ejecutar:

```powershell
cd C:\Editor-LATEX\windows_worker
pip install -r requirements.txt
```

### Paso 6: Configurar variables de entorno

Crear archivo `C:\Editor-LATEX\windows_worker\start_worker.bat`:

```bat
@echo off
echo ========================================
echo Worker de Procesamiento - Editor LATEX
echo ========================================

REM Configuración MySQL
set MYSQL_HOST=servidor-mysql.hostinger.com
set MYSQL_PORT=3306
set MYSQL_USER=usuario_worker
set MYSQL_PASS=tu_password_mysql
set MYSQL_DB=usuario_editor_latex

REM API Key de Gemini
set GEMINI_API_KEY=tu_api_key_gemini

REM Carpetas
set PLANTILLAS_FOLDER=C:\Editor-LATEX\plantillas
set TEMP_FOLDER=C:\Editor-LATEX\temp

REM Horario de trabajo (8am - 5pm)
set WORK_START_HOUR=8
set WORK_END_HOUR=17

REM Intervalo de polling (30 segundos)
set POLL_INTERVAL=30

echo.
echo Iniciando worker...
echo.

python worker.py

pause
```

**IMPORTANTE:** Reemplazar:
- `servidor-mysql.hostinger.com` con el host MySQL real
- `usuario_worker` con tu usuario MySQL
- `tu_password_mysql` con tu contraseña MySQL
- `usuario_editor_latex` con el nombre de tu base de datos
- `tu_api_key_gemini` con tu API Key de Gemini

### Paso 7: Probar conexión

Ejecutar `start_worker.bat`. Deberías ver:

```
========================================
WORKER DE PROCESAMIENTO DE DOCUMENTOS
========================================
MySQL: servidor-mysql.hostinger.com:3306
Base de datos: usuario_editor_latex
Horario: 8:00 - 17:00
Polling: cada 30 segundos
========================================

✓ Conectado a MySQL: servidor-mysql.hostinger.com:3306

✓ Worker iniciado. Esperando trabajos...
```

Si aparece algún error, revisar:
- Credenciales MySQL
- Acceso remoto MySQL habilitado
- Firewall de Windows
- API Key de Gemini

### Paso 8: Configurar inicio automático (Opcional)

Para que el worker inicie automáticamente cuando enciendas Windows:

1. Presionar `Win + R`
2. Escribir `shell:startup`
3. Copiar acceso directo de `start_worker.bat` a esa carpeta

---

## 🌐 PARTE 3: Configurar Múltiples Sitios Web

Si tienes 4 dominios diferentes para las 4 revistas:

### Opción A: 4 Dominios Distintos

1. **Configurar cada dominio en Hostinger:**
   - ideas.tudominio.com
   - informaticae.tudominio.com
   - estelac.tudominio.com
   - tecing.tudominio.com

2. **Subir archivos PHP a cada uno:**
   - Cada uno apunta a la misma base de datos
   - Solo cambia `REVISTA_CODIGO` en `config.php`

### Opción B: Subdirectorios

```
tudominio.com/ideas/
tudominio.com/informaticae/
tudominio.com/estelac/
tudominio.com/tecing/
```

Cada uno con su propio `config.php` configurado.

---

## 🧪 Pruebas

### 1. Probar sitio web

1. Ir a tu dominio
2. Completar formulario con tus datos
3. Subir un archivo de prueba (.doc, .docx o .tex)
4. Verificar que aparece mensaje de éxito
5. Revisar email de verificación

### 2. Verificar base de datos

En phpMyAdmin, ejecutar:

```sql
SELECT * FROM jobs ORDER BY created_at DESC LIMIT 5;
```

Deberías ver tu trabajo con status `pending`.

### 3. Probar worker

1. Asegurarte de que el worker está corriendo
2. Esperar a que procese (máx. 30 segundos + tiempo de procesamiento)
3. Revisar consola del worker para logs
4. Verificar status cambió a `completed`

### 4. Descargar PDF

1. Ir a página de estado con tu job ID
2. Click en "Descargar PDF"
3. Verificar que el PDF se descarga correctamente

---

## 🔧 Solución de Problemas

### Error: "Cannot connect to MySQL"

**Causa:** Worker no puede conectarse a MySQL de Hostinger

**Solución:**
1. Verificar que Remote MySQL esté habilitado en cPanel
2. Verificar que tu IP esté en la lista de Access Hosts
3. Probar conexión con HeidiSQL o MySQL Workbench desde Windows

### Error: "GEMINI_API_KEY no encontrada"

**Causa:** Variable de entorno no configurada

**Solución:**
1. Verificar que `start_worker.bat` tiene la API Key
2. Ejecutar el worker usando `start_worker.bat`, no `python worker.py` directo

### Error: "pdflatex no se encuentra"

**Causa:** LaTeX no está en el PATH

**Solución:**
1. Abrir MiKTeX Console
2. Ir a Settings > Directories
3. Copiar ruta (ej: `C:\Program Files\MiKTeX\miktex\bin\x64`)
4. Agregar al PATH de Windows:
   - Win + R → `sysdm.cpl`
   - Opciones avanzadas → Variables de entorno
   - Editar PATH → Agregar ruta de MiKTeX

### Worker no procesa fuera de horario

**Esto es normal.** El worker solo procesa de 8am a 5pm.

Para cambiar horario, editar en `start_worker.bat`:
```bat
set WORK_START_HOUR=0
set WORK_END_HOUR=24
```

### No llegan emails

**Opciones:**

1. **Verificar carpeta de spam**

2. **Verificar configuración SMTP en `config.php`**

3. **Verificar que cron job está ejecutándose:**
   ```bash
   # En cPanel, revisar "Cron Jobs" logs
   ```

4. **Ejecutar manualmente:**
   ```bash
   php cron_send_notifications.php
   ```

---

## 📊 Mantenimiento

### Limpieza automática

La base de datos tiene un evento programado que limpia:
- PDFs de trabajos completados después de 30 días
- Trabajos con error después de 7 días

Para ejecutar manualmente:
```sql
CALL clean_old_jobs();
```

### Actualizar configuración de revista

```sql
UPDATE revista_config
SET volumen = 6, numero = 1
WHERE codigo = 'ideas';
```

### Ver logs de procesamiento

```sql
SELECT * FROM processing_logs
WHERE job_id = 'tu-job-id'
ORDER BY created_at DESC;
```

### Estadísticas

```sql
-- Trabajos por estado
SELECT status, COUNT(*) as total
FROM jobs
GROUP BY status;

-- Trabajos por revista
SELECT revista_codigo, COUNT(*) as total
FROM jobs
GROUP BY revista_codigo;
```

---

## 🔐 Seguridad

### Recomendaciones:

1. ✅ **Usar HTTPS** (Hostinger ofrece SSL gratis con Let's Encrypt)

2. ✅ **Cambiar credenciales por defecto** en `config.php`

3. ✅ **Limitar IP para MySQL remoto** (no usar `%`)

4. ✅ **Configurar firewall** en Windows para permitir solo conexión a MySQL

5. ✅ **Rotar API Key** de Gemini periódicamente

6. ✅ **Backups regulares** de la base de datos

---

## 📞 Soporte

Si tienes problemas:

1. Revisar logs del worker en consola
2. Revisar logs de PHP en cPanel
3. Revisar tabla `processing_logs` en MySQL
4. Consultar documentación de Hostinger

---

¡Listo! Tu sistema de procesamiento de documentos está funcionando. 🎉
