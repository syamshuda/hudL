<?php
require_once 'config/koneksi.php';

// Keamanan: Pastikan ada sesi admin yang tersimpan
if (!isset($_SESSION['admin_original_session'])) {
    header("Location: login.php");
        exit();
        }

        // Ambil data admin dari sesi yang tersimpan
        $admin_session = $_SESSION['admin_original_session'];
        $id_admin = $admin_session['user_id'];
        $nama_pengguna_sebelumnya = $_SESSION['nama_lengkap'];
        $id_pengguna_sebelumnya = $_SESSION['user_id'];

        // Hancurkan sesi pengguna saat ini dan mulai ulang
        session_destroy();
        session_start();

        // Kembalikan data admin ke sesi utama
        $_SESSION['user_id'] = $admin_session['user_id'];
        $_SESSION['nama_lengkap'] = $admin_session['nama_lengkap'];
        $_SESSION['peran'] = $admin_session['peran'];

        // Catat aksi kembali ke log
        $aksi = "Admin (ID: $id_admin) kembali dari sesi " . $nama_pengguna_sebelumnya . " (ID: " . $id_pengguna_sebelumnya . ")";
        $stmt_log = $koneksi->prepare("INSERT INTO log_aktivitas (id_pengguna, aksi) VALUES (?, ?)");
        $stmt_log->bind_param("is", $id_admin, $aksi);
        $stmt_log->execute();
        $stmt_log->close();


        // Arahkan kembali ke dasbor admin
        header("Location: admin_dashboard.php?status=return_success");
        exit();
        ?>