% Archivo de clase para revista IDEAS
% ideas.cls

\NeedsTeXFormat{LaTeX2e}
\ProvidesClass{ideas}[2025/01/01 Clase para Revista IDEAS]

% Basado en article
\LoadClass[12pt,a4paper]{article}

% Paquetes requeridos
\RequirePackage[utf8]{inputenc}
\RequirePackage[spanish]{babel}
\RequirePackage{graphicx}
\RequirePackage{amsmath}
\RequirePackage{cite}
\RequirePackage[margin=2.5cm]{geometry}
\RequirePackage{fancyhdr}

% Variables de la revista
\newcommand{\revistaVolumen}[1]{\def\@revistaVolumen{#1}}
\newcommand{\revistaAño}[1]{\def\@revistaAño{#1}}
\newcommand{\revistaNumero}[1]{\def\@revistaNumero{#1}}
\newcommand{\paginaInicial}[1]{\def\@paginaInicial{#1}}

% Valores por defecto
\def\@revistaVolumen{1}
\def\@revistaAño{2025}
\def\@revistaNumero{1}
\def\@paginaInicial{1}

% Configuración de encabezados y pies de página
\pagestyle{fancy}
\fancyhf{}
\rhead{Revista IDEAS - Vol. \@revistaVolumen, Núm. \@revistaNumero}
\lhead{\thepage}
\renewcommand{\headrulewidth}{0.4pt}

% Logo en la primera página
\AtBeginDocument{
    \begin{center}
        \includegraphics[width=0.3\textwidth]{logos/logo_ideas.png}\\[0.3cm]
        {\Large\textbf{REVISTA IDEAS}}\\[0.3cm]
        {\large Volumen \@revistaVolumen\ $\bullet$ Número \@revistaNumero\ $\bullet$ Año \@revistaAño}\\[0.5cm]
        \hrule
        \vspace{0.5cm}
    \end{center}
}

% Pie de página con logo
\fancyfoot[C]{\includegraphics[height=0.8cm]{figuras/footer_ideas.png}}
