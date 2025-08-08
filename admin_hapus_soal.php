<?php
/**
 * File: admin_hapus_soal.php
  * Aksi untuk Admin menghapus soal dari bank soal terpusat.
   */

   require_once 'config/koneksi.php';

   // Keamanan: Pastikan hanya ADMIN yang bisa akses
   if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
       header("Location: logout.php");
           exit();
           }

           // Validasi ID
           if (!isset($_GET['id'])) {
               header("Location: admin_bank_soal.php");
                   exit();
                   }
                   $id_soal = intval($_GET['id']);

                   // Admin bisa menghapus soal mana saja
                   // Untuk keamanan tambahan, kita akan menghapus relasi di kuis_soal terlebih dahulu
                   $koneksi->begin_transaction();
                   try {
                       // Hapus dari kuis_soal (jika soal ini dipakai di kuis)
                           $stmt_hapus_relasi = $koneksi->prepare("DELETE FROM kuis_soal WHERE id_bank_soal = ?");
                               $stmt_hapus_relasi->bind_param("i", $id_soal);
                                   $stmt_hapus_relasi->execute();
                                       $stmt_hapus_relasi->close();

                                           // Hapus dari bank_soal
                                               $stmt_hapus_soal = $koneksi->prepare("DELETE FROM bank_soal WHERE id = ?");
                                                   $stmt_hapus_soal->bind_param("i", $id_soal);
                                                       $stmt_hapus_soal->execute();
                                                           $stmt_hapus_soal->close();
                                                               
                                                                   $koneksi->commit();
                                                                       header("Location: admin_bank_soal.php?status=sukses_hapus");

                                                                       } catch (Exception $e) {
                                                                           $koneksi->rollback();
                                                                               header("Location: admin_bank_soal.php?status=gagal");
                                                                               }

                                                                               $koneksi->close();
                                                                               exit();
                                                                               ?>