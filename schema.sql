-- Adminer 4.2.5 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `forgotten_passwords`;
CREATE TABLE `forgotten_passwords` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(11) NOT NULL,
  `reminder` varchar(141) CHARACTER SET ascii NOT NULL,
  `reminded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `reminder` (`reminder`),
  KEY `subscriber_id` (`subscriber_id`),
  CONSTRAINT `forgotten_passwords_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `url` varchar(255) CHARACTER SET ascii NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;

CREATE TRIGGER `pages_bi` BEFORE INSERT ON `pages` FOR EACH ROW
  INSERT INTO page_visits (page_url, visited_at) VALUES(NEW.url, NOW());;

DELIMITER ;

DROP TABLE IF EXISTS `page_visits`;
CREATE TABLE `page_visits` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `page_url` varchar(255) CHARACTER SET ascii NOT NULL,
  `visited_at` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `page_id` (`page_url`),
  CONSTRAINT `page_visits_ibfk_1` FOREIGN KEY (`page_url`) REFERENCES `pages` (`url`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `parts`;
CREATE TABLE `parts` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `page_url` varchar(255) CHARACTER SET ascii NOT NULL,
  `expression` varchar(255) CHARACTER SET ascii NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `page_id,expression` (`page_url`,`expression`),
  CONSTRAINT `parts_ibfk_1` FOREIGN KEY (`page_url`) REFERENCES `pages` (`url`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `part_visits`;
CREATE TABLE `part_visits` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `part_id` int(11) NOT NULL,
  `visited_at` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `part_id` (`part_id`),
  CONSTRAINT `part_visits_ibfk_1` FOREIGN KEY (`part_id`) REFERENCES `parts` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `subscribed_parts`;
CREATE TABLE `subscribed_parts` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(11) NOT NULL,
  `part_id` int(11) NOT NULL,
  `interval` varchar(10) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `subscriber_id,part_id` (`subscriber_id`,`part_id`),
  KEY `part_id` (`part_id`),
  CONSTRAINT `subscribed_parts_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers` (`ID`) ON DELETE CASCADE,
  CONSTRAINT `subscribed_parts_ibfk_2` FOREIGN KEY (`part_id`) REFERENCES `parts` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `subscribers`;
CREATE TABLE `subscribers` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(160) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `verification_codes`;
CREATE TABLE `verification_codes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(11) NOT NULL,
  `code` varchar(91) CHARACTER SET ascii NOT NULL,
  `used` tinyint(4) NOT NULL DEFAULT '0',
  `used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `subscriber_id` (`subscriber_id`),
  CONSTRAINT `verification_codes_ibfk_1` FOREIGN KEY (`subscriber_id`) REFERENCES `subscribers` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2016-07-17 16:32:20
