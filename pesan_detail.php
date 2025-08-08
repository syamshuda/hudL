<?php
require_once 'config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
        exit();
        }
        $id_pengguna = $_SESSION['user_id'];
        $peran_pengguna = $_SESSION['peran'];

        if (!isset($_GET['id'])) {
            header("Location: pesan_kotak_masuk.php");
                exit();
                }
                $id_pesan = intval($_GET['id']);

                // Ambil detail pesan, pastikan pengguna ini adalah PENERIMA atau PENGIRIM
                $stmt_pesan = $koneksi->prepare("
                    SELECT p.*, 
                               pengirim.nama_lengkap AS nama_pengirim, pengirim.peran AS peran_pengirim,
                                          penerima.nama_lengkap AS nama_penerima
                                              FROM pesan p
                                                  JOIN pengguna pengirim ON p.id_pengirim = pengirim.id
                                                      JOIN pengguna penerima ON p.id_penerima = penerima.id
                                                          WHERE p.id = ? AND (p.id_penerima = ? OR p.id_pengirim = ?)
                                                          ");
                                                          $stmt_pesan->bind_param("iii", $id_pesan, $id_pengguna, $id_pengguna);
                                                          $stmt_pesan->execute();
                                                          $result_pesan = $stmt_pesan->get_result();

                                                          if ($result_pesan->num_rows == 0) {
                                                              // Pesan tidak ditemukan atau pengguna tidak punya akses
                                                                  header("Location: pesan_kotak_masuk.php?status=not_found");
                                                                      exit();
                                                                      }
                                                                      $pesan = $result_pesan->fetch_assoc();

                                                                      // Jika pengguna adalah penerima, tandai pesan sebagai sudah dibaca
                                                                      if ($pesan['status_baca'] == 'belum_dibaca' && $pesan['id_penerima'] == $id_pengguna) {
                                                                          $koneksi->query("UPDATE pesan SET status_baca = 'sudah_dibaca' WHERE id = $id_pesan");
                                                                          }

                                                                          // Tentukan header
                                                                          $header_file = 'templates/header.php'; // Default Guru
                                                                          if ($peran_pengguna == 'admin') {
                                                                              $header_file = 'templates/header_admin.php';
                                                                              } elseif ($peran_pengguna == 'siswa') {
                                                                                  $header_file = 'templates/header_siswa.php';
                                                                                  }
                                                                                  include_once $header_file;

                                                                                  // Logika untuk menentukan apakah tombol balas muncul
                                                                                  $bisa_balas = false;
                                                                                  // Hanya penerima yang bisa membalas
                                                                                  if ($pesan['id_penerima'] == $id_pengguna) {
                                                                                      if ($peran_pengguna == 'guru' && ($pesan['peran_pengirim'] == 'guru' || $pesan['peran_pengirim'] == 'admin')) {
                                                                                              $bisa_balas = true; // Guru bisa balas guru & admin
                                                                                                  } elseif ($peran_pengguna == 'siswa' && $pesan['peran_pengirim'] == 'guru') {
                                                                                                          $bisa_balas = true; // Siswa bisa balas guru
                                                                                                              }
                                                                                                              }

                                                                                                              ?>
                                                                                                              <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

                                                                                                              <nav aria-label="breadcrumb">
                                                                                                                <ol class="breadcrumb">
                                                                                                                    <li class="breadcrumb-item"><a href="pesan_kotak_masuk.php">Pesan</a></li>
                                                                                                                        <li class="breadcrumb-item active" aria-current="page">Detail Pesan</li>
                                                                                                                          </ol>
                                                                                                                          </nav>

                                                                                                                          <div class="card">
                                                                                                                              <div class="card-header">
                                                                                                                                      <h4 class="mb-0"><?php echo htmlspecialchars($pesan['subjek']); ?></h4>
                                                                                                                                          </div>
                                                                                                                                              <div class="card-body">
                                                                                                                                                      <p class="text-muted">
                                                                                                                                                                  <strong>Dari:</strong> <?php echo htmlspecialchars($pesan['nama_pengirim']); ?><br>
                                                                                                                                                                              <strong>Kepada:</strong> <?php echo htmlspecialchars($pesan['nama_penerima']); ?><br>
                                                                                                                                                                                          <strong>Waktu:</strong> <?php echo date('l, d F Y H:i', strtotime($pesan['waktu_kirim'])); ?>
                                                                                                                                                                                                  </p>
                                                                                                                                                                                                          <hr>
                                                                                                                                                                                                                  <div class="isi-pesan">
                                                                                                                                                                                                                              <?php echo $pesan['isi_pesan']; // Konten dari CKEditor, aman untuk dirender ?>
                                                                                                                                                                                                                                      </div>
                                                                                                                                                                                                                                          </div>
                                                                                                                                                                                                                                          </div>

                                                                                                                                                                                                                                          <?php
                                                                                                                                                                                                                                          // Tampilkan form balasan jika kondisi terpenuhi
                                                                                                                                                                                                                                          if ($bisa_balas):
                                                                                                                                                                                                                                          ?>
                                                                                                                                                                                                                                          <hr>
                                                                                                                                                                                                                                          <h4><i class="bi bi-reply-fill me-2"></i>Balas Pesan</h4>
                                                                                                                                                                                                                                          <div class="card">
                                                                                                                                                                                                                                              <div class="card-body">
                                                                                                                                                                                                                                                      <form action="pesan_aksi.php" method="POST" id="form-balasan">
                                                                                                                                                                                                                                                                  <input type="hidden" name="penerima[]" value="<?php echo $pesan['peran_pengirim'].'-'.$pesan['id_pengirim']; ?>">
                                                                                                                                                                                                                                                                              <input type="hidden" name="subjek" value="Re: <?php echo htmlspecialchars($pesan['subjek']); ?>">
                                                                                                                                                                                                                                                                                          
                                                                                                                                                                                                                                                                                                      <div class="mb-3">
                                                                                                                                                                                                                                                                                                                      <label for="isi_balasan" class="form-label">Pesan Balasan Anda:</label>
                                                                                                                                                                                                                                                                                                                                      <textarea name="isi_pesan" id="isi_balasan_editor" class="form-control" rows="8"></textarea>
                                                                                                                                                                                                                                                                                                                                                  </div>
                                                                                                                                                                                                                                                                                                                                                              
                                                                                                                                                                                                                                                                                                                                                                          <div class="text-end">
                                                                                                                                                                                                                                                                                                                                                                                          <button type="submit" name="kirim_pesan" class="btn btn-primary">Kirim Balasan</button>
                                                                                                                                                                                                                                                                                                                                                                                                      </div>
                                                                                                                                                                                                                                                                                                                                                                                                              </form>
                                                                                                                                                                                                                                                                                                                                                                                                                  </div>
                                                                                                                                                                                                                                                                                                                                                                                                                  </div>

                                                                                                                                                                                                                                                                                                                                                                                                                  <script>
                                                                                                                                                                                                                                                                                                                                                                                                                      CKEDITOR.replace('isi_balasan_editor');
                                                                                                                                                                                                                                                                                                                                                                                                                          document.getElementById('form-balasan').addEventListener('submit', function() {
                                                                                                                                                                                                                                                                                                                                                                                                                                  CKEDITOR.instances.isi_balasan_editor.updateElement();
                                                                                                                                                                                                                                                                                                                                                                                                                                      });
                                                                                                                                                                                                                                                                                                                                                                                                                                      </script>

                                                                                                                                                                                                                                                                                                                                                                                                                                      <?php endif; ?>

                                                                                                                                                                                                                                                                                                                                                                                                                                      <?php
                                                                                                                                                                                                                                                                                                                                                                                                                                      $stmt_pesan->close();
                                                                                                                                                                                                                                                                                                                                                                                                                                      $koneksi->close();
                                                                                                                                                                                                                                                                                                                                                                                                                                      include_once 'templates/footer.php';
                                                                                                                                                                                                                                                                                                                                                                                                                                      ?>