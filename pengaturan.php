<?php
require_once '../config.php';

// Cek sesi admin
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$errors = [];
$success_message = "";

// Ambil pengaturan saat ini dari database
$pengaturan = [];
$sql_fetch = "SELECT * FROM pengaturan ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $sql_fetch);
if (mysqli_num_rows($result) > 0) {
    $pengaturan = mysqli_fetch_assoc($result);
} else {
    // Inisialisasi default jika kosong
    $pengaturan = [
        'id' => null, 'tahun_ajaran' => '', 'tgl_buka' => '', 
        'tgl_tutup' => '', 'kuota_siswa' => '', 'informasi_umum' => ''
    ];
}

// Proses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $tahun_ajaran = $_POST['tahun_ajaran'];
    $tgl_buka = $_POST['tgl_buka'];
    $tgl_tutup = $_POST['tgl_tutup'];
    $kuota_siswa = $_POST['kuota_siswa'];
    $informasi_umum = $_POST['informasi_umum'];

    if (empty($tahun_ajaran) || empty($tgl_buka) || empty($tgl_tutup) || empty($kuota_siswa)) {
        $errors[] = "Field Tahun Ajaran, Tanggal Buka/Tutup, dan Kuota wajib diisi.";
    }

    if (empty($errors)) {
        if (!empty($id)) {
            // Update
            $sql = "UPDATE pengaturan SET tahun_ajaran=?, tgl_buka=?, tgl_tutup=?, kuota_siswa=?, informasi_umum=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssiis", $tahun_ajaran, $tgl_buka, $tgl_tutup, $kuota_siswa, $informasi_umum, $id);
        } else {
            // Insert
            $sql = "INSERT INTO pengaturan (tahun_ajaran, tgl_buka, tgl_tutup, kuota_siswa, informasi_umum) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssis", $tahun_ajaran, $tgl_buka, $tgl_tutup, $kuota_siswa, $informasi_umum);
        }

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Pengaturan berhasil disimpan!";
            // Refresh data pengaturan setelah update
            $result = mysqli_query($conn, $sql_fetch);
            $pengaturan = mysqli_fetch_assoc($result);
        } else {
            $errors[] = "Gagal menyimpan pengaturan.";
        }
        mysqli_stmt_close($stmt);
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan PPDB - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; background-color: #f8f9fa; }
        .sidebar { width: 280px; background: #343a40; color: white; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #ffffff; background-color: #495057; }
        .content { flex-grow: 1; padding: 30px; }
    </style>
</head>
<body>
    <div class="sidebar d-flex flex-column p-3">
        <h4><i class="bi bi-person-gear"></i> Admin PPDB</h4><hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="pendaftar.php" class="nav-link"><i class="bi bi-people"></i> Manajemen Pendaftar</a></li>
            <li><a href="pengaturan.php" class="nav-link active" aria-current="page"><i class="bi bi-sliders"></i> Pengaturan PPDB</a></li>
        </ul><hr>
        <div>
            <form action="logout.php" method="post">
                <button type="submit" class="btn btn-danger w-100"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </div>
    </div>

    <div class="content">
        <h1 class="mb-4">Pengaturan PPDB</h1>

        <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
        <?php if(!empty($errors)): ?><div class="alert alert-danger"><?php foreach($errors as $e) echo "<p>$e</p>"; ?></div><?php endif; ?>

        <div class="card">
            <div class="card-header">Formulir Pengaturan Periode Pendaftaran</div>
            <div class="card-body">
                <form action="pengaturan.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $pengaturan['id']; ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Ajaran (*)</label>
                            <input type="text" name="tahun_ajaran" class="form-control" value="<?php echo htmlspecialchars($pengaturan['tahun_ajaran']); ?>" placeholder="Contoh: 2025/2026" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kuota Siswa (*)</label>
                            <input type="number" name="kuota_siswa" class="form-control" value="<?php echo htmlspecialchars($pengaturan['kuota_siswa']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Buka Pendaftaran (*)</label>
                            <input type="datetime-local" name="tgl_buka" class="form-control" value="<?php echo !empty($pengaturan['tgl_buka']) ? date('Y-m-d\TH:i', strtotime($pengaturan['tgl_buka'])) : ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Tutup Pendaftaran (*)</label>
                            <input type="datetime-local" name="tgl_tutup" class="form-control" value="<?php echo !empty($pengaturan['tgl_tutup']) ? date('Y-m-d\TH:i', strtotime($pengaturan['tgl_tutup'])) : ''; ?>" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Informasi Umum / Pengumuman</label>
                            <textarea name="informasi_umum" class="form-control" rows="5" placeholder="Tulis pengumuman yang akan ditampilkan di halaman depan..."><?php echo htmlspecialchars($pengaturan['informasi_umum']); ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
