<?php
require_once '../config.php';

// Cek sesi admin
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    // Jika tidak ada sesi, mungkin ini adalah akses tidak sah. Bisa redirect atau die.
    die("Akses ditolak. Silakan login sebagai admin.");
}

// Ambil filter dari URL jika ada
$search_term = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Bangun query untuk mengambil semua data yang relevan
$sql = "SELECT 
            u.id as user_id, u.nama_lengkap, u.email, u.nik_user, u.created_at as tgl_registrasi,
            p.nik, p.tempat_lahir, p.tanggal_lahir, p.jenis_kelamin, p.alamat, p.asal_sekolah, p.nilai_rata_rata, p.nisn,
            o.nama_ayah, o.pekerjaan_ayah, o.penghasilan_ayah, o.nama_ibu, o.pekerjaan_ibu, o.penghasilan_ibu,
            s.status_berkas, s.status_seleksi, s.catatan_admin, s.tgl_verifikasi, a.username as diverifikasi_oleh
        FROM users u 
        LEFT JOIN pendaftar p ON u.id = p.user_id 
        LEFT JOIN orang_tua o ON u.id = o.user_id
        LEFT JOIN seleksi s ON u.id = s.user_id
        LEFT JOIN admin a ON s.admin_id = a.id";

$where_clauses = [];
$params = [];
$types = '';

if (!empty($search_term)) {
    $where_clauses[] = "(u.nama_lengkap LIKE ? OR u.email LIKE ? OR p.nik LIKE ?)";
    $search_like = "%{$search_term}%";
    $params[] = &$search_like;
    $params[] = &$search_like;
    $params[] = &$search_like;
    $types .= 'sss';
}

if (!empty($filter_status)) {
    $where_clauses[] = "s.status_seleksi = ?";
    $params[] = &$filter_status;
    $types .= 's';
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY u.nama_lengkap ASC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Nama file untuk diunduh
$filename = "data_pendaftar_" . date('Ymd') . ".csv";

// Set header untuk memicu unduhan file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Buka output stream PHP
$output = fopen('php://output', 'w');

// Tulis baris header CSV
fputcsv($output, [
    'ID Pendaftar', 'Nama Lengkap', 'Email', 'NIK Akun', 'Tanggal Registrasi',
    'NIK Form', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin', 'Alamat', 'Asal Sekolah', 'Nilai Rata-rata', 'NISN',
    'Nama Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah', 'Nama Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu',
    'Status Berkas', 'Status Seleksi', 'Catatan Admin', 'Tanggal Verifikasi', 'Diverifikasi Oleh'
]);

// Tulis setiap baris data pendaftar ke file CSV
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

// Tutup statement dan koneksi
mysqli_stmt_close($stmt);
mysqli_close($conn);
exit();
?>
