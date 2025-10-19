-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 05:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spiceshelf`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(6, 'admin', 'admin@spiceshelf.com', '$2y$10$cJaNYv82L4NFwLM7PehZDu2vWMuRFoAAYZu9SprLPh1jcqdtuepjC', '2025-03-20 21:29:35');

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

CREATE TABLE `ads` (
  `ad_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `url` varchar(255) NOT NULL,
  `position` enum('sidebar','popup') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ads`
--

INSERT INTO `ads` (`ad_id`, `title`, `image`, `url`, `position`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(16, 'Test', 'uploads/ads/1746761318_juliana.jpg', 'https://www.unikl.edu.my/', 'sidebar', '2025-05-01', '2025-05-05', 'inactive', '2025-05-09 03:28:38', '2025-05-09 03:32:53'),
(17, 'test1', 'uploads/ads/1746764904_cauliflower-crust-pizza-lead-6778185f4ab16.jpg', 'https://www.unikl.edu.my/', 'sidebar', '2025-05-09', '2025-05-17', 'active', '2025-05-09 04:28:24', '2025-05-08 22:29:25');

-- --------------------------------------------------------

--
-- Table structure for table `ad_clicks`
--

CREATE TABLE `ad_clicks` (
  `click_id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ad_clicks`
--

INSERT INTO `ad_clicks` (`click_id`, `ad_id`, `user_id`, `ip_address`, `timestamp`) VALUES
(34, 16, NULL, '127.0.0.1', '2025-05-09 11:31:21'),
(35, 17, NULL, '127.0.0.1', '2025-05-09 12:28:39');

-- --------------------------------------------------------

--
-- Table structure for table `ad_impressions`
--

CREATE TABLE `ad_impressions` (
  `impression_id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ad_impressions`
--

INSERT INTO `ad_impressions` (`impression_id`, `ad_id`, `user_id`, `ip_address`, `timestamp`) VALUES
(327, 16, NULL, '127.0.0.1', '2025-05-09 11:28:52'),
(331, 16, NULL, '127.0.0.1', '2025-05-09 11:29:28'),
(332, 16, NULL, '127.0.0.1', '2025-05-09 11:31:27'),
(333, 16, NULL, '127.0.0.1', '2025-05-09 11:32:28'),
(334, 17, 25, '127.0.0.1', '2025-05-09 12:28:29'),
(335, 17, NULL, '127.0.0.1', '2025-05-09 12:28:31'),
(336, 17, NULL, '127.0.0.1', '2025-05-10 23:04:40'),
(337, 17, NULL, '127.0.0.1', '2025-05-10 23:04:48'),
(338, 17, NULL, '::1', '2025-05-10 23:09:33'),
(339, 17, 1, '::1', '2025-05-10 23:09:41'),
(340, 17, 1, '::1', '2025-05-10 23:09:51'),
(341, 17, NULL, '127.0.0.1', '2025-05-10 23:11:22');

-- --------------------------------------------------------

--
-- Table structure for table `allergies`
--

CREATE TABLE `allergies` (
  `allergy_id` int(11) NOT NULL,
  `allergy_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allergies`
--

INSERT INTO `allergies` (`allergy_id`, `allergy_name`) VALUES
(3, 'Dairy'),
(4, 'Eggs'),
(8, 'Fish'),
(9, 'None'),
(1, 'Peanuts'),
(5, 'Shellfish'),
(6, 'Soy'),
(2, 'Tree Nuts'),
(7, 'Wheat');

-- --------------------------------------------------------

--
-- Table structure for table `allergy_ingredient_mapping`
--

CREATE TABLE `allergy_ingredient_mapping` (
  `allergy_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allergy_ingredient_mapping`
--

INSERT INTO `allergy_ingredient_mapping` (`allergy_id`, `ingredient_id`) VALUES
(1, 63),
(2, 64),
(2, 65),
(2, 66),
(2, 67),
(3, 9),
(3, 10),
(3, 11),
(3, 36),
(3, 55),
(3, 56),
(3, 80),
(3, 81),
(4, 8),
(4, 36),
(5, 40),
(5, 82),
(6, 34),
(6, 70),
(6, 71),
(7, 19),
(7, 29),
(7, 57),
(7, 73),
(7, 75),
(8, 39),
(8, 41),
(8, 42),
(8, 68);

-- --------------------------------------------------------

--
-- Table structure for table `dietary_preferences`
--

CREATE TABLE `dietary_preferences` (
  `preference_id` int(11) NOT NULL,
  `preference_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dietary_preferences`
--

INSERT INTO `dietary_preferences` (`preference_id`, `preference_name`) VALUES
(4, 'Gluten-Free'),
(5, 'Keto'),
(7, 'None'),
(6, 'Paleo'),
(3, 'Pescatarian'),
(2, 'Vegan'),
(1, 'Vegetarian');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `recipe_id`, `created_at`) VALUES
(118, 19, 41, '2025-01-21 01:38:39'),
(119, 21, 47, '2025-01-21 02:25:44'),
(120, 21, 46, '2025-01-21 02:25:46'),
(123, 17, 47, '2025-03-20 08:29:18'),
(125, 25, 50, '2025-03-24 21:33:44'),
(126, 25, 62, '2025-03-24 21:33:46'),
(128, 17, 42, '2025-03-25 21:58:35'),
(129, 17, 43, '2025-03-25 21:58:37'),
(132, 25, 64, '2025-05-09 03:35:46'),
(133, 25, 53, '2025-05-09 03:35:50');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`ingredient_id`, `name`) VALUES
(65, 'Almonds'),
(51, 'Apple'),
(78, 'Arborio Rice'),
(69, 'Avocado'),
(52, 'Banana'),
(21, 'Basil'),
(7, 'Beef'),
(72, 'Beetroot'),
(16, 'Bell Pepper'),
(54, 'Blueberry'),
(57, 'Bread'),
(44, 'Broccoli'),
(31, 'Brown Sugar'),
(11, 'Butter'),
(59, 'Cabbage'),
(4, 'Carrot'),
(64, 'Cashews'),
(45, 'Cauliflower'),
(9, 'Cheese'),
(6, 'Chicken Breast'),
(62, 'Chickpeas'),
(26, 'Chili'),
(27, 'Cinnamon'),
(55, 'Coconut Milk'),
(48, 'Corn'),
(75, 'Couscous'),
(82, 'Crab'),
(15, 'Cucumber'),
(8, 'Egg'),
(58, 'Eggplant'),
(39, 'Fish'),
(29, 'Flour'),
(3, 'Garlic'),
(25, 'Ginger'),
(37, 'Ground Beef'),
(32, 'Honey'),
(77, 'Kale'),
(35, 'Ketchup'),
(43, 'Lamb'),
(50, 'Lemon'),
(61, 'Lentils'),
(13, 'Lettuce'),
(49, 'Lime'),
(36, 'Mayonnaise'),
(10, 'Milk'),
(80, 'Mozarella'),
(17, 'Mushroom'),
(73, 'Oats'),
(12, 'Olive Oil'),
(2, 'Onion'),
(81, 'Parmesan'),
(22, 'Parsley'),
(19, 'Pasta'),
(63, 'Peanuts'),
(47, 'Peas'),
(67, 'Pine Nuts'),
(38, 'Pork'),
(5, 'Potato'),
(60, 'Pumpkin'),
(74, 'Quinoa'),
(18, 'Rice'),
(24, 'Rosemary'),
(41, 'Salmon'),
(20, 'Salt'),
(68, 'Seaweed'),
(40, 'Shrimp'),
(34, 'Soy Sauce'),
(14, 'Spinach'),
(53, 'Strawberry'),
(30, 'Sugar'),
(71, 'Tempeh'),
(23, 'Thyme'),
(70, 'Tofu'),
(1, 'Tomato'),
(42, 'Tuna'),
(28, 'Vanilla Extract'),
(33, 'Vinegar'),
(66, 'Walnuts'),
(56, 'Yogurt'),
(46, 'Zucchini');

-- --------------------------------------------------------

--
-- Table structure for table `ingredient_measurement_defaults`
--

CREATE TABLE `ingredient_measurement_defaults` (
  `id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `measurement_id` int(11) NOT NULL,
  `is_default` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredient_measurement_defaults`
--

INSERT INTO `ingredient_measurement_defaults` (`id`, `ingredient_id`, `measurement_id`, `is_default`) VALUES
(1, 10, 1, 1),
(2, 10, 6, 0),
(3, 10, 15, 0),
(4, 12, 3, 1),
(5, 12, 1, 0),
(6, 33, 3, 1),
(7, 33, 1, 0),
(8, 34, 3, 1),
(9, 34, 1, 0),
(10, 32, 3, 1),
(11, 32, 1, 0),
(12, 55, 1, 1),
(13, 55, 20, 0),
(14, 35, 3, 1),
(15, 36, 3, 1),
(16, 36, 1, 0),
(17, 20, 2, 1),
(18, 20, 10, 0),
(19, 27, 2, 1),
(20, 21, 3, 0),
(21, 21, 24, 1),
(22, 22, 3, 0),
(23, 22, 24, 1),
(24, 23, 2, 0),
(25, 23, 24, 1),
(26, 24, 2, 0),
(27, 24, 24, 1),
(28, 25, 3, 1),
(29, 25, 2, 0),
(30, 3, 19, 1),
(31, 3, 2, 0),
(32, 1, 27, 1),
(33, 1, 1, 0),
(34, 2, 27, 1),
(35, 2, 1, 0),
(36, 4, 27, 1),
(37, 4, 1, 0),
(38, 5, 27, 1),
(39, 5, 1, 0),
(40, 16, 27, 1),
(41, 16, 1, 0),
(42, 14, 1, 1),
(43, 14, 4, 0),
(44, 17, 1, 1),
(45, 17, 4, 0),
(46, 15, 27, 1),
(47, 15, 1, 0),
(48, 13, 1, 1),
(49, 13, 27, 0),
(50, 44, 1, 1),
(51, 44, 4, 0),
(52, 45, 27, 1),
(53, 45, 1, 0),
(54, 59, 1, 0),
(55, 59, 27, 1),
(56, 51, 27, 1),
(57, 51, 1, 0),
(58, 52, 27, 1),
(59, 53, 1, 1),
(60, 53, 4, 0),
(61, 54, 1, 1),
(62, 54, 4, 0),
(63, 50, 27, 1),
(64, 50, 3, 0),
(65, 49, 27, 1),
(66, 49, 3, 0),
(67, 69, 27, 1),
(68, 69, 1, 0),
(69, 6, 18, 1),
(70, 6, 4, 0),
(71, 7, 4, 1),
(72, 7, 9, 0),
(73, 8, 27, 1),
(74, 9, 1, 1),
(75, 9, 4, 0),
(76, 70, 4, 1),
(77, 70, 1, 0),
(78, 71, 4, 1),
(79, 71, 1, 0),
(80, 39, 4, 1),
(81, 39, 27, 0),
(82, 41, 4, 1),
(83, 41, 27, 0),
(84, 40, 4, 1),
(85, 40, 27, 0),
(86, 18, 1, 1),
(87, 18, 4, 0),
(88, 19, 1, 1),
(89, 19, 4, 0),
(90, 29, 1, 1),
(91, 29, 4, 0),
(92, 73, 1, 1),
(93, 73, 4, 0),
(94, 57, 17, 1),
(95, 57, 27, 0),
(96, 30, 3, 0),
(97, 30, 1, 1),
(98, 31, 3, 0),
(99, 31, 1, 1),
(100, 63, 1, 1),
(101, 63, 4, 0),
(102, 65, 1, 1),
(103, 65, 4, 0),
(104, 66, 1, 1),
(105, 66, 4, 0),
(106, 64, 1, 1),
(107, 64, 4, 0),
(108, 62, 1, 1),
(109, 62, 20, 0),
(110, 61, 1, 1),
(111, 61, 4, 0),
(112, 11, 3, 1),
(113, 11, 1, 0),
(114, 56, 1, 1),
(115, 56, 4, 0),
(116, 81, 1, 0),
(117, 81, 3, 1),
(118, 80, 1, 1),
(119, 80, 4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `meal_plans`
--

CREATE TABLE `meal_plans` (
  `meal_plan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `meal_type` enum('Breakfast','Lunch','Dinner','Snack') NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `serving_size` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_plans`
--

INSERT INTO `meal_plans` (`meal_plan_id`, `user_id`, `meal_date`, `meal_type`, `recipe_id`, `serving_size`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-03-19', 'Breakfast', 55, 3, '', '2025-03-19 18:14:31', '2025-03-19 18:14:31'),
(2, 1, '2025-03-21', 'Dinner', 50, 10, '', '2025-03-19 18:17:39', '2025-03-19 18:17:39'),
(3, 1, '2025-04-01', 'Breakfast', 47, 1, '', '2025-03-19 18:37:10', '2025-03-19 18:37:10'),
(4, 1, '2025-03-19', 'Snack', 54, 5, '', '2025-03-19 18:40:22', '2025-03-19 18:40:22'),
(5, 1, '2025-03-19', 'Lunch', 50, 3, '', '2025-03-19 18:40:55', '2025-03-19 18:40:55'),
(6, 1, '2025-04-03', 'Breakfast', 53, 1, '', '2025-03-19 19:03:51', '2025-03-19 19:03:51'),
(7, 17, '2025-03-20', 'Breakfast', 43, 4, '', '2025-03-20 08:29:40', '2025-03-20 08:29:40'),
(8, 17, '2025-03-20', 'Breakfast', 54, 2, '', '2025-03-20 08:30:10', '2025-03-20 08:30:10'),
(9, 17, '2025-03-22', 'Lunch', 45, 4, '', '2025-03-20 08:30:34', '2025-03-20 08:30:34'),
(10, 25, '2025-03-30', 'Lunch', 62, 8, 'lunch party with friends!!', '2025-03-24 21:34:47', '2025-03-24 21:34:47'),
(11, 17, '2025-04-09', 'Breakfast', 62, 1, '', '2025-04-09 07:30:47', '2025-04-09 07:30:47'),
(12, 17, '2025-04-21', 'Lunch', 57, 4, '', '2025-04-21 05:22:56', '2025-04-21 05:22:56'),
(13, 17, '2025-04-21', 'Dinner', 44, 4, '', '2025-04-21 05:23:34', '2025-04-21 05:23:34'),
(15, 17, '2025-04-22', 'Snack', 64, 4, '', '2025-04-21 06:25:34', '2025-04-21 06:25:34'),
(16, 26, '2025-05-01', 'Breakfast', 46, 4, '', '2025-05-01 15:13:14', '2025-05-01 15:13:14'),
(17, 17, '2025-05-01', 'Breakfast', 53, 1, '', '2025-05-01 15:41:46', '2025-05-01 15:41:46'),
(18, 1, '2025-05-01', 'Breakfast', 54, 1, '', '2025-05-01 16:04:19', '2025-05-01 16:04:19'),
(19, 26, '2025-05-01', 'Lunch', 54, 1, '', '2025-05-01 16:08:42', '2025-05-01 16:08:42'),
(20, 25, '2025-05-01', 'Breakfast', 62, 10, '', '2025-05-01 16:26:40', '2025-05-01 16:26:40'),
(21, 1, '2025-05-07', 'Breakfast', 54, 4, '', '2025-05-07 03:22:40', '2025-05-07 03:22:40'),
(22, 1, '2025-05-07', 'Lunch', 50, 5, '', '2025-05-07 03:24:03', '2025-05-07 03:24:03');

-- --------------------------------------------------------

--
-- Table structure for table `measurements`
--

CREATE TABLE `measurements` (
  `measurement_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `measurements`
--

INSERT INTO `measurements` (`measurement_id`, `name`) VALUES
(21, 'bottle'),
(23, 'box'),
(25, 'bunch'),
(20, 'can'),
(19, 'clove'),
(1, 'cup'),
(11, 'dash'),
(15, 'fluid ounce'),
(14, 'gallon'),
(4, 'gram'),
(26, 'handful'),
(8, 'kilogram'),
(6, 'liter'),
(7, 'milliliter'),
(5, 'ounce'),
(22, 'packet'),
(18, 'piece'),
(10, 'pinch'),
(13, 'pint'),
(9, 'pound'),
(12, 'quart'),
(17, 'slice'),
(24, 'sprig'),
(16, 'stick'),
(3, 'tablespoon'),
(2, 'teaspoon'),
(27, 'whole');

-- --------------------------------------------------------

--
-- Table structure for table `pantry`
--

CREATE TABLE `pantry` (
  `pantry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `measurement_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiration_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pantry`
--

INSERT INTO `pantry` (`pantry_id`, `user_id`, `ingredient_id`, `quantity`, `measurement_id`, `added_at`, `expiration_date`) VALUES
(15, 14, 71, 350.00, 4, '2024-12-22 14:02:05', '2024-12-23'),
(17, 4, 65, 12.00, 21, '2025-01-08 04:53:24', '2025-01-09'),
(18, 4, 69, 3.00, 8, '2025-01-09 02:35:35', '2025-01-08'),
(19, 4, 7, 200.00, 4, '2025-01-10 03:44:54', '2025-01-13'),
(21, 19, 26, 1.00, 22, '2025-01-21 01:39:57', '2025-01-29'),
(22, 19, 18, 2.00, 22, '2025-01-21 07:52:46', '2025-02-21'),
(92, 25, 2, 2.00, 8, '2025-04-07 22:31:05', '2025-05-10'),
(93, 25, 3, 500.00, 4, '2025-04-07 22:31:29', '2025-05-10'),
(94, 25, 39, 300.00, 4, '2025-04-07 22:32:02', '2025-05-10'),
(95, 25, 20, 1.00, 8, '2025-04-07 22:32:17', '2025-05-10'),
(123, 17, 71, 60.00, 4, '2025-04-21 05:38:20', '2025-05-21'),
(125, 17, 44, 44.00, 4, '2025-04-21 06:26:28', '2025-05-21'),
(126, 17, 9, 0.67, 8, '2025-04-21 06:26:28', '2025-05-21'),
(127, 17, 8, 4.67, 18, '2025-04-21 06:26:28', '2025-05-21'),
(138, 1, 11, 9.50, 3, '2025-05-01 16:04:36', '2025-05-21'),
(139, 1, 4, 6.00, 27, '2025-05-01 16:04:36', '2025-05-21'),
(140, 1, 32, 2.00, 3, '2025-05-01 16:04:36', '2025-05-21'),
(141, 1, 20, 1.00, 10, '2025-05-01 16:04:36', '2025-05-21'),
(142, 1, 9, 0.00, 1, '2025-05-07 03:24:18', '2025-05-21'),
(143, 1, 6, 5.00, 18, '2025-05-07 03:24:18', '2025-05-21'),
(144, 1, 3, 7.50, 19, '2025-05-07 03:24:18', '2025-05-21'),
(145, 1, 17, 2.50, 1, '2025-05-07 03:24:18', '2025-05-21'),
(146, 1, 12, 2.50, 3, '2025-05-07 03:24:18', '2025-05-21');

-- --------------------------------------------------------

--
-- Table structure for table `preference_category_mapping`
--

CREATE TABLE `preference_category_mapping` (
  `preference_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `preference_category_mapping`
--

INSERT INTO `preference_category_mapping` (`preference_id`, `category_id`) VALUES
(1, 7),
(1, 12),
(1, 17),
(2, 5),
(2, 7),
(2, 17),
(3, 8),
(3, 17),
(4, 9),
(4, 17),
(5, 10),
(5, 11),
(5, 13),
(6, 13),
(6, 14),
(6, 17);

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipe_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `prep_time` int(11) NOT NULL,
  `cook_time` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `steps` varchar(700) DEFAULT NULL,
  `serving_size` int(11) DEFAULT NULL,
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`recipe_id`, `user_id`, `recipe_name`, `description`, `prep_time`, `cook_time`, `image`, `created_at`, `steps`, `serving_size`, `public`, `updated_at`) VALUES
(41, 17, 'QUINOA BUDDHA BOWL', 'A nourishing bowl packed with protein-rich quinoa, roasted vegetables, and tahini dressing', 20, 25, 'uploads/recipes/fullsizeoutput_6cd8-scaled.jpeg', '2025-01-21 00:35:54', '1. Cook quinoa according to package instructions\r\n2. Roast diced sweet potatoes and chickpeas at 400°F for 25 minutes\r\n3. Massage kale with olive oil and salt\r\n4. Make dressing by combining tahini, lemon juice, and minced garlic\r\n5. Assemble bowls with quinoa base, roasted vegetables, kale, and slic', 4, 1, '2025-01-21 00:35:54'),
(42, 17, 'MUSHROOM SPINACH RISOTTO', 'Creamy Italian risotto with wild mushrooms and fresh spinach', 15, 35, 'uploads/recipes/Mushroom-Spinach-Risotto-2-scaled.jpg', '2025-01-21 00:45:59', '1. Sauté mushrooms and set aside\r\n2. Cook onion and garlic in butter and oil\r\n3. Add rice and toast for 2 minutes\r\n4. Gradually add hot broth while stirring\r\n5. Add mushrooms and spinach at the end\r\n6. Finish with parmesan and butter', 6, 1, '2025-01-21 00:45:59'),
(43, 17, 'MEDITERRANEAN CHICKPEA SALAD', ' Fresh and light Mediterranean-style salad with protein-rich chickpeas', 15, 5, 'uploads/recipes/mediterranean-chickpeas-15.jpg', '2025-01-21 00:54:02', '1. Drain and rinse chickpeas\r\n2. Chop all vegetables into bite-sized pieces\r\n3. Combine all ingredients in a large bowl\r\n4. Mix dressing of olive oil, lemon juice, and oregano\r\n5. Toss everything together\r\n6. Chill for at least 30 minutes before serving', 6, 1, '2025-01-21 01:19:11'),
(44, 19, 'STIR-FRIED TOFU WITH VEGETABLES', 'A quick and protein-rich stir-fry with Asian flavors', 20, 15, 'uploads/recipes/tofu-stir-fry.c86d3627.jpg', '2025-01-21 01:24:34', '1. Press and cube tofu\r\n2. Cook rice separately\r\n3. Stir-fry tofu until golden\r\n4. Add vegetables and aromatics\r\n5. Season with soy sauce\r\n6. Serve over rice', 4, 1, '2025-01-21 01:24:34'),
(45, 19, 'LENTIL SALAD', 'A protein-packed cold salad perfect for meal prep', 15, 20, 'uploads/recipes/mediterranean-lentil-salad-23-scaled.jpeg', '2025-01-21 01:28:26', '1. Cook lentils until tender but firm\r\n2. Chop vegetables into small pieces\r\n3. Mix lemon juice, olive oil, and minced garlic\r\n4. Combine all ingredients\r\n5. Season to taste\r\n6. Chill before serving', 6, 1, '2025-01-21 01:28:26'),
(46, 19, 'SESAME SOBA WITH TEMPEH AND VEGETABLES', 'Japanese-inspired noodle dish with marinated tempeh and crunchy vegetables', 20, 15, 'uploads/recipes/cet3fnz.jpg', '2025-01-21 01:33:11', '1. Slice tempeh and marinate in soy sauce and ginger\r\n2. Pan-fry tempeh until golden brown\r\n3. Stir-fry vegetables with garlic\r\n4. Combine tempeh and vegetables\r\n5. Make sauce with remaining soy sauce and vinegar\r\n6. Toss everything together\r\n7. Garnish with sesame seeds', 4, 1, '2025-01-21 01:33:11'),
(47, 19, 'MEDITERRANEAN STUFFED EGGPLANT', 'Roasted eggplant halves filled with quinoa, vegetables, and herbs', 25, 40, 'uploads/recipes/Meaty-Mediterranean-Stuffed-Eggplant-3.jpg', '2025-01-21 01:37:07', '1. Halve eggplants lengthwise and score flesh\r\n2. Brush with olive oil and roast at 400°F until tender\r\n3. Cook quinoa according to package instructions\r\n4. Sauté onion, garlic, and diced tomatoes\r\n5. Mix with cooked quinoa and chickpeas\r\n6. Scoop out eggplant flesh, chop and add to quinoa mixture\r\n', 4, 1, '2025-01-21 01:37:07'),
(48, 20, 'CHEESY CAULIFLOWER BAKE', 'A creamy, cheesy cauliflower dish perfect for keto diet', 15, 25, 'uploads/recipes/__opt__aboutcom__coeus__resources__content_migration__simply_recipes__uploads__2020__11__Cheesy-Cauliflower-Bake-LEAD-5-80bf3eae38994418a34bfe24e8cfdf8b.jpg', '2025-01-21 01:59:32', '1. Cut cauliflower into florets\r\n2. Steam until just tender\r\n3. Make cheese sauce with butter, cream, and cheese\r\n4. Add minced garlic and seasonings\r\n5. Combine with cauliflower\r\n6. Bake until golden and bubbly', 6, 1, '2025-01-21 01:59:32'),
(49, 20, 'SALMON AVOCADO BOWL', 'A nutrient-rich keto bowl with fatty fish and healthy fats', 15, 15, 'uploads/recipes/Salmon-Avocado-Bowls-Updated-Square.jpeg', '2025-01-21 02:03:04', '1. Pan-sear salmon in butter\r\n2. Prepare bed of fresh spinach\r\n3. Slice avocado\r\n4. Assemble bowl with spinach base\r\n5. Top with salmon and avocado\r\n6. Dress with olive oil and lemon', 2, 1, '2025-01-21 02:03:04'),
(50, 20, 'MUSHROOM GARLIC BUTTER CHICKEN', 'Succulent chicken breast in a rich mushroom sauce', 10, 25, 'uploads/recipes/garlic-butter-chicken-4.jpg', '2025-01-21 02:06:01', '1. Season chicken breasts\r\n2. Pan-sear in olive oil until golden\r\n3. Remove chicken, add butter and mushrooms\r\n4. Add garlic and thyme\r\n5. Return chicken to pan\r\n6. Top with cheese and finish cooking', 4, 1, '2025-01-21 02:06:01'),
(51, 21, 'SIMPLE GARLIC PASTA', 'Quick and comforting pasta with garlic and olive oil', 5, 15, 'uploads/recipes/AR-269500-creamy-garlic-pasta-Beauties-2x1-bcd9cb83138849e4b17104a1cd51d063.jpg', '2025-01-21 02:14:34', '1. Cook pasta\r\n2. Sauté minced garlic in olive oil\r\n3. Toss with pasta\r\n4. Season and garnish', 2, 1, '2025-01-21 02:14:34'),
(52, 21, 'BASIC FRIED RICE', 'Simple and satisfying fried rice', 10, 15, 'uploads/recipes/284317easy-japanese-fried-ricelutzflcat4x3-74dc9380f1de4e599c57194083c6fb9d.jpg', '2025-01-21 02:16:45', '1. Scramble eggs, set aside\r\n2. Sauté onion and garlic\r\n3. Add rice, then eggs\r\n4. Season with soy sauce', 2, 1, '2025-01-21 02:16:45'),
(53, 21, 'QUICK TOMATO SOUP', 'Warming tomato soup in minutes', 5, 20, 'uploads/recipes/.9o.jpg', '2025-01-21 02:19:36', '1. Sauté onion and garlic\r\n2. Add chopped tomatoes\r\n3. Simmer 15 minutes\r\n4. Blend and serve', 4, 1, '2025-01-21 02:19:36'),
(54, 21, 'HONEY CARROTS', 'Sweet and simple side dish', 5, 15, 'uploads/recipes/214079-honey-roasted-carrots-DDMFS-4x3-6d416ea8a9cf48cfadc9209037a72fb4.jpg', '2025-01-21 02:21:37', '1. Slice carrots\r\n2. Steam until tender\r\n3. Toss with honey and butter\r\n4. Season lightly', 4, 1, '2025-01-21 02:21:37'),
(55, 21, 'CUCUMBER AVOCADO SALAD', 'Cucumber Avocado Salad', 10, 5, 'uploads/recipes/merlin_189372006_a9ab5728-f86c-4727-aedf-3f1f6f4633b8-mediumSquareAt3X.jpg', '2025-01-21 02:23:54', '1. Slice cucumber and avocado\r\n2. Drizzle with lemon and olive oil\r\n3. Season with salt\r\n4. Serve immediately', 2, 1, '2025-01-21 02:23:54'),
(57, 17, 'GARLICKY SPINACH CHICKPEA BOWL', 'A quick, nutritious bowl that takes minutes to prepare with just a few ingredients\r\n', 5, 10, 'uploads/recipes/garlic-butter-chicken-4.jpg', '2025-01-22 02:17:05', '1. Drain and rinse chickpeas\r\n2. Heat olive oil in pan\r\n3. Sauté minced garlic for 30 seconds\r\n4. Add chickpeas, cook until warm\r\n5. Add spinach, cook until just wilted\r\n6. Season with salt', 2, 1, '2025-04-09 07:29:39'),
(59, 1, 'testest', 'tett', 12, 20, 'uploads/recipes/Screenshot 2025-01-10 014735.png', '2025-03-21 14:17:35', 'hduiaosjoxqxnqiwjcsg', 2, 1, '2025-03-21 14:17:35'),
(60, 1, 'testest2', 'gduiashcnoi', 12, 20, 'uploads/recipes/Screenshot 2024-06-10 034107.png', '2025-03-21 14:18:15', 'dsdws', 2, 1, '2025-03-21 14:18:15'),
(62, 25, 'Cauliflower Pizza Crust', 'Cauliflower has invaded kitchens everywhere, and has gone way beyond just roasted. It has taken the form of mashed potatoes, toasty bread and even the center of our Thanksgiving table. That being said, my favorite use of cauliflower as a culinary master of disguise is in pizza crust. It might sound crazy, but it can be a delicious and healthy alternative to the classic. Try this easy, gluten-free pizza recipe once, and you’ll see why we’re cauliflower obsessed. ', 15, 45, 'uploads/recipes/cauliflower-crust-pizza-lead-6778185f4ab16.jpg', '2025-03-24 21:29:37', '1. Preheat oven to 425°. Boil cauliflower for 3–4 minutes, then drain and squeeze out excess water.  \r\n2. Pulse cauliflower in a food processor and drain.  \r\n3. Mix with egg, mozzarella, Parmesan, and salt.  \r\n4. Shape dough into a crust on a baking sheet and bake for 20 minutes.  \r\n5. Top with marinara, cheese, garlic, and tomatoes, then bake for 10 minutes.  \r\n6. Garnish with basil and balsamic glaze.', 4, 1, '2025-03-24 21:33:27'),
(64, 27, 'Burnt Cheesecake', 'This is a famous and simple dessert.', 15, 60, 'uploads/recipes/WhatsApp Image 2025-04-15 at 4.13.42 PM.jpeg', '2025-04-15 08:14:39', '1) Pukul cream cheese menggunakan paddle attachement  dan masukkan gula hingga creamy.\r\n2) Masukkan vanilla essence.\r\n3) Masukkan telur satu persatu dan secubit garam\r\n4) Kemudian masukkan whipping cream dan tepung.\r\n5) Masukkan adunan ke dalam loyang.\r\n6) Bakar pada suhu 180C selama 50 minit hingga 1 jam sehingga masak.', 6, 1, '2025-04-15 08:14:39');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_categories`
--

CREATE TABLE `recipe_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_categories`
--

INSERT INTO `recipe_categories` (`category_id`, `category_name`) VALUES
(1, 'Breakfast'),
(2, 'Dinner'),
(3, 'Lunch'),
(4, 'Snack'),
(5, 'Vegan'),
(6, 'Nut-Free'),
(7, 'Dairy-Free'),
(8, 'Pescatarian'),
(9, 'Gluten-Free'),
(10, 'Low-Carb'),
(11, 'Keto'),
(12, 'Vegetarian'),
(13, 'High-Protein'),
(14, 'Paleo'),
(15, 'Quick Meals'),
(16, 'Dessert'),
(17, 'Healthy'),
(18, 'Comfort Food'),
(19, 'Beverages'),
(20, 'Soups and Stews'),
(21, 'Appetizers'),
(22, 'Salads'),
(23, 'Side Dishes'),
(24, 'International Cuisine'),
(25, 'Brunch');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_category_mapping`
--

CREATE TABLE `recipe_category_mapping` (
  `recipe_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_category_mapping`
--

INSERT INTO `recipe_category_mapping` (`recipe_id`, `category_id`) VALUES
(41, 2),
(41, 3),
(41, 12),
(42, 2),
(42, 12),
(43, 3),
(43, 12),
(44, 2),
(44, 7),
(44, 12),
(45, 3),
(45, 7),
(45, 12),
(46, 2),
(46, 3),
(46, 7),
(46, 12),
(47, 2),
(47, 7),
(47, 12),
(48, 2),
(48, 3),
(48, 11),
(49, 2),
(49, 3),
(49, 11),
(50, 2),
(50, 11),
(51, 2),
(51, 3),
(52, 2),
(52, 3),
(53, 2),
(54, 2),
(55, 1),
(55, 3),
(57, 12),
(59, 2),
(60, 3),
(62, 2),
(62, 3),
(62, 11),
(64, 16);

-- --------------------------------------------------------

--
-- Table structure for table `recipe_comments`
--

CREATE TABLE `recipe_comments` (
  `comment_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_comments`
--

INSERT INTO `recipe_comments` (`comment_id`, `recipe_id`, `user_id`, `comment`, `created_at`) VALUES
(2, 55, 1, 'healthy snacks', '2025-05-10 15:11:01');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_ingredients`
--

CREATE TABLE `recipe_ingredients` (
  `id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `measurement_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_ingredients`
--

INSERT INTO `recipe_ingredients` (`id`, `recipe_id`, `ingredient_id`, `quantity`, `measurement_id`) VALUES
(41, 41, 62, 2.00, 20),
(42, 41, 74, 2.00, 1),
(43, 41, 12, 3.00, 3),
(44, 41, 3, 2.00, 19),
(45, 41, 69, 150.00, 4),
(46, 42, 78, 2.00, 1),
(47, 42, 17, 2.00, 1),
(48, 42, 14, 6.00, 1),
(49, 42, 2, 8.00, 5),
(50, 42, 3, 4.00, 19),
(51, 42, 11, 4.00, 3),
(52, 42, 12, 2.00, 3),
(53, 43, 62, 2.00, 20),
(54, 43, 1, 2.00, 1),
(55, 43, 15, 300.00, 4),
(56, 43, 22, 1.00, 22),
(57, 43, 12, 0.30, 1),
(58, 43, 50, 0.40, 1),
(59, 44, 70, 400.00, 4),
(60, 44, 44, 44.00, 4),
(61, 44, 3, 4.00, 19),
(62, 44, 25, 1.00, 3),
(63, 44, 34, 3.00, 3),
(64, 44, 18, 2.00, 1),
(65, 44, 12, 2.00, 3),
(66, 45, 61, 2.00, 1),
(67, 45, 1, 2.00, 27),
(68, 45, 15, 1.00, 27),
(69, 45, 2, 2.00, 27),
(70, 45, 22, 1.00, 26),
(71, 45, 12, 0.00, 1),
(72, 45, 50, 2.00, 27),
(73, 45, 3, 2.00, 19),
(74, 46, 71, 400.00, 4),
(75, 46, 59, 2.00, 1),
(76, 46, 16, 1.00, 27),
(77, 46, 3, 4.00, 19),
(78, 46, 25, 2.00, 3),
(79, 46, 34, 0.00, 1),
(80, 46, 33, 2.00, 3),
(81, 46, 12, 3.00, 3),
(82, 47, 58, 2.00, 27),
(83, 47, 74, 1.00, 1),
(84, 47, 1, 2.00, 27),
(85, 47, 2, 1.00, 27),
(86, 47, 3, 3.00, 19),
(87, 47, 62, 1.00, 20),
(88, 47, 22, 22.00, 4),
(89, 47, 12, 4.00, 3),
(90, 47, 50, 1.00, 27),
(91, 47, 23, 1.00, 3),
(92, 48, 45, 2.00, 27),
(93, 48, 9, 2.00, 1),
(94, 48, 11, 4.00, 3),
(95, 48, 3, 4.00, 19),
(96, 48, 23, 1.00, 3),
(97, 49, 41, 2.00, 27),
(98, 49, 69, 1.00, 27),
(99, 49, 14, 2.00, 1),
(100, 49, 12, 2.00, 3),
(101, 49, 50, 1.00, 27),
(102, 49, 11, 2.00, 3),
(103, 49, 22, 1.00, 26),
(104, 50, 6, 4.00, 18),
(105, 50, 17, 2.00, 1),
(106, 50, 11, 6.00, 3),
(107, 50, 3, 6.00, 19),
(108, 50, 9, 0.00, 1),
(109, 50, 12, 2.00, 3),
(110, 51, 19, 2.00, 1),
(111, 51, 3, 4.00, 19),
(112, 51, 12, 3.00, 3),
(113, 51, 22, 22.00, 4),
(114, 52, 18, 2.00, 1),
(115, 52, 8, 2.00, 27),
(116, 52, 2, 1.00, 27),
(117, 52, 34, 2.00, 3),
(118, 52, 3, 2.00, 19),
(119, 53, 1, 6.00, 27),
(120, 53, 2, 1.00, 27),
(121, 53, 3, 2.00, 19),
(122, 53, 12, 2.00, 3),
(123, 54, 4, 6.00, 27),
(124, 54, 32, 2.00, 3),
(125, 54, 11, 2.00, 3),
(126, 54, 20, 1.00, 10),
(127, 55, 15, 1.00, 27),
(128, 55, 69, 1.00, 27),
(129, 55, 50, 1.00, 27),
(130, 55, 12, 1.00, 3),
(133, 57, 3, 4.00, 19),
(134, 57, 14, 20.00, 4),
(136, 59, 65, 3.00, 21),
(137, 59, 4, 6.00, 10),
(138, 60, 65, 3.00, 21),
(140, 62, 45, 1.00, 27),
(141, 62, 8, 1.00, 27),
(142, 62, 9, 2.00, 1),
(143, 62, 1, 4.00, 27),
(144, 62, 3, 2.00, 19),
(145, 62, 21, 1.00, 11),
(147, 64, 9, 1.00, 8),
(148, 64, 8, 7.00, 18),
(149, 64, 30, 320.00, 4),
(150, 64, 28, 1.00, 3),
(151, 64, 29, 36.00, 4),
(152, 64, 20, 1.00, 2);

-- --------------------------------------------------------

--
-- Table structure for table `shopping_lists`
--

CREATE TABLE `shopping_lists` (
  `shopping_list_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shopping_lists`
--

INSERT INTO `shopping_lists` (`shopping_list_id`, `user_id`, `name`, `created_at`) VALUES
(9, 1, 'bolognese spaghetti', '2025-03-19 19:26:59'),
(10, 1, 'Shopping List for Mar 19 - Mar 25', '2025-03-19 19:29:37'),
(11, 17, 'Shopping list 1', '2025-03-20 08:30:55'),
(15, 1, 'Shopping List for Mar 20 - Mar 26', '2025-03-20 13:27:38'),
(16, 25, 'Shopping List for Mar 24 - Mar 30', '2025-03-24 21:35:17'),
(19, 17, 'Shopping List for Apr 21 - Apr 27', '2025-04-21 06:26:06'),
(20, 26, 'Shopping List for May 01 - May 07', '2025-05-01 15:22:15'),
(21, 1, 'Shopping List for May 01 - May 07', '2025-05-01 16:04:24'),
(22, 26, 'Shopping List for May 01 - May 07', '2025-05-01 16:59:26'),
(23, 1, 'Shopping List for May 07 - May 13', '2025-05-07 03:22:57'),
(24, 1, 'Shopping List for May 07 - May 13', '2025-05-07 03:23:31'),
(25, 1, 'Shopping List for May 07 - May 13', '2025-05-07 03:24:11');

-- --------------------------------------------------------

--
-- Table structure for table `shopping_list_items`
--

CREATE TABLE `shopping_list_items` (
  `id` int(11) NOT NULL,
  `shopping_list_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `measurement_id` int(11) NOT NULL,
  `purchased` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shopping_list_items`
--

INSERT INTO `shopping_list_items` (`id`, `shopping_list_id`, `ingredient_id`, `quantity`, `measurement_id`, `purchased`) VALUES
(57, 9, 1, 2.00, 27, 0),
(58, 10, 9, 1.00, 1, 0),
(59, 11, 11, 1.00, 3, 1),
(60, 11, 4, 3.00, 27, 1),
(61, 11, 62, 1.33, 20, 1),
(62, 11, 15, 0.67, 27, 1),
(63, 11, 15, 200.00, 4, 1),
(64, 11, 3, 1.33, 19, 1),
(65, 11, 32, 1.00, 3, 1),
(66, 11, 50, 1.33, 27, 1),
(67, 11, 50, 0.27, 1, 1),
(68, 11, 61, 1.33, 1, 1),
(69, 11, 12, 0.20, 1, 1),
(70, 11, 2, 1.33, 27, 1),
(71, 11, 22, 0.67, 22, 1),
(72, 11, 22, 0.67, 26, 1),
(73, 11, 20, 0.50, 10, 1),
(74, 11, 1, 1.33, 27, 1),
(75, 11, 1, 1.33, 1, 1),
(116, 15, 11, 15.00, 3, 1),
(117, 15, 9, 0.00, 1, 1),
(118, 15, 6, 10.00, 18, 1),
(119, 15, 3, 15.00, 19, 1),
(120, 16, 21, 2.00, 11, 1),
(121, 16, 45, 2.00, 27, 1),
(122, 16, 9, 4.00, 1, 1),
(123, 16, 8, 2.00, 27, 1),
(124, 16, 3, 4.00, 19, 1),
(125, 16, 1, 8.00, 27, 1),
(146, 19, 44, 44.00, 4, 1),
(147, 19, 9, 0.67, 8, 1),
(148, 19, 8, 4.67, 18, 1),
(149, 19, 29, 24.00, 4, 0),
(150, 19, 3, 12.00, 19, 0),
(151, 19, 25, 1.00, 3, 0),
(152, 19, 12, 2.00, 3, 0),
(153, 19, 18, 2.00, 1, 0),
(154, 19, 20, 0.67, 2, 0),
(155, 19, 34, 3.00, 3, 0),
(156, 19, 14, 40.00, 4, 0),
(157, 19, 30, 213.33, 4, 0),
(158, 19, 70, 400.00, 4, 0),
(159, 19, 28, 0.67, 3, 0),
(160, 20, 16, 1.00, 27, 1),
(161, 20, 59, 2.00, 1, 1),
(162, 20, 3, 4.00, 19, 1),
(163, 20, 25, 2.00, 3, 1),
(164, 20, 12, 3.00, 3, 1),
(165, 20, 34, 0.00, 1, 1),
(166, 20, 71, 400.00, 4, 1),
(167, 20, 33, 2.00, 3, 1),
(168, 21, 11, 0.50, 3, 1),
(169, 21, 4, 1.50, 27, 1),
(170, 21, 32, 0.50, 3, 1),
(171, 21, 20, 0.25, 10, 1),
(172, 22, 16, 1.00, 27, 0),
(173, 22, 11, 0.50, 3, 0),
(174, 22, 59, 2.00, 1, 0),
(175, 22, 4, 1.50, 27, 0),
(176, 22, 3, 4.00, 19, 0),
(177, 22, 25, 2.00, 3, 0),
(178, 22, 32, 0.50, 3, 0),
(179, 22, 12, 3.00, 3, 0),
(180, 22, 20, 0.25, 10, 0),
(181, 22, 34, 0.00, 1, 0),
(182, 22, 71, 400.00, 4, 0),
(183, 22, 33, 2.00, 3, 0),
(184, 23, 11, 1.50, 3, 1),
(185, 23, 4, 4.50, 27, 1),
(186, 23, 32, 1.50, 3, 1),
(187, 23, 20, 0.75, 10, 1),
(188, 25, 11, 7.50, 3, 1),
(189, 25, 9, 0.00, 1, 1),
(190, 25, 6, 5.00, 18, 1),
(191, 25, 3, 7.50, 19, 1),
(192, 25, 17, 2.50, 1, 1),
(193, 25, 12, 2.50, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `created_at`, `updated_at`, `last_login`, `status`) VALUES
(1, 'Juliana0311', 'julianakaryadi4@gmail.com', '$2y$10$B46tDxGMIlEN042qNmqJnuq5Fe88m33XeiZzuMJaJKerA/AchxkqO', '2024-12-08 03:12:16', '2025-05-10 15:09:40', '2025-05-10 23:09:40', 'active'),
(2, 'fira', 'att792993@hotmail.com', '$2y$10$ExJfNh5RjoKLzzTPyoEhAuJft6CFwYHk0Y1VHxbR6YCDTCjOZwX3O', '2024-12-09 04:59:56', '2025-05-08 19:37:29', NULL, 'inactive'),
(4, 'JulianaKaryadi', 'julianakaryadi@gmail.com', '$2y$10$DIpESOkxpbMycaEFEbG3p.KSXw0ML.iTpvTB4A60EJxFT.nVMEIyS', '2024-12-17 04:47:54', '2025-05-08 19:37:29', NULL, 'inactive'),
(5, 'mama', 'bioplusarewlop@mail.com', '$2y$10$qcdP7iLsWZZUUz6M2dqsuOv/VBR42NYCrnNiL5qDVJcP.WIjkG9X6', '2024-12-17 05:53:00', '2025-05-08 19:37:29', NULL, 'inactive'),
(6, 'Mika', 'leapfpartzabemen@mail.com', '$2y$10$TFU.4/6GGMfXnLWnTBzLWubGw5nzsUpOh6hXxYb3/0quuTR3OauCG', '2024-12-17 20:22:14', '2025-05-08 19:37:29', NULL, 'inactive'),
(7, 'Anis', 'gwv981824@hotmail.com', '$2y$10$y2irhExiOUtsnHk.fiwM1eSPnG8BJgFR7uKrHXvhkKP3mjQzqLbg6', '2024-12-17 20:26:41', '2025-05-08 19:37:29', NULL, 'inactive'),
(8, 'claire', '123Claire@gmail.com', '$2y$10$ZK9q3..LZFWvVXRw781nJu2vcUhp3hL9MdL2ED6YckFT4xs6FZ3sa', '2024-12-17 23:17:24', '2025-05-08 19:37:29', NULL, 'inactive'),
(13, 'jujube', 'julianakaryadi7@gmail.com', '$2y$10$ObN5hBHDSC7dL2.GRMMwQO9/kWVonTUS/vEQvO7ocPFo3SP95R7Rq', '2024-12-20 03:02:03', '2025-05-08 19:37:29', NULL, 'inactive'),
(14, 'Ana.my', 'julianakaryadi10@gmail.com', '$2y$10$JzD.sbPHIf9P.3ljqguzPuucUciFXVgZFmT59Tb.umA/weop7K0LK', '2024-12-22 13:05:09', '2025-05-08 19:37:29', NULL, 'inactive'),
(15, 'Mallika', 'malika09@gmail.com', '$2y$10$4Gwmcg6r/qc7xVMy6dIpDu5vTH2N/WiHMfqUaKRYY.1dTNRwfvSa.', '2024-12-27 09:43:37', '2025-05-08 19:37:29', NULL, 'inactive'),
(16, 'uau', 'zytech024@gmail.com', '$2y$10$AWFvZkCZ29UAS1YUUWthUeydyARV./2hXZyk/zPO5X6HULNiRWfJC', '2024-12-31 11:18:22', '2025-05-08 19:37:29', NULL, 'inactive'),
(17, 'aliya', 'aliyahelo@gmail.com', '$2y$10$OqGp3L63eR9iaOhGCFANq.7XDRb/lrmkPOrwdOpCCKZwhiuDGkd1i', '2025-01-01 12:47:43', '2025-05-08 21:31:29', '2025-05-09 05:31:29', 'active'),
(18, 'Juli', 'julia10@gmail.com', '$2y$10$fR879fwCPqsBFkKugdW2kuiQeyxOwiGZsMAHG3JbAF68CvvHMAkz6', '2025-01-10 03:36:20', '2025-05-08 19:37:29', NULL, 'inactive'),
(19, 'Rohaya_89', 'rohayya89@gmail.com', '$2y$10$3IufZGhG.xcIR5iUHXMvt.LTQI5TyJTHJtZjbDqt4Tl85UuC1/5ka', '2025-01-21 00:57:57', '2025-05-08 19:37:29', NULL, 'inactive'),
(20, 'Atiqah', 'atiqah23@gmail.com', '$2y$10$/2DKiDy6D9jv2hMDHyzvvueEasZlu0EQ5Srd6NWuXEOQrRmI4Cvku', '2025-01-21 01:56:48', '2025-05-08 19:37:29', NULL, 'inactive'),
(21, 'Maya', 'maya45@gmail.com', '$2y$10$QiZMQmhwIAP1RvIkzmhQ/OJ4JQBUbcHf.gTNso7YbS.ETKzlKnCZy', '2025-01-21 02:10:54', '2025-05-08 19:37:29', NULL, 'inactive'),
(22, 'Julia', 'julian@gmail.com', '$2y$10$qkpQjYGy8USZW5bSLn9qgOdpTO5sx7.fwh.SelgT.c2PZ6FZu8blC', '2025-01-21 07:49:10', '2025-05-08 19:37:29', NULL, 'inactive'),
(23, 'Shila98', 'shila98@gmail.com', '$2y$10$HS.a6QlyXlNPSdx9t2VeV.a7XNu5X38RTxPRUN24y9yQMw3tGMZSi', '2025-01-22 02:14:00', '2025-05-08 19:37:29', NULL, 'inactive'),
(24, 'yuna', 'yyuna@gmail.com', '$2y$10$754LtXS6FtSkarijD9w4X.mXrfC2BYib63mAAD8HxksvVW0MUeuJS', '2025-03-21 04:41:48', '2025-05-08 19:37:29', NULL, 'inactive'),
(25, 'anis29', 'as.29102024@gmail.com', '$2y$10$TtQ5841VXpjROgqR1zsnCO9cVCw33Wvu73EzKFXIbXtEzHbVMuT3q', '2025-03-24 21:21:00', '2025-05-09 04:32:30', '2025-05-09 12:32:30', 'active'),
(26, 'hepi', 'hepi0736@gmail.com', '$2y$10$jZhIJOlF9YWfhx3JB3vAUeAu7hb1AB2fEN9tYlisR2/gfaidDg1W.', '2025-03-28 12:47:47', '2025-05-10 15:11:26', '2025-05-10 23:11:26', 'active'),
(27, 'aleez_azman', 'alis200058@gmail.com', '$2y$10$/aP6t98p7Gk2Ru3B67T60e50YmNm4bNw8CpN2dsPB4BynQXzJ0OyW', '2025-04-15 08:02:26', '2025-05-08 19:37:29', NULL, 'inactive');

-- --------------------------------------------------------

--
-- Table structure for table `user_allergies`
--

CREATE TABLE `user_allergies` (
  `user_id` int(11) NOT NULL,
  `allergy_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_allergies`
--

INSERT INTO `user_allergies` (`user_id`, `allergy_id`) VALUES
(1, 9),
(2, 4),
(4, 1),
(4, 4),
(4, 8),
(5, 1),
(5, 3),
(5, 4),
(6, 3),
(6, 4),
(7, 7),
(8, 3),
(8, 4),
(8, 8),
(13, 9),
(14, 9),
(15, 9),
(16, 9),
(17, 9),
(18, 9),
(19, 3),
(20, 9),
(21, 9),
(22, 9),
(23, 9),
(24, 9),
(25, 9),
(26, 3),
(27, 9);

-- --------------------------------------------------------

--
-- Table structure for table `user_dietary_preferences`
--

CREATE TABLE `user_dietary_preferences` (
  `user_id` int(11) NOT NULL,
  `preference_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_dietary_preferences`
--

INSERT INTO `user_dietary_preferences` (`user_id`, `preference_id`) VALUES
(1, 7),
(2, 2),
(4, 7),
(5, 2),
(6, 1),
(7, 4),
(8, 3),
(13, 7),
(14, 7),
(15, 2),
(16, 7),
(17, 1),
(18, 7),
(19, 1),
(20, 5),
(21, 7),
(22, 7),
(23, 1),
(24, 7),
(25, 7),
(26, 7),
(27, 7);

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_notifications`
--

INSERT INTO `user_notifications` (`notification_id`, `user_id`, `notification_type`, `subject`, `message`, `status`, `sent_at`, `created_at`) VALUES
(1, 13, 'inactivity', 'Important: Your SpiceShelf Account Status', 'Dear jujube,\r\n\r\nWe noticed that you haven\'t logged into your SpiceShelf account recently. Per our inactive account policy, accounts that remain inactive for extended periods may be deleted.\r\n\r\nPlease log in to your account within the next 3 days to keep it active. If you do not log in, your account may be deleted along with all your recipes and data.\r\n\r\nThank you,\r\nThe SpiceShelf Team', 'pending', '2025-05-09 03:50:37', '2025-05-08 19:50:37'),
(2, 13, 'inactivity', 'Important: Your SpiceShelf Account Status', 'Dear jujube,\r\n\r\nWe noticed that you haven\'t logged into your SpiceShelf account recently. Per our inactive account policy, accounts that remain inactive for extended periods may be deleted.\r\n\r\nPlease log in to your account within the next 3 days to keep it active. If you do not log in, your account may be deleted along with all your recipes and data.\r\n\r\nThank you,\r\nThe SpiceShelf Team', 'pending', '2025-05-09 03:51:26', '2025-05-08 19:51:26'),
(3, 1, 'inactivity', 'Important: Your SpiceShelf Account Status', 'Dear Juliana0311,\r\n\r\nWe noticed that you haven\'t logged into your SpiceShelf account recently. Per our inactive account policy, accounts that remain inactive for extended periods may be deleted.\r\n\r\nPlease log in to your account within the next 3 days to keep it active. If you do not log in, your account may be deleted along with all your recipes and data.\r\n\r\nThank you,\r\nThe SpiceShelf Team', 'pending', '2025-05-09 03:52:10', '2025-05-08 19:52:10'),
(4, 1, 'inactivity', 'Important: Your SpiceShelf Account Status', 'Dear Juliana0311,\r\n\r\nWe noticed that you haven\'t logged into your SpiceShelf account recently. Per our inactive account policy, accounts that remain inactive for extended periods may be deleted.\r\n\r\nPlease log in to your account within the next 3 days to keep it active. If you do not log in, your account may be deleted along with all your recipes and data.\r\n\r\nThank you,\r\nThe SpiceShelf Team', 'failed', '2025-05-09 03:54:57', '2025-05-08 19:54:57'),
(5, 13, 'inactivity', 'Important: Your SpiceShelf Account Status', 'Dear jujube,\r\n\r\nWe noticed that you haven\'t logged into your SpiceShelf account recently. Per our inactive account policy, accounts that remain inactive for extended periods may be deleted.\r\n\r\nPlease log in to your account within the next 3 days to keep it active. If you do not log in, your account may be deleted along with all your recipes and data.\r\n\r\nThank you,\r\nThe SpiceShelf Team', 'sent', '2025-05-09 05:26:12', '2025-05-08 21:26:12'),
(6, 13, 'inactivity', 'Important: Your SpiceShelf Account Status 2', 'Dear jujube,\r\n\r\nWe noticed that you haven\'t logged into your SpiceShelf account recently. Per our inactive account policy, accounts that remain inactive for extended periods may be deleted.\r\n\r\nPlease log in to your account within the next 3 days to keep it active. If you do not log in, your account may be deleted along with all your recipes and data.\r\n\r\nThank you,\r\nThe SpiceShelf Team', 'sent', '2025-05-09 11:21:56', '2025-05-09 03:21:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ads`
--
ALTER TABLE `ads`
  ADD PRIMARY KEY (`ad_id`);

--
-- Indexes for table `ad_clicks`
--
ALTER TABLE `ad_clicks`
  ADD PRIMARY KEY (`click_id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Indexes for table `ad_impressions`
--
ALTER TABLE `ad_impressions`
  ADD PRIMARY KEY (`impression_id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Indexes for table `allergies`
--
ALTER TABLE `allergies`
  ADD PRIMARY KEY (`allergy_id`),
  ADD UNIQUE KEY `allergy_name` (`allergy_name`);

--
-- Indexes for table `allergy_ingredient_mapping`
--
ALTER TABLE `allergy_ingredient_mapping`
  ADD PRIMARY KEY (`allergy_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `dietary_preferences`
--
ALTER TABLE `dietary_preferences`
  ADD PRIMARY KEY (`preference_id`),
  ADD UNIQUE KEY `preference_name` (`preference_name`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `fk_recipe` (`recipe_id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD KEY `idx_ingredients_name` (`name`);

--
-- Indexes for table `ingredient_measurement_defaults`
--
ALTER TABLE `ingredient_measurement_defaults`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ingredient_id` (`ingredient_id`,`measurement_id`),
  ADD KEY `measurement_id` (`measurement_id`);

--
-- Indexes for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD PRIMARY KEY (`meal_plan_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `measurements`
--
ALTER TABLE `measurements`
  ADD PRIMARY KEY (`measurement_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `pantry`
--
ALTER TABLE `pantry`
  ADD PRIMARY KEY (`pantry_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ingredient_id` (`ingredient_id`),
  ADD KEY `measurement_id` (`measurement_id`);

--
-- Indexes for table `preference_category_mapping`
--
ALTER TABLE `preference_category_mapping`
  ADD PRIMARY KEY (`preference_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipe_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `recipe_categories`
--
ALTER TABLE `recipe_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `recipe_category_mapping`
--
ALTER TABLE `recipe_category_mapping`
  ADD PRIMARY KEY (`recipe_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `recipe_comments`
--
ALTER TABLE `recipe_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `ingredient_id` (`ingredient_id`),
  ADD KEY `measurement_id` (`measurement_id`);

--
-- Indexes for table `shopping_lists`
--
ALTER TABLE `shopping_lists`
  ADD PRIMARY KEY (`shopping_list_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shopping_list_items`
--
ALTER TABLE `shopping_list_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shopping_list_id` (`shopping_list_id`),
  ADD KEY `ingredient_id` (`ingredient_id`),
  ADD KEY `measurement_id` (`measurement_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`);

--
-- Indexes for table `user_allergies`
--
ALTER TABLE `user_allergies`
  ADD PRIMARY KEY (`user_id`,`allergy_id`),
  ADD KEY `allergy_id` (`allergy_id`);

--
-- Indexes for table `user_dietary_preferences`
--
ALTER TABLE `user_dietary_preferences`
  ADD PRIMARY KEY (`user_id`,`preference_id`),
  ADD KEY `preference_id` (`preference_id`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ads`
--
ALTER TABLE `ads`
  MODIFY `ad_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `ad_clicks`
--
ALTER TABLE `ad_clicks`
  MODIFY `click_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `ad_impressions`
--
ALTER TABLE `ad_impressions`
  MODIFY `impression_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=342;

--
-- AUTO_INCREMENT for table `allergies`
--
ALTER TABLE `allergies`
  MODIFY `allergy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `dietary_preferences`
--
ALTER TABLE `dietary_preferences`
  MODIFY `preference_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `ingredient_measurement_defaults`
--
ALTER TABLE `ingredient_measurement_defaults`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `meal_plans`
--
ALTER TABLE `meal_plans`
  MODIFY `meal_plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `measurements`
--
ALTER TABLE `measurements`
  MODIFY `measurement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `pantry`
--
ALTER TABLE `pantry`
  MODIFY `pantry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `recipe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `recipe_categories`
--
ALTER TABLE `recipe_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `recipe_comments`
--
ALTER TABLE `recipe_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT for table `shopping_lists`
--
ALTER TABLE `shopping_lists`
  MODIFY `shopping_list_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `shopping_list_items`
--
ALTER TABLE `shopping_list_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ad_clicks`
--
ALTER TABLE `ad_clicks`
  ADD CONSTRAINT `ad_clicks_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`ad_id`) ON DELETE CASCADE;

--
-- Constraints for table `ad_impressions`
--
ALTER TABLE `ad_impressions`
  ADD CONSTRAINT `ad_impressions_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`ad_id`) ON DELETE CASCADE;

--
-- Constraints for table `allergy_ingredient_mapping`
--
ALTER TABLE `allergy_ingredient_mapping`
  ADD CONSTRAINT `allergy_ingredient_mapping_ibfk_1` FOREIGN KEY (`allergy_id`) REFERENCES `allergies` (`allergy_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `allergy_ingredient_mapping_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ingredient_measurement_defaults`
--
ALTER TABLE `ingredient_measurement_defaults`
  ADD CONSTRAINT `ingredient_measurement_defaults_ibfk_1` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`),
  ADD CONSTRAINT `ingredient_measurement_defaults_ibfk_2` FOREIGN KEY (`measurement_id`) REFERENCES `measurements` (`measurement_id`);

--
-- Constraints for table `meal_plans`
--
ALTER TABLE `meal_plans`
  ADD CONSTRAINT `meal_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_plans_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `pantry`
--
ALTER TABLE `pantry`
  ADD CONSTRAINT `pantry_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `pantry_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`),
  ADD CONSTRAINT `pantry_ibfk_3` FOREIGN KEY (`measurement_id`) REFERENCES `measurements` (`measurement_id`);

--
-- Constraints for table `preference_category_mapping`
--
ALTER TABLE `preference_category_mapping`
  ADD CONSTRAINT `preference_category_mapping_ibfk_1` FOREIGN KEY (`preference_id`) REFERENCES `dietary_preferences` (`preference_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `preference_category_mapping_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `recipe_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe_category_mapping`
--
ALTER TABLE `recipe_category_mapping`
  ADD CONSTRAINT `recipe_category_mapping_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_category_mapping_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `recipe_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe_comments`
--
ALTER TABLE `recipe_comments`
  ADD CONSTRAINT `recipe_comments_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe_ingredients`
--
ALTER TABLE `recipe_ingredients`
  ADD CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredients_ibfk_3` FOREIGN KEY (`measurement_id`) REFERENCES `measurements` (`measurement_id`) ON DELETE CASCADE;

--
-- Constraints for table `shopping_lists`
--
ALTER TABLE `shopping_lists`
  ADD CONSTRAINT `shopping_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `shopping_list_items`
--
ALTER TABLE `shopping_list_items`
  ADD CONSTRAINT `shopping_list_items_ibfk_1` FOREIGN KEY (`shopping_list_id`) REFERENCES `shopping_lists` (`shopping_list_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shopping_list_items_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shopping_list_items_ibfk_3` FOREIGN KEY (`measurement_id`) REFERENCES `measurements` (`measurement_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_allergies`
--
ALTER TABLE `user_allergies`
  ADD CONSTRAINT `user_allergies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_allergies_ibfk_2` FOREIGN KEY (`allergy_id`) REFERENCES `allergies` (`allergy_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_dietary_preferences`
--
ALTER TABLE `user_dietary_preferences`
  ADD CONSTRAINT `user_dietary_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_dietary_preferences_ibfk_2` FOREIGN KEY (`preference_id`) REFERENCES `dietary_preferences` (`preference_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
