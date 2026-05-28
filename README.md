# LogNow!

**LogNow!** es una aplicación web de seguimiento y reseñas de videojuegos desarrollada como proyecto final del CFGS DAW.

La aplicación permite consultar un catálogo de videojuegos, guardar títulos en una biblioteca personal, escribir reseñas, puntuar juegos, crear listas y gestionar perfiles de usuario. Los datos principales de los videojuegos se obtienen desde la API de IGDB y se guardan en la base de datos para poder trabajar con ellos desde la aplicación.

La aplicación está disponible en https://lognow.jorgemv.es.

## Funcionalidades principales

- Catálogo y búsqueda de videojuegos.
- Ficha individual con información del juego, géneros, plataformas, desarrolladora y reseñas.
- Biblioteca personal con estados, favoritos, horas jugadas y plataforma.
- Sistema de puntuaciones y reseñas de usuarios.
- Perfiles públicos y edición del perfil propio con subida de imágenes.
- Listas personalizadas de videojuegos.
- Panel de administración para usuarios, reportes e importación inicial de juegos.
- Exportación PDF de usuarios registrados desde el panel de administración.

## Tecnologías utilizadas

- **Frontend:** HTML5, CSS3, JavaScript y jQuery.
- **Backend:** PHP 8 con PDO.
- **Base de datos:** MariaDB.
- **Servidor:** Nginx y PHP-FPM.
- **Entorno:** Docker con `docker compose`.
- **API externa:** [IGDB](https://www.igdb.com/api).
- **Bibliotecas:** FPDF, FontAwesome y Google Fonts.

## Puesta en marcha rápida

La instalación recomendada es en un entorno Linux, ya sea en una máquina real o en una máquina virtual.

Antes de levantar el proyecto hay que crear el archivo de variables de entorno copiando el ejemplo que se proporciona:

```bash
cp .env.example .env
```

En ese archivo hay que rellenar `TWITCH_CLIENT_ID` y `TWITCH_CLIENT_SECRET` con credenciales reales de Twitch Developers para poder importar juegos desde IGDB.

Después se construyen y arrancan los contenedores:

```bash
docker compose up -d --build
```

La aplicación queda disponible en:

```text
http://localhost
```

Para cargar la base de datos inicial:

```bash
docker compose exec -T db sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD"' < sql/lognow.sql
```

El SQL incluye usuarios de prueba: `pedrito@lognow.local` y `admin@lognow.local`, ambos con contraseña `lognow1234`.

Para usar la app con juegos reales hay que entrar como administrador, abrir `http://localhost/admin/` y lanzar la importación inicial desde el botón en el panel.

## Estructura del proyecto

```bash
includes/   Plantillas, sesión, conexión PDO y clases auxiliares
pages/      Páginas principales de la aplicación
admin/      Panel de administración
ajax/       Endpoints internos que devuelven JSON
api/        Integración con IGDB e importación de juegos
assets/     CSS, JavaScript e imágenes del frontend
uploads/    Imágenes subidas por los usuarios
sql/        Script principal de base de datos
docs/       Documentación del proyecto
```

## Documentación

La documentación completa del proyecto está publicada en GitHub Pages:

- [Introducción](https://jormarveg.github.io/LogNow/introduccion.html)
- [Instalación](https://jormarveg.github.io/LogNow/instalacion.html)
- [Uso](https://jormarveg.github.io/LogNow/uso.html)
- [Arquitectura](https://jormarveg.github.io/LogNow/arquitectura.html)
- [Guía de estilos](https://jormarveg.github.io/LogNow/guia-estilos.html)
- [Conclusiones](https://jormarveg.github.io/LogNow/conclusiones.html)
- [Referencias](https://jormarveg.github.io/LogNow/referencias.html)

## Autor

Jorge Martínez Vegara - Proyecto final CFGS DAW, 2025-2026.
