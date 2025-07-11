-- Mengatur mode SQL dan zona waktu untuk sesi ini
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Struktur dari tabel `genres`
-- Tabel ini menyimpan semua genre yang bisa dipilih.
--
CREATE TABLE `genres` (
  `id` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Isi data awal untuk tabel `genres`
--
INSERT INTO `genres` (`id`, `nama`) VALUES
(1, 'Action'),
(2, 'Comedy'),
(3, 'Horror'),
(4, 'Thriller'),
(5, 'Sci-Fi'),
(6, 'Drama'),
(7, 'Romance');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
-- Menyimpan data semua pengguna, termasuk peran (role) mereka.
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `profile_picture` varchar(255) DEFAULT NULL,
  `waktu_pembuatan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `videos`
-- Tabel utama yang menyimpan semua data film/video.
--
CREATE TABLE `videos` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `thumbnail_path` varchar(255) NOT NULL,
  `duration` varchar(20) NOT NULL,
  `release_year` int(4) DEFAULT 2024,
  `genre_id` int(11) DEFAULT NULL,
  `director` varchar(100) DEFAULT NULL,
  `cast` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `history`
-- Merekam setiap kali sebuah video ditonton untuk fitur analitik.
--
CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `terakhir_nonton` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Atur Primary Key dan Index untuk setiap tabel
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `genre_id` (`genre_id`);

ALTER TABLE `history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `video_id` (`video_id`);

--
-- Atur AUTO_INCREMENT untuk ID otomatis
--
ALTER TABLE `genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Atur Foreign Key (Constraint) untuk menghubungkan tabel
--
ALTER TABLE `videos`
  ADD CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `videos_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `history`
  ADD CONSTRAINT `history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `history_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;