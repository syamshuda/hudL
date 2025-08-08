<?php
/**
 * File: admin_settings.php
  * Halaman untuk Admin mengelola pengaturan umum situs.
   */

   require_once 'config/koneksi.php';

   // Keamanan: Pastikan hanya ADMIN yang bisa akses
   if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
       header("Location: logout.php");
           exit();
           }
           $id_admin = $_SESSION['user_id'];
           $pesan = '';

           // Logika untuk menyimpan perubahan
           if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_pengaturan'])) {
               $nama_situs_baru = sanitize($koneksi, $_POST['nama_situs']);
                   $pendaftaran_baru = intval($_POST['pendaftaran_dibuka']);

                       // Mulai transaksi untuk memastikan semua update berhasil
                           $koneksi->begin_transaction();
                               try {
                                       // Update Nama Situs
                                               $stmt_nama = $koneksi->prepare("UPDATE pengaturan SET setting_value = ? WHERE setting_name = 'nama_situs'");
                                                       $stmt_nama->bind_param("s", $nama_situs_baru);
                                                               $stmt_nama->execute();
                                                                       $stmt_nama->close();

                                                                               // Update Status Pendaftaran
                                                                                       $stmt_daftar = $koneksi->prepare("UPDATE pengaturan SET setting_value = ? WHERE setting_name = 'pendaftaran_dibuka'");
                                                                                               $stmt_daftar->bind_param("s", $pendaftaran_baru);
                                                                                                       $stmt_daftar->execute();
                                                                                                               $stmt_daftar->close();

                                                                                                                       $koneksi->commit();
                                                                                                                               $pesan = "<div class='alert alert-success'>Pengaturan berhasil disimpan. Halaman akan dimuat ulang.</div>";
                                                                                                                                       // Refresh halaman setelah 3 detik untuk memuat ulang pengaturan
                                                                                                                                               echo "<meta http-equiv='refresh' content='3'>";

                                                                                                                                                   } catch (Exception $e) {
                                                                                                                                                           $koneksi->rollback();
                                                                                                                                                                   $pesan = "<div class='alert alert-danger'>Gagal menyimpan pengaturan: " . $e->getMessage() . "</div>";
                                                                                                                                                                       }
                                                                                                                                                                       }

                                                                                                                                                                       // Ambil pengaturan terbaru dari database untuk ditampilkan di form
                                                                                                                                                                       // (Walaupun sudah ada di variabel global, kita panggil lagi untuk memastikan data paling update setelah POST)
                                                                                                                                                                       $pengaturan_terbaru = [];
                                                                                                                                                                       $result = $koneksi->query("SELECT setting_name, setting_value FROM pengaturan");
                                                                                                                                                                       while ($row = $result->fetch_assoc()) {
                                                                                                                                                                           $pengaturan_terbaru[$row['setting_name']] = $row['setting_value'];
                                                                                                                                                                           }


                                                                                                                                                                           include_once 'templates/header_admin.php';
                                                                                                                                                                           ?>

                                                                                                                                                                           <h1 class="mb-4"><i class="bi bi-gear-fill me-2"></i>Pengaturan Sistem</h1>
                                                                                                                                                                           <?php if(!empty($pesan)) echo $pesan; ?>

                                                                                                                                                                           <div class="card shadow-sm">
                                                                                                                                                                               <div class="card-body">
                                                                                                                                                                                       <form action="admin_settings.php" method="POST">
                                                                                                                                                                                                   <div class="mb-4">
                                                                                                                                                                                                                   <label for="nama_situs" class="form-label fw-bold">Nama Situs</label>
                                                                                                                                                                                                                                   <input type="text" class="form-control" id="nama_situs" name="nama_situs" value="<?php echo htmlspecialchars($pengaturan_terbaru['nama_situs']); ?>" required>
                                                                                                                                                                                                                                                   <small class="form-text text-muted">Nama ini akan muncul di judul halaman dan header.</small>
                                                                                                                                                                                                                                                               </div>

                                                                                                                                                                                                                                                                           <div class="mb-4">
                                                                                                                                                                                                                                                                                           <label for="pendaftaran_dibuka" class="form-label fw-bold">Pendaftaran Pengguna Baru</label>
                                                                                                                                                                                                                                                                                                           <select class="form-select" id="pendaftaran_dibuka" name="pendaftaran_dibuka">
                                                                                                                                                                                                                                                                                                                               <option value="1" <?php echo ($pengaturan_terbaru['pendaftaran_dibuka'] == 1) ? 'selected' : ''; ?>>Buka (Aktif)</option>
                                                                                                                                                                                                                                                                                                                                                   <option value="0" <?php echo ($pengaturan_terbaru['pendaftaran_dibuka'] == 0) ? 'selected' : ''; ?>>Tutup (Nonaktif)</option>
                                                                                                                                                                                                                                                                                                                                                                   </select>
                                                                                                                                                                                                                                                                                                                                                                                   <small class="form-text text-muted">Jika ditutup, halaman pendaftaran tidak akan bisa diakses.</small>
                                                                                                                                                                                                                                                                                                                                                                                               </div>

                                                                                                                                                                                                                                                                                                                                                                                                           <div class="d-flex justify-content-end">
                                                                                                                                                                                                                                                                                                                                                                                                                           <button type="submit" name="simpan_pengaturan" class="btn btn-primary">Simpan Pengaturan</button>
                                                                                                                                                                                                                                                                                                                                                                                                                                       </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                               </form>
                                                                                                                                                                                                                                                                                                                                                                                                                                                   </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                                   </div>

                                                                                                                                                                                                                                                                                                                                                                                                                                                   <?php 
                                                                                                                                                                                                                                                                                                                                                                                                                                                   $koneksi->close();
                                                                                                                                                                                                                                                                                                                                                                                                                                                   include_once 'templates/footer_admin.php'; 
                                                                                                                                                                                                                                                                                                                                                                                                                                                   ?>