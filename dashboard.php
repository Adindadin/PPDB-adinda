<?php
require_once 'config.php';

// Cek jika pengguna tidak login, arahkan ke halaman login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$nama_lengkap = $_SESSION["nama_lengkap"];

// Variabel untuk status
$status_pendaftaran = "Belum Lengkap";
$pesan_status = "Anda belum mengisi formulir pendaftaran. Silakan lengkapi data Anda.";
$link_formulir = "formulir.php";
$tombol_formulir = "Isi Formulir Pendaftaran";
$is_form_filled = false; // Inisialisasi variabel untuk mencegah warning

// Cek status pendaftaran dari database
// 1. Cek apakah data pendaftar sudah ada
$sql_check = "SELECT p.id, s.status_seleksi FROM pendaftar p LEFT JOIN seleksi s ON p.user_id = s.user_id WHERE p.user_id = ?";
if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
    mysqli_stmt_bind_param($stmt_check, "i", $user_id);
    if (mysqli_stmt_execute($stmt_check)) {
        mysqli_stmt_store_result($stmt_check);
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $is_form_filled = true; // Form sudah diisi
            mysqli_stmt_bind_result($stmt_check, $pendaftar_id, $status_seleksi);
            mysqli_stmt_fetch($stmt_check);

            $status_pendaftaran = $status_seleksi ?? 'Belum Lengkap'; // Default jika belum ada data seleksi
            $tombol_formulir = "Ubah Formulir Pendaftaran";

            $status_badge_class = 'bg-secondary'; // Default untuk Belum Lengkap
            $status_icon = 'bi-question-circle';

            switch ($status_pendaftaran) {
                case 'Belum diverifikasi':
                    $pesan_status = "Data Anda telah dikirim dan sedang menunggu verifikasi oleh admin.";
                    $status_badge_class = 'bg-warning text-dark';
                    $status_icon = 'bi-hourglass-split';
                    break;
                case 'Diterima':
                    $pesan_status = "Selamat! Anda dinyatakan DITERIMA sebagai siswa baru.";
                    $status_badge_class = 'bg-success';
                    $status_icon = 'bi-check-circle';
                    break;
                case 'Tidak diterima':
                    $pesan_status = "Mohon maaf, Anda dinyatakan TIDAK DITERIMA sebagai siswa baru.";
                    $status_badge_class = 'bg-danger';
                    $status_icon = 'bi-x-circle';
                    break;
                default:
                     $pesan_status = "Data Anda sudah tersimpan. Silakan tunggu proses verifikasi selanjutnya.";
                     $status_badge_class = 'bg-info text-dark';
                     $status_icon = 'bi-info-circle';
                     break;
            }
        }
    }
    mysqli_stmt_close($stmt_check);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pendaftar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Link ke stylesheet baru -->
    <style>
        .hero-dashboard {
            background: linear-gradient(45deg, #e0e5ec, #c0c5ce); /* Neutral gradient */
            color: white;
            padding: 60px 0;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .hero-dashboard h1 {
            font-weight: 700;
            font-size: 3.5rem;
        }
        .status-card .card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 30px;
        }
        .status-card .status-icon {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        .status-card .badge {
            font-size: 1.2rem;
            padding: 10px 20px;
            border-radius: 25px;
            margin-bottom: 15px;
        }
        .action-buttons .btn {
            margin: 5px;
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 10px;
        }
    </style>
</head>
<body class="auth-page"> <!-- Menggunakan class auth-page untuk background -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php"><strong>PPDB Online</strong></a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="hero-dashboard">
            <div class="container-fluid text-center">
                <h1 class="display-5 fw-bold">Selamat Datang, <?php echo htmlspecialchars($nama_lengkap); ?>!</h1>
                <p class="lead">Ini adalah halaman dashboard Anda. Kelola informasi pendaftaran Anda di sini.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-lg status-card">
                    <div class="card-header bg-white border-0 pt-4">
                        <h4 class="text-center mb-0">Status Pendaftaran Anda</h4>
                    </div>
                    <div class="card-body">
                        <i class="bi <?php echo $status_icon; ?> status-icon text-<?php echo str_replace('bg-', '', explode(' ', $status_badge_class)[0]); ?>"></i>
                        <span class="badge <?php echo $status_badge_class; ?>"><?php echo htmlspecialchars($status_pendaftaran); ?></span>
                        <p class="card-text text-muted mt-3"><?php echo htmlspecialchars($pesan_status); ?></p>
                        <hr class="w-75">
                        <div class="action-buttons">
                            <a href="<?php echo $link_formulir; ?>" class="btn btn-primary"><i class="bi bi-pencil-square"></i> <?php echo $tombol_formulir; ?></a>
                            <a href="upload.php" class="btn btn-secondary"><i class="bi bi-cloud-arrow-up"></i> Upload Dokumen</a>
                            <a href="cetak_bukti.php" class="btn btn-success <?php echo ($is_form_filled) ? '' : 'disabled'; ?>" <?php echo ($is_form_filled) ? '' : 'aria-disabled="true"'; ?>><i class="bi bi-printer"></i> Cetak Bukti Pendaftaran</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>