-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: swastholink
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Current Database: `swastholink`
--

/*!40000 DROP DATABASE IF EXISTS `swastholink`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `swastholink` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `swastholink`;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(255) DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctor_profiles`
--

DROP TABLE IF EXISTS `doctor_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doctor_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `hospital_id` bigint(20) unsigned DEFAULT NULL,
  `bmdc_number` varchar(255) NOT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `bmdc_certificate_path` varchar(255) DEFAULT NULL,
  `nid_document_path` varchar(255) DEFAULT NULL,
  `rsa_public_key` text DEFAULT NULL,
  `rsa_private_key_encrypted` text DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doctor_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `doctor_profiles_bmdc_number_unique` (`bmdc_number`),
  KEY `doctor_profiles_hospital_id_foreign` (`hospital_id`),
  KEY `doctor_profiles_verified_by_foreign` (`verified_by`),
  CONSTRAINT `doctor_profiles_hospital_id_foreign` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE SET NULL,
  CONSTRAINT `doctor_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `doctor_profiles_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctor_profiles`
--

LOCK TABLES `doctor_profiles` WRITE;
/*!40000 ALTER TABLE `doctor_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `doctor_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hospitals`
--

DROP TABLE IF EXISTS `hospitals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hospitals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `registration_number` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `license_document_path` varchar(255) DEFAULT NULL,
  `rsa_public_key` text DEFAULT NULL,
  `rsa_private_key_encrypted` text DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hospitals_user_id_unique` (`user_id`),
  UNIQUE KEY `hospitals_registration_number_unique` (`registration_number`),
  KEY `hospitals_verified_by_foreign` (`verified_by`),
  CONSTRAINT `hospitals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hospitals_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hospitals`
--

LOCK TABLES `hospitals` WRITE;
/*!40000 ALTER TABLE `hospitals` DISABLE KEYS */;
/*!40000 ALTER TABLE `hospitals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_08_11_141440_create_hospitals_table',2),(5,'2026_08_11_141441_add_role_and_status_to_users_table',2),(6,'2026_08_11_141441_create_doctor_profiles_table',2),(7,'2026_08_11_141442_create_audit_logs_table',2),(8,'2026_08_11_141442_create_pharmacist_profiles_table',2),(9,'2026_08_11_144131_create_prescriptions_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacist_profiles`
--

DROP TABLE IF EXISTS `pharmacist_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pharmacist_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `pharmacy_name` varchar(255) NOT NULL,
  `pharmacy_license_number` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `license_document_path` varchar(255) DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pharmacist_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `pharmacist_profiles_pharmacy_license_number_unique` (`pharmacy_license_number`),
  KEY `pharmacist_profiles_verified_by_foreign` (`verified_by`),
  CONSTRAINT `pharmacist_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pharmacist_profiles_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacist_profiles`
--

LOCK TABLES `pharmacist_profiles` WRITE;
/*!40000 ALTER TABLE `pharmacist_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `pharmacist_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prescriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lookup_code` varchar(12) NOT NULL,
  `doctor_id` bigint(20) unsigned NOT NULL,
  `hospital_id` bigint(20) unsigned DEFAULT NULL,
  `patient_id` bigint(20) unsigned DEFAULT NULL,
  `patient_name` varchar(255) NOT NULL,
  `patient_email` varchar(255) DEFAULT NULL,
  `medicines` text NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','dispensed') NOT NULL DEFAULT 'active',
  `dispensed_by` bigint(20) unsigned DEFAULT NULL,
  `dispensed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prescriptions_lookup_code_unique` (`lookup_code`),
  KEY `prescriptions_doctor_id_foreign` (`doctor_id`),
  KEY `prescriptions_hospital_id_foreign` (`hospital_id`),
  KEY `prescriptions_patient_id_foreign` (`patient_id`),
  KEY `prescriptions_dispensed_by_foreign` (`dispensed_by`),
  CONSTRAINT `prescriptions_dispensed_by_foreign` FOREIGN KEY (`dispensed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_hospital_id_foreign` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescriptions`
--

LOCK TABLES `prescriptions` WRITE;
/*!40000 ALTER TABLE `prescriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `prescriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('32dph7qiTLAFnoQpL964EWEQGGZftTlKDtLlcMKn',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYk1lQnFOR3FWRmtVMlhzTVRzVlBOa3FiTEFUZ2JvOVZIN3dLMDQySiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786458007),('7HfVJryc8CotSy5MsQo0XOeAI8N9heTxYnu80dX2',NULL,'127.0.0.1','curl/8.17.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRk1zTzFDT0p1cWhwM3ZwTzhrMEVVdUhkeHgwdFVNckVHZjlOS0RKVSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyOToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3Byb2ZpbGUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyOToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3Byb2ZpbGUiO3M6NToicm91dGUiO3M6MTI6InByb2ZpbGUuZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786458438),('8gRc0diCnK4JfwMhv73QDZ7LItkTkNuKcVFKR2TM',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM2pJcklNUENBRkNSdjMyZ1V3eEtvSmJwbmc1VnBiZGozMXI3Z0lLQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786462301),('8VDDcYLMWCE3sNSKk6YbuHr90S9wW2L2lkRBWEiv',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTTc3cURCVFIyOGlzaDhzOVk0bWpkaHUyNzc3SVN0ZGdaMVZwVkNrOCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786459536),('93mMxdTALE0IHLDI65j9KmolGtPhr0XxnR52Zd3f',NULL,'127.0.0.1','curl/8.17.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiM2J4ZVdyeWkxeWVJbDZhQ29MYkpOMm1BRThGQk5nQVh0QmJncktlcyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL2FwcHJvdmFscyI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vYXBwcm92YWxzIjtzOjU6InJvdXRlIjtzOjE1OiJhZG1pbi5hcHByb3ZhbHMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1786458438),('9V2K7yKYBnfxOQWaOAoQ6rCqB06QWSRqKQph9xY6',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWW1EN2hna0ZFcFJ2Nm5aZVZBb0FvU2VBTk5WbGpNV0NwNHRNN250VyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3Rlci9waGFybWFjaXN0IjtzOjU6InJvdXRlIjtzOjE5OiJyZWdpc3Rlci5waGFybWFjaXN0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786462317),('C3ft2sE6EPn8PCovldhM1tHI3Pefs67O9QJCJZuT',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.7632.6 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiclVVRUh6UkRTY1poM3duQlFoYzdvUlBRa2pUdnBCM2dCMUU3R3c2UCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786459544),('ciCgdzQCBl8U5sLjwcCQazvNWUCTAyE2qxmUKzKl',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicml0eEQ1a2dQd3Q1M3hZSTI1dmh6aklBRjRLSTk1N0x4MlR3VE8zTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786458437),('CKZMjWzwnc9j3jadpY3QY9DkWsoLSQDJeRxY44z3',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidFBJemM1UDN1aUVUcWdicW9HeVlxeGU5MzBhYnhLYk5tYldGSWE0bCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786458438),('dsN7rIk8cRv6GlDolcztWMBtAZqWsL0SlmPyN4ec',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.7632.6 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWXd6RjdQU2dMU2loOE1panhZamJURnBlWDByOHBhUnN6QXB2bXlkdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3Rlci9ob3NwaXRhbCI7czo1OiJyb3V0ZSI7czoxNzoicmVnaXN0ZXIuaG9zcGl0YWwiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1786458087),('dtie3ooppfharuLf8OIeS24iT1b2L8gG7zfRFAOx',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTmtuUGFMTGNjRUM0YjAyek9VZjRSSzJ5bTF6elZjRGpXQ1hyN3VEUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3Rlci9kb2N0b3IiO3M6NToicm91dGUiO3M6MTU6InJlZ2lzdGVyLmRvY3RvciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786462316),('EGuzFVqqp6MvOSaTiTbufIyrofZlsSTijCgzDI6x',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOHdWMU9ESzNEcDRkd1FDaTJsRUFIb1dubHpQNnJ0QVVQUWJMcDVwTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786461756),('ep1NbAR59os3v5uCNcSXHkJyBU3D8tTz7BkdMNa4',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWWF5RnNoalZHTXdPZllqcG1uZjd3U3p6dWpsNTBvbFJjdVd2NHVlOCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3RlciI7czo1OiJyb3V0ZSI7czo4OiJyZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786458014),('FGkdlV3xUCoM29PLuwqrXY3dP2gaIplFHrL5maww',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQTVmZHN4Y3ZzOXNaRlZwcmZsV3g1VVBkUXpMZ1BDbHM1MmNHbHdKayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786458013),('FLwjLvmprJy37k9sdZWzSy1dtmNGEaQea7QSy9ua',5,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.7632.6 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiV01JTTJTRDZza2F6c0JlVU5UREhTVWtTRTdIYnRpdkxkYUkyT21HTiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NTt9',1786458817),('ftQYEFe0KFUfSOiMAJj6SaxX2szGr0XqN7v3EWMP',2,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.7632.6 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWEEwc3pBbU8xN3lFSmdsSlIyNXN0dUlmVkJSRDlWekNDcGQxZU1XaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mjt9',1786458236),('gcbUl77XNCCdoVLGN1ETFw0lD7KpxGDzp1Lh1Tjg',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUnl0Ym9Sa3J3bWYxdHNRTkJ2SXQ5M1NkM2pDU2kzeDV6ak5Qa0FtVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3RlciI7czo1OiJyb3V0ZSI7czo4OiJyZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786458437),('hRxgoZtBC3L0kTmTOULcRfax0FYndDtQPxcehpnG',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicnlCSG5PSGIzTHNINDdyaUlBUHNiRTVQb3JQN3lwc0NDU2ZnVFVtbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786458747),('i8vNvXMSh6HKRpyAC6e6UBH84eYvgPqFVsiHY22I',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidkhpdUdxWFdDWnFGWmpSMU9uR0I3WTlTUUtXWjhaU0RPcHMwekVOdyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3Rlci9kb2N0b3IiO3M6NToicm91dGUiO3M6MTU6InJlZ2lzdGVyLmRvY3RvciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786458016),('jPrINE5gkWyTbMrYjAtuPn0Hqd7FVhYvvQIAp8AT',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ2VRNU9WdHR5dVpCcE5ZdGhkVmduZnNwUVhBZVVDdmQxbFA1UEQzdCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3Rlci9ob3NwaXRhbCI7czo1OiJyb3V0ZSI7czoxNzoicmVnaXN0ZXIuaG9zcGl0YWwiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1786462316),('kmsW2NMAeVMaxdvTep8f5Q8R1iF3aAKP3qO5msTZ',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.7632.6 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNlRNM2NMVmpxTVNXWEhPRHhDemdoY3cycUU2RndnWlA3V0hkWGt4NSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9hcHByb3ZhbHMiO3M6NToicm91dGUiO3M6MTU6ImFkbWluLmFwcHJvdmFscyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==',1786461870),('lGy3d52hTrWxRpcBF0yrhxQWyqfBslYwoHDdYTWU',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMEZtV0ZXN1FLRUFYdWhvRTk2a0JOUndTNll0OE9ackt5V09XQVBONiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3Rlci9kb2N0b3IiO3M6NToicm91dGUiO3M6MTU6InJlZ2lzdGVyLmRvY3RvciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786458437),('NEtU5VJRCDIYjFZaWttBX2KHMioKJjj4OR9pJ81i',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVm9vU0E4OVgyRW9QM0dkbjN3VFJ0c0Y3dUlRblFQcVNRY1lVV3ZSeiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786462293),('nixJNG9Dhi9jEs97ceF4oq0jKq4YaXS3JyQ9JKSE',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.7632.6 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiNDFmVHZHdnBjbUN4a2U2MEd1WUdZR1ZSY09OOEl2ekNOY1FHQVFGRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3Rlci9kb2N0b3IiO3M6NToicm91dGUiO3M6MTU6InJlZ2lzdGVyLmRvY3RvciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786458063),('noTbLuAQwWWk3qNAh4bB3tl7FEdDaTGODYu9qo0U',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.7632.6 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ1g5Yno4OVl6NjMzNEJoNTQ1NGNrRXNmeThGVFdac2kza2V2NkhLZyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3Rlci9kb2N0b3IiO3M6NToicm91dGUiO3M6MTU6InJlZ2lzdGVyLmRvY3RvciI7fX0=',1786462904),('OyxiNDJNQylzbS81FbCjST3XRquEoyFrAC2gdeYx',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicUZadG9FZGQxOGJ5dzhHWllZVjRXT04wN3BKVTBVYnNmU1F4N3YxYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786462315),('qqHy7TsxQPv4UUoIIx4bVSJ3Cvs8pr1atUPqTlLk',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTFZpalNaV0xtQkZsclk2RlhoMXk2aVhaMEprT2ZPdzBVQkNVc3UyTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786462315),('QRCBhRDHxQr9bMm88m3fzEcwCdGTiAzw29WR5Ek3',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOUNjZHN4OUdBMHNSMUhKSlRPM0xqZnRUanJ5SlpXOWVUV1I1bWtPRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3RlciI7czo1OiJyb3V0ZSI7czo4OiJyZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786462316),('RgK4OC3A9LxHYvf4NqGTXGrErUtwigaLtEV4Lj1T',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiamRvd3I0cXFnWkZGaDFibHg3UjZIclRaZ0g3cmFaVWxBTzNHZGpMUiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3Rlci9ob3NwaXRhbCI7czo1OiJyb3V0ZSI7czoxNzoicmVnaXN0ZXIuaG9zcGl0YWwiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1786458437),('SHhsbZIMfvRbbiG5TwKgs423lQsjHKmrvGKK5HJF',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.7632.6 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaFRBN2Q2TGU5R2dsNFdyMUVEcnZkYlRvV0J3UVM4dThDVUhiOGRhRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786458446),('TDjioTUpmcczRo45XBUR9MiGLySb9J2NWUPCR9ch',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiU1d3dFNXdEZiNnRMSFB4bkV6Mk5LM2t1RmRuQkN6VmxjZTMySHcxRSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786458432),('tJiMZreZ5SlOdXmHMmAg9sLlUgv1zYd754oQHIkb',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTDkzZ3ZOOW80S2VjdVA1WjJTdm5CMzRaeTQ5REhEMHBLelZ5T1ZlZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786458016),('TpOtDJmavLqIGw3Nlrnlxape1qmBrVs806ESTLwv',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.7632.6 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM04yWVhEOTJ3UEhQdXVaYnZ6NkhnMlc0TkFRa25JQlVncDRvTDNVSyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3Rlci9waGFybWFjaXN0IjtzOjU6InJvdXRlIjtzOjE5OiJyZWdpc3Rlci5waGFybWFjaXN0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786458075),('ue6B9iQ0jyIOtZa7Df2AQsM3wzueVm4fC1qyPGMP',NULL,'127.0.0.1','curl/8.17.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoia05TYjE2QkhuQ3ZBdXVudUgyd2VLamVPbVFFV1hkVG9jVTFFMW92ZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3Rlci9waGFybWFjaXN0IjtzOjU6InJvdXRlIjtzOjE5OiJyZWdpc3Rlci5waGFybWFjaXN0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1786458438),('UI4s3X70hkZFMRQS3RjIkaobbbVKC5sjdi8FSGdn',NULL,'127.0.0.1','curl/8.17.0','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTVY3cHV6aWw5OGE4ejVzUnYwbGhOUnJ1cVdKd2JOSWh4dkIxYW9kNiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1786458438),('VdgpXouMeY7uaDmYPwWyoeuuuBLiROd8C397BmFU',6,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.7632.6 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNERiSlpPMURYZ3ZkaGxlVHRvQnA0WFFXMDBoSlpqTTJUQXFqYWlTcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ob3NwaXRhbCI7czo1OiJyb3V0ZSI7czoxODoiaG9zcGl0YWwuZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Njt9',1786460065),('xqoe3ouBXNJm5pPTMmKvSZtd3wcky0yaJ5YrSbCs',4,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/145.0.7632.6 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUUZxa3pzbWE0SEQxZUxpd21rTHBJSGxjdXFxamU2d2FoWFFFa3ZtYyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9wZW5kaW5nLWFwcHJvdmFsIjtzOjU6InJvdXRlIjtzOjE2OiJwZW5kaW5nLWFwcHJvdmFsIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDt9',1786458688);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','hospital','doctor','patient','pharmacist') NOT NULL DEFAULT 'patient',
  `status` enum('pending','active','rejected') NOT NULL DEFAULT 'active',
  `phone` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'SwasthoLink Admin','admin@swastholink.test','admin','active',NULL,'2026-08-11 08:19:46','$2y$12$fnwT9kgo1MS1EeWZe74pruUbYdO5GIMlS3gnayWMIKVdy2V3Byl6u',NULL,'2026-08-11 08:19:46','2026-08-11 08:19:46');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'swastholink'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-11 22:19:23
