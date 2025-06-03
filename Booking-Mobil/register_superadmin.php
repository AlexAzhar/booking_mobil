<?php
require_once 'config/db.php';

// Cek apakah sudah ada superadmin, jika sudah, redirect ke login
$stmt = $pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'superadmin'");
$count = $stmt->fetch()['count'];


$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if (!$name || !$email || !$password || !$password2) {
        $errors[] = "Semua kolom wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    } elseif ($password !== $password2) {
        $errors[] = "Password dan konfirmasi password tidak sama.";
    } else {
        // Cek email sudah dipakai atau belum
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email sudah digunakan.";
        } else {
            // Simpan superadmin
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'superadmin')");
            $stmt->execute([$name, $email, $hash]);
            $success = "Superadmin berhasil dibuat. Silakan login.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Registrasi Superadmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container mt-5">
        <h3>Registrasi Superadmin Pertama</h3>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <a href="login.php" class="btn btn-primary">Ke Halaman Login</a>
        <?php else: ?>

            <form method="POST">
                <div class="mb-3">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" required value="<?= isset($name) ? htmlspecialchars($name) : '' ?>">
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password2" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Daftar Superadmin</button>
            </form>

        <?php endif; ?>
    </div>
</body>

</html>