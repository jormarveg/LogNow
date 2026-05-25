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
- [Subida de imágenes](#subida-de-imágenes)
- [Despliegue](#despliegue)
- [Comandos útiles](#comandos-útiles)


El proyecto se puede levantar con contenedores usando Docker y el archivo `docker-compose.yml`.

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

{: .warning }
El archivo `.env` contiene credenciales y no debe subirse al repositorio. 

## Puesta en marcha

Se clona el repositorio y se entra en la carpeta:

```bash
git clone https://github.com/jormarveg/LogNow.git
cd LogNow
```

Después se construyen y levantan los contenedores:

```bash
docker compose up -d --build
```

Servicios creados:

| Servicio | Uso |
|---|---|
| `web` | Nginx, servidor web principal. |
| `php` | PHP-FPM con PDO MySQL. |
| `db` | MariaDB con la base de datos `lognow`. |
| `phpmyadmin` | Interfaz web para revisar la base de datos. |

La aplicación queda disponible en:

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

Se puede importar desde phpMyAdmin entrando en `http://localhost:8000`, seleccionando la base de datos `lognow` y usando la pestaña **Importar** con el archivo `sql/lognow.sql`.

También se puede importar por consola desde la raíz del proyecto:

```bash
docker compose exec -T db sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" lognow' < sql/lognow.sql
```

Después se puede abrir `http://localhost/registro.php` y crear el primer usuario.

Para convertir ese usuario en administrador se puede hacer desde phpMyAdmin ejecutando una consulta SQL sobre la base de datos `lognow`, cambiando el email por el usado en el registro:

```sql
UPDATE USUARIO SET rol = 'admin' WHERE email = 'correo@ejemplo.com';
```

También se puede hacer desde consola entrando a MariaDB:

```bash
docker compose exec db sh -c 'mariadb -uroot -p"$MARIADB_ROOT_PASSWORD" lognow'
```

```sql
UPDATE USUARIO SET rol = 'admin' WHERE email = 'correo@ejemplo.com';
```

Con ese usuario se accede al panel desde:

```text
http://localhost/admin/
```

## Importación inicial de juegos

El panel de administración muestra una acción de importación inicial si el catálogo está vacío. Para que funcione, las variables `TWITCH_CLIENT_ID` y `TWITCH_CLIENT_SECRET` deben estar configuradas en `.env`.

También se puede lanzar el importador desde:

```text
http://localhost/api/importar.php
```

## Subida de imágenes

El avatar y el encabezado del perfil admiten JPG, PNG y WEBP hasta 5 MB. Nginx y PHP permiten un margen mayor para que la aplicación pueda mostrar un mensaje claro cuando el archivo supera el límite.

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
