% Plantilla para revista TECING
% tecing.cls

\NeedsTeXFormat{LaTeX2e}
\ProvidesClass{tecing}[2025/01/01 Clase para Revista TECING]

\LoadClass[11pt,a4paper,twocolumn]{article}

\RequirePackage{fontspec}
\RequirePackage[spanish]{babel}
\RequirePackage{graphicx}
\RequirePackage{multicol}
\RequirePackage[margin=2cm]{geometry}
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

% Fuentes
\setmainfont{Arial}

% Encabezados
\pagestyle{fancy}
\fancyhf{}
\fancyhead[C]{\small TECING - Tecnología, Ciencia e Ingeniería | Vol. \@revistaVolumen\ No. \@revistaNumero\ (\@revistaAño)}
\fancyfoot[C]{\thepage}
\renewcommand{\headrulewidth}{0.4pt}

% Encabezado inicial con logo
\AtBeginDocument{
    \twocolumn[
    \begin{@twocolumnfalse}
    \begin{center}
        \includegraphics[width=0.5\textwidth]{logos/logo_tecing.png}\\[0.2cm]
        {\Huge\textbf{TECING}}\\[0.2cm]
        {\large Tecnología, Ciencia e Ingeniería}\\[0.1cm]
        {\small Volumen \@revistaVolumen\ | Número \@revistaNumero\ | \@revistaAño}\\[0.3cm]
        \hrule height 1.5pt
        \vspace{0.5cm}
    \end{center}
    \end{@twocolumnfalse}
    ]
}
