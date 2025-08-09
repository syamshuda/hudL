-- Tambah kolom wali kelas pada tabel kelas
ALTER TABLE kelas ADD COLUMN id_wali_kelas INT NULL AFTER id_guru;

-- Tabel kasus BK
CREATE TABLE IF NOT EXISTS bk_kasus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT NOT NULL,
  sumber_rujukan VARCHAR(50) NULL,
  kategori VARCHAR(100) NULL,
  prioritas ENUM('rendah','sedang','tinggi') DEFAULT 'sedang',
  status ENUM('baru','proses','selesai') DEFAULT 'baru',
  assigned_bk INT NULL,
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bk_sesi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_kasus INT NOT NULL,
  tanggal DATETIME NOT NULL,
  catatan TEXT,
  lampiran VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pelanggaran siswa
CREATE TABLE IF NOT EXISTS aturan_pelanggaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jenis VARCHAR(150) NOT NULL,
  poin_default INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pelanggaran_siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT NOT NULL,
  jenis VARCHAR(150) NOT NULL,
  poin INT NOT NULL,
  tanggal DATE NOT NULL,
  id_pembuat INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Izin siswa dan data orang tua
CREATE TABLE IF NOT EXISTS izin_siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT NOT NULL,
  tanggal DATE NOT NULL,
  jenis ENUM('Sakit','Izin') NOT NULL,
  bukti_url VARCHAR(255) NULL,
  status ENUM('diajukan','disetujui','ditolak') DEFAULT 'diajukan',
  disetujui_oleh INT NULL,
  dibuat_pada DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orang_tua (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT NOT NULL,
  nama VARCHAR(150) NOT NULL,
  hubungan VARCHAR(50) NOT NULL,
  no_hp VARCHAR(30) NULL,
  email VARCHAR(150) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Struktur kurikulum
CREATE TABLE IF NOT EXISTS kurikulum (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  tahun_ajaran VARCHAR(20) NOT NULL,
  fase VARCHAR(20) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS capaian_pembelajaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kurikulum_id INT NOT NULL,
  kode VARCHAR(50) NOT NULL,
  deskripsi TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tujuan_pembelajaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cp_id INT NOT NULL,
  kode VARCHAR(50) NOT NULL,
  deskripsi TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS map_materi_tp (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_materi INT NOT NULL,
  id_tp INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS map_soal_tp (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_soal INT NOT NULL,
  id_tp INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Standar penilaian
CREATE TABLE IF NOT EXISTS kkm_mapel (
  id INT AUTO_INCREMENT PRIMARY KEY,
  mapel VARCHAR(100) NOT NULL,
  kelas_tingkat VARCHAR(20) NOT NULL,
  kkm DECIMAL(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS review_soal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_soal INT NOT NULL,
  reviewer_id INT NOT NULL,
  status ENUM('draft','menunggu','disetujui','revisi') DEFAULT 'draft',
  catatan TEXT NULL,
  waktu DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rubrik (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  deskripsi TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rubrik_item (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_rubrik INT NOT NULL,
  kriteria VARCHAR(255) NOT NULL,
  bobot DECIMAL(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;