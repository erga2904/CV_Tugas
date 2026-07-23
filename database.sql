-- Database SQL Script for CV Project
-- Create Database: cv_database

CREATE DATABASE IF NOT EXISTS `cv_database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cv_database`;

-- Drop existing tables in reverse order of foreign keys
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `education`;
DROP TABLE IF EXISTS `experiences`;
DROP TABLE IF EXISTS `users`;

-- 1. Table Users
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `title` VARCHAR(100) DEFAULT 'Full Stack Developer',
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(30) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `summary` TEXT DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'user') DEFAULT 'user',
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table Experiences
CREATE TABLE `experiences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `company_name` VARCHAR(100) NOT NULL,
  `job_title` VARCHAR(100) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE DEFAULT NULL,
  `is_current` TINYINT(1) DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  CONSTRAINT `fk_exp_users` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table Education
CREATE TABLE `education` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `school_name` VARCHAR(100) NOT NULL,
  `degree_obtained` VARCHAR(100) NOT NULL,
  `major` VARCHAR(100) DEFAULT NULL,
  `start_year` INT DEFAULT NULL,
  `graduation_year` INT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  CONSTRAINT `fk_edu_users` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table Skills
CREATE TABLE `skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `skill_name` VARCHAR(100) NOT NULL,
  `proficiency_level` VARCHAR(50) DEFAULT 'Intermediate',
  `percentage` INT DEFAULT 80,
  `category` VARCHAR(50) DEFAULT 'Programming',
  CONSTRAINT `fk_skills_users` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SEED INITIAL DATA
-- Note: Passwords are password_hash('adminpass', PASSWORD_BCRYPT) and password_hash('userpass', PASSWORD_BCRYPT)

INSERT INTO `users` (`id`, `username`, `slug`, `full_name`, `title`, `email`, `phone`, `address`, `summary`, `password`, `role`, `is_default`) VALUES
(1, 'admin', 'admin', 'Administrator Utama', 'System Administrator', 'admin@example.com', '081234567890', 'Jakarta, Indonesia', 'Pengelola sistem CV Multi-User dengan akses penuh ke seluruh data pengguna.', '$2y$10$wE99J58vM2rDk77n1K.Juu1q1m/hP4zT4M3gM4x5Y6Z7a8b9c0d1e', 'admin', 0),
(2, 'erga_refaldy', 'erga_refaldy', 'Erga Refaldy D.G', 'Senior Software Engineer & Web Developer', 'erga.refaldy@example.com', '085712345678', 'Bandung, Jawa Barat', 'Seorang Software Engineer berpengalaman dalam membangun aplikasi web modern, sistem berbasis microservices, dan arsitektur database performa tinggi. Memiliki passion tinggi pada pengembangan web full-stack dan otomasi sistem.', '$2y$10$wE99J58vM2rDk77n1K.Juu1q1m/hP4zT4M3gM4x5Y6Z7a8b9c0d1e', 'user', 1);

-- Experiences for Erga Refaldy D.G (user_id = 2)
INSERT INTO `experiences` (`user_id`, `company_name`, `job_title`, `start_date`, `end_date`, `is_current`, `description`) VALUES
(2, 'PT Teknologi Indonesia Jaya', 'Lead Web Developer', '2022-01-15', NULL, 1, 'Memimpin tim pengembang web beranggotakan 6 orang. Mengembangkan aplikasi e-commerce dan dashboard internal dengan arsitektur microservices PHP & MySQL. Meningkatkan kecepatan loading situs sebesar 45%.'),
(2, 'Solusi Digital Nusantara', 'Senior PHP & Web Engineer', '2019-06-01', '2021-12-31', 0, 'Mengembangkan RESTful API dan modul backend sistem manajemen inventaris enterprise. Melakukan optimasi query database MySQL yang menangani jutaan transaksi harian.'),
(2, 'Creative Media Works', 'Junior Full Stack Developer', '2017-03-01', '2019-05-30', 0, 'Membuat template CV interaktif, merancang layout responsif dengan HTML5/CSS3/Bootstrap, serta menangani pengintegrasian sistem autentikasi pengguna.');

-- Education for Erga Refaldy D.G (user_id = 2)
INSERT INTO `education` (`user_id`, `school_name`, `degree_obtained`, `major`, `start_year`, `graduation_year`, `description`) VALUES
(2, 'Universitas Komputer Indonesia', 'Sarjana Komputer (S.Kom)', 'Teknik Informatika', 2013, 2017, 'Lulus dengan predikat Cumlaude (IPK 3.82). Aktif dalam organisasi kemahasiswaan bidang pemrograman web dan pengabdian masyarakat.'),
(2, 'SMA Negeri 1 Bandung', 'Ijazah SMA', 'Ilmu Pengetahuan Alam (IPA)', 2010, 2013, 'Fokus pada mata pelajaran Matematika dan Fisika. Juara 2 Olimpiade Komputer tingkat Kota Bandung.');

-- Skills for Erga Refaldy D.G (user_id = 2)
INSERT INTO `skills` (`user_id`, `skill_name`, `proficiency_level`, `percentage`, `category`) VALUES
(2, 'PHP & MySQL', 'Expert', 95, 'Backend'),
(2, 'HTML5, CSS3, JavaScript', 'Expert', 90, 'Frontend'),
(2, 'Bootstrap & UI/UX Design', 'Advanced', 85, 'Frontend'),
(2, 'Git & Version Control', 'Advanced', 85, 'Tools'),
(2, 'RESTful API & JSON', 'Expert', 90, 'Backend'),
(2, 'Linux Server & Apache Configuration', 'Intermediate', 75, 'DevOps');
