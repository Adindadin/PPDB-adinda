<?php
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$errors = [];
$success_message = "";

// Define document types
$doc_types = [
    'kartu_keluarga' => 'Kartu Keluarga',
    'akta_lahir' => 'Akta Lahir',
    'raport' => 'Raport Terakhir',
    'ijazah' => 'Ijazah (jika ada)',
    'pas_foto' => 'Pas Foto (3x4)'
];

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doc_key = $_POST['doc_type'];
    if (isset($_FILES[$doc_key]) && $_FILES[$doc_key]['error'] == 0) {
        $target_dir = "uploads/" . $user_id . "/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file = $_FILES[$doc_key];
        $file_name = uniqid() . '-' . basename($file["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validations
        $allowed_types = ['jpg', 'png', 'jpeg', 'pdf'];
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Hanya file JPG, PNG, JPEG, & PDF yang diizinkan.";
        }
        if ($file["size"] > 5000000) { // 5MB
            $errors[] = "Ukuran file maksimal adalah 5MB.";
        }

        if (empty($errors)) {
            // Check if document already exists
            $sql_check = "SELECT id, path_file FROM dokumen WHERE user_id = ? AND jenis_dokumen = ?";
            $stmt_check = mysqli_prepare($conn, $sql_check);
            mysqli_stmt_bind_param($stmt_check, "is", $user_id, $doc_key);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            $existing_doc = mysqli_fetch_assoc($result_check);
            mysqli_stmt_close($stmt_check);

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                if ($existing_doc) {
                    // Update existing record and delete old file
                    $old_file_path = $existing_doc['path_file'];
                    $sql_update = "UPDATE dokumen SET path_file = ? WHERE id = ?";
                    $stmt_update = mysqli_prepare($conn, $sql_update);
                    mysqli_stmt_bind_param($stmt_update, "si", $target_file, $existing_doc['id']);
                    if (mysqli_stmt_execute($stmt_update)) {
                        if (file_exists($old_file_path)) {
                            unlink($old_file_path);
                        }
                        $success_message = "Dokumen berhasil diperbarui.";
                    }
                    mysqli_stmt_close($stmt_update);
                } else {
                    // Insert new record
                    $sql_insert = "INSERT INTO dokumen (user_id, jenis_dokumen, path_file) VALUES (?, ?, ?)";
                    $stmt_insert = mysqli_prepare($conn, $sql_insert);
                    mysqli_stmt_bind_param($stmt_insert, "iss", $user_id, $doc_key, $target_file);
                    mysqli_stmt_execute($stmt_insert);
                    $success_message = "Dokumen berhasil diunggah.";
                    mysqli_stmt_close($stmt_insert);
                }
            } else {
                $errors[] = "Terjadi kesalahan saat mengunggah file.";
            }
        }
    }
}

// Fetch uploaded documents
$uploaded_docs = [];
$sql_fetch = "SELECT jenis_dokumen, path_file FROM dokumen WHERE user_id = ?";
if ($stmt_fetch = mysqli_prepare($conn, $sql_fetch)) {
    mysqli_stmt_bind_param($stmt_fetch, "i", $user_id);
    mysqli_stmt_execute($stmt_fetch);
    $result = mysqli_stmt_get_result($stmt_fetch);
    while ($row = mysqli_fetch_assoc($result)) {
        $uploaded_docs[$row['jenis_dokumen']] = $row['path_file'];
    }
    mysqli_stmt_close($stmt_fetch);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Dokumen Pendaftaran</title>
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
        <h2>Upload Dokumen Persyaratan</h2>
        <p>Unggah semua dokumen yang diperlukan. Ukuran file maksimal 5MB (format: PDF, JPG, PNG).</p>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach($errors as $error): ?><p class="mb-0"><?php echo $error; ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($doc_types as $key => $value): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h5><?php echo $value; ?></h5>
                                <?php if (isset($uploaded_docs[$key])): ?>
                                    <span class="badge bg-success">Sudah Diunggah</span>
                                    <a href="<?php echo htmlspecialchars($uploaded_docs[$key]); ?>" target="_blank" class="ms-2">Lihat File</a>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Belum Diunggah</span>
                                <?php endif; ?>
                            </div>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="doc_type" value="<?php echo $key; ?>">
                                <div class="input-group">
                                    <input type="file" class="form-control" name="<?php echo $key; ?>" required>
                                    <button class="btn btn-primary" type="submit">Upload</button>
                                </div>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="mt-4 text-center">
             <a href="dashboard.php" class="btn btn-secondary">&laquo; Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>