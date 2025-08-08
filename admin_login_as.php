<?php
require_once 'config/koneksi.php';

// Keamanan: Pastikan yang melakukan aksi adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
    header("Location: login.php");
        exit();
        }

        // Keamanan: Pastikan ada ID target
        if (!isset($_GET['id'])) {
            header("Location: admin_dashboard.php");
                exit();
                }

                $id_admin = $_SESSION['user_id'];
                $id_target = intval($_GET['id']);

                // Ambil data pengguna target
                $stmt = $koneksi->prepare("SELECT id, nama_lengkap, peran FROM pengguna WHERE id = ?");
                $stmt->bind_param("i", $id_target);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $target_user = $result->fetch_assoc();

                        // Simpan sesi admin yang asli
                            $_SESSION['admin_original_session'] = [
                                    'user_id' => $_SESSION['user_id'],
                                            'nama_lengkap' => $_SESSION['nama_lengkap'],
                                                    'peran' => $_SESSION['peran']
                                                        ];

                                                            // Ganti sesi saat ini dengan data pengguna target
                                                                $_SESSION['user_id'] = $target_user['id'];
                                                                    $_SESSION['nama_lengkap'] = $target_user['nama_lengkap'];
                                                                        $_SESSION['peran'] = $target_user['peran'];

                                                                            // Catat aksi ini ke log
                                                                                $aksi = "Admin (ID: $id_admin) masuk sebagai " . $target_user['nama_lengkap'] . " (ID: " . $target_user['id'] . ")";
                                                                                    $stmt_log = $koneksi->prepare("INSERT INTO log_aktivitas (id_pengguna, aksi) VALUES (?, ?)");
                                                                                        $stmt_log->bind_param("is", $id_admin, $aksi);
                                                                                            $stmt_log->execute();
                                                                                                $stmt_log->close();


                                                                                                    // Arahkan ke dasbor yang sesuai
                                                                                                        if ($target_user['peran'] == 'guru') {
                                                                                                                header("Location: guru_dashboard.php");
                                                                                                                    } else {
                                                                                                                            header("Location: siswa_dashboard.php");
                                                                                                                                }
                                                                                                                                    exit();

                                                                                                                                    } else {
                                                                                                                                        // Pengguna target tidak ditemukan
                                                                                                                                            header("Location: admin_dashboard.php?error=user_not_found");
                                                                                                                                                exit();
                                                                                                                                                }
                                                                                                                                                ?>