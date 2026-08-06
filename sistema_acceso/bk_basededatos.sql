-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para control_acceso_db
CREATE DATABASE IF NOT EXISTS `control_asistencia_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `control_asistencia_db`;

-- Volcando estructura para tabla control_acceso_db.attendance_logs
CREATE TABLE IF NOT EXISTS `attendance_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `date_log` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `total_hours` decimal(5,2) DEFAULT NULL,
  `status` enum('a_tiempo','tarde') COLLATE utf8mb4_general_ci DEFAULT 'a_tiempo',
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_acceso_db.attendance_logs: ~2 rows (aproximadamente)
DELETE FROM `attendance_logs`;
INSERT INTO `attendance_logs` (`id`, `employee_id`, `date_log`, `check_in_time`, `check_out_time`, `total_hours`, `status`) VALUES
	(1, 2, '2025-11-21', '10:30:15', '10:31:36', NULL, 'a_tiempo'),
	(2, 3, '2025-11-21', '10:39:53', '10:41:42', NULL, 'a_tiempo'),
	(3, 1, '2025-11-23', '16:04:54', NULL, NULL, 'a_tiempo');

-- Volcando estructura para tabla control_acceso_db.departments
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_acceso_db.departments: ~6 rows (aproximadamente)
DELETE FROM `departments`;
INSERT INTO `departments` (`id`, `name`, `status`) VALUES
	(1, 'GERENCIA', 'activo'),
	(2, 'ADMINISTRACIÓN', 'activo'),
	(3, 'GESTIÓN HUMANA', 'activo'),
	(4, 'LOGÍSTICA', 'activo'),
	(5, 'SISTEMA', 'activo'),
	(6, 'PRODUCCIÓN', 'activo');

-- Volcando estructura para tabla control_acceso_db.employees
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `position` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default.png',
  `status` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`employee_code`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_acceso_db.employees: ~2 rows (aproximadamente)
DELETE FROM `employees`;
INSERT INTO `employees` (`id`, `employee_code`, `first_name`, `last_name`, `email`, `password`, `department_id`, `position`, `photo_path`, `status`, `created_at`) VALUES
	(1, 'EMP001', 'VICTOR', 'RAMOS', 'victor.rs.datsoft@gmail.com', '$2y$10$hjoa6/izjbON6303KeT9GuxstoJqZlEdq.E7N.Y7KBX7qWQzxbq0y', 5, 'JEFE DE SISTEMAS', 'default.png', 'activo', '2025-11-21 09:30:28'),
	(2, 'EMP002', 'CARLOS', 'RAMIREZ', 'carlosramirez@correo.com', '$2y$10$cOhxXeHlMBYzpdFVTcx/2.rwrHetLpnXBRDIodanipx6G.FBNEESi', 2, 'ASISTENTE ADMINISTRATIVO', 'default.png', 'activo', '2025-11-21 10:26:33'),
	(3, 'EMP003', 'MARIA', 'ARIAS', 'mariaarias@correo.com', '$2y$10$NebEGaM19vO7si.8ctlfvuJQPaHPgh0j6znFxLs8PQJVeM2vU4khm', 3, 'JEFE DE RRHH', 'default.png', 'activo', '2025-11-21 10:37:16');

-- Volcando estructura para tabla control_acceso_db.incidents
CREATE TABLE IF NOT EXISTS `incidents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `severity` enum('baja','media','alta') COLLATE utf8mb4_general_ci DEFAULT 'baja',
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `attachment` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_acceso_db.incidents: ~0 rows (aproximadamente)
DELETE FROM `incidents`;
INSERT INTO `incidents` (`id`, `title`, `description`, `severity`, `created_by`, `created_at`, `attachment`) VALUES
	(1, 'VISITA DE PROVEEDOR MARIO ARIAS', 'EL PROVEEDOR INGRESÓ CON HERRAMIENTAS DE TRABAJO', 'baja', 1, '2025-11-21 10:46:02', 'evidencia_1763739962.jpg');

-- Volcando estructura para tabla control_acceso_db.settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_acceso_db.settings: ~1 rows (aproximadamente)
DELETE FROM `settings`;
INSERT INTO `settings` (`id`, `setting_name`, `setting_value`) VALUES
	(1, 'entry_time', '08:00');

-- Volcando estructura para tabla control_acceso_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','guardia') COLLATE utf8mb4_general_ci DEFAULT 'guardia',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('activo','inactivo') COLLATE utf8mb4_general_ci DEFAULT 'activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_acceso_db.users: ~2 rows (aproximadamente)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `status`) VALUES
	(1, 'admin', '$2y$10$ZDQqXB3RymRVbB79F1ktB.bpUMb3HVWhNUKRPh/dRa4Hg0rGeA62W', 'admin', '2025-11-18 01:01:37', 'activo'),
	(3, 'seguridad', '$2y$10$4O7YBx0b4WlR3vMe12eN4OGe6X7J3tXUXzBH495rsT95YMtKCVh9K', 'guardia', '2025-11-21 10:28:36', 'activo');

-- Volcando estructura para tabla control_acceso_db.visitors
CREATE TABLE IF NOT EXISTS `visitors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dni` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `company` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_banned` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_acceso_db.visitors: ~0 rows (aproximadamente)
DELETE FROM `visitors`;
INSERT INTO `visitors` (`id`, `dni`, `full_name`, `company`, `phone`, `is_banned`) VALUES
	(1, '44444444', 'MARIO ARIAS', 'EMPRESA1', NULL, 0),
	(2, '33333333', 'JUAN ARIAS', 'EMPRESA2', NULL, 0);

-- Volcando estructura para tabla control_acceso_db.visitor_logs
CREATE TABLE IF NOT EXISTS `visitor_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visitor_id` int NOT NULL,
  `employee_to_visit_id` int DEFAULT NULL,
  `reason` text COLLATE utf8mb4_general_ci,
  `check_in` datetime DEFAULT CURRENT_TIMESTAMP,
  `check_out` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visitor_id` (`visitor_id`),
  KEY `employee_to_visit_id` (`employee_to_visit_id`),
  CONSTRAINT `visitor_logs_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`),
  CONSTRAINT `visitor_logs_ibfk_2` FOREIGN KEY (`employee_to_visit_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla control_acceso_db.visitor_logs: ~1 rows (aproximadamente)
DELETE FROM `visitor_logs`;
INSERT INTO `visitor_logs` (`id`, `visitor_id`, `employee_to_visit_id`, `reason`, `check_in`, `check_out`) VALUES
	(1, 1, NULL, 'REUNIÓN CON ÁREA LOGISTICA', '2025-11-21 10:43:22', '2025-11-21 10:44:23'),
	(2, 2, NULL, 'REUNION DE TRABAJO', '2025-11-23 16:06:17', NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
