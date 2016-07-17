-- Adminer 4.2.4-dev MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `url` varchar(255) CHARACTER SET ascii NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DELIMITER ;;

CREATE TRIGGER `pages_bi` BEFORE INSERT ON `pages` FOR EACH ROW
  INSERT INTO page_visits (page_url, visited_at) VALUES(NEW.url, NOW());;

DELIMITER ;

DROP TABLE IF EXISTS `page_visits`;
CREATE TABLE `page_visits` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `page_url` varchar(255) CHARACTER SET ascii NOT NULL,
  `visited_at` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `parts`;
CREATE TABLE `parts` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `page_url` varchar(255) CHARACTER SET ascii NOT NULL,
  `expression` varchar(255) CHARACTER SET ascii NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `page_id,expression` (`page_url`,`expression`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `part_visits`;
CREATE TABLE `part_visits` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `part_id` int(11) NOT NULL,
  `visited_at` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `subscribed_parts`;
CREATE TABLE `subscribed_parts` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(11) NOT NULL,
  `part_id` int(11) NOT NULL,
  `interval` varchar(10) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `subscriber_id,part_id` (`subscriber_id`,`part_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `subscribers`;
CREATE TABLE `subscribers` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- 2016-07-14 20:23:23
