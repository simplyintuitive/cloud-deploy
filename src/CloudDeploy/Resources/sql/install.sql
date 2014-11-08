-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 08, 2014 at 10:50 AM
-- Server version: 5.5.40-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cloud-deploy`
--

-- --------------------------------------------------------

--
-- Table structure for table `releases`
--

CREATE TABLE IF NOT EXISTS `releases` (
  `release_id` int(11) NOT NULL AUTO_INCREMENT,
  `deployment` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `release_date` datetime NOT NULL,
  PRIMARY KEY (`release_id`),
  KEY `deployment` (`deployment`,`release_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `upgrades`
--

CREATE TABLE IF NOT EXISTS `upgrades` (
  `upgrade_id` int(11) NOT NULL AUTO_INCREMENT,
  `release_id` int(11) NOT NULL,
  `node` varchar(255) NOT NULL,
  `upgrade_start_date` datetime NOT NULL,
  `upgrade_finish_date` datetime DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`upgrade_id`),
  KEY `release_id` (`release_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
