<?php
require_once 'config/koneksi.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['peran'] ?? '') !== 'kurikulum') {
  header('Location: login.php');
  exit();
}
include_once 'templates/header_kurikulum.php';
?>
<h1 class="mb-4">Dashboard Kurikulum</h1>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body">
      <h5 class="card-title">Pemetaan Kurikulum</h5>
      <p class="card-text">Coverage map akan ditambahkan.</p>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card h-100"><div class="card-body">
      <h5 class="card-title">QA Bank Soal</h5>
      <p class="card-text">Workflow review akan ditambahkan.</p>
    </div></div>
  </div>
</div>
<?php include_once 'templates/footer.php'; ?>