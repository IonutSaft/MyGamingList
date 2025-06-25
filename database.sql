--
-- Bază de date: `mygamelist`
--
CREATE DATABASE IF NOT EXISTS `mygamelist` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `mygamelist`;

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `comment`
--

DROP TABLE IF EXISTS `comment`;
CREATE TABLE IF NOT EXISTS `comment` (
  `comment_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `content` varchar(255) not null,
  `user_id` int not null,
  `post_id` int not null,
  `comment_date` datetime,
  FOREIGN KEY (user_id) REFERENCES `user`(user_id),
  FOREIGN KEY (post_id) REFERENCES `post`(post_id)
);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `follow`
--

DROP TABLE IF EXISTS `follow`;
CREATE TABLE IF NOT EXISTS `follow` (
  `follow_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `followed_user_id` int not null,
  `following_user_id` int not null,
  FOREIGN KEY (followed_user_id) REFERENCES `user`(user_id),
  FOREIGN KEY (following_user_id) REFERENCES `user`(user_id)
);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `game`
--

DROP TABLE IF EXISTS `game`;
CREATE TABLE IF NOT EXISTS `game` (
  `game_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) not null,
  `description` text,
  `publisher` varchar(100),
  `developer` varchar(100),
  `cover_url` varchar(255),
  `release_date` date,
  `rating` decimal(3,2)
  `created_at` timestamp,
  `unique_hash` binary(15)
);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `game_list`
--

DROP TABLE IF EXISTS `game_list`;
CREATE TABLE IF NOT EXISTS `game_list` (
  `game_list_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int not null,
  `game_id` int not null,
  `status` enum('Playing', 'Completed', 'Dropped', 'Plan to Play') not null,
  UNIQUE KEY `user_id` (`user_id`, `game_id`),
  FOREIGN KEY (game_id) REFERENCES `game`(game_id)
);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `like`
--

DROP TABLE IF EXISTS `like`;
CREATE TABLE IF NOT EXISTS `like` (
  `like_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int NOT NULL,
  `post_id` int default null,
  `comment_id` int default null,
  UNIQUE KEY `unique_like` (`user_id`, `post_id`),
  FOREIGN KEY (post_id) REFERENCES `post`(post_id),
  FOREIGN KEY (comment_id) REFERENCES `comment`(comment_id)
);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `message`
--

DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message` (
  `message_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `content` text NOT NULL,
  `sent_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `read_at` datetime DEFAULT NULL,
  `read_status` tinyint DEFAULT '0',
  FOREIGN KEY (sender_id) REFERENCES `user`(user_id),
  FOREIGN KEY (receiver_id) REFERENCES `user`(user_id)
);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `notification_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `actor_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `content` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  CONSTRAINT fK_actor FOREIGN KEY (actor_id) REFERENCES `user`(user_id)
  FOREIGN KEY (user_id) REFERENCES `user`(user_id)
);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `post`
--

DROP TABLE IF EXISTS `post`;
CREATE TABLE IF NOT EXISTS `post` (
  `post_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int NOT NULL,
  `text_content` varchar(500) NOT NULL,
  `media_content` varchar(255) DEFAULT NULL,
  `comment_count` int DEFAULT '0',
  `like_count` int DEFAULT '0',
  `post_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN key (user_id) REFERENCES `user`(user_id)
);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `post_tag`
--

DROP TABLE IF EXISTS `post_tag`;
CREATE TABLE IF NOT EXISTS `post_tag` (
  `post_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`),
  FOREIGN KEY (tag_id) REFERENCES `tag`(tag_id)
);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `tag`
--

DROP TABLE IF EXISTS `tag`;
CREATE TABLE IF NOT EXISTS `tag` (
  `tag_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  UNIQUE KEY `name` (`name`)
);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `token`
--

DROP TABLE IF EXISTS `token`;
CREATE TABLE IF NOT EXISTS `token` (
  `token_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  FOREIGN KEY (user_id) REFERENCES `user`(user_id)
);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(32) NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email_address` varchar(48) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '../assets/default/default_avatar.png',
  `cover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '../assets/default/default_cover.png',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
);

--
-- Indexuri pentru tabele eliminate
--

--
-- Indexuri pentru tabele `game`
--
ALTER TABLE `game` ADD FULLTEXT KEY `title` (`title`,`description`);
COMMIT;