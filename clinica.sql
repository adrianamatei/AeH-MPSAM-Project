-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 16, 2026 at 02:21 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clinica`
--

-- --------------------------------------------------------

--
-- Table structure for table `consultatii`
--

DROP TABLE IF EXISTS `consultatii`;
CREATE TABLE IF NOT EXISTS `consultatii` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cnp_pacient` char(13) COLLATE utf8mb4_general_ci NOT NULL,
  `nr_consultatie` int NOT NULL,
  `data_consultatie` date NOT NULL,
  `diagnostic` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `medicamentatie` text COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_id` int NOT NULL,
  `specialitate_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pacient_nr` (`cnp_pacient`,`nr_consultatie`),
  KEY `fk_consultatii_doctori` (`doctor_id`),
  KEY `fk_consultatii_specialitati` (`specialitate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultatii`
--

INSERT INTO `consultatii` (`id`, `cnp_pacient`, `nr_consultatie`, `data_consultatie`, `diagnostic`, `medicamentatie`, `doctor_id`, `specialitate_id`, `created_at`) VALUES
(2, '5020226355114', 1, '2024-01-18', 'Control periodic', 'Analize uzuale', 1, 1, '2026-01-06 17:32:55'),
(3, '2990505123456', 1, '2024-02-05', 'Diabet zaharat tip 2', 'Metformin', 1, 1, '2026-01-06 17:32:55'),
(4, '1880715123460', 1, '2024-03-02', 'Infecție respiratorie', 'Antitermice', 1, 1, '2026-01-06 17:32:55'),
(5, '1961201123458', 1, '2024-01-20', 'Hipertensiune arterială', 'Perindopril', 2, 2, '2026-01-06 17:32:55'),
(6, '1950909123462', 1, '2024-02-12', 'Hipertensiune severă', 'Tratament combinat', 2, 2, '2026-01-06 17:32:55'),
(7, '2990505123456', 2, '2024-03-10', 'Control cardiologic', 'ECG + recomandări', 2, 2, '2026-01-06 17:32:55'),
(8, '2950322123459', 1, '2024-01-25', 'Astm bronșic', 'Ventolin', 3, 3, '2026-01-06 17:32:55'),
(9, '4010101123461', 1, '2024-02-14', 'Astm alergic', 'Seretide', 3, 3, '2026-01-06 17:32:55'),
(10, '1870301123466', 1, '2024-03-22', 'Bronșită cronică', 'Tratament inhalator', 3, 3, '2026-01-06 17:32:55'),
(11, '2990505123456', 3, '2024-02-20', 'Diabet', 'Insulină', 4, 4, '2026-01-06 17:32:55'),
(12, '6020401123463', 1, '2024-03-05', 'Diabet gestațional', 'Regim alimentar', 4, 4, '2026-01-06 17:32:55'),
(13, '1910615123465', 1, '2024-03-18', 'Hipotiroidism', 'Levotiroxină', 4, 4, '2026-01-06 17:32:55'),
(14, '1950909123462', 2, '2024-01-30', 'Cancer pulmonar', 'Chimioterapie', 5, 5, '2026-01-06 17:32:55'),
(18, '1980101123457', 1, '2026-01-16', 'Astm', 'procurarea unui nou inhalator', 1, 1, '2026-01-16 09:13:12'),
(16, '1991212123464', 1, '2024-03-25', 'Cancer mamar', 'Trimitere + investigații', 5, 5, '2026-01-06 17:32:55');

-- --------------------------------------------------------

--
-- Table structure for table `doctori`
--

DROP TABLE IF EXISTS `doctori`;
CREATE TABLE IF NOT EXISTS `doctori` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cnp_doctor` char(13) COLLATE utf8mb4_general_ci NOT NULL,
  `nume` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `prenume` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `parola_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `specialitate_id` int NOT NULL,
  `is_director` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cnp_doctor` (`cnp_doctor`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_doctori_specialitati` (`specialitate_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctori`
--

INSERT INTO `doctori` (`id`, `cnp_doctor`, `nume`, `prenume`, `email`, `parola_hash`, `specialitate_id`, `is_director`, `created_at`) VALUES
(1, '1800101123456', 'Popescu', 'Ion', 'ion.popescu@clinica.ro', '$2y$10$Zm8RqAtFh1bklkU48P2wIuNVgK/hfdB1usw4hBq2DQkXr2OWqkAV6', 1, 1, '2026-01-06 17:32:55'),
(2, '2800202123457', 'Ionescu', 'Maria', 'maria.ionescu@clinica.ro', '$2y$10$Zm8RqAtFh1bklkU48P2wIuNVgK/hfdB1usw4hBq2DQkXr2OWqkAV6', 2, 0, '2026-01-06 17:32:55'),
(3, '1800303123458', 'Georgescu', 'Andrei', 'andrei.georgescu@clinica.ro', '$2y$10$Zm8RqAtFh1bklkU48P2wIuNVgK/hfdB1usw4hBq2DQkXr2OWqkAV6', 3, 0, '2026-01-06 17:32:55'),
(4, '2800404123459', 'Dumitrescu', 'Elena', 'elena.dumitrescu@clinica.ro', '$2y$10$Zm8RqAtFh1bklkU48P2wIuNVgK/hfdB1usw4hBq2DQkXr2OWqkAV6', 4, 0, '2026-01-06 17:32:55'),
(5, '1800505123460', 'Radu', 'Mihai', 'mihai.radu@clinica.ro', '$2y$10$Zm8RqAtFh1bklkU48P2wIuNVgK/hfdB1usw4hBq2DQkXr2OWqkAV6', 5, 0, '2026-01-06 17:32:55');

-- --------------------------------------------------------

--
-- Table structure for table `pacienti`
--

DROP TABLE IF EXISTS `pacienti`;
CREATE TABLE IF NOT EXISTS `pacienti` (
  `cnp` char(13) COLLATE utf8mb4_general_ci NOT NULL,
  `nume` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `prenume` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `adresa` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `data_nasterii` date NOT NULL,
  `sex` enum('M','F') COLLATE utf8mb4_general_ci NOT NULL,
  `varsta` int NOT NULL,
  `email` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `telefon` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cnp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pacienti`
--

INSERT INTO `pacienti` (`cnp`, `nume`, `prenume`, `adresa`, `data_nasterii`, `sex`, `varsta`, `email`, `telefon`, `created_at`) VALUES
('1980101123457', 'Bololoi', 'Narcis', 'Str. Daliei 222, Arad', '1998-01-01', 'M', 28, 'bololoi.narcis@clinica.com', '0787598994', '2026-01-16 09:12:03'),
('5020226355114', 'Ionescu', 'Adrian', 'Timișoara', '2002-02-26', 'M', 23, 'ionescu@clinica.ro', '0752123457', '2026-01-06 17:32:55'),
('2990505123456', 'Pop', 'Elena', 'Oradea', '1999-05-05', 'F', 25, 'elena.pop@clinica.ro', '0744000111', '2026-01-06 17:32:55'),
('1961201123458', 'Stan', 'Mihai', 'Arad', '1996-12-01', 'M', 28, 'mihai.stan@clinica.ro', '0722333444', '2026-01-06 17:32:55'),
('2950322123459', 'Dumitru', 'Ana', 'Sibiu', '1995-03-22', 'F', 29, 'ana.dumitru@clinica.ro', '0733111222', '2026-01-06 17:32:55'),
('1880715123460', 'Radu', 'Ioan', 'Brașov', '1988-07-15', 'M', 36, 'ioan.radu@clinica.ro', '0766555444', '2026-01-06 17:32:55'),
('4010101123461', 'Marin', 'Cristina', 'București', '2001-01-01', 'F', 24, 'cristina.marin@clinica.ro', '0777666888', '2026-01-06 17:32:55'),
('1950909123462', 'Petrescu', 'Dan', 'Constanța', '1995-09-09', 'M', 29, 'dan.petrescu@clinica.ro', '0788123123', '2026-01-06 17:32:55'),
('6020401123463', 'Vasile', 'Ioana', 'Iași', '2002-04-01', 'F', 22, 'ioana.vasile@clinica.ro', '0799000111', '2026-01-06 17:32:55'),
('1991212123464', 'Munteanu', 'Paul', 'Ploiești', '1999-12-12', 'M', 25, 'paul.munteanu@clinica.ro', '0700111222', '2026-01-06 17:32:55'),
('1910615123465', 'Cojocaru', 'Alina', 'Bacău', '1991-06-15', 'F', 33, 'alina.cojocaru@clinica.ro', '0711222333', '2026-01-06 17:32:55'),
('1870301123466', 'Nistor', 'Vlad', 'Suceava', '1987-03-01', 'M', 37, 'vlad.nistor@clinica.ro', '0722000111', '2026-01-06 17:32:55');

-- --------------------------------------------------------

--
-- Table structure for table `specialitati`
--

DROP TABLE IF EXISTS `specialitati`;
CREATE TABLE IF NOT EXISTS `specialitati` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nume` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nume` (`nume`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specialitati`
--

INSERT INTO `specialitati` (`id`, `nume`) VALUES
(1, 'Medicină internă'),
(2, 'Cardiologie'),
(3, 'Pneumologie'),
(4, 'Endocrinologie'),
(5, 'Oncologie');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
