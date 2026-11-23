-- ÚSALO SOLO UNA VEZ si ya tienes las tablas antiguas de la Agenda Personal.
-- ANTES: exporta una copia de seguridad desde phpMyAdmin.
-- No importa este archivo después de database.sql sobre una instalación nueva.
-- Las fechas de los contactos viejos se inicializarán con la fecha de migración.

CREATE TABLE IF NOT EXISTS categorias (
  id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias (id, nombre) VALUES
  (1, 'Familia'), (2, 'Amigos'), (3, 'Trabajo'), (4, 'Universidad'), (5, 'Otro');

ALTER TABLE contactos
  ADD COLUMN categoria_id TINYINT UNSIGNED NOT NULL DEFAULT 5,
  ADD COLUMN favorito TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE contactos
  ADD KEY idx_contactos_usuario_ciudad (id_usuario, ciudad),
  ADD KEY idx_contactos_usuario_favorito (id_usuario, favorito),
  ADD CONSTRAINT fk_contactos_categoria FOREIGN KEY (categoria_id)
    REFERENCES categorias (id) ON DELETE RESTRICT ON UPDATE RESTRICT;
