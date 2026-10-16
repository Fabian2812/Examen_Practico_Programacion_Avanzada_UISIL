CREATE DATABASE IF NOT EXISTS incidencias_uisil
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE incidencias_uisil;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS incidencias (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  reportado_por VARCHAR(120) NOT NULL,
  laboratorio VARCHAR(100) NOT NULL,
  equipo VARCHAR(120) NOT NULL,
  descripcion TEXT NOT NULL,
  prioridad ENUM('Baja', 'Media', 'Alta', 'Critica') NOT NULL,
  estado ENUM('Pendiente', 'En proceso', 'Resuelta') NOT NULL DEFAULT 'Pendiente',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_laboratorio (laboratorio),
  INDEX idx_estado (estado),
  INDEX idx_prioridad (prioridad)
) ENGINE=InnoDB;
