---
layout: default
title: Introducción
nav_order: 2
---


<!-- omit in toc -->
# Introducción

- [Objetivo del proyecto](#objetivo-del-proyecto)
- [Problema que resuelve](#problema-que-resuelve)
- [Usuarios principales](#usuarios-principales)
- [Alcance del proyecto](#alcance-del-proyecto)


**LogNow!** es una aplicación web desarrollada como **proyecto final del Ciclo Formativo de Grado Superior de Desarrollo de Aplicaciones Web**. Está pensada para usuarios que quieren llevar un seguimiento de los videojuegos que juegan, tienen por jugar o han completado.

La plataforma permite consultar un catálogo de juegos, guardar títulos en una biblioteca personal, escribir reseñas, asignar puntuaciones y crear listas propias. El proyecto permite aplicar de forma conjunta distintas competencias trabajadas durante el ciclo, como el diseño responsive, la programación en cliente y servidor, la gestión de bases de datos, el consumo de una API externa y el despliegue de una aplicación web.

La app combina información externa con contenido generado por los usuarios. Los datos generales de cada juego se obtienen desde **IGDB** y se almacenan en la base de datos local. Sobre esa información, LogNow! añade la parte personal y comunitaria: estados de juego, favoritos, reseñas, perfiles y listas.

La aplicación se ha desarrollado como una web clásica con PHP, MariaDB, HTML, CSS y JavaScript.

## Objetivo del proyecto

El objetivo principal de LogNow! es centralizar la actividad de un jugador en torno a sus videojuegos. Desde su cuenta, el usuario puede registrar qué juegos está jugando, cuáles ha completado, cuáles tiene por jugar y cuáles ha abandonado.

Además del seguimiento personal, la aplicación permite valorar juegos mediante puntuaciones y reseñas. Estas reseñas se muestran en la ficha de cada juego y también en los perfiles de usuario por lo que también ofrece una pequeña capa de comunidad.

Otro objetivo importante es evitar que el catálogo dependa de datos escritos manualmente. Para ello se utiliza IGDB como fuente externa y se guarda una copia local de los juegos consultados o importados. Así se reduce la dependencia de la API en cada carga de página.

## Problema que resuelve

Muchos jugadores tienen videojuegos repartidos entre varias plataformas y tiendas digitales. Con el tiempo puede ser difícil recordar qué juegos se han terminado, cuáles se dejaron a medias o qué opinión personal dejó cada título.

LogNow! resuelve ese problema ofreciendo una biblioteca única donde cada juego puede tener un estado claro: jugando, completado, pendiente o abandonado. También permite indicar tiempo jugado, fechas, favoritos y una puntuación personal.

La aplicación también ayuda a conservar opiniones evitando que una valoración quede perdida en una conversación, el usuario puede escribir una reseña asociada a su perfil. Esto permite consultar en el futuro qué juegos le gustaron, cuáles no y por qué.

## Usuarios principales

La aplicación distingue tres tipos de usuario:

- **Invitado:** puede navegar por el catálogo, buscar juegos, consultar fichas, ver perfiles y leer reseñas.
- **Usuario registrado:** puede gestionar su biblioteca, puntuar juegos, escribir reseñas, marcar favoritos, editar su perfil y crear listas personales.
- **Administrador:** puede acceder al panel de administración, consultar usuarios, activar o desactivar cuentas y revisar reportes.

## Alcance del proyecto

LogNow! incluye las funcionalidades principales necesarias para que la aplicación sea usable de principio a fin: autenticación, catálogo, búsqueda, ficha de juego, biblioteca personal, reseñas, listas, perfil de usuario y panel de administración.

También incorpora partes técnicas importantes, como conexión a base de datos con PDO, sesiones, roles de usuario, operaciones CRUD, peticiones AJAX, validación en cliente, diseño responsive, generación de PDF con FPDF e integración con API externa.

El alcance está ajustado al tiempo disponible para el proyecto. Las listas son privadas, las recomendaciones son básicas y el panel de administración se centra en usuarios, reportes de reseñas y exportación de usuarios en PDF.
