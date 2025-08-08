<?php
require_once 'config/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
        exit();
        }

        // Pastikan ada ID pesan yang dikirim
        if (!isset($_GET['id'])) {
            header("Location: pesan_kotak_masuk.php");
                exit();
                }

                $id_pesan = intval($_GET['id']);
                $id_pengguna = $_SESSION['user_id'];

                // Siapkan perintah DELETE
                // Keamanan: Pastikan hanya PENGIRIM ASLI yang bisa menghapus pesan
                $stmt = $koneksi->prepare("DELETE FROM pesan WHERE id = ? AND id_pengirim = ?");
                $stmt->bind_param("ii", $id_pesan, $id_pengguna);

                // Eksekusi perintah
                if ($stmt->execute()) {
                    // Jika berhasil, kembalikan ke halaman kotak masuk dengan notifikasi sukses
                        header("Location: pesan_kotak_masuk.php?status=hapus_sukses");
                        } else {
                            // Jika gagal, kembalikan dengan notifikasi error (opsional)
                                header("Location: pesan_kotak_masuk.php?status=hapus_gagal");
                                }

                                $stmt->close();
                                $koneksi->close();
                                exit();
                                ?>