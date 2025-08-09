<?php
require_once 'config/koneksi.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['peran'] ?? '') !== 'admin') {
  http_response_code(403);
  echo 'Akses ditolak';
  exit();
}

$dir = __DIR__ . '/migrations';
if (!is_dir($dir)) {
  echo "Tidak ada direktori migrations.";
  exit();
}

$files = glob($dir . '/*.sql');
sort($files);

$results = [];
foreach ($files as $file) {
  $sqlContent = file_get_contents($file);
  if ($sqlContent === false) {
    $results[] = [basename($file), 'GAGAL: tidak bisa membaca file'];
    continue;
  }
  // Pecah per pernyataan sederhana berdasarkan ;
  $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
  $okCount = 0; $failCount = 0; $errors = [];
  foreach ($statements as $stmt) {
    // Lewati komentar baris
    $lines = array_filter(array_map('trim', explode("\n", $stmt)), function($l){ return $l !== '' && !str_starts_with($l, '--');});
    if (empty($lines)) continue;
    $sql = implode("\n", $lines);
    if ($sql === '') continue;
    if ($koneksi->query($sql) === true) {
      $okCount++;
    } else {
      $failCount++;
      $errors[] = $koneksi->error;
    }
  }
  $results[] = [basename($file), "OK: $okCount, Gagal: $failCount", $errors];
}

?><!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Migrate</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <h1>Migrasi Database</h1>
  <p>Menjalankan file .sql di direktori migrations (khusus admin).</p>
  <table class="table table-bordered">
    <thead><tr><th>File</th><th>Ringkasan</th><th>Errors</th></tr></thead>
    <tbody>
      <?php foreach ($results as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row[0]); ?></td>
          <td><?php echo htmlspecialchars($row[1]); ?></td>
          <td><pre class="mb-0"><?php echo htmlspecialchars(implode("\n", $row[2] ?? [])); ?></pre></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>