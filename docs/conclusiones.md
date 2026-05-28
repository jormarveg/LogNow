---
layout: default
title: Conclusiones
nav_order: 7
---
<!-- omit in toc -->
# Conclusiones

- [Dificultades](#dificultades)
- [Aprendizajes](#aprendizajes)
- [Mejoras futuras](#mejoras-futuras)



LogNow! ha terminado siendo una aplicación bastante completa para el alcance del proyecto. Incluye catálogo, usuarios, biblioteca personal, reseñas, listas, perfil, administración e integración con una API externa.

## Dificultades

Una de las partes más complejas del proyecto ha sido la integración con IGDB. Al principio puede parecer suficiente con consultar la API y mostrar los resultados, pero en la práctica ha sido necesario adaptar los datos recibidos al modelo de la aplicación. No todos los juegos tienen siempre la misma información disponible, por lo que ha sido importante controlar valores nulos, portadas inexistentes, fechas incompletas o puntuaciones externas no disponibles.

Además, para no depender constantemente de la API externa, se ha implementado una caché local en la base de datos. Esto ha obligado a comprobar si un juego ya existía antes de insertarlo, relacionarlo correctamente con sus géneros y plataformas, y reutilizar los datos guardados en futuras búsquedas o visualizaciones.

Otra dificultad importante ha sido la gestión de puntuaciones y reseñas. La aplicación permite que un usuario puntúe rápidamente un juego sin escribir una reseña completa, pero también permite crear reseñas con texto. Esto ha hecho necesario diferenciar entre la puntuación personal, la puntuación media de la comunidad de LogNow! y la puntuación externa de IGDB. Mantener esa lógica ordenada ha sido fundamental para evitar datos contradictorios en el catálogo, las fichas de juego, los perfiles y las listas.

También ha supuesto trabajo adaptar la interfaz a distintos tamaños de pantalla. LogNow! tiene muchas vistas diferentes, como el catálogo, la ficha de juego, la biblioteca, el perfil, las listas, los formularios y el panel de administración. Conseguir que todas fueran utilizables en móvil, tablet y escritorio ha requerido ajustar menús, grids, tarjetas, formularios e imágenes mediante CSS responsive.

## Aprendizajes

Este proyecto me ha permitido trabajar con PHP y PDO en una aplicación más completa que los ejercicios realizados durante el ciclo. He aprendido a organizar mejor el código del servidor, separar funcionalidades en archivos reutilizables y utilizar consultas preparadas para acceder a la base de datos de forma más segura.

También he reforzado el uso de sesiones y roles de usuario. LogNow! diferencia entre visitantes, usuarios registrados y administradores, por lo que ha sido necesario controlar qué puede hacer cada tipo de usuario y proteger las páginas que requieren permisos concretos.

En la parte de cliente, he practicado el uso de JavaScript para mejorar la interacción de la aplicación sin depender siempre de recargas completas. Algunos ejemplos son el buscador, los filtros, el selector de puntuación con estrellas, las validaciones de formularios y determinadas acciones mediante AJAX.

Otro aprendizaje importante ha sido el tratamiento de datos externos. La integración con IGDB me ha ayudado a entender mejor cómo consumir una API, transformar los datos recibidos, guardarlos en una base de datos propia y combinarlos con información generada por los usuarios de la aplicación.

En cuanto al diseño, el proyecto me ha servido para aplicar una estrategia responsive en una aplicación con muchas vistas diferentes. He trabajado con Flexbox, Grid, media queries, variables CSS, adaptación de imágenes y menús distintos según el tamaño de pantalla.

Además, la parte de despliegue me ha ayudado a entender mejor cómo se organiza una aplicación web fuera del entorno de desarrollo. El uso de Docker, Nginx, PHP-FPM, MariaDB, Caddy y AWS Free Tier me ha permitido ver con más claridad la diferencia entre levantar una aplicación en local y publicarla en un servidor real.

## Mejoras futuras

Algunas mejoras posibles serían:

- Añadir un tema oscuro para usuarios que prefieran una interfaz menos luminosa.
- Permitir seguir a otros usuarios y acercar el perfil a una experiencia más social.
- Hacer listas públicas para que otros usuarios puedan consultarlas.
- Permitir reordenar juegos dentro de una lista desde la interfaz.
- Mejorar las recomendaciones con más criterios, no solo géneros.
- Añadir más estadísticas al perfil del usuario.
- Ampliar el panel de administración con más filtros y métricas.

Estas mejoras no son necesarias para que la aplicación funcione, pero podrían hacerla más completa si se continuara el proyecto.
