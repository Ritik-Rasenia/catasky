-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2026 at 11:15 AM
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
-- Database: `siddharsh`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `image`, `description`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Panduit', 'panduit', '1778066518.jpg', NULL, 1, '2026-05-06 05:51:58', '2026-05-06 05:51:58', NULL),
(2, 'R&M', 'rm', '1778132430.png', NULL, 1, '2026-05-07 00:10:30', '2026-05-07 00:10:30', NULL),
(3, 'Legrand', 'legrand', '1778132493.png', NULL, 1, '2026-05-07 00:11:33', '2026-05-07 00:11:33', NULL),
(4, 'VALRACK', 'valrack', '1778132539.png', NULL, 1, '2026-05-07 00:12:19', '2026-05-07 00:12:19', NULL),
(5, 'NETRACK', 'netrack', '1778132574.png', NULL, 1, '2026-05-07 00:12:54', '2026-05-07 00:12:54', NULL),
(6, 'CABLOFIL', 'cablofil', '1778132605.png', NULL, 1, '2026-05-07 00:13:25', '2026-05-07 00:13:25', NULL),
(7, 'NUMERIC', 'numeric', '1778132634.png', NULL, 1, '2026-05-07 00:13:54', '2026-05-07 00:13:54', NULL),
(8, 'HIKVISION', 'hikvision', '1778132665.png', NULL, 1, '2026-05-07 00:14:25', '2026-05-07 00:14:25', NULL),
(9, 'Godrej', 'godrej', '1778132705.png', NULL, 1, '2026-05-07 00:15:05', '2026-05-07 00:15:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `image`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Copper Systems', 'copper-systems', '1778066598.jpg', 1, '2026-05-06 05:53:18', '2026-05-06 05:53:18', NULL),
(2, 'Cabinets, Thermal Management, Racks and Enclosures', 'cabinets-thermal-management-racks-and-enclosures', '1778070165.jpg', 1, '2026-05-06 06:52:45', '2026-05-06 06:52:45', NULL),
(3, 'Fiber Optic Systems', 'fiber-optic-systems', '1778070235.jpg', 1, '2026-05-06 06:53:55', '2026-05-06 06:53:55', NULL),
(4, 'Grounding & Bonding', 'grounding-bonding', '1778070285.jpg', 1, '2026-05-06 06:54:45', '2026-05-06 06:54:45', NULL),
(5, 'Power, Environmental, Security & Connectivity Hardware', 'power-environmental-security-connectivity-hardware', '1778236627.jpg', 1, '2026-05-08 05:07:07', '2026-05-08 05:07:07', NULL),
(6, 'Safety & Security', 'safety-security', '1778236687.jpg', 1, '2026-05-08 05:08:07', '2026-05-08 05:08:07', NULL),
(7, 'Identification, Labels & Signs', 'identification-labels-signs', '1778236748.png', 1, '2026-05-08 05:09:08', '2026-05-08 05:09:08', NULL),
(8, 'Wire Management, Protection & Routing', 'wire-management-protection-routing', '1778236825.jpg', 1, '2026-05-08 05:10:25', '2026-05-08 05:10:25', NULL),
(9, 'Wire Termination', 'wire-termination', '1778236874.jpg', 1, '2026-05-08 05:11:14', '2026-05-08 05:11:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `child_categories`
--

CREATE TABLE `child_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `subcategory_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `child_categories`
--

INSERT INTO `child_categories` (`id`, `category_id`, `subcategory_id`, `name`, `slug`, `image`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Enterprise/Data Center Copper Cable', 'enterprisedata-center-copper-cable', '1778066712.jpg', 1, '2026-05-06 05:55:12', '2026-05-06 05:55:12', NULL),
(2, 2, 3, 'Cabinet Accessories', 'cabinet-accessories', '1778070690.jpg', 1, '2026-05-06 07:01:30', '2026-05-06 07:01:30', NULL),
(3, 2, 3, 'Micro Data Centers', 'micro-data-centers', '1778070759.jpg', 1, '2026-05-06 07:02:39', '2026-05-06 07:02:39', NULL),
(4, 2, 4, 'Horizontal Cable Managers', 'horizontal-cable-managers', '1778070842.jpg', 1, '2026-05-06 07:04:02', '2026-05-06 07:04:02', NULL),
(5, 2, 3, 'Cabinets', 'cabinets', NULL, 1, '2026-05-09 01:58:32', '2026-05-09 01:58:32', NULL),
(6, 2, 4, 'Cable Manager Accessories', 'cable-manager-accessories', NULL, 1, '2026-05-09 01:59:44', '2026-05-09 01:59:44', NULL),
(7, 2, 4, 'Vertical Cable Managers', 'vertical-cable-managers', NULL, 1, '2026-05-09 02:00:09', '2026-05-09 02:00:09', NULL),
(8, 2, 5, 'Industrial/Harsh Environment Enclosures', 'industrialharsh-environment-enclosures', NULL, 1, '2026-05-09 02:01:17', '2026-05-09 02:01:17', NULL),
(9, 2, 5, 'Zone Enclosure Accessories', 'zone-enclosure-accessories', NULL, 1, '2026-05-09 02:01:35', '2026-05-09 02:01:35', NULL),
(10, 2, 5, 'Zone Enclosures', 'zone-enclosures', NULL, 1, '2026-05-09 02:01:59', '2026-05-09 02:01:59', NULL),
(11, 2, 5, 'Industrial Enclosure Accessories', 'industrial-enclosure-accessories', NULL, 1, '2026-05-09 02:03:48', '2026-05-09 02:03:48', NULL),
(12, 2, 6, 'Rack Accessories', 'rack-accessories', NULL, 1, '2026-05-09 02:04:31', '2026-05-09 02:04:31', NULL),
(13, 2, 6, 'Racks', 'racks', NULL, 1, '2026-05-09 02:04:54', '2026-05-09 02:04:54', NULL),
(14, 2, 7, 'Aisle Containment', 'aisle-containment', NULL, 1, '2026-05-09 02:05:42', '2026-05-09 02:05:42', NULL),
(15, 2, 7, 'Air Inlet & Exhaust Duct', 'air-inlet-exhaust-duct', NULL, 1, '2026-05-09 02:06:01', '2026-05-09 02:06:01', NULL),
(16, 2, 7, 'Air Sealing Accessories', 'air-sealing-accessories', NULL, 1, '2026-05-09 02:06:21', '2026-05-09 02:06:21', NULL),
(17, 2, 7, 'Vertical Exhaust Duct', 'vertical-exhaust-duct', NULL, 1, '2026-05-09 02:06:39', '2026-05-09 02:06:39', NULL),
(18, 2, 7, 'Rack Containment', 'rack-containment', NULL, 1, '2026-05-09 02:06:57', '2026-05-09 02:06:57', NULL),
(19, 1, 8, 'Jack Modules', 'jack-modules', NULL, 1, '2026-05-09 03:03:05', '2026-05-09 03:03:05', NULL),
(20, 1, 8, 'Modular Plugs', 'modular-plugs', NULL, 1, '2026-05-09 03:03:20', '2026-05-09 03:03:20', NULL),
(21, 1, 8, 'Connector Tools & Accessories', 'connector-tools-accessories', NULL, 1, '2026-05-09 03:03:42', '2026-05-09 03:03:42', NULL),
(22, 1, 8, 'Power over Ethernet (PoE)', 'power-over-ethernet-poe', NULL, 1, '2026-05-09 03:04:10', '2026-05-09 03:04:10', NULL),
(23, 1, 9, 'Cross Connect Rack Mount Kits', 'cross-connect-rack-mount-kits', NULL, 1, '2026-05-09 03:05:29', '2026-05-09 03:05:29', NULL),
(24, 1, 9, 'Cross Connect Tools and Accessories', 'cross-connect-tools-and-accessories', NULL, 1, '2026-05-09 03:05:47', '2026-05-09 03:05:47', NULL),
(25, 1, 9, 'Punchdown Base Kits', 'punchdown-base-kits', NULL, 1, '2026-05-09 03:06:04', '2026-05-09 03:06:04', NULL),
(26, 1, 9, 'Cross Connect Bases', 'cross-connect-bases', NULL, 1, '2026-05-09 03:06:23', '2026-05-09 03:06:23', NULL),
(27, 1, 9, 'Cross Connect Patch Connectors', 'cross-connect-patch-connectors', NULL, 1, '2026-05-09 03:06:38', '2026-05-09 03:06:38', NULL),
(28, 1, 9, 'Connecting Blocks', 'connecting-blocks', NULL, 1, '2026-05-09 03:06:58', '2026-05-09 03:06:58', NULL),
(29, 1, 9, 'Cross-Connect Patch Cords', 'cross-connect-patch-cords', NULL, 1, '2026-05-09 03:07:26', '2026-05-09 03:07:26', NULL),
(30, 1, 11, 'Faceplates & Adapters', 'faceplates-adapters', NULL, 1, '2026-05-09 03:08:05', '2026-05-09 03:08:05', NULL),
(31, 1, 11, 'Faceplate Accessories', 'faceplate-accessories', NULL, 1, '2026-05-09 03:08:21', '2026-05-09 03:08:21', NULL),
(32, 1, 11, 'Surface Mount Boxes', 'surface-mount-boxes', NULL, 1, '2026-05-09 03:08:38', '2026-05-09 03:08:38', NULL),
(33, 1, 11, 'Faceplate Frame System', 'faceplate-frame-system', NULL, 1, '2026-05-09 03:08:56', '2026-05-09 03:08:56', NULL),
(34, 1, 12, 'Faceplate Adapters & Inserts', 'faceplate-adapters-inserts', NULL, 1, '2026-05-09 03:09:28', '2026-05-09 03:09:28', NULL),
(35, 1, 13, 'Patch Cords', 'patch-cords', NULL, 1, '2026-05-09 03:09:58', '2026-05-09 03:09:58', NULL),
(36, 1, 13, 'Patch Cord Accessories', 'patch-cord-accessories', NULL, 1, '2026-05-09 03:10:13', '2026-05-09 03:10:13', NULL),
(37, 1, 14, 'Populated Patch Panels', 'populated-patch-panels', NULL, 1, '2026-05-09 03:10:52', '2026-05-09 03:10:52', NULL),
(38, 1, 14, 'Patch Panel Labels', 'patch-panel-labels', NULL, 1, '2026-05-09 03:11:08', '2026-05-09 03:11:08', NULL),
(39, 1, 14, 'Patch Panel Accessories', 'patch-panel-accessories', NULL, 1, '2026-05-09 03:11:25', '2026-05-09 03:11:53', NULL),
(40, 1, 14, 'Modular Patch Panels', 'modular-patch-panels', NULL, 1, '2026-05-09 03:11:43', '2026-05-09 03:11:43', NULL),
(41, 1, 14, 'Patch Panel Kits', 'patch-panel-kits', NULL, 1, '2026-05-09 03:12:12', '2026-05-09 03:12:12', NULL),
(42, 1, 15, 'Block-Out Devices', 'block-out-devices', NULL, 1, '2026-05-09 03:12:50', '2026-05-09 03:12:50', NULL),
(43, 1, 15, 'Lock-In Devices', 'lock-in-devices', NULL, 1, '2026-05-09 03:13:24', '2026-05-09 03:13:24', NULL),
(44, 1, 16, 'Industrial Copper Cable', 'industrial-copper-cable', NULL, 1, '2026-05-09 03:13:59', '2026-05-09 03:13:59', NULL),
(45, 1, 16, 'Industrial Copper Faceplates, Outlets & Panels', 'industrial-copper-faceplates-outlets-panels', NULL, 1, '2026-05-09 03:14:20', '2026-05-09 03:14:20', NULL),
(46, 1, 16, 'Industrial Copper Patch Cords', 'industrial-copper-patch-cords', NULL, 1, '2026-05-09 03:14:36', '2026-05-09 03:14:36', NULL),
(47, 1, 16, 'Industrial Copper Connectors', 'industrial-copper-connectors', NULL, 1, '2026-05-09 03:14:53', '2026-05-09 03:14:53', NULL),
(48, 3, 17, 'Indoor Fiber Optic Cable', 'indoor-fiber-optic-cable', NULL, 1, '2026-05-09 03:15:43', '2026-05-09 03:15:43', NULL),
(49, 3, 17, 'Indoor/Outdoor Fiber Optic Cable', 'indooroutdoor-fiber-optic-cable', NULL, 1, '2026-05-09 03:16:00', '2026-05-09 03:16:00', NULL),
(50, 3, 17, 'Outside Plant Fiber Optic Cable', 'outside-plant-fiber-optic-cable', NULL, 1, '2026-05-09 03:16:14', '2026-05-09 03:16:14', NULL),
(51, 3, 17, 'Hybrid Powered Fiber Optic Cable', 'hybrid-powered-fiber-optic-cable', NULL, 1, '2026-05-09 03:16:36', '2026-05-09 03:16:36', NULL),
(52, 3, 18, 'Fiber Optic Adapters', 'fiber-optic-adapters', NULL, 1, '2026-05-09 03:17:09', '2026-05-09 03:17:09', NULL),
(53, 3, 18, 'Fiber Optic Connectors', 'fiber-optic-connectors', NULL, 1, '2026-05-09 03:17:26', '2026-05-09 03:17:26', NULL),
(54, 3, 19, 'Fiber Optic Trunk Cable Assemblies', 'fiber-optic-trunk-cable-assemblies', NULL, 1, '2026-05-09 03:18:02', '2026-05-09 03:18:02', NULL),
(55, 3, 19, 'Fiber Optic Interconnects, Patch Cords & Pigtails', 'fiber-optic-interconnects-patch-cords-pigtails', NULL, 1, '2026-05-09 03:18:16', '2026-05-09 03:18:16', NULL),
(56, 3, 19, 'Fiber Optic Breakout Harnesses', 'fiber-optic-breakout-harnesses', NULL, 1, '2026-05-09 03:18:32', '2026-05-09 03:18:32', NULL),
(57, 3, 20, 'Fiber Optic Cassettes', 'fiber-optic-cassettes', NULL, 1, '2026-05-09 03:19:04', '2026-05-09 03:19:04', NULL),
(58, 3, 20, 'Fiber Optic Enclosure Accessories', 'fiber-optic-enclosure-accessories', NULL, 1, '2026-05-09 03:19:22', '2026-05-09 03:19:22', NULL),
(59, 3, 20, 'Fiber Optic Enclosures', 'fiber-optic-enclosures', NULL, 1, '2026-05-09 03:19:40', '2026-05-09 03:19:40', NULL),
(60, 3, 20, 'Fiber Optic Panels', 'fiber-optic-panels', NULL, 1, '2026-05-09 03:19:59', '2026-05-09 03:19:59', NULL),
(61, 3, 21, 'Fiber Optic Accessories', 'fiber-optic-accessories', NULL, 1, '2026-05-09 03:20:34', '2026-05-09 03:20:34', NULL),
(62, 3, 21, 'Fiber Optic Cleaning Supplies', 'fiber-optic-cleaning-supplies', NULL, 1, '2026-05-09 03:20:56', '2026-05-09 03:20:56', NULL),
(63, 3, 21, 'Fiber Optic Termination Kits', 'fiber-optic-termination-kits', NULL, 1, '2026-05-09 03:21:17', '2026-05-09 03:21:17', NULL),
(64, 3, 21, 'Fiber Optic Tools', 'fiber-optic-tools', NULL, 1, '2026-05-09 03:21:34', '2026-05-09 03:21:34', NULL),
(65, 3, 23, 'Industrial Fiber Optic Faceplates, Outlets & Adapters', 'industrial-fiber-optic-faceplates-outlets-adapters', NULL, 1, '2026-05-09 03:21:58', '2026-05-09 03:21:58', NULL),
(66, 3, 24, 'Fiber Drop Cables  Multiport Service Terminals', 'fiber-drop-cables-multiport-service-terminals', NULL, 1, '2026-05-09 03:22:40', '2026-05-09 03:22:40', NULL),
(67, 3, 24, 'Multiport Service Terminals', 'multiport-service-terminals', NULL, 1, '2026-05-09 03:22:59', '2026-05-09 03:22:59', NULL),
(68, 4, 25, 'Equipment Bonding Kits', 'equipment-bonding-kits', NULL, 1, '2026-05-09 03:26:03', '2026-05-09 03:26:03', NULL),
(69, 4, 25, 'Flat Braided Bonding & Wrist Straps', 'flat-braided-bonding-wrist-straps', NULL, 1, '2026-05-09 03:40:47', '2026-05-09 03:40:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`id`, `product_id`, `name`, `email`, `phone`, `message`, `is_read`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Keaton Bruce', 'vijayraaz2621@gmail.com', '9797897969', 'I am interested in the Pan-Net® TX6000™ Category 6 U/UTP cable for an upcoming data center refresh. Could you please provide a formal quote for 15 rolls (305m each) in Blue? Additionally, I\'d like to confirm the lead time for delivery to our New Delhi office and if you provide the matching Panduit TX6™ PLUS Jack Modules.', 1, '2026-05-06 06:00:40', '2026-05-06 06:01:42', NULL),
(2, 1, 'Tad Oneal', 'ritikrasenia1234@gmail.com', '9789693456', 'I am interested in the Pan-Net® TX6000™ Category 6 U/UTP cable for an upcoming data center refresh. Could you please provide a formal quote for 15 rolls (305m each) in Blue? Additionally, I\'d like to confirm the lead time for delivery.', 1, '2026-05-06 06:04:14', '2026-05-06 07:15:37', NULL),
(3, 1, 'Kartik', 'kartik@mirashka.com', '9783953457', 'I am interested in the Pan-Net TX6000 Category 6 U/UTP Enhanced Copper Cable. Could you please provide a quote for [Quantity, e.g., 5 rolls] and let me know the expected delivery timeline for Noida?', 1, '2026-05-07 00:43:52', '2026-05-07 00:44:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_05_075711_create_brands_table', 1),
(5, '2026_05_05_085340_create_categories_table', 2),
(6, '2026_05_05_085342_create_subcategories_table', 2),
(7, '2026_05_05_085344_create_products_table', 2),
(8, '2026_05_05_085345_create_product_images_table', 2),
(9, '2026_05_05_085347_add_profile_image_to_users_table', 2),
(10, '2026_05_05_090000_create_permission_tables', 3),
(11, '2026_05_05_114700_create_settings_table', 4),
(12, '2026_05_05_114705_add_soft_deletes_to_crud_tables', 4),
(13, '2026_05_05_114753_add_additional_fields_to_products_table', 5),
(14, '2026_05_06_052748_create_child_categories_table', 6),
(15, '2026_05_06_052749_add_child_category_id_to_products_table', 7),
(16, '2026_05_06_100302_create_enquiries_table', 8),
(17, '2026_05_06_101937_add_status_to_enquiries_table', 9),
(18, '2026_05_06_102017_add_extra_fields_to_settings_table', 9),
(19, '2026_05_07_075936_add_seo_fields_to_products_table', 10),
(20, '2026_05_07_092246_add_part_code_to_products_table', 11),
(21, '2026_05_09_053837_create_product_import_logs_table', 12);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'role-list', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(2, 'role-create', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(3, 'role-edit', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(4, 'role-delete', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(5, 'user-list', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(6, 'user-create', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(7, 'user-edit', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(8, 'user-delete', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(9, 'brand-list', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(10, 'brand-create', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(11, 'brand-edit', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(12, 'brand-delete', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(13, 'category-list', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(14, 'category-create', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(15, 'category-edit', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(16, 'category-delete', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(17, 'subcategory-list', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(18, 'subcategory-create', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(19, 'subcategory-edit', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(20, 'subcategory-delete', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(21, 'product-list', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(22, 'product-create', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(23, 'product-edit', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(24, 'product-delete', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brand_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `subcategory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `child_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `part_code` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `full_description` longtext DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `packaging` text DEFAULT NULL,
  `additional_info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `brand_id`, `category_id`, `subcategory_id`, `child_category_id`, `name`, `slug`, `part_code`, `thumbnail`, `short_description`, `full_description`, `specifications`, `featured`, `status`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`, `deleted_at`, `tags`, `packaging`, `additional_info`) VALUES
(1, 1, 1, 1, 1, 'Pan-Net® TX6000™ Category 6 U/UTP Enhanced Copper Cable', 'pan-net-tx6000-category-6-uutp-enhanced-copper-cable', 'CCL3', '1778145323_download (5).png', 'Enhanced electrical headroom performance provides our enhanced Cat 6 U/UTP copper cable margin above industry standards and exceptional performance for PoE++ applications.', NULL, '<h3><strong>Specifications</strong></h3><figure class=\"table\"><table><tbody><tr><td>Environment</td><td>Indoor</td></tr><tr><td>Performance Level</td><td>Category 6</td></tr><tr><td>Cable Construction</td><td>U/UTP</td></tr><tr><td>Flammability Rating</td><td>Plenum (CMP)</td></tr><tr><td>Copper Cable Flame Rating</td><td>Plenum (CMP)</td></tr><tr><td>LP Rating</td><td>LP (0.5A)</td></tr><tr><td>Jacket Material</td><td>Fluorinated Ethylene Propylene</td></tr><tr><td>Nominal Cable Outside Diameter (In.)</td><td>0.225</td></tr><tr><td>Nominal Cable Outside Diameter (mm)</td><td>5.7</td></tr><tr><td>Sub Brand</td><td>Pan-Net®</td></tr><tr><td>Number of Pairs</td><td>4</td></tr><tr><td>Conductor Material</td><td>Copper</td></tr><tr><td>Conductor Type</td><td>Solid</td></tr><tr><td>Overall Length (ft.)</td><td>1000</td></tr><tr><td>Overall Length (m)</td><td>305</td></tr><tr><td>Conductor Gauge</td><td>23 AWG</td></tr><tr><td>Conductor Gauge (mm)</td><td>0.57</td></tr><tr><td>Insulation Material</td><td>Polyolefin</td></tr><tr><td>Color</td><td>Blue</td></tr><tr><td>Packaging Type</td><td>Carton</td></tr><tr><td>Product Type</td><td>Bulk Copper Cable</td></tr><tr><td>Part Features</td><td>Copper Cable</td></tr></tbody></table></figure>', 1, 1, NULL, NULL, NULL, '2026-05-06 05:58:06', '2026-05-07 03:58:02', NULL, 'Copper Cable, Cat 6, 23 AWG, UTP, CMP, Blue', '<h3><strong>Packaging Detail</strong></h3><figure class=\"table\"><table><tbody><tr><td>UPC</td><td>61305686003</td></tr><tr><td>Std. Pkg. Qty.</td><td>1000</td></tr><tr><td>Std. Pkg. Volume (cf)</td><td>1.7315</td></tr><tr><td>Std. Ctn. Qty.</td><td>36000</td></tr><tr><td>Std. Ctn. Volume (cf)</td><td>62.3333</td></tr></tbody></table></figure>', '<p>NOTE: Not all resources are available in your language.</p><p>If product info is not displayed in your selected language, it will be coming. We will continue to improve the experience over time.</p>');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image`, `created_at`, `updated_at`) VALUES
(5, 1, '1778245292_69fddeac3f8dc_download (26).jpg', '2026-05-08 07:31:32', '2026-05-08 07:31:32'),
(6, 1, '1778245292_69fddeac46bb6_download (25).jpg', '2026-05-08 07:31:32', '2026-05-08 07:31:32'),
(7, 1, '1778245292_69fddeac4899e_download (24).jpg', '2026-05-08 07:31:32', '2026-05-08 07:31:32'),
(8, 1, '1778245292_69fddeac4ac7b_download (23).jpg', '2026-05-08 07:31:32', '2026-05-08 07:31:32');

-- --------------------------------------------------------

--
-- Table structure for table `product_import_logs`
--

CREATE TABLE `product_import_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `total_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `imported_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `skipped_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `errors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`errors`)),
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_import_logs`
--

INSERT INTO `product_import_logs` (`id`, `filename`, `total_rows`, `imported_rows`, `skipped_rows`, `status`, `errors`, `started_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 'product-import-template.xlsx', 1, 1, 0, 'completed', '[]', '2026-05-09 00:57:55', '2026-05-09 00:57:56', '2026-05-09 00:57:53', '2026-05-09 00:57:56'),
(2, 'product-import-template.xlsx', 1, 1, 0, 'completed', '[]', '2026-05-09 01:06:17', '2026-05-09 01:06:17', '2026-05-09 01:06:15', '2026-05-09 01:06:17'),
(3, 'product-import-template.xlsx', 1, 1, 0, 'completed', '[]', '2026-05-09 01:10:19', '2026-05-09 01:10:19', '2026-05-09 01:10:16', '2026-05-09 01:10:19');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'web', '2026-05-05 06:02:52', '2026-05-05 06:02:52'),
(2, 'Staff', 'web', '2026-05-05 06:32:30', '2026-05-05 06:32:30');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(5, 2),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(9, 2),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(13, 2),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(17, 2),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(21, 2),
(22, 1),
(23, 1),
(24, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('a22lUpAAVC0pfu9xvinDqyxhkNJEO4p3XCKFhALS', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiN1pGaVA4STZlUjliVFExT2lVbzh0Q1NNUnNkSHJmMVJ5eU9idUZKRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9jaGlsZGNhdGVnb3JpZXMiO3M6NToicm91dGUiO3M6Mjc6ImFkbWluLmNoaWxkY2F0ZWdvcmllcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1778317848);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `site_title` varchar(255) DEFAULT NULL,
  `site_description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `admin_email` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `site_title`, `site_description`, `logo`, `favicon`, `created_at`, `updated_at`, `email`, `phone`, `address`, `admin_email`, `facebook`, `twitter`, `instagram`, `linkedin`, `youtube`) VALUES
(1, 'Siddharsh Technologies', 'Leading Enterprise IT Infrastructure & Networking Solutions Provider. Expert in Cisco, HP, Dell and more.', '1778064492_logo.png', '1778064492_favicon.png', '2026-05-06 05:12:10', '2026-05-07 00:41:57', 'info@siddharsh.com', '+91-8826363495', 'Plot No. 45, Second Floor, Okhla Industrial Estate Phase III, New Delhi, Delhi-110020, India', 'kartik@mirashka.com', 'https://facebook.com/siddharshtech', 'https://twitter.com/siddharshtech', 'https://instagram.com/siddharshtech', 'https://linkedin.com/company/siddharshtech', 'https://youtube.com/c/siddharshtech');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `category_id`, `name`, `slug`, `image`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Bulk Copper Cable', 'bulk-copper-cable', '1778066649.jpg', 1, '2026-05-06 05:54:09', '2026-05-06 05:54:09', NULL),
(2, 1, 'Cable Trunks & Switch Harnesses', 'cable-trunks-switch-harnesses', '1778070405.jpg', 1, '2026-05-06 06:56:45', '2026-05-06 06:56:45', NULL),
(3, 2, 'Cabinets & Accessories', 'cabinets-accessories', '1778070516.jpg', 1, '2026-05-06 06:58:36', '2026-05-06 06:58:36', NULL),
(4, 2, 'Cable Managers & Accessories', 'cable-managers-accessories', '1778070576.jpg', 1, '2026-05-06 06:59:36', '2026-05-06 06:59:36', NULL),
(5, 2, 'Enclosures & Accessories', 'enclosures-accessories', '1778236972.jpg', 1, '2026-05-08 05:12:52', '2026-05-08 05:12:52', NULL),
(6, 2, 'Racks & Accessories', 'racks-accessories', '1778237079.jpg', 1, '2026-05-08 05:14:39', '2026-05-08 05:14:39', NULL),
(7, 2, 'Thermal Management & Containment', 'thermal-management-containment', '1778237141.jpg', 1, '2026-05-08 05:15:41', '2026-05-08 05:15:41', NULL),
(8, 1, 'Connectors', 'connectors', '1778309568.jpg', 1, '2026-05-09 01:22:48', '2026-05-09 01:22:48', NULL),
(9, 1, 'Cross-Connect System', 'cross-connect-system', '1778309608.jpg', 1, '2026-05-09 01:23:28', '2026-05-09 01:23:28', NULL),
(10, 1, 'Direct Attach Cable Assemblies', 'direct-attach-cable-assemblies', '1778309672.jpg', 1, '2026-05-09 01:24:32', '2026-05-09 01:24:32', NULL),
(11, 1, 'Faceplates & Boxes', 'faceplates-boxes', '1778309730.jpg', 1, '2026-05-09 01:25:30', '2026-05-09 01:25:30', NULL),
(12, 1, 'Faceplate Systems', 'faceplate-systems', '1778309804.jpg', 1, '2026-05-09 01:26:44', '2026-05-09 01:26:44', NULL),
(13, 1, 'Patch Cords & Accessories', 'patch-cords-accessories', '1778309861.jpg', 1, '2026-05-09 01:27:41', '2026-05-09 01:27:41', NULL),
(14, 1, 'Patch Panels & Accessories', 'patch-panels-accessories', '1778309912.jpg', 1, '2026-05-09 01:28:32', '2026-05-09 01:28:32', NULL),
(15, 1, 'Physical Security Devices', 'physical-security-devices', '1778309961.jpg', 1, '2026-05-09 01:29:21', '2026-05-09 01:29:21', NULL),
(16, 1, 'Industrial Copper Systems', 'industrial-copper-systems', '1778310007.jpg', 1, '2026-05-09 01:30:07', '2026-05-09 01:30:07', NULL),
(17, 3, 'Bulk Fiber Optic Cable', 'bulk-fiber-optic-cable', NULL, 1, '2026-05-09 01:34:28', '2026-05-09 01:34:28', NULL),
(18, 3, 'Fiber Optic Adapters & Connectors', 'fiber-optic-adapters-connectors', NULL, 1, '2026-05-09 01:34:54', '2026-05-09 01:34:54', NULL),
(19, 3, 'Fiber Optic Cable Assemblies', 'fiber-optic-cable-assemblies', NULL, 1, '2026-05-09 01:35:12', '2026-05-09 01:35:12', NULL),
(20, 3, 'Fiber Optic Panels, Cassettes, & Enclosures', 'fiber-optic-panels-cassettes-enclosures', NULL, 1, '2026-05-09 01:35:27', '2026-05-09 01:35:27', NULL),
(21, 3, 'Fiber Optic Tools & Accessories', 'fiber-optic-tools-accessories', NULL, 1, '2026-05-09 01:35:46', '2026-05-09 01:35:46', NULL),
(22, 3, 'Fiber Outdoor Enclosures', 'fiber-outdoor-enclosures', NULL, 1, '2026-05-09 01:36:07', '2026-05-09 01:36:07', NULL),
(23, 3, 'Industrial Fiber Optic Systems', 'industrial-fiber-optic-systems', NULL, 1, '2026-05-09 01:36:24', '2026-05-09 01:36:24', NULL),
(24, 3, 'Outdoor Fiber Assemblies', 'outdoor-fiber-assemblies', NULL, 1, '2026-05-09 01:36:39', '2026-05-09 01:36:39', NULL),
(25, 4, 'Bonding Kits & Straps', 'bonding-kits-straps', NULL, 1, '2026-05-09 01:38:51', '2026-05-09 01:38:51', NULL),
(26, 4, 'Compression Grounding Connectors', 'compression-grounding-connectors', NULL, 1, '2026-05-09 01:39:05', '2026-05-09 01:39:05', NULL),
(27, 4, 'Grounding & Bonding Hardware', 'grounding-bonding-hardware', NULL, 1, '2026-05-09 01:39:21', '2026-05-09 01:39:21', NULL),
(28, 4, 'Grounding Busbars & Strips', 'grounding-busbars-strips', NULL, 1, '2026-05-09 01:39:38', '2026-05-09 01:39:38', NULL),
(29, 4, 'Grounding Clamps & Mechanical Connectors', 'grounding-clamps-mechanical-connectors', NULL, 1, '2026-05-09 01:40:01', '2026-05-09 01:40:01', NULL),
(30, 5, 'Fault Managed Power Systems (FMPS)', 'fault-managed-power-systems-fmps', NULL, 1, '2026-05-09 01:40:51', '2026-05-09 01:40:51', NULL),
(31, 5, 'Industrial Uninterruptible Power Supplies (IUPS)', 'industrial-uninterruptible-power-supplies-iups', NULL, 1, '2026-05-09 01:41:11', '2026-05-09 01:41:11', NULL),
(32, 5, 'Intelligent Connectivity & Accessories', 'intelligent-connectivity-accessories', NULL, 1, '2026-05-09 01:41:28', '2026-05-09 01:41:28', NULL),
(33, 5, 'PDU Accessories', 'pdu-accessories', NULL, 1, '2026-05-09 01:41:43', '2026-05-09 01:41:43', NULL),
(34, 5, 'PDU Power Cords', 'pdu-power-cords', NULL, 1, '2026-05-09 01:41:57', '2026-05-09 01:41:57', NULL),
(35, 5, 'Power Distribution Units (PDU\'s)', 'power-distribution-units-pdus', NULL, 1, '2026-05-09 01:42:17', '2026-05-09 01:42:17', NULL),
(36, 5, 'Uninterruptible Power Supplies (UPS)', 'uninterruptible-power-supplies-ups', NULL, 1, '2026-05-09 01:42:41', '2026-05-09 01:42:41', NULL),
(37, 5, 'UPS Accessories', 'ups-accessories', NULL, 1, '2026-05-09 01:43:04', '2026-05-09 01:43:04', NULL),
(38, 5, 'UPS Services', 'ups-services', NULL, 1, '2026-05-09 01:43:19', '2026-05-09 01:43:19', NULL),
(39, 6, 'Electrical Safety Devices', 'electrical-safety-devices', NULL, 1, '2026-05-09 01:44:02', '2026-05-09 01:44:02', NULL),
(40, 6, 'Lockout/Tagout (LOTO)', 'lockouttagout-loto', NULL, 1, '2026-05-09 01:44:22', '2026-05-09 01:44:22', NULL),
(41, 6, 'Padlocks', 'padlocks', NULL, 1, '2026-05-09 01:44:35', '2026-05-09 01:44:35', NULL),
(42, 7, 'Label Printers', 'label-printers', NULL, 1, '2026-05-09 01:45:21', '2026-05-09 01:45:21', NULL),
(43, 7, 'Labels', 'labels', NULL, 1, '2026-05-09 01:45:39', '2026-05-09 01:45:39', NULL),
(44, 7, 'Metal Plates, Signs, Tags & Tape', 'metal-plates-signs-tags-tape', NULL, 1, '2026-05-09 01:45:52', '2026-05-09 01:45:52', NULL),
(45, 8, 'Abrasion Protection & Electrical Tape', 'abrasion-protection-electrical-tape', NULL, 1, '2026-05-09 01:46:39', '2026-05-09 01:46:39', NULL),
(46, 8, 'Cable Cleats, Metal Ties, Strapping & Tools', 'cable-cleats-metal-ties-strapping-tools', NULL, 1, '2026-05-09 01:46:58', '2026-05-09 01:46:58', NULL),
(47, 8, 'Cable Clips, Clamps & Supports', 'cable-clips-clamps-supports', NULL, 1, '2026-05-09 01:51:54', '2026-05-09 01:51:54', NULL),
(48, 8, 'Cable Ties', 'cable-ties', NULL, 1, '2026-05-09 01:52:10', '2026-05-09 01:52:10', NULL),
(49, 8, 'Cable Tie Mounts, Tools & Accessories', 'cable-tie-mounts-tools-accessories', NULL, 1, '2026-05-09 01:52:32', '2026-05-09 01:52:32', NULL),
(50, 8, 'Fiber Optic Routing Systems', 'fiber-optic-routing-systems', NULL, 1, '2026-05-09 01:52:57', '2026-05-09 01:52:57', NULL),
(51, 8, 'Harness Boards', 'harness-boards', NULL, 1, '2026-05-09 01:53:13', '2026-05-09 01:53:13', NULL),
(52, 8, 'Surface Raceway', 'surface-raceway', NULL, 1, '2026-05-09 01:53:28', '2026-05-09 01:53:28', NULL),
(53, 8, 'Wire Basket Cable Tray', 'wire-basket-cable-tray', NULL, 1, '2026-05-09 01:53:43', '2026-05-09 01:53:43', NULL),
(54, 8, 'Wiring Duct', 'wiring-duct', NULL, 1, '2026-05-09 01:54:18', '2026-05-09 01:54:18', NULL),
(55, 9, 'Compression & Mechanical Lugs', 'compression-mechanical-lugs', NULL, 1, '2026-05-09 01:55:11', '2026-05-09 01:55:11', NULL),
(56, 9, 'Electrical Terminals', 'electrical-terminals', NULL, 1, '2026-05-09 01:55:39', '2026-05-09 01:55:39', NULL),
(57, 9, 'Wire Splices', 'wire-splices', NULL, 1, '2026-05-09 01:55:52', '2026-05-09 01:55:52', NULL),
(58, 9, 'Wire Termination Tools', 'wire-termination-tools', NULL, 1, '2026-05-09 01:56:08', '2026-05-09 01:56:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `profile_image`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 'Super Admin', 'admin@gmail.com', '1778071726.png', NULL, '$2y$10$MbYHu49X0EV5wDE2J.5bCebmygZbcdvYeETfr98SFV1vky15PIdE2', NULL, '2026-05-05 09:23:44', '2026-05-06 07:18:46'),
(3, 'Ritik Rasenia', 'ritikrasenia1234@gmail.com', NULL, NULL, '$2y$12$q7SS2n7uq82ZtKPvTyBP9O.BTwOv/lpikCZcUcH6pZl8yLJB4eW/q', NULL, '2026-05-05 06:32:53', '2026-05-05 06:32:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brands_slug_unique` (`slug`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `child_categories`
--
ALTER TABLE `child_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_categories_slug_unique` (`slug`),
  ADD KEY `child_categories_category_id_foreign` (`category_id`),
  ADD KEY `child_categories_subcategory_id_foreign` (`subcategory_id`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enquiries_product_id_foreign` (`product_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_brand_id_foreign` (`brand_id`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_subcategory_id_foreign` (`subcategory_id`),
  ADD KEY `products_child_category_id_foreign` (`child_category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_foreign` (`product_id`);

--
-- Indexes for table `product_import_logs`
--
ALTER TABLE `product_import_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subcategories_slug_unique` (`slug`),
  ADD KEY `subcategories_category_id_foreign` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `child_categories`
--
ALTER TABLE `child_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_import_logs`
--
ALTER TABLE `product_import_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `child_categories`
--
ALTER TABLE `child_categories`
  ADD CONSTRAINT `child_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `child_categories_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD CONSTRAINT `enquiries_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_child_category_id_foreign` FOREIGN KEY (`child_category_id`) REFERENCES `child_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
