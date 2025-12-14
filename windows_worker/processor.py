"""
Worker de Windows para procesar documentos
Este script debe ejecutarse en una laptop Windows con:
- Python 3.8+
- MiKTeX o TeX Live instalado
- Gemini API Key configurada
"""

import os
import sys
import json
import time
import shutil
import subprocess
from pathlib import Path
from datetime import datetime
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler
import google.generativeai as genai


# ============= CONFIGURACIÓN =============
# Configura tu API Key de Gemini
GEMINI_API_KEY = os.environ.get('GEMINI_API_KEY', '')

# Carpetas - ajusta estas rutas según tu configuración
# Pueden ser rutas locales o rutas de red compartida (ej: \\servidor\share\procesar)
PROCESAR_FOLDER = os.environ.get('PROCESAR_FOLDER', r'C:\Editor-LATEX\procesar')
PROCESADOS_FOLDER = os.environ.get('PROCESADOS_FOLDER', r'C:\Editor-LATEX\procesados')
PLANTILLAS_FOLDER = os.environ.get('PLANTILLAS_FOLDER', r'C:\Editor-LATEX\plantillas')
TEMP_FOLDER = os.environ.get('TEMP_FOLDER', r'C:\Editor-LATEX\temp')

# Variables configurables desde Windows
CONFIGURACION_LOCAL = {
    'volumen': os.environ.get('REVISTA_VOLUMEN', '1'),
    'año': os.environ.get('REVISTA_AÑO', '2025'),
    'numero': os.environ.get('REVISTA_NUMERO', '1'),
    'pagina_inicial': os.environ.get('REVISTA_PAGINA_INICIAL', '1')
}

# =========================================


class DocumentProcessor:
    """Procesa documentos Word/LaTeX usando Gemini API"""

    def __init__(self, api_key):
        if not api_key:
            raise ValueError("Se requiere GEMINI_API_KEY")

        genai.configure(api_key=api_key)
        self.model = genai.GenerativeModel('gemini-pro')

        # Crear carpetas si no existen
        os.makedirs(PROCESAR_FOLDER, exist_ok=True)
        os.makedirs(PROCESADOS_FOLDER, exist_ok=True)
        os.makedirs(TEMP_FOLDER, exist_ok=True)
        os.makedirs(PLANTILLAS_FOLDER, exist_ok=True)

    def process_word_to_latex(self, word_file, plantilla, metadata):
        """Convierte documento Word a LaTeX usando Gemini"""
        print(f"  → Procesando Word: {word_file}")

        try:
            # Leer el archivo Word (necesitarás python-docx)
            from docx import Document
            doc = Document(word_file)

            # Extraer texto del documento
            full_text = []
            for para in doc.paragraphs:
                full_text.append(para.text)

            content = '\n'.join(full_text)

            # Leer plantilla
            plantilla_path = os.path.join(PLANTILLAS_FOLDER, plantilla)
            if os.path.exists(plantilla_path):
                with open(plantilla_path, 'r', encoding='utf-8') as f:
                    plantilla_content = f.read()
            else:
                plantilla_content = "Plantilla no encontrada"

            # Preparar prompt para Gemini
            prompt = f"""
Eres un experto en LaTeX. Convierte el siguiente documento a formato LaTeX siguiendo esta plantilla.

PLANTILLA DE LA REVISTA:
{plantilla_content}

CONTENIDO DEL DOCUMENTO:
{content}

INSTRUCCIONES:
1. Mantén la estructura de la plantilla
2. Inserta el contenido del documento en las secciones apropiadas
3. Convierte el formato (negritas, cursivas, listas, etc.) a comandos LaTeX
4. Mantén las citas y referencias en formato BibTeX si las hay
5. Asegúrate de que el código LaTeX sea válido y compilable

METADATOS:
- Volumen: {metadata['configuracion']['volumen']}
- Año: {metadata['configuracion']['año']}
- Número: {metadata['configuracion']['numero']}
- Página inicial: {metadata['configuracion']['pagina_inicial']}

Devuelve SOLO el código LaTeX completo, sin explicaciones adicionales.
"""

            # Llamar a Gemini
            print("  → Consultando Gemini API...")
            response = self.model.generate_content(prompt)
            latex_content = response.text

            # Limpiar respuesta (remover markdown si existe)
            latex_content = latex_content.replace('```latex', '').replace('```', '').strip()

            return latex_content

        except ImportError:
            print("  ⚠ ERROR: python-docx no está instalado. Instalando...")
            subprocess.run([sys.executable, '-m', 'pip', 'install', 'python-docx'])
            return self.process_word_to_latex(word_file, plantilla, metadata)

        except Exception as e:
            print(f"  ✗ Error procesando Word: {e}")
            raise

    def process_latex_file(self, latex_file, metadata):
        """Procesa archivo LaTeX existente con Gemini para ajustar a plantilla"""
        print(f"  → Procesando LaTeX: {latex_file}")

        try:
            # Leer archivo LaTeX
            with open(latex_file, 'r', encoding='utf-8') as f:
                latex_content = f.read()

            # Leer plantilla
            plantilla_path = os.path.join(PLANTILLAS_FOLDER, metadata['plantilla'])
            if os.path.exists(plantilla_path):
                with open(plantilla_path, 'r', encoding='utf-8') as f:
                    plantilla_content = f.read()

                # Usar Gemini para ajustar a la plantilla
                prompt = f"""
Eres un experto en LaTeX. Ajusta el siguiente documento LaTeX para que use esta plantilla de revista.

PLANTILLA DE LA REVISTA:
{plantilla_content}

DOCUMENTO ORIGINAL:
{latex_content}

INSTRUCCIONES:
1. Adapta el documento original a la estructura de la plantilla
2. Mantén todo el contenido del documento original
3. Actualiza los metadatos:
   - Volumen: {metadata['configuracion']['volumen']}
   - Año: {metadata['configuracion']['año']}
   - Número: {metadata['configuracion']['numero']}
   - Página inicial: {metadata['configuracion']['pagina_inicial']}
4. Asegúrate de que sea compilable

Devuelve SOLO el código LaTeX completo.
"""

                print("  → Ajustando con Gemini API...")
                response = self.model.generate_content(prompt)
                latex_content = response.text.replace('```latex', '').replace('```', '').strip()

            return latex_content

        except Exception as e:
            print(f"  ⚠ Advertencia al procesar LaTeX: {e}")
            # Si falla, devolver el original
            with open(latex_file, 'r', encoding='utf-8') as f:
                return f.read()

    def compile_latex(self, latex_content, output_name, compilador='pdflatex'):
        """Compila LaTeX a PDF"""
        print(f"  → Compilando con {compilador}...")

        # Crear directorio temporal para este trabajo
        temp_dir = os.path.join(TEMP_FOLDER, output_name)
        os.makedirs(temp_dir, exist_ok=True)

        # Escribir archivo .tex
        tex_file = os.path.join(temp_dir, f"{output_name}.tex")
        with open(tex_file, 'w', encoding='utf-8') as f:
            f.write(latex_content)

        # Compilar (2 veces para referencias)
        for i in range(2):
            print(f"    Pasada {i+1}/2...")
            result = subprocess.run(
                [compilador, '-interaction=nonstopmode', f"{output_name}.tex"],
                cwd=temp_dir,
                capture_output=True,
                text=True
            )

            if result.returncode != 0:
                print(f"  ✗ Error en compilación:")
                print(result.stdout[-500:])  # Últimas 500 líneas
                raise Exception(f"Error al compilar LaTeX: {result.returncode}")

        # Verificar que se generó el PDF
        pdf_file = os.path.join(temp_dir, f"{output_name}.pdf")
        if not os.path.exists(pdf_file):
            raise Exception("El PDF no se generó")

        print(f"  ✓ PDF generado exitosamente")
        return pdf_file

    def process_job(self, job_id):
        """Procesa un trabajo completo"""
        print(f"\n{'='*60}")
        print(f"Procesando trabajo: {job_id}")
        print(f"{'='*60}")

        metadata_file = os.path.join(PROCESAR_FOLDER, f"{job_id}.json")

        try:
            # Leer metadata
            with open(metadata_file, 'r', encoding='utf-8') as f:
                metadata = json.load(f)

            # Actualizar con configuración local de Windows
            metadata['configuracion'].update(CONFIGURACION_LOCAL)

            print(f"Revista: {metadata['revista']}")
            print(f"Archivo: {metadata['filename_original']}")
            print(f"Tipo: {metadata['tipo_archivo']}")

            # Obtener archivo de entrada
            input_file = os.path.join(PROCESAR_FOLDER, metadata['filename'])

            if not os.path.exists(input_file):
                raise Exception(f"Archivo no encontrado: {input_file}")

            # Procesar según tipo
            latex_content = None

            if metadata['tipo_archivo'] in ['doc', 'docx']:
                latex_content = self.process_word_to_latex(
                    input_file,
                    metadata['plantilla'],
                    metadata
                )
            elif metadata['tipo_archivo'] == 'tex':
                latex_content = self.process_latex_file(input_file, metadata)
            else:
                raise Exception(f"Tipo de archivo no soportado: {metadata['tipo_archivo']}")

            # Compilar LaTeX
            pdf_file = self.compile_latex(
                latex_content,
                job_id,
                metadata['compilador']
            )

            # Mover PDF a carpeta de procesados
            final_pdf = os.path.join(PROCESADOS_FOLDER, f"{job_id}.pdf")
            shutil.copy2(pdf_file, final_pdf)

            # Actualizar metadata
            metadata['status'] = 'completed'
            metadata['completed_at'] = datetime.now().isoformat()
            metadata['pdf_path'] = final_pdf

            # Guardar metadata en procesados
            processed_metadata = os.path.join(PROCESADOS_FOLDER, f"{job_id}.json")
            with open(processed_metadata, 'w', encoding='utf-8') as f:
                json.dump(metadata, f, indent=2, ensure_ascii=False)

            # Eliminar archivos de la carpeta procesar
            os.remove(metadata_file)
            os.remove(input_file)

            print(f"✓ Trabajo completado exitosamente")
            print(f"  PDF: {final_pdf}")

        except Exception as e:
            print(f"✗ Error procesando trabajo: {e}")

            # Actualizar metadata con error
            try:
                with open(metadata_file, 'r', encoding='utf-8') as f:
                    metadata = json.load(f)

                metadata['status'] = 'error'
                metadata['error'] = str(e)
                metadata['error_at'] = datetime.now().isoformat()

                error_metadata = os.path.join(PROCESADOS_FOLDER, f"{job_id}.json")
                with open(error_metadata, 'w', encoding='utf-8') as f:
                    json.dump(metadata, f, indent=2, ensure_ascii=False)

                os.remove(metadata_file)
            except:
                pass


class FileWatcher(FileSystemEventHandler):
    """Monitorea la carpeta procesar en busca de nuevos trabajos"""

    def __init__(self, processor):
        self.processor = processor
        self.processing = set()

    def on_created(self, event):
        if event.is_directory:
            return

        # Solo procesar archivos .json (metadata)
        if not event.src_path.endswith('.json'):
            return

        job_id = Path(event.src_path).stem

        # Evitar procesar el mismo trabajo múltiples veces
        if job_id in self.processing:
            return

        # Esperar un poco para asegurar que el archivo esté completamente escrito
        time.sleep(1)

        self.processing.add(job_id)

        try:
            self.processor.process_job(job_id)
        finally:
            self.processing.discard(job_id)


def main():
    """Función principal"""
    print("="*60)
    print("WORKER DE PROCESAMIENTO DE DOCUMENTOS")
    print("="*60)
    print(f"Carpeta de procesamiento: {PROCESAR_FOLDER}")
    print(f"Carpeta de procesados: {PROCESADOS_FOLDER}")
    print(f"Configuración local:")
    print(f"  Volumen: {CONFIGURACION_LOCAL['volumen']}")
    print(f"  Año: {CONFIGURACION_LOCAL['año']}")
    print(f"  Número: {CONFIGURACION_LOCAL['numero']}")
    print(f"  Página inicial: {CONFIGURACION_LOCAL['pagina_inicial']}")
    print("="*60)

    if not GEMINI_API_KEY:
        print("\n✗ ERROR: No se encontró GEMINI_API_KEY")
        print("Configura la variable de entorno GEMINI_API_KEY")
        sys.exit(1)

    # Inicializar procesador
    processor = DocumentProcessor(GEMINI_API_KEY)

    # Procesar archivos existentes primero
    print("\nBuscando trabajos pendientes...")
    for file in os.listdir(PROCESAR_FOLDER):
        if file.endswith('.json'):
            job_id = file.replace('.json', '')
            print(f"Encontrado: {job_id}")
            processor.process_job(job_id)

    # Iniciar watchdog
    print("\n✓ Iniciando monitoreo de carpeta...")
    print("Esperando nuevos trabajos... (Ctrl+C para detener)\n")

    event_handler = FileWatcher(processor)
    observer = Observer()
    observer.schedule(event_handler, PROCESAR_FOLDER, recursive=False)
    observer.start()

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print("\n\nDeteniendo worker...")
        observer.stop()

    observer.join()
    print("Worker detenido.")


if __name__ == '__main__':
    main()
