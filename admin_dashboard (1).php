<?php
/**
 * File: admin_dashboard.php
 * Versi FINAL dengan Fitur Pencarian, Reset Password, Edit Profil, dan Hapus Akun Pengguna.
 */

require_once 'config/koneksi.php';

// Keamanan Tingkat Tinggi
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] != 'admin') {
    header("Location: logout.php");
    exit();
}

$id_admin = $_SESSION['user_id'];
$pesan = '';

// Logika notifikasi yang diperbarui
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    switch ($status) {
        case 'login_as_success':
            $pesan = "<div class='alert alert-success'>Anda berhasil login sebagai pengguna lain.</div>";
            break;
        case 'return_success':
            $pesan = "<div class='alert alert-info'>Anda telah kembali ke akun Admin Anda.</div>";
            break;
        case 'sukses_edit':
            $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>Profil pengguna berhasil diperbarui.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
        case 'gagal_edit':
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Gagal memperbarui profil pengguna. Email mungkin sudah digunakan.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
        case 'tanpa_perubahan':
            $pesan = "<div class='alert alert-info alert-dismissible fade show' role='alert'>Tidak ada perubahan data yang disimpan.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
        case 'sukses_hapus':
            $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>Akun pengguna berhasil dihapus secara permanen.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
        case 'gagal_hapus':
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Gagal menghapus akun pengguna.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
        case 'gagal_hapus_diri':
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Anda tidak dapat menghapus akun Anda sendiri.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            break;
    }
}

// Logika reset password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $id_pengguna = intval($_POST['id_pengguna']);
    $password_baru = $_POST['password_baru'];
    if (!empty($id_pengguna) && !empty($password_baru)) {
        $hashed_password_baru = hash('sha256', $password_baru);
        $stmt = $koneksi->prepare("UPDATE pengguna SET password = ? WHERE id = ?");
        if($stmt) {
            $stmt->bind_param("si", $hashed_password_baru, $id_pengguna);
            if ($stmt->execute()) {
                $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>Password berhasil direset.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            } else {
                $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Gagal mereset password.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            }
            $stmt->close();
        }
    } else {
        $pesan = "<div class='alert alert-warning alert-dismissible fade show' role='alert'>Password baru tidak boleh kosong.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

// === LOGIKA PENCARIAN (UNTUK LOAD PERTAMA KALI) ===
$search_query = "";
$params = [];
$types = "";

$sql_users = "SELECT id, nama_lengkap, email, no_hp, peran, status_akun FROM pengguna WHERE peran IN ('guru', 'siswa')";

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_query = trim($_GET['q']);
    $sql_users .= " AND (nama_lengkap LIKE ? OR email LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= "ss";
}

$sql_users .= " ORDER BY status_akun DESC, peran, nama_lengkap";

$stmt_users = $koneksi->prepare($sql_users);

if (!empty($params)) {
    $stmt_users->bind_param($types, ...$params);
}

$stmt_users->execute();
$result_users = $stmt_users->get_result();

include_once 'templates/header_admin.php';
?>

<h1 class="mb-4">Manajemen Pengguna</h1>

<?php if(!empty($pesan)) echo $pesan; ?>

<div class="card shadow-sm">
    <div class="card-header">
        <form action="admin_dashboard.php" method="GET" class="d-flex">
            <input class="form-control me-2" type="search" name="q" id="search-input" placeholder="Cari nama atau email pengguna..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button class="btn btn-primary" type="submit">Cari</button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Status Akun</th>
                        <th style="width: 40%;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="user-table-body">
                    <?php if ($result_users->num_rows > 0): ?>
                        <?php while($user = $result_users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php 
                                        $badge_class_peran = $user['peran'] == 'guru' ? 'bg-primary' : 'bg-secondary';
                                        echo "<span class='badge $badge_class_peran'>" . ucfirst($user['peran']) . "</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        if ($user['status_akun'] == 'aktif') {
                                            echo "<span class='badge bg-success'>Aktif</span>";
                                        } else {
                                            echo "<span class='badge bg-danger'>Tidak Aktif</span>";
                                        }
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="admin_login_as.php?id=<?php echo $user['id']; ?>" class="btn btn-info" onclick="return confirm('Anda akan masuk sebagai <?php echo htmlspecialchars($user['nama_lengkap']); ?>. Lanjutkan?')">
                                            <i class="bi bi-person-bounding-box"></i> Login As
                                        </a>
                                                                                 <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#editProfilModal" 
                                             data-user-id="<?php echo $user['id']; ?>" 
                                             data-user-nama="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" 
                                             data-user-email="<?php echo htmlspecialchars($user['email']); ?>"
                                             data-user-nohp="<?php echo htmlspecialchars($user['no_hp'] ?? ''); ?>"
                                             data-user-peran="<?php echo htmlspecialchars($user['peran']); ?>">
                                             <i class="bi bi-pencil-fill"></i> Edit
                                         </button>
                                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal" data-id-pengguna="<?php echo $user['id']; ?>" data-nama-pengguna="<?php echo htmlspecialchars($user['nama_lengkap']); ?>">
                                            <i class="bi bi-key-fill"></i> Reset Pass
                                        </button>
                                        <?php if ($user['status_akun'] == 'aktif'): ?>
                                            <a href="ubah_status_akun.php?id=<?php echo $user['id']; ?>&status_baru=tidak_aktif" class="btn btn-outline-secondary">Nonaktifkan</a>
                                        <?php else: ?>
                                            <a href="ubah_status_akun.php?id=<?php echo $user['id']; ?>&status_baru=aktif" class="btn btn-outline-success">Aktifkan</a>
                                        <?php endif; ?>
                                        <a href="admin_hapus_pengguna.php?id=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('PERINGATAN!\nAnda akan menghapus akun <?php echo htmlspecialchars($user['nama_lengkap']); ?> secara permanen.\n\nSemua data yang terkait dengan pengguna ini (kelas, nilai, jawaban, pesan, dll) akan ikut terhapus dan tidak dapat dikembalikan.\n\nApakah Anda benar-benar yakin?')">
                                            <i class="bi bi-trash-fill"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">
                                <?php if (!empty($search_query)): ?>
                                    Pengguna dengan nama atau email "<?php echo htmlspecialchars($search_query); ?>" tidak ditemukan.
                                <?php else: ?>
                                    Tidak ada data pengguna.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form action="admin_dashboard.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_pengguna" id="id_pengguna_modal">
                    <p>Anda akan mereset password untuk: <strong id="nama_pengguna_modal"></strong></p>
                    <div class="mb-3">
                        <label for="password_baru" class="form-label">Masukkan Password Baru</label>
                        <input type="text" class="form-control" name="password_baru" id="password_baru" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="reset_password" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editProfilModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfilModalLabel">Edit Profil Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="admin_edit_pengguna.php" method="POST">
                                 <div class="modal-body">
                     <input type="hidden" name="id_pengguna_edit" id="id_pengguna_edit_modal">
                     <div class="mb-3">
                         <label for="nama_lengkap_edit" class="form-label">Nama Lengkap</label>
                         <input type="text" class="form-control" name="nama_lengkap_edit" id="nama_lengkap_edit_modal" required>
                     </div>
                     <div class="mb-3">
                         <label for="email_edit" class="form-label">Alamat Email</label>
                         <input type="email" class="form-control" name="email_edit" id="email_edit_modal" required>
                     </div>
                     <div class="mb-3">
                         <label for="no_hp_edit" class="form-label">Nomor HP</label>
                         <input type="text" class="form-control" name="no_hp_edit" id="no_hp_edit_modal">
                     </div>
                     <div class="mb-3">
                         <label for="peran_edit" class="form-label">Peran</label>
                         <select class="form-select" id="peran_edit_modal">
                           <option value="guru">Guru</option>
                           <option value="siswa">Siswa</option>
                           <option value="bk">BK</option>
                           <option value="wali_kelas">Wali Kelas</option>
                           <option value="kurikulum">Kurikulum</option>
                           <option value="admin">Admin</option>
                         </select>
                         <div class="form-text">Perubahan peran disimpan terpisah.</div>
                       </div>
                 </div>
                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                     <button type="submit" name="simpan_perubahan_profil" class="btn btn-primary">Simpan Perubahan</button>
                     <button type="button" id="btn-simpan-peran" class="btn btn-outline-primary">Simpan Peran</button>
                 </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Script untuk Modal Reset Password
    const resetPasswordModal = document.getElementById('resetPasswordModal');
    if (resetPasswordModal) {
        resetPasswordModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const idPengguna = button.getAttribute('data-id-pengguna');
            const namaPengguna = button.getAttribute('data-nama-pengguna');
            resetPasswordModal.querySelector('.modal-title').textContent = 'Reset Password untuk ' + namaPengguna;
            resetPasswordModal.querySelector('#id_pengguna_modal').value = idPengguna;
            resetPasswordModal.querySelector('#nama_pengguna_modal').textContent = namaPengguna;
        });
    }

    // Script untuk Modal Edit Profil
    const editProfilModal = document.getElementById('editProfilModal');
    if (editProfilModal) {
        editProfilModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-nama');
            const userEmail = button.getAttribute('data-user-email');
            const userNoHp = button.getAttribute('data-user-nohp');
            const userRole = button.getAttribute('data-user-peran');
            
            editProfilModal.querySelector('.modal-title').textContent = 'Edit Profil: ' + userName;
            editProfilModal.querySelector('#id_pengguna_edit_modal').value = userId;
            editProfilModal.querySelector('#nama_lengkap_edit_modal').value = userName;
            editProfilModal.querySelector('#email_edit_modal').value = userEmail;
            editProfilModal.querySelector('#no_hp_edit_modal').value = userNoHp;
            const roleSelect = editProfilModal.querySelector('#peran_edit_modal');
            if (roleSelect) roleSelect.value = userRole;

            const btnSimpanPeran = editProfilModal.querySelector('#btn-simpan-peran');
            if (btnSimpanPeran) {
                btnSimpanPeran.onclick = function() {
                    const formData = new FormData();
                    formData.append('id_pengguna', userId);
                    formData.append('peran_baru', roleSelect.value);
                    fetch('admin_set_role.php', { method: 'POST', body: formData })
                        .then(r => { if (!r.ok) throw new Error('error'); return r.text(); })
                        .then(() => { window.location.href = 'admin_dashboard.php?status=sukses_edit'; })
                        .catch(() => { window.location.href = 'admin_dashboard.php?status=gagal_edit'; });
                };
            }
        });
    }

    // --- FITUR AUTO-REFRESH ---
    const searchInput = document.getElementById('search-input');
    const userTableBody = document.getElementById('user-table-body');
    const refreshInterval = 20000; // 20 detik

    function refreshUserTable() {
        if (document.activeElement === searchInput) {
            return;
        }
        const currentSearchQuery = searchInput.value;
        fetch(`ajax_get_users.php?q=${encodeURIComponent(currentSearchQuery)}`)
            .then(response => response.ok ? response.text() : Promise.reject('Gagal memuat data.'))
            .then(html => {
                userTableBody.innerHTML = html;
            })
            .catch(error => console.error('Error saat auto-refresh:', error));
    }

    setInterval(refreshUserTable, refreshInterval);
});
</script>

<?php
$stmt_users->close();
$koneksi->close();
include_once 'templates/footer_admin.php';
?>