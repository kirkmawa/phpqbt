-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 26, 2012 at 02:29 AM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.18

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `phpqbt`
--

-- --------------------------------------------------------

--
-- Table structure for table `bbservers`
--

CREATE TABLE IF NOT EXISTS `bbservers` (
  `ip` varchar(15) NOT NULL,
  `port` smallint(5) unsigned NOT NULL,
  `speed` int(11) NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bbservers`
--

INSERT INTO `bbservers` (`ip`, `port`, `speed`, `updated`) VALUES
('140.90.6.245', 1000, 1856, '2012-12-25 18:11:13'),
('140.90.128.146', 1000, 1783, '2012-12-25 18:10:33'),
('172.4.18.252', 1000, 127, '2012-12-25 18:11:48'),
('64.79.100.59', 2211, 157, '2012-12-25 18:10:58'),
('192.206.23.6', 1000, 1788, '2012-12-25 18:10:30'),
('140.90.24.118', 22, 1768, '2012-12-25 18:11:35'),
('216.248.137.10', 2211, 1731, '2012-12-25 18:11:17'),
('140.90.6.240', 1000, 1771, '2012-12-25 18:11:20'),
('76.107.51.99', 1000, 1902, '2012-12-25 18:11:31'),
('206.253.167.154', 2211, 147, '2012-12-25 18:11:55'),
('108.9.91.91', 2211, 1722, '2012-12-25 18:11:02'),
('71.184.33.106', 1000, 1767, '2012-12-25 18:10:51'),
('108.76.168.147', 1000, 1870, '2012-12-25 18:10:15'),
('136.145.85.36', 2211, 144, '2012-12-25 18:10:27'),
('140.90.128.133', 1000, 2201, '2012-12-25 18:11:37'),
('184.82.181.10', 2211, 391, '2012-12-25 18:10:48'),
('98.25.140.211', 2211, 133, '2012-12-25 18:11:10'),
('216.201.16.210', 1000, 1471, '2012-12-25 18:10:19'),
('24.54.148.4', 2211, 1714, '2012-12-25 18:11:28'),
('208.68.36.141', 2211, 155, '2012-12-25 18:01:34'),
('184.6.178.112', 1000, 99, '2012-12-25 18:11:42');
