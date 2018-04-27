CREATE TABLE `team_members_count` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `count` int(11) NOT NULL,
  `year_from` int(11) DEFAULT NULL,
  `year_to` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `team_members_count` (`id`, `count`, `year_from`, `year_to`) VALUES
(1,	13,	NULL,	2017),
(2,	14,	2018,	NULL);
