-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 03.12.2025 klo 11:06
-- Palvelimen versio: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `taitter`
--

-- --------------------------------------------------------

--
-- Rakenne taululle `followed_hashtags`
--

CREATE TABLE `followed_hashtags` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hashtag_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `follows`
--

CREATE TABLE `follows` (
  `follow_id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `follows`
--

INSERT INTO `follows` (`follow_id`, `follower_id`, `following_id`, `created_at`) VALUES
(1, 1, 2, '2025-12-02 08:49:54'),
(2, 2, 1, '2025-12-02 08:49:54'),
(3, 3, 1, '2025-12-02 08:49:54');

-- --------------------------------------------------------

--
-- Rakenne taululle `hashtags`
--

CREATE TABLE `hashtags` (
  `hashtag_id` int(11) NOT NULL,
  `tag_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `hashtags`
--

INSERT INTO `hashtags` (`hashtag_id`, `tag_name`) VALUES
(1, 'aloitus'),
(3, 'kahvi'),
(4, 'musiikki'),
(2, 'ohjelmointi');

-- --------------------------------------------------------

--
-- Rakenne taululle `mentions`
--

CREATE TABLE `mentions` (
  `mention_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `mentioned_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` varchar(144) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `posts`
--

INSERT INTO `posts` (`post_id`, `user_id`, `content`, `created_at`) VALUES
(1, 1, 'Moi kaikki! Tämä on ensimmäinen viestini #aloitus', '2025-12-02 08:49:54'),
(2, 1, 'Koodausta ja kahvia #ohjelmointi #kahvi', '2025-12-02 08:49:54'),
(3, 2, 'Rakkautta musiikkiin! @matti tule kuuntelemaan #musiikki', '2025-12-02 08:49:54'),
(5, 4, 'Testiä #ohjelmointi', '2025-12-03 10:01:08');

-- --------------------------------------------------------

--
-- Rakenne taululle `post_hashtags`
--

CREATE TABLE `post_hashtags` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `hashtag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `post_hashtags`
--

INSERT INTO `post_hashtags` (`id`, `post_id`, `hashtag_id`) VALUES
(2, 1, 1),
(3, 2, 2),
(4, 2, 3),
(5, 3, 4),
(1, 5, 2);

-- --------------------------------------------------------

--
-- Rakenne taululle `reposts`
--

CREATE TABLE `reposts` (
  `repost_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_picture_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vedos taulusta `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `bio`, `profile_picture_url`, `created_at`) VALUES
(1, 'matti', 'matti@example.com', 'hash123', 'Koodari Salosta', NULL, '2025-12-02 08:49:54'),
(2, 'liisa', 'liisa@example.com', 'hash456', 'Musiikin ystävä', NULL, '2025-12-02 08:49:54'),
(3, 'pekka', 'pekka@example.com', 'hash789', 'Ruoka ja matkailu', NULL, '2025-12-02 08:49:54'),
(4, 'Vesku', 'weex77@gmail.com', '$2y$10$mXuS2yfV6awrmP5cmNZr0eDjreJemJoKClDqdXidhRuguNsyGr8sC', NULL, NULL, '2025-12-03 10:00:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `followed_hashtags`
--
ALTER TABLE `followed_hashtags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`hashtag_id`),
  ADD KEY `hashtag_id` (`hashtag_id`);

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`follow_id`),
  ADD UNIQUE KEY `follower_id` (`follower_id`,`following_id`),
  ADD KEY `idx_follows_follower` (`follower_id`),
  ADD KEY `idx_follows_following` (`following_id`);

--
-- Indexes for table `hashtags`
--
ALTER TABLE `hashtags`
  ADD PRIMARY KEY (`hashtag_id`),
  ADD UNIQUE KEY `tag_name` (`tag_name`),
  ADD KEY `idx_hashtags_tag_name` (`tag_name`);

--
-- Indexes for table `mentions`
--
ALTER TABLE `mentions`
  ADD PRIMARY KEY (`mention_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `idx_mentions_user` (`mentioned_user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `idx_posts_user_id` (`user_id`),
  ADD KEY `idx_posts_created_at` (`created_at`);

--
-- Indexes for table `post_hashtags`
--
ALTER TABLE `post_hashtags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_id` (`post_id`,`hashtag_id`),
  ADD KEY `hashtag_id` (`hashtag_id`);

--
-- Indexes for table `reposts`
--
ALTER TABLE `reposts`
  ADD PRIMARY KEY (`repost_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `followed_hashtags`
--
ALTER TABLE `followed_hashtags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
  MODIFY `follow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hashtags`
--
ALTER TABLE `hashtags`
  MODIFY `hashtag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mentions`
--
ALTER TABLE `mentions`
  MODIFY `mention_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `post_hashtags`
--
ALTER TABLE `post_hashtags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reposts`
--
ALTER TABLE `reposts`
  MODIFY `repost_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Rajoitteet vedostauluille
--

--
-- Rajoitteet taululle `followed_hashtags`
--
ALTER TABLE `followed_hashtags`
  ADD CONSTRAINT `followed_hashtags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `followed_hashtags_ibfk_2` FOREIGN KEY (`hashtag_id`) REFERENCES `hashtags` (`hashtag_id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `mentions`
--
ALTER TABLE `mentions`
  ADD CONSTRAINT `mentions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mentions_ibfk_2` FOREIGN KEY (`mentioned_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `post_hashtags`
--
ALTER TABLE `post_hashtags`
  ADD CONSTRAINT `post_hashtags_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_hashtags_ibfk_2` FOREIGN KEY (`hashtag_id`) REFERENCES `hashtags` (`hashtag_id`) ON DELETE CASCADE;

--
-- Rajoitteet taululle `reposts`
--
ALTER TABLE `reposts`
  ADD CONSTRAINT `reposts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reposts_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
