<?php
require_once 'config/koneksi.php';

// Keamanan: Pastikan hanya admin
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
    // Jangan tampilkan error, cukup hentikan script
        exit();
        }

        // Atur error reporting untuk debugging jika diizinkan oleh server
        // error_reporting(E_ALL);
        // ini_set('display_errors', 1);

        $export_type = $_GET['export'] ?? '';
        $koneksi->set_charset("utf8"); // Pastikan charset benar

        if ($export_type == 'users') {
            $filename = "daftar_pengguna_" . date('Y-m-d') . ".csv";
                $headers = ['ID', 'Nama Lengkap', 'Email', 'Peran', 'Status Akun', 'Tanggal Daftar'];
                    $sql = "SELECT id, nama_lengkap, email, peran, status_akun, dibuat_pada FROM pengguna ORDER BY nama_lengkap";

                    } elseif ($export_type == 'classes') {
                        $filename = "daftar_kelas_" . date('Y-m-d') . ".csv";
                            $headers = ['ID Kelas', 'Nama Kelas', 'Mata Pelajaran', 'Nama Guru', 'Kode Kelas', 'Tanggal Dibuat'];
                                $sql = "
                                        SELECT k.id, k.nama_kelas, k.mata_pelajaran, u.nama_lengkap AS nama_guru, k.kode_kelas, k.tanggal_dibuat
                                                FROM kelas k
                                                        JOIN pengguna u ON k.id_guru = u.id
                                                                ORDER BY k.tanggal_dibuat DESC
                                                                    ";
                                                                    } else {
                                                                        die("Tipe ekspor tidak valid.");
                                                                        }

                                                                        // Atur header HTTP untuk file download CSV
                                                                        header('Content-Type: text/csv; charset=utf-8');
                                                                        header('Content-Disposition: attachment; filename="' . $filename . '"');
                                                                        header('Pragma: no-cache');
                                                                        header('Expires: 0');

                                                                        // Buka stream output PHP
                                                                        $output = fopen('php://output', 'w');

                                                                        // Tulis header ke file CSV
                                                                        fputcsv($output, $headers);

                                                                        // Eksekusi query dan tulis hasilnya baris per baris
                                                                        if ($result = $koneksi->query($sql)) {
                                                                            while ($row = $result->fetch_assoc()) {
                                                                                    fputcsv($output, $row);
                                                                                        }
                                                                                            $result->free();
                                                                                            }

                                                                                            $koneksi->close();
                                                                                            exit();
                                                                                            ?>