# --------------------------------------------------------
# Host:                         192.168.17.247
# Server version:               5.1.41-3ubuntu12.10
# Server OS:                    debian-linux-gnu
# HeidiSQL version:             6.0.0.3603
# Date/time:                    2012-05-05 19:22:32
# --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

# Dumping database structure for phpqbt
CREATE DATABASE IF NOT EXISTS `phpqbt` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `phpqbt`;


# Dumping structure for table phpqbt.bbservers
DROP TABLE IF EXISTS `bbservers`;
CREATE TABLE IF NOT EXISTS `bbservers` (
  `ip` varchar(15) NOT NULL,
  `port` smallint(5) unsigned NOT NULL,
  `speed` int(11) NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Data exporting was unselected.

# Dumping structure for table phpqbt.state
DROP TABLE IF EXISTS `state`;
CREATE TABLE IF NOT EXISTS `state` (
  `name` varchar(20) NOT NULL,
  `value` varchar(45) DEFAULT NULL,
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Data exporting was unselected.
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
