-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 27-04-2026 a las 20:08:24
-- Versión del servidor: 10.11.14-MariaDB-cll-lve
-- Versión de PHP: 8.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u6514444_streamhub`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `canales`
--

CREATE TABLE `canales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `logo` varchar(500) DEFAULT NULL,
  `stream_url` varchar(1000) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `views` varchar(20) DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `canales`
--

INSERT INTO `canales` (`id`, `nombre`, `logo`, `stream_url`, `category`, `views`, `activo`) VALUES
(914, 'ESPN', 'https://upload.wikimedia.org/wikipedia/commons/2/2f/ESPN_wordmark.svg', 'https://www.youtube.com/embed/live_stream?channel=UCiWLfSweyRNmLpgEHekhoAg', 'Deportes', '12.4K', 1),
(915, 'Fox Sports', 'https://upload.wikimedia.org/wikipedia/commons/7/74/Fox_Sports_2019.svg', 'https://www.youtube.com/embed/live_stream?channel=UCiWLfSweyRNmLpgEHekhoAg', 'Deportes', '8.7K', 1),
(916, 'Sky Sports', 'https://upload.wikimedia.org/wikipedia/en/thumb/a/a6/Sky_Sports_logo_2020.svg/1200px-Sky_Sports_logo_2020.svg.png', 'https://www.youtube.com/embed/live_stream?channel=UCiWLfSweyRNmLpgEHekhoAg', 'Deportes', '7.8K', 1),
(920, 'TUDN', 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3d/TUDN_logo.svg/2560px-TUDN_logo.svg.png', 'https://www.youtube.com/embed/live_stream?channel=UCiWLfSweyRNmLpgEHekhoAg', 'Deportes', '9.3K', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_canal`
--

CREATE TABLE `categorias_canal` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias_canal`
--

INSERT INTO `categorias_canal` (`id`, `nombre`) VALUES
(6, 'Adultos'),
(1, 'Deportes'),
(3, 'Entretenimiento'),
(5, 'Infantil'),
(4, 'Música'),
(2, 'Noticias'),
(7, 'Películas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config_sitio`
--

CREATE TABLE `config_sitio` (
  `clave` varchar(60) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `config_sitio`
--

INSERT INTO `config_sitio` (`clave`, `valor`, `descripcion`) VALUES
('color_acento', '#8b5cf6', 'Color principal de la interfaz'),
('mantenimiento', '1', '1 = activar modo mantenimiento'),
('max_fuentes_canal', '10', 'Máximo de fuentes por canal'),
('registro_abierto', '1', '1 = permite nuevos registros'),
('sitio_descripcion', 'TV en Vivo & Deportes', 'Descripción del sitio'),
('sitio_logo', '', 'URL del logo principal'),
('sitio_nombre', 'StreamHub', 'Nombre del sitio'),
('sofascore_timezone', 'America/Tegucigalpa', 'Timezone para timestamps de sofascore'),
('timezone', 'America/Tegucigalpa', 'Zona horaria del servidor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `logo` varchar(500) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipos`
--

INSERT INTO `equipos` (`id`, `nombre`, `logo`, `pais`) VALUES
(3, 'Wolverhampton', 'assets/img/equipos/sf/3.png', 'england'),
(6, 'Burnley', 'assets/img/equipos/sf/6.png', 'england'),
(7, 'Crystal Palace', 'assets/img/equipos/sf/7.png', 'england'),
(14, 'Nottingham Forest', 'assets/img/equipos/sf/14.png', 'england'),
(17, 'Manchester City', 'assets/img/equipos/sf/17.png', 'england'),
(30, 'Brighton & Hove Albion', 'assets/img/equipos/sf/30.png', 'england'),
(33, 'Tottenham Hotspur', 'assets/img/equipos/sf/33.png', 'england'),
(34, 'Leeds United', 'assets/img/equipos/sf/34.png', 'england'),
(35, 'Manchester United', 'assets/img/equipos/sf/35.png', 'england'),
(37, 'West Ham United', 'assets/img/equipos/sf/37.png', 'england'),
(38, 'Chelsea', 'assets/img/equipos/sf/38.png', 'england'),
(39, 'Newcastle United', 'assets/img/equipos/sf/39.png', 'england'),
(40, 'Aston Villa', 'assets/img/equipos/sf/40.png', 'england'),
(41, 'Sunderland', 'assets/img/equipos/sf/41.png', 'england'),
(42, 'Arsenal', 'assets/img/equipos/sf/42.png', 'england'),
(43, 'Fulham', 'assets/img/equipos/sf/43.png', 'england'),
(44, 'Liverpool', 'assets/img/equipos/sf/44.png', 'england'),
(48, 'Everton', 'assets/img/equipos/sf/48.png', 'england'),
(50, 'Brentford', 'assets/img/equipos/sf/50.png', 'england'),
(60, 'Bournemouth', 'assets/img/equipos/sf/60.png', 'england');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fuentes`
--

CREATE TABLE `fuentes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `canal` int(11) NOT NULL,
  `url` text NOT NULL,
  `ck_key` varchar(500) DEFAULT NULL,
  `ck_keyid` varchar(500) DEFAULT NULL,
  `pais` int(11) DEFAULT NULL,
  `tipo` int(11) NOT NULL,
  `epg` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `fuentes`
--

INSERT INTO `fuentes` (`id`, `nombre`, `canal`, `url`, `ck_key`, `ck_keyid`, `pais`, `tipo`, `epg`, `activo`, `created_at`) VALUES
(1, 'ESPN AR', 914, 'PRUEAB.M3U8', NULL, NULL, 6, 1, NULL, 1, '2026-04-26 16:52:01'),
(2, 'ESPN BR', 914, 'PRUEAB.M3U8', NULL, NULL, 6, 1, NULL, 1, '2026-04-26 16:52:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ligas`
--

CREATE TABLE `ligas` (
  `id` int(11) NOT NULL,
  `ligaNombre` varchar(200) NOT NULL,
  `ligaImg` varchar(500) DEFAULT NULL,
  `ligaPais` varchar(100) DEFAULT NULL,
  `tipo` varchar(60) DEFAULT 'soccer',
  `season` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ligas`
--

INSERT INTO `ligas` (`id`, `ligaNombre`, `ligaImg`, `ligaPais`, `tipo`, `season`, `activo`) VALUES
(17, 'Premier League', 'premier-league', 'england', 'football', '76986', 1),
(23, 'Serie A', 'serie-a', 'italy', 'football', '76457', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paises`
--

CREATE TABLE `paises` (
  `id` int(11) NOT NULL,
  `paisCodigo` varchar(60) NOT NULL,
  `paisNombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `paises`
--

INSERT INTO `paises` (`id`, `paisCodigo`, `paisNombre`) VALUES
(1, 'international', 'Internacional'),
(2, 'es', 'España'),
(3, 'us', 'Estados Unidos'),
(4, 'mx', 'México'),
(5, 'hn', 'Honduras'),
(6, 'ar', 'Argentina'),
(7, 'england', 'England'),
(77, 'italy', 'Italy');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidos`
--

CREATE TABLE `partidos` (
  `id` int(11) NOT NULL,
  `local` int(11) NOT NULL,
  `visitante` int(11) DEFAULT NULL,
  `liga` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `tipo` varchar(60) DEFAULT NULL,
  `starp` int(11) DEFAULT NULL,
  `vix` int(11) DEFAULT NULL,
  `canal1` int(11) DEFAULT NULL,
  `canal2` int(11) DEFAULT NULL,
  `canal3` int(11) DEFAULT NULL,
  `canal4` int(11) DEFAULT NULL,
  `canal5` int(11) DEFAULT NULL,
  `canal6` int(11) DEFAULT NULL,
  `canal7` int(11) DEFAULT NULL,
  `canal8` int(11) DEFAULT NULL,
  `canal9` int(11) DEFAULT NULL,
  `canal10` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `partidos`
--

INSERT INTO `partidos` (`id`, `local`, `visitante`, `liga`, `fecha_hora`, `tipo`, `starp`, `vix`, `canal1`, `canal2`, `canal3`, `canal4`, `canal5`, `canal6`, `canal7`, `canal8`, `canal9`, `canal10`) VALUES
(14023925, 38, 14, 17, '2026-05-04 08:00:00', 'football', 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023926, 35, 50, 17, '2026-04-27 13:00:00', 'football', 1, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023927, 40, 33, 17, '2026-05-03 12:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023928, 40, 44, 17, '2026-05-17 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023929, 6, 40, 17, '2026-05-10 07:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023930, 48, 17, 17, '2026-05-04 13:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023931, 42, 43, 17, '2026-05-02 10:30:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023935, 30, 3, 17, '2026-05-09 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023936, 60, 17, 17, '2026-05-17 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023937, 7, 48, 17, '2026-05-10 07:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023938, 41, 35, 17, '2026-05-09 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023939, 35, 44, 17, '2026-05-03 08:30:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023940, 34, 6, 17, '2026-05-01 13:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023942, 37, 42, 17, '2026-05-10 09:30:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023943, 14, 39, 17, '2026-05-10 07:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023944, 33, 34, 17, '2026-05-11 13:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023945, 50, 37, 17, '2026-05-02 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023946, 17, 50, 17, '2026-05-09 10:30:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023948, 43, 60, 17, '2026-05-09 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023949, 39, 30, 17, '2026-05-02 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023950, 42, 6, 17, '2026-05-17 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023951, 3, 41, 17, '2026-05-02 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023952, 38, 33, 17, '2026-05-17 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023953, 50, 7, 17, '2026-05-17 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023954, 34, 30, 17, '2026-05-17 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023955, 48, 41, 17, '2026-05-17 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023956, 35, 14, 17, '2026-05-17 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14023957, 39, 37, 17, '2026-05-17 08:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14024023, 60, 7, 17, '2026-05-03 07:00:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14024024, 44, 38, 17, '2026-05-09 05:30:00', 'football', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_fuente`
--

CREATE TABLE `tipos_fuente` (
  `id` int(11) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `icono` varchar(60) DEFAULT 'fa-play'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_fuente`
--

INSERT INTO `tipos_fuente` (`id`, `nombre`, `icono`) VALUES
(1, 'm3u8', 'fa-play'),
(2, 'hls', 'fa-play'),
(3, 'dash', 'fa-broadcast-tower'),
(4, 'dash-drm', 'fa-lock'),
(5, 'iframe', 'fa-window-maximize'),
(6, 'youtube', 'fa-youtube');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','usuario') NOT NULL DEFAULT 'usuario',
  `avatar_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `avatar_url`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Alex', 'slowdsports@gmail.com', '$2y$10$amYQlIjHAQDkGEGOkuYky.ka8QBTnBYjgCVOqClcMJV4IDHoqiEQG', 'admin', NULL, 1, '2026-04-22 01:01:40', '2026-04-27 02:38:24');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `canales`
--
ALTER TABLE `canales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias_canal`
--
ALTER TABLE `categorias_canal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `config_sitio`
--
ALTER TABLE `config_sitio`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `fuentes`
--
ALTER TABLE `fuentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `canal` (`canal`),
  ADD KEY `pais` (`pais`),
  ADD KEY `tipo` (`tipo`);

--
-- Indices de la tabla `ligas`
--
ALTER TABLE `ligas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `paises`
--
ALTER TABLE `paises`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `paisCodigo` (`paisCodigo`);

--
-- Indices de la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `local` (`local`),
  ADD KEY `visitante` (`visitante`),
  ADD KEY `liga` (`liga`),
  ADD KEY `partidos_ibfk_11` (`canal8`),
  ADD KEY `partidos_ibfk_13` (`canal10`),
  ADD KEY `partidos_ibfk_5` (`canal2`),
  ADD KEY `partidos_ibfk_7` (`canal4`),
  ADD KEY `partidos_ibfk_9` (`canal6`),
  ADD KEY `partidos_ibfk_10` (`canal7`),
  ADD KEY `partidos_ibfk_12` (`canal9`),
  ADD KEY `partidos_ibfk_4` (`canal1`),
  ADD KEY `partidos_ibfk_6` (`canal3`),
  ADD KEY `partidos_ibfk_8` (`canal5`);

--
-- Indices de la tabla `tipos_fuente`
--
ALTER TABLE `tipos_fuente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `canales`
--
ALTER TABLE `canales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=921;

--
-- AUTO_INCREMENT de la tabla `categorias_canal`
--
ALTER TABLE `categorias_canal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de la tabla `fuentes`
--
ALTER TABLE `fuentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `paises`
--
ALTER TABLE `paises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=378;

--
-- AUTO_INCREMENT de la tabla `tipos_fuente`
--
ALTER TABLE `tipos_fuente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `fuentes`
--
ALTER TABLE `fuentes`
  ADD CONSTRAINT `fuentes_ibfk_1` FOREIGN KEY (`canal`) REFERENCES `canales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fuentes_ibfk_2` FOREIGN KEY (`pais`) REFERENCES `paises` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fuentes_ibfk_3` FOREIGN KEY (`tipo`) REFERENCES `tipos_fuente` (`id`);

--
-- Filtros para la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD CONSTRAINT `partidos_ibfk_1` FOREIGN KEY (`local`) REFERENCES `equipos` (`id`),
  ADD CONSTRAINT `partidos_ibfk_10` FOREIGN KEY (`canal7`) REFERENCES `fuentes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partidos_ibfk_11` FOREIGN KEY (`canal8`) REFERENCES `fuentes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partidos_ibfk_12` FOREIGN KEY (`canal9`) REFERENCES `fuentes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partidos_ibfk_13` FOREIGN KEY (`canal10`) REFERENCES `fuentes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partidos_ibfk_2` FOREIGN KEY (`visitante`) REFERENCES `equipos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partidos_ibfk_3` FOREIGN KEY (`liga`) REFERENCES `ligas` (`id`),
  ADD CONSTRAINT `partidos_ibfk_4` FOREIGN KEY (`canal1`) REFERENCES `fuentes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partidos_ibfk_5` FOREIGN KEY (`canal2`) REFERENCES `fuentes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partidos_ibfk_6` FOREIGN KEY (`canal3`) REFERENCES `fuentes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partidos_ibfk_7` FOREIGN KEY (`canal4`) REFERENCES `fuentes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partidos_ibfk_8` FOREIGN KEY (`canal5`) REFERENCES `fuentes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `partidos_ibfk_9` FOREIGN KEY (`canal6`) REFERENCES `fuentes` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
