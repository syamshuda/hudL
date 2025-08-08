<?php
require_once 'config/koneksi.php';

// Keamanan: Pastikan hanya ADMIN yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
    header("Location: logout.php");
    exit();
}

$id_admin = $_SESSION['user_id'];
$pesan_profil = '';
$pesan_password = '';

// Logika untuk update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $nama_lengkap = sanitize($koneksi, $_POST['nama_lengkap']);
    $email = sanitize($koneksi, $_POST['email']);
    $no_hp = sanitize($koneksi, $_POST['no_hp']);

    if (!empty($nama_lengkap) && !empty($email)) {
        // Cek apakah email sudah digunakan oleh pengguna lain
        $stmt_cek_email = $koneksi->prepare("SELECT id FROM pengguna WHERE email = ? AND id != ?");
        $stmt_cek_email->bind_param("si", $email, $id_admin);
        $stmt_cek_email->execute();
        $result_cek_email = $stmt_cek_email->get_result();

        if ($result_cek_email->num_rows > 0) {
            $pesan_profil = "<div class='alert alert-danger'>Email sudah digunakan oleh akun lain.</div>";
        } else {
            $stmt = $koneksi->prepare("UPDATE pengguna SET nama_lengkap = ?, email = ?, no_hp = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nama_lengkap, $email, $no_hp, $id_admin);
            if ($stmt->execute()) {
                $_SESSION['nama_lengkap'] = $nama_lengkap; // Update nama di sesi
                $pesan_profil = "<div class='alert alert-success'>Profil berhasil diperbarui.</div>";
            } else {
                $pesan_profil = "<div class='alert alert-danger'>Gagal memperbarui profil.</div>";
            }
            $stmt->close();
        }
        $stmt_cek_email->close();
    } else {
        $pesan_profil = "<div class='alert alert-warning'>Nama lengkap dan email tidak boleh kosong.</div>";
    }
}

// Logika untuk ganti password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if (!empty($password_lama) && !empty($password_baru) && !empty($konfirmasi_password)) {
        if ($password_baru !== $konfirmasi_password) {
            $pesan_password = "<div class='alert alert-danger'>Password baru dan konfirmasi tidak cocok.</div>";
        } else {
            $stmt = $koneksi->prepare("SELECT password FROM pengguna WHERE id = ?");
            $stmt->bind_param("i", $id_admin);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (hash('sha256', $password_lama) === $user['password']) {
                $hashed_password_baru = hash('sha256', $password_baru);
                $stmt_update = $koneksi->prepare("UPDATE pengguna SET password = ? WHERE id = ?");
                $stmt_update->bind_param("si", $hashed_password_baru, $id_admin);
                if ($stmt_update->execute()) {
                    $pesan_password = "<div class='alert alert-success'>Password berhasil diubah.</div>";
                } else {
                    $pesan_password = "<div class='alert alert-danger'>Gagal mengubah password.</div>";
                }
                $stmt_update->close();
            } else {
                $pesan_password = "<div class='alert alert-danger'>Password lama salah.</div>";
            }
            $stmt->close();
        }
    } else {
        $pesan_password = "<div class='alert alert-warning'>Semua field password wajib diisi.</div>";
    }
}

// Ambil data admin saat ini
$stmt_admin = $koneksi->prepare("SELECT nama_lengkap, email, no_hp FROM pengguna WHERE id = ?");
$stmt_admin->bind_param("i", $id_admin);
$stmt_admin->execute();
$admin = $stmt_admin->get_result()->fetch_assoc();
$stmt_admin->close();

include_once 'templates/header_admin.php';
?>

<h1 class="mb-4"><i class="bi bi-person-circle me-2"></i>Profil Saya</h1>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Edit Detail Profil</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($pesan_profil)) echo $pesan_profil; ?>
                <form action="admin_profil.php" method="POST">
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($admin['nama_lengkap']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_hp" class="form-label">Nomor HP</label>
                        <input type="tel" class="form-control" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($admin['no_hp'] ?? ''); ?>">
                    </div>
                    <button type="submit" name="update_profil" class="btn btn-primary">Simpan Perubahan Profil</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Ganti Password</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($pesan_password)) echo $pesan_password; ?>
                <form action="admin_profil.php" method="POST">
                    <div class="mb-3">
                        <label for="password_lama" class="form-label">Password Lama</label>
                        <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_baru" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                    </div>
                    <div class="mb-3">
                        <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                    </div>
                    <button type="submit" name="ganti_password" class="btn btn-danger">Ganti Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$koneksi->close();
include_once 'templates/footer_admin.php';
?>