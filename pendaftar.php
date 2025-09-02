<?php
require_once '../config.php';

// Cek sesi admin
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Inisialisasi variabel pencarian dan filter
$search_term = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Bangun query dasar
$sql = "SELECT u.id, u.nama_lengkap, u.email, p.asal_sekolah, s.status_seleksi, s.status_berkas 
        FROM users u 
        LEFT JOIN pendaftar p ON u.id = p.user_id 
        LEFT JOIN seleksi s ON u.id = s.user_id";

$where_clauses = [];
$params = [];
$types = '';

// Tambahkan kondisi pencarian
if (!empty($search_term)) {
    $where_clauses[] = "(u.nama_lengkap LIKE ? OR u.email LIKE ? OR p.nik LIKE ?)";
    $search_like = "%{$search_term}%";
    $params[] = &$search_like;
    $params[] = &$search_like;
    $params[] = &$search_like;
    $types .= 'sss';
}

// Tambahkan kondisi filter status
if (!empty($filter_status)) {
    $where_clauses[] = "s.status_seleksi = ?";
    $params[] = &$filter_status;
    $types .= 's';
}

// Gabungkan klausa WHERE
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY u.created_at DESC";

// Eksekusi query
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pendaftar_list = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pendaftar - PPDB</title>
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
        <h4><i class="bi bi-person-gear"></i> Admin PPDB</h4>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="pendaftar.php" class="nav-link active" aria-current="page"><i class="bi bi-people"></i> Manajemen Pendaftar</a></li>
            <li><a href="pengaturan.php" class="nav-link"><i class="bi bi-sliders"></i> Pengaturan PPDB</a></li>
        </ul>
        <hr>
        <div>
            <form action="logout.php" method="post">
                <button type="submit" class="btn btn-danger w-100"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </div>
    </div>

    <div class="content">
        <h1 class="mb-4">Manajemen Pendaftar</h1>

        <div class="card mb-4">
            <div class="card-body">
                <form action="pendaftar.php" method="GET" class="row gx-3 gy-2 align-items-center">
                    <div class="col-sm-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama, Email, atau NIK..." value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>
                    <div class="col-sm-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status Seleksi</option>
                            <option value="Belum diverifikasi" <?php echo ($filter_status == 'Belum diverifikasi') ? 'selected' : ''; ?>>Belum diverifikasi</option>
                            <option value="Diterima" <?php echo ($filter_status == 'Diterima') ? 'selected' : ''; ?>>Diterima</option>
                            <option value="Tidak diterima" <?php echo ($filter_status == 'Tidak diterima') ? 'selected' : ''; ?>>Tidak diterima</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                    <div class="col-auto">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-download"></i> Ekspor Data
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="export.php?search=<?php echo urlencode($search_term); ?>&status=<?php echo urlencode($filter_status); ?>">Ekspor Hasil Pencarian (CSV)</a></li>
                                <li><a class="dropdown-item" href="export.php">Ekspor Semua (CSV)</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="export.php?status=Diterima">Hanya yang Diterima (CSV)</a></li>
                            </ul>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Daftar Calon Siswa</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Asal Sekolah</th>
                                <th>Status Berkas</th>
                                <th>Status Seleksi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendaftar_list)): ?>
                                <tr><td colspan="7" class="text-center">Tidak ada data untuk ditampilkan.</td></tr>
                            <?php else: ?>
                                <?php foreach ($pendaftar_list as $index => $pendaftar): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($pendaftar['email']); ?></td>
                                        <td><?php echo htmlspecialchars($pendaftar['asal_sekolah'] ?? '-'); ?></td>
                                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($pendaftar['status_berkas'] ?? 'Belum Isi Form'); ?></span></td>
                                        <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars($pendaftar['status_seleksi'] ?? 'Belum Isi Form'); ?></span></td>
                                        <td>
                                            <a href="detail_pendaftar.php?id=<?php echo $pendaftar['id']; ?>" class="btn btn-sm btn-info">Lihat Detail</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
