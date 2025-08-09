<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
$nama_situs = htmlspecialchars($PENGATURAN['nama_situs'] ?? 'Platform Belajar');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wali Kelas - <?php echo $nama_situs; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-info sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="wali_dashboard.php">
      <img src="assets/img/logo.png" alt="Logo" style="height:30px;margin-right:10px;">
      <?php echo $nama_situs; ?> (Wali)
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="wali_dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Kelas Wali</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Absensi</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Rekap</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Pengumuman</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? ''); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="admin_profil.php">Profil Saya</a></li>
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