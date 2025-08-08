<?php
require_once 'config/koneksi.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses data ini
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
    header('Content-Type: application/json');
        echo json_encode(['error' => 'Akses ditolak']);
            exit();
            }

            // Ambil semua pengguna dengan peran 'guru'
            $result = $koneksi->query("SELECT id, nama_lengkap FROM pengguna WHERE peran = 'guru' ORDER BY nama_lengkap ASC");

            $guru_list = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                        $guru_list[] = $row;
                            }
                            }

                            // Kembalikan data dalam format JSON
                            header('Content-Type: application/json');
                            echo json_encode($guru_list);

                            $koneksi->close();
                            ?>