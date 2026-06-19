CREATE DATABASE IF NOT EXISTS silk_swarakarna
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE silk_swarakarna;

-- Tabel Master 1: pasien
DROP TABLE IF EXISTS pemeriksaan;
DROP TABLE IF EXISTS layanan;
DROP TABLE IF EXISTS dokter;
DROP TABLE IF EXISTS pasien;

CREATE TABLE pasien (
  id_pasien VARCHAR(20) PRIMARY KEY COMMENT 'No Rekam Medis, format RM-XXX',
  nama_pasien VARCHAR(100) NOT NULL,
  tanggal_lahir DATE NOT NULL,
  no_hp VARCHAR(20) NOT NULL,
  alamat TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Master 2: dokter
CREATE TABLE dokter (
  id_dokter INT AUTO_INCREMENT PRIMARY KEY,
  nama_dokter VARCHAR(100) NOT NULL,
  spesialisasi VARCHAR(100) NOT NULL DEFAULT 'THT',
  no_izin_praktik VARCHAR(50) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Master 3: layanan
CREATE TABLE layanan (
  id_layanan INT AUTO_INCREMENT PRIMARY KEY,
  nama_layanan VARCHAR(100) NOT NULL,
  biaya INT NOT NULL COMMENT 'Harga dalam IDR',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Transaksi: pemeriksaan
CREATE TABLE pemeriksaan (
  id_periksa VARCHAR(20) PRIMARY KEY COMMENT 'No Transaksi, format TRX-YYYYNNN',
  id_pasien VARCHAR(20) NOT NULL,
  id_dokter INT NOT NULL,
  id_layanan INT NOT NULL,
  tanggal_periksa DATE NOT NULL,
  keluhan TEXT NOT NULL,
  status_pemeriksaan ENUM('Menunggu', 'Sedang Diperiksa', 'Selesai') NOT NULL DEFAULT 'Menunggu',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_pasien) REFERENCES pasien(id_pasien) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (id_dokter) REFERENCES dokter(id_dokter) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (id_layanan) REFERENCES layanan(id_layanan) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_tanggal (tanggal_periksa),
  INDEX idx_status (status_pemeriksaan)
) ENGINE=InnoDB;

-- Seed: contoh data master (3 dokter, 4 layanan, 3 pasien)
INSERT INTO dokter (nama_dokter, spesialisasi, no_izin_praktik) VALUES
  ('dr. Sari Wijaya, Sp.THT', 'THT', 'SIP-001/2024'),
  ('dr. Budi Santoso, Sp.THT-KL', 'THT - Klinik Pendengaran', 'SIP-002/2024'),
  ('dr. Ani Lestari, Sp.THT', 'THT', 'SIP-003/2024');

INSERT INTO layanan (nama_layanan, biaya) VALUES
  ('Audiometri', 250000),
  ('OAE (Otoacoustic Emission)', 350000),
  ('BERA (Brainstem Evoked Response Audiometry)', 750000),
  ('Timpanometri', 200000);

INSERT INTO pasien (id_pasien, nama_pasien, tanggal_lahir, no_hp, alamat) VALUES
  ('RM-001', 'Andi Pratama', '1990-05-15', '081234567890', 'Jl. Sudirman No. 45, Denpasar'),
  ('RM-002', 'Siti Aminah', '1985-11-22', '082345678901', 'Jl. Tukad Badung No. 12, Denpasar'),
  ('RM-003', 'I Wayan Surya', '2010-03-08', '083456789012', 'Jl. Gatot Subroto No. 88, Denpasar');
