<?php
// PASTIKAN INI ADALAH BARIS PERTAMA
require_once 'config/koneksi.php';

// Cek apakah pengguna sudah login atau belum
if (!isset($_SESSION['user_id'])) {
    // Jika BELUM login, langsung tampilkan halaman login.php
        // dan hentikan eksekusi skrip agar tidak melanjutkan ke bawah.
            include 'login.php';
                exit();
                }

                // Jika kode berlanjut ke sini, artinya pengguna SUDAH login.
                // Sekarang, kita arahkan ke dashboard yang sesuai berdasarkan perannya.

                $peran = $_SESSION['peran'];

                // Arahkan ke dashboard yang sesuai
                if ($peran == 'admin') {
                    include 'admin_dashboard.php';
                    } elseif ($peran == 'guru') {
                        include 'guru_dashboard.php';
                        } elseif ($peran == 'siswa') {
                            include 'siswa_dashboard.php';
                            } else {
                                // Jika peran tidak dikenali, logout saja untuk keamanan
                                    header("Location: logout.php");
                                        exit();
                                        }
                                        ?>