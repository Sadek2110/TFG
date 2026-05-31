-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: automatizaciones_fastplay_db:3306
-- Tiempo de generación: 28-05-2026 a las 18:29:11
-- Versión del servidor: 9.7.0
-- Versión de PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fastplay`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `achievements`
--

CREATE TABLE `achievements` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 0xF09F8F85
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `achievements`
--

INSERT INTO `achievements` (`id`, `code`, `name`, `description`, `icon`) VALUES
(1, 'first_goal', 'Primer gol', 'Marca tu primer gol oficial.', '🎖️'),
(2, 'hat_trick', 'Hat-trick', '3 goles en un solo partido.', '🏅'),
(3, 'captain', 'Capitán', 'Crea y dirige un equipo.', '🛡️'),
(4, 'veteran', 'Veterano', 'Juega 10 partidos.', '🎯'),
(5, 'mvp', 'MVP', 'Mejor jugador en una jornada.', '🏆');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Volcado de datos para la tabla `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `room_id`, `user_id`, `body`, `created_at`) VALUES
(1, 1, 1, '¡Bienvenidos a FastPlay! Por aquí coordinamos cualquier duda.', '2026-05-18 17:47:04'),
(2, 1, 2, '¿Alguien para un 7v7 este finde en Madrid?', '2026-05-18 17:47:04'),
(3, 2, 3, 'Buscamos rival amistoso este sábado, Barça Amateurs disponibles.', '2026-05-18 17:47:04'),
(4, 1, 11, 'hola', '2026-05-19 10:04:01'),
(5, 1, 9, 'Hola', '2026-05-19 10:44:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_rooms`
--

CREATE TABLE `chat_rooms` (
  `id` bigint UNSIGNED NOT NULL,
  `type` enum('group','general','team','league','match_negotiation','direct') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'group',
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `team_id` bigint UNSIGNED DEFAULT NULL,
  `match_request_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `chat_rooms`
--

INSERT INTO `chat_rooms` (`id`, `type`, `name`, `created_at`, `team_id`, `match_request_id`) VALUES
(1, 'general', 'Lobby general', '2026-05-18 17:47:04', NULL, NULL),
(2, 'match_negotiation', 'Capitanes — partidos amistosos', '2026-05-18 17:47:04', NULL, NULL),
(3, 'team', 'Equipo: Madrid Real C.F.', '2026-05-20 12:03:08', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fields`
--

CREATE TABLE `fields` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surface` enum('césped','sintético','tierra','cemento') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'césped',
  `capacity` int NOT NULL DEFAULT '22',
  `hourly_rate` decimal(8,2) NOT NULL DEFAULT '0.00',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `maps_url` text COLLATE utf8mb4_unicode_ci,
  `image` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci
) ;

--
-- Volcado de datos para la tabla `fields`
--

INSERT INTO `fields` (`id`, `name`, `city`, `address`, `surface`, `capacity`, `hourly_rate`, `latitude`, `longitude`, `maps_url`, `image`, `description`) VALUES
(1, 'La Cantera', 'Madrid', 'Av. de las Glorietas 12', 'césped', 22, 35.00, NULL, NULL, NULL, NULL, NULL),
(2, 'Pista 4', 'Madrid', 'Polideportivo Centro', 'sintético', 14, 22.00, NULL, NULL, NULL, NULL, NULL),
(3, 'Polideportivo Sur', 'Valencia', 'C/ del Mar, 3', 'césped', 22, 30.00, NULL, NULL, NULL, NULL, NULL),
(4, 'Camp Nou Petit', 'Barcelona', 'C/ de Sants, 88', 'césped', 22, 40.00, NULL, NULL, NULL, NULL, NULL),
(5, 'Sevilla Sur', 'Sevilla', 'Av. Heliópolis 21', 'tierra', 14, 18.00, NULL, NULL, NULL, NULL, NULL),
(6, 'Campo Federativo Jose Benoliel', 'Ceuta', 'Avenida de Africa, Ceuta', 'sintético', 22, 0.00, 35.8898000, -5.3262000, 'https://www.google.com/maps/search/?api=1&query=Campo+Federativo+Jose+Benoliel+Ceuta', NULL, 'Campo federativo de fútbol en Ceuta.'),
(7, 'Polideportivo La Libertad', 'Ceuta', 'Avenida de Lisboa, Ceuta', 'sintético', 14, 0.00, 35.8844000, -5.3441000, 'https://www.google.com/maps/search/?api=1&query=Polideportivo+La+Libertad+Ceuta', NULL, 'Instalación polideportiva para entrenamientos y partidos.'),
(8, 'Complejo Deportivo Diaz-Flor', 'Ceuta', 'Avenida de Otero, Ceuta', 'césped', 22, 0.00, 35.8871000, -5.3073000, 'https://www.google.com/maps/search/?api=1&query=Complejo+Deportivo+Diaz+Flor+Ceuta', NULL, 'Complejo deportivo municipal en Ceuta.'),
(9, 'Estadio Alfonso Murube', 'Ceuta', 'Av. de Otero, s/n', 'césped', 22, 0.00, 35.8875000, -5.3065000, 'https://www.google.com/maps/search/?api=1&query=Estadio+Alfonso+Murube+Ceuta', NULL, 'Campo Oficial · Federado'),
(10, 'Campo de Fútbol José Benoliel', 'Ceuta', 'Calle Francisco de Lería y Ortiz de Saracho, s/n', 'sintético', 22, 0.00, 35.8905000, -5.3250000, 'https://www.google.com/maps/search/?api=1&query=Campo+Futbol+Jose+Benoliel+Ceuta', NULL, 'Campo Oficial · Federado'),
(11, 'Campo de Fútbol José Martínez \"Pirri\"', 'Ceuta', 'Avenida de Madrid, s/n', 'sintético', 22, 0.00, 35.8862000, -5.3175000, 'https://www.google.com/maps/search/?api=1&query=Campo+Futbol+Pirri+Ceuta', NULL, 'Campo Oficial · Federado'),
(12, 'Campo de Fútbol Aiman Mohamed (Puente Quemadero)', 'Ceuta', 'Carretera del Tarajal (Zona Puente Quemadero), s/n', 'sintético', 22, 0.00, 35.8752000, -5.3347000, 'https://www.google.com/maps/search/?api=1&query=Puente+Quemadero+Ceuta', NULL, 'Campo Oficial · Federado'),
(13, 'Campo de Fútbol Tuhami Al-lal (Puente Quemadero)', 'Ceuta', 'Carretera del Tarajal (Zona Puente Quemadero), s/n', 'sintético', 22, 0.00, 35.8748000, -5.3352000, 'https://www.google.com/maps/search/?api=1&query=Puente+Quemadero+Ceuta', NULL, 'Campo Oficial · Federado'),
(14, 'Campo de Fútbol Emilio Cózar', 'Ceuta', 'Av. de África, s/n (Sede RFFCE)', 'sintético', 22, 0.00, 35.8888000, -5.3258000, 'https://www.google.com/maps/search/?api=1&query=Campo+Emilio+Cozar+Ceuta', NULL, 'Campo Oficial · Federado'),
(15, 'Pista de la Barriada del Sarchal (FC Futures)', 'Ceuta', 'Calle Recinto Sur / Camino del Sarchal, s/n', 'cemento', 10, 0.00, 35.8828000, -5.3142000, 'https://www.google.com/maps/search/?api=1&query=Barriada+Sarchal+Ceuta', NULL, 'Pista Pública · Barriada'),
(16, 'Pista Deportiva de Juan XXIII (Darío Duzmán)', 'Ceuta', 'Calle Agrupación Juan XXIII, s/n', 'cemento', 10, 0.00, 35.8920000, -5.3228000, 'https://www.google.com/maps/search/?api=1&query=Juan+XXIII+Ceuta', NULL, 'Pista Pública · Barriada'),
(17, 'Pista de la Barriada de Los Rosales', 'Ceuta', 'Calle Capitán Claudio Vázquez / Barriada Los Rosales, s/n', 'cemento', 10, 0.00, 35.8846000, -5.3389000, 'https://www.google.com/maps/search/?api=1&query=Barriada+Los+Rosales+Ceuta', NULL, 'Pista Pública · Barriada'),
(18, 'Pista de la Barriada de Zurrón', 'Ceuta', 'Calle Doctor Abdelkrim / Agrupación Zurrón, s/n', 'cemento', 10, 0.00, 35.8838000, -5.3362000, 'https://www.google.com/maps/search/?api=1&query=Barriada+Zurron+Ceuta', NULL, 'Pista Pública · Barriada'),
(19, 'Pista de la Junta de Obras del Puerto (JOP)', 'Ceuta', 'Avenida Cañonero Dato (Zona Portuaria), s/n', 'cemento', 10, 0.00, 35.8943000, -5.3097000, 'https://www.google.com/maps/search/?api=1&query=Puerto+Ceuta+JOP', NULL, 'Pista Pública · Barriada'),
(20, 'Pista de la Barriada Juan Carlos I (La Pantera)', 'Ceuta', 'Calle Agrupación Juan Carlos I, s/n', 'cemento', 10, 0.00, 35.8878000, -5.3290000, 'https://www.google.com/maps/search/?api=1&query=Juan+Carlos+I+Ceuta', NULL, 'Pista Pública · Barriada'),
(21, 'Pista de Manzanera', 'Ceuta', 'Calle Brull / Barriada de Manzanera, s/n', 'cemento', 10, 0.00, 35.8945000, -5.3150000, 'https://www.google.com/maps/search/?api=1&query=Manzanera+Ceuta', NULL, 'Pista Pública · Barriada'),
(22, 'Pista del Polígono Virgen de África', 'Ceuta', 'Calle Agrupación Polígono de África, s/n', 'cemento', 10, 0.00, 35.8820000, -5.3330000, 'https://www.google.com/maps/search/?api=1&query=Poligono+Virgen+Africa+Ceuta', NULL, 'Pista Pública · Barriada'),
(23, 'Pista de Hadú', 'Ceuta', 'Calle Capitán Claudio Vázquez (Plaza de San José), s/n', 'cemento', 10, 0.00, 35.8868000, -5.3138000, 'https://www.google.com/maps/search/?api=1&query=Hadu+Ceuta', NULL, 'Pista Pública · Barriada'),
(24, 'Campo Federativo Jose Benoliel', 'Ceuta', 'Avenida de Africa, Ceuta', 'sintético', 22, 0.00, 35.8898000, -5.3262000, 'https://www.google.com/maps/search/?api=1&query=Campo+Federativo+Jose+Benoliel+Ceuta', NULL, 'Campo federativo de fútbol en Ceuta.'),
(25, 'Polideportivo La Libertad', 'Ceuta', 'Avenida de Lisboa, Ceuta', 'sintético', 14, 0.00, 35.8844000, -5.3441000, 'https://www.google.com/maps/search/?api=1&query=Polideportivo+La+Libertad+Ceuta', NULL, 'Instalación polideportiva para entrenamientos y partidos.'),
(26, 'Complejo Deportivo Diaz-Flor', 'Ceuta', 'Avenida de Otero, Ceuta', 'césped', 22, 0.00, 35.8871000, -5.3073000, 'https://www.google.com/maps/search/?api=1&query=Complejo+Deportivo+Diaz+Flor+Ceuta', NULL, 'Complejo deportivo municipal en Ceuta.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `leagues`
--

CREATE TABLE `leagues` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pro` tinyint(1) NOT NULL DEFAULT '0',
  `prize` decimal(10,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `max_teams` int NOT NULL DEFAULT '12',
  `status` enum('open','in_progress') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Volcado de datos para la tabla `leagues`
--

INSERT INTO `leagues` (`id`, `name`, `city`, `pro`, `prize`, `start_date`, `end_date`, `max_teams`, `status`, `created_at`) VALUES
(1, 'Liga Pro Ceuta 25/26', 'Ceuta', 1, 1500.00, '2026-03-01', '2026-06-30', 12, 'open', '2026-05-18 17:47:04'),
(2, 'Copa Ciudad de Ceuta', 'Ceuta', 1, 1000.00, '2026-03-01', '2026-06-30', 12, 'open', '2026-05-18 17:47:04'),
(3, 'Liga Amistosa del Tarajal', 'Ceuta', 0, NULL, '2026-03-01', '2026-06-30', 12, 'open', '2026-05-18 17:47:04'),
(4, 'Liga Amistosa Hadú', 'Ceuta', 0, NULL, '2026-03-01', '2026-06-30', 12, 'open', '2026-05-18 17:47:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `league_teams`
--

CREATE TABLE `league_teams` (
  `league_id` bigint UNSIGNED NOT NULL,
  `team_id` bigint UNSIGNED NOT NULL,
  `points` int NOT NULL DEFAULT '0',
  `played` int NOT NULL DEFAULT '0',
  `won` int NOT NULL DEFAULT '0',
  `drawn` int NOT NULL DEFAULT '0',
  `lost` int NOT NULL DEFAULT '0',
  `gf` int NOT NULL DEFAULT '0',
  `ga` int NOT NULL DEFAULT '0',
  `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Volcado de datos para la tabla `league_teams`
--

INSERT INTO `league_teams` (`league_id`, `team_id`, `points`, `played`, `won`, `drawn`, `lost`, `gf`, `ga`, `registered_at`) VALUES
(1, 1, 9, 4, 3, 0, 1, 11, 5, '2026-05-18 17:47:04'),
(1, 2, 3, 3, 1, 0, 2, 4, 7, '2026-05-18 17:47:04'),
(1, 3, 7, 4, 2, 1, 1, 8, 6, '2026-05-18 17:47:04'),
(1, 4, 1, 3, 0, 1, 2, 3, 8, '2026-05-18 17:47:04'),
(2, 2, 6, 3, 2, 0, 1, 6, 3, '2026-05-18 17:47:04'),
(3, 1, 2, 3, 0, 2, 1, 2, 5, '2026-05-18 17:47:04'),
(3, 5, 4, 3, 1, 1, 1, 4, 4, '2026-05-18 17:47:04'),
(4, 4, 8, 4, 2, 2, 0, 7, 3, '2026-05-18 17:47:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint UNSIGNED NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `ip`, `email`, `success`, `attempted_at`) VALUES
(88, '10.11.0.4', 'deksa@dksaa.com', 1, '2026-05-28 17:48:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matches`
--

CREATE TABLE `matches` (
  `id` bigint UNSIGNED NOT NULL,
  `home_team_id` bigint UNSIGNED NOT NULL,
  `away_team_id` bigint UNSIGNED NOT NULL,
  `league_id` bigint UNSIGNED DEFAULT NULL,
  `field_id` bigint UNSIGNED DEFAULT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` enum('pending','confirmed','cancelled','finished') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `home_score` smallint DEFAULT NULL,
  `away_score` smallint DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `local_captain_id` bigint UNSIGNED DEFAULT NULL,
  `visitor_captain_id` bigint UNSIGNED DEFAULT NULL,
  `match_time` time DEFAULT NULL,
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `matches`
--

INSERT INTO `matches` (`id`, `home_team_id`, `away_team_id`, `league_id`, `field_id`, `scheduled_at`, `status`, `home_score`, `away_score`, `created_by`, `created_at`, `local_captain_id`, `visitor_captain_id`, `match_time`, `location`) VALUES
(1, 1, 2, 1, 1, '2026-06-12 19:30:00', 'confirmed', NULL, NULL, 1, '2026-05-18 17:47:04', NULL, NULL, NULL, NULL),
(2, 3, 4, 1, 2, '2026-06-15 21:00:00', 'finished', 3, 2, 1, '2026-05-18 17:47:04', NULL, NULL, NULL, NULL),
(3, 5, 1, 3, 3, '2026-06-22 20:00:00', 'pending', NULL, NULL, 1, '2026-05-18 17:47:04', NULL, NULL, NULL, NULL),
(4, 2, 6, NULL, 4, '2026-07-02 18:00:00', 'confirmed', NULL, NULL, 1, '2026-05-18 17:47:04', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `match_requests`
--

CREATE TABLE `match_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `requesting_team_id` bigint UNSIGNED NOT NULL,
  `requested_team_id` bigint UNSIGNED NOT NULL,
  `requesting_captain_id` bigint UNSIGNED NOT NULL,
  `requested_captain_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','accepted','accepted_final','rejected','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `proposed_date` date DEFAULT NULL,
  `proposed_time` time DEFAULT NULL,
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requesting_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `requested_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `match_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `match_requests`
--

INSERT INTO `match_requests` (`id`, `requesting_team_id`, `requested_team_id`, `requesting_captain_id`, `requested_captain_id`, `status`, `proposed_date`, `proposed_time`, `location`, `requesting_confirmed`, `requested_confirmed`, `match_id`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 1, 2, 'pending', NULL, NULL, NULL, 0, 0, NULL, '2026-05-20 10:13:24', '2026-05-20 10:13:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `action_url` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `is_read`, `action_url`, `created_at`) VALUES
(1, 2, 'team_join_request', 'Deksa quiere unirse a Madrid Real C.F..', 0, 'notification', '2026-05-20 09:48:21'),
(2, 1, 'team_join_request', 'Deksa quiere unirse a Atlético Centro.', 0, 'notification', '2026-05-20 09:57:41'),
(3, 2, 'match_request', 'Atlético Centro quiere jugar contra tu equipo.', 0, 'match-request/show/1', '2026-05-20 10:13:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `provider` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'stripe',
  `provider_customer_id` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_subscription_id` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','cancelled','pending','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `teams`
--

CREATE TABLE `teams` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `badge` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 0xF09F9BA1EFB88F,
  `captain_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shield` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `teams`
--

INSERT INTO `teams` (`id`, `name`, `city`, `badge`, `captain_id`, `created_at`, `shield`) VALUES
(1, 'Madrid Real C.F.', 'Madrid', '🛡️', 2, '2026-05-18 17:47:04', NULL),
(2, 'Barça Amateurs', 'Barcelona', '🛡️', 3, '2026-05-18 17:47:04', NULL),
(3, 'Atlético Centro', 'Madrid', '🛡️', 1, '2026-05-18 17:47:04', NULL),
(4, 'Sevilla Street', 'Sevilla', '🛡️', 5, '2026-05-18 17:47:04', NULL),
(5, 'Valencia Calle', 'Valencia', '🛡️', 4, '2026-05-18 17:47:04', NULL),
(6, 'Bilbao Norte', 'Bilbao', '🛡️', 6, '2026-05-18 17:47:04', NULL),
(7, 'Zaragoza FC', 'Zaragoza', '🛡️', 7, '2026-05-18 17:47:04', NULL),
(8, 'Málaga Costa', 'Málaga', '🛡️', 8, '2026-05-18 17:47:04', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `team_join_requests`
--

CREATE TABLE `team_join_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `team_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `captain_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','accepted','rejected','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `team_join_requests`
--

INSERT INTO `team_join_requests` (`id`, `team_id`, `user_id`, `captain_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 11, 2, 'pending', '2026-05-20 09:48:21', '2026-05-20 09:48:21'),
(2, 3, 11, 1, 'pending', '2026-05-20 09:57:41', '2026-05-20 09:57:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `team_members`
--

CREATE TABLE `team_members` (
  `team_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` enum('captain','player') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'player'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `team_members`
--

INSERT INTO `team_members` (`team_id`, `user_id`, `joined_at`, `role`) VALUES
(1, 2, '2026-05-18 17:47:04', 'player'),
(1, 9, '2026-05-19 07:38:30', 'player'),
(2, 3, '2026-05-18 17:47:04', 'player'),
(3, 1, '2026-05-18 17:47:04', 'player'),
(3, 2, '2026-05-18 17:47:04', 'player'),
(3, 11, '2026-05-20 10:02:46', 'player'),
(3, 12, '2026-05-19 08:13:54', 'player'),
(4, 5, '2026-05-18 17:47:04', 'player'),
(5, 4, '2026-05-18 17:47:04', 'player'),
(6, 6, '2026-05-18 17:47:04', 'player'),
(7, 7, '2026-05-18 17:47:04', 'player'),
(8, 8, '2026-05-18 17:47:04', 'player');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` smallint DEFAULT NULL,
  `city` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('player','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'player',
  `avatar` text COLLATE utf8mb4_unicode_ci,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dorsal` smallint DEFAULT NULL,
  `height_cm` smallint DEFAULT NULL,
  `goals` int NOT NULL DEFAULT '0',
  `assists` int NOT NULL DEFAULT '0',
  `is_premium` tinyint(1) NOT NULL DEFAULT '0',
  `current_team_id` bigint UNSIGNED DEFAULT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `age`, `city`, `position`, `password_hash`, `role`, `avatar`, `bio`, `created_at`, `updated_at`, `dorsal`, `height_cm`, `goals`, `assists`, `is_premium`, `current_team_id`, `google_id`) VALUES
(1, 'Sadek Admin', 'admin@fastplay.es', '+34600000000', 28, 'Madrid', 'Mediocampo', '$2y$10$ah8oxFHzLqh5/RfMQXFf0.XjtUAPQ85hDWDXYXNMgH5pPq3l.50xi', 'admin', NULL, NULL, '2026-05-18 17:47:04', '2026-05-18 17:47:04', NULL, NULL, 0, 0, 0, NULL, NULL),
(2, 'Jugador Demo', 'demo@fastplay.es', '+34611111111', 24, 'Madrid', 'Delantero', '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player', NULL, NULL, '2026-05-18 17:47:04', '2026-05-18 17:47:04', NULL, NULL, 0, 0, 0, NULL, NULL),
(3, 'Lucía Pérez', 'lucia@fastplay.es', '+34600123456', 22, 'Barcelona', 'Portera', '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player', NULL, NULL, '2026-05-18 17:47:04', '2026-05-18 17:47:04', NULL, NULL, 0, 0, 0, NULL, NULL),
(4, 'Marc Costa', 'marc@fastplay.es', '+34600234567', 27, 'Valencia', 'Defensa', '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player', NULL, NULL, '2026-05-18 17:47:04', '2026-05-18 17:47:04', NULL, NULL, 0, 0, 0, NULL, NULL),
(5, 'Ana Ruiz', 'ana@fastplay.es', '+34600345678', 26, 'Sevilla', 'Mediocampo', '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player', NULL, NULL, '2026-05-18 17:47:04', '2026-05-18 17:47:04', NULL, NULL, 0, 0, 0, NULL, NULL),
(6, 'Iván Soto', 'ivan@fastplay.es', '+34600456789', 25, 'Bilbao', 'Delantero', '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player', NULL, NULL, '2026-05-18 17:47:04', '2026-05-18 17:47:04', NULL, NULL, 0, 0, 0, NULL, NULL),
(7, 'Paula Gil', 'paula@fastplay.es', '+34600567890', 23, 'Zaragoza', 'Defensa', '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player', NULL, NULL, '2026-05-18 17:47:04', '2026-05-18 17:47:04', NULL, NULL, 0, 0, 0, NULL, NULL),
(8, 'Hugo Marín', 'hugo@fastplay.es', '+34600678901', 29, 'Málaga', 'Mediocampo', '$2y$10$D5keJqVGvaO8YuGTIubIlO6SVNb/Ad3xIAVHGTMumkj0Jl.7Y8HOy', 'player', NULL, NULL, '2026-05-18 17:47:04', '2026-05-18 17:47:04', NULL, NULL, 0, 0, 0, NULL, NULL),
(9, 'Rania', 'ranialaarbim@gmail.com', '675494955', 20, 'Ceuta', 'Portera', '$2y$10$nhaIqHTwLdcZ2GJHbhAcq.6gqUpqzuk7xMgYj.SLV7wiOHjlDrv7u', 'player', NULL, NULL, '2026-05-18 18:37:46', '2026-05-21 16:11:39', 4, 165, 0, 0, 0, NULL, NULL),
(10, 'Malik', 'elptrn17@gmail.com', '+34689708057', 15, NULL, NULL, '$2y$10$Ouob8YFl5YnxCQvvog3h7.j50dUkAP9jpt3OmxiWz6G5if.x1Ho3C', 'player', NULL, NULL, '2026-05-18 18:52:28', '2026-05-18 18:52:28', NULL, NULL, 0, 0, 0, NULL, NULL),
(11, 'Deksa', 'deksa@dksaa.com', NULL, 20, 'Ceuta', 'Portero', '$2y$10$oweqfu/rQoLTXNqlOI8dqurEnTR2QgxyV5PoDtnfODVokyLF4U6SO', 'player', NULL, NULL, '2026-05-18 22:41:50', '2026-05-20 10:02:46', 12, 188, 12, 0, 0, 3, NULL),
(12, 'Carlos', 'carlos@empresa.com', NULL, 19, 'Ceuta', 'Defensa', '$2y$10$cIds4Sef9yDtUkoeKRhphOGK1fIY9IGagKEsXjtRFCP/8UK8qEpCm', 'player', NULL, NULL, '2026-05-19 08:11:15', '2026-05-19 08:12:29', 17, 188, 999, 300, 0, NULL, NULL),
(13, 'khalid', 'kbenjouda16@gmail.com', NULL, 22, NULL, NULL, '$2y$10$pTOfPu3g9FKDL/r5MuEWNuvknWnNLllZdmKRIjraTon6tHgaUS62q', 'player', NULL, NULL, '2026-05-19 17:10:16', '2026-05-19 17:10:16', NULL, NULL, 0, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_achievements`
--

CREATE TABLE `user_achievements` (
  `user_id` bigint UNSIGNED NOT NULL,
  `achievement_id` bigint UNSIGNED NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `user_achievements`
--

INSERT INTO `user_achievements` (`user_id`, `achievement_id`, `earned_at`) VALUES
(2, 1, '2026-05-18 17:47:04'),
(2, 3, '2026-05-18 17:47:04');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_league_standings`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_league_standings` (
`drawn` int
,`ga` int
,`gf` int
,`goal_diff` bigint
,`league_id` bigint unsigned
,`league_name` varchar(150)
,`lost` int
,`played` int
,`points` int
,`team_id` bigint unsigned
,`team_name` varchar(120)
,`won` int
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_upcoming_matches`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_upcoming_matches` (
`away_team` varchar(120)
,`field` varchar(120)
,`home_team` varchar(120)
,`id` bigint unsigned
,`league` varchar(150)
,`scheduled_at` datetime
,`status` enum('pending','confirmed','cancelled','finished')
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_league_standings`
--
DROP TABLE IF EXISTS `v_league_standings`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `v_league_standings`  AS SELECT `l`.`id` AS `league_id`, `l`.`name` AS `league_name`, `t`.`id` AS `team_id`, `t`.`name` AS `team_name`, `lt`.`points` AS `points`, `lt`.`played` AS `played`, `lt`.`won` AS `won`, `lt`.`drawn` AS `drawn`, `lt`.`lost` AS `lost`, `lt`.`gf` AS `gf`, `lt`.`ga` AS `ga`, (`lt`.`gf` - `lt`.`ga`) AS `goal_diff` FROM ((`league_teams` `lt` join `leagues` `l` on((`l`.`id` = `lt`.`league_id`))) join `teams` `t` on((`t`.`id` = `lt`.`team_id`))) ORDER BY `lt`.`league_id` ASC, `lt`.`points` DESC, (`lt`.`gf` - `lt`.`ga`) DESC, `lt`.`gf` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_upcoming_matches`
--
DROP TABLE IF EXISTS `v_upcoming_matches`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `v_upcoming_matches`  AS SELECT `m`.`id` AS `id`, `m`.`scheduled_at` AS `scheduled_at`, `m`.`status` AS `status`, `h`.`name` AS `home_team`, `a`.`name` AS `away_team`, `f`.`name` AS `field`, `l`.`name` AS `league` FROM ((((`matches` `m` join `teams` `h` on((`h`.`id` = `m`.`home_team_id`))) join `teams` `a` on((`a`.`id` = `m`.`away_team_id`))) left join `fields` `f` on((`f`.`id` = `m`.`field_id`))) left join `leagues` `l` on((`l`.`id` = `m`.`league_id`))) WHERE (`m`.`status` in ('pending','confirmed')) ORDER BY `m`.`scheduled_at` ASC ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_achievements_code` (`code`);

--
-- Indices de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_room_time` (`room_id`,`created_at`),
  ADD KEY `idx_messages_user` (`user_id`);

--
-- Indices de la tabla `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fields_city` (`city`);

--
-- Indices de la tabla `leagues`
--
ALTER TABLE `leagues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leagues_city` (`city`),
  ADD KEY `idx_leagues_status` (`status`),
  ADD KEY `idx_leagues_pro` (`pro`);

--
-- Indices de la tabla `league_teams`
--
ALTER TABLE `league_teams`
  ADD PRIMARY KEY (`league_id`,`team_id`),
  ADD KEY `idx_lt_team` (`team_id`);

--
-- Indices de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_email` (`email`,`attempted_at`),
  ADD KEY `idx_login_attempts_ip` (`ip`,`attempted_at`);

--
-- Indices de la tabla `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_match_creator` (`created_by`),
  ADD KEY `idx_matches_status` (`status`),
  ADD KEY `idx_matches_scheduled` (`scheduled_at`),
  ADD KEY `idx_matches_league` (`league_id`),
  ADD KEY `idx_matches_field` (`field_id`),
  ADD KEY `idx_matches_home` (`home_team_id`),
  ADD KEY `idx_matches_away` (`away_team_id`);

--
-- Indices de la tabla `match_requests`
--
ALTER TABLE `match_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mr_teams_status` (`requesting_team_id`,`requested_team_id`,`status`),
  ADD KEY `fk_mr_res_team` (`requested_team_id`),
  ADD KEY `fk_mr_req_cap` (`requesting_captain_id`),
  ADD KEY `fk_mr_res_cap` (`requested_captain_id`),
  ADD KEY `fk_mr_match` (`match_id`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user` (`user_id`);

--
-- Indices de la tabla `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subscriptions_user_status` (`user_id`,`status`);

--
-- Indices de la tabla `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_teams_name_city` (`name`,`city`),
  ADD KEY `idx_teams_captain` (`captain_id`),
  ADD KEY `idx_teams_city` (`city`);

--
-- Indices de la tabla `team_join_requests`
--
ALTER TABLE `team_join_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tjr_team_user_status` (`team_id`,`user_id`,`status`),
  ADD KEY `fk_tjr_user` (`user_id`),
  ADD KEY `fk_tjr_captain` (`captain_id`);

--
-- Indices de la tabla `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`team_id`,`user_id`),
  ADD KEY `idx_team_members_user` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `idx_users_city` (`city`),
  ADD KEY `idx_users_role` (`role`);

--
-- Indices de la tabla `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`user_id`,`achievement_id`),
  ADD KEY `idx_user_achievements_ach` (`achievement_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_rooms`
--
ALTER TABLE `chat_rooms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `fields`
--
ALTER TABLE `fields`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `leagues`
--
ALTER TABLE `leagues`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT de la tabla `matches`
--
ALTER TABLE `matches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `match_requests`
--
ALTER TABLE `match_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `team_join_requests`
--
ALTER TABLE `team_join_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_msg_room` FOREIGN KEY (`room_id`) REFERENCES `chat_rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_msg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `league_teams`
--
ALTER TABLE `league_teams`
  ADD CONSTRAINT `fk_lt_league` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lt_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `fk_match_away` FOREIGN KEY (`away_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_match_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_match_field` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_match_home` FOREIGN KEY (`home_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_match_league` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `match_requests`
--
ALTER TABLE `match_requests`
  ADD CONSTRAINT `fk_mr_match` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mr_req_cap` FOREIGN KEY (`requesting_captain_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mr_req_team` FOREIGN KEY (`requesting_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mr_res_cap` FOREIGN KEY (`requested_captain_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mr_res_team` FOREIGN KEY (`requested_team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `fk_teams_captain` FOREIGN KEY (`captain_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `team_join_requests`
--
ALTER TABLE `team_join_requests`
  ADD CONSTRAINT `fk_tjr_captain` FOREIGN KEY (`captain_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tjr_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tjr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `fk_tm_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `fk_ua_ach` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ua_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
