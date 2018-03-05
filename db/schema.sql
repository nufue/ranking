SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `competition_types`;
CREATE TABLE `competition_types` (
  `id` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `year_from` int(11) DEFAULT NULL,
  `year_to` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `competition_types` (`id`, `description`, `year_from`, `year_to`) VALUES
('1_liga',	'1. liga',	NULL,	NULL),
('2_liga',	'2. liga',	NULL,	NULL),
('bodovany_pohar',	'Bodovaný pohárový závod',	NULL,	NULL),
('divize',	'Divize',	NULL,	NULL),
('memi_senioru',	'MeMi ČR seniorů',	NULL,	NULL),
('micr_hendikepovani',	'MiČR hendikepovaných',	NULL,	NULL),
('micr_u14',	'MiČR U14',	NULL,	2016),
('micr_u15',	'MiČR U15',	2017,	NULL),
('micr_u18',	'MiČR U18',	NULL,	2016),
('micr_u20',	'MiČR U20',	2017,	NULL),
('micr_u23',	'MiČR U23',	NULL,	2016),
('micr_u25',	'MiČR U25',	2017,	NULL),
('micr_zeny',	'MiČR žen',	NULL,	NULL),
('prebor_u10',	'Územní přebor U10',	NULL,	2012),
('prebor_u12',	'Územní přebor U12',	2013,	2016),
('prebor_u14',	'Územní přebor U14',	NULL,	2016),
('prebor_u15',	'Územní přebor U15',	2017,	NULL),
('prebor_u18',	'Územní přebor U18',	NULL,	2016),
('prebor_u20',	'Územní přebor U20',	2017,	NULL),
('prebor_u23',	'Územní přebor U23',	NULL,	2016),
('prebor_u25',	'Územní přebor U25',	2017,	NULL),
('uzemni_prebor',	'Územní přebor dospělých',	NULL,	NULL),
('zavod_u10',	'Závod U10',	NULL,	2012),
('zavod_u14',	'Závod U14',	NULL,	2016),
('zavod_u15',	'Závod U15',	2017,	NULL),
('zavod_u18',	'Závod U18',	NULL,	2016),
('zavod_u20',	'Závod U20',	2017,	NULL),
('zavod_u23',	'Závod U23',	NULL,	2016),
('zavod_u25',	'Závod U25',	2017,	NULL),
('zavod_zeny',	'Závod žen',	NULL,	NULL);

DROP TABLE IF EXISTS `competition_types_scoring`;
CREATE TABLE `competition_types_scoring` (
  `id_competition_type` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `scoring_table` int(10) unsigned NOT NULL,
  KEY `id_competition_type` (`id_competition_type`),
  KEY `scoring_table` (`scoring_table`),
  CONSTRAINT `competition_types_scoring_ibfk_1` FOREIGN KEY (`id_competition_type`) REFERENCES `competition_types` (`id`),
  CONSTRAINT `competition_types_scoring_ibfk_2` FOREIGN KEY (`scoring_table`) REFERENCES `scoring_tables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `competition_types_scoring` (`id_competition_type`, `scoring_table`) VALUES
('memi_senioru',	1),
('1_liga',	2),
('2_liga',	3),
('bodovany_pohar',	3),
('micr_zeny',	3),
('micr_u14',	3),
('micr_u15',	3),
('micr_u18',	3),
('micr_u20',	3),
('micr_u23',	3),
('micr_u25',	3),
('micr_hendikepovani',	3),
('uzemni_prebor',	4),
('zavod_zeny',	4),
('zavod_u10',	4),
('zavod_u14',	4),
('zavod_u15',	4),
('zavod_u18',	4),
('zavod_u20',	4),
('zavod_u23',	4),
('zavod_u25',	4),
('divize',	5),
('prebor_u10',	5),
('prebor_u12',	5),
('prebor_u14',	5),
('prebor_u15',	5),
('prebor_u18',	5),
('prebor_u20',	5),
('prebor_u23',	5),
('prebor_u25',	5);

DROP TABLE IF EXISTS `kategorie`;
CREATE TABLE `kategorie` (
  `kategorie` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`kategorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `kategorie` (`kategorie`) VALUES
('hendikep'),
('muz'),
('u10'),
('u10_zena'),
('u12'),
('u12_zena'),
('u14'),
('u14_zena'),
('u15'),
('u15_zena'),
('u18'),
('u18_zena'),
('u20'),
('u20_zena'),
('u23'),
('u23_zena'),
('u25'),
('u25_zena'),
('zena');

DROP TABLE IF EXISTS `leagues`;
CREATE TABLE `leagues` (
  `id` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `year_from` int(11) DEFAULT NULL,
  `year_to` int(11) DEFAULT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `leagues` (`id`, `name`, `year_from`, `year_to`, `order`) VALUES
('1',	'1. liga',	NULL,	NULL,	1),
('2a',	'2. liga, sk. A',	NULL,	NULL,	2),
('2b',	'2. liga, sk. B',	NULL,	NULL,	3),
('2c',	'2. liga, sk. C',	NULL,	NULL,	4);

DROP TABLE IF EXISTS `scoring_tables`;
CREATE TABLE `scoring_tables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `scoring_tables` (`id`) VALUES
(1),
(2),
(3),
(4),
(5);

DROP TABLE IF EXISTS `scoring_tables_rows`;
CREATE TABLE `scoring_tables_rows` (
  `id` int(10) unsigned NOT NULL,
  `rank` int(10) unsigned NOT NULL,
  `points` int(10) unsigned NOT NULL,
  UNIQUE KEY `id_rank` (`id`,`rank`),
  CONSTRAINT `scoring_tables_rows_ibfk_1` FOREIGN KEY (`id`) REFERENCES `scoring_tables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `scoring_tables_rows` (`id`, `rank`, `points`) VALUES
(1,	1,	40),
(1,	2,	38),
(1,	3,	36),
(1,	4,	34),
(1,	5,	32),
(1,	6,	30),
(1,	7,	28),
(1,	8,	26),
(1,	9,	24),
(1,	10,	22),
(1,	11,	20),
(1,	12,	18),
(1,	13,	16),
(1,	14,	14),
(1,	15,	12),
(1,	16,	10),
(1,	17,	8),
(1,	18,	6),
(1,	19,	4),
(1,	20,	2),
(1,	21,	1),
(2,	1,	36),
(2,	2,	33),
(2,	3,	31),
(2,	4,	29),
(2,	5,	27),
(2,	6,	25),
(2,	7,	22),
(2,	8,	19),
(2,	9,	16),
(2,	10,	13),
(2,	11,	10),
(2,	12,	7),
(2,	13,	4),
(2,	14,	1),
(3,	1,	30),
(3,	2,	27),
(3,	3,	25),
(3,	4,	23),
(3,	5,	21),
(3,	6,	19),
(3,	7,	16),
(3,	8,	13),
(3,	9,	9),
(3,	10,	6),
(3,	11,	3),
(3,	12,	1),
(4,	1,	25),
(4,	2,	22),
(4,	3,	19),
(4,	4,	16),
(4,	5,	13),
(4,	6,	11),
(4,	7,	10),
(4,	8,	8),
(4,	9,	5),
(4,	10,	3),
(4,	11,	2),
(4,	12,	1),
(5,	1,	15),
(5,	2,	12),
(5,	3,	10),
(5,	4,	8),
(5,	5,	6),
(5,	6,	4),
(5,	7,	2),
(5,	8,	1);

DROP TABLE IF EXISTS `team_name_override`;
CREATE TABLE `team_name_override` (
  `competitor` int(10) unsigned NOT NULL,
  `year` int(11) NOT NULL,
  `team_name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  UNIQUE KEY `competitor_year` (`competitor`,`year`),
  CONSTRAINT `team_name_override_ibfk_1` FOREIGN KEY (`competitor`) REFERENCES `zavodnici` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `tymy`;
CREATE TABLE `tymy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rok` int(10) unsigned NOT NULL,
  `liga` varchar(10) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `nazev_tymu` varchar(90) DEFAULT NULL,
  `kod` smallint(2) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rok_liga_kod` (`rok`,`liga`,`kod`),
  KEY `liga` (`liga`),
  CONSTRAINT `tymy_ibfk_1` FOREIGN KEY (`liga`) REFERENCES `leagues` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tymy_zavodnici`;
CREATE TABLE `tymy_zavodnici` (
  `id_tymu` int(10) unsigned NOT NULL,
  `id_zavodnika` int(10) unsigned NOT NULL,
  `poradi` smallint(5) unsigned NOT NULL,
  KEY `id_tymu` (`id_tymu`),
  KEY `id_zavodnika` (`id_zavodnika`),
  CONSTRAINT `tymy_zavodnici_ibfk_1` FOREIGN KEY (`id_tymu`) REFERENCES `tymy` (`id`),
  CONSTRAINT `tymy_zavodnici_ibfk_2` FOREIGN KEY (`id_zavodnika`) REFERENCES `zavodnici` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `zavodnici`;
CREATE TABLE `zavodnici` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registrace` varchar(15) DEFAULT NULL,
  `cele_jmeno` varchar(100) DEFAULT NULL,
  `registrovany` enum('A','N') DEFAULT 'A',
  PRIMARY KEY (`id`),
  UNIQUE KEY `registrace` (`registrace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `zavodnici_kategorie`;
CREATE TABLE `zavodnici_kategorie` (
  `id_zavodnika` int(10) unsigned NOT NULL,
  `rok` int(10) unsigned NOT NULL,
  `kategorie` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  UNIQUE KEY `id_zavodnika_rok` (`id_zavodnika`,`rok`),
  KEY `kategorie` (`kategorie`),
  CONSTRAINT `zavodnici_kategorie_ibfk_1` FOREIGN KEY (`id_zavodnika`) REFERENCES `zavodnici` (`id`),
  CONSTRAINT `zavodnici_kategorie_ibfk_2` FOREIGN KEY (`kategorie`) REFERENCES `kategorie` (`kategorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `zavodnici_zavody`;
CREATE TABLE `zavodnici_zavody` (
  `id_zavodu` int(10) unsigned NOT NULL,
  `id_zavodnika` int(10) unsigned NOT NULL,
  `tym` varchar(100) DEFAULT NULL,
  `cips1` bigint(20) DEFAULT NULL,
  `umisteni1` decimal(5,2) DEFAULT NULL,
  `cips2` bigint(20) DEFAULT NULL,
  `umisteni2` decimal(5,2) DEFAULT NULL,
  UNIQUE KEY `id_zavodu_id_zavodnika` (`id_zavodu`,`id_zavodnika`),
  KEY `id_zavodu` (`id_zavodu`),
  KEY `id_zavodnika` (`id_zavodnika`),
  CONSTRAINT `zavodnici_zavody_ibfk_1` FOREIGN KEY (`id_zavodu`) REFERENCES `zavody` (`id`),
  CONSTRAINT `zavodnici_zavody_ibfk_2` FOREIGN KEY (`id_zavodnika`) REFERENCES `zavodnici` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `zavody`;
CREATE TABLE `zavody` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rok` int(11) NOT NULL,
  `nazev` varchar(200) COLLATE utf8_czech_ci NOT NULL,
  `kategorie` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `typ` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `datum_od` date NOT NULL,
  `datum_do` date NOT NULL,
  `zobrazovat` enum('ano','ne') COLLATE utf8_czech_ci NOT NULL,
  `vysledky` enum('ano','ne') COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rok` (`rok`),
  KEY `typ2` (`typ`),
  CONSTRAINT `zavody_ibfk_1` FOREIGN KEY (`typ`) REFERENCES `competition_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;