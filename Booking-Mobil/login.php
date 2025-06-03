<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bisa login pakai email atau username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$_POST['identity'], $_POST['identity']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Username atau Password salah.";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #343a40;
            color: white;
        }
    </style>
</head>

<body>
    <section class="vh-100 d-flex align-items-center justify-content-center" style="background-image: url('mobil.jpg'); background-size: cover;">
        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card bg-dark text-black rounded">
                        <div class="card-body p-5 text-center">
                            <h3 class="mb-4" style="text-shadow: 0 0 5px rgb(255, 255, 255);">Selamat Datang di Aplikasi Booking Mobil Zam-Zam</h3>
                            <h1 class="fw-bold mb-2 text-uppercase" style="text-shadow: 0 0 9px rgb(255, 255, 255);">Login</h1>

                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="form-group mb-3 text-start">
                                    <label class="text-white">Username</label>
                                    <input type="text" name="identity" class="form-control" required>
                                </div>
                                <div class="form-group mb-4 text-start">
                                    <label class="text-white">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>