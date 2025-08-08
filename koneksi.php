<?php
/**
 * File: config/koneksi.php
  * Versi Penuh dan Benar untuk Produksi (Menyembunyikan Pesan Error)
   * Fungsi: Memulai sesi, mengatur penanganan error, dan membuat koneksi ke database.
    * File ini WAJIB dipanggil di baris paling atas pada setiap halaman.
     */

     // =================================================================//
     // === PENGATURAN ERROR UNTUK LINGKUNGAN PRODUKSI (LIVE) === //
     // =================================================================//
     // Matikan tampilan error ke pengguna
     ini_set('display_errors', 0);
     ini_set('display_startup_errors', 0);
     // Laporkan semua jenis error
     error_reporting(E_ALL);
     // Catat (log) semua error ke sebuah file bernama 'error_log.txt' di direktori yang sama
     ini_set('log_errors', 1);
     ini_set('error_log', 'error_log.txt');


     // 1. Memulai sesi
     // Ini harus menjadi baris pertama agar semua variabel $_SESSION bisa digunakan.
     if (session_status() == PHP_SESSION_NONE) {
         session_start();
         }


         // 2. Informasi koneksi database
         $hostname = 'sql105.infinityfree.com';
         $username = 'if0_39473769';
         $password = 'Kenanga004';
         $database = 'if0_39473769_lms';

         // 3. Membuat koneksi
         $koneksi = new mysqli($hostname, $username, $password, $database);

         // 4. Cek koneksi
         if ($koneksi->connect_error) {
             // Tulis ke log jika koneksi gagal, lalu hentikan aplikasi
                 error_log("Koneksi Gagal: " . $koneksi->connect_error);
                     // Tampilkan pesan yang ramah untuk pengguna
                         die("Sistem sedang dalam perbaikan. Silakan coba lagi nanti.");
                         }


                         // 5. Memuat Pengaturan Global
                         $PENGATURAN = [];
                         $result_pengaturan = $koneksi->query("SELECT setting_name, setting_value FROM pengaturan");
                         if ($result_pengaturan) {
                             while ($row = $result_pengaturan->fetch_assoc()) {
                                     $PENGATURAN[$row['setting_name']] = $row['setting_value'];
                                         }
                                         }


                                         // 6. Fungsi bantuan untuk membersihkan input (opsional tapi sangat disarankan)
                                         function sanitize($koneksi, $data) {
                                             return mysqli_real_escape_string($koneksi, htmlspecialchars(strip_tags($data)));
                                             }
                                             ?>