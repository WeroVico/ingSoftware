-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-11-2025 a las 23:55:34
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `lockers`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lista_espera`
--

CREATE TABLE `lista_espera` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pendiente','notificado') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `lista_espera`
--

INSERT INTO `lista_espera` (`id`, `id_usuario`, `id_modulo`, `fecha_registro`, `status`) VALUES
(1, 1, 2, '2025-09-17 04:26:15', 'notificado'),
(2, 3, 2, '2025-09-17 04:27:52', 'notificado'),
(3, 4, 2, '2025-09-17 04:28:04', 'notificado'),
(4, 8, 1, '2025-09-24 19:24:21', 'notificado'),
(7, 7, 1, '2025-09-24 19:25:10', 'notificado'),
(9, 3, 1, '2025-09-29 19:50:03', 'notificado'),
(10, 2, 1, '2025-09-29 19:51:07', 'notificado'),
(19, 2, 2, '2025-11-23 04:13:22', 'notificado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `locker`
--

CREATE TABLE `locker` (
  `id` int(11) NOT NULL,
  `id_modulo` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `etiqueta_completa` varchar(10) NOT NULL,
  `status` enum('disponible','ocupado','mantenimiento') NOT NULL DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cada locker individual con su estado';

--
-- Volcado de datos para la tabla `locker`
--

INSERT INTO `locker` (`id`, `id_modulo`, `numero`, `etiqueta_completa`, `status`) VALUES
(1, 1, 1, 'X-1', 'disponible'),
(2, 1, 2, 'X-2', 'disponible'),
(3, 1, 3, 'X-3', 'disponible'),
(4, 1, 4, 'X-4', 'disponible'),
(5, 1, 5, 'X-5', 'disponible'),
(6, 1, 6, 'X-6', 'disponible'),
(7, 1, 7, 'X-7', 'disponible'),
(8, 1, 8, 'X-8', 'disponible'),
(9, 1, 9, 'X-9', 'disponible'),
(10, 1, 10, 'X-10', 'disponible'),
(11, 1, 11, 'X-11', 'disponible'),
(12, 1, 12, 'X-12', 'disponible'),
(13, 1, 13, 'X-13', 'disponible'),
(14, 1, 14, 'X-14', 'disponible'),
(15, 1, 15, 'X-15', 'disponible'),
(16, 1, 16, 'X-16', 'disponible'),
(17, 1, 17, 'X-17', 'disponible'),
(18, 1, 18, 'X-18', 'disponible'),
(19, 1, 19, 'X-19', 'disponible'),
(20, 1, 20, 'X-20', 'disponible'),
(21, 2, 1, 'E-1', 'ocupado'),
(22, 2, 2, 'E-2', 'disponible'),
(23, 2, 3, 'E-3', 'disponible'),
(24, 2, 4, 'E-4', 'disponible'),
(25, 2, 5, 'E-5', 'disponible'),
(26, 2, 6, 'E-6', 'disponible'),
(27, 2, 7, 'E-7', 'disponible'),
(28, 2, 8, 'E-8', 'disponible'),
(29, 2, 9, 'E-9', 'disponible'),
(30, 2, 10, 'E-10', 'disponible'),
(31, 2, 11, 'E-11', 'disponible'),
(32, 2, 12, 'E-12', 'disponible'),
(33, 2, 13, 'E-13', 'disponible'),
(34, 2, 14, 'E-14', 'disponible'),
(35, 2, 15, 'E-15', 'disponible'),
(36, 2, 16, 'E-16', 'disponible'),
(37, 2, 17, 'E-17', 'disponible'),
(38, 2, 18, 'E-18', 'disponible'),
(39, 2, 19, 'E-19', 'disponible'),
(40, 2, 20, 'E-20', 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo`
--

CREATE TABLE `modulo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `etiqueta` char(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena los edificios o zonas de lockers';

--
-- Volcado de datos para la tabla `modulo`
--

INSERT INTO `modulo` (`id`, `nombre`, `etiqueta`) VALUES
(1, 'Módulo X', 'X'),
(2, 'Módulo E', 'E');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservacion`
--

CREATE TABLE `reservacion` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_locker` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` datetime NOT NULL,
  `status` enum('activa','vencida','cancelada') NOT NULL DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Vincula un usuario con un locker';

--
-- Volcado de datos para la tabla `reservacion`
--

INSERT INTO `reservacion` (`id`, `id_usuario`, `id_locker`, `fecha_inicio`, `fecha_fin`, `status`) VALUES
(2, 1, 40, '2025-09-16 20:41:49', '2025-12-20 00:00:00', 'cancelada'),
(3, 1, 15, '2025-09-16 20:44:44', '2025-12-20 00:00:00', 'cancelada'),
(4, 3, 36, '2025-09-16 20:45:36', '2025-12-20 00:00:00', 'cancelada'),
(5, 3, 16, '2025-09-16 20:47:19', '2025-12-20 00:00:00', 'cancelada'),
(6, 7, 22, '2025-09-24 13:19:17', '2025-09-24 15:19:17', 'cancelada'),
(7, 8, 21, '2025-09-24 13:26:43', '2025-09-24 19:26:43', 'cancelada'),
(8, 8, 1, '2025-09-24 13:35:58', '2025-09-24 15:35:58', 'cancelada'),
(9, 2, 21, '2025-09-29 13:46:04', '2025-09-29 21:46:04', 'cancelada'),
(10, 2, 21, '2025-09-29 14:19:16', '2025-09-29 16:19:16', 'cancelada'),
(11, 2, 21, '2025-10-06 13:30:12', '2025-10-06 15:30:12', 'cancelada'),
(12, 2, 37, '2025-10-06 14:14:31', '2025-10-06 16:14:31', 'cancelada'),
(13, 2, 22, '2025-10-15 14:25:34', '2025-10-16 05:25:34', 'cancelada'),
(14, 11, 24, '2025-11-10 14:15:34', '2025-11-10 20:15:34', 'cancelada'),
(15, 11, 21, '2025-11-10 14:34:17', '2025-11-10 14:36:17', 'cancelada'),
(16, 11, 21, '2025-11-10 14:40:05', '2025-11-10 16:40:05', 'cancelada'),
(17, 11, 1, '2025-11-10 14:40:15', '2025-11-10 14:42:15', 'activa'),
(18, 2, 23, '2025-11-22 22:12:22', '2025-11-23 00:12:22', 'cancelada'),
(19, 2, 21, '2025-11-22 22:14:09', '2025-11-23 00:14:09', 'cancelada'),
(20, 2, 21, '2025-11-22 22:16:53', '2025-11-23 00:16:53', 'cancelada'),
(21, 2, 21, '2025-11-24 16:55:09', '2025-11-24 18:55:09', 'activa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sistema_logs`
--

CREATE TABLE `sistema_logs` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `detalles` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `ip_usuario` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sistema_logs`
--

INSERT INTO `sistema_logs` (`id`, `id_usuario`, `accion`, `detalles`, `fecha`, `ip_usuario`) VALUES
(1, 2, 'ACCESO_SISTEMA', '{\"perfil_usuario\":{\"id\":2,\"nombre\":\"Angel Ulises\",\"apellidos\":\"Tinoco\",\"codigo\":\"220581409\",\"correo\":\"angel.tinoco8140@alumnos.udg.mx\",\"rol\":2},\"datos_evento\":{\"mensaje\":\"Inicio de sesión exitoso\",\"tipo\":\"login\"}}', '2025-11-22 22:35:32', '::1'),
(2, 2, 'ERROR_SISTEMA', '{\"perfil_usuario\":{\"id\":2,\"nombre\":\"Angel Ulises\",\"apellidos\":\"Tinoco\",\"codigo\":\"220581409\",\"correo\":\"angel.tinoco8140@alumnos.udg.mx\",\"rol\":2},\"datos_evento\":{\"accion_intentada\":\"INTENTO_RESERVA_FALLIDO\",\"error_reportado\":\"Tu reservación no puede terminar después de las 9:00 PM.\",\"contexto\":{\"id_locker_intentado\":21}}}', '2025-11-22 22:35:51', '::1'),
(3, 2, 'SALIDA_SISTEMA', '{\"perfil_usuario\":{\"id\":2,\"nombre\":\"Angel Ulises\",\"apellidos\":\"Tinoco\",\"codigo\":\"220581409\",\"correo\":\"angel.tinoco8140@alumnos.udg.mx\",\"rol\":2},\"datos_evento\":{\"mensaje\":\"Cierre de sesión voluntario\",\"tipo\":\"logout\"}}', '2025-11-22 22:44:00', '::1'),
(4, 2, 'ACCESO_SISTEMA', '{\"perfil_usuario\":{\"id\":2,\"nombre\":\"Angel Ulises\",\"apellidos\":\"Tinoco\",\"codigo\":\"220581409\",\"correo\":\"angel.tinoco8140@alumnos.udg.mx\",\"rol\":2},\"datos_evento\":{\"mensaje\":\"Inicio de sesión exitoso\",\"tipo\":\"login\"}}', '2025-11-22 22:46:08', '::1'),
(5, 2, 'SALIDA_SISTEMA', '{\"perfil_usuario\":{\"id\":2,\"nombre\":\"Uli\",\"apellidos\":\"Tinoco\",\"codigo\":\"220581409\",\"correo\":\"angel.tinoco8140@alumnos.udg.mx\",\"rol\":2},\"datos_evento\":{\"mensaje\":\"Cierre de sesión voluntario\",\"tipo\":\"logout\"}}', '2025-11-22 22:46:40', '::1'),
(6, 2, 'ACCESO_SISTEMA', '{\"perfil_usuario\":{\"id\":2,\"nombre\":\"Uli\",\"apellidos\":\"Tinoco\",\"codigo\":\"220581409\",\"correo\":\"angel.tinoco8140@alumnos.udg.mx\",\"rol\":2},\"datos_evento\":{\"mensaje\":\"Inicio de sesión exitoso\",\"tipo\":\"login\"}}', '2025-11-22 22:47:10', '::1'),
(7, 2, 'SALIDA_SISTEMA', '{\"perfil_usuario\":{\"id\":2,\"nombre\":\"Angel Ulises\",\"apellidos\":\"Tinoco\",\"codigo\":\"220581409\",\"correo\":\"angel.tinoco8140@alumnos.udg.mx\",\"rol\":2},\"datos_evento\":{\"mensaje\":\"Cierre de sesión voluntario\",\"tipo\":\"logout\"}}', '2025-11-22 22:47:18', '::1'),
(8, 12, 'REGISTRO_NUEVO_USUARIO', '{\"perfil_usuario\":{\"id\":12,\"nombre\":\"Testeando\",\"apellidos\":\"Login automatico\",\"codigo\":\"0000333\",\"correo\":\"testtest@mail.com\",\"rol\":2},\"datos_evento\":{\"rol_asignado\":2,\"codigo\":\"0000333\"}}', '2025-11-22 22:56:03', '::1'),
(9, 12, 'ACCESO_SISTEMA', '{\"perfil_usuario\":{\"id\":12,\"nombre\":\"Testeando\",\"apellidos\":\"Login automatico\",\"codigo\":\"0000333\",\"correo\":\"testtest@mail.com\",\"rol\":2},\"datos_evento\":{\"mensaje\":\"Inicio de sesión exitoso\",\"tipo\":\"login\"}}', '2025-11-22 22:56:03', '::1'),
(10, 12, 'SALIDA_SISTEMA', '{\"perfil_usuario\":{\"id\":12,\"nombre\":\"Testeando\",\"apellidos\":\"Login automatico\",\"codigo\":\"0000333\",\"correo\":\"testtest@mail.com\",\"rol\":2},\"datos_evento\":{\"mensaje\":\"Cierre de sesión voluntario\",\"tipo\":\"logout\"}}', '2025-11-22 22:56:10', '::1'),
(11, 2, 'ACCESO_SISTEMA', '{\"perfil_usuario\":{\"id\":2,\"nombre\":\"Angel Ulises\",\"apellidos\":\"Tinoco\",\"codigo\":\"220581409\",\"correo\":\"angel.tinoco8140@alumnos.udg.mx\",\"rol\":2},\"datos_evento\":{\"mensaje\":\"Inicio de sesión exitoso\",\"tipo\":\"login\"}}', '2025-11-24 16:55:06', '::1'),
(12, 2, 'NUEVA_RESERVA', '{\"perfil_usuario\":{\"id\":2,\"nombre\":\"Angel Ulises\",\"apellidos\":\"Tinoco\",\"codigo\":\"220581409\",\"correo\":\"angel.tinoco8140@alumnos.udg.mx\",\"rol\":2},\"datos_evento\":{\"id_locker\":21,\"duracion_seleccionada\":2,\"fecha_fin_calculada\":\"2025-11-24 18:55:09\",\"nota\":\"Usuario reservó exitosamente\"}}', '2025-11-24 16:55:09', '::1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(128) NOT NULL,
  `apellidos` varchar(128) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `archivo_nombre` varchar(255) DEFAULT NULL,
  `archivo_file` varchar(255) DEFAULT NULL,
  `eliminado` tinyint(1) DEFAULT 0,
  `fecha_alta` timestamp NOT NULL DEFAULT current_timestamp(),
  `rol` tinyint(1) NOT NULL DEFAULT 2 COMMENT '1: Admin, 2: Estudiante'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `apellidos`, `codigo`, `correo`, `pass`, `archivo_nombre`, `archivo_file`, `eliminado`, `fecha_alta`, `rol`) VALUES
(1, 'Ulises', 'Tinoco', '220581409', 'angelcampeon2005@gmail.com', '$2y$10$/gryKpQYg.pSqYSRcUW9eulKAEr.ueKd05BGyUGo0SJFstzrctL.K', 'Speedrunner guy.jpg', 'cc45e169f5e8195e0acf9db5cf38b0e9.jpg', 0, '2025-09-17 00:13:17', 2),
(2, 'Angel Ulises', 'Tinoco', '220581409', 'angel.tinoco8140@alumnos.udg.mx', '$2y$10$Pmn3eEnIz4gfd3XF9gFwCefo.KUFCJ7cg09iuLdcCEyeHtmUQrNXy', 'a.jpg', '6e398e4c1b20ee6691670ba521be6b23.jpg', 0, '2025-09-17 00:20:44', 2),
(3, 'Tester', 'Tinoco', '0', 'test@mail.com', '$2y$10$KnZWy4VKRj8aSrxttmx3vOWav/imeunMLnQvfGWUFVXkcAKWAQTOi', '1.png', '9c33784be9289bd786fc4f6bd4dcb9d1.png', 0, '2025-09-17 02:43:29', 1),
(4, 'ANGEL TESTER', 'ANGEL', '1', 'soyulisesyesteesminuevocorreo@gmail.com', '$2y$10$yJSU9xrYm2WoxNDasUXFr..t9EyUhMcGqrGHZmK5PFaF5iyCQvkme', 'chara.png', '2aa6ef9a4c7c339fc1ea94faeb1e7e11.png', 0, '2025-09-17 03:57:07', 1),
(5, 'Tester', 'a', '2', 'asdf@mail.com', '$2y$10$2ExkSUptA8nn2ljZVJzKuOb3RCMLdrXDYTlGdzxFhx3aOoku/qgxS', '20211224_213753.jpg', '2849a3cf925ae7d7144db4d3c41fed40.jpg', 0, '2025-09-18 00:05:48', 2),
(6, 'tester', '1', '3', 'tester@mail.com', '$2y$10$04fizTfnvjMQv6CitnBYDeqR6Dd9EFPoF7BHUtrO3VbjpVJ9ez0OG', '20211229_233053.jpg', '6c3a7c99467f742bd5dc27543df1b92b.jpg', 0, '2025-09-18 00:06:37', 2),
(7, 'Ulises', 'Zanchez', '1111', 'ulises@mail.com', '$2y$10$CP521NYQhxlKiPHbD2KTy.fuXG/mnjCyWtzmGjFMI4AfDTQzbIYha', 'Ulises.jpg', '48ea35024e809cf7325ab89dea900862.jpg', 0, '2025-09-24 19:18:48', 1),
(8, 'Yo', 'Angel', '220581409', 'void.vi.co01@gmail.com', '$2y$10$xs3hoA25aOmeWCMtUPbGl.Dy/uJRPUZu128n697G35RbMKtgSXVS6', 'SOBRECARGA.png', 'aaef16b440f3f7cd19f9c23e05ff87ad.png', 0, '2025-09-24 19:22:47', 2),
(9, 'Testerperron', 'asdf', '1', 'admin@mail.com', '$2y$10$4rfoY1m0CwDIX7KFE1lWN.VN4Oz5xjFU/0bTpF9bdh0gK1PRqDkGS', 'dead-by-daylight-springtrap-the-animatronic.jpg', 'f2ab559b6e23e39d8f1ec4226639fbd0.jpg', 0, '2025-09-29 19:39:23', 1),
(10, 'testeo', 'asdf', '1111', 'asdfasdf@mail', '$2y$10$VifsfbRFH0lS5Xd5rO8/y.T3gIVMZ.Fpgiz6k2KOalTuyvhZmrhga', 'Captura de pantalla 2025-09-03 102139.png', 'ca39d4cc2020cfff43188be473fe01c6.png', 0, '2025-10-06 20:41:39', 2),
(11, 'pepe', 'perez', '00000000069', 'correomail@mail.com', '$2y$10$8/tfvjTw9thRLuAu1XUEvOnGCSfWu4nQQVzscew2T.2BE0W/q5aya', 'Captura de pantalla (6).png', '0d1900ff266049556a420271ca6620ff.png', 0, '2025-11-10 19:57:05', 1),
(12, 'Testeando', 'Login automatico', '0000333', 'testtest@mail.com', '$2y$10$tgefWTfCRFx.r/UOrpKmP.U0aBETApH8T/./ecrGF/Mox6JM2k8ZO', 'ugh.gif', '2c19c9eac0b3fffe09bd4d3a3047efa7.gif', 0, '2025-11-23 04:56:03', 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `lista_espera`
--
ALTER TABLE `lista_espera`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`,`id_modulo`),
  ADD KEY `id_modulo` (`id_modulo`);

--
-- Indices de la tabla `locker`
--
ALTER TABLE `locker`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `etiqueta_completa` (`etiqueta_completa`),
  ADD KEY `id_modulo` (`id_modulo`);

--
-- Indices de la tabla `modulo`
--
ALTER TABLE `modulo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `etiqueta` (`etiqueta`);

--
-- Indices de la tabla `reservacion`
--
ALTER TABLE `reservacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_locker` (`id_locker`),
  ADD KEY `fk_reservaciones_usuarios` (`id_usuario`);

--
-- Indices de la tabla `sistema_logs`
--
ALTER TABLE `sistema_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `lista_espera`
--
ALTER TABLE `lista_espera`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `locker`
--
ALTER TABLE `locker`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `modulo`
--
ALTER TABLE `modulo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `reservacion`
--
ALTER TABLE `reservacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `sistema_logs`
--
ALTER TABLE `sistema_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `lista_espera`
--
ALTER TABLE `lista_espera`
  ADD CONSTRAINT `lista_espera_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `lista_espera_ibfk_2` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id`);

--
-- Filtros para la tabla `locker`
--
ALTER TABLE `locker`
  ADD CONSTRAINT `locker_ibfk_1` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id`);

--
-- Filtros para la tabla `reservacion`
--
ALTER TABLE `reservacion`
  ADD CONSTRAINT `fk_reservaciones_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `reservacion_ibfk_2` FOREIGN KEY (`id_locker`) REFERENCES `locker` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
