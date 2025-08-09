<?php
require_once 'config/koneksi.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['peran'] ?? '') !== 'admin') {
  header('Location: logout.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_pengguna = intval($_POST['id_pengguna'] ?? 0);
  $peran_baru = $_POST['peran_baru'] ?? '';

  $allowed = ['admin','guru','siswa','bk','wali_kelas','kurikulum'];
  if ($id_pengguna <= 0 || !in_array($peran_baru, $allowed, true)) {
    header('Location: admin_dashboard.php?status=gagal_edit&error=invalid_role');
    exit();
  }

  $stmt = $koneksi->prepare('UPDATE pengguna SET peran = ? WHERE id = ?');
  if ($stmt) {
    $stmt->bind_param('si', $peran_baru, $id_pengguna);
    if ($stmt->execute()) {
      header('Location: admin_dashboard.php?status=sukses_edit');
      exit();
    }
  }
  header('Location: admin_dashboard.php?status=gagal_edit&error=db');
  exit();
}

http_response_code(405);
echo 'Method Not Allowed';