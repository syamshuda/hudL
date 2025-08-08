<?php
/**
 * File: admin_hapus_pengguna.php
 * Aksi untuk Admin menghapus akun pengguna (guru atau siswa) secara permanen.
 */

require_once 'config/koneksi.php';

// Keamanan: Pastikan hanya ADMIN yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
    header("Location: logout.php");
    exit();
}

// Validasi ID pengguna dari URL
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php?status=gagal_hapus");
    exit();
}

$id_pengguna_hapus = intval($_GET['id']);
$id_admin = $_SESSION['user_id'];

// Keamanan tambahan: Admin tidak bisa menghapus akunnya sendiri
if ($id_pengguna_hapus === $id_admin) {
    header("Location: admin_dashboard.php?status=gagal_hapus_diri");
    exit();
}

// Gunakan transaksi untuk memastikan semua data terkait terhapus
$koneksi->begin_transaction();

try {
    // Ambil peran pengguna sebelum dihapus untuk logging
    $stmt_get_user = $koneksi->prepare("SELECT nama_lengkap, peran FROM pengguna WHERE id = ?");
    $stmt_get_user->bind_param("i", $id_pengguna_hapus);
    $stmt_get_user->execute();
    $result_user = $stmt_get_user->get_result();
    $user_to_delete = $result_user->fetch_assoc();
    $stmt_get_user->close();

    if ($user_to_delete) {
        // Hapus data terkait terlebih dahulu (best practice)
        // Ini adalah contoh, Anda mungkin perlu menambahkan penghapusan dari tabel lain
        // seperti 'jawaban_siswa', 'penilaian', 'pendaftaran_kelas', 'status_baca_materi', dll.
        
        if ($user_to_delete['peran'] == 'siswa') {
            $koneksi->query("DELETE FROM pendaftaran_kelas WHERE id_siswa = $id_pengguna_hapus");
            $koneksi->query("DELETE FROM jawaban_siswa WHERE id_siswa = $id_pengguna_hapus");
            $koneksi->query("DELETE FROM penilaian WHERE id_siswa = $id_pengguna_hapus");
            $koneksi->query("DELETE FROM status_baca_materi WHERE id_siswa = $id_pengguna_hapus");
            $koneksi->query("DELETE FROM log_poin WHERE id_siswa = $id_pengguna_hapus");
            $koneksi->query("DELETE FROM poin_total WHERE id_siswa = $id_pengguna_hapus");
        } elseif ($user_to_delete['peran'] == 'guru') {
            // Jika seorang guru dihapus, kelas-kelasnya mungkin perlu ditangani
            // Opsi 1: Hapus kelas-kelasnya (berisiko kehilangan data siswa)
            // Opsi 2: Set id_guru di tabel kelas menjadi NULL atau ke admin default
            // Untuk saat ini, kita tidak akan menghapus kelasnya, hanya akun gurunya.
            // Anda bisa menambahkan logika ini jika diperlukan.
        }

        // Hapus pesan yang dikirim atau diterima oleh pengguna ini
        $koneksi->query("DELETE FROM pesan WHERE id_pengirim = $id_pengguna_hapus OR id_penerima = $id_pengguna_hapus");

        // Hapus pengguna utama
        $stmt_hapus = $koneksi->prepare("DELETE FROM pengguna WHERE id = ?");
        $stmt_hapus->bind_param("i", $id_pengguna_hapus);
        $stmt_hapus->execute();
        $stmt_hapus->close();

        // Catat aksi ke log
        $aksi = "Admin (ID: $id_admin) menghapus pengguna: " . $user_to_delete['nama_lengkap'] . " (ID: $id_pengguna_hapus)";
        $stmt_log = $koneksi->prepare("INSERT INTO log_aktivitas (id_pengguna, aksi) VALUES (?, ?)");
        $stmt_log->bind_param("is", $id_admin, $aksi);
        $stmt_log->execute();
        $stmt_log->close();

        // Jika semua berhasil, commit transaksi
        $koneksi->commit();
        header("Location: admin_dashboard.php?status=sukses_hapus");
    } else {
        // Pengguna tidak ditemukan, rollback
        $koneksi->rollback();
        header("Location: admin_dashboard.php?status=gagal_hapus");
    }

} catch (Exception $e) {
    // Jika ada error, rollback transaksi
    $koneksi->rollback();
    // Redirect dengan pesan error
    header("Location: admin_dashboard.php?status=gagal_hapus&error=" . urlencode($e->getMessage()));
}

$koneksi->close();
exit();
?>