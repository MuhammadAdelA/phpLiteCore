-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 18, 2025 at 01:06 PM
-- Server version: 8.0.43
-- PHP Version: 8.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `phplitecore`
--

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `body`, `created_at`, `updated_at`) VALUES
(1, 1, 'Post Title 1', 'This is the body content for post number 1. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', '2025-08-29 12:55:23', '2025-08-29 12:55:23'),
(2, 2, 'Post Title 2', 'This is the body content for post number 2. Praesent id metus massa. Class aptent taciti sociosqu ad litora torquent per conubia nostra.', '2025-08-28 12:55:23', '2025-08-28 12:55:23'),
(3, 1, 'Post Title 3', 'This is the body content for post number 3. In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis.', '2025-08-27 12:55:23', '2025-08-27 12:55:23'),
(4, 2, 'Post Title 4', 'This is the body content for post number 4. Nunc vitae scelerisque ipsum. Sed non est.', '2025-08-26 12:55:23', '2025-08-26 12:55:23'),
(5, 1, 'Post Title 5', 'This is the body content for post number 5. Donec vel egestas dolor. Cras consequat, nunc id pretium aliquet.', '2025-08-25 12:55:23', '2025-08-25 12:55:23'),
(6, 2, 'Post Title 6', 'This is the body content for post number 6. Etiam leo sapien, dictum non turpis eu, egestas lobortis ex.', '2025-08-24 12:55:23', '2025-08-24 12:55:23'),
(7, 1, 'Post Title 7', 'This is the body content for post number 7. Suspendisse potenti. Sed vel neque nec est.', '2025-08-23 12:55:23', '2025-08-23 12:55:23'),
(8, 1, 'Post Title 8', 'This is the body content for post number 8. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.', '2025-08-22 12:55:23', '2025-08-22 12:55:23'),
(9, 2, 'Post Title 9', 'This is the body content for post number 9. Sed nisi. Nulla quis sem at nibh elementum imperdiet.', '2025-08-21 12:55:23', '2025-08-21 12:55:23'),
(10, 1, 'Post Title 10', 'This is the body content for post number 10. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed.', '2025-08-20 12:55:23', '2025-08-20 12:55:23'),
(11, 2, 'Post Title 11', 'This is the body content for post number 11. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', '2025-08-19 12:55:23', '2025-08-19 12:55:23'),
(12, 1, 'Post Title 12', 'This is the body content for post number 12. Praesent id metus massa. Class aptent taciti sociosqu ad litora torquent per conubia nostra.', '2025-08-18 12:55:23', '2025-08-18 12:55:23'),
(13, 2, 'Post Title 13', 'This is the body content for post number 13. In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis.', '2025-08-17 12:55:23', '2025-08-17 12:55:23'),
(14, 1, 'Post Title 14', 'This is the body content for post number 14. Nunc vitae scelerisque ipsum. Sed non est.', '2025-08-16 12:55:23', '2025-08-16 12:55:23'),
(15, 2, 'Post Title 15', 'This is the body content for post number 15. Donec vel egestas dolor. Cras consequat, nunc id pretium aliquet.', '2025-08-15 12:55:23', '2025-08-15 12:55:23'),
(16, 1, 'Post Title 16', 'This is the body content for post number 16. Etiam leo sapien, dictum non turpis eu, egestas lobortis ex.', '2025-08-14 12:55:23', '2025-08-14 12:55:23'),
(17, 2, 'Post Title 17', 'This is the body content for post number 17. Suspendisse potenti. Sed vel neque nec est.', '2025-08-13 12:55:23', '2025-08-13 12:55:23'),
(18, 1, 'Post Title 18', 'This is the body content for post number 18. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.', '2025-08-12 12:55:23', '2025-08-12 12:55:23'),
(19, 2, 'Post Title 19', 'This is the body content for post number 19. Sed nisi. Nulla quis sem at nibh elementum imperdiet.', '2025-08-11 12:55:23', '2025-08-11 12:55:23'),
(20, 1, 'Post Title 20', 'This is the body content for post number 20. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed.', '2025-08-10 12:55:23', '2025-08-10 12:55:23'),
(21, 1, 'Post Title 21', 'This is the body content for post number 21. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', '2025-08-09 12:55:23', '2025-08-09 12:55:23'),
(22, 2, 'Post Title 22', 'This is the body content for post number 22. Praesent id metus massa. Class aptent taciti sociosqu ad litora torquent per conubia nostra.', '2025-08-08 12:55:23', '2025-08-08 12:55:23'),
(23, 1, 'Post Title 23', 'This is the body content for post number 23. In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis.', '2025-08-07 12:55:23', '2025-08-07 12:55:23'),
(24, 2, 'Post Title 24', 'This is the body content for post number 24. Nunc vitae scelerisque ipsum. Sed non est.', '2025-08-06 12:55:23', '2025-08-06 12:55:23'),
(25, 1, 'Post Title 25', 'This is the body content for post number 25. Donec vel egestas dolor. Cras consequat, nunc id pretium aliquet.', '2025-08-05 12:55:23', '2025-08-05 12:55:23'),
(26, 2, 'Post Title 26', 'This is the body content for post number 26. Etiam leo sapien, dictum non turpis eu, egestas lobortis ex.', '2025-08-04 12:55:23', '2025-08-04 12:55:23'),
(27, 1, 'Post Title 27', 'This is the body content for post number 27. Suspendisse potenti. Sed vel neque nec est.', '2025-08-03 12:55:23', '2025-08-03 12:55:23'),
(28, 2, 'Post Title 28', 'This is the body content for post number 28. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.', '2025-08-02 12:55:23', '2025-08-02 12:55:23'),
(29, 1, 'Post Title 29', 'This is the body content for post number 29. Sed nisi. Nulla quis sem at nibh elementum imperdiet.', '2025-08-01 12:55:23', '2025-08-01 12:55:23'),
(30, 2, 'Post Title 30', 'This is the body content for post number 30. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed.', '2025-07-31 12:55:23', '2025-07-31 12:55:23'),
(31, 1, 'Post Title 31', 'This is the body content for post number 31. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', '2025-07-30 12:55:23', '2025-07-30 12:55:23'),
(32, 2, 'Post Title 32', 'This is the body content for post number 32. Praesent id metus massa. Class aptent taciti sociosqu ad litora torquent per conubia nostra.', '2025-07-29 12:55:23', '2025-07-29 12:55:23'),
(33, 1, 'Post Title 33', 'This is the body content for post number 33. In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis.', '2025-07-28 12:55:23', '2025-07-28 12:55:23'),
(34, 2, 'Post Title 34', 'This is the body content for post number 34. Nunc vitae scelerisque ipsum. Sed non est.', '2025-07-27 12:55:23', '2025-07-27 12:55:23'),
(35, 1, 'Post Title 35', 'This is the body content for post number 35. Donec vel egestas dolor. Cras consequat, nunc id pretium aliquet.', '2025-07-26 12:55:23', '2025-07-26 12:55:23'),
(36, 2, 'Post Title 36', 'This is the body content for post number 36. Etiam leo sapien, dictum non turpis eu, egestas lobortis ex.', '2025-07-25 12:55:23', '2025-07-25 12:55:23'),
(37, 1, 'Post Title 37', 'This is the body content for post number 37. Suspendisse potenti. Sed vel neque nec est.', '2025-07-24 12:55:23', '2025-07-24 12:55:23'),
(38, 2, 'Post Title 38', 'This is the body content for post number 38. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.', '2025-07-23 12:55:23', '2025-07-23 12:55:23'),
(39, 1, 'Post Title 39', 'This is the body content for post number 39. Sed nisi. Nulla quis sem at nibh elementum imperdiet.', '2025-07-22 12:55:23', '2025-07-22 12:55:23'),
(40, 2, 'Post Title 40', 'This is the body content for post number 40. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed.', '2025-07-21 12:55:23', '2025-07-21 12:55:23'),
(41, 1, 'Post Title 41', 'This is the body content for post number 41. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', '2025-07-20 12:55:23', '2025-07-20 12:55:23'),
(42, 2, 'Post Title 42', 'This is the body content for post number 42. Praesent id metus massa. Class aptent taciti sociosqu ad litora torquent per conubia nostra.', '2025-07-19 12:55:23', '2025-07-19 12:55:23'),
(43, 1, 'Post Title 43', 'This is the body content for post number 43. In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis.', '2025-07-18 12:55:23', '2025-07-18 12:55:23'),
(44, 2, 'Post Title 44', 'This is the body content for post number 44. Nunc vitae scelerisque ipsum. Sed non est.', '2025-07-17 12:55:23', '2025-07-17 12:55:23'),
(45, 1, 'Post Title 45', 'This is the body content for post number 45. Donec vel egestas dolor. Cras consequat, nunc id pretium aliquet.', '2025-07-16 12:55:23', '2025-07-16 12:55:23'),
(46, 2, 'Post Title 46', 'This is the body content for post number 46. Etiam leo sapien, dictum non turpis eu, egestas lobortis ex.', '2025-07-15 12:55:23', '2025-07-15 12:55:23'),
(47, 1, 'Post Title 47', 'This is the body content for post number 47. Suspendisse potenti. Sed vel neque nec est.', '2025-07-14 12:55:23', '2025-07-14 12:55:23'),
(48, 2, 'Post Title 48', 'This is the body content for post number 48. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.', '2025-07-13 12:55:23', '2025-07-13 12:55:23'),
(49, 1, 'Post Title 49', 'This is the body content for post number 49. Sed nisi. Nulla quis sem at nibh elementum imperdiet.', '2025-07-12 12:55:23', '2025-07-12 12:55:23'),
(50, 2, 'Post Title 50', 'This is the body content for post number 50. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed.', '2025-07-11 12:55:23', '2025-07-11 12:55:23'),
(51, 1, 'Post Title 51', 'This is the body content for post number 51. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', '2025-07-10 12:55:23', '2025-07-10 12:55:23'),
(52, 2, 'Post Title 52', 'This is the body content for post number 52. Praesent id metus massa. Class aptent taciti sociosqu ad litora torquent per conubia nostra.', '2025-07-09 12:55:23', '2025-07-09 12:55:23'),
(53, 1, 'Post Title 53', 'This is the body content for post number 53. In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis.', '2025-07-08 12:55:23', '2025-07-08 12:55:23'),
(54, 2, 'Post Title 54', 'This is the body content for post number 54. Nunc vitae scelerisque ipsum. Sed non est.', '2025-07-07 12:55:23', '2025-07-07 12:55:23'),
(55, 1, 'Post Title 55', 'This is the body content for post number 55. Donec vel egestas dolor. Cras consequat, nunc id pretium aliquet.', '2025-07-06 12:55:23', '2025-07-06 12:55:23'),
(56, 2, 'Post Title 56', 'This is the body content for post number 56. Etiam leo sapien, dictum non turpis eu, egestas lobortis ex.', '2025-07-05 12:55:23', '2025-07-05 12:55:23'),
(57, 1, 'Post Title 57', 'This is the body content for post number 57. Suspendisse potenti. Sed vel neque nec est.', '2025-07-04 12:55:23', '2025-07-04 12:55:23'),
(58, 2, 'Post Title 58', 'This is the body content for post number 58. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.', '2025-07-03 12:55:23', '2025-07-03 12:55:23'),
(59, 1, 'Post Title 59', 'This is the body content for post number 59. Sed nisi. Nulla quis sem at nibh elementum imperdiet.', '2025-07-02 12:55:23', '2025-07-02 12:55:23'),
(60, 2, 'Post Title 60', 'This is the body content for post number 60. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed.', '2025-07-01 12:55:23', '2025-07-01 12:55:23'),
(61, 1, 'Post Title 61', 'This is the body content for post number 61. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', '2025-06-30 12:55:23', '2025-06-30 12:55:23'),
(62, 2, 'Post Title 62', 'This is the body content for post number 62. Praesent id metus massa. Class aptent taciti sociosqu ad litora torquent per conubia nostra.', '2025-06-29 12:55:23', '2025-06-29 12:55:23'),
(63, 1, 'Post Title 63', 'This is the body content for post number 63. In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis.', '2025-06-28 12:55:23', '2025-06-28 12:55:23'),
(64, 2, 'Post Title 64', 'This is the body content for post number 64. Nunc vitae scelerisque ipsum. Sed non est.', '2025-06-27 12:55:23', '2025-06-27 12:55:23'),
(65, 1, 'Post Title 65', 'This is the body content for post number 65. Donec vel egestas dolor. Cras consequat, nunc id pretium aliquet.', '2025-06-26 12:55:23', '2025-06-26 12:55:23'),
(66, 2, 'Post Title 66', 'This is the body content for post number 66. Etiam leo sapien, dictum non turpis eu, egestas lobortis ex.', '2025-06-25 12:55:23', '2025-06-25 12:55:23'),
(67, 1, 'Post Title 67', 'This is the body content for post number 67. Suspendisse potenti. Sed vel neque nec est.', '2025-06-24 12:55:23', '2025-06-24 12:55:23'),
(68, 2, 'Post Title 68', 'This is the body content for post number 68. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.', '2025-06-23 12:55:23', '2025-06-23 12:55:23'),
(69, 1, 'Post Title 69', 'This is the body content for post number 69. Sed nisi. Nulla quis sem at nibh elementum imperdiet.', '2025-06-22 12:55:23', '2025-06-22 12:55:23'),
(70, 2, 'Post Title 70', 'This is the body content for post number 70. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed.', '2025-06-21 12:55:23', '2025-06-21 12:55:23'),
(71, 1, 'Post Title 71', 'This is the body content for post number 71. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', '2025-06-20 12:55:23', '2025-06-20 12:55:23'),
(72, 2, 'Post Title 72', 'This is the body content for post number 72. Praesent id metus massa. Class aptent taciti sociosqu ad litora torquent per conubia nostra.', '2025-06-19 12:55:23', '2025-06-19 12:55:23'),
(73, 1, 'Post Title 73', 'This is the body content for post number 73. In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis.', '2025-06-18 12:55:23', '2025-06-18 12:55:23'),
(74, 2, 'Post Title 74', 'This is the body content for post number 74. Nunc vitae scelerisque ipsum. Sed non est.', '2025-06-17 12:55:23', '2025-06-17 12:55:23'),
(75, 1, 'Post Title 75', 'This is the body content for post number 75. Donec vel egestas dolor. Cras consequat, nunc id pretium aliquet.', '2025-06-16 12:55:23', '2025-06-16 12:55:23'),
(76, 2, 'Post Title 76', 'This is the body content for post number 76. Etiam leo sapien, dictum non turpis eu, egestas lobortis ex.', '2025-06-15 12:55:23', '2025-06-15 12:55:23'),
(77, 1, 'Post Title 77', 'This is the body content for post number 77. Suspendisse potenti. Sed vel neque nec est.', '2025-06-14 12:55:23', '2025-06-14 12:55:23'),
(78, 2, 'Post Title 78', 'This is the body content for post number 78. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.', '2025-06-13 12:55:23', '2025-06-13 12:55:23'),
(79, 1, 'Post Title 79', 'This is the body content for post number 79. Sed nisi. Nulla quis sem at nibh elementum imperdiet.', '2025-06-12 12:55:23', '2025-06-12 12:55:23'),
(80, 2, 'Post Title 80', 'This is the body content for post number 80. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed.', '2025-06-11 12:55:23', '2025-06-11 12:55:23'),
(81, 1, 'Post Title 81', 'This is the body content for post number 81. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', '2025-06-10 12:55:23', '2025-06-10 12:55:23'),
(82, 2, 'Post Title 82', 'This is the body content for post number 82. Praesent id metus massa. Class aptent taciti sociosqu ad litora torquent per conubia nostra.', '2025-06-09 12:55:23', '2025-06-09 12:55:23'),
(83, 1, 'Post Title 83', 'This is the body content for post number 83. In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis.', '2025-06-08 12:55:23', '2025-06-08 12:55:23'),
(84, 2, 'Post Title 84', 'This is the body content for post number 84. Nunc vitae scelerisque ipsum. Sed non est.', '2025-06-07 12:55:23', '2025-06-07 12:55:23'),
(85, 1, 'Post Title 85', 'This is the body content for post number 85. Donec vel egestas dolor. Cras consequat, nunc id pretium aliquet.', '2025-06-06 12:55:23', '2025-06-06 12:55:23'),
(86, 2, 'Post Title 86', 'This is the body content for post number 86. Etiam leo sapien, dictum non turpis eu, egestas lobortis ex.', '2025-06-05 12:55:23', '2025-06-05 12:55:23'),
(87, 1, 'Post Title 87', 'This is the body content for post number 87. Suspendisse potenti. Sed vel neque nec est.', '2025-06-04 12:55:23', '2025-06-04 12:55:23'),
(88, 2, 'Post Title 88', 'This is the body content for post number 88. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.', '2025-06-03 12:55:23', '2025-06-03 12:55:23'),
(89, 1, 'Post Title 89', 'This is the body content for post number 89. Sed nisi. Nulla quis sem at nibh elementum imperdiet.', '2025-06-02 12:55:23', '2025-06-02 12:55:23'),
(90, 2, 'Post Title 90', 'This is the body content for post number 90. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed.', '2025-06-01 12:55:23', '2025-06-01 12:55:23'),
(91, 1, 'Post Title 91', 'This is the body content for post number 91. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus.', '2025-05-31 12:55:23', '2025-05-31 12:55:23'),
(92, 2, 'Post Title 92', 'This is the body content for post number 92. Praesent id metus massa. Class aptent taciti sociosqu ad litora torquent per conubia nostra.', '2025-05-30 12:55:23', '2025-05-30 12:55:23'),
(93, 1, 'Post Title 93', 'This is the body content for post number 93. In hac habitasse platea dictumst. Curabitur at lacus ac velit ornare lobortis.', '2025-05-29 12:55:23', '2025-05-29 12:55:23'),
(94, 2, 'Post Title 94', 'This is the body content for post number 94. Nunc vitae scelerisque ipsum. Sed non est.', '2025-05-28 12:55:23', '2025-05-28 12:55:23'),
(95, 1, 'Post Title 95', 'This is the body content for post number 95. Donec vel egestas dolor. Cras consequat, nunc id pretium aliquet.', '2025-05-27 12:55:23', '2025-05-27 12:55:23'),
(96, 2, 'Post Title 96', 'This is the body content for post number 96. Etiam leo sapien, dictum non turpis eu, egestas lobortis ex.', '2025-05-26 12:55:23', '2025-05-26 12:55:23'),
(97, 1, 'Post Title 97', 'This is the body content for post number 97. Suspendisse potenti. Sed vel neque nec est.', '2025-05-25 12:55:23', '2025-05-25 12:55:23'),
(98, 2, 'Post Title 98', 'This is the body content for post number 98. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.', '2025-05-24 12:55:23', '2025-05-24 12:55:23'),
(99, 1, 'Post Title 99', 'This is the body content for post number 99. Sed nisi. Nulla quis sem at nibh elementum imperdiet.', '2025-05-23 12:55:23', '2025-05-23 12:55:23'),
(100, 2, 'Post Title 100', 'This is the body content for post number 100. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed.', '2025-05-22 12:55:23', '2025-05-22 12:55:23'),
(101, 1, 'aaaaa', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', '2025-10-06 20:44:45', '2025-10-06 20:44:45'),
(104, 1, 'جديدة (تجربة تعديل)', 'تجربة جديدة (تعديل)', '2025-10-18 05:48:57', '2025-10-18 09:42:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'muhammad', 'mohammed@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-09-22 11:02:27', '2025-10-18 09:03:43'),
(2, 'Ahmed', 'ahmed@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-09-22 11:02:27', '2025-09-22 11:02:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posts_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
