-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net
--
-- Host: mysql.ofscience.net
-- Generation Time: Dec 04, 2012 at 12:47 PM
-- Server version: 5.1.56
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `tanklab`
--
CREATE DATABASE `tanklab` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `tanklab`;

-- --------------------------------------------------------

--
-- Table structure for table `account_stats`
--

CREATE TABLE IF NOT EXISTS `account_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(168) NOT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `clan_url` text NOT NULL,
  `clan_img` text NOT NULL,
  `clan_name` text NOT NULL,
  `clan_motto` text NOT NULL,
  `clan_days` text NOT NULL,
  `clan_enrolled` text NOT NULL,
  `battles` int(11) NOT NULL,
  `victories` int(11) NOT NULL,
  `survived` int(11) NOT NULL,
  `destroyed` int(11) NOT NULL,
  `detected` int(11) NOT NULL,
  `hitratio` float NOT NULL,
  `damage` int(11) NOT NULL,
  `capture` int(11) NOT NULL,
  `defense` int(11) NOT NULL,
  `experience` int(11) NOT NULL,
  `avg_exp` float NOT NULL,
  `max_exp` int(11) NOT NULL,
  `global_rating_val` int(11) NOT NULL,
  `global_rating_place` int(11) NOT NULL,
  `vb_val` int(11) NOT NULL,
  `vb_place` int(11) NOT NULL,
  `avg_exp_val` int(11) NOT NULL,
  `avg_exp_place` int(11) NOT NULL,
  `victories_val` int(11) NOT NULL,
  `victories_place` int(11) NOT NULL,
  `battles_val` int(11) NOT NULL,
  `battles_place` int(11) NOT NULL,
  `capture_val` int(11) NOT NULL,
  `capture_place` int(11) NOT NULL,
  `defense_val` int(11) NOT NULL,
  `defense_place` int(11) NOT NULL,
  `frag_val` int(11) NOT NULL,
  `frag_place` int(11) NOT NULL,
  `detect_val` int(11) NOT NULL,
  `detect_place` int(11) NOT NULL,
  `experience_val` int(11) NOT NULL,
  `experience_place` int(11) NOT NULL,
  `efficiency` float NOT NULL,
  `updated` varchar(168) NOT NULL,
  `registered` varchar(168) NOT NULL,
  `clan_tag` varchar(168) NOT NULL,
  `defeats` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `acid` (`account_id`),
  KEY `stat_updatetime` (`updateTime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1669800 ;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(64) NOT NULL,
  `account_name` varchar(64) NOT NULL,
  `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clan_id` varchar(168) NOT NULL,
  `battles` int(11) NOT NULL,
  `wr` float NOT NULL,
  `eff` int(11) NOT NULL,
  `lastUpdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_name` (`account_name`),
  UNIQUE KEY `account_id` (`account_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=72349 ;

-- --------------------------------------------------------

--
-- Table structure for table `clan_stats`
--

CREATE TABLE IF NOT EXISTS `clan_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clan_id` varchar(168) NOT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `abbreviation` varchar(64) NOT NULL,
  `created_at` int(11) NOT NULL,
  `name` varchar(168) NOT NULL,
  `member_count` int(11) NOT NULL,
  `owner` varchar(168) NOT NULL,
  `motto` text NOT NULL,
  `clan_emblem_url` text NOT NULL,
  `clan_color` varchar(64) NOT NULL,
  `owner_id` varchar(168) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=147244 ;

-- --------------------------------------------------------

--
-- Table structure for table `clans`
--

CREATE TABLE IF NOT EXISTS `clans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clan_id` varchar(128) NOT NULL,
  `clan_name` varchar(128) NOT NULL,
  `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `refreshDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `clan_name` (`clan_name`),
  UNIQUE KEY `clan_id` (`clan_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1389 ;

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(64) NOT NULL,
  `dstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` text NOT NULL,
  `clan_id` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7221 ;

-- --------------------------------------------------------

--
-- Table structure for table `tank_list`
--

CREATE TABLE IF NOT EXISTS `tank_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(168) NOT NULL,
  `name` varchar(168) NOT NULL,
  `class` varchar(168) NOT NULL,
  `premium` tinyint(1) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=247 ;

-- --------------------------------------------------------

--
-- Table structure for table `tank_stats`
--

CREATE TABLE IF NOT EXISTS `tank_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(168) NOT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `name` text NOT NULL,
  `url` text NOT NULL,
  `image` text NOT NULL,
  `level` int(11) NOT NULL,
  `battles` int(11) NOT NULL,
  `victories` int(11) NOT NULL,
  `account_stats_update` varchar(168) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `WOTupdate` (`account_stats_update`),
  KEY `acid` (`account_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=113777199 ;
--
-- Database: `tanklab_eu`
--
CREATE DATABASE `tanklab_eu` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `tanklab_eu`;

-- --------------------------------------------------------

--
-- Table structure for table `account_stats`
--

CREATE TABLE IF NOT EXISTS `account_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(168) NOT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `clan_url` text NOT NULL,
  `clan_img` text NOT NULL,
  `clan_name` text NOT NULL,
  `clan_motto` text NOT NULL,
  `clan_days` text NOT NULL,
  `clan_enrolled` text NOT NULL,
  `battles` int(11) NOT NULL,
  `victories` int(11) NOT NULL,
  `survived` int(11) NOT NULL,
  `destroyed` int(11) NOT NULL,
  `detected` int(11) NOT NULL,
  `hitratio` float NOT NULL,
  `damage` int(11) NOT NULL,
  `capture` int(11) NOT NULL,
  `defense` int(11) NOT NULL,
  `experience` int(11) NOT NULL,
  `avg_exp` float NOT NULL,
  `max_exp` int(11) NOT NULL,
  `global_rating_val` int(11) NOT NULL,
  `global_rating_place` int(11) NOT NULL,
  `vb_val` int(11) NOT NULL,
  `vb_place` int(11) NOT NULL,
  `avg_exp_val` int(11) NOT NULL,
  `avg_exp_place` int(11) NOT NULL,
  `victories_val` int(11) NOT NULL,
  `victories_place` int(11) NOT NULL,
  `battles_val` int(11) NOT NULL,
  `battles_place` int(11) NOT NULL,
  `capture_val` int(11) NOT NULL,
  `capture_place` int(11) NOT NULL,
  `defense_val` int(11) NOT NULL,
  `defense_place` int(11) NOT NULL,
  `frag_val` int(11) NOT NULL,
  `frag_place` int(11) NOT NULL,
  `detect_val` int(11) NOT NULL,
  `detect_place` int(11) NOT NULL,
  `experience_val` int(11) NOT NULL,
  `experience_place` int(11) NOT NULL,
  `efficiency` float NOT NULL,
  `updated` varchar(168) NOT NULL,
  `registered` varchar(168) NOT NULL,
  `clan_tag` varchar(168) NOT NULL,
  `defeats` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `acid` (`account_id`),
  KEY `stat_updatetime` (`updateTime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=668465 ;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(64) NOT NULL,
  `account_name` varchar(64) NOT NULL,
  `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clan_id` varchar(168) NOT NULL,
  `battles` int(11) NOT NULL,
  `wr` float NOT NULL,
  `eff` int(11) NOT NULL,
  `lastUpdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_name` (`account_name`),
  UNIQUE KEY `account_id` (`account_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=58317 ;

-- --------------------------------------------------------

--
-- Table structure for table `clan_stats`
--

CREATE TABLE IF NOT EXISTS `clan_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clan_id` varchar(168) NOT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `abbreviation` varchar(64) NOT NULL,
  `created_at` int(11) NOT NULL,
  `name` varchar(168) NOT NULL,
  `member_count` int(11) NOT NULL,
  `owner` varchar(168) NOT NULL,
  `motto` text NOT NULL,
  `clan_emblem_url` text NOT NULL,
  `clan_color` varchar(64) NOT NULL,
  `owner_id` varchar(168) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37380 ;

-- --------------------------------------------------------

--
-- Table structure for table `clans`
--

CREATE TABLE IF NOT EXISTS `clans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clan_id` varchar(128) NOT NULL,
  `clan_name` varchar(128) NOT NULL,
  `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `refreshDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `clan_name` (`clan_name`),
  UNIQUE KEY `clan_id` (`clan_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=984 ;

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(64) NOT NULL,
  `dstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` text NOT NULL,
  `clan_id` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7221 ;

-- --------------------------------------------------------

--
-- Table structure for table `tank_list`
--

CREATE TABLE IF NOT EXISTS `tank_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(168) NOT NULL,
  `name` varchar(168) NOT NULL,
  `class` varchar(168) NOT NULL,
  `premium` tinyint(1) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=245 ;

-- --------------------------------------------------------

--
-- Table structure for table `tank_stats`
--

CREATE TABLE IF NOT EXISTS `tank_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(168) NOT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `name` text NOT NULL,
  `url` text NOT NULL,
  `image` text NOT NULL,
  `level` int(11) NOT NULL,
  `battles` int(11) NOT NULL,
  `victories` int(11) NOT NULL,
  `account_stats_update` varchar(168) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `WOTupdate` (`account_stats_update`),
  KEY `acid` (`account_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=46694040 ;
--
-- Database: `tanklab_sea`
--
CREATE DATABASE `tanklab_sea` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `tanklab_sea`;

-- --------------------------------------------------------

--
-- Table structure for table `account_stats`
--

CREATE TABLE IF NOT EXISTS `account_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(168) NOT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `clan_url` text NOT NULL,
  `clan_img` text NOT NULL,
  `clan_name` text NOT NULL,
  `clan_motto` text NOT NULL,
  `clan_days` text NOT NULL,
  `clan_enrolled` text NOT NULL,
  `battles` int(11) NOT NULL,
  `victories` int(11) NOT NULL,
  `survived` int(11) NOT NULL,
  `destroyed` int(11) NOT NULL,
  `detected` int(11) NOT NULL,
  `hitratio` float NOT NULL,
  `damage` int(11) NOT NULL,
  `capture` int(11) NOT NULL,
  `defense` int(11) NOT NULL,
  `experience` int(11) NOT NULL,
  `avg_exp` float NOT NULL,
  `max_exp` int(11) NOT NULL,
  `global_rating_val` int(11) NOT NULL,
  `global_rating_place` int(11) NOT NULL,
  `vb_val` int(11) NOT NULL,
  `vb_place` int(11) NOT NULL,
  `avg_exp_val` int(11) NOT NULL,
  `avg_exp_place` int(11) NOT NULL,
  `victories_val` int(11) NOT NULL,
  `victories_place` int(11) NOT NULL,
  `battles_val` int(11) NOT NULL,
  `battles_place` int(11) NOT NULL,
  `capture_val` int(11) NOT NULL,
  `capture_place` int(11) NOT NULL,
  `defense_val` int(11) NOT NULL,
  `defense_place` int(11) NOT NULL,
  `frag_val` int(11) NOT NULL,
  `frag_place` int(11) NOT NULL,
  `detect_val` int(11) NOT NULL,
  `detect_place` int(11) NOT NULL,
  `experience_val` int(11) NOT NULL,
  `experience_place` int(11) NOT NULL,
  `efficiency` float NOT NULL,
  `updated` varchar(168) NOT NULL,
  `registered` varchar(168) NOT NULL,
  `clan_tag` varchar(168) NOT NULL,
  `defeats` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `acid` (`account_id`),
  KEY `stat_updatetime` (`updateTime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1421219 ;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(64) NOT NULL,
  `account_name` varchar(64) NOT NULL,
  `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `clan_id` varchar(168) NOT NULL,
  `battles` int(11) NOT NULL,
  `wr` float NOT NULL,
  `eff` int(11) NOT NULL,
  `lastUpdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_name` (`account_name`),
  UNIQUE KEY `account_id` (`account_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=48624 ;

-- --------------------------------------------------------

--
-- Table structure for table `clan_stats`
--

CREATE TABLE IF NOT EXISTS `clan_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clan_id` varchar(168) NOT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `abbreviation` varchar(64) NOT NULL,
  `created_at` int(11) NOT NULL,
  `name` varchar(168) NOT NULL,
  `member_count` int(11) NOT NULL,
  `owner` varchar(168) NOT NULL,
  `motto` text NOT NULL,
  `clan_emblem_url` text NOT NULL,
  `clan_color` varchar(64) NOT NULL,
  `owner_id` varchar(168) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=155140 ;

-- --------------------------------------------------------

--
-- Table structure for table `clans`
--

CREATE TABLE IF NOT EXISTS `clans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clan_id` varchar(128) NOT NULL,
  `clan_name` varchar(128) NOT NULL,
  `createDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `refreshDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `clan_name` (`clan_name`),
  UNIQUE KEY `clan_id` (`clan_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=812 ;

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE IF NOT EXISTS `stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(64) NOT NULL,
  `dstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` text NOT NULL,
  `clan_id` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7221 ;

-- --------------------------------------------------------

--
-- Table structure for table `tank_list`
--

CREATE TABLE IF NOT EXISTS `tank_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(168) NOT NULL,
  `name` varchar(168) NOT NULL,
  `class` varchar(168) NOT NULL,
  `premium` tinyint(1) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=456 ;

-- --------------------------------------------------------

--
-- Table structure for table `tank_stats`
--

CREATE TABLE IF NOT EXISTS `tank_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(168) NOT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `name` text NOT NULL,
  `url` text NOT NULL,
  `image` text NOT NULL,
  `level` int(11) NOT NULL,
  `battles` int(11) NOT NULL,
  `victories` int(11) NOT NULL,
  `account_stats_update` varchar(168) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `WOTupdate` (`account_stats_update`),
  KEY `acid` (`account_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=87954612 ;
