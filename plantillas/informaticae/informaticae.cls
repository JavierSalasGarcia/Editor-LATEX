% Plantilla para revista INFORMATICAE
% informaticae.cls

\NeedsTeXFormat{LaTeX2e}
\ProvidesClass{informaticae}[2025/01/01 Clase para Revista INFORMATICAE]

\LoadClass[11pt,a4paper]{article}

\RequirePackage{fontspec}
\RequirePackage[spanish]{babel}
\RequirePackage{graphicx}
\RequirePackage{listings}
\RequirePackage{xcolor}
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

% Configuración de código
\lstset{
    basicstyle=\ttfamily\small,
    keywordstyle=\color{blue},
    commentstyle=\color{gray},
    stringstyle=\color{red},
    frame=single,
    breaklines=true
}

% Encabezados
\pagestyle{fancy}
\fancyhf{}
\rhead{INFORMATICAE - \@revistaAño}
\lhead{Pág. \thepage}
\cfoot{Vol. \@revistaVolumen\ | Núm. \@revistaNumero}

% Encabezado inicial
\AtBeginDocument{
    \begin{center}
        \includegraphics[width=0.4\textwidth]{logos/logo_informaticae.png}\\[0.2cm]
        {\Huge\textbf{INFORMATICAE}}\\[0.2cm]
        {\Large Revista de Informática y Tecnología}\\[0.3cm]
        \hrule height 2pt
        \vspace{0.3cm}
        {\large Volumen \@revistaVolumen\ $\circ$ Número \@revistaNumero\ $\circ$ \@revistaAño}\\[0.5cm]
    \end{center}
}
