SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DROP TABLE IF EXISTS `common_phrases`;
CREATE TABLE IF NOT EXISTS `common_phrases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `zh` varchar(500) NOT NULL,
  `en` varchar(500) NOT NULL,
  `sortorder` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=4 ;

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sn` varchar(20) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `forwho` varchar(255) NOT NULL,
  `contact_name` varchar(255) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(255) NOT NULL,
  `passenger_num` varchar(255) NOT NULL,
  `when` varchar(255) NOT NULL,
  `pickup_address` varchar(255) NOT NULL,
  `via` text NOT NULL,
  `dropoff_address` varchar(255) NOT NULL,
  `vehicle` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `status` int(11) NOT NULL,
  `created_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sn` (`sn`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=6 ;

DROP TABLE IF EXISTS `orders_for_driver`;
CREATE TABLE IF NOT EXISTS `orders_for_driver` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sn` varchar(20) NOT NULL,
  `vehicle_code` varchar(255) NOT NULL,
  `contact_name` varchar(255) NOT NULL,
  `contact_phone` varchar(255) NOT NULL,
  `when` varchar(255) NOT NULL,
  `pickup_address` varchar(255) NOT NULL,
  `pickup_coordinates` varchar(255) NOT NULL COMMENT '接客地点坐标。逗号分隔，经度在前，国内偏移后。',
  `via` text NOT NULL,
  `dropoff_address` varchar(255) NOT NULL,
  `dropoff_coordinates` varchar(255) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sn` (`sn`),
  KEY `vehicle_code` (`vehicle_code`(191))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=6 ;

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=2 ;

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `code_verify` varchar(255) NOT NULL,
  `lpn` varchar(255) NOT NULL COMMENT '车牌号',
  `model` varchar(255) NOT NULL COMMENT '品牌和型号',
  `color` varchar(255) NOT NULL,
  `driver_name` varchar(255) NOT NULL COMMENT '司机姓名',
  `driver_phone` varchar(255) NOT NULL COMMENT '司机电话',
  `device_id` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL COMMENT '位置坐标, 国内偏移, 逗号分隔, 经度在前',
  `locate_time` int(10) unsigned NOT NULL COMMENT '最后一次上报坐标的时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=6 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
