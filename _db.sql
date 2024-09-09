-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 05-03-2022 a las 17:22:49
-- Versión del servidor: 5.7.35-log
-- Versión de PHP: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `c2020493_alc`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_admins`
--

CREATE TABLE `pd_admins` (
  `ID` int(11) NOT NULL,
  `usuario` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `contraseña` char(60) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `nombre` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `habilitado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Volcado de datos para la tabla `pd_admins`
--

INSERT INTO `pd_admins` (`ID`, `usuario`, `contraseña`, `nombre`, `habilitado`) VALUES
(1, 'admin', '$2y$10$5O0cGMgp2xB6em2hKn8/Memrh3ID2Jn.GJBzGxUNoXYcbl6iAf9xG', 'Cuenta de Administración Principal', 1);
-- Contraseña: asd

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_articulos`
--

CREATE TABLE `pd_articulos` (
  `ID` int(11) NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `precio` decimal(15,2) NOT NULL,
  `vendedorID` int(11) NOT NULL,
  `seccionID` int(11) NOT NULL DEFAULT '0',
  `codigo_de_barras` bigint(13) UNSIGNED NOT NULL,
  `disponible` tinyint(4) NOT NULL DEFAULT '1',
  `ext_de_img` varchar(5) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `destacado` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_cambiosdelivery`
--

CREATE TABLE `pd_cambiosdelivery` (
  `cuando` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `precio` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_cambiosdeprecio`
--

CREATE TABLE `pd_cambiosdeprecio` (
  `ID` int(11) NOT NULL,
  `articuloID` int(11) NOT NULL,
  `cuando` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `nuevoPrecio` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_categorias`
--

CREATE TABLE `pd_categorias` (
  `ID` int(11) NOT NULL,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_horarios`
--

CREATE TABLE `pd_horarios` (
  `ID` int(11) NOT NULL,
  `vendedorID` int(11) NOT NULL,
  `desde` time DEFAULT NULL,
  `hasta` time DEFAULT NULL,
  `lunes` tinyint(1) NOT NULL DEFAULT '0',
  `martes` tinyint(1) NOT NULL DEFAULT '0',
  `miercoles` tinyint(1) NOT NULL DEFAULT '0',
  `jueves` tinyint(1) NOT NULL DEFAULT '0',
  `viernes` tinyint(1) NOT NULL DEFAULT '0',
  `sabado` tinyint(1) NOT NULL DEFAULT '0',
  `domingo` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_pedidos_articulos`
--

CREATE TABLE `pd_pedidos_articulos` (
  `ID` int(11) NOT NULL,
  `pedidoID` int(11) NOT NULL,
  `articuloID` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_pedidos_metadatos`
--

CREATE TABLE `pd_pedidos_metadatos` (
  `ID` int(11) NOT NULL,
  `vendedorID` int(11) NOT NULL,
  `cuando` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nombre` varchar(40) COLLATE utf8mb4_bin NOT NULL,
  `direccion` varchar(60) COLLATE utf8mb4_bin NOT NULL,
  `concretado` int(11) NOT NULL DEFAULT '2',
  `delivery` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_reclamos`
--

CREATE TABLE `pd_reclamos` (
  `ID` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `objeto` int(11) NOT NULL,
  `reclamo` varchar(700) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_secciones`
--

CREATE TABLE `pd_secciones` (
  `ID` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `vendedorID` int(11) NOT NULL,
  `parentID` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pd_vendedores`
--

CREATE TABLE `pd_vendedores` (
  `ID` int(11) NOT NULL,
  `usuario` varchar(80) COLLATE utf8mb4_bin NOT NULL,
  `contraseña` char(60) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `numero` varchar(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `correo` varchar(200) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `lat` decimal(9,6) NOT NULL,
  `lon` decimal(9,6) NOT NULL,
  `habilitado` tinyint(1) NOT NULL DEFAULT '1',
  `nombre` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` varchar(100) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `ciudad` varchar(60) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `direccion` varchar(150) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `provincia` varchar(60) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `pais` varchar(90) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `nombreURL` varchar(80) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `categoriaID` int(11) NOT NULL,
  `color` varchar(6) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `minimoCompra` int(11) NOT NULL DEFAULT '500'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `pd_admins`
--
ALTER TABLE `pd_admins`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `pd_articulos`
--
ALTER TABLE `pd_articulos`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `pd_cambiosdelivery`
--
ALTER TABLE `pd_cambiosdelivery`
  ADD PRIMARY KEY (`cuando`);

--
-- Indices de la tabla `pd_cambiosdeprecio`
--
ALTER TABLE `pd_cambiosdeprecio`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `pd_categorias`
--
ALTER TABLE `pd_categorias`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `pd_horarios`
--
ALTER TABLE `pd_horarios`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `pd_pedidos_articulos`
--
ALTER TABLE `pd_pedidos_articulos`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `pd_pedidos_metadatos`
--
ALTER TABLE `pd_pedidos_metadatos`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `pd_reclamos`
--
ALTER TABLE `pd_reclamos`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `pd_secciones`
--
ALTER TABLE `pd_secciones`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `pd_vendedores`
--
ALTER TABLE `pd_vendedores`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pd_admins`
--
ALTER TABLE `pd_admins`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pd_articulos`
--
ALTER TABLE `pd_articulos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pd_cambiosdeprecio`
--
ALTER TABLE `pd_cambiosdeprecio`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pd_categorias`
--
ALTER TABLE `pd_categorias`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pd_horarios`
--
ALTER TABLE `pd_horarios`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pd_pedidos_articulos`
--
ALTER TABLE `pd_pedidos_articulos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pd_pedidos_metadatos`
--
ALTER TABLE `pd_pedidos_metadatos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pd_reclamos`
--
ALTER TABLE `pd_reclamos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pd_secciones`
--
ALTER TABLE `pd_secciones`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pd_vendedores`
--
ALTER TABLE `pd_vendedores`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
