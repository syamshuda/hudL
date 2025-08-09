<?php
require_once 'config/koneksi.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['peran'] ?? '') !== 'bk') {
  header('Location: login.php');
  exit();
}
include_once 'templates/header_bk.php';
?>
<h1 class="mb-4">Dashboard BK</h1>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body">
      <h5 class="card-title">Kasus Aktif</h5>
      <p class="card-text">Modul kasus akan ditambahkan.</p>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body">
      <h5 class="card-title">Rujukan Baru</h5>
      <p class="card-text">Placeholder.</p>
    </div></div>
  </div>
</div>
<?php include_once 'templates/footer.php'; ?>