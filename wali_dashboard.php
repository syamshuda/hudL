<?php
require_once 'config/koneksi.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['peran'] ?? '') !== 'wali_kelas') {
  header('Location: login.php');
  exit();
}
include_once 'templates/header_wali.php';
?>
<h1 class="mb-4">Dashboard Wali Kelas</h1>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body">
      <h5 class="card-title">Kelas Wali</h5>
      <p class="card-text">Ringkasan kelas wali akan ditambahkan.</p>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body">
      <h5 class="card-title">Kehadiran</h5>
      <p class="card-text">Placeholder.</p>
    </div></div>
  </div>
</div>
<?php include_once 'templates/footer.php'; ?>