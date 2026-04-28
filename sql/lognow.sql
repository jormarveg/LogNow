CREATE DATABASE IF NOT EXISTS lognow
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_general_ci;
USE lognow;

-- Tablas independientes

CREATE TABLE USUARIO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    nick VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('usuario', 'admin') DEFAULT 'usuario',
    avatar VARCHAR(255),
    encabezado VARCHAR(255),
    biografia TEXT,
    activo BOOLEAN DEFAULT TRUE,
    registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE DESARROLLADORA (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    pais VARCHAR(50),
    igdb_id INT
);

CREATE TABLE GENERO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    igdb_id INT
);

CREATE TABLE PLATAFORMA (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    acronimo VARCHAR(20),
    igdb_id INT
);

-- Tablas con dependencias

CREATE TABLE VIDEOJUEGO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    igdb_id INT UNIQUE,
    titulo VARCHAR(150) NOT NULL,
    portada_url VARCHAR(255),
    background_url VARCHAR(255),
    fecha_lanzamiento DATE,
    puntuacion_igdb DECIMAL(4,1),
    descripcion TEXT,
    id_desarrolladora INT,
    fecha_cache DATETIME,
    FOREIGN KEY (id_desarrolladora) REFERENCES DESARROLLADORA(id) ON DELETE SET NULL
);

CREATE TABLE LISTA (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id) ON DELETE CASCADE
);

-- Relaciones N:M

CREATE TABLE VIDEOJUEGO_GENERO (
    id_videojuego INT NOT NULL,
    id_genero INT NOT NULL,
    PRIMARY KEY (id_videojuego, id_genero),
    FOREIGN KEY (id_videojuego) REFERENCES VIDEOJUEGO(id) ON DELETE CASCADE,
    FOREIGN KEY (id_genero) REFERENCES GENERO(id) ON DELETE CASCADE
);

CREATE TABLE VIDEOJUEGO_PLATAFORMA (
    id_videojuego INT NOT NULL,
    id_plataforma INT NOT NULL,
    PRIMARY KEY (id_videojuego, id_plataforma),
    FOREIGN KEY (id_videojuego) REFERENCES VIDEOJUEGO(id) ON DELETE CASCADE,
    FOREIGN KEY (id_plataforma) REFERENCES PLATAFORMA(id) ON DELETE CASCADE
);

CREATE TABLE USUARIO_JUEGO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_videojuego INT NOT NULL,
    id_plataforma INT NOT NULL,
    estado ENUM('jugando', 'completado', 'pendiente', 'abandonado') NOT NULL,
    horas_jugadas INT DEFAULT 0,
    minutos_jugados INT DEFAULT 0,
    fecha_inicio DATE,
    fecha_fin DATE,
    favorito BOOLEAN DEFAULT FALSE,
    UNIQUE(id_usuario, id_videojuego),
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id) ON DELETE CASCADE,
    FOREIGN KEY (id_videojuego) REFERENCES VIDEOJUEGO(id) ON DELETE CASCADE,
    FOREIGN KEY (id_plataforma) REFERENCES PLATAFORMA(id) ON DELETE RESTRICT
);

CREATE TABLE RESENA (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_videojuego INT NOT NULL,
    puntuacion INT CHECK (puntuacion BETWEEN 0 AND 100 AND puntuacion % 10 = 0),
    comentario TEXT,
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    editada BOOLEAN DEFAULT FALSE,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id) ON DELETE CASCADE,
    FOREIGN KEY (id_videojuego) REFERENCES VIDEOJUEGO(id) ON DELETE CASCADE
);

CREATE TABLE LISTA_VIDEOJUEGO (
    id_lista INT NOT NULL,
    id_videojuego INT NOT NULL,
    orden INT DEFAULT 0,
    PRIMARY KEY (id_lista, id_videojuego),
    FOREIGN KEY (id_lista) REFERENCES LISTA(id) ON DELETE CASCADE,
    FOREIGN KEY (id_videojuego) REFERENCES VIDEOJUEGO(id) ON DELETE CASCADE
);

CREATE TABLE REPORTE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_resena INT NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    estado ENUM('pendiente', 'revisado', 'descartado') DEFAULT 'pendiente',
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id) ON DELETE CASCADE,
    FOREIGN KEY (id_resena) REFERENCES RESENA(id) ON DELETE CASCADE
);
