---
layout: default
title: Guía de estilos
nav_order: 6
---

<!-- omit in toc -->
# Guía de estilos

- [Introducción](#introducción)
- [Paleta de colores](#paleta-de-colores)
- [Uso del color](#uso-del-color)
- [Tipografía](#tipografía)
- [Iconografía](#iconografía)
- [Componentes de interfaz](#componentes-de-interfaz)
- [Estructura](#estructura)
- [Menús](#menús)
- [Imágenes y logotipos](#imágenes-y-logotipos)
- [Diseño responsivo y puntos de ruptura](#diseño-responsivo-y-puntos-de-ruptura)
- [Estados y efectos visuales](#estados-y-efectos-visuales)
- [Accesibilidad](#accesibilidad)

## Introducción

El diseño de **LogNow!** se basa en una interfaz limpia que prioriza la facilidad de uso tanto en dispositivos móviles como en escritorio, siguiendo una metodología **_mobile-first_**.

En móvil se acercan las secciones principales a la zona inferior de la pantalla, y en escritorio se aprovecha el ancho para mostrar más contenido en columnas.

<p align="center">
  <img src="img/principal-movil-marco.webp" style="max-width: 300px; width: 100%;"><br>
  Página principal en móvil.
</p>

<div align="center">
  <img src="img/principal-escritorio-marco.webp" style="width: 100%;">
  Página principal en escritorio.
</div><br>

## Paleta de colores

La paleta usa tonos neutros y un **azul de acento** para las acciones principales.

| Uso | Color | Aplicación |
|---|---|---|
| Principal | <span class="muestra-color" style="background:#2c88d9;"></span>`#2c88d9` | Logotipo, enlaces activos, botones y elementos interactivos. |
| Puntuación | <span class="muestra-color" style="background:#f7c325;"></span>`#f7c325` | Estrellas y valoraciones. |
| Bordes | <span class="muestra-color" style="background:#c3cfd9;"></span>`#c3cfd9` | Separación entre bloques, tarjetas y formularios. |
| Fondos suaves | <span class="muestra-color" style="background:#dfe6ed;"></span>`#dfe6ed` | Navegación inferior y zonas secundarias. |
| Fondo claro | <span class="muestra-color" style="background:#f7f9fa;"></span>`#f7f9fa` | Cabecera y fondos de apoyo. |
| Texto principal | <span class="muestra-color" style="background:#333333;"></span>`#333333` | Texto general de la interfaz. |
| Títulos | <span class="muestra-color" style="background:#1a1a1a;"></span>`#1a1a1a` | Encabezados y textos destacados. |

## Uso del color

El **azul** se usa para los elementos con los que el usuario puede interactuar, como botones, enlaces activos o estados de navegación. El **amarillo** se deja para las puntuaciones, porque así las valoraciones se localizan rápido.

Los **grises** ayudan a separar zonas sin competir con las portadas de los juegos, que ya tienen bastante peso visual. Por eso los colores no se usan como decoración todo el rato, sino para ordenar mejor la pantalla.

Los **fondos claros** aparecen en la cabecera, la navegación y algunos paneles secundarios. El texto principal usa un gris oscuro y los títulos un tono más fuerte para que la jerarquía se entienda sin tener que aumentar demasiado los tamaños.

## Tipografía

La tipografía principal es **Poppins**, cargada desde Google Fonts. Se usa en textos, formularios, botones, navegación y tarjetas. Los pesos principales son 400 para texto normal, 600 para etiquetas y botones, y 700 para títulos.

La marca **LogNow!** usa **Limelight**, también desde Google Fonts. Esta es la fuente del logo textual de la aplicación. Se aplica solo al nombre de la marca mediante la clase `.marca-lognow`, para que el logotipo tenga un aspecto propio sin depender de una imagen fija.

## Iconografía

Los iconos se cargan desde **FontAwesome 6.5.1** por CDN. Se usan como apoyo visual en navegación, búsqueda, favoritos, estados, administración y acciones rápidas.

En móvil, la barra inferior combina icono y texto para que las secciones principales sean fáciles de reconocer. En escritorio, los iconos aparecen en acciones concretas donde ayudan a escanear más rápido la interfaz.

Las estrellas de puntuación tienen tratamiento propio con el color amarillo, que es un color bastante clásico para valoraciones.

## Componentes de interfaz

La interfaz repite algunos componentes para que las pantallas no parezcan páginas totalmente distintas entre sí.

| Componente | Uso |
|---|---|
| Tarjetas de juego | Muestran portada, título y algunos datos breves en catálogo, carruseles, biblioteca y listas. |
| Tarjetas de reseña | Agrupan el juego, el usuario, la puntuación, la fecha y el texto de la reseña. |
| Botones y acciones | Sirven para guardar cambios, filtrar, importar, editar, eliminar o marcar favoritos. |
| Formularios y filtros | Mantienen campos agrupados, etiquetas claras y botones de acción al final del bloque. |
| Etiquetas de estado | Resumen estados como jugando, completado, pendiente, abandonado, activo o inactivo. |
| Tablas de administración | Organizan usuarios, reportes y datos de gestión en las pantallas del panel admin. |

Estos componentes ayudan a que el usuario reconozca patrones repetidos entre páginas, aunque cada vista tenga su propio contenido.

## Estructura

La aplicación usa una estructura común en todas las páginas principales. Cada vista se apoya en una cabecera, un contenido principal, una navegación adaptada al tamaño de pantalla y un pie de página.

La maquetación usa **Flexbox** y **CSS Grid**. El catálogo se organiza como una cuadrícula de portadas, la ficha de juego combina información principal con acciones personales, y el perfil agrupa cabecera, estadísticas, favoritos, biblioteca, listas y reseñas.

<div align="center">
  <img src="img/catalogo-movil-marco.webp" style="max-width: 300px; width: 100%;"><br>
 Catálogo en móvil.
</div>

<div align="center">
  <img src="img/catalogo-escritorio-marco.webp" style="width: 100%;">
  Catálogo en escritorio.
</div>

La aplicación comparte `header`, `nav` móvil y `footer` con plantillas PHP. Así se mantiene la misma navegación en todas las vistas.

La jerarquía de texto se mantiene sencilla:

| Elemento | Uso |
|---|---|
| `h1` | Título principal de cada vista. |
| `h2` | Bloques importantes, como reseñas, listas o administración. |
| Texto base | Lectura general, formularios y descripciones. |
| Etiquetas | Estados, filtros, campos y mensajes breves. |

Los textos de interfaz son directos. Se evita añadir ayuda o mensajes repetidos si el propio control ya deja clara la acción.

## Menús

La navegación principal cambia según el dispositivo. En móvil se usa una **barra inferior fija** con accesos a Inicio, Buscar, Juegos y Perfil o Entrar. Este menú queda cerca del pulgar y permite moverse por las secciones principales sin ocupar espacio en la parte superior.

![Menú móvil](img/menu-movil.webp)

En pantallas más grandes se usa una **navegación superior** dentro de la cabecera. Ahí aparecen el logotipo, el buscador y los enlaces principales.

![Menú en escritorio](img/menu-escritorio.webp)

Los **enlaces activos** se marcan con el color principal para que el usuario sepa en qué sección está. Los iconos se usan como apoyo visual, especialmente en móvil y en acciones rápidas.

## Imágenes y logotipos

Las **portadas** de videojuegos usan proporción vertical, parecida a una caja de juego. Se muestran con `object-fit: cover` para evitar deformaciones y con una imagen por defecto cuando IGDB no devuelve portada.

El perfil del usuario usa dos imágenes: **avatar circular** y **encabezado horizontal**. Ambas se suben desde la edición del perfil y están limitadas a 5 MB.

![Ejemplo encabezado de perfil](img/ejemplo-encabezado.webp)
<center>Ejemplo de encabezado e imagen de perfil de usuario.</center>

El logotipo es el texto **LogNow!** en azul con la fuente **Limelight**. En móvil aparece centrado en la cabecera y en pantallas más grandes se alinea a la izquierda para dejar espacio al buscador y a la navegación superior.

![Logo](img/logo.webp)

Al ser un logotipo textual, mantiene buena nitidez en cualquier resolución y se adapta bien al diseño responsive.

## Diseño responsivo y puntos de ruptura

La navegación cambia según el ancho de pantalla:

| Ancho | Comportamiento |
|---|---|
| Menos de `768px` | Cabecera simple y barra inferior fija con Inicio, Buscar, Juegos y Perfil o Entrar. |
| Desde `768px` | Se oculta la barra inferior y aparece la navegación superior. |
| Desde `992px` | Se aprovecha más el ancho para grids, paneles laterales y tablas. |

Esta estructura facilita el uso con una mano en móvil y deja más espacio al buscador y al menú en escritorio.

## Estados y efectos visuales

Los elementos interactivos tienen estados visuales para que la interfaz responda mejor. Se usan efectos de `hover`, `focus` y `active`, transiciones suaves, cambios de color, sombras, bordes y pequeñas transformaciones en botones, enlaces, tarjetas y menús.

La idea es que el usuario note qué elementos se pueden pulsar sin cargar la página de efectos innecesarios.

## Accesibilidad

La aplicación usa **etiquetas semánticas** de HTML5 como `header`, `nav`, `main`, `section` y `footer`. Esto mejora la organización del código y la lectura de la estructura.

Los **contrastes principales** están pensados para fondos claros. Los estados importantes no dependen solo del color: también se acompañan de texto, iconos, posición o cambios de forma.

En móvil se cuidan los **tamaños de botones** y campos para que sean cómodos al tocar. La navegación inferior coloca las secciones principales en una zona accesible para el pulgar.
