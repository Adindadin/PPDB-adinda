<?php
require_once '../config.php';

// Cek sesi admin
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Validasi ID pendaftar dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: ID Pendaftar tidak valid.");
}
$user_id = $_GET['id'];
$admin_id = $_SESSION['admin_id'];

$errors = [];
$success_message = "";

// Proses form verifikasi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status_berkas = $_POST['status_berkas'];
    $status_seleksi = $_POST['status_seleksi'];
    $catatan_admin = $_POST['catatan_admin'];

    $sql_update = "UPDATE seleksi SET status_berkas = ?, status_seleksi = ?, catatan_admin = ?, admin_id = ?, tgl_verifikasi = NOW() WHERE user_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql_update)) {
        mysqli_stmt_bind_param($stmt, "sssii", $status_berkas, $status_seleksi, $catatan_admin, $admin_id, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Status pendaftar berhasil diperbarui!";
        } else {
            $errors[] = "Gagal memperbarui status.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Ambil semua data pendaftar dari berbagai tabel
$data = [];
$sql = "SELECT u.nama_lengkap, u.email, u.nik_user, u.created_at as tgl_daftar, 
               p.*, 
               o.*, 
               s.status_berkas, s.status_seleksi, s.catatan_admin
        FROM users u
        LEFT JOIN pendaftar p ON u.id = p.user_id
        LEFT JOIN orang_tua o ON u.id = o.user_id
        LEFT JOIN seleksi s ON u.id = s.user_id
        WHERE u.id = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    if (!$data) {
        die("Data pendaftar tidak ditemukan.");
    }
}

// Ambil data dokumen
$dokumen = [];
$sql_docs = "SELECT jenis_dokumen, path_file FROM dokumen WHERE user_id = ?";
if ($stmt_docs = mysqli_prepare($conn, $sql_docs)) {
    mysqli_stmt_bind_param($stmt_docs, "i", $user_id);
    mysqli_stmt_execute($stmt_docs);
    $result_docs = mysqli_stmt_get_result($stmt_docs);
    while ($row = mysqli_fetch_assoc($result_docs)) {
        $dokumen[$row['jenis_dokumen']] = $row['path_file'];
    }
}

$doc_types = ['kartu_keluarga', 'akta_lahir', 'raport', 'ijazah', 'pas_foto'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendaftar - PPDB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; background-color: #f8f9fa; }
        .sidebar { width: 280px; background: #343a40; color: white; flex-shrink: 0; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #ffffff; background-color: #495057; }
        .content { flex-grow: 1; padding: 30px; }
        .data-label { font-weight: bold; color: #555; }
        .data-value { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="sidebar d-flex flex-column p-3">
        <h4><i class="bi bi-person-gear"></i> Admin PPDB</h4><hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="pendaftar.php" class="nav-link active" aria-current="page"><i class="bi bi-people"></i> Manajemen Pendaftar</a></li>
            <li><a href="pengaturan.php" class="nav-link"><i class="bi bi-sliders"></i> Pengaturan PPDB</a></li>
        </ul><hr>
        <div>
            <form action="logout.php" method="post">
                <button type="submit" class="btn btn-danger w-100"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </div>
    </div>

    <div class="content">
        <a href="pendaftar.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali ke Daftar</a>
        <h1 class="mb-4">Detail Pendaftar: <?php echo htmlspecialchars($data['nama_lengkap']); ?></h1>

        <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
        <?php if(!empty($errors)): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo "<p>$e</p>"; ?></div><?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <!-- Data Diri & Akademik -->
                <div class="card mb-4"><div class="card-header">Data Pribadi & Akademik</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6"><p class="data-label">NIK</p><p class="data-value"><?php echo htmlspecialchars($data['nik'] ?? '-'); ?></p></div>
                            <div class="col-md-6"><p class="data-label">NISN</p><p class="data-value"><?php echo htmlspecialchars($data['nisn'] ?? '-'); ?></p></div>
                            <div class="col-md-6"><p class="data-label">Tempat, Tanggal Lahir</p><p class="data-value"><?php echo htmlspecialchars($data['tempat_lahir'] ?? '-') . ", " . (!empty($data['tanggal_lahir']) ? date("d F Y", strtotime($data['tanggal_lahir'])) : '-'); ?></p></div>
                            <div class="col-md-6"><p class="data-label">Jenis Kelamin</p><p class="data-value"><?php echo htmlspecialchars($data['jenis_kelamin'] ?? '-'); ?></p></div>
                            <div class="col-md-12"><p class="data-label">Alamat</p><p class="data-value"><?php echo htmlspecialchars($data['alamat'] ?? '-'); ?></p></div>
                            <hr>
                            <div class="col-md-6"><p class="data-label">Asal Sekolah</p><p class="data-value"><?php echo htmlspecialchars($data['asal_sekolah'] ?? '-'); ?></p></div>
                            <div class="col-md-6"><p class="data-label">Nilai Rata-rata</p><p class="data-value"><?php echo htmlspecialchars($data['nilai_rata_rata'] ?? '-'); ?></p></div>
                        </div>
                    </div>
                </div>
                <!-- Data Ortu -->
                <div class="card mb-4"><div class="card-header">Data Orang Tua</div>
                    <div class="card-body">
                         <div class="row">
                            <div class="col-md-6"><p class="data-label">Nama Ayah</p><p class="data-value"><?php echo htmlspecialchars($data['nama_ayah'] ?? '-'); ?></p></div>
                            <div class="col-md-6"><p class="data-label">Pekerjaan Ayah</p><p class="data-value"><?php echo htmlspecialchars($data['pekerjaan_ayah'] ?? '-'); ?></p></div>
                            <div class="col-md-6"><p class="data-label">Nama Ibu</p><p class="data-value"><?php echo htmlspecialchars($data['nama_ibu'] ?? '-'); ?></p></div>
                            <div class="col-md-6"><p class="data-label">Pekerjaan Ibu</p><p class="data-value"><?php echo htmlspecialchars($data['pekerjaan_ibu'] ?? '-'); ?></p></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <!-- Dokumen -->
                <div class="card mb-4"><div class="card-header">Dokumen Persyaratan</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach($doc_types as $key): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo ucwords(str_replace('_',' ',$key)); ?>
                                <?php if(isset($dokumen[$key])): ?>
                                    <a href="../<?php echo htmlspecialchars($dokumen[$key]); ?>" target="_blank" class="btn btn-sm btn-outline-primary">Lihat</a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Kosong</span>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <!-- Form Verifikasi -->
                <div class="card mb-4"><div class="card-header">Verifikasi & Seleksi</div>
                    <div class="card-body">
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Status Berkas</label>
                                <select name="status_berkas" class="form-select">
                                    <option value="Belum diperiksa" <?php echo ($data['status_berkas'] == 'Belum diperiksa') ? 'selected' : ''; ?>>Belum diperiksa</option>
                                    <option value="Lengkap dan Valid" <?php echo ($data['status_berkas'] == 'Lengkap dan Valid') ? 'selected' : ''; ?>>Lengkap dan Valid</option>
                                    <option value="Tidak Lengkap / Ditolak" <?php echo ($data['status_berkas'] == 'Tidak Lengkap / Ditolak') ? 'selected' : ''; ?>>Tidak Lengkap / Ditolak</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status Seleksi</label>
                                <select name="status_seleksi" class="form-select">
                                    <option value="Belum diverifikasi" <?php echo ($data['status_seleksi'] == 'Belum diverifikasi') ? 'selected' : ''; ?>>Belum diverifikasi</option>
                                    <option value="Diterima" <?php echo ($data['status_seleksi'] == 'Diterima') ? 'selected' : ''; ?>>Diterima</option>
                                    <option value="Tidak diterima" <?php echo ($data['status_seleksi'] == 'Tidak diterima') ? 'selected' : ''; ?>>Tidak diterima</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan untuk Pendaftar</label>
                                <textarea name="catatan_admin" class="form-control" rows="3"><?php echo htmlspecialchars($data['catatan_admin'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
