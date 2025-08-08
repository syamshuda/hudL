<?php
// templates/header_siswa.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ambil pengaturan global jika belum ada
if (!isset($PENGATURAN)) {
    if (!isset($koneksi) || $koneksi->connect_error) {
        $temp_koneksi = new mysqli("localhost", "root", "", "tanda");
        if (!$temp_koneksi->connect_error) {
            $result = $temp_koneksi->query("SELECT setting_name, setting_value FROM pengaturan");
            $PENGATURAN = [];
            while ($row = $result->fetch_assoc()) {
                $PENGATURAN[$row['setting_name']] = $row['setting_value'];
            }
            $temp_koneksi->close();
        } else {
            $PENGATURAN['nama_situs'] = 'Platform Belajar';
        }
    } else {
        $result = $koneksi->query("SELECT setting_name, setting_value FROM pengaturan");
        $PENGATURAN = [];
        while ($row = $result->fetch_assoc()) {
            $PENGATURAN[$row['setting_name']] = $row['setting_value'];
        }
    }
}
$nama_situs = htmlspecialchars($PENGATURAN['nama_situs'] ?? 'Platform Belajar');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siswa - <?php echo $nama_situs; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="siswa_dashboard.php">
                <img src="assets/img/logo.png" alt="Logo" style="height: 30px; margin-right: 10px;">
                <?php echo $nama_situs; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="siswa_dashboard.php">Kelas Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="papan_peringkat.php">Papan Peringkat</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="rekapDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Rekapitulasi
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="rekapDropdown">
                            <li><a class="dropdown-item" href="siswa_rekap_absensi.php">Rekap Absensi</a></li>
                            <li><a class="dropdown-item" href="siswa_rekap_nilai.php">Rekap Nilai</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pesan_kotak_masuk.php">Pesan</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="siswa_profil.php">Profil Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
                 <?php if (isset($_SESSION['admin_original_session'])): ?>
                    <a href="admin_return.php" class="btn btn-warning ms-2">Kembali ke Admin</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container mt-4">