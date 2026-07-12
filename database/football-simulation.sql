-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: football-simulation
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cup_eliminate_stage_matches`
--

DROP TABLE IF EXISTS `cup_eliminate_stage_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cup_eliminate_stage_matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` int(11) NOT NULL,
  `round` varchar(45) DEFAULT NULL,
  `branch` varchar(45) DEFAULT NULL,
  `slot_index` smallint(5) unsigned NOT NULL DEFAULT 0,
  `team1_id` int(11) DEFAULT NULL,
  `team2_id` int(11) DEFAULT NULL,
  `team1_score` tinyint(4) DEFAULT NULL,
  `team2_score` tinyint(4) DEFAULT NULL,
  `team1_possession` int(11) NOT NULL DEFAULT 50,
  `team2_possession` int(11) NOT NULL DEFAULT 50,
  `team1_foul` int(11) NOT NULL DEFAULT 0,
  `team2_foul` int(11) NOT NULL DEFAULT 0,
  `match_events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`match_events`)),
  `winner_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cup_eliminate_stage_matches_season_id_index` (`season_id`),
  KEY `cup_eliminate_stage_matches_season_id_round_slot_index_index` (`season_id`,`round`,`slot_index`),
  KEY `cup_eliminate_stage_matches_winner_id_index` (`winner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=449 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cup_eliminate_stage_matches`
--

LOCK TABLES `cup_eliminate_stage_matches` WRITE;
/*!40000 ALTER TABLE `cup_eliminate_stage_matches` DISABLE KEYS */;
/*!40000 ALTER TABLE `cup_eliminate_stage_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cup_group_stage_matches`
--

DROP TABLE IF EXISTS `cup_group_stage_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cup_group_stage_matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` int(11) NOT NULL,
  `group` varchar(45) DEFAULT NULL,
  `round` int(11) DEFAULT NULL,
  `team1_id` int(11) NOT NULL DEFAULT 0,
  `team2_id` int(11) NOT NULL DEFAULT 0,
  `team1_score` int(11) DEFAULT NULL,
  `team2_score` int(11) DEFAULT NULL,
  `team1_possession` int(11) NOT NULL DEFAULT 50,
  `team2_possession` int(11) NOT NULL DEFAULT 50,
  `team1_foul` int(11) NOT NULL DEFAULT 0,
  `team2_foul` int(11) NOT NULL DEFAULT 0,
  `match_events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`match_events`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cup_group_stage_matches_season_id_index` (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cup_group_stage_matches`
--

LOCK TABLES `cup_group_stage_matches` WRITE;
/*!40000 ALTER TABLE `cup_group_stage_matches` DISABLE KEYS */;
/*!40000 ALTER TABLE `cup_group_stage_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cup_group_teams`
--

DROP TABLE IF EXISTS `cup_group_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cup_group_teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` int(11) NOT NULL,
  `group` varchar(255) NOT NULL,
  `team_ids` varchar(1015) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cup_group_teams_season_id_index` (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cup_group_teams`
--

LOCK TABLES `cup_group_teams` WRITE;
/*!40000 ALTER TABLE `cup_group_teams` DISABLE KEYS */;
/*!40000 ALTER TABLE `cup_group_teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cup_positions`
--

DROP TABLE IF EXISTS `cup_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cup_positions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cup_standing_id` bigint(20) unsigned NOT NULL,
  `season_id` bigint(20) unsigned NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `result` varchar(45) NOT NULL DEFAULT 'group_stage',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cup_positions_season_id_index` (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=897 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cup_positions`
--

LOCK TABLES `cup_positions` WRITE;
/*!40000 ALTER TABLE `cup_positions` DISABLE KEYS */;
/*!40000 ALTER TABLE `cup_positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cup_seasons`
--

DROP TABLE IF EXISTS `cup_seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cup_seasons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season` int(11) NOT NULL,
  `teams_count` int(11) DEFAULT NULL,
  `meta` varchar(45) NOT NULL DEFAULT 'attack',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cup_seasons_season_index` (`season`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cup_seasons`
--

LOCK TABLES `cup_seasons` WRITE;
/*!40000 ALTER TABLE `cup_seasons` DISABLE KEYS */;
/*!40000 ALTER TABLE `cup_seasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cup_standings`
--

DROP TABLE IF EXISTS `cup_standings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cup_standings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL DEFAULT 0,
  `group` varchar(45) DEFAULT NULL,
  `match_played` int(11) NOT NULL DEFAULT 0,
  `goal_scored` int(11) NOT NULL DEFAULT 0,
  `goal_conceded` int(11) NOT NULL DEFAULT 0,
  `goal_difference` int(11) NOT NULL DEFAULT 0,
  `average_possession` double NOT NULL DEFAULT 50,
  `foul` int(11) NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 0,
  `win` int(11) NOT NULL DEFAULT 0,
  `draw` int(11) NOT NULL DEFAULT 0,
  `lose` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cup_standings_season_id_team_id_index` (`season_id`,`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=897 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cup_standings`
--

LOCK TABLES `cup_standings` WRITE;
/*!40000 ALTER TABLE `cup_standings` DISABLE KEYS */;
/*!40000 ALTER TABLE `cup_standings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `league_group_teams`
--

DROP TABLE IF EXISTS `league_group_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `league_group_teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` int(11) NOT NULL,
  `group` varchar(255) NOT NULL,
  `team_ids` varchar(1015) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `league_group_teams_season_id_index` (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `league_group_teams`
--

LOCK TABLES `league_group_teams` WRITE;
/*!40000 ALTER TABLE `league_group_teams` DISABLE KEYS */;
/*!40000 ALTER TABLE `league_group_teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `league_matches`
--

DROP TABLE IF EXISTS `league_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `league_matches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` int(11) NOT NULL,
  `division` varchar(45) NOT NULL,
  `round` int(11) DEFAULT NULL,
  `team1_id` int(11) NOT NULL DEFAULT 0,
  `team2_id` int(11) NOT NULL DEFAULT 0,
  `team1_score` int(11) DEFAULT NULL,
  `team2_score` int(11) DEFAULT NULL,
  `team1_possession` int(11) NOT NULL DEFAULT 50,
  `team2_possession` int(11) NOT NULL DEFAULT 50,
  `team1_foul` int(11) NOT NULL DEFAULT 0,
  `team2_foul` int(11) NOT NULL DEFAULT 0,
  `match_events` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`match_events`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `league_matches_season_id_division_index` (`season_id`,`division`)
) ENGINE=InnoDB AUTO_INCREMENT=19381 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `league_matches`
--

LOCK TABLES `league_matches` WRITE;
/*!40000 ALTER TABLE `league_matches` DISABLE KEYS */;
/*!40000 ALTER TABLE `league_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `league_positions`
--

DROP TABLE IF EXISTS `league_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `league_positions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `league_standing_id` bigint(20) unsigned NOT NULL,
  `season_id` bigint(20) unsigned NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `result` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `league_positions_season_id_index` (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1021 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `league_positions`
--

LOCK TABLES `league_positions` WRITE;
/*!40000 ALTER TABLE `league_positions` DISABLE KEYS */;
/*!40000 ALTER TABLE `league_positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `league_seasons`
--

DROP TABLE IF EXISTS `league_seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `league_seasons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season` int(11) NOT NULL,
  `teams_count` int(11) NOT NULL,
  `meta` varchar(45) NOT NULL DEFAULT 'attack',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `league_seasons_season_index` (`season`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `league_seasons`
--

LOCK TABLES `league_seasons` WRITE;
/*!40000 ALTER TABLE `league_seasons` DISABLE KEYS */;
/*!40000 ALTER TABLE `league_seasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `league_standings`
--

DROP TABLE IF EXISTS `league_standings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `league_standings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL DEFAULT 0,
  `division` varchar(45) DEFAULT NULL,
  `match_played` int(11) NOT NULL DEFAULT 0,
  `goal_scored` int(11) NOT NULL DEFAULT 0,
  `goal_conceded` int(11) NOT NULL DEFAULT 0,
  `goal_difference` int(11) NOT NULL DEFAULT 0,
  `average_possession` double NOT NULL DEFAULT 50,
  `foul` int(11) NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 0,
  `win` int(11) NOT NULL DEFAULT 0,
  `draw` int(11) NOT NULL DEFAULT 0,
  `lose` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `league_standings_season_id_team_id_index` (`season_id`,`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1021 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `league_standings`
--

LOCK TABLES `league_standings` WRITE;
/*!40000 ALTER TABLE `league_standings` DISABLE KEYS */;
/*!40000 ALTER TABLE `league_standings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `shortname` varchar(45) NOT NULL,
  `description` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
INSERT INTO `regions` VALUES (1,'Nhật Bản','JP',NULL),(2,'Ngoại Quốc','EN',NULL),(3,'Indonesia','ID',NULL),(4,'Dev_is','DV',NULL);
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `color_1` varchar(10) NOT NULL DEFAULT '000000',
  `color_2` varchar(10) NOT NULL DEFAULT '000000',
  `color_3` varchar(10) DEFAULT NULL,
  `attack` int(11) NOT NULL DEFAULT 50,
  `defense` int(11) NOT NULL DEFAULT 50,
  `control` int(11) NOT NULL DEFAULT 50,
  `creative` int(11) NOT NULL DEFAULT 50,
  `pace` int(11) NOT NULL DEFAULT 50,
  `mental` int(11) NOT NULL DEFAULT 50,
  `discipline` int(11) NOT NULL DEFAULT 50,
  `luck` int(11) NOT NULL DEFAULT 50,
  `stamina` int(11) NOT NULL DEFAULT 50,
  `goalkeeping` int(11) NOT NULL DEFAULT 50,
  `elo` int(11) NOT NULL DEFAULT 1000,
  `region_id` int(11) NOT NULL,
  `shirt_type` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teams_region_foreign` (`region_id`),
  CONSTRAINT `teams_region_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'Suisei','#00c8f0','#242b4c','#fbff00',95,95,95,90,90,85,90,86,90,84,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(2,'Miko','#ff99e9','#ffffff','#ff6600',86,95,87,99,92,90,84,71,93,83,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(3,'Marine','#ff0000','#000000','#ffd500',88,88,99,96,82,93,86,82,80,84,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(4,'Iroha','#1aff94','#ffffff','#ffdd00',93,85,87,82,95,85,99,78,95,74,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(5,'Amelia','#f1f500','#ffffff','#4dfffc',81,95,91,95,89,82,88,71,86,93,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(6,'Kronii','#0011ff','#ffffff','#fbff00',88,88,88,88,88,87,88,88,88,88,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(7,'Bijou','#ffffff','#5b1a4f','#f785ff',78,99,85,82,83,93,94,82,94,87,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(8,'Kobo','#00ffee','#ffffff','#ff5757',93,79,90,92,99,81,84,89,87,74,1000,3,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(9,'Subaru','#fff700','#000000','#ffffff',90,90,82,85,89,90,93,79,99,79,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(10,'Pekora','#00fffb','#ffffff','#ffa033',99,81,87,96,95,92,74,94,85,69,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(11,'Fauna','#00ff11','#002c94','#ffffff',75,92,94,69,70,85,97,80,89,83,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(12,'Ollie','#c70039','#000000','#b4a7c8',97,79,84,94,93,85,83,86,79,78,1000,3,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(13,'Ao','#004cff','#000000','#ffffff',81,81,88,84,88,81,82,92,81,71,1000,4,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(14,'Fubuki','#ffffff','#000000','#00fffb',84,84,84,84,84,92,90,91,91,72,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(15,'Azki','#ff24af','#491313','#ffffff',83,89,95,83,73,97,92,77,90,81,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(16,'Towa','#ae00ff','#000000','#ffffff',81,85,94,78,84,83,96,82,82,84,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(17,'Cecilia','#00ff1e','#ffffff','#ffdd00',86,86,86,86,86,77,97,83,95,88,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(18,'Gigi','#ffd500','#000000','#ff0000',97,87,83,98,95,85,73,88,90,79,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(19,'Calliope','#fd9eff','#000000','#ffffff',83,93,93,73,83,92,81,80,90,87,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(20,'Kaela','#ffea00','#000000','#ff0000',78,98,84,82,72,74,96,69,88,99,1000,3,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(21,'Kanade','#fdff70','#ffffff','#8e0108',87,83,90,87,91,96,90,84,85,81,1000,4,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(22,'Kanata','#ffffff','#003670','#80f5e7',78,91,83,78,79,84,92,75,83,93,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(23,'Raora','#ff6bd3','#000000','#b8fffa',79,83,93,84,77,82,92,79,81,83,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(24,'Nerissa','#0011ff','#030303','#ffffff',86,94,83,82,83,87,88,80,81,84,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(25,'Baelz','#ff0000','#ffffff','#ffe11f',94,73,83,87,97,74,72,87,94,74,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(26,'Zeta','#d4d4d4','#ffffff','#000000',92,85,85,81,84,86,85,92,84,77,1000,3,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(27,'Hajime','#e1bdff','#ffffff','#8000ff',85,82,93,85,86,78,78,66,93,69,1000,4,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(28,'Chihaya','#00803e','#000000','#ffffff',79,91,93,82,83,86,90,77,84,88,1000,4,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(29,'Korone','#ffdd00','#ffffff','#ff0000',93,83,83,96,94,95,84,70,85,69,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(30,'Elizabeth','#ff0000','#000000','#6cc0f4',81,92,84,75,83,81,95,79,84,85,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(31,'Moona','#bb00ff','#000000','#fffca8',90,90,90,80,80,80,83,75,85,75,1000,3,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(32,'Raden','#2c875c','#000000','#f8e944',85,81,89,93,83,83,84,84,80,57,1000,4,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(33,'Fuwamoco','#05e2ff','#f67aff','#ffffff',88,88,88,84,89,94,80,77,95,71,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(34,'Matsuri','#ffae00','#9e0000','#9effcd',92,69,85,81,90,91,72,65,88,67,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(35,'Okayu','#e27aff','#000000','#ffffff',83,85,88,86,76,76,89,89,85,80,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(36,'Vivi','#ff66a1','#000000','#42d9ff',84,87,87,86,81,84,87,92,88,83,1000,4,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(37,'Riona','#ff3856','#000000','#d1d1d1',93,85,85,82,91,89,90,77,90,87,1000,4,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(38,'Gura','#00bfff','#ffffff','#ff3838',93,75,85,92,91,84,80,86,79,85,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(39,'Aqua','#ff85fb','#ffffff','#004cff',89,81,85,81,87,83,83,85,83,81,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(40,'Risu','#ff99ca','#ffffff','#8f0000',88,79,93,89,87,78,68,79,84,65,1000,3,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(41,'Chloe','#ff0000','#ffffff','#000000',92,73,88,91,95,73,69,86,82,69,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(42,'Koyori','#ff5cfa','#ffffff','#66ffe0',82,84,94,82,82,80,88,71,95,72,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(43,'Kiara','#ff9500','#ffffff','#35fd82',85,89,87,83,82,85,74,61,91,83,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(44,'Watame','#ffea00','#ffffff','#7a0000',65,95,78,62,71,87,93,83,83,97,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(45,'Shiori','#000000','#ffffff','#edbdff',96,77,89,91,92,68,83,99,94,68,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(46,'Ayame','#ff0000','#ffffff','#000000',93,70,84,83,92,76,87,79,74,62,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(47,'Irys','#ff006f','#ffffff','#6a0080',89,75,80,84,90,69,69,91,81,88,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(48,'Niko','#ff7300','#000000','#ffffff',90,75,90,87,90,81,81,83,81,73,1000,4,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(49,'Nene','#ff5900','#ffffff','#ffdd00',88,82,89,81,88,79,74,86,89,76,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(50,'Anya','#ffc800','#02007a','#a20101',64,96,80,72,82,78,86,65,84,93,1000,3,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(51,'Lui','#a30041','#000000','#ffffff',75,85,95,72,67,86,97,68,86,86,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(52,'Noel','#d9d9d9','#000b5c','#000000',89,96,79,70,69,69,87,69,88,93,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(53,'Lamy','#00fbff','#000000','#ffffff',88,80,90,85,86,78,83,65,82,76,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(54,'Sora','#0040ff','#ffffff','#ff7070',80,80,80,80,80,80,80,80,90,70,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(55,'Mio','#ff0000','#000000','#ffffff',77,86,94,78,63,81,83,66,86,86,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(56,'Ina\'nis','#7300ff','#000000','#ffb057',74,74,96,75,75,85,95,71,85,70,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(57,'Aki','#62ff29','#ff0059','#ffdd80',67,95,89,71,69,82,88,69,88,93,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(58,'Su','#00ffb3','#ffffff','#ffe252',92,68,88,85,93,73,73,82,92,62,1000,4,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(59,'Reine','#002aff','#ffffff','#00ff7b',83,84,92,77,79,86,83,68,66,82,1000,3,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(60,'Mumei','#d69200','#007508','#ffffff',97,63,82,86,94,73,68,95,82,60,1000,2,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(61,'Flare','#ff8800','#ffffff','#5cc9ff',88,69,87,83,85,86,71,86,81,64,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(62,'Botan','#000000','#ffffff','#c4ffc2',93,83,90,76,81,95,70,66,85,61,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(63,'Polka','#ff0000','#0091ff','#ffdd00',92,74,81,89,96,78,71,85,83,63,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56'),(64,'Laplus','#5900ff','#000000','#ffffff',87,71,89,81,87,81,70,91,81,62,1000,1,NULL,'2026-07-10 13:44:23','2026-07-12 02:59:56');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-12 17:04:51
