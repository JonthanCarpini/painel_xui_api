-- MySQL dump 10.19  Distrib 10.3.39-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: xui
-- ------------------------------------------------------
-- Server version	10.3.39-MariaDB-0ubuntu0.20.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_codes`
--

DROP TABLE IF EXISTS `access_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `type` tinyint(4) DEFAULT 0,
  `enabled` tinyint(4) DEFAULT 0,
  `groups` mediumtext DEFAULT NULL,
  `whitelist` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blocked_asns`
--

DROP TABLE IF EXISTS `blocked_asns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocked_asns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asn` int(5) DEFAULT 0,
  `isp` varchar(256) DEFAULT NULL,
  `domain` varchar(256) DEFAULT NULL,
  `country` varchar(16) DEFAULT NULL,
  `num_ips` int(16) DEFAULT 0,
  `type` varchar(64) DEFAULT NULL,
  `allocated` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `blocked` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `asn` (`asn`)
) ENGINE=InnoDB AUTO_INCREMENT=68952 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blocked_ips`
--

DROP TABLE IF EXISTS `blocked_ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocked_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(39) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_2` (`ip`),
  UNIQUE KEY `ip_3` (`ip`),
  KEY `ip` (`ip`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blocked_isps`
--

DROP TABLE IF EXISTS `blocked_isps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocked_isps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isp` mediumtext DEFAULT NULL,
  `blocked` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blocked_uas`
--

DROP TABLE IF EXISTS `blocked_uas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocked_uas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_agent` varchar(255) DEFAULT NULL,
  `exact_match` int(11) DEFAULT 0,
  `attempts_blocked` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `exact_match` (`exact_match`),
  KEY `user_agent` (`user_agent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bouquets`
--

DROP TABLE IF EXISTS `bouquets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bouquets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bouquet_name` mediumtext DEFAULT NULL,
  `bouquet_channels` mediumtext DEFAULT NULL,
  `bouquet_movies` mediumtext DEFAULT NULL,
  `bouquet_radios` mediumtext DEFAULT NULL,
  `bouquet_series` mediumtext DEFAULT NULL,
  `bouquet_order` int(16) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crontab`
--

DROP TABLE IF EXISTS `crontab`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crontab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) DEFAULT NULL,
  `time` varchar(128) DEFAULT '* * * * *',
  `enabled` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`),
  KEY `filename` (`filename`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detect_restream`
--

DROP TABLE IF EXISTS `detect_restream`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detect_restream` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) DEFAULT NULL,
  `blocked` tinyint(1) DEFAULT NULL,
  `ports_open` mediumtext DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detect_restream_logs`
--

DROP TABLE IF EXISTS `detect_restream_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detect_restream_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `enigma2_actions`
--

DROP TABLE IF EXISTS `enigma2_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enigma2_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) DEFAULT NULL,
  `type` mediumtext DEFAULT NULL,
  `key` mediumtext DEFAULT NULL,
  `command` mediumtext DEFAULT NULL,
  `command2` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `enigma2_devices`
--

DROP TABLE IF EXISTS `enigma2_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enigma2_devices` (
  `device_id` int(12) NOT NULL AUTO_INCREMENT,
  `mac` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `modem_mac` varchar(255) DEFAULT NULL,
  `local_ip` varchar(255) DEFAULT NULL,
  `public_ip` varchar(255) DEFAULT NULL,
  `key_auth` varchar(255) DEFAULT NULL,
  `enigma_version` varchar(255) DEFAULT NULL,
  `cpu` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `lversion` mediumtext DEFAULT NULL,
  `token` varchar(32) DEFAULT NULL,
  `last_updated` int(11) DEFAULT NULL,
  `watchdog_timeout` int(11) DEFAULT NULL,
  `lock_device` tinyint(4) DEFAULT 0,
  `telnet_enable` tinyint(4) DEFAULT 1,
  `ftp_enable` tinyint(4) DEFAULT 1,
  `ssh_enable` tinyint(4) DEFAULT 1,
  `dns` varchar(255) DEFAULT NULL,
  `original_mac` varchar(255) DEFAULT NULL,
  `rc` tinyint(4) DEFAULT 1,
  `mac_filter` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`device_id`),
  KEY `mac` (`mac`),
  KEY `user_id` (`user_id`),
  FULLTEXT KEY `search` (`mac_filter`,`public_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `epg`
--

DROP TABLE IF EXISTS `epg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `epg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `epg_name` varchar(255) DEFAULT NULL,
  `epg_file` varchar(300) DEFAULT NULL,
  `last_updated` int(11) DEFAULT NULL,
  `days_keep` int(11) DEFAULT 7,
  `data` longtext DEFAULT NULL,
  `offset` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `epg_api`
--

DROP TABLE IF EXISTS `epg_api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `epg_api` (
  `stationId` int(8) NOT NULL,
  `altId` varchar(50) DEFAULT NULL,
  `callSign` varchar(256) DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `bcastLangs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `type` varchar(128) DEFAULT NULL,
  `signalType` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `videoType` varchar(128) DEFAULT NULL,
  `affiliateId` int(11) DEFAULT NULL,
  `affiliateCallSign` varchar(128) DEFAULT NULL,
  `picon` varchar(1024) DEFAULT NULL,
  `eng` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `epg_channels`
--

DROP TABLE IF EXISTS `epg_channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `epg_channels` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `epg_id` int(8) DEFAULT NULL,
  `channel_id` varchar(64) DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `langs` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `epg_data`
--

DROP TABLE IF EXISTS `epg_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `epg_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `epg_id` int(11) DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `lang` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `start` int(11) DEFAULT NULL,
  `end` int(11) DEFAULT NULL,
  `description` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `channel_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `epg_id` (`epg_id`),
  KEY `start` (`start`),
  KEY `end` (`end`),
  KEY `lang` (`lang`),
  KEY `channel_id` (`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `epg_languages`
--

DROP TABLE IF EXISTS `epg_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `epg_languages` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `language` varchar(256) DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `dateadded` timestamp NULL DEFAULT current_timestamp(),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hmac_keys`
--

DROP TABLE IF EXISTS `hmac_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hmac_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(64) DEFAULT NULL,
  `notes` varchar(1024) DEFAULT NULL,
  `enabled` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lines`
--

DROP TABLE IF EXISTS `lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `last_ip` varchar(255) DEFAULT NULL,
  `exp_date` int(11) DEFAULT NULL,
  `admin_enabled` int(11) DEFAULT 1,
  `enabled` int(11) DEFAULT 1,
  `admin_notes` mediumtext DEFAULT NULL,
  `reseller_notes` mediumtext DEFAULT NULL,
  `bouquet` mediumtext DEFAULT NULL,
  `allowed_outputs` mediumtext DEFAULT NULL,
  `max_connections` int(11) DEFAULT 1,
  `is_restreamer` tinyint(4) DEFAULT 0,
  `is_trial` tinyint(4) DEFAULT 0,
  `is_mag` tinyint(4) DEFAULT 0,
  `is_e2` tinyint(4) DEFAULT 0,
  `is_stalker` tinyint(4) DEFAULT 0,
  `is_isplock` tinyint(4) DEFAULT 0,
  `allowed_ips` mediumtext DEFAULT NULL,
  `allowed_ua` mediumtext DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `pair_id` int(11) DEFAULT NULL,
  `force_server_id` int(11) DEFAULT 0,
  `as_number` varchar(30) DEFAULT NULL,
  `isp_desc` mediumtext DEFAULT NULL,
  `forced_country` varchar(3) DEFAULT NULL,
  `bypass_ua` tinyint(4) DEFAULT 0,
  `play_token` mediumtext DEFAULT NULL,
  `last_expiration_video` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `access_token` varchar(32) DEFAULT NULL,
  `contact` mediumtext DEFAULT NULL,
  `last_activity` int(11) DEFAULT NULL,
  `last_activity_array` mediumtext DEFAULT NULL,
  `updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `exp_date` (`exp_date`),
  KEY `is_restreamer` (`is_restreamer`),
  KEY `admin_enabled` (`admin_enabled`),
  KEY `enabled` (`enabled`),
  KEY `is_trial` (`is_trial`),
  KEY `created_at` (`created_at`),
  KEY `pair_id` (`pair_id`),
  KEY `is_mag` (`is_mag`),
  KEY `username` (`username`),
  KEY `password` (`password`),
  KEY `is_e2` (`is_e2`),
  KEY `order_default` (`id`,`is_mag`,`is_e2`),
  FULLTEXT KEY `search` (`username`,`admin_notes`,`reseller_notes`,`last_ip`,`contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lines_activity`
--

DROP TABLE IF EXISTS `lines_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lines_activity` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `user_ip` varchar(39) DEFAULT NULL,
  `container` varchar(50) DEFAULT NULL,
  `date_start` int(11) DEFAULT NULL,
  `date_end` int(11) DEFAULT NULL,
  `geoip_country_code` varchar(22) DEFAULT NULL,
  `isp` varchar(255) DEFAULT NULL,
  `external_device` varchar(255) DEFAULT NULL,
  `divergence` float DEFAULT 0,
  `hmac_id` tinyint(4) DEFAULT NULL,
  `hmac_identifier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`activity_id`),
  KEY `user_id` (`user_id`),
  KEY `stream_id` (`stream_id`),
  KEY `server_id` (`server_id`),
  KEY `date_end` (`date_end`),
  KEY `container` (`container`),
  KEY `geoip_country_code` (`geoip_country_code`),
  KEY `date_start` (`date_start`),
  KEY `date_start_2` (`date_start`,`date_end`),
  KEY `user_ip` (`user_ip`),
  KEY `user_agent` (`user_agent`),
  KEY `isp` (`isp`),
  KEY `parent_id` (`proxy_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lines_divergence`
--

DROP TABLE IF EXISTS `lines_divergence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lines_divergence` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(32) DEFAULT NULL,
  `divergence` float DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uuid` (`uuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lines_live`
--

DROP TABLE IF EXISTS `lines_live`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lines_live` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `proxy_id` int(11) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `user_ip` varchar(39) DEFAULT NULL,
  `container` varchar(50) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `active_pid` int(11) DEFAULT NULL,
  `date_start` int(11) DEFAULT NULL,
  `date_end` int(11) DEFAULT NULL,
  `geoip_country_code` varchar(22) DEFAULT NULL,
  `isp` varchar(255) DEFAULT NULL,
  `external_device` varchar(255) DEFAULT NULL,
  `divergence` float DEFAULT 0,
  `hls_last_read` int(11) DEFAULT NULL,
  `hls_end` tinyint(4) DEFAULT 0,
  `fingerprinting` tinyint(4) DEFAULT 0,
  `hmac_id` tinyint(4) DEFAULT NULL,
  `hmac_identifier` varchar(255) DEFAULT NULL,
  `uuid` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`activity_id`),
  KEY `user_agent` (`user_agent`),
  KEY `user_ip` (`user_ip`),
  KEY `container` (`container`),
  KEY `pid` (`pid`),
  KEY `active_pid` (`active_pid`),
  KEY `geoip_country_code` (`geoip_country_code`),
  KEY `user_id` (`user_id`),
  KEY `stream_id` (`stream_id`),
  KEY `server_id` (`server_id`),
  KEY `date_start` (`date_start`),
  KEY `date_end` (`date_end`),
  KEY `hls_end` (`hls_end`),
  KEY `parent_id` (`proxy_id`) USING BTREE,
  KEY `fingerprinting` (`fingerprinting`),
  KEY `hmac_id` (`hmac_id`),
  KEY `hmac_identifier` (`hmac_identifier`),
  KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lines_logs`
--

DROP TABLE IF EXISTS `lines_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lines_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `client_status` varchar(255) DEFAULT NULL,
  `query_string` mediumtext DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `extra_data` mediumtext DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `login_logs`
--

DROP TABLE IF EXISTS `login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `access_code` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `login_ip` varchar(255) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mag_claims`
--

DROP TABLE IF EXISTS `mag_claims`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mag_claims` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mag_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `real_type` varchar(10) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mag_id` (`mag_id`),
  KEY `stream_id` (`stream_id`),
  KEY `real_type` (`real_type`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mag_devices`
--

DROP TABLE IF EXISTS `mag_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mag_devices` (
  `mag_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `bright` int(10) DEFAULT 200,
  `contrast` int(10) DEFAULT 127,
  `saturation` int(10) DEFAULT 127,
  `aspect` mediumtext DEFAULT NULL,
  `video_out` varchar(20) DEFAULT 'rca',
  `volume` int(5) DEFAULT 50,
  `playback_buffer_bytes` int(50) DEFAULT 0,
  `playback_buffer_size` int(50) DEFAULT 0,
  `audio_out` int(5) DEFAULT 1,
  `mac` varchar(50) DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `ls` varchar(20) DEFAULT NULL,
  `ver` varchar(300) DEFAULT NULL,
  `lang` varchar(50) DEFAULT NULL,
  `locale` varchar(30) DEFAULT 'en_GB.utf8',
  `city_id` int(11) DEFAULT 0,
  `hd` int(10) DEFAULT 1,
  `main_notify` int(5) DEFAULT 1,
  `fav_itv_on` int(5) DEFAULT 0,
  `now_playing_start` int(50) DEFAULT NULL,
  `now_playing_type` int(11) DEFAULT 0,
  `now_playing_content` varchar(50) DEFAULT NULL,
  `time_last_play_tv` int(50) DEFAULT NULL,
  `time_last_play_video` int(50) DEFAULT NULL,
  `hd_content` int(11) DEFAULT 1,
  `image_version` varchar(350) DEFAULT NULL,
  `last_change_status` int(11) DEFAULT NULL,
  `last_start` int(11) DEFAULT NULL,
  `last_active` int(11) DEFAULT NULL,
  `keep_alive` int(11) DEFAULT NULL,
  `playback_limit` int(11) DEFAULT 3,
  `screensaver_delay` int(11) DEFAULT 10,
  `stb_type` varchar(20) DEFAULT NULL,
  `sn` varchar(255) DEFAULT NULL,
  `last_watchdog` int(50) DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  `country` varchar(5) DEFAULT NULL,
  `plasma_saving` int(11) DEFAULT 0,
  `ts_enabled` int(11) DEFAULT 0,
  `ts_enable_icon` int(11) DEFAULT 1,
  `ts_path` varchar(35) DEFAULT NULL,
  `ts_max_length` int(11) DEFAULT 3600,
  `ts_buffer_use` varchar(15) DEFAULT 'cyclic',
  `ts_action_on_exit` varchar(20) DEFAULT 'no_save',
  `ts_delay` varchar(20) DEFAULT 'on_pause',
  `video_clock` varchar(10) DEFAULT 'Off',
  `rtsp_type` int(11) DEFAULT 4,
  `rtsp_flags` int(11) DEFAULT 0,
  `stb_lang` varchar(15) DEFAULT 'en',
  `display_menu_after_loading` int(11) DEFAULT 1,
  `record_max_length` int(11) DEFAULT 180,
  `plasma_saving_timeout` int(11) DEFAULT 600,
  `now_playing_link_id` int(11) DEFAULT NULL,
  `now_playing_streamer_id` int(11) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `device_id2` varchar(255) DEFAULT NULL,
  `hw_version` varchar(255) DEFAULT NULL,
  `parent_password` varchar(20) DEFAULT '0000',
  `spdif_mode` int(11) DEFAULT 1,
  `show_after_loading` varchar(60) DEFAULT 'main_menu',
  `play_in_preview_by_ok` int(11) DEFAULT 1,
  `hdmi_event_reaction` int(11) DEFAULT 1,
  `mag_player` varchar(20) DEFAULT 'ffmpeg',
  `play_in_preview_only_by_ok` varchar(10) DEFAULT 'true',
  `watchdog_timeout` int(11) DEFAULT NULL,
  `fav_channels` mediumtext DEFAULT NULL,
  `tv_archive_continued` mediumtext DEFAULT NULL,
  `tv_channel_default_aspect` varchar(255) DEFAULT 'fit',
  `last_itv_id` int(11) DEFAULT 0,
  `units` varchar(20) DEFAULT 'metric',
  `token` varchar(32) DEFAULT NULL,
  `lock_device` tinyint(4) DEFAULT 0,
  `theme_type` tinyint(1) DEFAULT 0,
  `mac_filter` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`mag_id`),
  KEY `user_id` (`user_id`),
  KEY `mac` (`mac`),
  FULLTEXT KEY `search` (`mac_filter`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mag_events`
--

DROP TABLE IF EXISTS `mag_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mag_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(3) DEFAULT 0,
  `mag_device_id` int(11) DEFAULT NULL,
  `event` varchar(20) DEFAULT NULL,
  `need_confirm` tinyint(3) DEFAULT 0,
  `msg` mediumtext DEFAULT NULL,
  `reboot_after_ok` tinyint(3) DEFAULT 0,
  `auto_hide_timeout` tinyint(3) DEFAULT 0,
  `send_time` int(50) DEFAULT NULL,
  `additional_services_on` tinyint(3) DEFAULT 1,
  `anec` tinyint(3) DEFAULT 0,
  `vclub` tinyint(3) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `mag_device_id` (`mag_device_id`),
  KEY `event` (`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mag_logs`
--

DROP TABLE IF EXISTS `mag_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mag_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mag_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mag_id` (`mag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mysql_syslog`
--

DROP TABLE IF EXISTS `mysql_syslog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysql_syslog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `error` longtext DEFAULT NULL,
  `username` varchar(64) DEFAULT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `database` varchar(64) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `server_id` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ondemand_check`
--

DROP TABLE IF EXISTS `ondemand_check`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ondemand_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `source_id` int(4) DEFAULT NULL,
  `source_url` varchar(1024) DEFAULT NULL,
  `video_codec` varchar(50) DEFAULT NULL,
  `audio_codec` varchar(50) DEFAULT NULL,
  `resolution` varchar(50) DEFAULT NULL,
  `response` int(11) DEFAULT NULL,
  `fps` int(11) DEFAULT NULL,
  `errors` mediumtext DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `output_devices`
--

DROP TABLE IF EXISTS `output_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `output_devices` (
  `device_id` int(11) NOT NULL AUTO_INCREMENT,
  `device_name` varchar(255) DEFAULT NULL,
  `device_key` varchar(255) DEFAULT NULL,
  `device_filename` varchar(255) DEFAULT NULL,
  `device_header` mediumtext DEFAULT NULL,
  `device_conf` mediumtext DEFAULT NULL,
  `device_footer` mediumtext DEFAULT NULL,
  `default_output` int(11) DEFAULT 0,
  `copy_text` mediumtext DEFAULT NULL,
  PRIMARY KEY (`device_id`),
  KEY `device_key` (`device_key`),
  KEY `default_output` (`default_output`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `output_formats`
--

DROP TABLE IF EXISTS `output_formats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `output_formats` (
  `access_output_id` int(11) NOT NULL AUTO_INCREMENT,
  `output_name` varchar(255) DEFAULT NULL,
  `output_key` varchar(255) DEFAULT NULL,
  `output_ext` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`access_output_id`),
  KEY `output_key` (`output_key`),
  KEY `output_ext` (`output_ext`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `panel_logs`
--

DROP TABLE IF EXISTS `panel_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `panel_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT 'pdo',
  `log_message` longtext DEFAULT NULL,
  `log_extra` longtext DEFAULT NULL,
  `line` int(11) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `unique` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `panel_stats`
--

DROP TABLE IF EXISTS `panel_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `panel_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(16) DEFAULT NULL,
  `time` int(16) DEFAULT 0,
  `count` float DEFAULT 0,
  `server_id` int(16) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `profiles`
--

DROP TABLE IF EXISTS `profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profiles` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_name` varchar(255) DEFAULT NULL,
  `profile_options` mediumtext DEFAULT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `providers`
--

DROP TABLE IF EXISTS `providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `providers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `ip` varchar(128) DEFAULT NULL,
  `port` int(5) DEFAULT 80,
  `username` varchar(128) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `data` mediumtext DEFAULT NULL,
  `last_changed` int(11) DEFAULT NULL,
  `legacy` tinyint(1) DEFAULT 0,
  `enabled` tinyint(1) DEFAULT 1,
  `status` tinyint(1) DEFAULT 0,
  `ssl` tinyint(1) DEFAULT 0,
  `hls` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `providers_streams`
--

DROP TABLE IF EXISTS `providers_streams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `providers_streams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `category_id` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `category_array` mediumtext DEFAULT NULL,
  `stream_display_name` mediumtext DEFAULT NULL,
  `stream_icon` mediumtext DEFAULT NULL,
  `channel_id` varchar(255) DEFAULT NULL,
  `added` int(11) DEFAULT NULL,
  `modified` int(11) DEFAULT NULL,
  `type` varchar(16) DEFAULT 'live',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `channel_id` (`channel_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `added` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recordings`
--

DROP TABLE IF EXISTS `recordings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recordings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) DEFAULT NULL,
  `created_id` int(11) DEFAULT NULL,
  `category_id` longtext DEFAULT NULL,
  `bouquets` longtext DEFAULT NULL,
  `title` mediumtext DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `stream_icon` mediumtext DEFAULT NULL,
  `start` int(11) DEFAULT NULL,
  `end` int(11) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `archive` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rtmp_ips`
--

DROP TABLE IF EXISTS `rtmp_ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rtmp_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `push` tinyint(1) DEFAULT NULL,
  `pull` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servers`
--

DROP TABLE IF EXISTS `servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_type` int(1) DEFAULT 0,
  `xui_version` varchar(50) DEFAULT NULL,
  `server_name` varchar(255) DEFAULT NULL,
  `domain_name` mediumtext DEFAULT NULL,
  `server_ip` varchar(255) DEFAULT NULL,
  `private_ip` varchar(255) DEFAULT NULL,
  `is_main` int(16) DEFAULT 0,
  `enabled` int(16) DEFAULT 1,
  `parent_id` text DEFAULT NULL,
  `http_broadcast_port` int(11) DEFAULT 80,
  `https_broadcast_port` int(11) DEFAULT 443,
  `http_ports_add` mediumtext DEFAULT NULL,
  `https_ports_add` mediumtext DEFAULT NULL,
  `total_clients` int(11) DEFAULT 250,
  `network_interface` varchar(255) DEFAULT 'eth0',
  `status` tinyint(4) DEFAULT -1,
  `enable_geoip` int(11) DEFAULT 0,
  `geoip_countries` mediumtext DEFAULT NULL,
  `last_check_ago` int(11) DEFAULT 0,
  `server_hardware` mediumtext DEFAULT NULL,
  `total_services` int(11) DEFAULT 3,
  `persistent_connections` tinyint(4) DEFAULT 0,
  `rtmp_port` int(11) DEFAULT 8880,
  `geoip_type` varchar(13) DEFAULT 'low_priority',
  `isp_names` mediumtext DEFAULT NULL,
  `isp_type` varchar(13) DEFAULT 'low_priority',
  `enable_isp` tinyint(4) DEFAULT 0,
  `network_guaranteed_speed` int(11) DEFAULT 1000,
  `timeshift_only` tinyint(4) DEFAULT 0,
  `whitelist_ips` mediumtext DEFAULT NULL,
  `watchdog_data` mediumtext DEFAULT NULL,
  `video_devices` mediumtext DEFAULT NULL,
  `audio_devices` mediumtext DEFAULT NULL,
  `gpu_info` mediumtext DEFAULT NULL,
  `interfaces` mediumtext DEFAULT NULL,
  `random_ip` tinyint(4) DEFAULT 0,
  `enable_proxy` tinyint(4) DEFAULT 0,
  `enable_https` tinyint(4) DEFAULT 0,
  `certbot_renew` tinyint(4) DEFAULT 0,
  `certbot_ssl` mediumtext DEFAULT NULL,
  `uuid` varchar(256) DEFAULT NULL,
  `use_disk` tinyint(1) DEFAULT 0,
  `last_status` tinyint(4) DEFAULT 1,
  `time_offset` int(11) DEFAULT 0,
  `ping` int(11) DEFAULT 0,
  `requests_per_second` int(11) DEFAULT 0,
  `xui_revision` int(2) DEFAULT NULL,
  `php_version` int(2) DEFAULT 74,
  `php_pids` longtext DEFAULT NULL,
  `connections` int(16) DEFAULT 0,
  `users` int(16) DEFAULT 0,
  `remote_status` tinyint(1) DEFAULT 1,
  `governors` mediumtext DEFAULT NULL,
  `governor` varchar(512) DEFAULT NULL,
  `sysctl` mediumtext DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `enable_gzip` tinyint(1) DEFAULT 0,
  `limit_requests` int(11) DEFAULT 0,
  `limit_burst` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `total_clients` (`total_clients`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servers_stats`
--

DROP TABLE IF EXISTS `servers_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servers_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) DEFAULT 0,
  `connections` int(11) DEFAULT 0,
  `streams` int(11) DEFAULT 0,
  `users` int(11) DEFAULT 0,
  `cpu` float DEFAULT 0,
  `cpu_cores` int(11) DEFAULT 0,
  `cpu_avg` float DEFAULT 0,
  `total_mem` int(11) DEFAULT 0,
  `total_mem_free` int(11) DEFAULT 0,
  `total_mem_used` int(11) DEFAULT 0,
  `total_mem_used_percent` float DEFAULT 0,
  `total_disk_space` bigint(20) DEFAULT 0,
  `uptime` varchar(255) DEFAULT NULL,
  `total_running_streams` int(11) DEFAULT 0,
  `bytes_sent` bigint(20) DEFAULT 0,
  `bytes_received` bigint(20) DEFAULT 0,
  `bytes_sent_total` bigint(128) DEFAULT 0,
  `bytes_received_total` bigint(128) DEFAULT 0,
  `cpu_load_average` float DEFAULT 0,
  `gpu_info` mediumtext DEFAULT NULL,
  `iostat_info` mediumtext DEFAULT NULL,
  `time` int(16) DEFAULT 0,
  `total_users` int(11) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `server_name` mediumtext DEFAULT NULL,
  `default_timezone` varchar(255) DEFAULT 'Europe/London',
  `allowed_stb_types` mediumtext DEFAULT NULL,
  `client_prebuffer` int(11) DEFAULT 30,
  `split_clients` varchar(255) DEFAULT 'equal',
  `stream_max_analyze` int(11) DEFAULT 5000000,
  `show_not_on_air_video` tinyint(4) DEFAULT 1,
  `not_on_air_video_path` mediumtext DEFAULT NULL,
  `show_banned_video` tinyint(4) DEFAULT 1,
  `banned_video_path` mediumtext DEFAULT NULL,
  `show_expired_video` tinyint(4) DEFAULT 1,
  `expired_video_path` mediumtext DEFAULT NULL,
  `show_expiring_video` tinyint(4) DEFAULT 1,
  `expiring_video_path` mediumtext DEFAULT NULL,
  `mag_container` varchar(255) DEFAULT 'ts',
  `api_container` varchar(255) DEFAULT 'ts',
  `probesize` int(11) DEFAULT 5000000,
  `allowed_ips_admin` mediumtext DEFAULT NULL,
  `block_svp` tinyint(4) DEFAULT 1,
  `allow_countries` mediumtext DEFAULT NULL,
  `user_auto_kick_hours` int(11) DEFAULT 4,
  `disallow_empty_user_agents` tinyint(4) DEFAULT 1,
  `show_all_category_mag` tinyint(4) DEFAULT 1,
  `flood_limit` int(11) DEFAULT 40,
  `flood_ips_exclude` mediumtext DEFAULT NULL,
  `flood_seconds` int(11) DEFAULT 2,
  `vod_bitrate_plus` int(11) DEFAULT 60,
  `read_buffer_size` int(11) DEFAULT 8192,
  `seg_time` int(3) DEFAULT 6,
  `seg_list_size` int(3) DEFAULT 6,
  `tv_channel_default_aspect` varchar(255) DEFAULT 'fit',
  `playback_limit` int(11) DEFAULT 4,
  `show_tv_channel_logo` tinyint(4) DEFAULT 1,
  `show_channel_logo_in_preview` tinyint(4) DEFAULT 1,
  `enable_connection_problem_indication` tinyint(4) DEFAULT 1,
  `vod_limit_perc` int(11) DEFAULT 150,
  `allowed_stb_types_for_local_recording` mediumtext DEFAULT NULL,
  `stalker_theme` varchar(255) DEFAULT 'digital',
  `rtmp_random` tinyint(4) DEFAULT 1,
  `api_ips` mediumtext DEFAULT NULL,
  `use_buffer` tinyint(4) DEFAULT 0,
  `restreamer_prebuffer` tinyint(4) DEFAULT 0,
  `audio_restart_loss` tinyint(4) DEFAULT 0,
  `stalker_lock_images` mediumtext DEFAULT NULL,
  `channel_number_type` varchar(25) DEFAULT 'bouquet',
  `stb_change_pass` tinyint(4) DEFAULT 0,
  `enable_debug_stalker` tinyint(4) DEFAULT 0,
  `online_capacity_interval` smallint(6) DEFAULT 10,
  `always_enabled_subtitles` tinyint(4) DEFAULT 1,
  `test_download_url` varchar(255) DEFAULT 'https://speed.hetzner.de/100MB.bin',
  `api_pass` varchar(255) DEFAULT NULL,
  `message_of_day` mediumtext DEFAULT NULL,
  `enable_isp_lock` tinyint(4) DEFAULT 1,
  `show_isps` tinyint(4) DEFAULT 1,
  `save_closed_connection` tinyint(4) DEFAULT 1,
  `client_logs_save` tinyint(4) DEFAULT 1,
  `case_sensitive_line` tinyint(4) DEFAULT 1,
  `county_override_1st` tinyint(4) DEFAULT 0,
  `disallow_2nd_ip_con` tinyint(4) DEFAULT 0,
  `split_by` varchar(255) DEFAULT 'con',
  `use_mdomain_in_lists` tinyint(4) DEFAULT 1,
  `priority_backup` tinyint(4) DEFAULT 1,
  `tmdb_api_key` mediumtext DEFAULT NULL,
  `mag_security` tinyint(4) DEFAULT 1,
  `hls_accelerator` tinyint(1) DEFAULT 1,
  `backups_pid` int(5) DEFAULT 0,
  `backups_to_keep` int(5) DEFAULT 0,
  `cc_time` int(10) DEFAULT 0,
  `dashboard_stats` tinyint(1) DEFAULT 1,
  `default_entries` int(5) DEFAULT 50,
  `disable_trial` tinyint(1) DEFAULT 0,
  `download_images` tinyint(1) DEFAULT 1,
  `dropbox_keep` tinyint(1) DEFAULT 0,
  `dropbox_remote` tinyint(1) DEFAULT 0,
  `ip_logout` tinyint(1) DEFAULT 1,
  `last_backup` int(10) DEFAULT 0,
  `login_flood` tinyint(1) DEFAULT 15,
  `recaptcha_enable` tinyint(1) DEFAULT 0,
  `reseller_restrictions` tinyint(1) DEFAULT 0,
  `stats_pid` int(5) DEFAULT 0,
  `tmdb_pid` int(5) DEFAULT 0,
  `watch_pid` int(5) DEFAULT 0,
  `automatic_backups` varchar(16) DEFAULT 'off',
  `dropbox_token` varchar(256) DEFAULT '',
  `recaptcha_v2_secret_key` varchar(256) DEFAULT '',
  `recaptcha_v2_site_key` varchar(256) DEFAULT '',
  `tmdb_language` varchar(16) DEFAULT '',
  `release_parser` varchar(16) DEFAULT 'python',
  `language` varchar(16) DEFAULT 'en',
  `encrypt_hls` tinyint(1) DEFAULT 0,
  `disable_ts` tinyint(1) DEFAULT 0,
  `disable_ts_allow_restream` tinyint(1) DEFAULT 0,
  `disable_hls` tinyint(1) DEFAULT 0,
  `disable_hls_allow_restream` tinyint(1) DEFAULT 0,
  `disable_rtmp` tinyint(1) DEFAULT 1,
  `disable_rtmp_allow_restream` tinyint(1) DEFAULT 0,
  `date_format` varchar(16) DEFAULT 'Y-m-d',
  `datetime_format` varchar(16) DEFAULT 'Y-m-d H:i:s',
  `enable_epg_api` tinyint(1) DEFAULT 1,
  `epg_api_days_fetch` tinyint(4) DEFAULT 7,
  `epg_api_days_keep` tinyint(4) DEFAULT 7,
  `epg_api_extended` tinyint(4) DEFAULT 0,
  `streams_grouped` tinyint(1) DEFAULT 1,
  `disable_player_api` tinyint(1) DEFAULT 0,
  `disable_playlist` tinyint(1) DEFAULT 0,
  `disable_xmltv` tinyint(1) DEFAULT 0,
  `disable_enigma2` tinyint(1) DEFAULT 1,
  `disable_ministra` tinyint(1) DEFAULT 0,
  `api_redirect` tinyint(1) DEFAULT 0,
  `movie_year_append` tinyint(1) DEFAULT 0,
  `log_clear` int(11) DEFAULT 31,
  `cleanup` tinyint(1) DEFAULT 1,
  `fingerprint_max` int(11) DEFAULT 25,
  `bruteforce_mac_attempts` tinyint(4) DEFAULT 5,
  `bruteforce_username_attempts` tinyint(4) DEFAULT 10,
  `bruteforce_frequency` int(11) DEFAULT 300,
  `auto_update_lbs` tinyint(4) DEFAULT 1,
  `update_version` varchar(50) DEFAULT NULL,
  `dashboard_display_alt` tinyint(4) DEFAULT 1,
  `dashboard_map` tinyint(4) DEFAULT 1,
  `cpu_limit` tinyint(4) DEFAULT 0,
  `mem_limit` int(11) DEFAULT 0,
  `detect_restream_servers` tinyint(1) DEFAULT 1,
  `detect_restream_block_user` tinyint(1) DEFAULT 0,
  `detect_restream_block_ip` tinyint(1) DEFAULT 0,
  `detect_restream_ports` varchar(1024) DEFAULT '[25461,25550,31210]',
  `cloudflare` tinyint(1) DEFAULT 0,
  `js_navigate` tinyint(1) DEFAULT 1,
  `encrypt_playlist` tinyint(1) DEFAULT 1,
  `encrypt_playlist_restreamer` tinyint(1) DEFAULT 1,
  `stream_logs_save` tinyint(1) DEFAULT 1,
  `restrict_same_ip` tinyint(1) DEFAULT 1,
  `show_tickets` tinyint(1) DEFAULT 1,
  `percentage_match` tinyint(1) DEFAULT 80,
  `thread_count` int(11) DEFAULT 4,
  `scan_seconds` int(11) DEFAULT 86400,
  `verify_host` tinyint(4) DEFAULT 1,
  `max_genres` tinyint(4) DEFAULT 3,
  `legacy_get` tinyint(4) DEFAULT 0,
  `legacy_xmltv` tinyint(4) DEFAULT 0,
  `mag_disable_ssl` tinyint(4) DEFAULT 0,
  `hide_failures` tinyint(4) DEFAULT 0,
  `legacy_panel_api` tinyint(4) DEFAULT 0,
  `connection_loop_per` tinyint(4) DEFAULT 0,
  `connection_loop_count` tinyint(4) DEFAULT 0,
  `api_probe` tinyint(4) DEFAULT 1,
  `max_simultaneous_downloads` tinyint(4) DEFAULT 2,
  `restart_php_fpm` tinyint(4) DEFAULT 1,
  `cache_playlists` int(11) DEFAULT 0,
  `send_xui_header` int(11) DEFAULT 1,
  `request_prebuffer` int(11) DEFAULT 1,
  `debug_show_errors` tinyint(4) DEFAULT 0,
  `block_streaming_servers` tinyint(4) DEFAULT 0,
  `block_proxies` tinyint(4) DEFAULT 0,
  `ip_subnet_match` tinyint(4) DEFAULT 0,
  `last_cache` int(11) DEFAULT 0,
  `last_cache_taken` int(11) DEFAULT 0,
  `ministra_allow_blank` int(11) DEFAULT 0,
  `enable_cache` tinyint(4) DEFAULT 1,
  `legacy_mag_auth` tinyint(4) DEFAULT 0,
  `ignore_invalid_users` tinyint(4) DEFAULT 0,
  `on_demand_instant_off` tinyint(4) DEFAULT 0,
  `on_demand_failure_exit` tinyint(4) DEFAULT 0,
  `on_demand_wait_time` tinyint(4) DEFAULT 20,
  `playlist_from_mysql` tinyint(4) DEFAULT 0,
  `show_images` tinyint(4) DEFAULT 1,
  `kill_rogue_ffmpeg` tinyint(4) DEFAULT 1,
  `monitor_connection_status` tinyint(4) DEFAULT 1,
  `stream_fail_sleep` tinyint(4) DEFAULT 10,
  `custom_ip_header` varchar(256) DEFAULT '',
  `send_protection_headers` tinyint(4) DEFAULT 0,
  `send_altsvc_header` tinyint(4) DEFAULT 0,
  `send_server_header` varchar(256) DEFAULT '',
  `send_unique_header` varchar(256) DEFAULT '',
  `send_unique_header_domain` varchar(256) DEFAULT '',
  `max_items` int(11) DEFAULT 0,
  `restrict_playlists` tinyint(4) DEFAULT 1,
  `mag_legacy_redirect` tinyint(4) DEFAULT 0,
  `save_login_logs` tinyint(4) DEFAULT 1,
  `save_restart_logs` tinyint(4) DEFAULT 1,
  `keep_activity` int(11) DEFAULT 0,
  `keep_client` int(11) DEFAULT 0,
  `keep_login` int(11) DEFAULT 0,
  `keep_errors` int(11) DEFAULT 0,
  `keep_restarts` int(11) DEFAULT 0,
  `ignore_keyframes` int(11) DEFAULT 0,
  `seg_delete_threshold` int(11) DEFAULT 4,
  `fails_per_time` int(11) DEFAULT 86400,
  `segment_type` tinyint(1) DEFAULT 1,
  `thread_count_movie` tinyint(3) DEFAULT 5,
  `thread_count_show` tinyint(3) DEFAULT 1,
  `redirect_timeout` tinyint(1) DEFAULT 5,
  `create_expiration` tinyint(3) DEFAULT 5,
  `redis_handler` tinyint(1) DEFAULT 0,
  `redis_password` varchar(512) DEFAULT '',
  `force_epg_timezone` tinyint(1) DEFAULT 0,
  `check_vod` tinyint(1) DEFAULT 0,
  `max_encode_movies` int(11) DEFAULT 10,
  `max_encode_cc` int(11) DEFAULT 1,
  `queue_loop` int(11) DEFAULT 1,
  `cache_thread_count` int(4) DEFAULT 4,
  `cache_changes` tinyint(1) DEFAULT 1,
  `player_blur` tinyint(1) DEFAULT 0,
  `player_opacity` tinyint(1) DEFAULT 10,
  `player_allow_playlist` tinyint(1) DEFAULT 1,
  `player_allow_bouquet` tinyint(1) DEFAULT 1,
  `player_hide_incompatible` tinyint(1) DEFAULT 0,
  `player_allow_hevc` tinyint(1) DEFAULT 0,
  `read_native_hls` tinyint(1) DEFAULT 1,
  `keep_protocol` tinyint(1) DEFAULT 0,
  `ffmpeg_cpu` varchar(8) DEFAULT '4.0',
  `ffmpeg_gpu` varchar(8) DEFAULT '4.0',
  `header_stats` tinyint(1) DEFAULT 1,
  `mag_keep_extension` tinyint(1) DEFAULT 1,
  `show_connected_video` tinyint(1) DEFAULT 1,
  `connected_video_path` mediumtext DEFAULT NULL,
  `disallow_2nd_ip_max` int(11) DEFAULT 1,
  `probesize_ondemand` int(11) DEFAULT 256000,
  `ffmpeg_warnings` int(11) DEFAULT 1,
  `vod_sort_newest` tinyint(1) DEFAULT 0,
  `mag_message` mediumtext DEFAULT NULL,
  `mag_default_type` tinyint(1) DEFAULT 0,
  `fps_delay` int(11) DEFAULT 600,
  `fps_check_type` tinyint(1) DEFAULT 1,
  `show_category_duplicates` tinyint(1) DEFAULT 0,
  `probe_extra_wait` int(11) DEFAULT 10,
  `restream_deny_unauthorised` tinyint(1) DEFAULT 1,
  `extract_subtitles` tinyint(1) DEFAULT 0,
  `reseller_ssl_domain` tinyint(1) DEFAULT 1,
  `auto_send_logs` tinyint(1) DEFAULT 0,
  `disable_xmltv_restreamer` tinyint(1) DEFAULT 0,
  `disable_playlist_restreamer` tinyint(1) DEFAULT 0,
  `mag_load_all_channels` tinyint(1) DEFAULT 0,
  `connection_sync_timer` int(11) DEFAULT 1,
  `segment_wait_time` int(11) DEFAULT 20,
  `total_users` int(11) DEFAULT 0,
  `dts_legacy_ffmpeg` tinyint(1) DEFAULT 0,
  `allow_cdn_access` tinyint(1) DEFAULT 0,
  `disable_mag_token` tinyint(1) DEFAULT 0,
  `ondemand_balance_equal` tinyint(1) DEFAULT 0,
  `update_data` mediumtext DEFAULT NULL,
  `dashboard_status` tinyint(1) DEFAULT 1,
  `on_demand_checker` tinyint(1) DEFAULT 0,
  `on_demand_scan_time` int(16) DEFAULT 3600,
  `on_demand_max_probe` int(16) DEFAULT 5,
  `on_demand_scan_keep` int(16) DEFAULT 604800,
  `parse_type` varchar(12) DEFAULT 'ptn',
  `fallback_parser` tinyint(1) DEFAULT 0,
  `alternative_titles` tinyint(1) DEFAULT 0,
  `search_items` int(3) DEFAULT 20,
  `enable_search` tinyint(4) DEFAULT 1,
  `modal_edit` tinyint(1) DEFAULT 1,
  `group_buttons` tinyint(1) DEFAULT 1,
  `license` varchar(32) DEFAULT NULL,
  `stop_failures` tinyint(3) DEFAULT 3,
  `restreamer_bypass_proxy` tinyint(1) DEFAULT 0,
  `mysql_sleep_kill` int(11) DEFAULT 21600,
  `reissues` mediumtext DEFAULT NULL,
  `status_uuid` varchar(512) DEFAULT NULL,
  `threshold_cpu` tinyint(3) DEFAULT 67,
  `threshold_mem` tinyint(3) DEFAULT 67,
  `threshold_disk` tinyint(3) DEFAULT 67,
  `threshold_network` tinyint(3) DEFAULT 67,
  `threshold_clients` tinyint(3) DEFAULT 67,
  `auth_flood_seconds` int(11) DEFAULT 10,
  `auth_flood_limit` int(11) DEFAULT 30,
  `auth_flood_sleep` int(11) DEFAULT 1,
  `php_loopback` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `signals`
--

DROP TABLE IF EXISTS `signals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `signals` (
  `signal_id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `rtmp` tinyint(4) DEFAULT 0,
  `time` int(11) DEFAULT NULL,
  `custom_data` mediumtext DEFAULT NULL,
  `cache` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`signal_id`),
  KEY `server_id` (`server_id`),
  KEY `time` (`time`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streams`
--

DROP TABLE IF EXISTS `streams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) DEFAULT NULL,
  `category_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `stream_display_name` mediumtext DEFAULT NULL,
  `stream_source` mediumtext DEFAULT NULL,
  `stream_icon` mediumtext DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `enable_transcode` tinyint(4) DEFAULT 0,
  `transcode_attributes` mediumtext DEFAULT NULL,
  `custom_ffmpeg` mediumtext DEFAULT NULL,
  `movie_properties` mediumtext DEFAULT NULL,
  `movie_subtitles` mediumtext DEFAULT NULL,
  `read_native` tinyint(4) DEFAULT 1,
  `target_container` text DEFAULT NULL,
  `stream_all` tinyint(4) DEFAULT 0,
  `remove_subtitles` tinyint(4) DEFAULT 0,
  `custom_sid` varchar(150) DEFAULT NULL,
  `epg_api` int(1) DEFAULT 0,
  `epg_id` int(11) DEFAULT NULL,
  `channel_id` varchar(255) DEFAULT NULL,
  `epg_lang` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT 0,
  `auto_restart` mediumtext DEFAULT NULL,
  `transcode_profile_id` int(11) DEFAULT 0,
  `gen_timestamps` tinyint(4) DEFAULT 1,
  `added` int(11) DEFAULT NULL,
  `series_no` int(11) DEFAULT 0,
  `direct_source` tinyint(4) DEFAULT 0,
  `tv_archive_duration` int(11) DEFAULT 0,
  `tv_archive_server_id` int(11) DEFAULT 0,
  `tv_archive_pid` int(11) DEFAULT 0,
  `vframes_server_id` int(11) DEFAULT 0,
  `vframes_pid` int(11) DEFAULT 0,
  `movie_symlink` tinyint(4) DEFAULT 0,
  `rtmp_output` tinyint(4) DEFAULT 0,
  `allow_record` tinyint(4) DEFAULT 0,
  `probesize_ondemand` int(11) DEFAULT 128000,
  `custom_map` mediumtext DEFAULT NULL,
  `external_push` mediumtext DEFAULT NULL,
  `delay_minutes` int(11) DEFAULT 0,
  `tmdb_language` varchar(64) DEFAULT NULL,
  `llod` tinyint(4) DEFAULT 0,
  `year` int(4) DEFAULT NULL,
  `rating` float NOT NULL DEFAULT 0,
  `plex_uuid` varchar(256) DEFAULT '',
  `uuid` varchar(32) DEFAULT NULL,
  `epg_offset` int(11) DEFAULT 0,
  `updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `similar` mediumtext DEFAULT NULL,
  `tmdb_id` int(11) DEFAULT NULL,
  `adaptive_link` mediumtext DEFAULT NULL,
  `title_sync` varchar(64) DEFAULT NULL,
  `fps_restart` tinyint(1) DEFAULT 0,
  `fps_threshold` int(11) DEFAULT 90,
  `direct_proxy` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `enable_transcode` (`enable_transcode`),
  KEY `read_native` (`read_native`),
  KEY `epg_id` (`epg_id`),
  KEY `channel_id` (`channel_id`),
  KEY `transcode_profile_id` (`transcode_profile_id`),
  KEY `order` (`order`),
  KEY `direct_source` (`direct_source`),
  KEY `rtmp_output` (`rtmp_output`),
  KEY `epg_api` (`epg_api`),
  KEY `uuid` (`uuid`),
  FULLTEXT KEY `search` (`stream_display_name`,`stream_source`,`notes`,`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streams_arguments`
--

DROP TABLE IF EXISTS `streams_arguments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streams_arguments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `argument_cat` varchar(255) DEFAULT NULL,
  `argument_name` varchar(255) DEFAULT NULL,
  `argument_description` mediumtext DEFAULT NULL,
  `argument_wprotocol` varchar(255) DEFAULT NULL,
  `argument_key` varchar(255) DEFAULT NULL,
  `argument_cmd` varchar(255) DEFAULT NULL,
  `argument_type` varchar(255) DEFAULT NULL,
  `argument_default_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streams_categories`
--

DROP TABLE IF EXISTS `streams_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streams_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_type` varchar(255) DEFAULT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT 0,
  `cat_order` int(11) DEFAULT 0,
  `is_adult` int(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `category_type` (`category_type`),
  KEY `cat_order` (`cat_order`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streams_episodes`
--

DROP TABLE IF EXISTS `streams_episodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streams_episodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_num` int(11) DEFAULT NULL,
  `episode_num` int(11) DEFAULT NULL,
  `series_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `season_num` (`season_num`),
  KEY `series_id` (`series_id`),
  KEY `stream_id` (`stream_id`),
  KEY `episode_num` (`episode_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streams_errors`
--

DROP TABLE IF EXISTS `streams_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streams_errors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `error` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `stream_id` (`stream_id`) USING BTREE,
  KEY `server_id` (`server_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streams_logs`
--

DROP TABLE IF EXISTS `streams_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streams_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `action` varchar(500) DEFAULT NULL,
  `source` varchar(1024) DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streams_options`
--

DROP TABLE IF EXISTS `streams_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streams_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) DEFAULT NULL,
  `argument_id` int(11) DEFAULT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stream_id` (`stream_id`),
  KEY `argument_id` (`argument_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streams_series`
--

DROP TABLE IF EXISTS `streams_series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streams_series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `category_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `cover_big` varchar(255) DEFAULT NULL,
  `genre` varchar(255) DEFAULT NULL,
  `plot` mediumtext DEFAULT NULL,
  `cast` mediumtext DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `director` varchar(255) DEFAULT NULL,
  `release_date` varchar(255) DEFAULT NULL,
  `last_modified` int(11) DEFAULT NULL,
  `tmdb_id` int(11) DEFAULT NULL,
  `seasons` mediumtext DEFAULT NULL,
  `episode_run_time` int(11) DEFAULT 0,
  `backdrop_path` mediumtext DEFAULT NULL,
  `youtube_trailer` mediumtext DEFAULT NULL,
  `tmdb_language` varchar(50) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `plex_uuid` varchar(256) DEFAULT '',
  `similar` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `last_modified` (`last_modified`),
  FULLTEXT KEY `search` (`title`,`plot`,`cast`,`director`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streams_servers`
--

DROP TABLE IF EXISTS `streams_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streams_servers` (
  `server_stream_id` int(11) NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) DEFAULT NULL,
  `server_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `to_analyze` tinyint(4) DEFAULT 0,
  `stream_status` int(11) DEFAULT 0,
  `stream_started` int(11) DEFAULT NULL,
  `stream_info` mediumtext DEFAULT NULL,
  `monitor_pid` int(11) DEFAULT NULL,
  `aes_pid` int(11) DEFAULT NULL,
  `current_source` mediumtext DEFAULT NULL,
  `bitrate` int(11) DEFAULT NULL,
  `progress_info` mediumtext DEFAULT NULL,
  `cc_info` mediumtext DEFAULT NULL,
  `on_demand` tinyint(4) DEFAULT 0,
  `delay_pid` int(11) DEFAULT NULL,
  `delay_available_at` int(11) DEFAULT NULL,
  `pids_create_channel` mediumtext DEFAULT NULL,
  `cchannel_rsources` mediumtext DEFAULT NULL,
  `updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `compatible` tinyint(1) DEFAULT 0,
  `audio_codec` varchar(64) DEFAULT NULL,
  `video_codec` varchar(64) DEFAULT NULL,
  `resolution` int(5) DEFAULT NULL,
  `ondemand_check` int(16) DEFAULT NULL,
  PRIMARY KEY (`server_stream_id`),
  UNIQUE KEY `stream_id_2` (`stream_id`,`server_id`),
  KEY `stream_id` (`stream_id`),
  KEY `pid` (`pid`),
  KEY `server_id` (`server_id`),
  KEY `stream_status` (`stream_status`),
  KEY `stream_started` (`stream_started`),
  KEY `parent_id` (`parent_id`),
  KEY `to_analyze` (`to_analyze`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streams_stats`
--

DROP TABLE IF EXISTS `streams_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streams_stats` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `stream_id` int(16) DEFAULT 0,
  `rank` int(16) DEFAULT 0,
  `time` int(16) DEFAULT 0,
  `connections` int(16) DEFAULT 0,
  `users` int(16) DEFAULT 0,
  `type` varchar(16) DEFAULT NULL,
  `dateadded` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `streams_types`
--

DROP TABLE IF EXISTS `streams_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streams_types` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) DEFAULT NULL,
  `type_key` varchar(255) DEFAULT NULL,
  `type_output` varchar(255) DEFAULT NULL,
  `live` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`type_id`),
  KEY `type_key` (`type_key`),
  KEY `type_output` (`type_output`),
  KEY `live` (`live`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `syskill_log`
--

DROP TABLE IF EXISTS `syskill_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `syskill_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process` varchar(16) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `cpu` float DEFAULT NULL,
  `mem` int(11) DEFAULT NULL,
  `reason` varchar(256) DEFAULT NULL,
  `command` mediumtext DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT 1,
  `admin_read` tinyint(4) DEFAULT 0,
  `user_read` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `status` (`status`),
  KEY `admin_read` (`admin_read`),
  KEY `user_read` (`user_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tickets_replies`
--

DROP TABLE IF EXISTS `tickets_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) DEFAULT NULL,
  `admin_reply` tinyint(4) DEFAULT NULL,
  `message` mediumtext DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `date_registered` int(11) DEFAULT NULL,
  `last_login` int(11) DEFAULT NULL,
  `member_group_id` int(11) DEFAULT NULL,
  `credits` float DEFAULT 0,
  `notes` mediumtext DEFAULT NULL,
  `status` tinyint(2) DEFAULT 1,
  `reseller_dns` mediumtext DEFAULT NULL,
  `owner_id` int(11) DEFAULT 0,
  `override_packages` text DEFAULT NULL,
  `hue` varchar(50) DEFAULT NULL,
  `theme` int(1) DEFAULT 0,
  `timezone` varchar(255) DEFAULT NULL,
  `api_key` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member_group_id` (`member_group_id`),
  KEY `username` (`username`),
  KEY `password` (`password`),
  FULLTEXT KEY `search` (`username`,`email`,`ip`,`notes`,`reseller_dns`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_credits_logs`
--

DROP TABLE IF EXISTS `users_credits_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_credits_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `date` int(11) DEFAULT NULL,
  `reason` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `target_id` (`target_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` mediumtext DEFAULT NULL,
  `is_admin` tinyint(4) DEFAULT 0,
  `is_reseller` tinyint(4) DEFAULT NULL,
  `total_allowed_gen_trials` int(11) DEFAULT 0,
  `total_allowed_gen_in` varchar(255) DEFAULT NULL,
  `delete_users` tinyint(4) DEFAULT 0,
  `allowed_pages` mediumtext DEFAULT NULL,
  `can_delete` tinyint(4) DEFAULT 1,
  `create_sub_resellers` tinyint(4) DEFAULT 0,
  `create_sub_resellers_price` float DEFAULT 0,
  `reseller_client_connection_logs` tinyint(4) DEFAULT 1,
  `can_view_vod` tinyint(4) DEFAULT 1,
  `allow_download` tinyint(4) DEFAULT 1,
  `minimum_trial_credits` int(16) DEFAULT 1,
  `allow_restrictions` tinyint(4) DEFAULT 1,
  `allow_change_username` tinyint(4) DEFAULT 1,
  `allow_change_password` tinyint(4) DEFAULT 1,
  `minimum_username_length` int(16) DEFAULT 8,
  `minimum_password_length` int(16) DEFAULT 8,
  `allow_change_bouquets` tinyint(1) DEFAULT 0,
  `notice_html` mediumtext DEFAULT NULL,
  `subresellers` mediumtext DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  KEY `is_admin` (`is_admin`),
  KEY `is_reseller` (`is_reseller`),
  KEY `can_delete` (`can_delete`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_logs`
--

DROP TABLE IF EXISTS `users_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `log_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `cost` int(16) DEFAULT NULL,
  `credits_after` int(16) DEFAULT NULL,
  `date` int(30) DEFAULT NULL,
  `deleted_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_packages`
--

DROP TABLE IF EXISTS `users_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_name` varchar(255) DEFAULT NULL,
  `is_addon` tinyint(4) DEFAULT 0,
  `is_trial` tinyint(4) DEFAULT 0,
  `is_official` tinyint(4) DEFAULT 0,
  `trial_credits` float DEFAULT 0,
  `official_credits` float DEFAULT 0,
  `trial_duration` int(11) DEFAULT 0,
  `trial_duration_in` varchar(255) DEFAULT NULL,
  `official_duration` int(11) DEFAULT 0,
  `official_duration_in` varchar(255) DEFAULT NULL,
  `groups` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `bouquets` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `addon_packages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `is_line` tinyint(4) DEFAULT 0,
  `is_mag` tinyint(4) DEFAULT 0,
  `is_e2` tinyint(4) DEFAULT 0,
  `is_restreamer` tinyint(4) DEFAULT 0,
  `is_isplock` tinyint(4) DEFAULT 0,
  `output_formats` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `max_connections` int(11) DEFAULT 1,
  `force_server_id` int(11) DEFAULT 0,
  `forced_country` varchar(2) DEFAULT NULL,
  `lock_device` tinyint(4) DEFAULT 1,
  `check_compatible` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `is_trial` (`is_trial`),
  KEY `is_official` (`is_official`),
  KEY `can_gen_mag` (`is_mag`) USING BTREE,
  KEY `can_gen_e2` (`is_e2`) USING BTREE,
  KEY `is_line` (`is_line`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `watch_categories`
--

DROP TABLE IF EXISTS `watch_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watch_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(1) DEFAULT 0,
  `genre_id` int(8) DEFAULT 0,
  `genre` varchar(64) DEFAULT NULL,
  `category_id` int(8) DEFAULT 0,
  `bouquets` varchar(4096) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `watch_folders`
--

DROP TABLE IF EXISTS `watch_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watch_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) DEFAULT NULL,
  `directory` varchar(2048) DEFAULT NULL,
  `rclone_dir` varchar(2048) DEFAULT NULL,
  `server_id` int(8) DEFAULT 0,
  `category_id` int(8) DEFAULT 0,
  `bouquets` varchar(4096) DEFAULT '[]',
  `last_run` int(32) DEFAULT 0,
  `active` int(1) DEFAULT 1,
  `disable_tmdb` int(1) DEFAULT 0,
  `ignore_no_match` int(1) DEFAULT 0,
  `auto_subtitles` int(1) DEFAULT 0,
  `fb_bouquets` varchar(4096) DEFAULT '[]',
  `fb_category_id` int(8) DEFAULT 0,
  `allowed_extensions` varchar(4096) DEFAULT '[]',
  `language` varchar(32) DEFAULT NULL,
  `read_native` tinyint(4) DEFAULT 0,
  `movie_symlink` tinyint(4) DEFAULT 0,
  `auto_encode` tinyint(4) DEFAULT 1,
  `ffprobe_input` tinyint(4) DEFAULT 1,
  `transcode_profile_id` int(11) DEFAULT 0,
  `auto_upgrade` tinyint(4) DEFAULT 0,
  `fallback_title` tinyint(4) DEFAULT 0,
  `plex_ip` varchar(128) DEFAULT NULL,
  `plex_port` int(5) DEFAULT 0,
  `plex_username` varchar(256) DEFAULT NULL,
  `plex_password` varchar(256) DEFAULT NULL,
  `plex_libraries` mediumtext DEFAULT NULL,
  `scan_missing` tinyint(4) DEFAULT 0,
  `extract_metadata` tinyint(4) DEFAULT 0,
  `store_categories` tinyint(1) DEFAULT 0,
  `duplicate_tmdb` tinyint(1) DEFAULT 0,
  `check_tmdb` tinyint(1) DEFAULT 1,
  `remove_subtitles` tinyint(1) DEFAULT 0,
  `target_container` varchar(64) DEFAULT NULL,
  `server_add` varchar(512) DEFAULT NULL,
  `direct_proxy` tinyint(1) DEFAULT 0,
  `plex_token` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `watch_logs`
--

DROP TABLE IF EXISTS `watch_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watch_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(1) DEFAULT 0,
  `server_id` int(8) DEFAULT 0,
  `filename` varchar(4096) DEFAULT NULL,
  `status` int(1) DEFAULT 0,
  `stream_id` int(8) DEFAULT 0,
  `dateadded` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `watch_refresh`
--

DROP TABLE IF EXISTS `watch_refresh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watch_refresh` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(1) DEFAULT 0,
  `stream_id` int(16) DEFAULT 0,
  `status` int(8) DEFAULT 0,
  `dateadded` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-04  0:38:03
-- MySQL dump 10.19  Distrib 10.3.39-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: xui
-- ------------------------------------------------------
-- Server version	10.3.39-MariaDB-0ubuntu0.20.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `access_codes`
--

LOCK TABLES `access_codes` WRITE;
/*!40000 ALTER TABLE `access_codes` DISABLE KEYS */;
INSERT INTO `access_codes` VALUES (1,'zdXMtZC9',0,1,'[1]',NULL);
/*!40000 ALTER TABLE `access_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `blocked_asns`
--



--
-- Dumping data for table `blocked_ips`
--

LOCK TABLES `blocked_ips` WRITE;
/*!40000 ALTER TABLE `blocked_ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocked_ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `blocked_isps`
--

LOCK TABLES `blocked_isps` WRITE;
/*!40000 ALTER TABLE `blocked_isps` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocked_isps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `blocked_uas`
--

LOCK TABLES `blocked_uas` WRITE;
/*!40000 ALTER TABLE `blocked_uas` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocked_uas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `bouquets`
--

LOCK TABLES `bouquets` WRITE;
/*!40000 ALTER TABLE `bouquets` DISABLE KEYS */;
/*!40000 ALTER TABLE `bouquets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `crontab`
--

LOCK TABLES `crontab` WRITE;
/*!40000 ALTER TABLE `crontab` DISABLE KEYS */;
INSERT INTO `crontab` VALUES (2,'lines_logs.php','* * * * *',1),(3,'epg.php','0 0 * * *',1),(5,'streams.php','* * * * *',1),(6,'activity.php','* * * * *',1),(7,'servers.php','* * * * *',1),(8,'cache.php','* * * * *',1),(9,'stats.php','0 * * * *',1),(10,'errors.php','* * * * *',1),(11,'tmdb.php','0 * * * *',1),(12,'tmp.php','* * * * *',1),(13,'users.php','* * * * *',1),(14,'vod.php','* * * * *',1),(15,'series.php','* * * * *',1),(16,'watch.php','*/5 * * * *',1),(17,'backups.php','* * * * *',1),(18,'streams_logs.php','* * * * *',1),(19,'license.php','0 0 * * *',1),(20,'cleanup.php','0 * * * *',1),(22,'certbot.php','0 0 * * *',1),(24,'cache_engine.php','*/5 * * * *',1),(25,'providers.php','0 * * * *',1),(26,'tmdb_popular.php','0 * * * *',1),(27,'plex.php','*/5 * * * *',1);
/*!40000 ALTER TABLE `crontab` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `detect_restream`
--

LOCK TABLES `detect_restream` WRITE;
/*!40000 ALTER TABLE `detect_restream` DISABLE KEYS */;
/*!40000 ALTER TABLE `detect_restream` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `enigma2_actions`
--

LOCK TABLES `enigma2_actions` WRITE;
/*!40000 ALTER TABLE `enigma2_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `enigma2_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `enigma2_devices`
--

LOCK TABLES `enigma2_devices` WRITE;
/*!40000 ALTER TABLE `enigma2_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `enigma2_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `epg`
--

LOCK TABLES `epg` WRITE;
/*!40000 ALTER TABLE `epg` DISABLE KEYS */;
/*!40000 ALTER TABLE `epg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `epg_api`
--

LOCK TABLES `epg_api` WRITE;
/*!40000 ALTER TABLE `epg_api` DISABLE KEYS */;
/*!40000 ALTER TABLE `epg_api` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `epg_channels`
--

LOCK TABLES `epg_channels` WRITE;
/*!40000 ALTER TABLE `epg_channels` DISABLE KEYS */;
/*!40000 ALTER TABLE `epg_channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `epg_languages`
--

LOCK TABLES `epg_languages` WRITE;
/*!40000 ALTER TABLE `epg_languages` DISABLE KEYS */;
INSERT INTO `epg_languages` VALUES (1,'af','Afrikaans','2020-10-26 13:36:48'),(2,'sq','Albanian','2020-10-26 13:36:48'),(3,'ar','Arabic','2020-10-26 13:36:48'),(4,'arc','Aramaic','2020-10-26 13:36:48'),(5,'arp','Arapaho','2020-10-26 13:36:48'),(6,'hy','Armenian','2020-10-26 13:36:48'),(7,'eu','Basque','2020-10-26 13:36:49'),(8,'be','Belarusian','2020-10-26 13:36:49'),(9,'bn','Bengali','2020-10-26 13:36:49'),(10,'bg','Bulgarian','2020-10-26 13:36:49'),(11,'ca','Catalan','2020-10-26 13:36:49'),(12,'km','Central Khmer','2020-10-26 13:36:49'),(13,'zh','Chinese','2020-10-26 13:36:49'),(14,'hr','Croatian','2020-10-26 13:36:49'),(15,'cs','Czech','2020-10-26 13:36:49'),(16,'da','Danish','2020-10-26 13:36:49'),(17,'nl','Dutch','2020-10-26 13:36:49'),(18,'en','English','2020-10-26 13:36:49'),(19,'en-GB','English (UK)','2020-10-26 13:36:50'),(20,'fa','Farsi','2020-10-26 13:36:50'),(21,'fi','Finnish','2020-10-26 13:36:50'),(22,'fr-FR','French','2020-10-26 13:36:50'),(23,'fr-CA','French (Canada)','2020-10-26 13:36:50'),(24,'gd','Gaelic','2020-10-26 13:36:50'),(25,'de','German','2020-10-26 13:36:50'),(26,'el','Greek','2020-10-26 13:36:50'),(27,'he','Hebrew','2020-10-26 13:36:50'),(28,'hi','Hindi','2020-10-26 13:36:50'),(29,'hu','Hungarian','2020-10-26 13:36:50'),(30,'iu','Inuktitut','2020-10-26 13:36:50'),(31,'it','Italian','2020-10-26 13:36:51'),(32,'ja','Japanese','2020-10-26 13:36:51'),(33,'ko','Korean','2020-10-26 13:36:51'),(34,'ku','Kurdish','2020-10-26 13:36:51'),(35,'mk','Macedonian','2020-10-26 13:36:51'),(36,'ml','Malayalam','2020-10-26 13:36:51'),(37,'no','Norwegian','2020-10-26 13:36:51'),(38,'pl','Polish','2020-10-26 13:36:51'),(39,'pt','Portuguese','2020-10-26 13:36:51'),(40,'pt-BR','Portuguese (Brazil)','2020-10-26 13:36:52'),(41,'pa','Punjabi','2020-10-26 13:36:52'),(42,'ro','Romanian','2020-10-26 13:36:52'),(43,'ru','Russian','2020-10-26 13:36:52'),(44,'sr','Serbian','2020-10-26 13:36:52'),(45,'so','Somali','2020-10-26 13:36:52'),(46,'es','Spanish','2020-10-26 13:36:52'),(47,'es-ES','Spanish (Castilian)','2020-10-26 13:36:52'),(48,'sv','Swedish','2020-10-26 13:36:53'),(49,'tl','Tagalog','2020-10-26 13:36:53'),(50,'ta','Tamil','2020-10-26 13:36:53'),(51,'te','Telugu','2020-10-26 13:36:53'),(52,'tr','Turkish','2020-10-26 13:36:53'),(53,'ur','Urdu','2020-10-26 13:36:53'),(54,'vi','Vietnamese','2020-10-26 13:36:53'),(55,'yi','Yiddish','2020-10-26 13:36:53');
/*!40000 ALTER TABLE `epg_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `hmac_keys`
--

LOCK TABLES `hmac_keys` WRITE;
/*!40000 ALTER TABLE `hmac_keys` DISABLE KEYS */;
/*!40000 ALTER TABLE `hmac_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `lines`
--

LOCK TABLES `lines` WRITE;
/*!40000 ALTER TABLE `lines` DISABLE KEYS */;
/*!40000 ALTER TABLE `lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `lines_divergence`
--

LOCK TABLES `lines_divergence` WRITE;
/*!40000 ALTER TABLE `lines_divergence` DISABLE KEYS */;
/*!40000 ALTER TABLE `lines_divergence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `mag_devices`
--

LOCK TABLES `mag_devices` WRITE;
/*!40000 ALTER TABLE `mag_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `mag_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `mag_events`
--

LOCK TABLES `mag_events` WRITE;
/*!40000 ALTER TABLE `mag_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `mag_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `ondemand_check`
--

LOCK TABLES `ondemand_check` WRITE;
/*!40000 ALTER TABLE `ondemand_check` DISABLE KEYS */;
/*!40000 ALTER TABLE `ondemand_check` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `output_devices`
--

LOCK TABLES `output_devices` WRITE;
/*!40000 ALTER TABLE `output_devices` DISABLE KEYS */;
INSERT INTO `output_devices` VALUES (1,'M3U Standard','m3u','playlist_{USERNAME}.m3u','#EXTM3U','#EXTINF:-1,{CHANNEL_NAME}\r\n{URL}','',2,NULL),(2,'M3U Plus','m3u_plus','playlist_{USERNAME}_plus.m3u','#EXTM3U','#EXTINF:-1 xui-id=\"{XUI_ID}\" tvg-id=\"{CHANNEL_ID}\" tvg-name=\"{CHANNEL_NAME}\" tvg-logo=\"{CHANNEL_ICON}\" group-title=\"{CATEGORY}\",{CHANNEL_NAME}\r\n{URL}','',2,NULL),(3,'Simple List','simple','simple_{USERNAME}.txt','','{URL} #Name: {CHANNEL_NAME}','',2,NULL),(4,'Ariva','ariva','ariva_{USERNAME}.txt','','{CHANNEL_NAME},{URL}','',2,NULL),(5,'DreamBox OE 2.0','dreambox','userbouquet.favourites.tv','#NAME {BOUQUET_NAME}','#SERVICE {ESR_ID}{SID}{URL#:}\r\n#DESCRIPTION {CHANNEL_NAME}','',2,NULL),(6,'Enigma 2 OE 1.6','enigma16','userbouquet.favourites.tv','#NAME {BOUQUET_NAME}','#SERVICE 4097{SID}{URL#:}\r\n#DESCRIPTION {CHANNEL_NAME}','',2,NULL),(7,'Enigma 2 OE 1.6 Auto Script','enigma216_script','iptv.sh','USERNAME=\"{USERNAME}\";PASSWORD=\"{PASSWORD}\";bouquet=\"{BOUQUET_NAME}\";directory=\"/etc/enigma2/iptv.sh\";url=\"{SERVER_URL}playlist/$USERNAME/$PASSWORD/enigma16?output={OUTPUT_KEY}\";rm /etc/enigma2/userbouquet.\"$bouquet\"__tv_.tv;wget -O /etc/enigma2/userbouquet.\"$bouquet\"__tv_.tv $url;if ! cat /etc/enigma2/bouquets.tv | grep -v grep | grep -c $bouquet > /dev/null;then echo \"[+] Creating IPTV folder...\";cat /etc/enigma2/bouquets.tv | sed -n 1p > /etc/enigma2/new_bouquets.tv;echo \'#SERVICE 1:7:1:0:0:0:0:0:0:0:FROM BOUQUET \"userbouquet.\'$bouquet\'__tv_.tv\" ORDER BY bouquet\' >> /etc/enigma2/new_bouquets.tv; cat /etc/enigma2/bouquets.tv | sed -n \'2,$p\' >> /etc/enigma2/new_bouquets.tv;rm /etc/enigma2/bouquets.tv;mv /etc/enigma2/new_bouquets.tv /etc/enigma2/bouquets.tv;fi;rm /usr/bin/enigma2_pre_start.sh;echo \"Writing to file...\";echo \"/bin/sh \"$directory\" > /dev/null 2>&1 &\" > /usr/bin/enigma2_pre_start.sh;chmod 777 /usr/bin/enigma2_pre_start.sh;wget -qO - \"http://127.0.0.1/web/servicelistreload?mode=2\";wget -qO - \"http://127.0.0.1/web/servicelistreload?mode=2\"; read -p \"Press enter to complete setup and reboot\";;','','',2,'wget -O /etc/enigma2/iptv.sh {DEVICE_LINK} && chmod 777 /etc/enigma2/iptv.sh && /etc/enigma2/iptv.sh'),(8,'Enigma 2 OE 2.0 Auto Script','enigma22_script','iptv.sh','USERNAME=\"{USERNAME}\";PASSWORD=\"{PASSWORD}\";bouquet=\"{BOUQUET_NAME}\";directory=\"/etc/enigma2/iptv.sh\";url=\"{SERVER_URL}playlist/$USERNAME/$PASSWORD/dreambox?output={OUTPUT_KEY}\";rm /etc/enigma2/userbouquet.\"$bouquet\"__tv_.tv;wget -O /etc/enigma2/userbouquet.\"$bouquet\"__tv_.tv $url;if ! cat /etc/enigma2/bouquets.tv | grep -v grep | grep -c $bouquet > /dev/null;then echo \"[+] Creating IPTV folder...\";cat /etc/enigma2/bouquets.tv | sed -n 1p > /etc/enigma2/new_bouquets.tv;echo \'#SERVICE 1:7:1:0:0:0:0:0:0:0:FROM BOUQUET \"userbouquet.\'$bouquet\'__tv_.tv\" ORDER BY bouquet\' >> /etc/enigma2/new_bouquets.tv; cat /etc/enigma2/bouquets.tv | sed -n \'2,$p\' >> /etc/enigma2/new_bouquets.tv;rm /etc/enigma2/bouquets.tv;mv /etc/enigma2/new_bouquets.tv /etc/enigma2/bouquets.tv;fi;rm /usr/bin/enigma2_pre_start.sh;echo \"Writing to file...\";echo \"/bin/sh \"$directory\" > /dev/null 2>&1 &\" > /usr/bin/enigma2_pre_start.sh;chmod 777 /usr/bin/enigma2_pre_start.sh;wget -qO - \"http://127.0.0.1/web/servicelistreload?mode=2\";wget -qO - \"http://127.0.0.1/web/servicelistreload?mode=2\"; read -p \"Press enter to complete setup and reboot\";','','',2,'wget -O /etc/enigma2/iptv.sh {DEVICE_LINK} && chmod 777 /etc/enigma2/iptv.sh && /etc/enigma2/iptv.sh'),(9,'Fortec999/Prifix9400/Starport','fps','Royal.cfg','','IPTV: { {CHANNEL_NAME} } { {URL} }','',2,NULL),(10,'Geant/Starsat/Tiger/Qmax/Hyper/Royal','gst','{USERNAME}_list.txt','','I: {URL} {CHANNEL_NAME}','',2,NULL),(11,'GigaBlue','gigablue','userbouquet.favourites.tv','#NAME {BOUQUET_NAME}','#SERVICE 4097:0:1:0:0:0:0:0:0:0:{URL#:}\r\n#DESCRIPTION {CHANNEL_NAME}','',2,NULL),(12,'MediaStar / StarLive v4','mediastar','tvlist.txt','','{CHANNEL_NAME} {URL}','',2,NULL),(13,'Octagon','octagon','internettv.feed','','[TITLE]\r\n{CHANNEL_NAME}\r\n[URL]\r\n{URL}\r\n[DESCRIPTION]\r\nIPTV\r\n[TYPE]\r\nLive','',2,NULL),(14,'Octagon Auto Script','octagon_script','iptv','USERNAME=\"{USERNAME}\";PASSWORD=\"{PASSWORD}\";url=\"{SERVER_URL}get.php?username=$USERNAME&password=$PASSWORD&type=octagon&output={OUTPUT_KEY}\";rm /var/freetvplus/internettv.feed;wget -O /var/freetvplus/internettv.feed1 $url;chmod 777 /var/freetvplus/internettv.feed1;awk -v BINMODE=3 -v RS=\'(\\r\\n|\\n)\' -v ORS=\'\\n\' \'{ print }\' /var/freetvplus/internettv.feed1 > /var/freetvplus/internettv.feed;rm /var/freetvplus/internettv.feed1','','',2,'wget -qO /var/bin/iptv {DEVICE_LINK}'),(15,'Revolution 60/60 | Sunplus','revosun','network_iptv.cfg','','IPTV: { {CHANNEL_NAME} } { {URL} }','',2,NULL),(16,'Spark','spark','webtv_usr.xml','<?xml version=\"1.0\"?>\r\n<webtvs>','<webtv title=\"{CHANNEL_NAME}\" urlkey=\"0\" url=\"{URL}\" description=\"\" iconsrc=\"{CHANNEL_ICON}\" iconsrc_b=\"\" group=\"0\" type=\"0\" />','</webtvs>',2,NULL),(17,'Starlive v3/StarSat HD6060/AZclass','starlivev3','iptvlist.txt','','{CHANNEL_NAME},{URL}','',2,NULL),(18,'StarLive v5','starlivev5','channel.jason','','','',2,NULL),(19,'WebTV List','webtvlist','webtv list.txt','','Channel name:{CHANNEL_NAME}\r\nURL:{URL}','[Webtv channel END]',2,NULL),(20,'Zorro','zorro','iptv.cfg','<NETDBS_TXT_VER_1>','IPTV: { {CHANNEL_NAME} } { {URL} } -HIDE_URL','',2,NULL);
/*!40000 ALTER TABLE `output_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `output_formats`
--

LOCK TABLES `output_formats` WRITE;
/*!40000 ALTER TABLE `output_formats` DISABLE KEYS */;
INSERT INTO `output_formats` VALUES (1,'HLS','m3u8','m3u8'),(2,'MPEGTS','ts','ts'),(3,'RTMP','rtmp','');
/*!40000 ALTER TABLE `output_formats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `profiles`
--

LOCK TABLES `profiles` WRITE;
/*!40000 ALTER TABLE `profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `providers`
--

LOCK TABLES `providers` WRITE;
/*!40000 ALTER TABLE `providers` DISABLE KEYS */;
/*!40000 ALTER TABLE `providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `providers_streams`
--

LOCK TABLES `providers_streams` WRITE;
/*!40000 ALTER TABLE `providers_streams` DISABLE KEYS */;
/*!40000 ALTER TABLE `providers_streams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `queue`
--

LOCK TABLES `queue` WRITE;
/*!40000 ALTER TABLE `queue` DISABLE KEYS */;
/*!40000 ALTER TABLE `queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `recordings`
--

LOCK TABLES `recordings` WRITE;
/*!40000 ALTER TABLE `recordings` DISABLE KEYS */;
/*!40000 ALTER TABLE `recordings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `rtmp_ips`
--

LOCK TABLES `rtmp_ips` WRITE;
/*!40000 ALTER TABLE `rtmp_ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `rtmp_ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `servers`
--

LOCK TABLES `servers` WRITE;
/*!40000 ALTER TABLE `servers` DISABLE KEYS */;
INSERT INTO `servers` VALUES (1,0,'1.5.12','Main Server','','127.0.0.1','',1,1,'0',80,443,NULL,NULL,1000,'auto',1,0,'[]',1770165482,NULL,4,0,8880,'low_priority','','low_priority',0,1000,0,'[\"127.0.0.1\"]','{\"cpu\":47.23,\"cpu_cores\":1,\"cpu_avg\":58,\"cpu_name\":\"Intel(R) Core(TM) i7-10750H CPU @ 2.60GHz\",\"total_mem\":6072520,\"total_mem_free\":5058364,\"total_mem_used\":1015328,\"total_mem_used_percent\":16.72,\"total_disk_space\":12040970240,\"free_disk_space\":4834426880,\"kernel\":\"5.4.0-216-generic\",\"uptime\":\"23m 17s\",\"total_running_streams\":0,\"bytes_sent\":0,\"bytes_sent_total\":0,\"bytes_received\":0,\"bytes_received_total\":0,\"network_speed\":0,\"interfaces\":[\"enp0s3\"],\"network_info\":[],\"audio_devices\":[],\"video_devices\":[],\"gpu_info\":[],\"iostat_info\":{\"avg-cpu\":{\"user\":4.52,\"nice\":0.58,\"system\":2.96,\"iowait\":0.33,\"steal\":0,\"idle\":91.61},\"disk\":[{\"disk_device\":\"dm-0\",\"tps\":35.04,\"MB_read\\/s\":0.47,\"MB_wrtn\\/s\":2.32,\"MB_dscd\\/s\":0,\"MB_read\":654,\"MB_wrtn\":3247,\"MB_dscd\":0},{\"disk_device\":\"loop0\",\"tps\":0.03,\"MB_read\\/s\":0,\"MB_wrtn\\/s\":0,\"MB_dscd\\/s\":0,\"MB_read\":0,\"MB_wrtn\":0,\"MB_dscd\":0},{\"disk_device\":\"loop1\",\"tps\":1.08,\"MB_read\\/s\":0,\"MB_wrtn\\/s\":0,\"MB_dscd\\/s\":0,\"MB_read\":1,\"MB_wrtn\":0,\"MB_dscd\":0},{\"disk_device\":\"loop2\",\"tps\":0.03,\"MB_read\\/s\":0,\"MB_wrtn\\/s\":0,\"MB_dscd\\/s\":0,\"MB_read\":0,\"MB_wrtn\":0,\"MB_dscd\":0},{\"disk_device\":\"loop3\",\"tps\":16.8,\"MB_read\\/s\":0.02,\"MB_wrtn\\/s\":0,\"MB_dscd\\/s\":0,\"MB_read\":23,\"MB_wrtn\":0,\"MB_dscd\":0},{\"disk_device\":\"loop4\",\"tps\":0.04,\"MB_read\\/s\":0,\"MB_wrtn\\/s\":0,\"MB_dscd\\/s\":0,\"MB_read\":1,\"MB_wrtn\":0,\"MB_dscd\":0},{\"disk_device\":\"loop5\",\"tps\":0.05,\"MB_read\\/s\":0,\"MB_wrtn\\/s\":0,\"MB_dscd\\/s\":0,\"MB_read\":1,\"MB_wrtn\":0,\"MB_dscd\":0},{\"disk_device\":\"loop6\",\"tps\":0,\"MB_read\\/s\":0,\"MB_wrtn\\/s\":0,\"MB_dscd\\/s\":0,\"MB_read\":0,\"MB_wrtn\":0,\"MB_dscd\":0},{\"disk_device\":\"sda\",\"tps\":25.91,\"MB_read\\/s\":0.48,\"MB_wrtn\\/s\":2.32,\"MB_dscd\\/s\":0,\"MB_read\":664,\"MB_wrtn\":3243,\"MB_dscd\":0}]},\"cpu_load_average\":0.58,\"cpu_average_array\":[1.02,4,3,2.5300000000000002,2.98,2.51,2.01,2.53,3.94,3,3.5,2.52,47.23]}','[]','[]','[]','[\"eth0\"]',0,0,0,0,NULL,NULL,0,0,0,0,0,2,74,'[\"13295\",\"13297\",\"13299\",\"13301\"]',0,0,1,NULL,NULL,NULL,NULL,0,0,0);
/*!40000 ALTER TABLE `servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'XUI.one','Europe/London','[]',10,'equal',5000000,1,'',1,'',1,'',1,'','ts','ts',5000000,'',1,'[\"ALL\"]',3,1,1,40,'',2,400,8192,10,6,'fit',3,1,1,1,10,'[]','default',1,'',0,0,0,'','bouquet',1,0,10,0,'https://speed.hetzner.de/100MB.bin','','Welcome to XUI.one',1,1,1,1,1,0,0,'conn',1,1,'',1,1,14126,0,1770165482,1,50,0,1,10,0,1,1770165482,20,0,1,0,0,0,'daily','','','','','python','en',0,0,0,0,0,0,0,'Y-m-d','Y-m-d H:i:s',1,7,7,2,1,0,0,0,0,0,0,0,31,1,25,10,10,300,1,'1.1.0',1,0,0,0,1,0,0,'[25461,25550,31210]',0,1,1,0,1,1,0,80,50,86400,0,3,1,1,0,0,1,0,0,1,2,1,0,1,1,0,0,0,0,0,0,0,1,0,0,0,0,20,0,1,1,1,10,'',0,0,'','','',0,1,0,1,1,0,0,0,0,0,0,4,86400,1,5,1,5,5,0,'wtIjNxxLHGyywgBY2MAQBTNajIGkWLbKjFWqwpkGXpAh6NmZt89pxdoYdhq4mDrybDJg3IVPRs9h6xqiJWooeFJiuetaBuXeERtBBJ0ysRD9qXJxCvVQP6N57EqFSQswxPm4IyQh4vKzsJqxVp65t2wlXQLblRRfcZBoNXLxpBl0dopuBOViLR8yzmCrpS1G4eFiATbSsqpXwGEHGNlhONvTEMwtifSEGjI9IdqB3BUIcViO3wyVl53ZGcawEo8d299qrSSO6KfSGKafdZcdWNLr4JC61BRIND4CVVt4EJyRyBAraY3hE6BXlqJ6bwICrICztP6IZzISaZWfq3cMmSimHOC2wXtDPZ7VbFY7vO3u2GrAIcGhqJKUdOTmZq6urRlODuOzBh14EJYoZWPk1nnzVTlGUbjT90AKRsbl1Wq5zNKlIhq3J9Q47ma6A6UjZtLh6VEeyPzqqI8QNsnlvTWGUoypBziDUKRSB5S0TCToEccvl58XPb7bwJvXhMS4',0,0,10,1,1,10,1,0,10,1,1,0,0,1,0,'4.0','4.0',1,1,1,NULL,1,256000,1,0,'You can switch between the modern and legacy themes by using the <span class=\"label\">Green</span> and <span class=\"label\">Yellow</span> buttons on your remote control. Doing so will restart your device.',0,600,1,0,10,1,0,1,0,0,0,0,1,20,0,0,0,0,0,NULL,1,0,3600,5,604800,'ptn',0,0,20,1,1,1,NULL,3,0,21600,NULL,NULL,67,67,67,67,67,10,30,1,1);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `streams`
--

LOCK TABLES `streams` WRITE;
/*!40000 ALTER TABLE `streams` DISABLE KEYS */;
/*!40000 ALTER TABLE `streams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `streams_arguments`
--

LOCK TABLES `streams_arguments` WRITE;
/*!40000 ALTER TABLE `streams_arguments` DISABLE KEYS */;
INSERT INTO `streams_arguments` VALUES (1,'fetch','User Agent','Set a Custom User Agent','http','user_agent','-user_agent \"%s\"','text','Mozilla/5.0'),(2,'fetch','HTTP Proxy','Set an HTTP Proxy in this format: ip:port','http','proxy','-http_proxy \"%s\"','text',NULL),(3,'transcode','Average Video Bit Rate','With this you can change the bitrate of the target video. It is very useful in case you want your video to be playable on slow internet connections',NULL,'bitrate','-b:v %dk','text',NULL),(4,'transcode','Average Audio Bitrate','Change Audio Bitrate',NULL,'audio_bitrate','-b:a %dk','text',NULL),(5,'transcode','Minimum Bitrate Tolerance','-minrate FFmpeg argument. Specify the minimum bitrate tolerance here. Specify in kbps. Enter INT number.',NULL,'minimum_bitrate','-minrate %dk','text',NULL),(6,'transcode','Maximum Bitrate Tolerance','-maxrate FFmpeg argument. Specify the maximum bitrate tolerance here.Specify in kbps. Enter INT number. ',NULL,'maximum_bitrate','-maxrate %dk','text',NULL),(7,'transcode','Buffer Size','-bufsize is the rate control buffer. Basically it is assumed that the receiver/end player will buffer that much data so its ok to fluctuate within that much. Specify in kbps. Enter INT number.',NULL,'bufsize','-bufsize %dk','text',NULL),(8,'transcode','CRF Value','The range of the quantizer scale is 0-51: where 0 is lossless, 23 is default, and 51 is worst possible. A lower value is a higher quality and a subjectively sane range is 18-28. Consider 18 to be visually lossless or nearly so: it should look the same or ',NULL,'crf','-crf %d','text',NULL),(9,'transcode','Scaling','Change the Width & Height of the target Video. (Eg. 320:240 ) .  If we\'d like to keep the aspect ratio, we need to specify only one component, either width or height, and set the other component to -1. (eg 320:-1)',NULL,'scaling','-filter_complex \"scale=%s\"','text',NULL),(10,'transcode','Aspect','Change the target Video Aspect. (eg 16:9)',NULL,'aspect','-aspect %s','text',NULL),(11,'transcode','Target Video FrameRate','Set the frame rate',NULL,'video_frame_rate','-r %d','text',NULL),(12,'transcode','Audio Sample Rate','Set the Audio Sample rate in Hz',NULL,'audio_sample_rate','-ar %d','text',NULL),(13,'transcode','Audio Channels','Specify Audio Channels',NULL,'audio_channels','-ac %d','text',NULL),(14,'transcode','Remove Sensitive Parts (delogo filter)','With this filter you can remove sensitive parts in your video. You will just specifiy the x & y pixels where there is a sensitive area and the width and height that will be removed. Example Use: x=0:y=0:w=100:h=77:band=10 ',NULL,'delogo','-filter_complex \"delogo=%s\"','text',NULL),(15,'transcode','Threads','Specify the number of threads you want to use for the transcoding process. Entering 0 as value will make FFmpeg to choose the most optimal settings',NULL,'threads','-threads %d','text',NULL),(16,'transcode','Logo Path','Add your Own Logo to the stream. The logo will be placed in the upper left. Please be sure that you have selected H.264 as codec otherwise this option won\'t work. Note that adding your own logo will consume A LOT of cpu power',NULL,'logo','-i \"%s\" -filter_complex \"overlay\"','text',NULL),(17,'fetch','Cookie','Set an HTTP Cookie that might be useful to fetch your INPUT Source.','http','cookie','-cookies \'%s\'','text',NULL),(18,'transcode','DeInterlacing Filter','It check pixels of previous, current and next frames to re-create the missed field by some local adaptive method (edge-directed interpolation) and uses spatial check to prevent most artifacts. ',NULL,'','-filter_complex \"yadif\"','radio','0'),(19,'fetch','Headers','Set Custom Headers','http','headers','-headers $\'%s\r\n\'','text',NULL);
/*!40000 ALTER TABLE `streams_arguments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `streams_categories`
--

LOCK TABLES `streams_categories` WRITE;
/*!40000 ALTER TABLE `streams_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `streams_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `streams_episodes`
--

LOCK TABLES `streams_episodes` WRITE;
/*!40000 ALTER TABLE `streams_episodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `streams_episodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `streams_options`
--

LOCK TABLES `streams_options` WRITE;
/*!40000 ALTER TABLE `streams_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `streams_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `streams_series`
--

LOCK TABLES `streams_series` WRITE;
/*!40000 ALTER TABLE `streams_series` DISABLE KEYS */;
/*!40000 ALTER TABLE `streams_series` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `streams_servers`
--

LOCK TABLES `streams_servers` WRITE;
/*!40000 ALTER TABLE `streams_servers` DISABLE KEYS */;
/*!40000 ALTER TABLE `streams_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `streams_types`
--

LOCK TABLES `streams_types` WRITE;
/*!40000 ALTER TABLE `streams_types` DISABLE KEYS */;
INSERT INTO `streams_types` VALUES (1,'Live Streams','live','live',1),(2,'Movies','movie','movie',0),(3,'Created Channels','created_live','live',1),(4,'Radio Stations','radio_streams','live',1),(5,'TV Series','series','series',0);
/*!40000 ALTER TABLE `streams_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tickets_replies`
--

LOCK TABLES `tickets_replies` WRITE;
/*!40000 ALTER TABLE `tickets_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users_groups`
--

LOCK TABLES `users_groups` WRITE;
/*!40000 ALTER TABLE `users_groups` DISABLE KEYS */;
INSERT INTO `users_groups` VALUES (1,'Administrators',1,0,0,'day',0,'[]',0,0,0,0,0,1,0,0,1,1,8,8,0,NULL,NULL),(2,'Resellers',0,1,100000,'month',1,'[]',0,0,0,1,1,1,0,1,1,1,8,8,0,NULL,NULL);
/*!40000 ALTER TABLE `users_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users_packages`
--

LOCK TABLES `users_packages` WRITE;
/*!40000 ALTER TABLE `users_packages` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `watch_categories`
--

LOCK TABLES `watch_categories` WRITE;
/*!40000 ALTER TABLE `watch_categories` DISABLE KEYS */;
INSERT INTO `watch_categories` VALUES (1,1,28,'Action',0,'[]'),(2,1,12,'Adventure',0,'[]'),(3,1,16,'Animation',0,'[]'),(4,1,35,'Comedy',0,'[]'),(5,1,80,'Crime',0,'[]'),(6,1,99,'Documentary',0,'[]'),(7,1,18,'Drama',0,'[]'),(8,1,10751,'Family',0,'[]'),(9,1,14,'Fantasy',0,'[]'),(10,1,36,'History',0,'[]'),(11,1,27,'Horror',0,'[]'),(12,1,10402,'Music',0,'[]'),(13,1,9648,'Mystery',0,'[]'),(14,1,10749,'Romance',0,'[]'),(15,1,878,'Science Fiction',0,'[]'),(16,1,10770,'TV Movie',0,'[]'),(17,1,53,'Thriller',0,'[]'),(18,1,10752,'War',0,'[]'),(19,1,37,'Western',0,'[]'),(20,2,28,'Action',0,'[]'),(21,2,12,'Adventure',0,'[]'),(22,2,16,'Animation',0,'[]'),(23,2,35,'Comedy',0,'[]'),(24,2,80,'Crime',0,'[]'),(25,2,99,'Documentary',0,'[]'),(26,2,18,'Drama',0,'[]'),(27,2,10751,'Family',0,'[]'),(28,2,14,'Fantasy',0,'[]'),(29,2,36,'History',0,'[]'),(30,2,27,'Horror',0,'[]'),(31,2,10402,'Music',0,'[]'),(32,2,9648,'Mystery',0,'[]'),(33,2,10749,'Romance',0,'[]'),(34,2,878,'Science Fiction',0,'[]'),(35,2,10770,'TV Movie',0,'[]'),(36,2,53,'Thriller',0,'[]'),(37,2,10752,'War',0,'[]'),(38,2,37,'Western',0,'[]'),(39,1,10759,'Action & Adventure',0,'[]'),(40,2,10759,'Action & Adventure',0,'[]'),(47,1,10762,'Kids',0,'[]'),(48,2,10762,'Kids',0,'[]'),(50,1,10763,'News',0,'[]'),(51,2,10763,'News',0,'[]'),(52,1,10764,'Reality',0,'[]'),(53,2,10764,'Reality',0,'[]'),(54,1,10765,'Sci-Fi & Fantasy',0,'[]'),(55,2,10765,'Sci-Fi & Fantasy',0,'[]'),(56,1,10766,'Soap',0,'[]'),(57,2,10766,'Soap',0,'[]'),(58,1,10767,'Talk',0,'[]'),(59,2,10767,'Talk',0,'[]'),(60,1,10768,'War & Politics',0,'[]'),(61,2,10768,'War & Politics',0,'[]'),(62,3,1,'Action',0,'[]'),(63,3,2,'Action / Adventure',0,'[]'),(64,3,3,'Adventure',0,'[]'),(65,3,4,'Animation',0,'[]'),(66,3,5,'Anime',0,'[]'),(67,3,6,'Biography',0,'[]'),(68,3,7,'Children',0,'[]'),(69,3,8,'Comedy',0,'[]'),(70,3,9,'Crime',0,'[]'),(71,3,10,'Documentary',0,'[]'),(72,3,11,'Drama',0,'[]'),(73,3,12,'Family',0,'[]'),(74,3,13,'Fantasy',0,'[]'),(75,3,14,'History',0,'[]'),(76,3,15,'Horror',0,'[]'),(77,3,16,'Martial Arts',0,'[]'),(78,3,17,'Music',0,'[]'),(79,3,18,'Musical',0,'[]'),(80,3,19,'Mystery',0,'[]'),(81,3,20,'News',0,'[]'),(82,3,21,'Non-Fiction',0,'[]'),(83,3,22,'Reality',0,'[]'),(84,3,23,'Romance',0,'[]'),(85,3,24,'Science Fiction',0,'[]'),(86,3,25,'Short',0,'[]'),(87,3,26,'Sport',0,'[]'),(88,3,27,'Talk Show',0,'[]'),(89,3,28,'Thriller',0,'[]'),(90,3,29,'TV Movie',0,'[]'),(91,3,30,'War',0,'[]'),(92,3,31,'Western',0,'[]'),(93,4,1,'Action',0,'[]'),(94,4,2,'Action & Adventure',0,'[]'),(95,4,3,'Adventure',0,'[]'),(96,4,4,'Animation',0,'[]'),(97,4,5,'Anime',0,'[]'),(98,4,6,'Biography',0,'[]'),(99,4,7,'Children',0,'[]'),(100,4,8,'Comedy',0,'[]'),(101,4,9,'Crime',0,'[]'),(102,4,10,'Documentary',0,'[]'),(103,4,11,'Drama',0,'[]'),(104,4,12,'Family',0,'[]'),(105,4,13,'Fantasy',0,'[]'),(106,4,14,'Food',0,'[]'),(107,4,15,'Game Show',0,'[]'),(108,4,16,'History',0,'[]'),(109,4,17,'Home and Garden',0,'[]'),(110,4,18,'Horror',0,'[]'),(111,4,19,'Indie',0,'[]'),(112,4,20,'Martial Arts',0,'[]'),(113,4,21,'Mini-Series',0,'[]'),(114,4,22,'Musical',0,'[]'),(115,4,23,'Mystery',0,'[]'),(116,4,24,'News',0,'[]'),(117,4,25,'Reality',0,'[]'),(118,4,26,'Romance',0,'[]'),(119,4,27,'Science Fiction',0,'[]'),(120,4,28,'Sci-Fi & Fantasy',0,'[]'),(121,4,29,'Soap',0,'[]'),(122,4,30,'Special Interest',0,'[]'),(123,4,31,'Sport',0,'[]'),(124,4,32,'Suspense',0,'[]'),(125,4,33,'Talk',0,'[]'),(126,4,34,'Talk Show',0,'[]'),(127,4,35,'Thriller',0,'[]'),(128,4,36,'Thriller',0,'[]'),(129,4,37,'Travel',0,'[]'),(130,4,38,'War',0,'[]'),(131,4,39,'War & Politics',0,'[]'),(132,4,40,'Western',0,'[]');
/*!40000 ALTER TABLE `watch_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `watch_folders`
--

LOCK TABLES `watch_folders` WRITE;
/*!40000 ALTER TABLE `watch_folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `watch_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `watch_refresh`
--

LOCK TABLES `watch_refresh` WRITE;
/*!40000 ALTER TABLE `watch_refresh` DISABLE KEYS */;
/*!40000 ALTER TABLE `watch_refresh` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-04  0:38:03
