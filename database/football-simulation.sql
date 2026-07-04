-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: football_simulation
-- ------------------------------------------------------
-- Server version	8.0.46

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
  `id` int NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `round` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `team1_id` int DEFAULT NULL,
  `team2_id` int DEFAULT NULL,
  `team1_score` tinyint(1) DEFAULT NULL,
  `team2_score` tinyint(1) DEFAULT NULL,
  `team1_possession` int DEFAULT '50',
  `team2_possession` int DEFAULT '50',
  `team1_foul` int DEFAULT '0',
  `team2_foul` int DEFAULT '0',
  `winner_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cup_eliminate_season` (`season_id`),
  KEY `idx_cup_eliminate_winner` (`winner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `id` bigint NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `group` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `round` int DEFAULT NULL,
  `team1_id` int DEFAULT '0',
  `team2_id` int DEFAULT '0',
  `team1_score` int DEFAULT '0',
  `team2_score` int DEFAULT '0',
  `team1_possession` int DEFAULT '50',
  `team2_possession` int DEFAULT '50',
  `team1_foul` int DEFAULT '0',
  `team2_foul` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cup_group_matches_season` (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=897 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `id` int NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `team_ids` varchar(1015) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cup_group_teams_season` (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cup_standing_id` bigint unsigned NOT NULL,
  `season_id` bigint unsigned NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `result` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'group_stage',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cup_positions_season_id_index` (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `id` int NOT NULL AUTO_INCREMENT,
  `season` int NOT NULL,
  `teams_count` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `meta` varchar(45) COLLATE utf8mb4_general_ci DEFAULT 'attack',
  PRIMARY KEY (`id`),
  KEY `idx_cup_seasons_season` (`season`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `id` int NOT NULL AUTO_INCREMENT,
  `team_id` int NOT NULL,
  `season_id` int NOT NULL DEFAULT '0',
  `group` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `match_played` int DEFAULT '0',
  `goal_scored` int NOT NULL DEFAULT '0',
  `goal_conceded` int NOT NULL DEFAULT '0',
  `goal_difference` int DEFAULT '0',
  `average_possession` double DEFAULT '50',
  `foul` int DEFAULT '0',
  `points` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `win` int DEFAULT '0',
  `draw` int DEFAULT '0',
  `lose` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_cup_standings_season` (`season_id`,`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cup_standings`
--

LOCK TABLES `cup_standings` WRITE;
/*!40000 ALTER TABLE `cup_standings` DISABLE KEYS */;
/*!40000 ALTER TABLE `cup_standings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2019_12_14_000001_create_personal_access_tokens_table',1),(2,'2026_01_28_000000_update_teams_stats_columns',1),(3,'2026_06_24_000000_add_goalkeeping_and_luck_to_teams',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regions` (
  `id` int NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shortname` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `color_1` varchar(10) COLLATE utf8mb4_general_ci DEFAULT '000000',
  `color_2` varchar(10) COLLATE utf8mb4_general_ci DEFAULT '000000',
  `color_3` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attack` int DEFAULT '0',
  `defense` int DEFAULT '0',
  `control` int DEFAULT '0',
  `creative` int DEFAULT '0',
  `pace` int DEFAULT '0',
  `mental` int DEFAULT '0',
  `discipline` int DEFAULT '0',
  `luck` int NOT NULL DEFAULT '50',
  `stamina` int DEFAULT '0',
  `goalkeeping` int NOT NULL DEFAULT '50',
  `form` int DEFAULT '50',
  `region` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `shirt_type` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'Suisei','#00c8f0','#242b4c','#ffffff',95,89,88,89,75,85,86,50,93,50,100,1,'2024-12-17 13:05:50','checkered'),(2,'Miko','#ff99e9','#ffffff','#ff6600',83,95,76,82,75,86,84,50,93,50,15,1,'2024-12-17 13:05:50',NULL),(3,'Marine','#b30000','#000000','#ffd500',77,83,96,88,73,82,90,50,82,50,50,1,'2024-12-17 13:05:50',NULL),(4,'Iroha','#1aff94','#ffffff','#ffdd00',93,68,85,92,83,77,78,50,94,50,90,1,'2024-12-17 13:05:50',NULL),(5,'Amelia','#f1f500','#ffffff','#4dfffc',85,94,91,89,89,72,69,50,83,50,45,2,'2024-12-17 13:05:50',NULL),(6,'Kronii','#0011ff','#ffffff','#fbff00',84,84,84,84,84,84,84,50,84,50,55,2,'2024-12-17 13:05:50',NULL),(7,'Bijou','#ffffff','#5b1a4f','#f785ff',68,96,85,82,70,86,91,50,94,50,20,2,'2024-12-17 13:05:50',NULL),(8,'Kobo','#00ffee','#ffffff','#ff5757',93,72,83,81,97,82,80,50,83,50,45,3,'2024-12-17 13:05:50',NULL),(9,'Subaru','#fff700','#000000','#ffffff',73,80,81,82,83,85,72,50,93,50,70,1,'2024-12-17 13:05:50',NULL),(10,'Pekora','#00fffb','#ffffff','#ffa033',95,67,86,97,93,80,71,50,83,50,70,1,'2024-12-17 13:05:50','halves'),(11,'Fauna','#00ff11','#002c94','#ffffff',73,93,93,62,60,78,82,50,82,50,5,2,'2024-12-17 13:05:50',NULL),(12,'Ollie','#c70039','#000000','#b4a7c8',94,60,85,90,93,72,70,50,83,50,40,3,'2024-12-17 13:05:50',NULL),(13,'Ao','#004cff','#000000','#ffffff',69,83,81,83,75,79,72,50,81,50,35,4,'2024-12-17 13:05:50',NULL),(14,'Fubuki','#ffffff','#000000','#00fffb',78,78,80,85,81,83,75,50,87,50,75,1,'2024-12-17 13:05:50',NULL),(15,'Azki','#ff24af','#491313','#ffffff',70,85,89,78,73,78,90,50,85,50,45,1,'2024-12-17 13:05:50',NULL),(16,'Towa','#ae00ff','#000000','#ffffff',82,83,95,76,85,68,71,50,83,50,90,1,'2024-12-17 13:05:50',NULL),(17,'Cecilia','#00ff1e','#ffffff','#ffdd00',83,83,83,72,84,65,84,50,93,50,50,2,'2024-12-17 13:05:50',NULL),(18,'Gigi','#ffd500','#000000','#ff0000',96,70,82,95,95,82,63,50,91,50,25,2,'2024-12-17 13:05:50',NULL),(19,'Calliope','#fd9eff','#000000','#ffffff',83,91,93,72,67,70,81,50,90,50,75,2,'2024-12-17 13:05:50',NULL),(20,'Kaela','#ffea00','#000000','#ff0000',62,89,91,83,68,72,90,50,95,50,10,3,'2024-12-17 13:05:50',NULL),(21,'Kanade','#fdff70','#ffffff','#8e0108',86,78,89,85,89,85,74,50,87,50,80,4,'2024-12-17 13:05:50',NULL),(22,'Kanata','#ffffff','#003670','#80f5e7',68,92,77,76,79,80,67,50,85,50,70,1,'2024-12-17 13:05:50',NULL),(23,'Raora','#ff6bd3','#000000','#b8fffa',80,90,89,82,68,76,90,50,73,50,55,2,'2024-12-17 13:05:50',NULL),(24,'Nerissa','#0011ff','#030303','#ffffff',81,86,83,86,81,65,84,50,83,50,55,2,'2024-12-17 13:05:50',NULL),(25,'Baelz','#ff0000','#ffffff','#ffe11f',87,63,83,92,97,78,66,50,82,50,100,2,'2024-12-17 13:05:50',NULL),(26,'Zeta','#d4d4d4','#ffffff','#000000',81,81,87,73,79,75,65,50,81,50,25,3,'2024-12-17 13:05:50',NULL),(27,'Hajime','#e1bdff','#ffffff','#8000ff',84,79,90,81,76,72,77,50,88,50,65,4,'2024-12-17 13:05:50',NULL),(28,'Chihaya','#00803e','#000000','#ffffff',82,83,74,72,69,72,68,50,79,50,25,4,'2024-12-17 13:05:50',NULL),(29,'Korone','#ffdd00','#ffffff','#ff0000',87,60,79,81,88,76,60,50,92,50,40,1,'2024-12-17 13:05:50',NULL),(30,'Elizabeth','#ff0000','#000000','#6cc0f4',70,91,79,80,64,71,83,50,86,50,70,2,'2024-12-17 13:05:50',NULL),(31,'Moona','#bb00ff','#000000','#fffca8',78,78,86,77,75,72,71,50,86,50,35,3,'2024-12-17 13:05:50',NULL),(32,'Raden','#2c875c','#000000','#f8e944',75,85,81,81,67,70,79,50,83,50,10,4,'2024-12-17 13:05:50',NULL),(33,'Fuwawa','#05e2ff','#ffffff','#ff7ae2',71,86,81,82,76,82,61,50,84,50,5,2,'2024-12-17 13:05:50',NULL),(34,'Mococo','#ff4df0','#ffffff','#8af1ff',87,64,80,82,80,83,61,50,87,50,50,2,'2024-12-17 13:05:50',NULL),(35,'Okayu','#e27aff','#000000','#ffffff',80,88,85,90,69,69,80,50,86,50,90,1,'2024-12-17 13:05:50',NULL),(36,'Vivi','#ff66a1','#000000','#42d9ff',87,85,87,86,87,78,79,50,82,50,30,4,'2024-12-17 13:05:50',NULL),(37,'Riona','#ff3856','#000000','#d1d1d1',91,72,82,88,86,68,75,50,85,50,20,4,'2026-01-17 11:29:57',NULL),(38,'Gura','#00bfff','#ffffff','#ff3838',84,72,81,86,81,73,70,50,77,50,95,2,'2026-01-17 11:30:05',NULL),(39,'Aqua','#ff85fb','#ffffff','#004cff',82,74,81,84,74,73,72,50,82,50,55,1,'2026-01-17 11:31:13',NULL),(40,'Risu','#ff99ca','#ffffff','#8f0000',81,78,82,79,82,73,65,50,84,50,55,3,'2026-01-17 11:31:13',NULL),(41,'Chloe','#ff0000','#ffffff','#000000',80,66,79,79,76,71,66,50,81,50,5,1,'2026-01-17 11:31:24',NULL),(42,'Koyori','#ff5cfa','#ffffff','#66ffe0',76,83,77,78,70,74,80,50,85,50,40,1,'2026-01-17 11:31:24',NULL),(43,'Kiara','#ff9500','#ffffff','#35fd82',74,89,84,76,67,79,63,50,91,50,35,2,'2026-01-17 11:31:24',NULL),(44,'Watame','#ffea00','#ffffff','#7a0000',60,91,73,67,62,77,84,50,85,50,10,1,'2026-01-17 11:31:24',NULL),(45,'Shiori','#000000','#ffffff','#edbdff',88,63,88,83,82,68,67,50,85,50,65,2,'2026-01-17 11:31:24',NULL),(46,'Ayame','#ff0000','#ffffff','#000000',84,74,78,80,84,70,83,50,69,50,75,1,'2026-01-17 11:31:24',NULL),(47,'Irys','#ff006f','#ffffff','#6a0080',87,73,82,91,75,65,69,50,81,50,5,2,'2026-01-17 11:31:24',NULL),(48,'Niko','#ff7300','#000000','#ffffff',85,75,82,79,66,76,72,50,86,50,80,4,'2026-01-17 11:31:24',NULL),(49,'Nene','#ff5900','#ffffff','#ffdd00',65,88,85,87,69,70,68,50,92,50,45,1,'2026-01-17 11:31:24',NULL),(50,'Anya','#ffc800','#02007a','#a20101',62,88,81,68,62,66,86,50,86,50,20,3,'2026-01-17 11:31:24',NULL),(51,'Lui','#a30041','#000000','#ffffff',62,78,90,74,62,65,84,50,84,50,80,1,'2026-01-17 11:31:24',NULL),(52,'Noel','#d9d9d9','#000b5c','#000000',61,90,69,62,61,79,85,50,92,50,100,1,'2026-01-17 11:31:24',NULL),(53,'Lamy','#00fbff','#000000','#ffffff',73,72,82,72,69,67,79,50,85,50,10,1,'2026-01-17 11:31:24',NULL),(54,'Sora','#0040ff','#ffffff','#ff7070',72,72,72,72,70,69,85,50,86,50,90,1,'2026-01-17 11:31:24',NULL),(55,'Mio','#ff0000','#000000','#ffffff',79,68,84,77,72,62,83,50,74,50,50,1,'2026-01-17 11:31:24',NULL),(56,'Inanis','#7300ff','#000000','#ffb057',70,70,91,73,63,67,86,50,79,50,70,2,'2026-01-17 11:31:24',NULL),(57,'Aki','#62ff29','#ff0059','#ffdd80',62,84,79,83,71,69,72,50,77,50,100,1,'2026-01-17 11:31:51',NULL),(58,'Su','#00ffb3','#ffffff','#ffe252',87,67,82,82,79,64,70,50,68,50,35,4,'2026-01-17 11:31:51',NULL),(59,'Iofifteen','#93ff75','#002699','#ffdbf0',80,75,78,77,65,70,79,50,74,50,20,3,'2026-01-17 11:31:51',NULL),(60,'Mumei','#d69200','#007508','#ffffff',88,61,80,88,88,61,63,50,70,50,40,2,'2026-01-17 11:31:51',NULL),(61,'Flare','#ff8800','#ffffff','#5cc9ff',86,69,80,79,77,66,71,50,71,50,30,1,'2026-01-17 11:31:51',NULL),(62,'Botan','#000000','#ffffff','#c4ffc2',85,65,86,84,61,65,65,50,87,50,40,1,'2026-01-17 11:31:51',NULL),(63,'Polka','#ff0000','#0091ff','#ffdd00',81,72,67,78,84,70,72,50,73,50,40,1,'2026-01-17 11:31:51',NULL),(64,'Laplus','#5900ff','#000000','#ffffff',82,62,78,79,80,73,65,50,79,50,50,1,'2026-01-17 11:31:51',NULL);
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tier_matches`
--

DROP TABLE IF EXISTS `tier_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tier_matches` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `tier` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `round` int DEFAULT NULL,
  `team1_id` int DEFAULT '0',
  `team2_id` int DEFAULT '0',
  `team1_score` int DEFAULT '0',
  `team2_score` int DEFAULT '0',
  `team1_possession` int DEFAULT '50',
  `team2_possession` int DEFAULT '50',
  `team1_foul` int DEFAULT '0',
  `team2_foul` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tier_matches_season` (`season_id`,`tier`)
) ENGINE=InnoDB AUTO_INCREMENT=11029 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tier_matches`
--

LOCK TABLES `tier_matches` WRITE;
/*!40000 ALTER TABLE `tier_matches` DISABLE KEYS */;
/*!40000 ALTER TABLE `tier_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tier_positions`
--

DROP TABLE IF EXISTS `tier_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tier_positions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tier_standing_id` bigint unsigned NOT NULL,
  `season_id` bigint unsigned NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `result` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tier_positions_season_id_index` (`season_id`)
) ENGINE=InnoDB AUTO_INCREMENT=241 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tier_positions`
--

LOCK TABLES `tier_positions` WRITE;
/*!40000 ALTER TABLE `tier_positions` DISABLE KEYS */;
/*!40000 ALTER TABLE `tier_positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tier_seasons`
--

DROP TABLE IF EXISTS `tier_seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tier_seasons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season` int NOT NULL,
  `teams_count` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `meta` varchar(45) COLLATE utf8mb4_general_ci DEFAULT 'attack',
  PRIMARY KEY (`id`),
  KEY `idx_tier_seasons_season` (`season`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tier_seasons`
--

LOCK TABLES `tier_seasons` WRITE;
/*!40000 ALTER TABLE `tier_seasons` DISABLE KEYS */;
/*!40000 ALTER TABLE `tier_seasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tier_standings`
--

DROP TABLE IF EXISTS `tier_standings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tier_standings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `team_id` int NOT NULL,
  `season_id` int NOT NULL DEFAULT '0',
  `tier` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `match_played` int DEFAULT '0',
  `goal_scored` int NOT NULL DEFAULT '0',
  `goal_conceded` int NOT NULL DEFAULT '0',
  `goal_difference` int DEFAULT '0',
  `average_possession` double DEFAULT '50',
  `foul` int DEFAULT '0',
  `points` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `win` int DEFAULT '0',
  `draw` int DEFAULT '0',
  `lose` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_tier_standings_season` (`season_id`,`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1777 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tier_standings`
--

LOCK TABLES `tier_standings` WRITE;
/*!40000 ALTER TABLE `tier_standings` DISABLE KEYS */;
/*!40000 ALTER TABLE `tier_standings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tier_team_groups`
--

DROP TABLE IF EXISTS `tier_team_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tier_team_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `tier` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `team_ids` varchar(1015) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tier_team_groups_season` (`season_id`,`tier`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tier_team_groups`
--

LOCK TABLES `tier_team_groups` WRITE;
/*!40000 ALTER TABLE `tier_team_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `tier_team_groups` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-04 19:12:08
