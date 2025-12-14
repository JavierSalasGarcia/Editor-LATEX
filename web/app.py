import os
import json
import uuid
from datetime import datetime
from flask import Flask, render_template, request, jsonify, send_file
from werkzeug.utils import secure_filename
from config import REVISTAS, UPLOAD_FOLDER, PROCESSED_FOLDER, MAX_FILE_SIZE, ALLOWED_EXTENSIONS

app = Flask(__name__)
app.config['MAX_CONTENT_LENGTH'] = MAX_FILE_SIZE

# Variable de entorno para definir qué revista es este sitio
# Ejemplo: export REVISTA=ideas
REVISTA_ACTUAL = os.environ.get('REVISTA', 'ideas')

# Crear carpetas si no existen
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
os.makedirs(PROCESSED_FOLDER, exist_ok=True)


def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS


@app.route('/')
def index():
    """Renderiza el formulario de carga"""
    revista = REVISTAS.get(REVISTA_ACTUAL, REVISTAS['ideas'])
    return render_template('index.html', revista=revista)


@app.route('/upload', methods=['POST'])
def upload_file():
    """Maneja la carga de archivos"""
    if 'file' not in request.files:
        return jsonify({'error': 'No se envió ningún archivo'}), 400

    file = request.files['file']

    if file.filename == '':
        return jsonify({'error': 'No se seleccionó ningún archivo'}), 400

    if not allowed_file(file.filename):
        return jsonify({'error': 'Tipo de archivo no permitido. Solo .doc, .docx, .tex'}), 400

    # Generar ID único para este trabajo
    job_id = str(uuid.uuid4())

    # Guardar el archivo con un nombre seguro
    filename = secure_filename(file.filename)
    ext = filename.rsplit('.', 1)[1].lower()
    new_filename = f"{job_id}.{ext}"
    filepath = os.path.join(UPLOAD_FOLDER, new_filename)

    file.save(filepath)

    # Crear archivo de metadata para el worker
    metadata = {
        'job_id': job_id,
        'revista': REVISTA_ACTUAL,
        'filename_original': filename,
        'filename': new_filename,
        'tipo_archivo': ext,
        'timestamp': datetime.now().isoformat(),
        'status': 'pending',
        'compilador': REVISTAS[REVISTA_ACTUAL]['compilador'],
        'plantilla': REVISTAS[REVISTA_ACTUAL]['plantilla'],
        'configuracion': REVISTAS[REVISTA_ACTUAL]['configuracion']
    }

    metadata_path = os.path.join(UPLOAD_FOLDER, f"{job_id}.json")
    with open(metadata_path, 'w', encoding='utf-8') as f:
        json.dump(metadata, f, indent=2, ensure_ascii=False)

    return jsonify({
        'success': True,
        'job_id': job_id,
        'message': 'Archivo cargado exitosamente. Procesando...'
    })


@app.route('/status/<job_id>')
def check_status(job_id):
    """Verifica el estado de un trabajo"""
    metadata_path = os.path.join(UPLOAD_FOLDER, f"{job_id}.json")
    processed_metadata = os.path.join(PROCESSED_FOLDER, f"{job_id}.json")

    # Buscar primero en procesados
    if os.path.exists(processed_metadata):
        with open(processed_metadata, 'r', encoding='utf-8') as f:
            metadata = json.load(f)
        return jsonify(metadata)

    # Luego en pendientes
    if os.path.exists(metadata_path):
        with open(metadata_path, 'r', encoding='utf-8') as f:
            metadata = json.load(f)
        return jsonify(metadata)

    return jsonify({'error': 'Trabajo no encontrado'}), 404


@app.route('/download/<job_id>')
def download_file(job_id):
    """Descarga el PDF procesado"""
    pdf_path = os.path.join(PROCESSED_FOLDER, f"{job_id}.pdf")

    if not os.path.exists(pdf_path):
        return jsonify({'error': 'PDF no encontrado o aún no está listo'}), 404

    # Obtener nombre original del archivo
    metadata_path = os.path.join(PROCESSED_FOLDER, f"{job_id}.json")
    original_name = "documento"

    if os.path.exists(metadata_path):
        with open(metadata_path, 'r', encoding='utf-8') as f:
            metadata = json.load(f)
            original_name = metadata.get('filename_original', 'documento').rsplit('.', 1)[0]

    return send_file(
        pdf_path,
        as_attachment=True,
        download_name=f"{original_name}_procesado.pdf",
        mimetype='application/pdf'
    )


if __name__ == '__main__':
    print(f"Iniciando servidor para la revista: {REVISTAS[REVISTA_ACTUAL]['nombre_completo']}")
    print(f"Carpeta de procesamiento: {os.path.abspath(UPLOAD_FOLDER)}")
    print(f"Carpeta de procesados: {os.path.abspath(PROCESSED_FOLDER)}")
    app.run(debug=True, host='0.0.0.0', port=5000)
