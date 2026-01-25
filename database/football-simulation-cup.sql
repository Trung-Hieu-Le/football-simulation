-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: football-simulation-cup
-- ------------------------------------------------------
-- Server version	8.0.30

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
-- Table structure for table `eliminate_stage_matches`
--

DROP TABLE IF EXISTS `eliminate_stage_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eliminate_stage_matches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `round` varchar(45) DEFAULT NULL,
  `branch` varchar(45) DEFAULT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group_stage_matches`
--

DROP TABLE IF EXISTS `group_stage_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_stage_matches` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `group` varchar(45) DEFAULT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22501 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `group_stage_standings`
--

DROP TABLE IF EXISTS `group_stage_standings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_stage_standings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `team_id` int NOT NULL,
  `season_id` int NOT NULL DEFAULT '0',
  `group` varchar(45) DEFAULT NULL,
  `match_played` int DEFAULT '0',
  `goal_scored` int NOT NULL DEFAULT '0',
  `goal_conceded` int NOT NULL DEFAULT '0',
  `goal_difference` int DEFAULT '0',
  `average_possession` double DEFAULT '50',
  `foul` int DEFAULT '0',
  `points` int DEFAULT '0',
  `position` int DEFAULT '0',
  `title` varchar(45) DEFAULT 'group_stage',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `win` int DEFAULT '0',
  `draw` int DEFAULT '0',
  `lose` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3942 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_stage_standings`
--

--
-- Table structure for table `group_teams`
--

DROP TABLE IF EXISTS `group_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_teams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `group` varchar(255) NOT NULL,
  `team_ids` varchar(1015) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1264 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regions` (
  `id` int NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `shortname` varchar(45) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;
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
-- Table structure for table `seasons`
--

DROP TABLE IF EXISTS `seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seasons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season` int NOT NULL,
  `teams_count` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `meta` varchar(45) DEFAULT 'attack',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `color_1` varchar(10) DEFAULT '000000',
  `color_2` varchar(10) DEFAULT '000000',
  `color_3` varchar(10) DEFAULT NULL,
  `attack` int DEFAULT '0',
  `defense` int DEFAULT '0',
  `control` int DEFAULT '0',
  `stamina` int DEFAULT '0',
  `aggressive` int DEFAULT '0',
  `penalty` int DEFAULT '0',
  `form` int DEFAULT '50',
  `region` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `shirt_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=626 DEFAULT CHARSET=utf8mb4 ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'Suisei','#00c8f0','#242b4c','#ffffff',95,78,89,85,92,31,50,1,'2024-12-17 13:05:50','checkered'),(2,'Miko','#ff99e9','#ffffff','#ff6600',74,90,77,95,74,58,95,1,'2024-12-17 13:05:50',NULL),(3,'Marine','#b30000','#000000','#ffd500',75,83,95,75,81,59,75,1,'2024-12-17 13:05:50',NULL),(4,'Iroha','#1aff94','#ffffff','#ffdd00',90,67,87,90,81,53,80,1,'2024-12-17 13:05:50',NULL),(5,'Amelia','#f1f500','#ffffff','#4dfffc',70,93,85,83,81,57,100,2,'2024-12-17 13:05:50',NULL),(6,'Kronii','#0011ff','#ffffff','#fbff00',78,78,78,78,78,78,95,2,'2024-12-17 13:05:50',NULL),(7,'Bijou','#ffffff','#5b1a4f','#f785ff',60,95,81,94,85,53,100,2,'2024-12-17 13:05:50',NULL),(8,'Kobo','#00ffee','#ffffff','#ff5757',93,72,83,83,85,53,85,3,'2024-12-17 13:05:50',NULL),(9,'Subaru','#fff700','#000000','#ffffff',74,77,81,91,91,44,5,1,'2024-12-17 13:05:50',NULL),(10,'Pekora','#00fffb','#ffffff','#ffa033',95,66,88,83,91,35,100,1,'2024-12-17 13:05:50','halves'),(11,'Chloe','#ff0000','#000000','#ffffff',84,70,81,80,78,63,90,1,'2024-12-17 13:05:50','sleeves'),(12,'Gura','#006eff','#ffffff','#faffff',94,75,92,75,83,41,70,2,'2024-12-17 13:05:50',NULL),(13,'Fauna','#00ff11','#002c94','#ffffff',73,94,94,84,77,36,5,2,'2024-12-17 13:05:50',NULL),(14,'Fuwamoco','#61edff','#000000','#f7a6f0',82,82,90,92,67,47,80,2,'2024-12-17 13:05:50',NULL),(15,'Ollie','#c70039','#000000','#b4a7c8',94,59,90,86,83,47,15,3,'2024-12-17 13:05:50',NULL),(16,'Ao','#004cff','#000000','#ffffff',78,91,86,82,81,42,65,4,'2024-12-17 13:05:50',NULL),(17,'Fubuki','#ffffff','#000000','#00fffb',77,77,83,89,82,42,10,1,'2024-12-17 13:05:50',NULL),(18,'Noel','#ededed','#042676','#000000',54,93,72,81,88,60,60,1,'2024-12-17 13:05:50',NULL),(19,'Towa','#ae00ff','#000000','#ffffff',80,78,93,80,69,48,10,1,'2024-12-17 13:05:50',NULL),(20,'Cecilia','#00ff1e','#ffffff','#ffdd00',83,83,83,93,75,33,5,2,'2024-12-17 13:05:50',NULL),(21,'Gigi','#ffd500','#000000','#ff0000',88,62,78,90,91,41,5,2,'2024-12-17 13:05:50',NULL),(22,'Calliope','#fd9eff','#000000','#ffffff',72,89,77,80,80,52,75,2,'2024-12-17 13:05:50',NULL),(23,'Kaela','#ffea00','#000000','#ff0000',62,89,76,95,60,67,100,3,'2024-12-17 13:05:50',NULL),(24,'Kanade','#fdff70','#ffffff','#8e0108',80,70,82,84,76,57,60,4,'2024-12-17 13:05:50',NULL),(25,'Aqua','#ff66f2','#0033ff','#70fdff',93,80,91,74,45,56,95,1,'2024-12-17 13:05:50',NULL),(26,'Kanata','#ffffff','#003670','#80f5e7',61,91,82,86,90,30,65,1,'2024-12-17 13:05:50',NULL),(27,'Raora','#ff6bd3','#000000','#b8fffa',76,90,80,78,73,42,60,2,'2024-12-17 13:05:50',NULL),(28,'Nerissa','#0011ff','#030303','#ffffff',80,80,80,85,55,60,90,2,'2024-12-17 13:05:50',NULL),(29,'Baelz','#ff0000','#ffffff','#ffe11f',93,55,81,89,89,32,5,2,'2024-12-17 13:05:50',NULL),(30,'Zeta','#d4d4d4','#ffffff','#000000',81,81,87,81,55,54,60,3,'2024-12-17 13:05:50',NULL),(31,'Hajime','#e1bdff','#ffffff','#8000ff',78,78,78,81,74,50,5,4,'2024-12-17 13:05:50',NULL),(32,'Chihaya','#00803e','#000000','#ffffff',83,83,69,77,66,60,85,4,'2024-12-17 13:05:50',NULL),(33,'Lamy','#33ffe7','#ffffff','#ffdc7a',70,88,76,70,62,63,100,1,'2024-12-17 13:05:50',NULL),(34,'Flare','#ffdd00','#00058a','#ffffff',87,58,82,77,71,54,5,1,'2024-12-17 13:05:50',NULL),(35,'Korone','#b30000','#ffffff','#ffc800',90,55,79,87,81,37,10,1,'2024-12-17 13:05:50',NULL),(36,'Elizabeth','#ff0000','#000000','#6cc0f4',69,82,67,81,83,48,30,2,'2024-12-17 13:05:50',NULL),(37,'Irys','#ff00c8','#ffffff','#000000',92,67,86,83,54,47,10,2,'2024-12-17 13:05:50',NULL),(38,'Moona','#bb00ff','#000000','#fffca8',75,75,75,75,75,55,65,3,'2024-12-17 13:05:50',NULL),(39,'Raden','#2c875c','#000000','#f8e944',79,73,90,83,67,37,15,4,'2024-12-17 13:05:50',NULL),(40,'Niko','#ffd505','#ffffff','#000000',80,63,81,76,84,45,20,4,'2024-12-17 13:05:50',NULL),(41,'Sora','#0040ff','#ffffff','#ffffff',60,87,68,89,75,40,5,1,'2024-12-17 13:05:50',NULL),(42,'Koyori','#f7a6d9','#f7f7f7','#d1fff7',72,75,76,84,57,55,65,1,'2024-12-17 13:05:50',NULL),(43,'Watame','#fdff7a','#ffffff','#6b0000',58,85,82,82,55,57,95,1,'2024-12-17 13:05:50',NULL),(44,'Kiara','#ff8800','#ffffff','#00fad0',70,85,61,91,63,50,25,2,'2024-12-17 13:05:50',NULL),(45,'Shiori','#000000','#ffffff','#dbbceb',89,61,87,79,73,30,5,2,'2024-12-17 13:05:50',NULL),(46,'Mumei','#84531a','#ffffff','#e4ff85',82,59,70,73,89,46,10,2,'2024-12-17 13:05:50',NULL),(47,'Anya','#ce4e09','#153699','#fad000',61,82,82,75,62,57,70,3,'2024-12-17 13:05:50',NULL),(48,'Vivi','#ff66a1','#000000','#42d9ff',80,63,84,80,75,37,5,4,'2024-12-17 13:05:50',NULL),(610,'Team 1','#000000','#ffffff','#ffffff',70,70,70,70,70,35,30,5,'2025-01-08 08:44:08',NULL),(611,'Team 2','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL),(612,'Team 3','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL),(613,'Team 4','#000000','#ffffff','#ffffff',70,70,70,70,70,35,30,5,'2025-01-08 08:44:08',NULL),(614,'Team 5','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL),(615,'Team 6','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL),(616,'Team 7','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL),(617,'Team 8','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL),(618,'Team 9','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL),(619,'Team 10','#000000','#ffffff','#ffffff',70,70,70,70,70,35,40,5,'2025-01-08 08:44:08',NULL),(620,'Team 11','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL),(621,'Team 12','#000000','#ffffff','#ffffff',70,70,70,70,70,35,30,5,'2025-01-08 08:44:08',NULL),(622,'Team 13','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL),(623,'Team 14','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL),(624,'Team 15','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL),(625,'Team 16','#000000','#ffffff','#ffffff',70,70,70,70,70,35,35,5,'2025-01-08 08:44:08',NULL);
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

-- Dump completed on 2025-01-10 15:59:50
