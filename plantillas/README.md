# Estructura de Plantillas

Cada revista tiene su propia carpeta con todos los archivos necesarios para la compilación.

## 📁 Estructura por Revista

```
plantillas/
├── ideas/
│   ├── main.tex              # Plantilla principal (punto de entrada)
│   ├── ideas.cls             # Archivo de clase LaTeX
│   ├── logos/
│   │   ├── logo_ideas.png    # Logo de la revista
│   │   └── README.md
│   └── figuras/
│       ├── footer_ideas.png  # Pie de página
│       └── README.md
├── informaticae/
│   ├── main.tex
│   ├── informaticae.cls
│   ├── logos/
│   └── figuras/
├── estelac/
│   ├── main.tex
│   ├── estelac.cls
│   ├── logos/
│   └── figuras/
└── tecing/
    ├── main.tex
    ├── tecing.cls
    ├── logos/
    └── figuras/
```

## 📝 Archivos Principales

### 1. `main.tex`
- **Propósito:** Archivo principal que será compilado
- **Contenido:** Usa `\documentclass{nombre_revista}` para cargar la clase
- **Procesamiento:** Gemini AI insertará el contenido del usuario aquí

### 2. `revista.cls` (ej: `ideas.cls`)
- **Propósito:** Define el estilo y formato de la revista
- **Contenido:**
  - Configuración de márgenes
  - Encabezados y pies de página
  - Comandos personalizados
  - Carga de logos y figuras
  - Metadatos (volumen, año, número, página)

### 3. `logos/`
- **Propósito:** Contiene logos de la revista
- **Archivos típicos:**
  - `logo_revista.png` - Logo principal
  - Otros logos si son necesarios

### 4. `figuras/`
- **Propósito:** Figuras reutilizables (pies de página, separadores, etc.)
- **Archivos típicos:**
  - `footer_revista.png` - Imagen de pie de página
  - `separator.png` - Separadores de sección
  - Otros elementos gráficos

## 🔧 Cómo Funciona el Worker

1. **Detecta trabajo pendiente** en MySQL
2. **Identifica la revista** (ej: "ideas")
3. **Copia TODA la carpeta** `plantillas/ideas/` al directorio temporal
4. **Procesa el documento** del usuario con Gemini AI
5. **Gemini genera código LaTeX** que será insertado en `main.tex`
6. **Compila** `main.tex` (que usa `ideas.cls` y referencia logos/figuras)
7. **Genera el PDF** final

## ✏️ Personalizar una Plantilla

### Opción 1: Modificar Archivo de Clase (.cls)

Edita `ideas.cls` para cambiar:
- Márgenes: `\RequirePackage[margin=2.5cm]{geometry}`
- Encabezados: Modificar comandos `\fancyhead`
- Logos: Cambiar ruta en `\includegraphics{logos/logo_ideas.png}`
- Estilos: Agregar paquetes adicionales

### Opción 2: Agregar Archivos Adicionales

Puedes agregar:
- Archivos `.sty` (paquetes personalizados)
- Archivos `.bst` (estilos de bibliografía)
- Archivos de configuración
- Imágenes adicionales

**IMPORTANTE:** Todos los archivos en la carpeta de la revista serán copiados al directorio de compilación.

## 📦 Ejemplo Completo: IDEAS

**ideas.cls:**
```latex
\NeedsTeXFormat{LaTeX2e}
\ProvidesClass{ideas}[2025/01/01 Clase para Revista IDEAS]
\LoadClass[12pt,a4paper]{article}

% Paquetes
\RequirePackage{graphicx}
\RequirePackage{fancyhdr}

% Variables
\newcommand{\revistaVolumen}[1]{\def\@revistaVolumen{#1}}
\newcommand{\revistaAño}[1]{\def\@revistaAño{#1}}

% Logo inicial
\AtBeginDocument{
    \includegraphics[width=0.3\textwidth]{logos/logo_ideas.png}
}
```

**main.tex:**
```latex
\documentclass{ideas}

\revistaVolumen{5}
\revistaAño{2025}

\begin{document}

% Gemini AI insertará el contenido aquí

\end{document}
```

## 🎯 Buenas Prácticas

1. **Usa rutas relativas:** `logos/logo.png` en vez de rutas absolutas
2. **Nombres consistentes:** Mantén nombres de archivo sin espacios ni acentos
3. **Formatos estándar:** PNG para logos (con transparencia), JPG para fotos
4. **Documenta:** Incluye README.md en carpetas importantes
5. **Versionado:** Usa Git para mantener historial de cambios en plantillas

## 🐛 Solución de Problemas

### Error: "File logo_ideas.png not found"

**Causa:** El logo no existe o está en la ubicación incorrecta

**Solución:**
1. Verificar que el archivo existe en `plantillas/ideas/logos/`
2. Verificar que el nombre coincide exactamente (sensible a mayúsculas)
3. Verificar que la extensión es correcta (.png, no .PNG)

### Error: "Undefined control sequence"

**Causa:** Comando personalizado no definido en el .cls

**Solución:**
1. Revisar que todos los comandos `\revistaVolumen`, etc. estén definidos en el .cls
2. Verificar que los paquetes necesarios estén cargados

### El logo no aparece

**Causa:** Ruta incorrecta o archivo no copiado

**Solución:**
1. El worker copia toda la carpeta, verificar logs
2. Usar `\graphicspath{{logos/}{figuras/}}` en el .cls si es necesario

## 📚 Recursos Adicionales

- [LaTeX Class Writing Guide](https://www.latex-project.org/help/documentation/clsguide.pdf)
- [CTAN - Package Documentation](https://www.ctan.org/)
- Ejemplos de clases: `article.cls`, `book.cls`

---

**Nota:** Cada vez que modifiques archivos de plantilla, NO necesitas reiniciar el worker. Los cambios se aplicarán en el próximo trabajo procesado.
