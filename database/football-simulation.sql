-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th1 31, 2026 lúc 12:28 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `football-simulation`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cup_eliminate_stage_matches`
--

CREATE TABLE `cup_eliminate_stage_matches` (
  `id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `round` varchar(45) DEFAULT NULL,
  `branch` varchar(45) DEFAULT NULL,
  `team1_id` int(11) DEFAULT NULL,
  `team2_id` int(11) DEFAULT NULL,
  `team1_score` tinyint(1) DEFAULT NULL,
  `team2_score` tinyint(1) DEFAULT NULL,
  `team1_possession` int(11) DEFAULT 50,
  `team2_possession` int(11) DEFAULT 50,
  `team1_foul` int(11) DEFAULT 0,
  `team2_foul` int(11) DEFAULT 0,
  `winner_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cup_group_stage_matches`
--

CREATE TABLE `cup_group_stage_matches` (
  `id` bigint(20) NOT NULL,
  `season_id` int(11) NOT NULL,
  `group` varchar(45) DEFAULT NULL,
  `round` int(11) DEFAULT NULL,
  `team1_id` int(11) DEFAULT 0,
  `team2_id` int(11) DEFAULT 0,
  `team1_score` int(11) DEFAULT 0,
  `team2_score` int(11) DEFAULT 0,
  `team1_possession` int(11) DEFAULT 50,
  `team2_possession` int(11) DEFAULT 50,
  `team1_foul` int(11) DEFAULT 0,
  `team2_foul` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cup_group_teams`
--

CREATE TABLE `cup_group_teams` (
  `id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `group` varchar(255) NOT NULL,
  `team_ids` varchar(1015) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cup_positions`
--

CREATE TABLE `cup_positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cup_standing_id` bigint(20) UNSIGNED NOT NULL,
  `season_id` bigint(20) UNSIGNED NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `result` varchar(45) NOT NULL DEFAULT 'group_stage',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cup_seasons`
--

CREATE TABLE `cup_seasons` (
  `id` int(11) NOT NULL,
  `season` int(11) NOT NULL,
  `teams_count` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `meta` varchar(45) DEFAULT 'attack'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cup_standings`
--

CREATE TABLE `cup_standings` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL DEFAULT 0,
  `group` varchar(45) DEFAULT NULL,
  `match_played` int(11) DEFAULT 0,
  `goal_scored` int(11) NOT NULL DEFAULT 0,
  `goal_conceded` int(11) NOT NULL DEFAULT 0,
  `goal_difference` int(11) DEFAULT 0,
  `average_possession` double DEFAULT 50,
  `foul` int(11) DEFAULT 0,
  `points` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `win` int(11) DEFAULT 0,
  `draw` int(11) DEFAULT 0,
  `lose` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `shortname` varchar(45) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `regions`
--

INSERT INTO `regions` (`id`, `name`, `shortname`, `description`) VALUES
(1, 'Nhật Bản', 'JP', NULL),
(2, 'Ngoại Quốc', 'EN', NULL),
(3, 'Indonesia', 'ID', NULL),
(4, 'Dev_is', 'DV', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `color_1` varchar(10) DEFAULT '000000',
  `color_2` varchar(10) DEFAULT '000000',
  `color_3` varchar(10) DEFAULT NULL,
  `attack` int(11) DEFAULT 0,
  `defense` int(11) DEFAULT 0,
  `control` int(11) DEFAULT 0,
  `creative` int(11) DEFAULT 0,
  `pace` int(11) DEFAULT 0,
  `mental` int(11) DEFAULT 0,
  `discipline` int(11) DEFAULT 0,
  `stamina` int(11) DEFAULT 0,
  `form` int(11) DEFAULT 50,
  `region` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `shirt_type` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `teams`
--

INSERT INTO `teams` (`id`, `name`, `color_1`, `color_2`, `color_3`, `attack`, `defense`, `control`, `creative`, `pace`, `mental`, `discipline`, `stamina`, `form`, `region`, `created_at`, `shirt_type`) VALUES
(1, 'Suisei', '#00c8f0', '#242b4c', '#ffffff', 95, 89, 88, 89, 75, 85, 86, 93, 100, 1, '2024-12-17 13:05:50', 'checkered'),
(2, 'Miko', '#ff99e9', '#ffffff', '#ff6600', 83, 95, 76, 82, 75, 86, 84, 93, 15, 1, '2024-12-17 13:05:50', NULL),
(3, 'Marine', '#b30000', '#000000', '#ffd500', 77, 83, 96, 88, 73, 82, 90, 82, 50, 1, '2024-12-17 13:05:50', NULL),
(4, 'Iroha', '#1aff94', '#ffffff', '#ffdd00', 93, 68, 85, 92, 83, 77, 78, 94, 90, 1, '2024-12-17 13:05:50', NULL),
(5, 'Amelia', '#f1f500', '#ffffff', '#4dfffc', 85, 94, 91, 89, 89, 72, 69, 83, 45, 2, '2024-12-17 13:05:50', NULL),
(6, 'Kronii', '#0011ff', '#ffffff', '#fbff00', 84, 84, 84, 84, 84, 84, 84, 84, 55, 2, '2024-12-17 13:05:50', NULL),
(7, 'Bijou', '#ffffff', '#5b1a4f', '#f785ff', 68, 96, 85, 82, 70, 86, 91, 94, 25, 2, '2024-12-17 13:05:50', NULL),
(8, 'Kobo', '#00ffee', '#ffffff', '#ff5757', 93, 72, 83, 81, 97, 82, 80, 83, 45, 3, '2024-12-17 13:05:50', NULL),
(9, 'Subaru', '#fff700', '#000000', '#ffffff', 73, 80, 81, 82, 83, 85, 72, 93, 70, 1, '2024-12-17 13:05:50', NULL),
(10, 'Pekora', '#00fffb', '#ffffff', '#ffa033', 95, 67, 86, 97, 93, 80, 71, 83, 70, 1, '2024-12-17 13:05:50', 'halves'),
(11, 'Fauna', '#00ff11', '#002c94', '#ffffff', 73, 93, 93, 62, 60, 78, 82, 82, 5, 2, '2024-12-17 13:05:50', NULL),
(12, 'Ollie', '#c70039', '#000000', '#b4a7c8', 94, 60, 85, 90, 93, 72, 70, 83, 40, 3, '2024-12-17 13:05:50', NULL),
(13, 'Ao', '#004cff', '#000000', '#ffffff', 69, 83, 81, 83, 75, 79, 72, 81, 35, 4, '2024-12-17 13:05:50', NULL),
(14, 'Fubuki', '#ffffff', '#000000', '#00fffb', 78, 78, 80, 85, 81, 83, 75, 87, 75, 1, '2024-12-17 13:05:50', NULL),
(15, 'Azki', '#ff24af', '#491313', '#ffffff', 70, 85, 89, 78, 73, 78, 90, 85, 45, 1, '2024-12-17 13:05:50', NULL),
(16, 'Towa', '#ae00ff', '#000000', '#ffffff', 82, 83, 95, 76, 85, 68, 71, 83, 90, 1, '2024-12-17 13:05:50', NULL),
(17, 'Cecilia', '#00ff1e', '#ffffff', '#ffdd00', 83, 83, 83, 72, 84, 65, 84, 93, 50, 2, '2024-12-17 13:05:50', NULL),
(18, 'Gigi', '#ffd500', '#000000', '#ff0000', 96, 70, 82, 95, 95, 82, 63, 91, 25, 2, '2024-12-17 13:05:50', NULL),
(19, 'Calliope', '#fd9eff', '#000000', '#ffffff', 83, 91, 93, 72, 67, 70, 81, 90, 75, 2, '2024-12-17 13:05:50', NULL),
(20, 'Kaela', '#ffea00', '#000000', '#ff0000', 62, 89, 91, 83, 68, 72, 90, 95, 15, 3, '2024-12-17 13:05:50', NULL),
(21, 'Kanade', '#fdff70', '#ffffff', '#8e0108', 86, 78, 89, 85, 89, 85, 74, 87, 80, 4, '2024-12-17 13:05:50', NULL),
(22, 'Kanata', '#ffffff', '#003670', '#80f5e7', 68, 92, 77, 76, 79, 80, 67, 85, 70, 1, '2024-12-17 13:05:50', NULL),
(23, 'Raora', '#ff6bd3', '#000000', '#b8fffa', 80, 90, 89, 82, 68, 76, 90, 73, 55, 2, '2024-12-17 13:05:50', NULL),
(24, 'Nerissa', '#0011ff', '#030303', '#ffffff', 81, 86, 83, 86, 81, 65, 84, 83, 55, 2, '2024-12-17 13:05:50', NULL),
(25, 'Baelz', '#ff0000', '#ffffff', '#ffe11f', 87, 63, 83, 92, 97, 78, 66, 82, 100, 2, '2024-12-17 13:05:50', NULL),
(26, 'Zeta', '#d4d4d4', '#ffffff', '#000000', 81, 81, 87, 73, 79, 75, 65, 81, 25, 3, '2024-12-17 13:05:50', NULL),
(27, 'Hajime', '#e1bdff', '#ffffff', '#8000ff', 84, 79, 90, 81, 76, 72, 77, 88, 65, 4, '2024-12-17 13:05:50', NULL),
(28, 'Chihaya', '#00803e', '#000000', '#ffffff', 82, 83, 74, 72, 69, 72, 68, 79, 25, 4, '2024-12-17 13:05:50', NULL),
(29, 'Korone', '#ffdd00', '#ffffff', '#ff0000', 87, 60, 79, 81, 88, 76, 60, 92, 40, 1, '2024-12-17 13:05:50', NULL),
(30, 'Elizabeth', '#ff0000', '#000000', '#6cc0f4', 70, 91, 79, 80, 64, 71, 83, 86, 70, 2, '2024-12-17 13:05:50', NULL),
(31, 'Moona', '#bb00ff', '#000000', '#fffca8', 78, 78, 86, 77, 75, 72, 71, 86, 35, 3, '2024-12-17 13:05:50', NULL),
(32, 'Raden', '#2c875c', '#000000', '#f8e944', 75, 85, 81, 81, 67, 70, 79, 83, 10, 4, '2024-12-17 13:05:50', NULL),
(33, 'Fuwawa', '#05e2ff', '#ffffff', '#ff7ae2', 71, 86, 81, 82, 76, 82, 61, 84, 5, 2, '2024-12-17 13:05:50', NULL),
(34, 'Mococo', '#ff4df0', '#ffffff', '#8af1ff', 87, 64, 80, 82, 80, 83, 61, 87, 50, 2, '2024-12-17 13:05:50', NULL),
(35, 'Okayu', '#e27aff', '#000000', '#ffffff', 80, 88, 85, 90, 69, 69, 80, 86, 85, 1, '2024-12-17 13:05:50', NULL),
(36, 'Vivi', '#ff66a1', '#000000', '#42d9ff', 87, 85, 87, 86, 87, 78, 79, 82, 30, 4, '2024-12-17 13:05:50', NULL),
(37, 'Riona', '#ff3856', '#000000', '#d1d1d1', 91, 72, 82, 88, 86, 68, 75, 85, 20, 4, '2026-01-17 11:29:57', NULL),
(38, 'Gura', '#00bfff', '#ffffff', '#ff3838', 84, 72, 81, 86, 81, 73, 70, 77, 95, 2, '2026-01-17 11:30:05', NULL),
(39, 'Aqua', '#ff85fb', '#ffffff', '#004cff', 82, 74, 81, 84, 74, 73, 72, 82, 55, 1, '2026-01-17 11:31:13', NULL),
(40, 'Risu', '#ff99ca', '#ffffff', '#8f0000', 81, 78, 82, 79, 82, 73, 65, 84, 55, 3, '2026-01-17 11:31:13', NULL),
(41, 'Chloe', '#ff0000', '#ffffff', '#000000', 80, 66, 79, 79, 76, 71, 66, 81, 5, 1, '2026-01-17 11:31:24', NULL),
(42, 'Koyori', '#ff5cfa', '#ffffff', '#66ffe0', 76, 83, 77, 78, 70, 74, 80, 85, 40, 1, '2026-01-17 11:31:24', NULL),
(43, 'Kiara', '#ff9500', '#ffffff', '#35fd82', 74, 89, 84, 76, 67, 79, 63, 91, 35, 2, '2026-01-17 11:31:24', NULL),
(44, 'Watame', '#ffea00', '#ffffff', '#7a0000', 60, 91, 73, 67, 62, 77, 84, 85, 10, 1, '2026-01-17 11:31:24', NULL),
(45, 'Shiori', '#000000', '#ffffff', '#edbdff', 88, 63, 88, 83, 82, 68, 67, 85, 65, 2, '2026-01-17 11:31:24', NULL),
(46, 'Ayame', '#ff0000', '#ffffff', '#000000', 84, 74, 78, 80, 84, 70, 83, 69, 75, 1, '2026-01-17 11:31:24', NULL),
(47, 'Irys', '#ff006f', '#ffffff', '#6a0080', 87, 73, 82, 91, 75, 65, 69, 81, 5, 2, '2026-01-17 11:31:24', NULL),
(48, 'Niko', '#ff7300', '#000000', '#ffffff', 85, 75, 82, 79, 66, 76, 72, 86, 80, 4, '2026-01-17 11:31:24', NULL),
(49, 'Nene', '#ff5900', '#ffffff', '#ffdd00', 65, 88, 85, 87, 69, 70, 68, 92, 45, 1, '2026-01-17 11:31:24', NULL),
(50, 'Anya', '#ffc800', '#02007a', '#a20101', 62, 88, 81, 68, 62, 66, 86, 86, 20, 3, '2026-01-17 11:31:24', NULL),
(51, 'Lui', '#a30041', '#000000', '#ffffff', 62, 78, 90, 74, 62, 65, 84, 84, 80, 1, '2026-01-17 11:31:24', NULL),
(52, 'Noel', '#d9d9d9', '#000b5c', '#000000', 61, 90, 69, 62, 61, 79, 85, 92, 100, 1, '2026-01-17 11:31:24', NULL),
(53, 'Lamy', '#00fbff', '#000000', '#ffffff', 73, 72, 82, 72, 69, 67, 79, 85, 10, 1, '2026-01-17 11:31:24', NULL),
(54, 'Sora', '#0040ff', '#ffffff', '#ff7070', 72, 72, 72, 72, 70, 69, 85, 86, 90, 1, '2026-01-17 11:31:24', NULL),
(55, 'Mio', '#ff0000', '#000000', '#ffffff', 79, 68, 84, 77, 72, 62, 83, 74, 50, 1, '2026-01-17 11:31:24', NULL),
(56, 'Inanis', '#7300ff', '#000000', '#ffb057', 70, 70, 91, 73, 63, 67, 86, 79, 70, 2, '2026-01-17 11:31:24', NULL),
(57, 'Aki', '#62ff29', '#ff0059', '#ffdd80', 62, 84, 79, 83, 71, 69, 72, 77, 100, 1, '2026-01-17 11:31:51', NULL),
(58, 'Su', '#00ffb3', '#ffffff', '#ffe252', 87, 67, 82, 82, 79, 64, 70, 68, 35, 4, '2026-01-17 11:31:51', NULL),
(59, 'Iofifteen', '#93ff75', '#002699', '#ffdbf0', 80, 75, 78, 77, 65, 70, 79, 74, 20, 3, '2026-01-17 11:31:51', NULL),
(60, 'Mumei', '#d69200', '#007508', '#ffffff', 88, 61, 80, 88, 88, 61, 63, 70, 40, 2, '2026-01-17 11:31:51', NULL),
(61, 'Flare', '#ff8800', '#ffffff', '#5cc9ff', 86, 69, 80, 79, 77, 66, 71, 71, 30, 1, '2026-01-17 11:31:51', NULL),
(62, 'Botan', '#000000', '#ffffff', '#c4ffc2', 85, 65, 86, 84, 61, 65, 65, 87, 40, 1, '2026-01-17 11:31:51', NULL),
(63, 'Polka', '#ff0000', '#0091ff', '#ffdd00', 81, 72, 67, 78, 84, 70, 72, 73, 40, 1, '2026-01-17 11:31:51', NULL),
(64, 'Laplus', '#5900ff', '#000000', '#ffffff', 82, 62, 78, 79, 80, 73, 65, 79, 50, 1, '2026-01-17 11:31:51', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tier_matches`
--

CREATE TABLE `tier_matches` (
  `id` bigint(20) NOT NULL,
  `season_id` int(11) NOT NULL,
  `tier` varchar(45) DEFAULT NULL,
  `round` int(11) DEFAULT NULL,
  `team1_id` int(11) DEFAULT 0,
  `team2_id` int(11) DEFAULT 0,
  `team1_score` int(11) DEFAULT 0,
  `team2_score` int(11) DEFAULT 0,
  `team1_possession` int(11) DEFAULT 50,
  `team2_possession` int(11) DEFAULT 50,
  `team1_foul` int(11) DEFAULT 0,
  `team2_foul` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tier_positions`
--

CREATE TABLE `tier_positions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tier_standing_id` bigint(20) UNSIGNED NOT NULL,
  `season_id` bigint(20) UNSIGNED NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `result` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tier_seasons`
--

CREATE TABLE `tier_seasons` (
  `id` int(11) NOT NULL,
  `season` int(11) NOT NULL,
  `teams_count` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `meta` varchar(45) DEFAULT 'attack'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tier_standings`
--

CREATE TABLE `tier_standings` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL DEFAULT 0,
  `tier` varchar(45) DEFAULT NULL,
  `match_played` int(11) DEFAULT 0,
  `goal_scored` int(11) NOT NULL DEFAULT 0,
  `goal_conceded` int(11) NOT NULL DEFAULT 0,
  `goal_difference` int(11) DEFAULT 0,
  `average_possession` double DEFAULT 50,
  `foul` int(11) DEFAULT 0,
  `points` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `win` int(11) DEFAULT 0,
  `draw` int(11) DEFAULT 0,
  `lose` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tier_team_groups`
--

CREATE TABLE `tier_team_groups` (
  `id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `tier` varchar(255) NOT NULL,
  `team_ids` varchar(1015) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cup_eliminate_stage_matches`
--
ALTER TABLE `cup_eliminate_stage_matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cup_eliminate_season` (`season_id`),
  ADD KEY `idx_cup_eliminate_winner` (`winner_id`);

--
-- Chỉ mục cho bảng `cup_group_stage_matches`
--
ALTER TABLE `cup_group_stage_matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cup_group_matches_season` (`season_id`);

--
-- Chỉ mục cho bảng `cup_group_teams`
--
ALTER TABLE `cup_group_teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cup_group_teams_season` (`season_id`);

--
-- Chỉ mục cho bảng `cup_positions`
--
ALTER TABLE `cup_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cup_positions_season_id_index` (`season_id`);

--
-- Chỉ mục cho bảng `cup_seasons`
--
ALTER TABLE `cup_seasons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cup_seasons_season` (`season`);

--
-- Chỉ mục cho bảng `cup_standings`
--
ALTER TABLE `cup_standings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cup_standings_season` (`season_id`,`team_id`);

--
-- Chỉ mục cho bảng `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tier_matches`
--
ALTER TABLE `tier_matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tier_matches_season` (`season_id`,`tier`);

--
-- Chỉ mục cho bảng `tier_positions`
--
ALTER TABLE `tier_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tier_positions_season_id_index` (`season_id`);

--
-- Chỉ mục cho bảng `tier_seasons`
--
ALTER TABLE `tier_seasons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tier_seasons_season` (`season`);

--
-- Chỉ mục cho bảng `tier_standings`
--
ALTER TABLE `tier_standings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tier_standings_season` (`season_id`,`team_id`);

--
-- Chỉ mục cho bảng `tier_team_groups`
--
ALTER TABLE `tier_team_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tier_team_groups_season` (`season_id`,`tier`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cup_eliminate_stage_matches`
--
ALTER TABLE `cup_eliminate_stage_matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT cho bảng `cup_group_stage_matches`
--
ALTER TABLE `cup_group_stage_matches`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=673;

--
-- AUTO_INCREMENT cho bảng `cup_group_teams`
--
ALTER TABLE `cup_group_teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `cup_positions`
--
ALTER TABLE `cup_positions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT cho bảng `cup_seasons`
--
ALTER TABLE `cup_seasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `cup_standings`
--
ALTER TABLE `cup_standings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT cho bảng `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT cho bảng `tier_matches`
--
ALTER TABLE `tier_matches`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11029;

--
-- AUTO_INCREMENT cho bảng `tier_positions`
--
ALTER TABLE `tier_positions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

--
-- AUTO_INCREMENT cho bảng `tier_seasons`
--
ALTER TABLE `tier_seasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT cho bảng `tier_standings`
--
ALTER TABLE `tier_standings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1777;

--
-- AUTO_INCREMENT cho bảng `tier_team_groups`
--
ALTER TABLE `tier_team_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
