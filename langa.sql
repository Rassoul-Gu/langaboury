-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mer. 22 oct. 2025 à 10:26
-- Version du serveur : 11.4.8-MariaDB
-- Version de PHP : 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `difo5341_langa`
--

-- --------------------------------------------------------

--
-- Structure de la table `enigmes`
--

CREATE TABLE `enigmes` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `enigme_text` text NOT NULL,
  `answer` varchar(200) NOT NULL,
  `next_location` text DEFAULT NULL,
  `points` int(11) DEFAULT 10,
  `qr_code` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('active','finished') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groups_table`
--

CREATE TABLE `groups_table` (
  `id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `color` varchar(20) DEFAULT '#3B82F6',
  `score` int(11) DEFAULT 0,
  `current_step` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `qr_codes_files`
--

CREATE TABLE `qr_codes_files` (
  `id` int(11) NOT NULL,
  `enigme_id` int(11) NOT NULL,
  `qr_code` varchar(50) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `qr_files`
--

CREATE TABLE `qr_files` (
  `id` int(11) NOT NULL,
  `enigme_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `qr_content` varchar(255) NOT NULL,
  `generated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Structure de la table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `enigme_id` int(11) NOT NULL,
  `answer_submitted` varchar(200) NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `enigmes`
--
ALTER TABLE `enigmes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_code` (`qr_code`),
  ADD UNIQUE KEY `unique_group_step` (`group_id`,`step_number`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `idx_qr` (`qr_code`),
  ADD KEY `idx_group_step` (`group_id`,`step_number`);

--
-- Index pour la table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `groups_table`
--
ALTER TABLE `groups_table`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`);

--
-- Index pour la table `qr_codes_files`
--
ALTER TABLE `qr_codes_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enigme` (`enigme_id`),
  ADD KEY `idx_qr_code` (`qr_code`),
  ADD KEY `idx_created` (`created_at` DESC);

--
-- Index pour la table `qr_files`
--
ALTER TABLE `qr_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enigme_file` (`enigme_id`),
  ADD KEY `idx_qr_files_group` (`group_id`),
  ADD KEY `idx_qr_files_enigme` (`enigme_id`),
  ADD KEY `idx_qr_files_name` (`file_name`);

--
-- Index pour la table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enigme_id` (`enigme_id`),
  ADD KEY `idx_group` (`group_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `enigmes`
--
ALTER TABLE `enigmes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `groups_table`
--
ALTER TABLE `groups_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qr_codes_files`
--
ALTER TABLE `qr_codes_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `qr_files`
--
ALTER TABLE `qr_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `enigmes`
--
ALTER TABLE `enigmes`
  ADD CONSTRAINT `enigmes_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enigmes_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups_table` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groups_table`
--
ALTER TABLE `groups_table`
  ADD CONSTRAINT `groups_table_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `qr_codes_files`
--
ALTER TABLE `qr_codes_files`
  ADD CONSTRAINT `qr_codes_files_ibfk_1` FOREIGN KEY (`enigme_id`) REFERENCES `enigmes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `qr_files`
--
ALTER TABLE `qr_files`
  ADD CONSTRAINT `qr_files_ibfk_1` FOREIGN KEY (`enigme_id`) REFERENCES `enigmes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qr_files_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups_table` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups_table` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`enigme_id`) REFERENCES `enigmes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
