<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

$errors = [];
$success = null;

$edit_mode = false;
$edit_id = null;
$edit_data = ['name' => '', 'email' => '', 'username' => '', 'role' => ''];

if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role != 'superadmin'");
    $stmt->execute([$edit_id]);
    $edit_data = $stmt->fetch();

    if (!$edit_data) {
        header("Location: superadmin_manage.php");
        exit();
    }
    $edit_mode = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $id_post = isset($_POST['id']) ? (int)$_POST['id'] : null;

    if (!$name || !$email || !$username || !$role || !in_array($role, ['admin', 'user', 'driver'])) {
        $errors[] = "Isi semua form dengan benar.";
    } else {
        if ($id_post) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (email = ? OR username = ?) AND id != ?");
            $stmt->execute([$email, $username, $id_post]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
        }

        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email atau username sudah terdaftar.";
        } else {
            if ($id_post) {
                if ($password) {
                    $hashed = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, username = ?, password = ?, role = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $username, $hashed, $role, $id_post]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, username = ?, role = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $username, $role, $id_post]);
                }
                $success = "User berhasil diperbarui.";
                $edit_mode = false;
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, username, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $username, $hashed, $role]);
                $success = "User berhasil ditambahkan.";
            }
        }
    }
}

$stmt = $pdo->query("SELECT * FROM users WHERE role != 'superadmin' ORDER BY role, name");
$users = $stmt->fetchAll();

$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $per_page;

$total_stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
$total_bookings = $total_stmt->fetchColumn();
$total_pages = ceil($total_bookings / $per_page);

$stmt = $pdo->prepare("SELECT b.pickup_location, b.destination, b.date, b.time, b.status, 
           u.name as user_name, d.name as driver_name,
           v.vehicle_name, v.plate_number
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    LEFT JOIN assignments a ON b.id = a.booking_id
    LEFT JOIN users d ON a.driver_id = d.id
    LEFT JOIN vehicles v ON a.vehicle_id = v.id
    ORDER BY b.date DESC, b.time DESC
    LIMIT :per_page OFFSET :offset
");
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$booking_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$booking_history) {
    $booking_history = [];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Users & Drivers - Superadmin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body style="background-image: url('mobil.jpg'); background-repeat: no-repeat; background-size: cover; background-size: cover ;">
    <header class="py-3 mb-4 border-bottom">
        <div class="container d-flex flex-wrap justify-content-center">
            <h3 class="d-flex align-items-center mb-3 mb-lg-0 me-lg-auto text-decoration-none">
                <span class="fs-4">Superadmin</span>
            </h3>
            <form class="col-12 col-lg-auto mb-3 mb-lg-0">
                <a href="logout.php" type="button" class="btn btn-danger">Logout</a>
            </form>
        </div>
    </header>

    <div class="container mt-4">
        <!-- <h3>Kelola Users & Drivers</h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?> -->




        <div class="container d-flex flex-wrap justify-content-center">
            <h3 class="d-flex align-items-center mb-3 mb-lg-0 me-lg-auto text-decoration-none">
                <span class="fs-4">Daftar Users & Drivers</span>
            </h3>
            <form class="col-12 col-lg-auto mb-3 mb-lg-0">
                <a href="add_user.php" class="btn btn-primary">+ Tambah User Baru</a>
            </form>
        </div>
        <br>
        <table class="table table-striped-columns table table-hover">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_user.php?id=<?= $u['id'] ?>" onclick="return confirm('Yakin ingin hapus user ini?')" class="btn btn-danger btn-sm">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h4 class="mt-5">Riwayat Booking</h4>
        <table class="table table-striped-columns table table-hover">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Pickup</th>
                    <th>Tujuan</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Status</th>
                    <th>Driver</th>
                    <th>Mobil</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($booking_history as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['user_name']) ?></td>
                        <td><?= htmlspecialchars($b['pickup_location']) ?></td>
                        <td><?= htmlspecialchars($b['destination']) ?></td>
                        <td><?= htmlspecialchars($b['date']) ?></td>
                        <td><?= date('H:i', strtotime($b['time'])) ?> WIB</td>
                        <td><?= htmlspecialchars(ucfirst($b['status'])) ?></td>
                        <td><?= htmlspecialchars($b['driver_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($b['vehicle_name'] ? $b['vehicle_name'] . ' (' . $b['plate_number'] . ')' : '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</body>

</html>