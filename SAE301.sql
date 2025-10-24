-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : ven. 24 oct. 2025 à 12:57
-- Version du serveur : 10.11.14-MariaDB-0+deb12u2
-- Version de PHP : 8.3.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `viroulaud8`
--

-- --------------------------------------------------------

--
-- Structure de la table `Category`
--

CREATE TABLE `Category` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Déchargement des données de la table `Category`
--

INSERT INTO `Category` (`id`, `name`) VALUES
(1, 'bougies'),
(2, 'mobilier'),
(3, 'fournitures');

-- --------------------------------------------------------

--
-- Structure de la table `CommandePanier`
--

CREATE TABLE `CommandePanier` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `date_sauvegarde` datetime DEFAULT current_timestamp(),
  `date_commande` datetime DEFAULT NULL,
  `statut` varchar(32) DEFAULT 'Panier',
  `montant_total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `CommandePanier`
--

INSERT INTO `CommandePanier` (`id`, `order_number`, `client_id`, `date_sauvegarde`, `date_commande`, `statut`, `montant_total`) VALUES
(5, 'CMD-20251024-948796', 10, '2025-10-24 10:03:35', '2025-10-24 10:04:19', 'Commande', '21.64'),
(6, 'CMD-20251024-029027', 10, '2025-10-24 10:05:39', '2025-10-24 10:05:39', 'Commande', '84.68'),
(7, 'CMD-20251024-907626', 10, '2025-10-24 11:26:20', '2025-10-24 11:26:20', 'Commande', '8.52'),
(8, 'CMD-20251024-700518', 10, '2025-10-24 11:28:03', '2025-10-24 11:28:03', 'Commande', '135.70'),
(9, 'CMD-20251024-855952', 10, '2025-10-24 11:33:15', '2025-10-24 11:33:15', 'Commande', '6.34'),
(10, 'CMD-20251024-677380', 11, '2025-10-24 12:48:41', '2025-10-24 12:48:41', 'Commande', '2.24'),
(11, 'CMD-20251024-585532', 11, '2025-10-24 13:14:08', '2025-10-24 13:14:08', 'Commande', '6.96'),
(12, 'CMD-20251024-733529', 11, '2025-10-24 13:23:21', '2025-10-24 13:23:21', 'Commande', '12.94');

-- --------------------------------------------------------

--
-- Structure de la table `CommandePanierItem`
--

CREATE TABLE `CommandePanierItem` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `CommandePanierItem`
--

INSERT INTO `CommandePanierItem` (`id`, `commande_id`, `variant_id`, `quantite`, `prix_unitaire`) VALUES
(3, 5, 4, 2, '6.60'),
(4, 5, 14, 1, '8.44'),
(5, 6, 1, 1, '79.90'),
(6, 6, 16, 1, '4.78'),
(7, 7, 3, 1, '6.34'),
(8, 7, 15, 1, '2.18'),
(9, 8, 2, 1, '55.80'),
(10, 8, 1, 1, '79.90'),
(11, 9, 3, 1, '6.34'),
(12, 10, 5, 1, '2.24'),
(13, 11, 15, 1, '2.18'),
(14, 11, 16, 1, '4.78'),
(15, 12, 3, 1, '6.34'),
(16, 12, 4, 1, '6.60');

-- --------------------------------------------------------

--
-- Structure de la table `Product`
--

CREATE TABLE `Product` (
  `id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `category` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `stock_alert_threshold` int(11) DEFAULT 5 COMMENT 'Seuil pour "Bientôt épuisé"'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Déchargement des données de la table `Product`
--

INSERT INTO `Product` (`id`, `name`, `price`, `stock`, `image`, `category`, `description`, `stock_alert_threshold`) VALUES
(1, 'Fauteuil en tissu tout doux ', '79.90', 0, 'chaise.jpg', 2, 'Polyester/polyuréthane/acier. 54 x 47 x 81 cm.', 5),
(2, 'Table d’appoint en bois', '55.80', 0, 'table.jpg', 2, 'Bois de pin. Ø40 x 40 cm.', 5),
(3, 'Bougie parfumée Gingerbread', '6.34', 0, 'Bougie parfumée Gingerbread_default.jpg', 1, 'Cire de colza/coton/verre/métal. Ø7,3 x 9,1 cm. 45 heures.', 5),
(4, 'Bougie en forme de sapin ', '6.60', 0, 'sapinxldefault.jpg', 1, 'Cire de paraffine/coton. 9 x 16 cm. 30heures.', 5),
(5, 'Bougie 4 cm ', '2.24', 0, 'cube.jpg', 1, 'Cire de paraffine/coton. 4 x 4 x H4 cm. 4 heures.', 5),
(9, 'Panier', '4.08', 0, 'panier.jpg', 2, 'Métal. 16 x 16 x 7 cm.', 5),
(14, 'Organiseur de bureau ', '8.44', 0, 'organiseur.jpg', 3, 'Papier.', 5),
(15, 'Chemise à élastique ', '2.18', 0, 'chemise.jpg\r\n', 3, 'Carton. 32 x 24 cm.', 5),
(16, 'Porte-revues ', '4.78', 0, 'porte-revues.jpg', 3, 'Carton. 32 x 25 x 8,5 cm.', 5),
(31, 'Bougie bloc 11 cm', '2.64', 0, 'bougie_bloc.jpg', 1, 'Cire de paraffine/coton. 5,8 x 11 cm.', 5),
(32, 'Pouf en tissu tout doux', '29.90', 0, 'pouf.jpg', 2, 'Polyester. 25 x 39 cm.', 5),
(33, 'Carnet de notes à pois A5', '5.98', 0, 'livre.jpg', 3, '96 feuilles.', 5);

-- --------------------------------------------------------

--
-- Structure de la table `ProductImage`
--

CREATE TABLE `ProductImage` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ProductImage`
--

INSERT INTO `ProductImage` (`id`, `product_id`, `url`) VALUES
(1, 1, 'chaise1.jpg'),
(2, 1, 'chaise2.jpg'),
(3, 1, 'chaise3.jpg'),
(4, 1, 'chaise4.jpg'),
(5, 2, 'table1.jpg'),
(6, 2, 'table2.jpg'),
(7, 2, 'table3.jpg'),
(8, 2, 'table4.jpg'),
(9, 3, 'BougieparfuméeGingerbread1.jpg'),
(10, 4, 'sapinxl1.jpg'),
(11, 5, 'cube1.jpg'),
(12, 9, 'panier1.jpg'),
(13, 14, 'organiseur1.jpg'),
(14, 14, 'organiseur2.jpg'),
(15, 15, 'chemise.jpg'),
(16, 16, 'porte-revues.jpg'),
(17, 1, 'chaise.jpg'),
(18, 2, 'table.jpg'),
(19, 3, 'Bougie parfumée Gingerbread_default.jpg'),
(20, 4, 'sapinxldefault.jpg'),
(21, 5, 'cube.jpg'),
(22, 9, 'panier.jpg'),
(23, 14, 'organiseur.jpg'),
(32, 31, 'bougie_bloc.jpg'),
(33, 31, 'bougie_bloc1.jpg'),
(34, 32, 'pouf.jpg'),
(35, 32, 'pouf1.jpg'),
(36, 33, 'livre.jpg'),
(37, 33, 'livre1.jpg'),
(38, 33, 'livre2.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `Users`
--

CREATE TABLE `Users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `Users`
--

INSERT INTO `Users` (`id`, `username`, `first_name`, `last_name`, `date_of_birth`, `email`, `password_hash`) VALUES
(11, 'anna', 'Anna', 'Viroulaud', '2006-10-08', 'anna.viroulaud@gmail.com', '$2y$10$vGBMprgSymhtM1tKoaDxbunSJ2U0PHFmkfhy90CQ1325dQW2jz9wC');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Category`
--
ALTER TABLE `Category`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `CommandePanier`
--
ALTER TABLE `CommandePanier`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_client_statut` (`client_id`,`statut`);

--
-- Index pour la table `CommandePanierItem`
--
ALTER TABLE `CommandePanierItem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_panier_id` (`commande_id`),
  ADD KEY `idx_variant_id` (`variant_id`);

--
-- Index pour la table `Product`
--
ALTER TABLE `Product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`);

--
-- Index pour la table `ProductImage`
--
ALTER TABLE `ProductImage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Category`
--
ALTER TABLE `Category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `CommandePanier`
--
ALTER TABLE `CommandePanier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `CommandePanierItem`
--
ALTER TABLE `CommandePanierItem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `Product`
--
ALTER TABLE `Product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `ProductImage`
--
ALTER TABLE `ProductImage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT pour la table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `CommandePanierItem`
--
ALTER TABLE `CommandePanierItem`
  ADD CONSTRAINT `fk_panieritem_panier` FOREIGN KEY (`commande_id`) REFERENCES `CommandePanier` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Product`
--
ALTER TABLE `Product`
  ADD CONSTRAINT `category` FOREIGN KEY (`category`) REFERENCES `Category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `ProductImage`
--
ALTER TABLE `ProductImage`
  ADD CONSTRAINT `ProductImage_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `Product` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
