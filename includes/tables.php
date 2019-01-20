<?php
$filesTable = "CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `file_id` varchar(200) NOT NULL UNIQUE KEY,
  `file_name` varchar(20) NOT NULL UNIQUE KEY,
  `file_dir` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` float NOT NULL,
  `user_id` int(11) NOT NULL,
  `message_id` varchar(500) NOT NULL UNIQUE KEY,
  `date` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$usersTable = "CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `chat_id` int(11) NOT NULL UNIQUE KEY,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `files` int(11) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";