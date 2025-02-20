-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 10, 2025 at 11:18 AM
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
-- Database: `gestion_collectes`
--

-- --------------------------------------------------------

--
-- Table structure for table `benevoles`
--

DROP TABLE IF EXISTS `benevoles`;
CREATE TABLE IF NOT EXISTS `benevoles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('admin','participant') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `benevoles`
--

INSERT INTO `benevoles` (`id`, `nom`, `email`, `mot_de_passe`, `role`) VALUES
(1, 'Alice Dupont', 'alice.dupont@example.com', 'alicedupont', 'admin'),
(2, 'Bob Martin', 'bob.martin@example.com', 'bobmartin', 'participant'),
(3, 'Charlie Dubois', 'charlie.dubois@example.com', '9148b120a413e9e84e57f1231f04119a', 'participant'),


-- --------------------------------------------------------

--
-- Table structure for table `collectes`
--

DROP TABLE IF EXISTS `collectes`;
CREATE TABLE IF NOT EXISTS `collectes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date_collecte` date NOT NULL,
  `lieu` varchar(255) NOT NULL,
  `id_benevole` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_benevole` (`id_benevole`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `collectes`
--

INSERT INTO `collectes` (`id`, `date_collecte`, `lieu`, `id_benevole`) VALUES
(1, '2024-02-01', 'Parc Central', 1),
(2, '2024-02-05', 'Plage du Sud', 2),
(3, '2024-02-10', 'Quartier Nord', 1),
(4, '2025-01-04', 'paris', 3),
(6, '3058-06-25', 'lyon', 3),
(7, '2029-04-07', 'toulon', 3),
(8, '2026-04-25', 'lille', 1),
(9, '2028-05-10', 'toulouse', 3),
(10, '0008-02-02', 'vertou', 1);

-- --------------------------------------------------------

--
-- Table structure for table `dechets_collectes`
--

DROP TABLE IF EXISTS `dechets_collectes`;
CREATE TABLE IF NOT EXISTS `dechets_collectes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_collecte` int DEFAULT NULL,
  `type_dechet` varchar(50) NOT NULL,
  `quantite_kg` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_collecte` (`id_collecte`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dechets_collectes`
--

INSERT INTO `dechets_collectes` (`id`, `id_collecte`, `type_dechet`, `quantite_kg`) VALUES
(1, 1, 'plastique', 5.2),
(2, 1, 'verre', 3.1),
(3, 2, 'métal', 2.4),
(4, 2, 'papier', 1.7),
(5, 3, 'organique', 6.5),
(6, 3, 'plastique', 4.3);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `collectes`
--
ALTER TABLE `collectes`
  ADD CONSTRAINT `collectes_ibfk_1` FOREIGN KEY (`id_benevole`) REFERENCES `benevoles` (`id`);

--
-- Constraints for table `dechets_collectes`
--
ALTER TABLE `dechets_collectes`
  ADD CONSTRAINT `dechets_collectes_ibfk_1` FOREIGN KEY (`id_collecte`) REFERENCES `collectes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


CREATE TABLE messages (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `messages` (`id`, `user_id`, `message`, `created_at`) VALUES (NULL, '1', 'Bienvenue', CURRENT_TIMESTAMP);

DROP TABLE IF EXISTS `budget`;
CREATE TABLE IF NOT EXISTS `budget` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date` date NOT NULL,
  `heure` time DEFAULT NULL,
  `montant` float NOT NULL,
  `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `budget` (`id`, `nom`, `date`, `heure`, `montant`, `commentaire`) VALUES (NULL, 'Anonyme', '2025-02-18', '21:56:39', '3000', 'Dons anonyme');