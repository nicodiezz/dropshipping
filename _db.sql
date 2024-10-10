-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 26-09-2024 a las 20:57:34
-- Versión del servidor: 8.0.39
-- Versión de PHP: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dropshippingnueva`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_admins`
--

DROP TABLE IF EXISTS `pd_admins`;
CREATE TABLE IF NOT EXISTS `pd_admins` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(80) NOT NULL,
  `contraseña` char(60) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `habilitado` tinyint NOT NULL,
  `color` int NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `pd_admins`
--

INSERT INTO `pd_admins` (`ID`, `usuario`, `contraseña`, `nombre`, `habilitado`, `color`) VALUES
(1, 'admin', '$2y$10$5O0cGMgp2xB6em2hKn8/Memrh3ID2Jn.GJBzGxUNoXYcbl6iAf9xG', 'Cuenta de Administración Principal', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_articulos`
--

DROP TABLE IF EXISTS `pd_articulos`;
CREATE TABLE IF NOT EXISTS `pd_articulos` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `precio` decimal(15,2) NOT NULL,
  `grupoID` int NOT NULL,
  `codigo_de_barras` bigint UNSIGNED NOT NULL,
  `disponible` tinyint NOT NULL DEFAULT '1',
  `ext_de_img` varchar(5) DEFAULT NULL,
  `destacado` tinyint NOT NULL,
  `seccionID` int NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `pd_articulos_grupoid_foreign` (`grupoID`),
  KEY `pd_articulos_seccionid_foreign` (`seccionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_cambiosdeprecio`
--

DROP TABLE IF EXISTS `pd_cambiosdeprecio`;
CREATE TABLE IF NOT EXISTS `pd_cambiosdeprecio` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `articuloID` int NOT NULL,
  `cuando` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nuevoPrecio` decimal(15,2) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `pd_cambiosdeprecio_articuloid_foreign` (`articuloID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_grupos`
--

DROP TABLE IF EXISTS `pd_grupos`;
CREATE TABLE IF NOT EXISTS `pd_grupos` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_pedidos_articulos`
--

DROP TABLE IF EXISTS `pd_pedidos_articulos`;
CREATE TABLE IF NOT EXISTS `pd_pedidos_articulos` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `pedidoID` int NOT NULL,
  `articuloID` int NOT NULL,
  `cantidad` int NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `pd_pedidos_articulos_articuloid_foreign` (`articuloID`),
  KEY `pd_pedidos_articulos_pedidoid_foreign` (`pedidoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_pedidos_metadatos`
--

DROP TABLE IF EXISTS `pd_pedidos_metadatos`;
CREATE TABLE IF NOT EXISTS `pd_pedidos_metadatos` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `vendedorID` int NOT NULL,
  `cuando` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nombre` varchar(40) NOT NULL,
  `direccion` varchar(60) NOT NULL,
  `concretado` int NOT NULL DEFAULT '2',
  PRIMARY KEY (`ID`),
  KEY `pd_pedidos_metadatos_vendedorid_foreign` (`vendedorID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_reclamos`
--

DROP TABLE IF EXISTS `pd_reclamos`;
CREATE TABLE IF NOT EXISTS `pd_reclamos` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `objeto` int NOT NULL,
  `reclamo` varchar(700) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_secciones`
--

DROP TABLE IF EXISTS `pd_secciones`;
CREATE TABLE IF NOT EXISTS `pd_secciones` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(200) DEFAULT 'DEFAULT NULL',
  `grupoID` int NOT NULL,
  `parentID` int NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `pd_secciones_parentid_foreign` (`parentID`),
  KEY `pd_secciones_grupoid_foreign` (`grupoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_vendedores`
--

DROP TABLE IF EXISTS `pd_vendedores`;
CREATE TABLE IF NOT EXISTS `pd_vendedores` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(80) NOT NULL,
  `contraseña` char(60) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `correo` varchar(200) NOT NULL,
  `habilitado` tinyint NOT NULL DEFAULT '1',
  `nombre` varchar(80) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `ciudad` varchar(60) NOT NULL,
  `direccion` varchar(150) NOT NULL,
  `provincia` varchar(60) NOT NULL,
  `pais` varchar(90) NOT NULL,
  `nombreURL` varchar(80) NOT NULL,
  `minimoCompra` int NOT NULL DEFAULT '500',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_vendedores_grupos`
--

DROP TABLE IF EXISTS `pd_vendedores_grupos`;
CREATE TABLE IF NOT EXISTS `pd_vendedores_grupos` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `vendedorID` int NOT NULL,
  `grupoID` int NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `pd_vendedores_grupos_vendedorid_foreign` (`vendedorID`),
  KEY `pd_vendedores_grupos_grupoid_foreign` (`grupoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pd_articulos`
--
ALTER TABLE `pd_articulos`
  ADD CONSTRAINT `pd_articulos_grupoid_foreign` FOREIGN KEY (`grupoID`) REFERENCES `pd_grupos` (`ID`),
  ADD CONSTRAINT `pd_articulos_seccionid_foreign` FOREIGN KEY (`seccionID`) REFERENCES `pd_secciones` (`ID`);

--
-- Filtros para la tabla `pd_cambiosdeprecio`
--
ALTER TABLE `pd_cambiosdeprecio`
  ADD CONSTRAINT `pd_cambiosdeprecio_articuloid_foreign` FOREIGN KEY (`articuloID`) REFERENCES `pd_articulos` (`ID`);

--
-- Filtros para la tabla `pd_pedidos_articulos`
--
ALTER TABLE `pd_pedidos_articulos`
  ADD CONSTRAINT `pd_pedidos_articulos_articuloid_foreign` FOREIGN KEY (`articuloID`) REFERENCES `pd_articulos` (`ID`),
  ADD CONSTRAINT `pd_pedidos_articulos_pedidoid_foreign` FOREIGN KEY (`pedidoID`) REFERENCES `pd_pedidos_metadatos` (`ID`);

--
-- Filtros para la tabla `pd_pedidos_metadatos`
--
ALTER TABLE `pd_pedidos_metadatos`
  ADD CONSTRAINT `pd_pedidos_metadatos_vendedorid_foreign` FOREIGN KEY (`vendedorID`) REFERENCES `pd_vendedores` (`ID`);

--
-- Filtros para la tabla `pd_secciones`
--
ALTER TABLE `pd_secciones`
  ADD CONSTRAINT `pd_secciones_grupoid_foreign` FOREIGN KEY (`grupoID`) REFERENCES `pd_grupos` (`ID`),
  ADD CONSTRAINT `pd_secciones_parentid_foreign` FOREIGN KEY (`parentID`) REFERENCES `pd_secciones` (`ID`);

--
-- Filtros para la tabla `pd_vendedores_grupos`
--
ALTER TABLE `pd_vendedores_grupos`
  ADD CONSTRAINT `pd_vendedores_grupos_grupoid_foreign` FOREIGN KEY (`grupoID`) REFERENCES `pd_grupos` (`ID`),
  ADD CONSTRAINT `pd_vendedores_grupos_vendedorid_foreign` FOREIGN KEY (`vendedorID`) REFERENCES `pd_vendedores` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
