-- ============================================================
-- Oriental Muay Boran Academy - Database Backup
-- Generated: 2026-02-18 18:15:12
-- Database: oma_database
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;
CREATE DATABASE IF NOT EXISTS `oma_database` DEFAULT CHARACTER SET utf8mb4;
USE `oma_database`;

-- ------------------------------------------------------------
-- Table: `affiliates`
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `affiliates`;
CREATE TABLE `affiliates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `logo_path` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `website_url` varchar(500) DEFAULT NULL,
  `facebook_url` varchar(500) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `affiliates`
INSERT INTO `affiliates` VALUES ('2', 'E.L. Kubo', 'assets/uploads/affiliates/6967aaae9de56_1768401582.png', 'Partner martial arts school', 'https://www.facebook.com/profile.php?id=61582707795581', 'https://www.facebook.com/profile.php?id=61582707795581', '', '', '2', 'active', '2026-01-14 22:11:48', '2026-01-20 22:45:06');
INSERT INTO `affiliates` VALUES ('4', 'Cor3 Solutions', 'assets/uploads/affiliates/6967aac0413ad_1768401600.png', 'Corporate training partner', 'https://www.facebook.com/Cor3Solutions', 'https://www.facebook.com/Cor3Solutions', '', '', '4', 'active', '2026-01-14 22:11:48', '2026-01-20 22:45:29');
INSERT INTO `affiliates` VALUES ('7', 'Kru Muay Thai Association', 'assets/uploads/affiliates/696f949e07cfd_1768920222.jpg', '', 'https://www.facebook.com/Krumuaythaiassociation', 'https://www.facebook.com/Krumuaythaiassociation', 'Mareljadesalvador@gmail.com', '09939031007', '0', 'active', '2026-01-20 22:43:42', '2026-01-22 15:45:00');
INSERT INTO `affiliates` VALUES ('8', 'MMBC Combat Hunters Tribe', 'assets/uploads/affiliates/697d4f9357c09_1769820051.jpg', '', '', '', '', '', '0', 'active', '2026-01-31 08:40:51', '2026-01-31 08:40:51');
INSERT INTO `affiliates` VALUES ('9', 'Silverback MMA and Fitness', 'assets/uploads/affiliates/697d4fa8432e5_1769820072.jpg', '', '', '', '', '', '0', 'active', '2026-01-31 08:41:12', '2026-01-31 08:41:12');
INSERT INTO `affiliates` VALUES ('10', 'KUSOG', 'assets/uploads/affiliates/697d4fbe0a38a_1769820094.jpg', '', '', '', '', '', '0', 'active', '2026-01-31 08:41:34', '2026-01-31 08:41:34');
INSERT INTO `affiliates` VALUES ('11', 'CMBT Club', 'assets/uploads/affiliates/697d4fd23e75f_1769820114.jpg', '', '', '', '', '', '0', 'active', '2026-01-31 08:41:54', '2026-01-31 08:41:54');
INSERT INTO `affiliates` VALUES ('12', 'Brawlers Lab', 'assets/uploads/affiliates/6988d2cfda01e_1770574543.png', '', '', '', '', '', '0', 'active', '2026-02-09 02:15:43', '2026-02-09 02:16:23');
INSERT INTO `affiliates` VALUES ('13', 'Combat Hunters Tribe', 'assets/uploads/affiliates/6988d314875a4_1770574612.png', '', '', '', '', '', '0', 'active', '2026-02-09 02:16:52', '2026-02-09 02:16:52');
INSERT INTO `affiliates` VALUES ('14', 'Bataan Chapter MuayThai', 'assets/uploads/affiliates/6988d33017083_1770574640.png', '', '', '', '', '', '0', 'active', '2026-02-09 02:17:20', '2026-02-09 02:17:20');

-- ------------------------------------------------------------
-- Table: `contact_messages`
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','archived') DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (no data)

-- ------------------------------------------------------------
-- Table: `course_materials`
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `course_materials`;
CREATE TABLE `course_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('beginner','intermediate','advanced','instructor','weapon') NOT NULL,
  `khan_level_min` int(11) DEFAULT 1,
  `khan_level_max` int(11) DEFAULT 16,
  `file_path` varchar(500) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_public` tinyint(1) DEFAULT 0,
  `status` enum('draft','published','archived') DEFAULT 'published',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_category` (`category`),
  KEY `idx_khan_level` (`khan_level_min`,`khan_level_max`),
  KEY `idx_status` (`status`),
  CONSTRAINT `course_materials_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `course_materials`
INSERT INTO `course_materials` VALUES ('1', 'Khan 1: Fundamentals', 'Vital points of the body, Muaythai History 1 (Origin/Traditions), and basic stances (Yuen Duen).', 'beginner', '1', '1', 'assets/uploads/courses/6988db6983f81_1770576745.pdf', 'application/pdf', '', 'assets/uploads/courses/697cb9157a653_1769781525.png', '6', '1', '1', 'published', NULL, '2026-01-29 13:31:34', '2026-02-09 02:52:25');
INSERT INTO `course_materials` VALUES ('2', 'Khan 2: Basic Striking', 'Muaythai History 2 (Evolution), Arts of Punches (Silapa Gan Chai Maht), and Sitting Wai Kru 1.', 'beginner', '2', '2', NULL, '', '', NULL, '24', '2', '1', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:44:53');
INSERT INTO `course_materials` VALUES ('3', 'Khan 3: Combat Foundation', 'Insight Meditation, Arts of Knee strikes, Elbow strikes, Clinching, and Sitting Wai Kru 2.', 'beginner', '3', '3', NULL, '', '', NULL, '35', '3', '1', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:44:58');
INSERT INTO `course_materials` VALUES ('4', 'Khan 4: Defensive Tactics', 'History of King Naresuan, defensive tactics, and counter attacks for punches, kicks, and knees.', 'intermediate', '4', '4', NULL, '', '', NULL, '45', '4', '1', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:45:05');
INSERT INTO `course_materials` VALUES ('5', 'Khan 5: Counter Mastery', 'History of Prachao Suea, advanced counter attacks for elbows and clinching, and Standing Wai Kru 4.', 'intermediate', '5', '5', NULL, '', '', NULL, '41', '5', '1', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:45:11');
INSERT INTO `course_materials` VALUES ('6', 'Khan 6: Amateur Practicum', 'Intensive training for amateur athletes including full sparring or tournament fights.', 'intermediate', '6', '6', NULL, '', '', NULL, '120', '6', '1', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:45:16');
INSERT INTO `course_materials` VALUES ('7', 'Khan 7: Professional Practicum', 'Professional athlete training and fight practicum in professional tournaments.', 'advanced', '7', '7', NULL, '', '', NULL, '120', '7', '1', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:45:21');
INSERT INTO `course_materials` VALUES ('8', 'Khan 8: Warrior Dance (Punches/Kicks)', 'Life of Phraya Pichai and Warrior Dances (Ram Muay) for Punches and Kicks.', 'advanced', '8', '8', NULL, '', '', NULL, '40', '8', '1', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:45:26');
INSERT INTO `course_materials` VALUES ('9', 'Khan 9: Warrior Dance (Knees/Elbows)', 'Life of Nai Khanom Tom and Warrior Dances (Ram Muay) for Knee and Elbow strikes.', 'advanced', '9', '9', NULL, '', '', NULL, '35', '9', '1', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:45:32');
INSERT INTO `course_materials` VALUES ('10', 'Khan 10: Muaythai in Philippines', 'History of Muaythai in the Philippines, Major (Mae Mai) and Minor techniques.', 'advanced', '10', '10', NULL, '', '', NULL, '46', '10', '1', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:45:53');
INSERT INTO `course_materials` VALUES ('11', 'Khan 11: Instructor Pedagogy 1', 'Nutrition, Thai Massage, Anatomy, and teaching methodologies for groups and young learners.', 'instructor', '11', '11', NULL, '', '', NULL, '66', '11', '0', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:45:59');
INSERT INTO `course_materials` VALUES ('12', 'Khan 12: Instructor Pedagogy 2', 'First Aid, Trainer duties, equipment management, and teaching with moving targets.', 'instructor', '12', '12', NULL, '', '', NULL, '63', '12', '0', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:46:05');
INSERT INTO `course_materials` VALUES ('13', 'Khan 13: Officiating &amp; Ethics', 'Code of conduct for officials, refereeing, and judging for amateur and professional bouts.', 'instructor', '13', '13', NULL, '', '', NULL, '14', '13', '0', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:46:17');
INSERT INTO `course_materials` VALUES ('14', 'Khan 14: Ancient Weaponry', 'Ancient Thai practice of weaponry: Battleaxe (Kwan), Dagger, Spear, Rattan Stick, and Shield.', 'weapon', '14', '14', NULL, '', '', NULL, '97', '14', '0', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:46:45');
INSERT INTO `course_materials` VALUES ('15', 'Khan 15: Management &amp; Promotion', 'Methods of marketing Muaythai, project proposal writing, and implementation practicum.', 'instructor', '15', '15', NULL, '', '', NULL, '4', '15', '0', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:46:22');
INSERT INTO `course_materials` VALUES ('16', 'Khan 16: Assessment &amp; Upgrading', 'Yearly re-assessment, training seminars on new techniques, and conference presentations.', 'instructor', '16', '16', NULL, '', '', NULL, '8', '16', '0', 'published', NULL, '2026-01-29 13:31:34', '2026-01-29 13:46:37');

-- ------------------------------------------------------------
-- Table: `event_gallery`
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `event_gallery`;
CREATE TABLE `event_gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `image_path` varchar(500) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`category`),
  KEY `idx_order` (`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `event_gallery`
INSERT INTO `event_gallery` VALUES ('1', 'Unarmed Defense Training', 'Unarmed defense is not about aggression, it\'s about control, confidence, and readiness. We teach practitioners how to manage threats using the mechanical advantages found in traditional Muay Thai.', NULL, NULL, 'assets/images/unarmed.png', 'Training', '1', 'active', '2026-01-14 22:11:48', '2026-01-14 22:11:48');
INSERT INTO `event_gallery` VALUES ('2', 'MCJFD Personnel Empowerment', 'Manila, Philippines—The Manila City Jail Female Dormitory conducted a Seminar on Muay Thai-based Unarmed Self-Defense. This enhanced the capability and preparedness of personnel in managing critical situations without weapons.', NULL, NULL, 'assets/images/mcjfd.png', 'Seminar', '2', 'active', '2026-01-14 22:11:48', '2026-01-14 22:11:48');
INSERT INTO `event_gallery` VALUES ('3', 'QCJFD Unarmed Combat Skills', 'Isinagawa sa Quezon City Jail Female Dormitory ang pagsasanay sa Muay Thai. Layunin nito na palakasin ang kakayahan ng mga kawani sa pagtatanggol sa sarili bilang bahagi ng kanilang propesyonal na tungkulin.', NULL, NULL, 'assets/images/QCJFD .png', 'Training', '3', 'active', '2026-01-14 22:11:48', '2026-01-14 22:11:48');
INSERT INTO `event_gallery` VALUES ('4', 'Safety & Facility Order', 'Mahalaga ang pagtuturo ng Muay Thai sa mga personnel dahil nakatutulong ito upang maging handa sila sa anumang sitwasyong maaaring magbanta sa kanilang kaligtasan at mapanatili ang kaayusan ng pasilidad.', NULL, NULL, 'assets/images/Safety.png', 'Institutional', '4', 'active', '2026-01-14 22:11:48', '2026-01-14 22:11:48');
INSERT INTO `event_gallery` VALUES ('5', 'test', 'asd', '2026-01-20', '', 'assets/uploads/events/69773d15df424_1769422101.png', '', '0', 'active', '2026-01-26 18:00:25', '2026-01-26 18:08:21');

-- ------------------------------------------------------------
-- Table: `event_photos`
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `event_photos`;
CREATE TABLE `event_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- (no data)

-- ------------------------------------------------------------
-- Table: `instructors`
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `instructors`;
CREATE TABLE `instructors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `photo_path` varchar(500) DEFAULT NULL,
  `khan_level` varchar(50) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `specialization` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `facebook_url` varchar(500) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_khan_level` (`khan_level`),
  KEY `idx_order` (`display_order`),
  CONSTRAINT `instructors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `instructors`
INSERT INTO `instructors` VALUES ('18', '2', 'Rusha Bayacsan', 'assets/uploads/instructors/697ae8a9ace99_1769662633.png', '11', '', '', '', '', '', 'Rusha@gmail.com', '', '0', 'active', '2026-01-29 12:57:13', '2026-01-29 12:57:13');
INSERT INTO `instructors` VALUES ('19', '3', 'Alvin Adolfo', 'assets/uploads/instructors/697ae8f5519b1_1769662709.png', '11', '', '', 'camp: ----\r\naddress: qc\r\nyear started muaythai: 2013\r\nmma: karate 1st done black belt, taekwondo 1st done kukkiwon black, kickboxing\r\nachievements: champion karate year 2016, tkd 1st place 2013\r\ncontact 09056609386 smart 09204632175', 'camp: ----\r\naddress: qc\r\nyear started muaythai: 2013\r\nmma: karate 1st done black belt, taekwondo 1st done kukkiwon black, kickboxing\r\nachievements: champion karate year 2016, tkd 1st place 2013\r\ncontact 09056609386 smart 09204632175', '', '08alvincadolfo@gmail.com', '', '0', 'active', '2026-01-29 12:58:29', '2026-01-30 19:58:20');
INSERT INTO `instructors` VALUES ('20', '4', 'Vincent Hisona', 'assets/uploads/instructors/697aec852bfbb_1769663621.png', '11', '', '', '', '', '', 'Vincent@gmail.com', '', '0', 'active', '2026-01-29 13:11:38', '2026-01-29 13:13:41');
INSERT INTO `instructors` VALUES ('21', '5', 'Roberto Serdone Jr.', 'assets/uploads/instructors/697aec4fce407_1769663567.png', '11', '', '', '', '', '', 'roberto@gmail.com', '', '0', 'active', '2026-01-29 13:12:47', '2026-01-30 14:12:48');
INSERT INTO `instructors` VALUES ('22', '6', 'Joseph Vincent Lim', 'assets/uploads/instructors/697aed4713434_1769663815.png', '11', '', '', '', '', '', 'Joseph@gmail.com', '', '0', 'active', '2026-01-29 13:16:55', '2026-01-29 13:16:55');
INSERT INTO `instructors` VALUES ('23', '7', 'John Vincent Miraflor', 'assets/uploads/instructors/697aed8773f3b_1769663879.png', '11', '', '', '', '', '', 'John@gmail.com', '', '0', 'active', '2026-01-29 13:17:59', '2026-01-29 13:17:59');
INSERT INTO `instructors` VALUES ('24', '8', 'Fredlyn Sayod-Miraflor', 'assets/uploads/instructors/697aedc5e4515_1769663941.png', '11', '', '', '', '', '', 'Fredlyn@gmail.com', '', '0', 'active', '2026-01-29 13:19:01', '2026-01-29 13:19:01');
INSERT INTO `instructors` VALUES ('25', '9', 'Rho Fajutra', 'assets/uploads/instructors/697aedece55dd_1769663980.png', '11', '', '', '', '', '', 'Rho@gmail.com', '', '0', 'active', '2026-01-29 13:19:40', '2026-01-29 13:19:40');
INSERT INTO `instructors` VALUES ('26', '10', 'Krisna Limbaga', 'assets/uploads/instructors/697aee088c105_1769664008.png', '11', '', '', '', '', '', 'krisna@gmail.com', '', '0', 'active', '2026-01-29 13:20:08', '2026-01-29 13:20:08');
INSERT INTO `instructors` VALUES ('27', '11', 'Art Pantinople', 'assets/uploads/instructors/697aee2919ce4_1769664041.png', '11', '', '', '', '', '', 'art@gmail.com', '', '0', 'active', '2026-01-29 13:20:41', '2026-01-29 13:20:41');
INSERT INTO `instructors` VALUES ('28', '12', 'Felixander Bagayao', 'assets/uploads/instructors/697aee4f29630_1769664079.png', '11', '', '', '', '', '', 'Felixander@gmail.com', '', '0', 'active', '2026-01-29 13:21:19', '2026-01-29 13:21:19');
INSERT INTO `instructors` VALUES ('29', '13', 'Michael Rimando', 'assets/uploads/instructors/697aee6b17374_1769664107.png', '11', '', '', 'camp: nakmuay camp\r\naddress: Benguet \r\nyear started muaythai: 2015\r\nmma: boxing, kickboxing\r\nachievements: multiple national muaythai championship medalist \r\neducation: \r\ncontact: 09760612206', '', '', 'Michael@gmail.com', '', '0', 'active', '2026-01-29 13:21:47', '2026-01-30 19:18:05');
INSERT INTO `instructors` VALUES ('30', '14', 'Ricardo Forlales', 'assets/uploads/instructors/697aee8ac1173_1769664138.png', '11', '', '', '', '', '', 'Ricardo@gmail.com', '', '0', 'active', '2026-01-29 13:22:18', '2026-01-29 13:22:18');
INSERT INTO `instructors` VALUES ('31', '15', 'Vernie Garcia', 'assets/uploads/instructors/697aeea6d43bd_1769664166.png', '11', '', '', 'camp: Oriental Muayboran Academy\r\naddress: CALABARZON, MIMAROPA, NCR\r\nyear started muaythai: 2004\r\nmma: boxing, kickboxing\r\nachievements: multiple boxing and muaythai medalist', '', '', 'Vernie@gmail.com', '', '0', 'active', '2026-01-29 13:22:46', '2026-01-30 21:50:26');

-- ------------------------------------------------------------
-- Table: `khan_colors`
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `khan_colors`;
CREATE TABLE `khan_colors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `khan_level` int(11) NOT NULL,
  `color_name` varchar(50) NOT NULL,
  `color_code` varchar(20) DEFAULT NULL,
  `hex_color` varchar(7) DEFAULT NULL,
  `is_instructor_level` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `khan_level` (`khan_level`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `khan_colors`
INSERT INTO `khan_colors` VALUES ('1', '1', 'White', 'white', '#FFFFFF', '0', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('2', '2', 'Yellow', 'yellow', '#FFD700', '0', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('3', '3', 'Yellow-White', 'yellow-white', '#FFFACD', '0', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('4', '4', 'Green', 'green', '#00A651', '0', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('5', '5', 'Green-White', 'green-white', '#90EE90', '0', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('6', '6', 'Blue', 'blue', '#0066CC', '0', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('7', '7', 'Blue-White', 'blue-white', '#87CEEB', '0', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('8', '8', 'Brown', 'brown', '#8B4513', '0', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('9', '9', 'Brown-White', 'brown-white', '#D2B48C', '0', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('10', '10', 'Red', 'red', '#DC143C', '0', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('11', '11', 'Red-White', 'red-white', '#FFB6C1', '1', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('12', '12', 'Red-Yellow', 'red-yellow', '#FF6B35', '1', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('13', '13', 'Red-Silver', 'red-silver', '#C0C0C0', '1', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('14', '14', 'Silver', 'silver', '#C0C0C0', '1', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('15', '15', 'Silver-Gold', 'silver-gold', '#D4AF37', '1', '2026-01-26 19:32:41');
INSERT INTO `khan_colors` VALUES ('16', '16', 'Gold', 'gold', '#FFD700', '1', '2026-01-26 19:32:41');

-- ------------------------------------------------------------
-- Table: `khan_level_changes`
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `khan_level_changes`;
CREATE TABLE `khan_level_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `from_level` int(11) DEFAULT NULL,
  `to_level` int(11) NOT NULL,
  `change_date` date NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `became_instructor` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `changed_by` (`changed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table `khan_level_changes`
INSERT INTO `khan_level_changes` VALUES ('1', '4', '1', '5', '2026-02-18', NULL, NULL, '0', '2026-02-18 04:33:19');
INSERT INTO `khan_level_changes` VALUES ('2', '5', '1', '5', '2026-02-18', NULL, NULL, '0', '2026-02-18 04:33:34');
INSERT INTO `khan_level_changes` VALUES ('3', '7', '1', '5', '2026-02-18', NULL, NULL, '0', '2026-02-18 04:33:39');
INSERT INTO `khan_level_changes` VALUES ('4', '24', '3', '5', '2026-02-18', NULL, NULL, '0', '2026-02-18 23:30:48');
INSERT INTO `khan_level_changes` VALUES ('5', '140', '1', '2', '2026-02-18', NULL, NULL, '0', '2026-02-18 23:41:15');

-- ------------------------------------------------------------
-- Table: `khan_members`
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `khan_members`;
CREATE TABLE `khan_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `photo_path` varchar(500) DEFAULT NULL,
  `current_khan_level` int(11) DEFAULT 1,
  `khan_color` varchar(50) DEFAULT NULL,
  `date_joined` date NOT NULL,
  `date_promoted` date DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `training_location` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','refresher') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `instructor_id` (`instructor_id`),
  KEY `idx_khan_level` (`current_khan_level`),
  KEY `idx_status` (`status`),
  KEY `idx_email` (`email`),
  CONSTRAINT `khan_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `khan_members_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `khan_members`
INSERT INTO `khan_members` VALUES ('1', NULL, 'test', 'test@gmail.com', '', NULL, '1', 'White', '2026-01-08', '0000-00-00', '19', 'LA TRINIDAD, BENGUET', 'active', '', '2026-01-29 13:50:25', '2026-01-29 13:50:25');
INSERT INTO `khan_members` VALUES ('2', '16', 'Erexter Taligan', 'erexter07@gmail.com', '', NULL, '1', 'White', '2026-01-14', NULL, '29', 'OMA HQ', 'active', 'Philippians 4:13 I can do all things through Christ who strengthen me.', '2026-01-30 19:15:39', '2026-01-31 09:05:04');
INSERT INTO `khan_members` VALUES ('4', NULL, 'Armando Solar', 'armando-solar_1771358298323@archive.local', '', NULL, '5', 'Green-White', '2016-10-29', '2016-11-06', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 03:58:18', '2026-02-18 04:33:19');
INSERT INTO `khan_members` VALUES ('5', NULL, 'Jay Harold Gregorio', 'jay-harold-gregorio_1771358306414@archive.local', '', NULL, '5', 'Green-White', '2016-10-29', '2016-11-06', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 03:58:26', '2026-02-18 04:33:34');
INSERT INTO `khan_members` VALUES ('6', NULL, 'Jerry Valdez', 'jerry-valdez_1771358314322@archive.local', '', NULL, '1', 'White', '2016-10-29', '2016-10-29', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 03:58:34', '2026-02-18 03:58:34');
INSERT INTO `khan_members` VALUES ('7', NULL, 'Jonathan Polosan', 'jonathan-polosan_1771358322488@archive.local', '', NULL, '5', 'Green-White', '2016-10-29', '2016-11-06', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 03:58:42', '2026-02-18 04:33:39');
INSERT INTO `khan_members` VALUES ('8', NULL, 'Philip Delarmino', 'philip-delarmino_1771358335718@archive.local', '', NULL, '1', 'White', '2016-10-29', '2016-10-29', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 03:58:55', '2026-02-18 03:58:55');
INSERT INTO `khan_members` VALUES ('9', NULL, 'Presious Ocaya', 'presious-ocaya_1771358343923@archive.local', '', NULL, '1', 'White', '2016-10-29', '2016-10-29', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 03:59:03', '2026-02-18 03:59:03');
INSERT INTO `khan_members` VALUES ('10', NULL, 'Roland Claro', 'roland-claro_1771358365735@archive.local', '', NULL, '1', 'White', '2016-10-29', '2016-10-29', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 03:59:25', '2026-02-18 03:59:25');
INSERT INTO `khan_members` VALUES ('11', NULL, 'Rusha Mae Bayacsan', 'rusha-mae-bayacsan_1771358369143@archive.local', '', NULL, '1', 'White', '2016-10-29', '2016-10-29', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 03:59:29', '2026-02-18 03:59:29');
INSERT INTO `khan_members` VALUES ('12', NULL, 'Cedrick Wasit', 'cedrick-wasit_1771358466933@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:06', '2026-02-18 04:01:06');
INSERT INTO `khan_members` VALUES ('13', NULL, 'Daniel Gumilao', 'daniel-gumilao_1771358469270@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:09', '2026-02-18 04:01:09');
INSERT INTO `khan_members` VALUES ('14', NULL, 'Edgar Oya', 'edgar-oya_1771358473458@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:13', '2026-02-18 04:01:13');
INSERT INTO `khan_members` VALUES ('15', NULL, 'Farlein Sagana', 'farlein-sagana_1771358476296@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:16', '2026-02-18 04:01:16');
INSERT INTO `khan_members` VALUES ('16', NULL, 'Gringo Navarro', 'gringo-navarro_1771358479491@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:19', '2026-02-18 04:01:19');
INSERT INTO `khan_members` VALUES ('17', NULL, 'Jessica O. Amos', 'jessica-o-amos_1771358489564@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:29', '2026-02-18 04:01:29');
INSERT INTO `khan_members` VALUES ('18', NULL, 'John Cay-ohen', 'john-cay-ohen_1771358493507@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:33', '2026-02-18 04:01:33');
INSERT INTO `khan_members` VALUES ('19', NULL, 'Josaphat Navarro', 'josaphat-navarro_1771358496189@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:36', '2026-02-18 04:01:36');
INSERT INTO `khan_members` VALUES ('20', NULL, 'Lou Roa', 'lou-roa_1771358499507@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:39', '2026-02-18 04:01:39');
INSERT INTO `khan_members` VALUES ('21', NULL, 'Luisito Mateo', 'luisito-mateo_1771358502321@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:42', '2026-02-18 04:01:42');
INSERT INTO `khan_members` VALUES ('22', NULL, 'Martina Apostol', 'martina-apostol_1771358506715@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:46', '2026-02-18 04:01:46');
INSERT INTO `khan_members` VALUES ('23', NULL, 'Michael Franco', 'michael-franco_1771358512841@archive.local', '', NULL, '3', 'Yellow-White', '2016-11-10', '2016-11-10', NULL, 'LA TRINIDAD, BENGUET', 'active', 'Manually encoded', '2026-02-18 04:01:52', '2026-02-18 04:01:52');
INSERT INTO `khan_members` VALUES ('24', NULL, 'Michael Rimando', 'michael-rimando_1771358517345@archive.local', '', NULL, '5', 'Green-White', '2016-11-10', '2016-11-06', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:01:57', '2026-02-18 23:30:48');
INSERT INTO `khan_members` VALUES ('25', NULL, 'Aldrich S. Nicdao', 'aldrich-s-nicdao_1771358545845@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:02:25', '2026-02-18 04:02:25');
INSERT INTO `khan_members` VALUES ('26', NULL, 'Aliecarl Aron D. Tolentino', 'aliecarl-aron-d-tolentino_1771358549171@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:02:29', '2026-02-18 04:02:29');
INSERT INTO `khan_members` VALUES ('27', NULL, 'Alvin Jose Adolfo', 'alvin-jose-adolfo_1771358558517@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:02:38', '2026-02-18 04:02:38');
INSERT INTO `khan_members` VALUES ('28', NULL, 'Andre Rainier Fuggan', 'andre-rainier-fuggan_1771358560505@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:02:40', '2026-02-18 04:02:40');
INSERT INTO `khan_members` VALUES ('29', NULL, 'April May S. Bonoan', 'april-may-s-bonoan_1771358563443@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:02:43', '2026-02-18 04:02:43');
INSERT INTO `khan_members` VALUES ('30', NULL, 'Arlene Conlu', 'arlene-conlu_1771358567755@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:02:47', '2026-02-18 04:02:47');
INSERT INTO `khan_members` VALUES ('31', NULL, 'Arlyn D. Ranese', 'arlyn-d-ranese_1771358570160@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:02:50', '2026-02-18 04:02:50');
INSERT INTO `khan_members` VALUES ('32', NULL, 'Arnel Sustento', 'arnel-sustento_1771358573486@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:02:53', '2026-02-18 04:02:53');
INSERT INTO `khan_members` VALUES ('33', NULL, 'Art Pantinople', 'art-pantinople_1771358577834@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:02:57', '2026-02-18 04:02:57');
INSERT INTO `khan_members` VALUES ('34', NULL, 'Benjamin Bragado', 'benjamin-bragado_1771358580673@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:03:00', '2026-02-18 04:03:00');
INSERT INTO `khan_members` VALUES ('35', NULL, 'Chryster N. Terado', 'chryster-n-terado_1771358584136@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:03:04', '2026-02-18 04:03:04');
INSERT INTO `khan_members` VALUES ('36', NULL, 'Cristina D. Condrado', 'cristina-d-condrado_1771358588523@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:03:08', '2026-02-18 04:03:08');
INSERT INTO `khan_members` VALUES ('37', NULL, 'Darrel Felonia', 'darrel-felonia_1771358593542@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:03:13', '2026-02-18 04:03:13');
INSERT INTO `khan_members` VALUES ('38', NULL, 'Edsel G. Vengco', 'edsel-g-vengco_1771358597545@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:03:17', '2026-02-18 04:03:17');
INSERT INTO `khan_members` VALUES ('39', NULL, 'Edward Pagonzaga', 'edward-pagonzaga_1771358601310@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:03:21', '2026-02-18 04:03:21');
INSERT INTO `khan_members` VALUES ('40', NULL, 'Erick M. Angyab', 'erick-m-angyab_1771358605601@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:03:25', '2026-02-18 04:03:25');
INSERT INTO `khan_members` VALUES ('41', NULL, 'Erwin Pamplona', 'erwin-pamplona_1771358608909@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:03:28', '2026-02-18 04:03:28');
INSERT INTO `khan_members` VALUES ('42', NULL, 'Ferlin B. Mercurio', 'ferlin-b-mercurio_1771358611492@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:03:31', '2026-02-18 04:03:31');
INSERT INTO `khan_members` VALUES ('43', NULL, 'Fredelyn Sayod', 'fredelyn-sayod_1771358732651@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:05:32', '2026-02-18 04:05:32');
INSERT INTO `khan_members` VALUES ('44', NULL, 'Garry Albert Zarandona', 'garry-albert-zarandona_1771358738218@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:05:38', '2026-02-18 04:05:38');
INSERT INTO `khan_members` VALUES ('45', NULL, 'Gilbert L. Mendez', 'gilbert-l-mendez_1771358751335@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:05:51', '2026-02-18 04:05:51');
INSERT INTO `khan_members` VALUES ('46', NULL, 'Harry G. Austria', 'harry-g-austria_1771358755111@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:05:55', '2026-02-18 04:05:55');
INSERT INTO `khan_members` VALUES ('47', NULL, 'Ivan Priel Saganahay', 'ivan-priel-saganahay_1771358759219@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:05:59', '2026-02-18 04:05:59');
INSERT INTO `khan_members` VALUES ('48', NULL, 'James Bayan C. Sanoy', 'james-bayan-c-sanoy_1771358762979@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:02', '2026-02-18 04:06:02');
INSERT INTO `khan_members` VALUES ('49', NULL, 'Jeason Guiriba', 'jeason-guiriba_1771358765592@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:05', '2026-02-18 04:06:05');
INSERT INTO `khan_members` VALUES ('50', NULL, 'Jeremiah Navarro', 'jeremiah-navarro_1771358768764@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:08', '2026-02-18 04:06:08');
INSERT INTO `khan_members` VALUES ('51', NULL, 'Jericho Veloso', 'jericho-veloso_1771358773831@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:13', '2026-02-18 04:06:13');
INSERT INTO `khan_members` VALUES ('52', NULL, 'Jescille Espiritu Santo', 'jescille-espiritu-santo_1771358776818@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:16', '2026-02-18 04:06:16');
INSERT INTO `khan_members` VALUES ('53', NULL, 'John Vincent I. Miraflor', 'john-vincent-i-miraflor_1771358779800@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:19', '2026-02-18 04:06:19');
INSERT INTO `khan_members` VALUES ('54', NULL, 'Jonar Cruz', 'jonar-cruz_1771358782781@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:22', '2026-02-18 04:06:22');
INSERT INTO `khan_members` VALUES ('55', NULL, 'Joseph Vincent Lim', 'joseph-vincent-lim_1771358785624@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:25', '2026-02-18 04:06:25');
INSERT INTO `khan_members` VALUES ('56', NULL, 'Kevin Andrew Lipana', 'kevin-andrew-lipana_1771358789836@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:29', '2026-02-18 04:06:29');
INSERT INTO `khan_members` VALUES ('57', NULL, 'Krisna Limbaga', 'krisna-limbaga_1771358792689@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:32', '2026-02-18 04:06:32');
INSERT INTO `khan_members` VALUES ('58', NULL, 'Lucio Macalalad Sr.', 'lucio-macalalad-sr-_1771358795476@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:35', '2026-02-18 04:06:35');
INSERT INTO `khan_members` VALUES ('59', NULL, 'Macy E. Fresnoza', 'macy-e-fresnoza_1771358799705@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:39', '2026-02-18 04:06:39');
INSERT INTO `khan_members` VALUES ('60', NULL, 'Mardel Claro', 'mardel-claro_1771358802204@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:42', '2026-02-18 04:06:42');
INSERT INTO `khan_members` VALUES ('61', NULL, 'Mark Binuya', 'mark-binuya_1771358805134@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:45', '2026-02-18 04:06:45');
INSERT INTO `khan_members` VALUES ('62', NULL, 'Marvin Yawing', 'marvin-yawing_1771358808942@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:48', '2026-02-18 04:06:48');
INSERT INTO `khan_members` VALUES ('63', NULL, 'May Ann Calumpang', 'may-ann-calumpang_1771358811190@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:51', '2026-02-18 04:06:51');
INSERT INTO `khan_members` VALUES ('64', NULL, 'Maybelle P. Parreno', 'maybelle-p-parreno_1771358815619@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:55', '2026-02-18 04:06:55');
INSERT INTO `khan_members` VALUES ('65', NULL, 'Michael Franc S. D Fresnoza', 'michael-franc-s-d-fresnoza_1771358818658@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:06:58', '2026-02-18 04:06:58');
INSERT INTO `khan_members` VALUES ('66', NULL, 'Nicko Jay T. Supentran', 'nicko-jay-t-supentran_1771358847726@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:07:27', '2026-02-18 04:07:27');
INSERT INTO `khan_members` VALUES ('67', NULL, 'Nino Paulo G. Almendrala', 'nino-paulo-g-almendrala_1771358850319@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:07:30', '2026-02-18 04:07:30');
INSERT INTO `khan_members` VALUES ('68', NULL, 'Noel S. Villa', 'noel-s-villa_1771358860238@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:07:40', '2026-02-18 04:07:40');
INSERT INTO `khan_members` VALUES ('69', NULL, 'Nur-ainie A. Bitaga', 'nur-ainie-a-bitaga_1771358864875@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:07:44', '2026-02-18 04:07:44');
INSERT INTO `khan_members` VALUES ('70', NULL, 'Peter A. Perdoza', 'peter-a-perdoza_1771358868563@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:07:48', '2026-02-18 04:07:48');
INSERT INTO `khan_members` VALUES ('71', NULL, 'Randy R. Mores', 'randy-r-mores_1771358871366@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:07:51', '2026-02-18 04:07:51');
INSERT INTO `khan_members` VALUES ('72', NULL, 'Ranniel R. Boniol', 'ranniel-r-boniol_1771358875569@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:07:55', '2026-02-18 04:07:55');
INSERT INTO `khan_members` VALUES ('73', NULL, 'Redentor G. Talampas', 'redentor-g-talampas_1771358879868@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:07:59', '2026-02-18 04:07:59');
INSERT INTO `khan_members` VALUES ('74', NULL, 'Redentor Mangubat', 'redentor-mangubat_1771358882430@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:02', '2026-02-18 04:08:02');
INSERT INTO `khan_members` VALUES ('75', NULL, 'Regen Dinlay', 'regen-dinlay_1771358885447@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:05', '2026-02-18 04:08:05');
INSERT INTO `khan_members` VALUES ('76', NULL, 'Rey Lague', 'rey-lague_1771358899274@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:19', '2026-02-18 04:08:19');
INSERT INTO `khan_members` VALUES ('77', NULL, 'Reza M. Grajo', 'reza-m-grajo_1771358902350@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:22', '2026-02-18 04:08:22');
INSERT INTO `khan_members` VALUES ('78', NULL, 'Rho J.fajutrao', 'rho-j-fajutrao_1771358905842@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:25', '2026-02-18 04:08:25');
INSERT INTO `khan_members` VALUES ('79', NULL, 'Richard Francis P. Ayala', 'richard-francis-p-ayala_1771358912744@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:32', '2026-02-18 04:08:32');
INSERT INTO `khan_members` VALUES ('80', NULL, 'Rissan D. Muelas', 'rissan-d-muelas_1771358915629@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:35', '2026-02-18 04:08:35');
INSERT INTO `khan_members` VALUES ('81', NULL, 'Rodel A. Lagarue', 'rodel-a-lagarue_1771358919827@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:39', '2026-02-18 04:08:39');
INSERT INTO `khan_members` VALUES ('82', NULL, 'Rommelson Bulan Perez', 'rommelson-bulan-perez_1771358923600@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:43', '2026-02-18 04:08:43');
INSERT INTO `khan_members` VALUES ('83', NULL, 'Ronald C. Mercado', 'ronald-c-mercado_1771358929382@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:49', '2026-02-18 04:08:49');
INSERT INTO `khan_members` VALUES ('84', NULL, 'Roshelle Lestino', 'roshelle-lestino_1771358934354@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:54', '2026-02-18 04:08:54');
INSERT INTO `khan_members` VALUES ('85', NULL, 'Roy L. Jornales', 'roy-l-jornales_1771358938341@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:08:58', '2026-02-18 04:08:58');
INSERT INTO `khan_members` VALUES ('86', NULL, 'Salvador Layson', 'salvador-layson_1771358941409@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:09:01', '2026-02-18 04:09:01');
INSERT INTO `khan_members` VALUES ('87', NULL, 'Shad de Guzman', 'shad-de-guzman_1771358944659@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:09:04', '2026-02-18 04:09:04');
INSERT INTO `khan_members` VALUES ('88', NULL, 'Sherwin A. Reyes', 'sherwin-a-reyes_1771358952315@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:09:12', '2026-02-18 04:09:12');
INSERT INTO `khan_members` VALUES ('89', NULL, 'Sulpiano C. Laurio Jr.', 'sulpiano-c-laurio-jr-_1771358956739@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:09:16', '2026-02-18 04:09:16');
INSERT INTO `khan_members` VALUES ('90', NULL, 'Vincent T. Hisona', 'vincent-t-hisona_1771358960659@archive.local', '', NULL, '3', 'Yellow-White', '2016-12-14', '2016-12-14', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:09:20', '2026-02-18 04:09:20');
INSERT INTO `khan_members` VALUES ('91', NULL, 'Adrian G. Aquino', 'adrian-g-aquino_1771359012781@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:12', '2026-02-18 04:10:12');
INSERT INTO `khan_members` VALUES ('92', NULL, 'Argen Jonel S. Benito', 'argen-jonel-s-benito_1771359015306@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:15', '2026-02-18 04:10:15');
INSERT INTO `khan_members` VALUES ('93', NULL, 'Cesar Marasigan', 'cesar-marasigan_1771359018494@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:18', '2026-02-18 04:10:18');
INSERT INTO `khan_members` VALUES ('94', NULL, 'Earl Victa', 'earl-victa_1771359024674@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:24', '2026-02-18 04:10:24');
INSERT INTO `khan_members` VALUES ('95', NULL, 'Eduard Arcilla Jr.', 'eduard-arcilla-jr-_1771359027203@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:27', '2026-02-18 04:10:27');
INSERT INTO `khan_members` VALUES ('96', NULL, 'Erving M. Lanot', 'erving-m-lanot_1771359033675@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:33', '2026-02-18 04:10:33');
INSERT INTO `khan_members` VALUES ('97', NULL, 'Felixander A. Bagayao', 'felixander-a-bagayao_1771359036420@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:36', '2026-02-18 04:10:36');
INSERT INTO `khan_members` VALUES ('98', NULL, 'Gerald Lorenzo', 'gerald-lorenzo_1771359039483@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:39', '2026-02-18 04:10:39');
INSERT INTO `khan_members` VALUES ('99', NULL, 'Harry B. Palgan', 'harry-b-palgan_1771359042206@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:42', '2026-02-18 04:10:42');
INSERT INTO `khan_members` VALUES ('100', NULL, 'Jorge C. Pascua', 'jorge-c-pascua_1771359046670@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:46', '2026-02-18 04:10:46');
INSERT INTO `khan_members` VALUES ('101', NULL, 'Joseph E. Azuela', 'joseph-e-azuela_1771359049941@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:49', '2026-02-18 04:10:49');
INSERT INTO `khan_members` VALUES ('102', NULL, 'Kenneth Calara', 'kenneth-calara_1771359052856@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:52', '2026-02-18 04:10:52');
INSERT INTO `khan_members` VALUES ('103', NULL, 'Lj S. Bicera', 'lj-s-bicera_1771359055450@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:10:55', '2026-02-18 04:10:55');
INSERT INTO `khan_members` VALUES ('104', NULL, 'Marc Antoni Cabacungan', 'marc-antoni-cabacungan_1771359062716@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:11:02', '2026-02-18 04:11:02');
INSERT INTO `khan_members` VALUES ('105', NULL, 'Mark Anthony C. Capati', 'mark-anthony-c-capati_1771359065468@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:11:05', '2026-02-18 04:11:05');
INSERT INTO `khan_members` VALUES ('106', NULL, 'Sean Joshua M. Jimenez', 'sean-joshua-m-jimenez_1771359068948@archive.local', '', NULL, '3', 'Yellow-White', '2018-04-15', '2018-04-15', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:11:08', '2026-02-18 04:11:08');
INSERT INTO `khan_members` VALUES ('107', NULL, 'Adrian Jay. B. Gemar', 'adrian-jay-b-gemar_1771359109746@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:11:49', '2026-02-18 04:11:49');
INSERT INTO `khan_members` VALUES ('108', NULL, 'Ariel Camacho', 'ariel-camacho_1771359116791@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:11:56', '2026-02-18 04:11:56');
INSERT INTO `khan_members` VALUES ('109', NULL, 'Bladymil M. Cruz', 'bladymil-m-cruz_1771359120312@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:00', '2026-02-18 04:12:00');
INSERT INTO `khan_members` VALUES ('110', NULL, 'Christopher D. Chongco', 'christopher-d-chongco_1771359124642@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:04', '2026-02-18 04:12:04');
INSERT INTO `khan_members` VALUES ('111', NULL, 'Dexter D. Tominez', 'dexter-d-tominez_1771359128301@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:08', '2026-02-18 04:12:08');
INSERT INTO `khan_members` VALUES ('112', NULL, 'Dondon S. Pascua', 'dondon-s-pascua_1771359132393@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:12', '2026-02-18 04:12:12');
INSERT INTO `khan_members` VALUES ('113', NULL, 'Habiba M. Flordeliza', 'habiba-m-flordeliza_1771359138110@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:18', '2026-02-18 04:12:18');
INSERT INTO `khan_members` VALUES ('114', NULL, 'Ira D. Victa', 'ira-d-victa_1771359142705@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:22', '2026-02-18 04:12:22');
INSERT INTO `khan_members` VALUES ('115', NULL, 'Jason O. Vergara', 'jason-o-vergara_1771359145222@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:25', '2026-02-18 04:12:25');
INSERT INTO `khan_members` VALUES ('116', NULL, 'Jeovanni M. Naduma', 'jeovanni-m-naduma_1771359149748@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:29', '2026-02-18 04:12:29');
INSERT INTO `khan_members` VALUES ('117', NULL, 'Jerald Alonzo Bosikaw', 'jerald-alonzo-bosikaw_1771359152351@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:32', '2026-02-18 04:12:32');
INSERT INTO `khan_members` VALUES ('118', NULL, 'Jerson B. Panis', 'jerson-b-panis_1771359155957@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:35', '2026-02-18 04:12:35');
INSERT INTO `khan_members` VALUES ('119', NULL, 'Jetson R. Balacano', 'jetson-r-balacano_1771359160514@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:40', '2026-02-18 04:12:40');
INSERT INTO `khan_members` VALUES ('120', NULL, 'Jonathan C. Lobo', 'jonathan-c-lobo_1771359175384@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:55', '2026-02-18 04:12:55');
INSERT INTO `khan_members` VALUES ('121', NULL, 'Jonathan V. Ripay', 'jonathan-v-ripay_1771359178513@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:12:58', '2026-02-18 04:12:58');
INSERT INTO `khan_members` VALUES ('122', NULL, 'Joseph Allan M. Dela Cruz', 'joseph-allan-m-dela-cruz_1771359181996@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:13:01', '2026-02-18 04:13:01');
INSERT INTO `khan_members` VALUES ('123', NULL, 'Kim Cyrus C. Vallejos', 'kim-cyrus-c-vallejos_1771359185129@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:13:05', '2026-02-18 04:13:05');
INSERT INTO `khan_members` VALUES ('124', NULL, 'Kurtny Love S. Dayrit', 'kurtny-love-s-dayrit_1771359190381@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:13:10', '2026-02-18 04:13:10');
INSERT INTO `khan_members` VALUES ('125', NULL, 'Ma Elena P. Silvestre', 'ma-elena-p-silvestre_1771359218972@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:13:38', '2026-02-18 04:13:38');
INSERT INTO `khan_members` VALUES ('126', NULL, 'Marc Jesson D. Moran', 'marc-jesson-d-moran_1771359221905@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:13:41', '2026-02-18 04:13:41');
INSERT INTO `khan_members` VALUES ('127', NULL, 'Moises Lois C. Ilogon', 'moises-lois-c-ilogon_1771359224270@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:13:44', '2026-02-18 04:13:44');
INSERT INTO `khan_members` VALUES ('128', NULL, 'Ric Bryan G. Lavapie', 'ric-bryan-g-lavapie_1771359395125@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:16:35', '2026-02-18 04:16:35');
INSERT INTO `khan_members` VALUES ('129', NULL, 'Thomas Mark Mendez', 'thomas-mark-mendez_1771359398270@archive.local', '', NULL, '3', 'Yellow-White', '2018-07-22', '2018-07-22', NULL, 'MUAYTHAI NATIONAL TRAINING CENTER, PASIG CITY', 'active', 'Manually encoded', '2026-02-18 04:16:38', '2026-02-18 04:16:38');
INSERT INTO `khan_members` VALUES ('130', NULL, 'Michelle Alumno', 'michelle-alumno_1771359421194@archive.local', '', NULL, '3', 'Yellow-White', '2018-09-14', '2018-09-14', NULL, 'BAGUIO-BENGUET', 'active', 'Manually encoded', '2026-02-18 04:17:01', '2026-02-18 04:17:01');
INSERT INTO `khan_members` VALUES ('131', NULL, 'Shiela Marie  Alumno', 'shiela-marie-alumno_1771359426283@archive.local', '', NULL, '3', 'Yellow-White', '2018-09-14', '2018-09-14', NULL, 'BAGUIO-BENGUET', 'active', 'Manually encoded', '2026-02-18 04:17:06', '2026-02-18 04:17:06');
INSERT INTO `khan_members` VALUES ('132', NULL, 'Edwin Aliong', 'edwin-aliong_1771360329789@archive.local', '', NULL, '3', 'Yellow-White', '2018-09-14', '2018-09-14', NULL, 'BAGUIO-BENGUET', 'active', 'Manually encoded', '2026-02-18 04:32:09', '2026-02-18 04:32:09');
INSERT INTO `khan_members` VALUES ('133', NULL, 'Rowena Okabe', 'rowena-okabe_1771360332386@archive.local', '', NULL, '3', 'Yellow-White', '2018-09-14', '2018-09-14', NULL, 'BAGUIO-BENGUET', 'active', 'Manually encoded', '2026-02-18 04:32:12', '2026-02-18 04:32:12');
INSERT INTO `khan_members` VALUES ('134', NULL, 'Rexce Brual', 'rexce-brual_1771360336862@archive.local', '', NULL, '3', 'Yellow-White', '2018-09-14', '2018-09-14', NULL, 'BAGUIO-BENGUET', 'active', 'Manually encoded', '2026-02-18 04:32:16', '2026-02-18 04:32:16');
INSERT INTO `khan_members` VALUES ('135', NULL, 'Nilo de la Cruz', 'nilo-de-la-cruz_1771360339770@archive.local', '', NULL, '3', 'Yellow-White', '2018-09-14', '2018-09-14', NULL, 'BAGUIO-BENGUET', 'active', 'Manually encoded', '2026-02-18 04:32:19', '2026-02-18 04:32:19');
INSERT INTO `khan_members` VALUES ('136', NULL, 'Jeezrel Cadiogan', 'jeezrel-cadiogan_1771360343856@archive.local', '', NULL, '3', 'Yellow-White', '2018-09-14', '2018-09-14', NULL, 'BAGUIO-BENGUET', 'active', 'Manually encoded', '2026-02-18 04:32:23', '2026-02-18 04:32:23');
INSERT INTO `khan_members` VALUES ('137', NULL, 'Mariafe Co', 'mariafe-co_1771360348561@archive.local', '', NULL, '3', 'Yellow-White', '2018-09-14', '2018-09-14', NULL, 'BAGUIO-BENGUET', 'active', 'Manually encoded', '2026-02-18 04:32:28', '2026-02-18 04:32:28');
INSERT INTO `khan_members` VALUES ('138', NULL, 'Marilyn Tabareng', 'marilyn-tabareng_1771360351178@archive.local', '', NULL, '3', 'Yellow-White', '2018-09-14', '2018-09-14', NULL, 'BAGUIO-BENGUET', 'active', 'Manually encoded', '2026-02-18 04:32:31', '2026-02-18 04:32:31');
INSERT INTO `khan_members` VALUES ('140', NULL, 'Asd', 'asd_1771429267232@oma.com', '', NULL, '2', 'Yellow', '2026-02-18', '2026-02-18', NULL, '', 'active', 'Manually encoded', '2026-02-18 23:41:07', '2026-02-18 23:41:15');

-- ------------------------------------------------------------
-- Table: `khan_training_history`
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `khan_training_history`;
CREATE TABLE `khan_training_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `khan_level` int(11) NOT NULL,
  `training_date` date NOT NULL,
  `certified_date` date DEFAULT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `status` enum('in_progress','completed','certified') DEFAULT 'in_progress',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`),
  KEY `instructor_id` (`instructor_id`),
  KEY `idx_member_level` (`member_id`,`khan_level`),
  KEY `idx_khan_level` (`khan_level`),
  KEY `idx_training_date` (`training_date`),
  KEY `idx_certified_date` (`certified_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `khan_training_history_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `khan_members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `khan_training_history_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `khan_training_history`
INSERT INTO `khan_training_history` VALUES ('1', '2', '2', '2026-01-30', NULL, '29', '0', '', '', 'completed', '2026-01-30 19:19:07', '2026-01-30 19:19:07');
INSERT INTO `khan_training_history` VALUES ('2', '2', '2', '2026-01-30', NULL, '29', '0', '', '', 'completed', '2026-01-30 19:19:11', '2026-01-30 19:19:11');
INSERT INTO `khan_training_history` VALUES ('3', '140', '1', '2026-02-18', '2026-02-18', NULL, '', 'Manually encoded from masterlist', NULL, 'certified', '2026-02-18 23:41:07', '2026-02-18 23:41:07');
INSERT INTO `khan_training_history` VALUES ('4', '140', '2', '2026-02-18', '2026-02-18', NULL, '', 'Manually encoded from masterlist', NULL, 'certified', '2026-02-18 23:41:15', '2026-02-18 23:41:15');

-- ------------------------------------------------------------
-- Table: `users`
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `role` enum('admin','instructor','member') DEFAULT 'member',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `khan_level` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `users`
INSERT INTO `users` VALUES ('1', NULL, 'Ajarn Brendaley', 'admin@oma.com', '$2y$10$rWvjYM6/LCbwjSWCcOxN3OwHzOc8ajbl0T3TDxY4EHSRtkbsG1O7a', '', 'admin', 'active', NULL, '2026-01-14 22:11:48', '2026-01-29 13:14:19');
INSERT INTO `users` VALUES ('2', 'OMA-002', 'Rusha Bayacsan', 'Rusha@gmail.com', '$2y$10$mF2X4DxCvSOmf3tmPscBPOcjZRvcLo8fbSQeSeuLfp0b/vRyfDsYa', '', 'instructor', 'active', NULL, '2026-01-29 12:57:13', '2026-01-29 12:57:13');
INSERT INTO `users` VALUES ('3', 'OMA-003', 'Alvin Adolfo', 'Alvin@gmail.com', '$2y$10$RBoiO.TwrQPKkoeQa4V.b.X/ozUBW9pD1xLCYPKTCYJL8LJalqXai', '', 'instructor', 'active', NULL, '2026-01-29 12:58:29', '2026-01-29 12:58:29');
INSERT INTO `users` VALUES ('4', 'OMA-004', 'Vincent Hisona', 'Vincent@gmail.com', '$2y$10$3P5aCU9fcWpbk4DcRqrAPuLMXlZXElxGhE0XZRfrFKCPEawKVHX9i', '', 'instructor', 'active', NULL, '2026-01-29 13:11:38', '2026-01-29 13:11:38');
INSERT INTO `users` VALUES ('5', 'OMA-005', 'Roberto Serdone', 'roberto@gmail.com', '$2y$10$L8Ju2ZUUpJ9Qr/0nrjsLnOqys1MZmavpZWUQTVORvy1CVPz9Y.u0G', '', 'instructor', 'active', NULL, '2026-01-29 13:12:47', '2026-01-29 13:12:47');
INSERT INTO `users` VALUES ('6', 'OMA-006', 'Joseph Vincent Lim', 'Joseph@gmail.com', '$2y$10$rfekBEMhz4l.rBePt/qDFeIruj2Uv094woCOcdmlLhXsKGzWIfNjW', '', 'instructor', 'active', NULL, '2026-01-29 13:16:55', '2026-01-29 13:16:55');
INSERT INTO `users` VALUES ('7', 'OMA-007', 'John Vincent Miraflor', 'John@gmail.com', '$2y$10$vsHXNNtXs.5GlH.vF8b5.OufmR2qk6sdydxaHLZYuDyZrdSc3EnVC', '', 'instructor', 'active', NULL, '2026-01-29 13:17:59', '2026-01-29 13:17:59');
INSERT INTO `users` VALUES ('8', 'OMA-008', 'Fredlyn Sayod-Miraflor', 'Fredlyn@gmail.com', '$2y$10$Lkdyw35saGtaUQqP36X6meFZ23HHnoLl50t2OLFidiQN27ASg6gcG', '', 'instructor', 'active', NULL, '2026-01-29 13:19:01', '2026-01-29 13:19:01');
INSERT INTO `users` VALUES ('9', 'OMA-009', 'Rho Fajutra', 'Rho@gmail.com', '$2y$10$/HhIJK0GTF7sBmx8j6n7yOE09pr/aZHuYRGQvAfv4LPZF3W1idD8q', '', 'instructor', 'active', NULL, '2026-01-29 13:19:40', '2026-01-29 13:19:40');
INSERT INTO `users` VALUES ('10', 'OMA-010', 'Krisna Limbaga', 'krisna@gmail.com', '$2y$10$6bWNFYOHGZTTYAEq7e/vD.VSCn/Ryq9D98ZFCky.h9kDhH6czv/Lm', '', 'instructor', 'active', NULL, '2026-01-29 13:20:08', '2026-01-29 13:20:08');
INSERT INTO `users` VALUES ('11', 'OMA-011', 'Art Pantinople', 'art@gmail.com', '$2y$10$qssE1rWe1jTPRcj4TqgimePZLaZDSBhrWn/5nBmmHDkZZ46gkNuTy', '', 'instructor', 'active', NULL, '2026-01-29 13:20:41', '2026-01-29 13:20:41');
INSERT INTO `users` VALUES ('12', 'OMA-012', 'Felixander Bagayao', 'Felixander@gmail.com', '$2y$10$8kT6rkYUY42zKltlOewJpOydWfym4HVImMJrmupGLLkZf5BIB3uGe', '', 'instructor', 'active', NULL, '2026-01-29 13:21:19', '2026-01-29 13:21:19');
INSERT INTO `users` VALUES ('13', 'OMA-013', 'Michael Rimando', 'Michael@gmail.com', '$2y$10$DQvt1PtJuRoLSNThh2N2qO2PoqlCQ7TPa6ak.mz38wEIA5/Qce18i', '', 'instructor', 'active', NULL, '2026-01-29 13:21:47', '2026-01-30 19:18:05');
INSERT INTO `users` VALUES ('14', 'OMA-014', 'Ricardo Forlales', 'Ricardo@gmail.com', '$2y$10$fPUHxWlArLaVT88qr2ejnOmvybQihqS3kuE/vsazItAWmo.bRlaOq', '', 'instructor', 'active', NULL, '2026-01-29 13:22:18', '2026-01-29 13:22:18');
INSERT INTO `users` VALUES ('15', 'OMA-015', 'Vernie Garcia', 'Vernie@gmail.com', '$2y$10$IC.SOniTzlTT0p.ohO8HDOb9dwVuzuAiSTWVxoFFeW4jvnricFbl6', '', 'instructor', 'active', NULL, '2026-01-29 13:22:46', '2026-01-29 13:22:46');
INSERT INTO `users` VALUES ('16', 'OMA-016', 'Erexter Taligan', 'Erexter@gmail.com', '$2y$10$JTeGKulrj8bUy.Hx6IMmUu7lrpBmKrXYaePeaKCrJsB0KX.SnvNTC', '', 'member', 'active', NULL, '2026-01-31 09:04:54', '2026-01-31 09:04:54');

SET FOREIGN_KEY_CHECKS=1;

-- ============================================================
-- Backup complete: 11 tables
-- ============================================================
