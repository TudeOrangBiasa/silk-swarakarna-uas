-- SILK-Swarakarna schema + seed
-- MariaDB 10.11.16, InnoDB, utf8mb4_unicode_ci
-- 4 tables: pasien, dokter, layanan, pemeriksaan
-- 3 master + 1 transaksi, FK ON DELETE RESTRICT

CREATE DATABASE IF NOT EXISTS silk_swarakarna
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE silk_swarakarna;

-- Drop in dependency order (children first)
DROP TABLE IF EXISTS pemeriksaan;
DROP TABLE IF EXISTS layanan;
DROP TABLE IF EXISTS dokter;
DROP TABLE IF EXISTS pasien;

-- =====================================================================
-- Master 1: pasien
-- PK: id_pasien = No Rekam Medis (RM-XXX), human-readable
-- =====================================================================
CREATE TABLE IF NOT EXISTS pasien (
  id_pasien VARCHAR(20) NOT NULL
    COMMENT 'No Rekam Medis, format RM-XXX, auto-generated',
  nama_pasien VARCHAR(100) NOT NULL,
  tanggal_lahir DATE NOT NULL,
  no_hp VARCHAR(20) NOT NULL
    COMMENT 'Nomor HP, format 08xxxxxxxxxx',
  alamat TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_pasien),
  KEY idx_pasien_nama (nama_pasien),
  -- NOTE: CURDATE() not allowed in MariaDB CHECK; enforced by trigger below
  CONSTRAINT chk_pasien_tanggal_lahir CHECK (tanggal_lahir <= '2099-12-31')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Trigger: prevent future birth dates (CURDATE() non-deterministic, cannot be in CHECK)
--
DELIMITER //
CREATE TRIGGER trg_pasien_check_tanggal_lahir_bi
  BEFORE INSERT ON pasien
  FOR EACH ROW
BEGIN
  IF NEW.tanggal_lahir > CURDATE() THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'CHECK constraint chk_pasien_tanggal_lahir violated: tanggal_lahir cannot be in the future';
  END IF;
END//
CREATE TRIGGER trg_pasien_check_tanggal_lahir_bu
  BEFORE UPDATE ON pasien
  FOR EACH ROW
BEGIN
  IF NEW.tanggal_lahir > CURDATE() THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'CHECK constraint chk_pasien_tanggal_lahir violated: tanggal_lahir cannot be in the future';
  END IF;
END//
DELIMITER ;

-- =====================================================================
-- Master 2: dokter
-- PK: id_dokter (auto-increment), no_izin_praktik UNIQUE
-- =====================================================================
CREATE TABLE IF NOT EXISTS dokter (
  id_dokter INT UNSIGNED NOT NULL AUTO_INCREMENT
    COMMENT 'auto-increment PK',
  nama_dokter VARCHAR(100) NOT NULL,
  spesialisasi VARCHAR(100) NOT NULL DEFAULT 'THT',
  no_izin_praktik VARCHAR(50) NOT NULL
    COMMENT 'Surat Izin Praktik, UNIQUE',
  no_hp VARCHAR(20) NULL
    COMMENT 'optional',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_dokter),
  UNIQUE KEY uk_dokter_no_izin (no_izin_praktik),
  KEY idx_dokter_nama (nama_dokter),
  KEY idx_dokter_spesialisasi (spesialisasi),
  CONSTRAINT chk_dokter_spesialisasi CHECK (spesialisasi <> '')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- Master 3: layanan
-- PK: id_layanan (auto-increment), biaya INT (IDR, no fractional)
-- =====================================================================
CREATE TABLE IF NOT EXISTS layanan (
  id_layanan INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_layanan VARCHAR(100) NOT NULL,
  biaya INT UNSIGNED NOT NULL
    COMMENT 'Harga dalam IDR (integer, no fractional rupiah)',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_layanan),
  KEY idx_layanan_nama (nama_layanan),
  CONSTRAINT chk_layanan_biaya CHECK (biaya > 0),
  CONSTRAINT chk_layanan_nama CHECK (nama_layanan <> '')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- Transaksi: pemeriksaan
-- PK: id_periksa = No Transaksi (TRX-YYYYNNN), FK to 3 master
-- =====================================================================
CREATE TABLE IF NOT EXISTS pemeriksaan (
  id_periksa VARCHAR(20) NOT NULL
    COMMENT 'No Transaksi, format TRX-YYYYNNN, auto-generated per year',
  id_pasien VARCHAR(20) NOT NULL,
  id_dokter INT UNSIGNED NOT NULL,
  id_layanan INT UNSIGNED NOT NULL,
  tanggal_periksa DATE NOT NULL,
  keluhan TEXT NOT NULL,
  status_pemeriksaan ENUM('Menunggu', 'Sedang Diperiksa', 'Selesai')
    NOT NULL DEFAULT 'Menunggu',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id_periksa),
  KEY idx_periksa_tanggal (tanggal_periksa),
  KEY idx_periksa_status (status_pemeriksaan),
  KEY idx_periksa_status_created (status_pemeriksaan, created_at),
  KEY idx_periksa_pasien (id_pasien),
  KEY idx_periksa_dokter (id_dokter),
  KEY idx_periksa_layanan (id_layanan),
  CONSTRAINT fk_periksa_pasien
    FOREIGN KEY (id_pasien) REFERENCES pasien(id_pasien)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_periksa_dokter
    FOREIGN KEY (id_dokter) REFERENCES dokter(id_dokter)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_periksa_layanan
    FOREIGN KEY (id_layanan) REFERENCES layanan(id_layanan)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  -- NOTE: upper bound CURDATE()+1Y not allowed in MariaDB CHECK; enforced by trigger below
  CONSTRAINT chk_periksa_tanggal_min CHECK (tanggal_periksa >= '2020-01-01'),
  CONSTRAINT chk_periksa_keluhan CHECK (CHAR_LENGTH(keluhan) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Trigger: prevent future pemeriksaan dates beyond 1 year (non-deterministic, cannot be in CHECK)
--
DELIMITER //
CREATE TRIGGER trg_periksa_check_tanggal_bi
  BEFORE INSERT ON pemeriksaan
  FOR EACH ROW
BEGIN
  IF NEW.tanggal_periksa > DATE_ADD(CURDATE(), INTERVAL 1 YEAR) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'CHECK constraint chk_periksa_tanggal violated: tanggal_periksa cannot be more than 1 year in the future';
  END IF;
END//
CREATE TRIGGER trg_periksa_check_tanggal_bu
  BEFORE UPDATE ON pemeriksaan
  FOR EACH ROW
BEGIN
  IF NEW.tanggal_periksa > DATE_ADD(CURDATE(), INTERVAL 1 YEAR) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'CHECK constraint chk_periksa_tanggal violated: tanggal_periksa cannot be more than 1 year in the future';
  END IF;
END//
DELIMITER ;

-- =====================================================================
-- Seed data
-- =====================================================================

-- 2 dokter (THT specialist)
INSERT INTO dokter (nama_dokter, spesialisasi, no_izin_praktik, no_hp) VALUES
  ('dr. Sari Wijaya, Sp.THT', 'THT', 'SIP-001/2024', '081234567001'),
  ('dr. Budi Santoso, Sp.THT-KL', 'THT - Klinik Pendengaran', 'SIP-002/2024', '081234567002');

-- 3 layanan (Audiometri, OAE, BERA)
INSERT INTO layanan (nama_layanan, biaya) VALUES
  ('Audiometri', 250000),
  ('OAE (Otoacoustic Emission)', 350000),
  ('BERA (Brainstem Evoked Response Audiometry)', 750000);

-- 3 pasien
INSERT INTO pasien (id_pasien, nama_pasien, tanggal_lahir, no_hp, alamat) VALUES
  ('RM-001', 'Andi Pratama', '1990-05-15', '081234567890', 'Jl. Sudirman No. 45, Denpasar'),
  ('RM-002', 'Siti Aminah', '1985-11-22', '082345678901', 'Jl. Tukad Badung No. 12, Denpasar'),
  ('RM-003', 'I Wayan Surya', '2010-03-08', '083456789012', 'Jl. Gatot Subroto No. 88, Denpasar');

-- 2 pemeriksaan with different statuses
INSERT INTO pemeriksaan (id_periksa, id_pasien, id_dokter, id_layanan, tanggal_periksa, keluhan, status_pemeriksaan) VALUES
  ('TRX-2026001', 'RM-001', 1, 1, '2026-06-18', 'Tes pendengaran rutin, telinga kiri terasa berkurang', 'Selesai'),
  ('TRX-2026002', 'RM-002', 2, 3, '2026-06-19', 'Keluhan vertigo, perlu BERA untuk konfirmasi neurologis', 'Sedang Diperiksa');
