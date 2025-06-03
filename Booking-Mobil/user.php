<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Proses submit booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, pickup_location, destination, date, time, passenger_count, agenda) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user']['id'],
        $_POST['pickup_location'],
        $_POST['destination'],
        $_POST['date'],
        $_POST['time'],
        (int)($_POST['passenger_count'] ?? 1),
        trim($_POST['agenda'] ?? '')
    ]);
    $success = true;
}

// === Pagination untuk Riwayat Booking ===
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Hitung total booking untuk user
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$totalRows = $stmt->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

// Ambil riwayat booking dengan kendaraan jika ada
$stmt = $pdo->prepare("SELECT b.*, v.vehicle_name, v.plate_number
    FROM bookings b
    LEFT JOIN assignments a ON b.id = a.booking_id
    LEFT JOIN vehicles v ON a.vehicle_id = v.id
    WHERE b.user_id = :user_id
    ORDER BY b.date DESC, b.time DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':user_id', $_SESSION['user']['id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$booking_history = $stmt->fetchAll();

// Build base URL for pagination
$queryParams = $_GET;
unset($queryParams['page']);
$baseUrl = $_SERVER['PHP_SELF'] . (http_build_query($queryParams) ? '?' . http_build_query($queryParams) . '&' : '?');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Booking Mobil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body style="background-image: url('mobil.jpg'); background-repeat: no-repeat; background-size: cover;">
    <header class="py-3 mb-4 border-bottom">
        <div class="container d-flex flex-wrap justify-content-center">
            <h3 class="d-flex align-items-center mb-3 mb-lg-0 me-lg-auto text-decoration-none">
                <span class="fs-4">User</span>
            </h3>
            <form class="col-12 col-lg-auto mb-3 mb-lg-0">
                <a href="logout.php" type="button" class="btn btn-danger">Logout</a>
            </form>
        </div>
    </header>

    <div class="container mt-4">
        <h3>Form Booking Mobil</h3>
        <?php if (isset($success)): ?>
            <div class="alert alert-success">Booking berhasil.</div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Lokasi Jemput</label>
                <input type="text" name="pickup_location" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Tujuan</label>
                <input type="text" name="destination" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Jam</label>
                <input type="time" name="time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Tanggal</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Jumlah Penumpang</label>
                <input type="number" name="passenger_count" class="form-control" min="1" value="1" required>
            </div>
            <div class="mb-3">
                <label>Agenda</label>
                <textarea name="agenda" class="form-control" rows="2" placeholder="Isi agenda perjalanan (opsional)"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Booking</button>
        </form>

        <h4 class="mt-5">Riwayat Booking</h4>
        <table class="table table-striped-columns align-middle table-hover">
            <thead>
                <tr>
                    <th>Pickup</th>
                    <th>Tujuan</th>
                    <th>Jam</th>
                    <th>Tanggal</th>
                    <th>Penumpang</th>
                    <th>Agenda</th>
                    <th>Status</th>
                    <th>Mobil</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($booking_history as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['pickup_location']) ?></td>
                        <td><?= htmlspecialchars($b['destination']) ?></td>
                        <td><?= date('H:i', strtotime($b['time'])) ?> WIB</td>
                        <td><?= htmlspecialchars($b['date']) ?></td>
                        <td><?= (int)$b['passenger_count'] ?></td>
                        <td><?= htmlspecialchars($b['agenda']) ?: '-' ?></td>
                        <td><?= htmlspecialchars(ucfirst($b['status'])) ?></td>
                        <td>
                            <?= $b['vehicle_name'] ? htmlspecialchars($b['vehicle_name'] . ' (' . $b['plate_number'] . ')') : '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $page > 1 ? $baseUrl . 'page=' . ($page - 1) : '#' ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl . 'page=' . $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $page < $totalPages ? $baseUrl . 'page=' . ($page + 1) : '#' ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</body>

</html>