-- -------------------------------------------------------------
-- TablePlus 6.8.6(662)
--
-- https://tableplus.com/
--
-- Database: db
-- Generation Time: 2026-04-07 20:54:01.8250
-- -------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


DROP TABLE IF EXISTS `event_tickets`;
CREATE TABLE `event_tickets` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `ticket_title` varchar(100) NOT NULL,
  `ticket_price` decimal(10,2) NOT NULL,
  `ticket_quantity` int(11) NOT NULL,
  `ticket_status` tinyint(4) NOT NULL,
  PRIMARY KEY (`ticket_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_title` varchar(100) NOT NULL,
  `event_slug` varchar(100) NOT NULL,
  `event_start` datetime NOT NULL,
  `event_end` datetime NOT NULL,
  `event_status` tinyint(4) NOT NULL,
  PRIMARY KEY (`event_id`),
  UNIQUE KEY `event_slug` (`event_slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `item_metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`item_metadata`)),
  PRIMARY KEY (`item_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `order_payments`;
CREATE TABLE `order_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `stripe_payment_method_id` varchar(255) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_status` varchar(50) NOT NULL,
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `patron_id` int(11) NOT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `order_tax` decimal(10,2) NOT NULL,
  `order_fee` decimal(10,2) NOT NULL,
  `order_discount` decimal(10,2) NOT NULL,
  `order_subtotal` decimal(10,2) NOT NULL,
  `order_total` decimal(10,2) NOT NULL,
  `order_status` varchar(50) NOT NULL,
  `order_date` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `patrons`;
CREATE TABLE `patrons` (
  `patron_id` int(11) NOT NULL AUTO_INCREMENT,
  `patron_fname` varchar(50) NOT NULL,
  `patron_lname` varchar(50) NOT NULL,
  `patron_email` varchar(100) NOT NULL,
  `patron_created` timestamp NULL DEFAULT current_timestamp(),
  `patron_status` tinyint(4) NOT NULL,
  PRIMARY KEY (`patron_id`),
  UNIQUE KEY `patron_email` (`patron_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
  `subscription_id` int(11) NOT NULL AUTO_INCREMENT,
  `stripe_subscription_id` varchar(255) NOT NULL,
  `order_id` int(11) NOT NULL,
  `patron_id` int(11) NOT NULL,
  `subscription_title` varchar(100) NOT NULL,
  `subscription_amount` decimal(10,2) NOT NULL,
  `subscription_status` varchar(50) NOT NULL,
  `subscription_created` timestamp NULL DEFAULT current_timestamp(),
  `subscription_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `subscription_cancelled_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`subscription_id`),
  KEY `order_id` (`order_id`),
  KEY `patron_id` (`patron_id`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`patron_id`) REFERENCES `patrons` (`patron_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `event_tickets` (`ticket_id`, `event_id`, `ticket_title`, `ticket_price`, `ticket_quantity`, `ticket_status`) VALUES
(1, 1, 'General Admission', 50.00, 100, 1),
(2, 1, 'VIP Pass', 150.00, 20, 1),
(3, 1, 'Free Entry', 0.00, 50, 1),
(4, 2, 'Early Bird', 30.00, 200, 1),
(5, 2, 'Regular', 40.00, 300, 1),
(6, 3, 'Standard', 25.00, 150, 1),
(7, 3, 'Premium', 75.00, 50, 1);

INSERT INTO `events` (`event_id`, `event_title`, `event_slug`, `event_start`, `event_end`, `event_status`) VALUES
(1, 'Spring Gala', 'spring-gala', '2024-05-01 19:00:00', '2024-05-01 23:00:00', 1),
(2, 'Summer Festival', 'summer-festival', '2024-06-15 12:00:00', '2024-06-15 22:00:00', 1),
(3, 'Autumn Concert', 'autumn-concert', '2024-09-20 18:00:00', '2024-09-20 21:00:00', 1);



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;