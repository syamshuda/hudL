<?php
/**
 * File: get_jawaban_siswa.php
 * VERSI PERBAIKAN: Memperbaiki query untuk mencegah duplikasi soal.
 * PERBAIKAN FINAL: Menggunakan str_replace untuk mengatasi masalah format \r\n pada jawaban esai.
 */

require_once 'config/koneksi.php';

// Proteksi, pastikan parameter ada
if (!isset($_GET['id_kuis']) || !isset($_GET['id_siswa'])) {
    die("<div class='alert alert-danger'>Parameter tidak lengkap.</div>");
}

$id_kuis = intval($_GET['id_kuis']);
$id_siswa = intval($_GET['id_siswa']);

// Ambil nilai dan komentar yang sudah ada
$penilaian_sebelumnya = null;
$stmt_penilaian = $koneksi->prepare("SELECT nilai, komentar FROM penilaian WHERE id_soal = ? AND id_siswa = ?");
$stmt_penilaian->bind_param("ii", $id_kuis, $id_siswa);
$stmt_penilaian->execute();
$result_penilaian = $stmt_penilaian->get_result();
if ($result_penilaian->num_rows > 0) {
    $penilaian_sebelumnya = $result_penilaian->fetch_assoc();
}
$stmt_penilaian->close();


// Query untuk mengambil semua pertanyaan di kuis ini dan jawaban siswa terkait
$query = "
    SELECT 
        bs.soal, 
        bs.tipe_soal, 
        bs.opsi_a, bs.opsi_b, bs.opsi_c, bs.opsi_d, bs.opsi_e,
        bs.kunci_jawaban,
        js.jawaban as jawaban_siswa
    FROM kuis_soal ks
    JOIN bank_soal bs ON ks.id_bank_soal = bs.id
    LEFT JOIN jawaban_siswa js ON ks.id_bank_soal = js.id_pertanyaan AND js.id_siswa = ? AND js.id_soal = ?
    WHERE ks.id_kuis = ?
    ORDER BY ks.id
";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("iii", $id_siswa, $id_kuis, $id_kuis);
$stmt->execute();
$result = $stmt->get_result();

$skor_otomatis = 0;
$jumlah_pg = 0;

// Mulai membuat output HTML
$output = '<div id="jawaban-container">';

while ($row = $result->fetch_assoc()) {
    $output .= "<div class='mb-4 p-3 border rounded'>";
    $output .= "<div>" . $row['soal'] . "</div>"; // Tidak perlu htmlspecialchars karena dari CKEditor

    if ($row['tipe_soal'] == 'pilihan_ganda') {
        $jumlah_pg++;
        $jawaban_siswa = strtoupper(trim($row['jawaban_siswa']));
        $kunci_jawaban = strtoupper(trim($row['kunci_jawaban']));
        
        $output .= "<ul class='list-group mt-2'>";
        foreach (['A', 'B', 'C', 'D', 'E'] as $opsi_huruf) {
            $opsi_teks = 'opsi_' . strtolower($opsi_huruf);
            if (!empty($row[$opsi_teks])) {
                $class = '';
                $icon = '';
                if ($opsi_huruf == $kunci_jawaban) {
                    $class = 'list-group-item-success';
                    $icon = ' (Kunci Jawaban)';
                }
                if ($opsi_huruf == $jawaban_siswa) {
                    $class = ($jawaban_siswa == $kunci_jawaban) ? 'list-group-item-success' : 'list-group-item-danger';
                    $icon .= ' (Jawaban Siswa)';
                }
                $output .= "<li class='list-group-item " . $class . "'>" . $opsi_huruf . ". " . htmlspecialchars($row[$opsi_teks]) . "<strong>" . $icon . "</strong></li>";
            }
        }
        $output .= "</ul>";
        
        if ($jawaban_siswa == $kunci_jawaban) {
            $skor_otomatis++;
        }
    } elseif ($row['tipe_soal'] == 'esai') {
        $output .= "<h6 class='mt-2'>Jawaban Esai Siswa:</h6>";
        // ===== PERBAIKAN FINAL DI SINI =====
        // 1. Ambil teks jawaban atau default text
        $jawaban_mentah = $row['jawaban_siswa'] ?? 'Tidak ada jawaban.';
        // 2. Amankan dari XSS
        $jawaban_aman = htmlspecialchars($jawaban_mentah);
        // 3. Ganti karakter '\r\n' menjadi tag <br> untuk baris baru
        $jawaban_format = str_replace('\r\n', '<br>', $jawaban_aman);
        // 4. Tampilkan hasilnya
        $output .= "<div class='p-3 bg-light border rounded'>" . $jawaban_format . "</div>";
        // ===== AKHIR DARI PERBAIKAN =====
    }
    $output .= "</div>";
}
$output .= "</div>";
$stmt->close();

// Hitung skor akhir PG
$skor_akhir_pg = ($jumlah_pg > 0) ? ($skor_otomatis / $jumlah_pg) * 100 : 0;
$nilai_final_value = $penilaian_sebelumnya['nilai'] ?? number_format($skor_akhir_pg, 2);
$komentar_value = $penilaian_sebelumnya['komentar'] ?? '';


// Form untuk submit nilai
$output .= "
<hr>
<h4>Form Penilaian Akhir</h4>
<form action='penilaian.php?id_soal=$id_kuis' method='POST' onsubmit='return confirm(\"Simpan penilaian ini? Poin siswa akan diperbarui.\")'>
    <input type='hidden' name='id_siswa' value='$id_siswa'>
    <p>Skor Pilihan Ganda (Otomatis): <strong>" . number_format($skor_akhir_pg, 2) . "</strong></p>
    <div class='mb-3'>
        <label for='nilai_final' class='form-label'><strong>Input Nilai Final (0-100)</strong></label>
        <input type='number' step='0.01' class='form-control' id='nilai_final' name='nilai_final' value='" . $nilai_final_value . "' required>
        <small class='form-text text-muted'>Anda bisa menyesuaikan skor akhir setelah mempertimbangkan nilai esai.</small>
    </div>
    <div class='mb-3'>
        <label for='komentar' class='form-label'>Komentar / Feedback untuk Siswa</label>
        <textarea class='form-control' name='komentar' id='komentar' rows='3'>".htmlspecialchars($komentar_value)."</textarea>
    </div>
    <div class='modal-footer'>
        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Tutup</button>
        <button type='submit' name='simpan_nilai' class='btn btn-success'>Simpan Nilai</button>
    </div>
</form>";

// Kirim output HTML kembali ke halaman utama
echo $output;
$koneksi->close();
?>