
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `mata_praktikum` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_praktikum` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT,
  `kode_praktikum` VARCHAR(50) UNIQUE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data untuk tabel `mata_praktikum`
INSERT INTO `mata_praktikum` (`nama_praktikum`, `deskripsi`, `kode_praktikum`) VALUES
('Pemrograman Web Dasar', 'Mempelajari dasar-dasar pengembangan web menggunakan HTML, CSS, dan PHP.', 'PWD001'),
('Jaringan Komputer Lanjut', 'Membahas konsep dan implementasi jaringan komputer tingkat lanjut.', 'JKL002'),
('Basis Data Lanjutan', 'Pendalaman materi basis data, termasuk optimasi query dan administrasi database.', 'BDL003');

CREATE TABLE `registrasi_praktikum` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `praktikum_id` INT(11) NOT NULL,
  `status_registrasi` ENUM('terdaftar', 'selesai', 'dibatalkan') DEFAULT 'terdaftar',
  `tanggal_daftar` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_praktikum` (`user_id`, `praktikum_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`praktikum_id`) REFERENCES `mata_praktikum`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `modul_praktikum` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `praktikum_id` INT(11) NOT NULL,
  `judul_modul` VARCHAR(255) NOT NULL,
  `deskripsi_modul` TEXT,
  `file_materi` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`praktikum_id`) REFERENCES `mata_praktikum`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `laporan_praktikum` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `modul_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `file_laporan` VARCHAR(255) NOT NULL,
  `tanggal_unggah` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `nilai` INT(3) DEFAULT NULL,
  `feedback` TEXT,
  `status_laporan` ENUM('not_graded', 'graded') DEFAULT 'not_graded', -- Perubahan di sini!
  PRIMARY KEY (`id`),
  FOREIGN KEY (`modul_id`) REFERENCES `modul_praktikum`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
