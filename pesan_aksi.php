<?php
require_once 'config/koneksi.php';

// Proteksi, pengguna harus login untuk bisa mengirim pesan
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
        exit();
        }
        $id_pengirim = $_SESSION['user_id'];

        if (isset($_POST['kirim_pesan'])) {
            $penerima_list = $_POST['penerima'];
                $subjek = sanitize($koneksi, $_POST['subjek']);
                    $isi_pesan = $_POST['isi_pesan']; // Dari CKEditor
                        $id_balasan_untuk = isset($_POST['id_balasan_untuk']) ? intval($_POST['id_balasan_untuk']) : null;

                            if (empty($penerima_list) || empty($subjek) || empty($isi_pesan)) {
                                    // Handle error, data tidak lengkap
                                            header("Location: pesan_kirim.php?status=error");
                                                    exit();
                                                        }

                                                            $daftar_id_penerima = [];
                                                                foreach ($penerima_list as $penerima) {
                                                                        if ($penerima == 'semua_guru') {
                                                                                    $result = $koneksi->query("SELECT id FROM pengguna WHERE peran = 'guru'");
                                                                                                while ($row = $result->fetch_assoc()) $daftar_id_penerima[] = $row['id'];
                                                                                                        } elseif ($penerima == 'semua_siswa') {
                                                                                                                    $result = $koneksi->query("SELECT id FROM pengguna WHERE peran = 'siswa'");
                                                                                                                                while ($row = $result->fetch_assoc()) $daftar_id_penerima[] = $row['id'];
                                                                                                                                        } else {
                                                                                                                                                    // Formatnya adalah "peran-id", contoh: "guru-12"
                                                                                                                                                                list($peran, $id) = explode('-', $penerima);
                                                                                                                                                                            $daftar_id_penerima[] = intval($id);
                                                                                                                                                                                    }
                                                                                                                                                                                        }
                                                                                                                                                                                            
                                                                                                                                                                                                // Hapus duplikat ID jika ada
                                                                                                                                                                                                    $daftar_id_penerima = array_unique($daftar_id_penerima);

                                                                                                                                                                                                        // Mulai transaksi
                                                                                                                                                                                                            $koneksi->begin_transaction();
                                                                                                                                                                                                                try {
                                                                                                                                                                                                                        $stmt_kirim = $koneksi->prepare("INSERT INTO pesan (id_pengirim, id_penerima, subjek, isi_pesan, id_balasan_untuk) VALUES (?, ?, ?, ?, ?)");
                                                                                                                                                                                                                                
                                                                                                                                                                                                                                        foreach ($daftar_id_penerima as $id_penerima) {
                                                                                                                                                                                                                                                    // Jangan kirim pesan ke diri sendiri
                                                                                                                                                                                                                                                                if ($id_penerima == $id_pengirim) continue;
                                                                                                                                                                                                                                                                            
                                                                                                                                                                                                                                                                                        $stmt_kirim->bind_param("iissi", $id_pengirim, $id_penerima, $subjek, $isi_pesan, $id_balasan_untuk);
                                                                                                                                                                                                                                                                                                    $stmt_kirim->execute();
                                                                                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                                                                                                    
                                                                                                                                                                                                                                                                                                                            $stmt_kirim->close();
                                                                                                                                                                                                                                                                                                                                    $koneksi->commit();
                                                                                                                                                                                                                                                                                                                                            header("Location: pesan_kotak_masuk.php?status=sukses");
                                                                                                                                                                                                                                                                                                                                                    exit();

                                                                                                                                                                                                                                                                                                                                                        } catch (Exception $e) {
                                                                                                                                                                                                                                                                                                                                                                $koneksi->rollback();
                                                                                                                                                                                                                                                                                                                                                                        // Log error atau tampilkan pesan error
                                                                                                                                                                                                                                                                                                                                                                                die("Gagal mengirim pesan: " . $e->getMessage());
                                                                                                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                                                                                                    } else {
                                                                                                                                                                                                                                                                                                                                                                                        header("Location: pesan_kotak_masuk.php");
                                                                                                                                                                                                                                                                                                                                                                                            exit();
                                                                                                                                                                                                                                                                                                                                                                                            }

                                                                                                                                                                                                                                                                                                                                                                                            $koneksi->close();
                                                                                                                                                                                                                                                                                                                                                                                            ?>