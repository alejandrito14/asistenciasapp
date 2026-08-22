/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80040 (8.0.40)
 Source Host           : localhost:8889
 Source Schema         : control_asistencia_db

 Target Server Type    : MySQL
 Target Server Version : 80040 (8.0.40)
 File Encoding         : 65001

 Date: 04/08/2026 22:13:51
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for alumnos
-- ----------------------------
DROP TABLE IF EXISTS `alumnos`;
CREATE TABLE `alumnos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `matricula` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `apellido_paterno` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `apellido_materno` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `correo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tutor_nombre` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tutor_apellido_paterno` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tutor_apellido_materno` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tutor_telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tutor_correo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricula` (`matricula`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `alumnos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for alumnos_grupos
-- ----------------------------
DROP TABLE IF EXISTS `alumnos_grupos`;
CREATE TABLE `alumnos_grupos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alumno_id` int NOT NULL,
  `grupo_id` int NOT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alumno_id` (`alumno_id`),
  KEY `grupo_id` (`grupo_id`),
  CONSTRAINT `alumnos_grupos_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`),
  CONSTRAINT `alumnos_grupos_ibfk_2` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for asistencias
-- ----------------------------
DROP TABLE IF EXISTS `asistencias`;
CREATE TABLE `asistencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sesion_clase_id` int NOT NULL,
  `alumno_id` int NOT NULL,
  `estado_id` int NOT NULL,
  `hora_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `observaciones` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sesion_clase_id` (`sesion_clase_id`,`alumno_id`),
  KEY `alumno_id` (`alumno_id`),
  KEY `estado_id` (`estado_id`),
  CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`sesion_clase_id`) REFERENCES `sesiones_clase` (`id`),
  CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`),
  CONSTRAINT `asistencias_ibfk_3` FOREIGN KEY (`estado_id`) REFERENCES `catalogo_asistencia` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for attendance_logs
-- ----------------------------
DROP TABLE IF EXISTS `attendance_logs`;
CREATE TABLE `attendance_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `date_log` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `total_hours` decimal(5,2) DEFAULT NULL,
  `status` enum('a_tiempo','tarde') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'a_tiempo',
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for catalogo_asistencia
-- ----------------------------
DROP TABLE IF EXISTS `catalogo_asistencia`;
CREATE TABLE `catalogo_asistencia` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for departments
-- ----------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('activo','inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for employees
-- ----------------------------
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'default.png',
  `status` enum('activo','inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'activo',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`employee_code`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for grupo_materia_maestro
-- ----------------------------
DROP TABLE IF EXISTS `grupo_materia_maestro`;
CREATE TABLE `grupo_materia_maestro` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_materia_id` int NOT NULL,
  `maestro_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grupo_materia_id` (`grupo_materia_id`),
  KEY `maestro_id` (`maestro_id`),
  CONSTRAINT `grupo_materia_maestro_ibfk_1` FOREIGN KEY (`grupo_materia_id`) REFERENCES `grupo_materias` (`id`),
  CONSTRAINT `grupo_materia_maestro_ibfk_2` FOREIGN KEY (`maestro_id`) REFERENCES `maestros` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for grupo_materias
-- ----------------------------
DROP TABLE IF EXISTS `grupo_materias`;
CREATE TABLE `grupo_materias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_id` int NOT NULL,
  `materia_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grupo_id` (`grupo_id`),
  KEY `materia_id` (`materia_id`),
  CONSTRAINT `grupo_materias_ibfk_1` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`),
  CONSTRAINT `grupo_materias_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for grupos
-- ----------------------------
DROP TABLE IF EXISTS `grupos`;
CREATE TABLE `grupos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `grado` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `semestre` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `turno` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ciclo_escolar` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for horarios
-- ----------------------------
DROP TABLE IF EXISTS `horarios`;
CREATE TABLE `horarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_materia_maestro_id` int NOT NULL,
  `dia_semana` enum('LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `aula` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grupo_materia_maestro_id` (`grupo_materia_maestro_id`),
  CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`grupo_materia_maestro_id`) REFERENCES `grupo_materia_maestro` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for incidents
-- ----------------------------
DROP TABLE IF EXISTS `incidents`;
CREATE TABLE `incidents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `severity` enum('baja','media','alta') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'baja',
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `incidents_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for maestros
-- ----------------------------
DROP TABLE IF EXISTS `maestros`;
CREATE TABLE `maestros` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `apellido_paterno` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `apellido_materno` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `correo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `maestros_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for materias
-- ----------------------------
DROP TABLE IF EXISTS `materias`;
CREATE TABLE `materias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for sesiones_clase
-- ----------------------------
DROP TABLE IF EXISTS `sesiones_clase`;
CREATE TABLE `sesiones_clase` (
  `id` int NOT NULL AUTO_INCREMENT,
  `grupo_materia_maestro_id` int NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio_real` datetime DEFAULT NULL,
  `hora_fin_real` datetime DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `estatus` enum('ABIERTA','CERRADA') COLLATE utf8mb4_general_ci DEFAULT 'ABIERTA',
  `usuario_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grupo_materia_maestro_id` (`grupo_materia_maestro_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `sesiones_clase_ibfk_1` FOREIGN KEY (`grupo_materia_maestro_id`) REFERENCES `grupo_materia_maestro` (`id`),
  CONSTRAINT `sesiones_clase_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','guardia') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'guardia',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('activo','inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for usuarios
-- ----------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rol` enum('ADMIN','MAESTRO','ALUMNO') COLLATE utf8mb4_general_ci NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `estado_cuenta` enum('PENDIENTE','ACTIVO','ELIMINADO') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ACTIVO',
  `verification_code_hash` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `verification_expires_at` datetime DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for visitor_logs
-- ----------------------------
DROP TABLE IF EXISTS `visitor_logs`;
CREATE TABLE `visitor_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visitor_id` int NOT NULL,
  `employee_to_visit_id` int DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `check_in` datetime DEFAULT CURRENT_TIMESTAMP,
  `check_out` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visitor_id` (`visitor_id`),
  KEY `employee_to_visit_id` (`employee_to_visit_id`),
  CONSTRAINT `visitor_logs_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`),
  CONSTRAINT `visitor_logs_ibfk_2` FOREIGN KEY (`employee_to_visit_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for visitors
-- ----------------------------
DROP TABLE IF EXISTS `visitors`;
CREATE TABLE `visitors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dni` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `company` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_banned` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
