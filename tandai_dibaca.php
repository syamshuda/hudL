<?php
/**
 * File: tandai_dibaca.php
  * VERSI PERBAIKAN: Hanya untuk menandai materi telah dibaca, tanpa memberikan poin.
   */

   require_once 'config/koneksi.php';

   // Pastikan siswa sudah login
   if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'siswa') {
       exit();
       }

       // Pastikan id_materi dikirim
       if (isset($_POST['id_materi'])) {
           $id_siswa = $_SESSION['user_id'];
               $id_materi = intval($_POST['id_materi']);

                   // Gunakan INSERT IGNORE untuk mencegah error jika data sudah ada
                       $stmt = $koneksi->prepare("INSERT IGNORE INTO status_baca_materi (id_siswa, id_materi) VALUES (?, ?)");
                           $stmt->bind_param("ii", $id_siswa, $id_materi);
                               $stmt->execute();
                                   $stmt->close();
                                       
                                           // Kirim respons sukses kembali ke JavaScript
                                               echo json_encode(['status' => 'success']);
                                               }
                                               $koneksi->close();
                                               ?>