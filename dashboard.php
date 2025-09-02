<?php
require_once '../config.php';

// Cek sesi admin
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Ambil data statistik
// 1. Jumlah Total Pendaftar (dari tabel users)
$total_pendaftar = 0;
$sql_total = "SELECT COUNT(id) as total FROM users";
if ($result = mysqli_query($conn, $sql_total)) {
    $row = mysqli_fetch_assoc($result);
    $total_pendaftar = $row['total'];
}

// 2. Jumlah Diterima, Tidak Diterima, Belum Diverifikasi (dari tabel seleksi)
$diterima = $tidak_diterima = $belum_diverifikasi = 0;
$sql_seleksi = "SELECT status_seleksi, COUNT(id) as jumlah FROM seleksi GROUP BY status_seleksi";
if ($result = mysqli_query($conn, $sql_seleksi)) {
    while ($row = mysqli_fetch_assoc($result)) {
        switch ($row['status_seleksi']) {
            case 'Diterima':
                $diterima = $row['jumlah'];
                break;
            case 'Tidak diterima':
                $tidak_diterima = $row['jumlah'];
                break;
            case 'Belum diverifikasi':
                $belum_diverifikasi = $row['jumlah'];
                break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PPDB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 280px;
            background: #343a40;
            color: white;
        }
        .sidebar .nav-link {
            color: #adb5bd;
        }
        .sidebar .nav-link.active,
        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: #495057;
        }
        .content {
            flex-grow: 1;
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="sidebar d-flex flex-column p-3">
        <h4><i class="bi bi-person-gear"></i> Admin PPDB</h4>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active" aria-current="page">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="pendaftar.php" class="nav-link">
                    <i class="bi bi-people"></i> Manajemen Pendaftar
                </a>
            </li>
            <li>
                <a href="pengaturan.php" class="nav-link">
                    <i class="bi bi-sliders"></i> Pengaturan PPDB
                </a>
            </li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-2"></i>
                <strong><?php echo htmlspecialchars($_SESSION["admin_nama"]); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li>
                    <form action="logout.php" method="post" class="d-inline">
                        <button type="submit" class="dropdown-item">Sign out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <div class="content">
        <h1 class="mb-4">Dashboard</h1>
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person-plus"></i> Total Pendaftar</h5>
                        <p class="card-text fs-2"><?php echo $total_pendaftar; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person-check"></i> Diterima</h5>
                        <p class="card-text fs-2"><?php echo $diterima; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person-x"></i> Tidak Diterima</h5>
                        <p class="card-text fs-2"><?php echo $tidak_diterima; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-dark bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-hourglass-split"></i> Belum Diverifikasi</h5>
                        <p class="card-text fs-2"><?php echo $belum_diverifikasi; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <h2>Pendaftar Terbaru</h2>
            <p>Fitur ini akan menampilkan daftar pendaftar terbaru di sini.</p>
            <!-- Tabel pendaftar terbaru akan ditambahkan di sini nanti -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
