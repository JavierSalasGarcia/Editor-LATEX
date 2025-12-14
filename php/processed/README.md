# Carpeta de Archivos Procesados

Esta carpeta contiene los archivos ZIP generados por el worker.

**Contenido de cada ZIP:**
- `documento.tex` - Archivo LaTeX procesado
- `revista.cls` - Clase LaTeX de la revista
- `logos/` - Logos de la revista
- `figuras/` - Figuras y elementos gráficos
- `documento.pdf` - PDF compilado

**Estructura:**
- `job-id.zip` - Archivo ZIP completo

**Importante:**
- Los archivos se eliminan automáticamente después de 30 días
- NO subir esta carpeta a Git (está en .gitignore)
- Asegurar permisos 755 para la carpeta

**Seguridad:**
- Solo accesible vía download.php con autenticación de job_id
- Configurar .htaccess para denegar acceso directo
