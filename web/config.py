# Configuración de las revistas
REVISTAS = {
    'ideas': {
        'nombre': 'IDEAS',
        'nombre_completo': 'Revista IDEAS',
        'compilador': 'pdflatex',  # o 'xelatex'
        'plantilla': 'plantilla_ideas.tex',
        'configuracion': {
            'volumen': 1,
            'año': 2025,
            'numero': 1,
            'pagina_inicial': 1
        }
    },
    'informaticae': {
        'nombre': 'INFORMATICAE',
        'nombre_completo': 'Revista INFORMATICAE',
        'compilador': 'xelatex',
        'plantilla': 'plantilla_informaticae.tex',
        'configuracion': {
            'volumen': 1,
            'año': 2025,
            'numero': 1,
            'pagina_inicial': 1
        }
    },
    'estelac': {
        'nombre': 'ESTELAC',
        'nombre_completo': 'Revista ESTELAC',
        'compilador': 'pdflatex',
        'plantilla': 'plantilla_estelac.tex',
        'configuracion': {
            'volumen': 1,
            'año': 2025,
            'numero': 1,
            'pagina_inicial': 1
        }
    },
    'tecing': {
        'nombre': 'TECING',
        'nombre_completo': 'Revista TECING',
        'compilador': 'xelatex',
        'plantilla': 'plantilla_tecing.tex',
        'configuracion': {
            'volumen': 1,
            'año': 2025,
            'numero': 1,
            'pagina_inicial': 1
        }
    }
}

# Configuración general
UPLOAD_FOLDER = '../procesar'
PROCESSED_FOLDER = '../procesados'
MAX_FILE_SIZE = 50 * 1024 * 1024  # 50MB
ALLOWED_EXTENSIONS = {'doc', 'docx', 'tex'}
