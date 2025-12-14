% Plantilla para revista ESTELAC
% estelac.cls

\NeedsTeXFormat{LaTeX2e}
\ProvidesClass{estelac}[2025/01/01 Clase para Revista ESTELAC]

\LoadClass[12pt,letterpaper]{article}

\RequirePackage[utf8]{inputenc}
\RequirePackage[spanish]{babel}
\RequirePackage{graphicx}
\RequirePackage{amsmath,amssymb}
\RequirePackage[margin=3cm]{geometry}
\RequirePackage{natbib}
\RequirePackage{fancyhdr}

% Variables
\newcommand{\revistaVolumen}[1]{\def\@revistaVolumen{#1}}
\newcommand{\revistaAño}[1]{\def\@revistaAño{#1}}
\newcommand{\revistaNumero}[1]{\def\@revistaNumero{#1}}
\newcommand{\paginaInicial}[1]{\def\@paginaInicial{#1}}

\def\@revistaVolumen{1}
\def\@revistaAño{2025}
\def\@revistaNumero{1}
\def\@paginaInicial{1}

% Encabezados
\pagestyle{fancy}
\fancyhf{}
\lhead{ESTELAC}
\rhead{Vol. \@revistaVolumen(\@revistaNumero), \@revistaAño}
\cfoot{\thepage}

% Logo y header inicial
\AtBeginDocument{
    \begin{center}
        \includegraphics[width=0.3\textwidth]{logos/logo_estelac.png}\\[0.5cm]
        {\Large\textsc{Revista ESTELAC}}\\[0.2cm]
        {\large Estudios en Tecnología, Educación y Cultura}\\[0.3cm]
        \rule{\linewidth}{0.5mm}\\[0.2cm]
        {\small ISSN: 1234-5678 $\bullet$ Vol. \@revistaVolumen\ $\bullet$ Núm. \@revistaNumero\ $\bullet$ \@revistaAño}\\[0.5cm]
        \rule{\linewidth}{0.5mm}
    \end{center}
}

% Pie de página
\fancyfoot[L]{\includegraphics[height=1cm]{figuras/footer_estelac.png}}
