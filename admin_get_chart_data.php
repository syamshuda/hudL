<?php
require_once 'config/koneksi.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
    http_response_code(403);
        echo json_encode(['error' => 'Akses ditolak']);
            exit();
            }

            header('Content-Type: application/json');

            $chart_type = $_GET['chart'] ?? '';
            $data = ['labels' => [], 'values' => []];

            // Data untuk 12 bulan terakhir
            $months = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = date('Y-m', strtotime("-$i months"));
                    $months[$month] = 0; // Inisialisasi dengan 0
                    }

                    if ($chart_type == 'registrations') {
                        $sql = "SELECT DATE_FORMAT(dibuat_pada, '%Y-%m') AS bulan, COUNT(id) AS jumlah FROM pengguna WHERE dibuat_pada >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY bulan";
                            $result = $koneksi->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                        if (isset($months[$row['bulan']])) {
                                                    $months[$row['bulan']] = $row['jumlah'];
                                                            }
                                                                }
                                                                } elseif ($chart_type == 'classes') {
                                                                    $sql = "SELECT DATE_FORMAT(tanggal_dibuat, '%Y-%m') AS bulan, COUNT(id) AS jumlah FROM kelas WHERE tanggal_dibuat >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY bulan";
                                                                        $result = $koneksi->query($sql);
                                                                            while ($row = $result->fetch_assoc()) {
                                                                                     if (isset($months[$row['bulan']])) {
                                                                                                 $months[$row['bulan']] = $row['jumlah'];
                                                                                                         }
                                                                                                             }
                                                                                                             }

                                                                                                             // Format data untuk Chart.js
                                                                                                             foreach ($months as $month => $value) {
                                                                                                                 $data['labels'][] = date('M Y', strtotime($month . '-01'));
                                                                                                                     $data['values'][] = $value;
                                                                                                                     }

                                                                                                                     echo json_encode($data);
                                                                                                                     $koneksi->close();
                                                                                                                     ?>