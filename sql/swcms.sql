-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Creato il: Giu 19, 2025 alle 17:21
-- Versione del server: 8.0.42
-- Versione PHP: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `swcms`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text,
  `parent_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `parent_id`, `created_at`, `updated_at`) VALUES
(1, 'Uncategorized', 'uncategorized', 'Default category', NULL, '2025-04-16 14:37:50', NULL),
(3, '123345', '123345', '123', NULL, '2025-04-16 15:48:58', '2025-05-26 16:56:21'),
(5, 'vari', 'vari', '', NULL, '2025-05-27 09:24:51', NULL),
(6, 'web', 'web', '', NULL, '2025-05-30 15:01:35', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `post_id` int NOT NULL,
  `author_name` varchar(50) DEFAULT NULL,
  `author_email` varchar(100) DEFAULT NULL,
  `author_url` varchar(100) DEFAULT NULL,
  `author_ip` varchar(100) DEFAULT NULL,
  `content` text NOT NULL,
  `status` enum('approved','pending','spam','trash') NOT NULL DEFAULT 'pending',
  `parent_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `media`
--

CREATE TABLE `media` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filepath` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filetype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filesize` int NOT NULL,
  `width` int DEFAULT NULL,
  `height` int DEFAULT NULL,
  `metadata` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `media_relationships`
--

CREATE TABLE `media_relationships` (
  `id` int NOT NULL,
  `media_id` int NOT NULL,
  `related_id` int NOT NULL,
  `related_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `menu_blocks`
--

CREATE TABLE `menu_blocks` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `menu_blocks`
--

INSERT INTO `menu_blocks` (`id`, `name`, `key`, `position`, `active`) VALUES
(1, 'CONTENT MANAGEMENT', 'content_management', 1, 1),
(2, 'USER MANAGEMENT', 'user_management', 2, 1),
(3, 'SYSTEM', 'system', 3, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int NOT NULL,
  `block_id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `label` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `permission_key` varchar(100) DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  `plugin` varchar(100) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `menu_items`
--

INSERT INTO `menu_items` (`id`, `block_id`, `parent_id`, `label`, `url`, `icon`, `permission_key`, `position`, `plugin`, `active`) VALUES
(1, 1, NULL, 'Dashboard', '/admin', 'fas fa-tachometer-alt', NULL, 1, NULL, 1),
(2, 1, NULL, 'Articles', '/admin/articles', 'fas fa-newspaper', 'articles', 2, NULL, 1),
(3, 1, NULL, 'Pages', '/admin/pages', 'fas fa-file-alt', 'pages', 3, NULL, 1),
(4, 1, NULL, 'Categories', '/admin/categories', 'fas fa-folder', 'categories', 4, NULL, 1),
(5, 1, NULL, 'Tags', '/admin/tags', 'fas fa-tags', 'tags', 5, NULL, 1),
(6, 1, NULL, 'Comments', '/admin/comments', 'fas fa-comments', 'comments', 6, NULL, 1),
(7, 1, NULL, 'Media Library', '/admin/media', 'fas fa-images', 'media', 7, NULL, 1),
(8, 2, NULL, 'Users', '/admin/users', 'fas fa-users', 'users', 1, NULL, 1),
(9, 2, NULL, 'Roles & Permissions', '/admin/roles', 'fas fa-user-tag', 'roles', 2, NULL, 1),
(10, 3, NULL, 'Appearance', '/admin/appearance', 'fas fa-palette', 'appearance', 1, NULL, 1),
(11, 3, NULL, 'Plugins', '/admin/plugins', 'fas fa-puzzle-piece', 'plugins', 2, NULL, 1),
(12, 3, NULL, 'Settings', '/admin/settings', 'fas fa-cogs', 'settings', 3, NULL, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `migrations`
--

CREATE TABLE `migrations` (
  `id` int NOT NULL,
  `migration` varchar(255) NOT NULL,
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `applied_at`) VALUES
(1, '2025_05_23_000000_create_media_tables.php', '2025-05-24 15:50:48');

-- --------------------------------------------------------

--
-- Struttura della tabella `options`
--

CREATE TABLE `options` (
  `id` int NOT NULL,
  `option_name` varchar(191) NOT NULL,
  `option_value` longtext NOT NULL,
  `autoload` enum('yes','no') NOT NULL DEFAULT 'yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `options`
--

INSERT INTO `options` (`id`, `option_name`, `option_value`, `autoload`) VALUES
(1, 'site_title', 'swCMS', 'yes'),
(2, 'site_description', 'A modular CMS built with PHP and MVC architecture', 'yes'),
(3, 'site_url', 'http://localhost:8080', 'yes'),
(4, 'admin_email', 'admin@example.com', 'yes'),
(5, 'posts_per_page', '10', 'yes'),
(6, 'date_format', 'F j, Y', 'yes'),
(7, 'time_format', 'g:i a', 'yes'),
(8, 'theme', 'default', 'yes'),
(9, 'timezone', 'Europe/Rome', 'yes');

-- --------------------------------------------------------

--
-- Struttura della tabella `pages`
--

CREATE TABLE `pages` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text,
  `status` enum('published','draft','trash') NOT NULL DEFAULT 'draft',
  `author_id` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `template` varchar(50) DEFAULT 'default',
  `order` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `published_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `status`, `author_id`, `parent_id`, `template`, `order`, `created_at`, `updated_at`, `published_at`) VALUES
(1, 'testing', 'testing', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Rutrum commodo nulla dui torquent molestie interdum cum. Taciti nisl inceptos consectetur cras taciti turpis ultrices. Tellus turpis dictum ullamcorper duis vitae ipsum nostra. Justo penatibus eros justo mi nec sollicitudin in. Hendrerit et ad non nullam pulvinar diam pellentesque. Enim sollicitudin ultricies dignissim himenaeos integer lacinia lacus.</p>\r\n<p>Sed dictum a convallis donec varius in eros. Curabitur fermentum laoreet id est sem luctus inceptos. Elementum facilisi sociis morbi vehicula risus nascetur placerat. Nisl neque eget non vehicula himenaeos bibendum facilisi. Justo sapien mi eget penatibus aenean accumsan condimentum. Vehicula montes mus viverra metus fermentum ultrices nullam. Curabitur placerat ante et nisl cubilia nascetur nunc.</p>\r\n<p>Tempus porta mi duis dui mi risus primis. Mattis dui tempor etiam consequat proin purus commodo. Quis placerat odio consectetur tortor duis id ante. Molestie nulla luctus venenatis dolor elit volutpat fringilla. Sollicitudin phasellus torquent aliquet quam iaculis aliquam adipiscing. Cras ridiculus curae rhoncus sed tellus magna posuere. Luctus senectus sed justo rutrum ad ac justo.</p>\r\n<p>Sollicitudin est eros dignissim diam turpis ridiculus cubilia. Interdum vehicula sem convallis pulvinar maecenas etiam conubia. Posuere sollicitudin arcu iaculis litora turpis sociosqu congue. Sagittis cubilia pharetra enim fringilla curae litora tempus. Vestibulum ultrices penatibus platea odio egestas venenatis dapibus. Viverra aliquet lorem massa mus ultrices dictumst molestie. Iaculis nisi suscipit eros proin cubilia volutpat fringilla.</p>', 'published', 1, NULL, 'full-width', 0, '2025-04-06 16:40:52', '2025-05-30 14:24:55', '2025-05-27 09:46:15'),
(2, 'Home', 'home', '<p>Benvenuto sul mio sito personale. Mi dedico alla creazione di soluzioni web eleganti, funzionali e accessibili. Con una passione per il design e l\'esperienza utente, trasformo idee in progetti digitali di successo.</p>\r\n<p>Esplora il mio portfolio per vedere i miei lavori recenti o contattami per discutere del tuo prossimo progetto.</p>', 'published', 1, NULL, 'default', 0, '2025-05-30 14:25:25', '2025-06-01 17:19:55', '2025-06-01 17:19:55');

-- --------------------------------------------------------

--
-- Struttura della tabella `page_revisions`
--

CREATE TABLE `page_revisions` (
  `id` int NOT NULL,
  `page_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `status` enum('published','draft','trash') NOT NULL DEFAULT 'draft',
  `revision_note` text,
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `page_revisions`
--

INSERT INTO `page_revisions` (`id`, `page_id`, `title`, `content`, `status`, `revision_note`, `created_by`, `created_at`) VALUES
(2, 1, 'testing', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Rutrum commodo nulla dui torquent molestie interdum cum. Taciti nisl inceptos consectetur cras taciti turpis ultrices. Tellus turpis dictum ullamcorper duis vitae ipsum nostra. Justo penatibus eros justo mi nec sollicitudin in. Hendrerit et ad non nullam pulvinar diam pellentesque. Enim sollicitudin ultricies dignissim himenaeos integer lacinia lacus.</p>\r\n<p>Sed dictum a convallis donec varius in eros. Curabitur fermentum laoreet id est sem luctus inceptos. Elementum facilisi sociis morbi vehicula risus nascetur placerat. Nisl neque eget non vehicula himenaeos bibendum facilisi. Justo sapien mi eget penatibus aenean accumsan condimentum. Vehicula montes mus viverra metus fermentum ultrices nullam. Curabitur placerat ante et nisl cubilia nascetur nunc.</p>\r\n<p>Tempus porta mi duis dui mi risus primis. Mattis dui tempor etiam consequat proin purus commodo. Quis placerat odio consectetur tortor duis id ante. Molestie nulla luctus venenatis dolor elit volutpat fringilla. Sollicitudin phasellus torquent aliquet quam iaculis aliquam adipiscing. Cras ridiculus curae rhoncus sed tellus magna posuere. Luctus senectus sed justo rutrum ad ac justo.</p>\r\n<p>Sollicitudin est eros dignissim diam turpis ridiculus cubilia. Interdum vehicula sem convallis pulvinar maecenas etiam conubia. Posuere sollicitudin arcu iaculis litora turpis sociosqu congue. Sagittis cubilia pharetra enim fringilla curae litora tempus. Vestibulum ultrices penatibus platea odio egestas venenatis dapibus. Viverra aliquet lorem massa mus ultrices dictumst molestie. Iaculis nisi suscipit eros proin cubilia volutpat fringilla.</p>', 'draft', 'Initial version', 1, '2025-04-06 16:40:52'),
(3, 1, 'testing new', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Rutrum commodo nulla dui torquent molestie interdum cum. Taciti nisl inceptos consectetur cras taciti turpis ultrices. Tellus turpis dictum ullamcorper duis vitae ipsum nostra. Justo penatibus eros justo mi nec sollicitudin in. Hendrerit et ad non nullam pulvinar diam pellentesque. Enim sollicitudin ultricies dignissim himenaeos integer lacinia lacus.</p>\r\n<p>Sed dictum a convallis donec varius in eros. Curabitur fermentum laoreet id est sem luctus inceptos. Elementum facilisi sociis morbi vehicula risus nascetur placerat. Nisl neque eget non vehicula himenaeos bibendum facilisi. Justo sapien mi eget penatibus aenean accumsan condimentum. Vehicula montes mus viverra metus fermentum ultrices nullam. Curabitur placerat ante et nisl cubilia nascetur nunc.</p>\r\n<p>Tempus porta mi duis dui mi risus primis. Mattis dui tempor etiam consequat proin purus commodo. Quis placerat odio consectetur tortor duis id ante. Molestie nulla luctus venenatis dolor elit volutpat fringilla. Sollicitudin phasellus torquent aliquet quam iaculis aliquam adipiscing. Cras ridiculus curae rhoncus sed tellus magna posuere. Luctus senectus sed justo rutrum ad ac justo.</p>\r\n<p>Sollicitudin est eros dignissim diam turpis ridiculus cubilia. Interdum vehicula sem convallis pulvinar maecenas etiam conubia. Posuere sollicitudin arcu iaculis litora turpis sociosqu congue. Sagittis cubilia pharetra enim fringilla curae litora tempus. Vestibulum ultrices penatibus platea odio egestas venenatis dapibus. Viverra aliquet lorem massa mus ultrices dictumst molestie. Iaculis nisi suscipit eros proin cubilia volutpat fringilla.</p>', 'draft', 'Updated page', 1, '2025-04-06 16:51:01'),
(4, 1, 'testing new', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Rutrum commodo nulla dui torquent molestie interdum cum. Taciti nisl inceptos consectetur cras taciti turpis ultrices. Tellus turpis dictum ullamcorper duis vitae ipsum nostra. Justo penatibus eros justo mi nec sollicitudin in. Hendrerit et ad non nullam pulvinar diam pellentesque. Enim sollicitudin ultricies dignissim himenaeos integer lacinia lacus.</p>\r\n<p>Sed dictum a convallis donec varius in eros. Curabitur fermentum laoreet id est sem luctus inceptos. Elementum facilisi sociis morbi vehicula risus nascetur placerat. Nisl neque eget non vehicula himenaeos bibendum facilisi. Justo sapien mi eget penatibus aenean accumsan condimentum. Vehicula montes mus viverra metus fermentum ultrices nullam. Curabitur placerat ante et nisl cubilia nascetur nunc.</p>\r\n<p>Tempus porta mi duis dui mi risus primis. Mattis dui tempor etiam consequat proin purus commodo. Quis placerat odio consectetur tortor duis id ante. Molestie nulla luctus venenatis dolor elit volutpat fringilla. Sollicitudin phasellus torquent aliquet quam iaculis aliquam adipiscing. Cras ridiculus curae rhoncus sed tellus magna posuere. Luctus senectus sed justo rutrum ad ac justo.</p>\r\n<p>Sollicitudin est eros dignissim diam turpis ridiculus cubilia. Interdum vehicula sem convallis pulvinar maecenas etiam conubia. Posuere sollicitudin arcu iaculis litora turpis sociosqu congue. Sagittis cubilia pharetra enim fringilla curae litora tempus. Vestibulum ultrices penatibus platea odio egestas venenatis dapibus. Viverra aliquet lorem massa mus ultrices dictumst molestie. Iaculis nisi suscipit eros proin cubilia volutpat fringilla.</p>', 'draft', 'Updated page', 1, '2025-04-06 16:51:13'),
(5, 1, 'testing new', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Rutrum commodo nulla dui torquent molestie interdum cum. Taciti nisl inceptos consectetur cras taciti turpis ultrices. Tellus turpis dictum ullamcorper duis vitae ipsum nostra. Justo penatibus eros justo mi nec sollicitudin in. Hendrerit et ad non nullam pulvinar diam pellentesque. Enim sollicitudin ultricies dignissim himenaeos integer lacinia lacus.</p>\r\n<p>Sed dictum a convallis donec varius in eros. Curabitur fermentum laoreet id est sem luctus inceptos. Elementum facilisi sociis morbi vehicula risus nascetur placerat. Nisl neque eget non vehicula himenaeos bibendum facilisi. Justo sapien mi eget penatibus aenean accumsan condimentum. Vehicula montes mus viverra metus fermentum ultrices nullam. Curabitur placerat ante et nisl cubilia nascetur nunc.</p>\r\n<p>Tempus porta mi duis dui mi risus primis. Mattis dui tempor etiam consequat proin purus commodo. Quis placerat odio consectetur tortor duis id ante. Molestie nulla luctus venenatis dolor elit volutpat fringilla. Sollicitudin phasellus torquent aliquet quam iaculis aliquam adipiscing. Cras ridiculus curae rhoncus sed tellus magna posuere. Luctus senectus sed justo rutrum ad ac justo.</p>\r\n<p>Sollicitudin est eros dignissim diam turpis ridiculus cubilia. Interdum vehicula sem convallis pulvinar maecenas etiam conubia. Posuere sollicitudin arcu iaculis litora turpis sociosqu congue. Sagittis cubilia pharetra enim fringilla curae litora tempus. Vestibulum ultrices penatibus platea odio egestas venenatis dapibus. Viverra aliquet lorem massa mus ultrices dictumst molestie. Iaculis nisi suscipit eros proin cubilia volutpat fringilla.</p>', 'draft', 'Restored from revision #4', 1, '2025-04-06 16:54:23'),
(7, 1, 'testing', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Rutrum commodo nulla dui torquent molestie interdum cum. Taciti nisl inceptos consectetur cras taciti turpis ultrices. Tellus turpis dictum ullamcorper duis vitae ipsum nostra. Justo penatibus eros justo mi nec sollicitudin in. Hendrerit et ad non nullam pulvinar diam pellentesque. Enim sollicitudin ultricies dignissim himenaeos integer lacinia lacus.</p>\r\n<p>Sed dictum a convallis donec varius in eros. Curabitur fermentum laoreet id est sem luctus inceptos. Elementum facilisi sociis morbi vehicula risus nascetur placerat. Nisl neque eget non vehicula himenaeos bibendum facilisi. Justo sapien mi eget penatibus aenean accumsan condimentum. Vehicula montes mus viverra metus fermentum ultrices nullam. Curabitur placerat ante et nisl cubilia nascetur nunc.</p>\r\n<p>Tempus porta mi duis dui mi risus primis. Mattis dui tempor etiam consequat proin purus commodo. Quis placerat odio consectetur tortor duis id ante. Molestie nulla luctus venenatis dolor elit volutpat fringilla. Sollicitudin phasellus torquent aliquet quam iaculis aliquam adipiscing. Cras ridiculus curae rhoncus sed tellus magna posuere. Luctus senectus sed justo rutrum ad ac justo.</p>\r\n<p>Sollicitudin est eros dignissim diam turpis ridiculus cubilia. Interdum vehicula sem convallis pulvinar maecenas etiam conubia. Posuere sollicitudin arcu iaculis litora turpis sociosqu congue. Sagittis cubilia pharetra enim fringilla curae litora tempus. Vestibulum ultrices penatibus platea odio egestas venenatis dapibus. Viverra aliquet lorem massa mus ultrices dictumst molestie. Iaculis nisi suscipit eros proin cubilia volutpat fringilla.</p>', 'published', 'Updated page', 1, '2025-05-27 09:46:15'),
(8, 1, 'testing', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Rutrum commodo nulla dui torquent molestie interdum cum. Taciti nisl inceptos consectetur cras taciti turpis ultrices. Tellus turpis dictum ullamcorper duis vitae ipsum nostra. Justo penatibus eros justo mi nec sollicitudin in. Hendrerit et ad non nullam pulvinar diam pellentesque. Enim sollicitudin ultricies dignissim himenaeos integer lacinia lacus.</p>\r\n<p>Sed dictum a convallis donec varius in eros. Curabitur fermentum laoreet id est sem luctus inceptos. Elementum facilisi sociis morbi vehicula risus nascetur placerat. Nisl neque eget non vehicula himenaeos bibendum facilisi. Justo sapien mi eget penatibus aenean accumsan condimentum. Vehicula montes mus viverra metus fermentum ultrices nullam. Curabitur placerat ante et nisl cubilia nascetur nunc.</p>\r\n<p>Tempus porta mi duis dui mi risus primis. Mattis dui tempor etiam consequat proin purus commodo. Quis placerat odio consectetur tortor duis id ante. Molestie nulla luctus venenatis dolor elit volutpat fringilla. Sollicitudin phasellus torquent aliquet quam iaculis aliquam adipiscing. Cras ridiculus curae rhoncus sed tellus magna posuere. Luctus senectus sed justo rutrum ad ac justo.</p>\r\n<p>Sollicitudin est eros dignissim diam turpis ridiculus cubilia. Interdum vehicula sem convallis pulvinar maecenas etiam conubia. Posuere sollicitudin arcu iaculis litora turpis sociosqu congue. Sagittis cubilia pharetra enim fringilla curae litora tempus. Vestibulum ultrices penatibus platea odio egestas venenatis dapibus. Viverra aliquet lorem massa mus ultrices dictumst molestie. Iaculis nisi suscipit eros proin cubilia volutpat fringilla.</p>', 'published', 'Updated page', 1, '2025-05-27 09:47:43'),
(9, 1, 'testing', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Rutrum commodo nulla dui torquent molestie interdum cum. Taciti nisl inceptos consectetur cras taciti turpis ultrices. Tellus turpis dictum ullamcorper duis vitae ipsum nostra. Justo penatibus eros justo mi nec sollicitudin in. Hendrerit et ad non nullam pulvinar diam pellentesque. Enim sollicitudin ultricies dignissim himenaeos integer lacinia lacus.</p>\r\n<p>Sed dictum a convallis donec varius in eros. Curabitur fermentum laoreet id est sem luctus inceptos. Elementum facilisi sociis morbi vehicula risus nascetur placerat. Nisl neque eget non vehicula himenaeos bibendum facilisi. Justo sapien mi eget penatibus aenean accumsan condimentum. Vehicula montes mus viverra metus fermentum ultrices nullam. Curabitur placerat ante et nisl cubilia nascetur nunc.</p>\r\n<p>Tempus porta mi duis dui mi risus primis. Mattis dui tempor etiam consequat proin purus commodo. Quis placerat odio consectetur tortor duis id ante. Molestie nulla luctus venenatis dolor elit volutpat fringilla. Sollicitudin phasellus torquent aliquet quam iaculis aliquam adipiscing. Cras ridiculus curae rhoncus sed tellus magna posuere. Luctus senectus sed justo rutrum ad ac justo.</p>\r\n<p>Sollicitudin est eros dignissim diam turpis ridiculus cubilia. Interdum vehicula sem convallis pulvinar maecenas etiam conubia. Posuere sollicitudin arcu iaculis litora turpis sociosqu congue. Sagittis cubilia pharetra enim fringilla curae litora tempus. Vestibulum ultrices penatibus platea odio egestas venenatis dapibus. Viverra aliquet lorem massa mus ultrices dictumst molestie. Iaculis nisi suscipit eros proin cubilia volutpat fringilla.</p>', 'published', 'Updated page', 1, '2025-05-27 09:52:43'),
(10, 1, 'testing', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Rutrum commodo nulla dui torquent molestie interdum cum. Taciti nisl inceptos consectetur cras taciti turpis ultrices. Tellus turpis dictum ullamcorper duis vitae ipsum nostra. Justo penatibus eros justo mi nec sollicitudin in. Hendrerit et ad non nullam pulvinar diam pellentesque. Enim sollicitudin ultricies dignissim himenaeos integer lacinia lacus.</p>\r\n<p>Sed dictum a convallis donec varius in eros. Curabitur fermentum laoreet id est sem luctus inceptos. Elementum facilisi sociis morbi vehicula risus nascetur placerat. Nisl neque eget non vehicula himenaeos bibendum facilisi. Justo sapien mi eget penatibus aenean accumsan condimentum. Vehicula montes mus viverra metus fermentum ultrices nullam. Curabitur placerat ante et nisl cubilia nascetur nunc.</p>\r\n<p>Tempus porta mi duis dui mi risus primis. Mattis dui tempor etiam consequat proin purus commodo. Quis placerat odio consectetur tortor duis id ante. Molestie nulla luctus venenatis dolor elit volutpat fringilla. Sollicitudin phasellus torquent aliquet quam iaculis aliquam adipiscing. Cras ridiculus curae rhoncus sed tellus magna posuere. Luctus senectus sed justo rutrum ad ac justo.</p>\r\n<p>Sollicitudin est eros dignissim diam turpis ridiculus cubilia. Interdum vehicula sem convallis pulvinar maecenas etiam conubia. Posuere sollicitudin arcu iaculis litora turpis sociosqu congue. Sagittis cubilia pharetra enim fringilla curae litora tempus. Vestibulum ultrices penatibus platea odio egestas venenatis dapibus. Viverra aliquet lorem massa mus ultrices dictumst molestie. Iaculis nisi suscipit eros proin cubilia volutpat fringilla.</p>', 'published', 'Restored from revision #7', 1, '2025-05-27 09:57:45'),
(11, 1, 'testing', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Rutrum commodo nulla dui torquent molestie interdum cum. Taciti nisl inceptos consectetur cras taciti turpis ultrices. Tellus turpis dictum ullamcorper duis vitae ipsum nostra. Justo penatibus eros justo mi nec sollicitudin in. Hendrerit et ad non nullam pulvinar diam pellentesque. Enim sollicitudin ultricies dignissim himenaeos integer lacinia lacus.</p>\r\n<p>Sed dictum a convallis donec varius in eros. Curabitur fermentum laoreet id est sem luctus inceptos. Elementum facilisi sociis morbi vehicula risus nascetur placerat. Nisl neque eget non vehicula himenaeos bibendum facilisi. Justo sapien mi eget penatibus aenean accumsan condimentum. Vehicula montes mus viverra metus fermentum ultrices nullam. Curabitur placerat ante et nisl cubilia nascetur nunc.</p>\r\n<p>Tempus porta mi duis dui mi risus primis. Mattis dui tempor etiam consequat proin purus commodo. Quis placerat odio consectetur tortor duis id ante. Molestie nulla luctus venenatis dolor elit volutpat fringilla. Sollicitudin phasellus torquent aliquet quam iaculis aliquam adipiscing. Cras ridiculus curae rhoncus sed tellus magna posuere. Luctus senectus sed justo rutrum ad ac justo.</p>\r\n<p>Sollicitudin est eros dignissim diam turpis ridiculus cubilia. Interdum vehicula sem convallis pulvinar maecenas etiam conubia. Posuere sollicitudin arcu iaculis litora turpis sociosqu congue. Sagittis cubilia pharetra enim fringilla curae litora tempus. Vestibulum ultrices penatibus platea odio egestas venenatis dapibus. Viverra aliquet lorem massa mus ultrices dictumst molestie. Iaculis nisi suscipit eros proin cubilia volutpat fringilla.</p>', 'published', 'Updated page', 1, '2025-05-30 14:24:55'),
(12, 2, 'Home', '<p>Benvenuto sul mio sito personale. Mi dedico alla creazione di soluzioni web eleganti, funzionali e accessibili. Con una passione per il design e l\'esperienza utente, trasformo idee in progetti digitali di successo.</p>\r\n<p>Esplora il mio portfolio per vedere i miei lavori recenti o contattami per discutere del tuo prossimo progetto.</p>', 'draft', 'Initial version', 1, '2025-05-30 14:25:25'),
(13, 2, 'Home', '<p>Benvenuto sul mio sito personale. Mi dedico alla creazione di soluzioni web eleganti, funzionali e accessibili. Con una passione per il design e l\'esperienza utente, trasformo idee in progetti digitali di successo.</p>\r\n<p>Esplora il mio portfolio per vedere i miei lavori recenti o contattami per discutere del tuo prossimo progetto.</p>', 'published', 'Updated page', 1, '2025-06-01 17:19:55');

-- --------------------------------------------------------

--
-- Struttura della tabella `plugins`
--

CREATE TABLE `plugins` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `version` varchar(20) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `installed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `posts`
--

CREATE TABLE `posts` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text,
  `excerpt` text,
  `status` enum('published','draft','trash') NOT NULL DEFAULT 'draft',
  `comment_status` enum('open','closed') NOT NULL DEFAULT 'open',
  `author_id` int NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `published_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `content`, `excerpt`, `status`, `comment_status`, `author_id`, `featured_image`, `created_at`, `updated_at`, `published_at`) VALUES
(19, 'aaaaa', 'aaaaa', '<p>aaaaaaaaaaaaaaa</p>', 'aaaaaa', 'draft', 'open', 1, '/uploads/media/uploads/media/2025/05/thumbs/Screenshot-2025-01-06-165614_683b2179af52a.png', '2025-05-30 14:55:06', '2025-05-31 15:34:32', NULL),
(21, 'CBI Checker: Uno Strumento per la Validazione XML Bancaria', 'cbi-checker-uno-strumento-per-la-validazione-xml-bancaria', '<h2>Uno Strumento Dedicato</h2>\r\n<p>Nel panorama degli strumenti di validazione XML disponibili online, esiste una sorprendente lacuna quando si tratta di verificare documenti conformi allo standard CBI (Corporate Banking Interbancario) utilizzato nel sistema bancario italiano. Questa mancanza ha creato notevoli difficolt&agrave; per sviluppatori, consulenti finanziari e professionisti bancari che necessitano di verificare la conformit&agrave; dei propri file XML prima dell\'invio ai sistemi interbancari. &Egrave; proprio per colmare questo vuoto che &egrave; nato CBI Checker, un\'applicazione web specializzata nella validazione e formattazione di file XML secondo lo schema CBI Payment Request.</p>\r\n<h2>Caratteristiche Uniche per Esigenze Specifiche</h2>\r\n<p>A differenza dei validatori XML generici, CBI Checker &egrave; stato progettato specificamente per lo standard CBI Payment Request (CBIPaymentRequest.00.04.01.xsd), offrendo funzionalit&agrave; che nessun altro strumento online fornisce. L\'interfaccia intuitiva con supporto per drag and drop consente di caricare rapidamente i file, mentre il sistema di validazione non si limita a segnalare gli errori, ma li evidenzia direttamente nel codice con riferimenti precisi alla linea problematica. Questa caratteristica, unita alla possibilit&agrave; di formattare automaticamente il documento mantenendone la validit&agrave;, rende CBI Checker uno strumento indispensabile per chiunque lavori con documenti XML nel contesto bancario italiano.</p>\r\n<h2>Accessibilit&agrave; e Sicurezza</h2>\r\n<p>Una delle priorit&agrave; nello sviluppo di CBI Checker &egrave; stata garantire la massima accessibilit&agrave; senza compromettere la sicurezza dei dati sensibili. Per questo motivo, l\'applicazione &egrave; stata progettata per funzionare completamente in locale, senza inviare alcun dato a server esterni. Pu&ograve; essere facilmente eseguita tramite Docker, rendendo l\'installazione semplice e veloce su qualsiasi sistema operativo, oppure installata manualmente su un server web con supporto PHP. Questa flessibilit&agrave;, combinata con la natura open source del progetto, assicura che gli utenti possano verificare personalmente il codice e adattare lo strumento alle proprie esigenze specifiche.</p>\r\n<h2>Un Contributo alla Comunit&agrave; Finanziaria Italiana</h2>\r\n<p>CBI Checker rappresenta un contributo significativo alla comunit&agrave; di sviluppatori e professionisti finanziari italiani, offrendo gratuitamente uno strumento che colma una lacuna importante. La documentazione dettagliata, gli esempi inclusi e l\'interfaccia user-friendly rendono la validazione XML accessibile anche a chi non possiede competenze tecniche avanzate. In un contesto in cui gli errori nei file di pagamento possono causare ritardi e problemi significativi, avere a disposizione uno strumento dedicato e affidabile come CBI Checker pu&ograve; fare la differenza nella gestione quotidiana delle operazioni bancarie elettroniche.</p>\r\n<p>Versione online:&nbsp;<a href=\"https://cbitester.jmclement.net/\" target=\"_blank\" rel=\"noopener\">Qui</a>&nbsp;e sorgenti disponibili&nbsp;<a href=\"https://github.com/jeanmarieclement/CBI-Checker\" target=\"_blank\" rel=\"noopener\">su GitHub</a> (con file docker per uso locale)</p>', 'Nel panorama degli strumenti di validazione XML disponibili online, esiste una sorprendente lacuna quando si tratta di verificare documenti conformi allo standard CBI (Corporate Banking Interbancario) utilizzato nel sistema bancario italiano. Questa mancanza ha creato notevoli difficoltà per sviluppatori, consulenti finanziari e professionisti bancari che necessitano di verificare la conformità dei propri file XML prima dell\'invio ai sistemi interbancari. È proprio per colmare questo vuoto che è nato CBI Checker, un\'applicazione web specializzata nella validazione e formattazione di file XML secondo lo schema CBI Payment Request.', 'published', 'open', 1, '', '2025-05-30 14:59:31', '2025-05-30 15:01:02', '2025-05-30 15:01:02'),
(22, 'nuovo con immagine', 'nuovo-con-immagine', '<p>nuovo</p>', 'nuovo', 'published', 'open', 1, '/uploads/media/uploads/media/2025/05/thumbs/20250513_114924-Grandi_6834a4af34ece.jpg', '2025-05-30 16:39:47', '2025-05-30 19:01:24', '2025-05-30 19:00:15'),
(28, 'nuovo', 'nuovo-1', '<p>nuovo</p>', 'nuovo', 'draft', 'open', 1, '', '2025-05-30 16:58:04', NULL, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `post_categories`
--

CREATE TABLE `post_categories` (
  `post_id` int NOT NULL,
  `category_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `post_categories`
--

INSERT INTO `post_categories` (`post_id`, `category_id`) VALUES
(19, 3),
(28, 5),
(21, 6);

-- --------------------------------------------------------

--
-- Struttura della tabella `post_tags`
--

CREATE TABLE `post_tags` (
  `post_id` int NOT NULL,
  `tag_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `post_tags`
--

INSERT INTO `post_tags` (`post_id`, `tag_id`) VALUES
(28, 12);

-- --------------------------------------------------------

--
-- Struttura della tabella `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `level` int NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `template_permissions` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `roles`
--

INSERT INTO `roles` (`id`, `name`, `level`, `description`, `created_at`, `updated_at`, `template_permissions`) VALUES
(1, 'super_admin', 4, 'Full system access', '2025-04-06 17:56:22', '2025-04-07 13:24:41', '{\"allowed_templates\": [\"*\"]}'),
(2, 'admin', 3, 'Administrative access', '2025-04-06 17:56:22', '2025-04-07 13:24:41', '{\"allowed_templates\": [\"dashboard\", \"profile\", \"users\", \"articles\", \"pages\", \"media\", \"comments\", \"settings\"]}'),
(3, 'editor', 2, 'Can edit all content', '2025-04-06 17:56:22', '2025-04-07 13:24:41', '{\"allowed_templates\": [\"dashboard\", \"profile\", \"articles\", \"pages\", \"media\", \"comments\"]}'),
(4, 'author', 1, 'Can create and edit own content', '2025-04-06 17:56:22', '2025-04-07 13:24:41', '{\"allowed_templates\": [\"dashboard\", \"profile\", \"articles\", \"pages\"]}'),
(5, 'subscriber', 0, 'Basic read-only access', '2025-04-06 17:56:22', '2025-04-07 13:24:41', '{\"allowed_templates\": [\"dashboard\", \"profile\"]}');

-- --------------------------------------------------------

--
-- Struttura della tabella `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text,
  `description` varchar(255) DEFAULT NULL,
  `autoload` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `description`, `autoload`, `created_at`, `updated_at`) VALUES
(1, 'homepage_mode', 'page', NULL, 1, '2025-06-01 15:19:50', '2025-06-01 15:19:50'),
(3, 'homepage_page', '2', NULL, 1, '2025-06-01 15:19:50', '2025-06-01 15:19:50'),
(5, 'site_title', 'Jean-Marie Clement', NULL, 1, '2025-06-01 15:19:50', '2025-06-05 18:32:53'),
(7, 'site_description', 'Sviluppatore web e consulente IT con esperienza in PHP, JavaScript, VB.NET. Soluzioni software personalizzate e consulenza informatica per aziende e professionisti.', NULL, 1, '2025-06-01 15:19:50', '2025-06-01 15:19:50'),
(9, 'site_language', 'it', NULL, 1, '2025-06-01 15:19:50', '2025-06-01 15:19:50'),
(11, 'site_timezone', 'Europe/Rome', NULL, 1, '2025-06-01 15:19:50', '2025-06-01 15:19:50'),
(13, 'posts_per_page', '10', NULL, 1, '2025-06-01 15:19:50', '2025-06-01 15:19:50'),
(15, 'comments_enabled', '1', NULL, 1, '2025-06-01 15:19:50', '2025-06-01 15:19:50'),
(17, 'meta_description', 'Sviluppatore web e consulente IT con esperienza in PHP, JavaScript, VB.NET. Soluzioni software personalizzate e consulenza informatica per aziende e professionisti.', NULL, 1, '2025-06-01 15:19:51', '2025-06-01 15:19:51'),
(19, 'meta_keywords', 'sviluppatore web, consulente IT, PHP, JavaScript, VB.NET, Brescia, sviluppo software', NULL, 1, '2025-06-01 15:19:51', '2025-06-01 15:19:51'),
(21, 'SITE_NAME', 'Sito Personale', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 18:34:04'),
(22, 'SITE_URL', 'http://localhost', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 15:21:16'),
(23, 'ADMIN_URL', 'http://localhost/admin', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 15:21:16'),
(24, 'THEME_ACTIVE', 'jmclement', NULL, 1, '2025-06-05 15:21:16', '2025-06-07 16:16:46'),
(25, 'ALLOW_REGISTRATION', '1', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 15:21:16'),
(26, 'SESSION_TIMEOUT', '1800', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 15:21:16'),
(27, 'DEBUG_MODE', '1', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 18:33:35'),
(28, 'TIMEZONE', 'Europe/Rome', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 15:21:16'),
(29, 'LANGUAGE', 'it', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 15:21:16'),
(30, 'SMTP_HOST', '', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 16:57:46'),
(31, 'SMTP_PORT', '', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 16:57:46'),
(32, 'SMTP_USER', '', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 16:57:46'),
(33, 'SMTP_PASS', 'admin123', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 16:57:46'),
(34, 'MAIL_FROM', 'admin@example.com', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 16:57:46'),
(35, 'MAIL_FROM_NAME', '', NULL, 1, '2025-06-05 15:21:16', '2025-06-05 16:57:46');

-- --------------------------------------------------------

--
-- Struttura della tabella `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `tags`
--

INSERT INTO `tags` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(2, '123', '123', NULL, '2025-04-17 17:45:42', '2025-04-17 17:45:42'),
(3, 'admin', 'admin', NULL, '2025-04-17 17:45:42', '2025-04-17 17:45:42'),
(5, 'mistery', 'mistery', NULL, '2025-05-23 16:50:18', '2025-05-23 16:50:18'),
(7, 'nuovo', 'nuovo', NULL, '2025-05-30 16:58:04', '2025-05-30 16:58:04'),
(12, 'mai fatto', 'mai-fatto', NULL, '2025-05-30 17:37:17', '2025-05-30 17:37:17');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'subscriber',
  `status` enum('active','inactive','banned') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `display_name`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$12$mGrCTA7lYBNSD8X85NpB2OibBvlM0hPqwg4HjkJQm3DgjOYN6kbqa', 'admin@example.com', 'Administrator', 'super_admin', 'active', '2025-04-02 17:19:21', '2025-06-19 15:09:08'),
(2, 'JM', '$2y$12$0uoedL5CdbQi/eut/MkXGu6kOt3tsy1Du9hPyPOtUwOGFZfMtYFNO', 'info@jmclement.net', 'Jean-Marie', 'subscriber', 'active', '2025-04-03 13:18:48', '2025-04-03 17:40:42');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indici per le tabelle `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `filetype` (`filetype`);

--
-- Indici per le tabelle `media_relationships`
--
ALTER TABLE `media_relationships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `media_id` (`media_id`),
  ADD KEY `related` (`related_id`,`related_type`);

--
-- Indici per le tabelle `menu_blocks`
--
ALTER TABLE `menu_blocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indici per le tabelle `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `block_id` (`block_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indici per le tabelle `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_name` (`option_name`);

--
-- Indici per le tabelle `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indici per le tabelle `page_revisions`
--
ALTER TABLE `page_revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_id` (`page_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indici per le tabelle `plugins`
--
ALTER TABLE `plugins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indici per le tabelle `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`);

--
-- Indici per le tabelle `post_categories`
--
ALTER TABLE `post_categories`
  ADD PRIMARY KEY (`post_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indici per le tabelle `post_tags`
--
ALTER TABLE `post_tags`
  ADD PRIMARY KEY (`post_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indici per le tabelle `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indici per le tabelle `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indici per le tabelle `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_role` (`role`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `media`
--
ALTER TABLE `media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `media_relationships`
--
ALTER TABLE `media_relationships`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `menu_blocks`
--
ALTER TABLE `menu_blocks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `options`
--
ALTER TABLE `options`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT per la tabella `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `page_revisions`
--
ALTER TABLE `page_revisions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT per la tabella `plugins`
--
ALTER TABLE `plugins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT per la tabella `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=588;

--
-- AUTO_INCREMENT per la tabella `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `media_relationships`
--
ALTER TABLE `media_relationships`
  ADD CONSTRAINT `fk_medias` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Limiti per la tabella `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`block_id`) REFERENCES `menu_blocks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_items_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pages_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `page_revisions`
--
ALTER TABLE `page_revisions`
  ADD CONSTRAINT `fk_pages_revisions` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Limiti per la tabella `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Limiti per la tabella `post_categories`
--
ALTER TABLE `post_categories`
  ADD CONSTRAINT `fk_categories_posts` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_posts_categories` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Limiti per la tabella `post_tags`
--
ALTER TABLE `post_tags`
  ADD CONSTRAINT `fk_posts_tags` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_tags_posts` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Limiti per la tabella `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role`) REFERENCES `roles` (`name`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
