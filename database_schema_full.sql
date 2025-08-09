-- Encoding dan mode standar
SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Tabel pengaturan aplikasi
CREATE TABLE IF NOT EXISTS pengaturan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_name VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pengaturan (setting_name, setting_value) VALUES
  ('nama_situs','Platform Belajar'),
  ('pendaftaran_dibuka','1')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- Tabel pengguna
CREATE TABLE IF NOT EXISTS pengguna (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_lengkap VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  no_hp VARCHAR(30) NULL,
  password VARCHAR(128) NOT NULL,
  peran ENUM('admin','guru','siswa','bk','wali_kelas','kurikulum') NOT NULL DEFAULT 'siswa',
  status_akun ENUM('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
  dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin default (email: admin@example.com, password: admin123)
INSERT INTO pengguna (nama_lengkap,email,no_hp,password,peran,status_akun)
VALUES ('Administrator','admin@example.com',NULL,SHA2('admin123',256),'admin','aktif')
ON DUPLICATE KEY UPDATE nama_lengkap=VALUES(nama_lengkap);

-- Tabel kelas
CREATE TABLE IF NOT EXISTS kelas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_guru INT NOT NULL,
  id_wali_kelas INT NULL,
  nama_kelas VARCHAR(150) NOT NULL,
  mata_pelajaran VARCHAR(150) NOT NULL,
  deskripsi TEXT NULL,
  kode_kelas VARCHAR(20) NOT NULL UNIQUE,
  tanggal_dibuat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (id_guru),
  INDEX (id_wali_kelas)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relasi pendaftaran siswa ke kelas
CREATE TABLE IF NOT EXISTS pendaftaran_kelas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT NOT NULL,
  id_kelas INT NOT NULL,
  dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_siswa_kelas (id_siswa, id_kelas),
  INDEX (id_kelas)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel materi (konten pembelajaran)
CREATE TABLE IF NOT EXISTS materi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_kelas INT NOT NULL,
  judul VARCHAR(200) NOT NULL,
  konten MEDIUMTEXT NULL,
  waktu_buka DATETIME NULL,
  urutan INT NULL,
  tanggal_dibuat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (id_kelas)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel soal/kuis
CREATE TABLE IF NOT EXISTS soal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_kelas INT NOT NULL,
  judul_soal VARCHAR(200) NOT NULL,
  deskripsi MEDIUMTEXT NULL,
  batas_waktu DATETIME NULL,
  mode_kuis VARCHAR(50) NULL,
  waktu_per_soal INT NULL,
  acak_soal TINYINT(1) NOT NULL DEFAULT 0,
  waktu_dibuat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  waktu_buka DATETIME NULL,
  urutan INT NULL,
  INDEX (id_kelas)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jawaban siswa untuk kuis
CREATE TABLE IF NOT EXISTS jawaban_siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_soal INT NOT NULL,
  id_siswa INT NOT NULL,
  jawaban MEDIUMTEXT NULL,
  waktu_submit DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (id_soal),
  INDEX (id_siswa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Penilaian (skor) kuis
CREATE TABLE IF NOT EXISTS penilaian (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_soal INT NOT NULL,
  id_siswa INT NOT NULL,
  nilai DECIMAL(5,2) NOT NULL,
  komentar TEXT NULL,
  UNIQUE KEY uniq_penilaian (id_soal, id_siswa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pertemuan kelas (jadwal)
CREATE TABLE IF NOT EXISTS pertemuan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_kelas INT NOT NULL,
  judul_pertemuan VARCHAR(200) NOT NULL,
  tanggal_pertemuan DATE NOT NULL,
  waktu_dibuat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (id_kelas),
  INDEX (tanggal_pertemuan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Absensi siswa per pertemuan
CREATE TABLE IF NOT EXISTS absensi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_pertemuan INT NOT NULL,
  id_siswa INT NOT NULL,
  status ENUM('Hadir','Sakit','Izin','Alfa') NOT NULL DEFAULT 'Alfa',
  UNIQUE KEY uniq_absen (id_pertemuan, id_siswa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pesan (DM)
CREATE TABLE IF NOT EXISTS pesan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_pengirim INT NOT NULL,
  id_penerima INT NOT NULL,
  subjek VARCHAR(200) NULL,
  isi_pesan MEDIUMTEXT NOT NULL,
  waktu_kirim DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status_baca TINYINT(1) NOT NULL DEFAULT 0,
  id_balasan_untuk INT NULL,
  INDEX (id_pengirim),
  INDEX (id_penerima)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Kolaborasi (forum ringan)
CREATE TABLE IF NOT EXISTS kolaborasi_post (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_guru INT NOT NULL,
  judul VARCHAR(200) NOT NULL,
  isi MEDIUMTEXT NOT NULL,
  waktu_post DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kolaborasi_komentar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_post INT NOT NULL,
  id_pengirim INT NOT NULL,
  isi MEDIUMTEXT NOT NULL,
  waktu DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (id_post)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pengumuman (opsional, dipakai modul admin_pengumuman)
CREATE TABLE IF NOT EXISTS pengumuman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(200) NOT NULL,
  isi MEDIUMTEXT NOT NULL,
  dibuat_oleh INT NOT NULL,
  dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tambahan peran BK/Wali/Kurikulum
-- BK: Kasus dan sesi
CREATE TABLE IF NOT EXISTS bk_kasus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT NOT NULL,
  sumber_rujukan VARCHAR(50) NULL,
  kategori VARCHAR(100) NULL,
  prioritas ENUM('rendah','sedang','tinggi') NOT NULL DEFAULT 'sedang',
  status ENUM('baru','proses','selesai') NOT NULL DEFAULT 'baru',
  assigned_bk INT NULL,
  dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bk_sesi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_kasus INT NOT NULL,
  tanggal DATETIME NOT NULL,
  catatan MEDIUMTEXT NULL,
  lampiran VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pelanggaran dan aturan
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

-- Izin siswa dan orang tua
CREATE TABLE IF NOT EXISTS izin_siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT NOT NULL,
  tanggal DATE NOT NULL,
  jenis ENUM('Sakit','Izin') NOT NULL,
  bukti_url VARCHAR(255) NULL,
  status ENUM('diajukan','disetujui','ditolak') NOT NULL DEFAULT 'diajukan',
  disetujui_oleh INT NULL,
  dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
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
  deskripsi MEDIUMTEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tujuan_pembelajaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cp_id INT NOT NULL,
  kode VARCHAR(50) NOT NULL,
  deskripsi MEDIUMTEXT NOT NULL
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
  status ENUM('draft','menunggu','disetujui','revisi') NOT NULL DEFAULT 'draft',
  catatan MEDIUMTEXT NULL,
  waktu DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rubrik (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  deskripsi MEDIUMTEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rubrik_item (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_rubrik INT NOT NULL,
  kriteria VARCHAR(255) NOT NULL,
  bobot DECIMAL(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indeks tambahan yang bermanfaat
CREATE INDEX IF NOT EXISTS idx_pengguna_peran ON pengguna (peran);
CREATE INDEX IF NOT EXISTS idx_kelas_kode ON kelas (kode_kelas);
CREATE INDEX IF NOT EXISTS idx_pesan_waktu ON pesan (waktu_kirim);

-- Catatan: Tambahkan FOREIGN KEY sesuai kebutuhan jika server mengizinkan, misalnya:
-- ALTER TABLE kelas ADD CONSTRAINT fk_kelas_guru FOREIGN KEY (id_guru) REFERENCES pengguna(id);
-- Untuk shared hosting, seringkali tanpa FK lebih aman agar impor tidak gagal.