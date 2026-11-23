-- Agenda Personal B1: instalación nueva (MySQL / MariaDB).
-- En Hostinger: crea primero la base en hPanel, selecciónala en phpMyAdmin e importa este archivo.
-- No contiene CREATE DATABASE ni USE, para poder usar el nombre asignado por el hosting.

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(254) NOT NULL,
  password VARCHAR(255) NOT NULL,
  fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
  id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias (id, nombre) VALUES
  (1, 'Familia'),
  (2, 'Amigos'),
  (3, 'Trabajo'),
  (4, 'Universidad'),
  (5, 'Otro');

CREATE TABLE IF NOT EXISTS contactos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_usuario INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  telefono VARCHAR(30) NOT NULL,
  correo VARCHAR(254) NOT NULL,
  ciudad VARCHAR(100) NOT NULL,
  categoria_id TINYINT UNSIGNED NOT NULL DEFAULT 5,
  favorito TINYINT(1) NOT NULL DEFAULT 0,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contactos_usuario_nombre (id_usuario, nombre, id),
  KEY idx_contactos_usuario_ciudad (id_usuario, ciudad),
  KEY idx_contactos_usuario_favorito (id_usuario, favorito),
  CONSTRAINT fk_contactos_usuario FOREIGN KEY (id_usuario)
    REFERENCES usuarios (id) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT fk_contactos_categoria FOREIGN KEY (categoria_id)
    REFERENCES categorias (id) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
