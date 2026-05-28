---
layout: default
title: Instalación
nav_order: 3
---

<!-- omit in toc -->
# Instalación

- [Herramientas necesarias](#herramientas-necesarias)
- [Variables de entorno](#variables-de-entorno)
- [Puesta en marcha](#puesta-en-marcha)
- [Base de datos](#base-de-datos)
- [Importación inicial de juegos](#importación-inicial-de-juegos)
- [Despliegue](#despliegue)
- [Comandos útiles](#comandos-útiles)


El proyecto se puede levantar con contenedores usando Docker y el archivo `docker-compose.yml`.

{: .info }
El proyecto se ha desarrollado y probado en Linux. Se recomienda instalar en un entorno Linux, ya sea en una máquina real o en una máquina virtual, para poder seguir bien estos pasos.

## Herramientas necesarias

- Git
- Docker y `docker compose`
- Cuenta gratuita en Twitch Developers para obtener credenciales de IGDB

## Variables de entorno

Antes de levantar los contenedores hay que crear un archivo `.env` en la raíz del proyecto. El repositorio incluye `.env.example` como plantilla:

```bash
cp .env.example .env
```

Contenido esperado:

```bash
TWITCH_CLIENT_ID=tu_client_id
TWITCH_CLIENT_SECRET=tu_client_secret
MARIADB_ROOT_PASSWORD=password
```

`TWITCH_CLIENT_ID` y `TWITCH_CLIENT_SECRET` deben sustituirse por credenciales reales de Twitch Developers. Son necesarias para importar juegos desde IGDB y dejar el catálogo preparado.

{: .warning }
El archivo `.env` contiene credenciales y debe ignorarse en GIT.

## Puesta en marcha

1. Se clona el repositorio y se entra en la carpeta:

```bash
git clone https://github.com/jormarveg/LogNow.git
cd LogNow
```

2. Después se construyen y levantan los contenedores:

```bash
docker compose up -d --build
```

3. Se puede comprobar que los servicios están levantados con:

```bash
docker compose ps
```

Deberían aparecer los servicios `web`, `php`, `db` y `phpmyadmin`.

Servicios creados:

| Servicio | Uso |
|---|---|
| `web` | Nginx, servidor web principal. |
| `php` | PHP-FPM con PDO MySQL. |
| `db` | MariaDB con la base de datos `lognow`. |
| `phpmyadmin` | Interfaz web para revisar la base de datos. |

4. La aplicación queda disponible en:

```text
http://localhost
```

phpMyAdmin queda disponible en:

```text
http://localhost:8000
```

## Base de datos

El script principal está en:

```text
sql/lognow.sql
```

Se puede importar desde phpMyAdmin entrando en `http://localhost:8000` y usando la pestaña **Importar** con el archivo `sql/lognow.sql`.

También se puede importar por consola desde la raíz del proyecto:

```bash
docker compose exec -T db sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD"' < sql/lognow.sql
```

El script incluye dos usuarios de prueba para poder entrar directamente después de importar la base de datos:

| Rol | Email | Contraseña |
|---|---|---|
| Administrador | `admin@lognow.local` | `lognow1234` |
| Usuario | `pedrito@lognow.local` | `lognow1234` |

Con el usuario administrador se accede al panel desde:

```text
http://localhost/admin/
```

## Importación inicial de juegos

Habiendo iniciado sesión con el rol de `admin`, el panel de administración muestra una **acción de importación inicial** si el catálogo está vacío. Para que funcione, las variables `TWITCH_CLIENT_ID` y `TWITCH_CLIENT_SECRET` deben estar configuradas en `.env`. Tras unos segundos, se habrá importado un catálogo inicial.

## Despliegue

Para el despliegue se ha preparado el archivo `docker-compose-prod.yml`. La idea es mantener una estructura parecida a la del entorno local, pero adaptada a un servidor público.

Los servicios principales siguen siendo Nginx, PHP-FPM y MariaDB. En producción se añade Caddy como proxy inverso, de forma que recibe las peticiones públicas por HTTPS y las envía al contenedor web.

También hay que crear un archivo `Caddyfile` con el dominio que se vaya a usar y la redirección al servicio `web` del compose.

Las credenciales y claves externas se siguen leyendo desde variables de entorno. El archivo `.env` debe crearse en el servidor, igual que en local, pero no debe subirse al repositorio.

## Comandos útiles

Ver contenedores:

```bash
docker compose ps
```

Revisar logs de PHP:

```bash
docker compose logs php
```

Revisar logs de Nginx:

```bash
docker compose logs web
```

Parar el entorno:

```bash
docker compose down
```

Empezar de cero eliminando también la base de datos local:

```bash
docker compose down -v
docker compose up -d --build
```

Este comando borra el volumen de MariaDB, por lo que después hay que volver a importar `sql/lognow.sql`.
