<?php
/**
 * File: admin_edit_pengguna.php
 * Aksi untuk admin mengedit detail profil pengguna (guru/siswa).
 */

require_once 'config/koneksi.php';

// Keamanan: Pastikan hanya ADMIN yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
    header("Location: logout.php");
    exit();
}

// Pastikan form disubmit dengan metode POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_perubahan_profil'])) {
    
    // Validasi dan sanitasi input
    $id_pengguna_edit = intval($_POST['id_pengguna_edit']);
    $nama_lengkap_baru = sanitize($koneksi, $_POST['nama_lengkap_edit']);
    $email_baru = sanitize($koneksi, $_POST['email_edit']);
    $no_hp_baru = sanitize($koneksi, $_POST['no_hp_edit']); // no_hp ditambahkan

    // Validasi dasar agar tidak ada field yang kosong
    if (empty($id_pengguna_edit) || empty($nama_lengkap_baru) || empty($email_baru)) {
        header("Location: admin_dashboard.php?status=gagal_edit&error=datakosong");
        exit();
    }

    // Siapkan query update
    $stmt_update = $koneksi->prepare("UPDATE pengguna SET nama_lengkap = ?, email = ?, no_hp = ? WHERE id = ?");
    
    if ($stmt_update) {
        $stmt_update->bind_param("sssi", $nama_lengkap_baru, $email_baru, $no_hp_baru, $id_pengguna_edit);
        
        if ($stmt_update->execute()) {
            // Cek apakah ada baris yang terpengaruh untuk memastikan ada perubahan
            if ($stmt_update->affected_rows > 0) {
                header("Location: admin_dashboard.php?status=sukses_edit");
            } else {
                // Tidak ada perubahan, kembalikan dengan status 'tanpaperubahan'
                header("Location: admin_dashboard.php?status=tanpa_perubahan");
            }
        } else {
            // Gagal eksekusi (misal: email duplikat)
            header("Location: admin_dashboard.php?status=gagal_edit&error=dberror");
        }
        $stmt_update->close();
    } else {
        // Gagal mempersiapkan query
        header("Location: admin_dashboard.php?status=gagal_edit&error=prepare");
    }

} else {
    // Jika akses langsung ke file ini tanpa POST, redirect ke dashboard
    header("Location: admin_dashboard.php");
}

$koneksi->close();
exit();
?>