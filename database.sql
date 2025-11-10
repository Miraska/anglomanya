-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 10, 2025 at 12:23 AM
-- Server version: 8.0.30
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `anglomaniya`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `course_id` int NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Для начинающих', 'Курсы английского языка для начинающих с нуля', 'beginners', '2025-10-28 16:26:10', '2025-10-28 16:26:10'),
(2, 'Бизнес английский', 'Деловой английский для карьеры и бизнеса', 'business', '2025-10-28 16:26:10', '2025-10-28 16:26:10'),
(3, 'Подготовка к экзаменам', 'Подготовка к ОГЭ, ЕГЭ, IELTS, TOEFL', 'exams', '2025-10-28 16:26:10', '2025-10-28 16:26:10'),
(4, 'Для средних', 'Курсы для продолжающих изучение английского', 'intermediate', '2025-10-28 16:26:10', '2025-10-28 16:26:10'),
(5, 'Для продвинутых', 'Продвинутые курсы английского языка', 'advanced', '2025-10-28 16:26:10', '2025-10-28 16:26:10');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `full_description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` decimal(3,2) DEFAULT '0.00',
  `is_popular` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `category_id`, `title`, `description`, `full_description`, `price`, `image`, `pdf_file`, `rating`, `is_popular`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Английский с нуля до Intermediate', 'Полный курс для начинающих с персональным куратором и разговорными клубами', 'f5effefefe', '1999.00', 'assets/media/images/products/1.png', NULL, '4.50', 1, 1, '2025-10-28 16:26:10', '2025-11-09 14:31:55'),
(2, 2, 'Деловая коммуникация и переговоры', 'Для карьерного роста: презентации, встречи, корпоративная переписка', 'Английский для карьеры: выступайте на встречах, проводите презентации и пишите письма как носитель.\n\nХватит нервничать перед международными коллами и тратить часы на одно письмо. Повысьте свою ценность на рынке труда и откройте двери к глобальным возможностям. Мы научим вас не просто грамматике, а деловой коммуникации, которая строит репутацию.\n\nПочему этот курс — ваше карьерное преимущество:\n\nВы будете звучать убедительно. Вы освоите структуры и лексику для эффектных презентаций, чтобы ваши идеи услышали и оценили.\n\nВы сэкономите время и нервы. Вы отработаете шаблоны и фразы для ведения продуктивных встреч и четкой корпоративной переписки.\n\nВы избежите неловких ошибок. Вы узнаете тонкости делового этикета и избежите оплошностей в общении с иностранными партнерами и коллегами.\n\n', '2490.00', 'assets/media/images/products/2.png', NULL, '3.00', 0, 1, '2025-10-28 16:26:10', '2025-11-09 12:53:43'),
(3, 3, 'Подготовка к ОГЭ с нуля', 'Гарантированная подготовка к ОГЭ с результатом и пробными тестами', 'Гарантированная подготовка к ОГЭ: Приди на экзамен уверенным в своем результате!\n\nХватит гадать, хватит зубрежки и паники из-за непонятных заданий. Мы знаем все «подводные камни» ОГЭ и научим тебя не просто решать, а понимать каждый предмет. Наша система подготовки — это твой кратчайший путь к высокому баллу без лишнего стресса.\n\nПочему наш курс — твоя уверенность на 100%:\n\nТы будешь знать свой реальный уровень. Регулярные пробные тесты в формате реального ОГЭ покажут твои сильные и слабые стороны. Ты придешь на экзамен без сюрпризов.\n\nТы перестанешь бояться. Мы разберем каждое задание по косточкам, дадим четкие алгоритмы и шаблоны для решения даже самых сложных задач.\n\nТы получишь нужный балл. Наша гарантия — это не пустые слова, а отработанная методика. Мы готовим до результата, потому что знаем: твой успех — это и наш успех.', '1199.00', 'assets/media/images/products/3.png', NULL, '4.00', 1, 1, '2025-10-28 16:26:10', '2025-11-09 13:35:30');

-- --------------------------------------------------------

--
-- Table structure for table `course_lessons`
--

CREATE TABLE `course_lessons` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `content_type` enum('video','text','pdf','quiz','audio','image','game','download') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` int DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `is_free` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `content_text` text COLLATE utf8mb4_unicode_ci,
  `additional_materials` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_lessons`
--

INSERT INTO `course_lessons` (`id`, `course_id`, `title`, `description`, `content_type`, `content_url`, `duration`, `sort_order`, `is_free`, `created_at`, `updated_at`, `content_text`, `additional_materials`) VALUES
(1, 1, 'Введение в английский язык', 'Основные понятия и алфавит', 'text', NULL, 30, 1, 0, '2025-10-31 11:18:33', '2025-10-31 11:18:33', NULL, NULL),
(2, 1, 'Приветствия и знакомства', 'Учимся представляться и знакомиться', 'video', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 45, 2, 0, '2025-10-31 11:18:33', '2025-10-31 11:18:33', NULL, NULL),
(3, 1, 'Базовые фразы и выражения', 'Повседневные выражения для общения', 'text', NULL, 40, 3, 0, '2025-10-31 11:18:33', '2025-10-31 11:18:33', NULL, NULL),
(4, 2, 'Деловая переписка', 'Правила ведения бизнес-переписки', 'text', NULL, 50, 1, 0, '2025-10-31 11:18:33', '2025-10-31 11:18:33', NULL, NULL),
(5, 2, 'Проведение презентаций', 'Как эффективно проводить презентации на английском', 'video', 'https://www.youtube.com/watch?v=8LIz3xvPJZQ', 60, 2, 0, '2025-10-31 11:18:33', '2025-11-08 08:11:46', NULL, NULL),
(6, 3, 'Структура ОГЭ по английскому', 'Обзор экзамена и критерии оценки', 'text', NULL, 35, 1, 0, '2025-10-31 11:18:33', '2025-10-31 11:18:33', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `payment_id`, `created_at`, `updated_at`) VALUES
(1, 2, '4489.00', 'pending', 'fake_payment_69048b2b380d4', '2025-10-31 10:10:51', '2025-10-31 10:10:51'),
(2, 2, '1999.00', 'pending', 'fake_payment_69048b7ed10bb', '2025-10-31 10:12:14', '2025-10-31 10:12:14'),
(3, 2, '2490.00', 'pending', 'fake_payment_69048c89cbceb', '2025-10-31 10:16:41', '2025-10-31 10:16:41'),
(4, 2, '1999.00', 'pending', 'fake_payment_69048d98b7b26', '2025-10-31 10:21:12', '2025-10-31 10:21:12'),
(5, 2, '1999.00', 'paid', 'fake_payment_690494e841e10', '2025-10-31 10:52:24', '2025-10-31 10:52:28'),
(6, 2, '2490.00', 'paid', 'fake_payment_6904970d99e38', '2025-10-31 11:01:33', '2025-10-31 11:01:37'),
(7, 2, '1199.00', 'paid', 'fake_payment_6904ac1e6767e', '2025-10-31 12:31:26', '2025-10-31 12:31:30'),
(8, 3, '1999.00', 'paid', 'fake_payment_6910a5a9f196d', '2025-11-09 14:31:05', '2025-11-09 14:31:10');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `course_id` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `course_id`, `price`, `created_at`) VALUES
(1, 1, 2, '2490.00', '2025-10-31 10:10:51'),
(2, 1, 1, '1999.00', '2025-10-31 10:10:51'),
(3, 2, 1, '1999.00', '2025-10-31 10:12:14'),
(4, 3, 2, '2490.00', '2025-10-31 10:16:41'),
(5, 4, 1, '1999.00', '2025-10-31 10:21:12'),
(6, 5, 1, '1999.00', '2025-10-31 10:52:24'),
(7, 6, 2, '2490.00', '2025-10-31 11:01:33'),
(8, 7, 3, '1199.00', '2025-10-31 12:31:26'),
(9, 8, 1, '1999.00', '2025-11-09 14:31:05');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `course_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `course_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, 4, 'Мне очент понравился этот курс!!!!', '2025-10-29 12:30:09'),
(2, 2, 2, 3, 'Привет!', '2025-10-30 09:26:57'),
(6, 3, 2, 4, 'eg5rg6rgr56ggr6g6r', '2025-11-09 13:42:00'),
(7, 1, 3, 5, 'Классный курс!', '2025-11-09 14:31:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('student','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'student',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `avatar`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin@anglomaniya.ru', 'admin123', 'Администратор', NULL, 'admin', '2025-10-28 16:26:10', '2025-10-28 16:30:14'),
(2, 'miraska.007@gmail.com', 'Miras2005', 'Мирас', 'uploads/avatars/69048804ed228.png', 'student', '2025-10-28 18:48:30', '2025-11-09 13:34:18'),
(3, 'ilabipov2000@gmail.com', '123456', 'TeSoro', 'uploads/avatars/690332bc78c45.jpg', 'student', '2025-10-30 09:32:31', '2025-10-30 09:41:16');

-- --------------------------------------------------------

--
-- Table structure for table `user_courses`
--

CREATE TABLE `user_courses` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `course_id` int NOT NULL,
  `purchased_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_courses`
--

INSERT INTO `user_courses` (`id`, `user_id`, `course_id`, `purchased_at`) VALUES
(3, 2, 1, '2025-10-31 10:52:28'),
(4, 2, 2, '2025-10-31 11:01:37'),
(5, 2, 3, '2025-10-31 12:31:30'),
(6, 3, 1, '2025-11-09 14:31:10');

-- --------------------------------------------------------

--
-- Table structure for table `user_lesson_progress`
--

CREATE TABLE `user_lesson_progress` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `lesson_id` int NOT NULL,
  `completed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_lesson_progress`
--

INSERT INTO `user_lesson_progress` (`id`, `user_id`, `lesson_id`, `completed_at`) VALUES
(1, 2, 4, '2025-10-31 11:23:57'),
(2, 2, 5, '2025-10-31 11:24:01'),
(3, 2, 1, '2025-11-08 08:06:28'),
(4, 2, 2, '2025-11-08 08:13:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_course` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_popular` (`is_popular`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `course_lessons`
--
ALTER TABLE `course_lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment` (`payment_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_course` (`course_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_course_review` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `user_courses`
--
ALTER TABLE `user_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_course` (`user_id`,`course_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_course` (`course_id`);

--
-- Indexes for table `user_lesson_progress`
--
ALTER TABLE `user_lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_lesson` (`user_id`,`lesson_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `course_lessons`
--
ALTER TABLE `course_lessons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_courses`
--
ALTER TABLE `user_courses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_lesson_progress`
--
ALTER TABLE `user_lesson_progress`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_lessons`
--
ALTER TABLE `course_lessons`
  ADD CONSTRAINT `course_lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_courses`
--
ALTER TABLE `user_courses`
  ADD CONSTRAINT `user_courses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_lesson_progress`
--
ALTER TABLE `user_lesson_progress`
  ADD CONSTRAINT `user_lesson_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_lesson_progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `course_lessons` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
