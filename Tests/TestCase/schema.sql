-- Adminer 4.2.4-dev MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) CHARACTER SET ascii NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `page_visits`;
CREATE TABLE `page_visits` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `visited_at` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `parts`;
CREATE TABLE `parts` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `expression` varchar(255) CHARACTER SET ascii NOT NULL,
  `content` text COLLATE utf8_czech_ci NOT NULL,
  `interval` varchar(10) CHARACTER SET ascii NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `page_id,subscriber_id,expression` (`page_id`,`subscriber_id`,`expression`),
  KEY `subscriber_id` (`subscriber_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `part_visits`;
CREATE TABLE `part_visits` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `part_id` int(11) NOT NULL,
  `visited_at` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `subscribers`;
CREATE TABLE `subscribers` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

DROP TABLE IF EXISTS `forgotten_passwords`;
CREATE TABLE `forgotten_passwords` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(11) NOT NULL,
  `reminder` varchar(141) CHARACTER SET latin1 NOT NULL,
  `reminded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `reminder` (`reminder`),
  KEY `subscriber_id` (`subscriber_id`)
) ENGINE=MyIsam DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

DROP TABLE IF EXISTS `verification_codes`;
CREATE TABLE `verification_codes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(11) NOT NULL,
  `code` varchar(91) CHARACTER SET latin1 NOT NULL,
  `used` tinyint(4) NOT NULL DEFAULT '0',
  `used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `subscriber_id` (`subscriber_id`)
) ENGINE=MyIsam DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `part_id` int(11) NOT NULL,
  `sent_at` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyIsam DEFAULT COLLATE=utf8_czech_ci;


-- 2016-06-18 07:55:08
