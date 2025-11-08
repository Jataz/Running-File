-- Create database and documents table (optional helper)
CREATE DATABASE IF NOT EXISTS `file_system`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `file_system`;

CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `stored_name` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(255),
  `size` BIGINT,
  `description` TEXT,
  `sha256` CHAR(64),
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;