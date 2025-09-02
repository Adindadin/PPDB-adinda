<?php
require_once 'config.php';

// Cek jika pengguna tidak login, arahkan ke halaman login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$errors = [];
$success_message = "";

// Inisialisasi variabel untuk data form
$nik = $tempat_lahir = $tanggal_lahir = $jenis_kelamin = $alamat = $asal_sekolah = $nilai_rata_rata = $nisn = "";
$nama_ayah = $pekerjaan_ayah = $penghasilan_ayah = $nama_ibu = $pekerjaan_ibu = $penghasilan_ibu = "";

// Cek apakah data sudah ada (mode edit)
$is_edit_mode = false;
$sql_fetch = "SELECT p.*, o.nama_ayah, o.pekerjaan_ayah, o.penghasilan_ayah, o.nama_ibu, o.pekerjaan_ibu, o.penghasilan_ibu 
              FROM pendaftar p 
              LEFT JOIN orang_tua o ON p.user_id = o.user_id
              WHERE p.user_id = ?";
if ($stmt_fetch = mysqli_prepare($conn, $sql_fetch)) {
    mysqli_stmt_bind_param($stmt_fetch, "i", $user_id);
    if (mysqli_stmt_execute($stmt_fetch)) {
        $result = mysqli_stmt_get_result($stmt_fetch);
        if ($row = mysqli_fetch_assoc($result)) {
            $is_edit_mode = true;
            // Isi variabel dari database
            $nik = $row['nik'];
            $tempat_lahir = $row['tempat_lahir'];
            $tanggal_lahir = $row['tanggal_lahir'];
            $jenis_kelamin = $row['jenis_kelamin'];
            $alamat = $row['alamat'];
            $asal_sekolah = $row['asal_sekolah'];
            $nilai_rata_rata = $row['nilai_rata_rata'];
            $nisn = $row['nisn'];
            $nama_ayah = $row['nama_ayah'];
            $pekerjaan_ayah = $row['pekerjaan_ayah'];
            $penghasilan_ayah = $row['penghasilan_ayah'];
            $nama_ibu = $row['nama_ibu'];
            $pekerjaan_ibu = $row['pekerjaan_ibu'];
            $penghasilan_ibu = $row['penghasilan_ibu'];
        }
    }
    mysqli_stmt_close($stmt_fetch);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan bersihkan data dari form
    $nik = trim($_POST['nik']);
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tanggal_lahir = trim($_POST['tanggal_lahir']);
    $jenis_kelamin = trim($_POST['jenis_kelamin']);
    $alamat = trim($_POST['alamat']);
    $asal_sekolah = trim($_POST['asal_sekolah']);
    $nilai_rata_rata = trim($_POST['nilai_rata_rata']);
    $nisn = trim($_POST['nisn']);
    $nama_ayah = trim($_POST['nama_ayah']);
    $pekerjaan_ayah = trim($_POST['pekerjaan_ayah']);
    $penghasilan_ayah = trim($_POST['penghasilan_ayah']);
    $nama_ibu = trim($_POST['nama_ibu']);
    $pekerjaan_ibu = trim($_POST['pekerjaan_ibu']);
    $penghasilan_ibu = trim($_POST['penghasilan_ibu']);

    // Validasi sederhana (bisa ditambahkan lebih banyak)
    if (empty($nik) || empty($tempat_lahir) || empty($tanggal_lahir) || empty($jenis_kelamin) || empty($alamat) || empty($asal_sekolah) || empty($nilai_rata_rata)) {
        $errors[] = "Semua field yang bertanda (*) wajib diisi.";
    }

    if (empty($errors)) {
        if ($is_edit_mode) {
            // Mode UPDATE
            $sql_pendaftar = "UPDATE pendaftar SET nik=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, alamat=?, asal_sekolah=?, nilai_rata_rata=?, nisn=? WHERE user_id=?";
            $sql_ortu = "UPDATE orang_tua SET nama_ayah=?, pekerjaan_ayah=?, penghasilan_ayah=?, nama_ibu=?, pekerjaan_ibu=?, penghasilan_ibu=? WHERE user_id=?";
        } else {
            // Mode INSERT
            $sql_pendaftar = "INSERT INTO pendaftar (nik, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, asal_sekolah, nilai_rata_rata, nisn, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sql_ortu = "INSERT INTO orang_tua (nama_ayah, pekerjaan_ayah, penghasilan_ayah, nama_ibu, pekerjaan_ibu, penghasilan_ibu, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        }

        // Transaksi Database
        mysqli_begin_transaction($conn);
        try {
            // Eksekusi tabel pendaftar
            $stmt_pendaftar = mysqli_prepare($conn, $sql_pendaftar);
            mysqli_stmt_bind_param($stmt_pendaftar, "ssssssdsi", $nik, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $asal_sekolah, $nilai_rata_rata, $nisn, $user_id);
            mysqli_stmt_execute($stmt_pendaftar);

            // Eksekusi tabel orang_tua
            $stmt_ortu = mysqli_prepare($conn, $sql_ortu);
            mysqli_stmt_bind_param($stmt_ortu, "ssssssi", $nama_ayah, $pekerjaan_ayah, $penghasilan_ayah, $nama_ibu, $pekerjaan_ibu, $penghasilan_ibu, $user_id);
            mysqli_stmt_execute($stmt_ortu);

            // Jika ini adalah insert baru, buat juga record di tabel seleksi
            if (!$is_edit_mode) {
                $sql_seleksi = "INSERT INTO seleksi (user_id, status_seleksi, status_berkas) VALUES (?, 'Belum diverifikasi', 'Belum diperiksa')";
                $stmt_seleksi = mysqli_prepare($conn, $sql_seleksi);
                mysqli_stmt_bind_param($stmt_seleksi, "i", $user_id);
                mysqli_stmt_execute($stmt_seleksi);
            }

            mysqli_commit($conn);
            $success_message = "Data Anda berhasil disimpan!";
            header("Location: dashboard.php?status=success");
            exit;

        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($conn);
            $errors[] = "Terjadi kesalahan saat menyimpan data: " . $exception->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><strong>PPDB Online</strong></a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Kembali ke Dashboard</a></li>
                <li class="nav-item"><a href="logout.php" class="btn btn-danger">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <h2>Formulir Pendaftaran Siswa Baru</h2>
        <p>Harap isi semua data dengan benar. Pola isian dengan tanda (*) wajib diisi.</p>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <!-- Data Pribadi -->
            <div class="card mb-4">
                <div class="card-header"><h4>1. Data Pribadi</h4></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap (sesuai Akta)</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>" disabled readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" disabled readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIK (*)</label>
                            <input type="text" name="nik" class="form-control" value="<?php echo htmlspecialchars($nik); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NISN (jika ada)</label>
                            <input type="text" name="nisn" class="form-control" value="<?php echo htmlspecialchars($nisn); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tempat Lahir (*)</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="<?php echo htmlspecialchars($tempat_lahir); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Lahir (*)</label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo htmlspecialchars($tanggal_lahir); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jenis Kelamin (*)</label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="" disabled <?php echo empty($jenis_kelamin) ? 'selected' : ''; ?>>-- Pilih --</option>
                                <option value="Laki-laki" <?php echo ($jenis_kelamin == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?php echo ($jenis_kelamin == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Alamat Lengkap (*)</label>
                            <textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($alamat); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Orang Tua -->
            <div class="card mb-4">
                <div class="card-header"><h4>2. Data Orang Tua / Wali</h4></div>
                <div class="card-body">
                    <div class="row">
                        <h5 class="mb-3">Data Ayah</h5>
                        <div class="col-md-6 mb-3"><label class="form-label">Nama Ayah</label><input type="text" name="nama_ayah" class="form-control" value="<?php echo htmlspecialchars($nama_ayah); ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Pekerjaan Ayah</label><input type="text" name="pekerjaan_ayah" class="form-control" value="<?php echo htmlspecialchars($pekerjaan_ayah); ?>"></div>
                        <div class="col-md-12 mb-3"><label class="form-label">Penghasilan Ayah</label><input type="text" name="penghasilan_ayah" class="form-control" value="<?php echo htmlspecialchars($penghasilan_ayah); ?>"></div>
                        <hr class="my-4">
                        <h5 class="mb-3">Data Ibu</h5>
                        <div class="col-md-6 mb-3"><label class="form-label">Nama Ibu</label><input type="text" name="nama_ibu" class="form-control" value="<?php echo htmlspecialchars($nama_ibu); ?>"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Pekerjaan Ibu</label><input type="text" name="pekerjaan_ibu" class="form-control" value="<?php echo htmlspecialchars($pekerjaan_ibu); ?>"></div>
                        <div class="col-md-12 mb-3"><label class="form-label">Penghasilan Ibu</label><input type="text" name="penghasilan_ibu" class="form-control" value="<?php echo htmlspecialchars($penghasilan_ibu); ?>"></div>
                    </div>
                </div>
            </div>

            <!-- Data Akademik -->
            <div class="card mb-4">
                <div class="card-header"><h4>3. Data Akademik</h4></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asal Sekolah (*)</label>
                            <input type="text" name="asal_sekolah" class="form-control" value="<?php echo htmlspecialchars($asal_sekolah); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nilai Rata-rata Raport (*)</label>
                            <input type="number" step="0.01" name="nilai_rata_rata" class="form-control" value="<?php echo htmlspecialchars($nilai_rata_rata); ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Simpan Data Formulir</button>
            </div>
        </form>
    </div>
</body>
</html>
