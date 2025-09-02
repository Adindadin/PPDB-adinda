<?php
require_once 'config.php';

// Cek sesi pendaftar
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Ambil semua data pendaftar
$data = [];
$sql = "SELECT u.nama_lengkap, u.email, u.nik_user, u.created_at as tgl_daftar, 
               p.*, o.*, s.status_berkas, s.status_seleksi
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

    // Cek jika pendaftar sudah mengisi formulir atau belum
    if (!$data || empty($data['nik'])) {
        echo "<div style='text-align:center; margin-top: 50px;'><h1>Akses Ditolak</h1><p>Anda harus melengkapi formulir pendaftaran terlebih dahulu sebelum dapat mencetak bukti.</p><a href='dashboard.php'>Kembali ke Dashboard</a></div>";
        exit;
    }
}

// Data untuk QR Code (contoh: ID Pendaftaran)
$qr_data = "ID Pendaftaran: " . $user_id . "\nNama: " . $data['nama_lengkap'];
$qr_data_encoded = urlencode($qr_data);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pendaftaran - <?php echo htmlspecialchars($data['nama_lengkap']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #e9ecef; }
        .paper-container { max-width: 800px; margin: 30px auto; background: white; padding: 40px; border-radius: 5px; }
        .header-doc {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .header-doc h3 { margin: 0; font-weight: bold; }
        .header-doc p { margin: 0; }
        .data-table { width: 100%; }
        .data-table td { padding: 8px 0; vertical-align: top; }
        .data-table td:first-child { width: 30%; color: #555; }
        .data-table td:nth-child(2) { width: 2%; }
        .footer-doc { margin-top: 50px; text-align: center; }
        .qr-code { float: right; }

        @media print {
            body { background-color: white; }
            .paper-container { margin: 0; padding: 0; border: none; box-shadow: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="paper-container">
        <div class="header-doc">
            <h3>BUKTI PENDAFTARAN SISWA BARU</h3>
            <p>PPDB ONLINE TAHUN AJARAN 2025/2026</p>
        </div>

        <h4>A. DATA CALON SISWA</h4>
        <table class="data-table">
            <tr><td>Nama Lengkap</td><td>:</td><td><strong><?php echo htmlspecialchars($data['nama_lengkap']); ?></strong></td></tr>
            <tr><td>NIK</td><td>:</td><td><?php echo htmlspecialchars($data['nik']); ?></td></tr>
            <tr><td>NISN</td><td>:</td><td><?php echo htmlspecialchars($data['nisn'] ?? '-'); ?></td></tr>
            <tr><td>Tempat, Tanggal Lahir</td><td>:</td><td><?php echo htmlspecialchars($data['tempat_lahir']) . ", " . date("d F Y", strtotime($data['tanggal_lahir'])); ?></td></tr>
            <tr><td>Jenis Kelamin</td><td>:</td><td><?php echo htmlspecialchars($data['jenis_kelamin']); ?></td></tr>
            <tr><td>Asal Sekolah</td><td>:</td><td><?php echo htmlspecialchars($data['asal_sekolah']); ?></td></tr>
            <tr><td>Nilai Rata-rata</td><td>:</td><td><?php echo htmlspecialchars($data['nilai_rata_rata']); ?></td></tr>
        </table>

        <h4 class="mt-4">B. STATUS PENDAFTARAN</h4>
        <table class="data-table">
            <tr><td>Status Berkas</td><td>:</td><td><span class="badge bg-primary"><?php echo htmlspecialchars($data['status_berkas']); ?></span></td></tr>
            <tr><td>Status Seleksi</td><td>:</td><td><span class="badge bg-success"><?php echo htmlspecialchars($data['status_seleksi']); ?></span></td></tr>
        </table>

        <div class="footer-doc">
            <div class="qr-code">
                <img src="https://chart.googleapis.com/chart?chs=120x120&cht=qr&chl=<?php echo $qr_data_encoded; ?>" alt="QR Code">
            </div>
            <p>Dicetak pada: <?php echo date("d F Y, H:i:s"); ?></p>
            <p>Harap simpan bukti pendaftaran ini dengan baik.</p>
        </div>
    </div>

    <div class="text-center my-4 no-print">
        <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        <button onclick="window.print()" class="btn btn-primary">Cetak atau Simpan sebagai PDF</button>
    </div>
</body>
</html>
