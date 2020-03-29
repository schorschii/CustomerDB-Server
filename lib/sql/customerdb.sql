-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 18. Mrz 2020 um 21:09
-- Server-Version: 10.3.22-MariaDB-0+deb10u1
-- PHP-Version: 7.3.14-1~deb10u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `customerdb`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Client`
--

CREATE TABLE `Client` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `pending_activation_token` text DEFAULT NULL,
  `pending_reset_token` text DEFAULT NULL,
  `pending_deletion_token` text DEFAULT NULL,
  `last_login` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Customer`
--

CREATE TABLE `Customer` (
  `client_id` int(11) NOT NULL,
  `id` bigint(11) NOT NULL,
  `title` text CHARACTER SET utf8mb4 NOT NULL,
  `first_name` text CHARACTER SET utf8mb4 NOT NULL,
  `last_name` text CHARACTER SET utf8mb4 NOT NULL,
  `phone_home` text CHARACTER SET utf8mb4 NOT NULL,
  `phone_mobile` text CHARACTER SET utf8mb4 NOT NULL,
  `phone_work` text CHARACTER SET utf8mb4 NOT NULL,
  `email` text CHARACTER SET utf8mb4 NOT NULL,
  `street` text CHARACTER SET utf8mb4 NOT NULL,
  `zipcode` text CHARACTER SET utf8mb4 NOT NULL,
  `city` text CHARACTER SET utf8mb4 NOT NULL,
  `country` text CHARACTER SET utf8mb4 NOT NULL,
  `birthday` datetime DEFAULT NULL,
  `customer_group` text CHARACTER SET utf8mb4 NOT NULL,
  `newsletter` tinyint(11) NOT NULL,
  `notes` text CHARACTER SET utf8mb4 NOT NULL,
  `custom_fields` text CHARACTER SET utf8mb4 NOT NULL,
  `image` longblob DEFAULT NULL,
  `consent` longblob DEFAULT NULL,
  `last_modified` datetime NOT NULL DEFAULT current_timestamp(),
  `removed` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `Voucher`
--

CREATE TABLE `Voucher` (
  `client_id` int(11) NOT NULL,
  `id` bigint(11) NOT NULL,
  `original_value` double NOT NULL DEFAULT 0,
  `current_value` double NOT NULL DEFAULT 0,
  `voucher_no` text CHARACTER SET utf8mb4 NOT NULL,
  `from_customer` text CHARACTER SET utf8mb4 NOT NULL,
  `for_customer` text CHARACTER SET utf8mb4 NOT NULL,
  `issued` datetime NOT NULL DEFAULT current_timestamp(),
  `valid_until` datetime DEFAULT NULL,
  `redeemed` datetime DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 NOT NULL,
  `last_modified` datetime NOT NULL DEFAULT current_timestamp(),
  `removed` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `Client`
--
ALTER TABLE `Client`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `Customer`
--
ALTER TABLE `Customer`
  ADD PRIMARY KEY (`client_id`,`id`);

--
-- Indizes für die Tabelle `Voucher`
--
ALTER TABLE `Voucher`
  ADD PRIMARY KEY (`client_id`,`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `Client`
--
ALTER TABLE `Client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `Customer`
--
ALTER TABLE `Customer`
  ADD CONSTRAINT `FK_Customer_Client` FOREIGN KEY (`client_id`) REFERENCES `Client` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `Voucher`
--
ALTER TABLE `Voucher`
  ADD CONSTRAINT `FK_Voucher_Client` FOREIGN KEY (`client_id`) REFERENCES `Client` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
