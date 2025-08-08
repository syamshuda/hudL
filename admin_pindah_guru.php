<?php
require_once 'config/koneksi.php';

// Keamanan: Pastikan admin yang sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
    header("Location: login.php");
        exit();
        }

        // Validasi input dari form
        if (isset($_POST['id_kelas']) && isset($_POST['id_guru_baru'])) {
            $id_kelas = intval($_POST['id_kelas']);
                $id_guru_baru = intval($_POST['id_guru_baru']);
                    $id_admin = $_SESSION['user_id'];

                        // Update guru pengampu di tabel kelas
                            $stmt = $koneksi->prepare("UPDATE kelas SET id_guru = ? WHERE id = ?");
                                $stmt->bind_param("ii", $id_guru_baru, $id_kelas);
                                    
                                        if ($stmt->execute()) {
                                                // Catat aksi ini ke dalam log
                                                        $aksi = "Admin (ID: $id_admin) mengubah guru pengampu untuk kelas ID: $id_kelas menjadi Guru ID: $id_guru_baru";
                                                                $stmt_log = $koneksi->prepare("INSERT INTO log_aktivitas (id_pengguna, aksi) VALUES (?, ?)");
                                                                        $stmt_log->bind_param("is", $id_admin, $aksi);
                                                                                $stmt_log->execute();
                                                                                        $stmt_log->close();
                                                                                                
                                                                                                        // Arahkan kembali ke halaman manajemen kelas dengan pesan sukses
                                                                                                                header("Location: admin_manajemen_kelas.php?status=sukses_pindah_guru");
                                                                                                                    } else {
                                                                                                                            // Arahkan kembali dengan pesan error (opsional)
                                                                                                                                    header("Location: admin_manajemen_kelas.php?status=gagal_pindah_guru");
                                                                                                                                        }
                                                                                                                                            
                                                                                                                                                $stmt->close();
                                                                                                                                                    $koneksi->close();
                                                                                                                                                        exit();

                                                                                                                                                        } else {
                                                                                                                                                            // Jika data tidak lengkap, kembalikan ke halaman sebelumnya
                                                                                                                                                                header("Location: admin_manajemen_kelas.php");
                                                                                                                                                                    exit();
                                                                                                                                                                    }
                                                                                                                                                                    ?>