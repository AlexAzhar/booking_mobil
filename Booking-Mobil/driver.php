<?php
session_start();
require_once 'config/db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'driver') {
    header("Location: login.php");
    exit();
}

$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = (int)$_POST['assignment_id'];

    $stmt = $pdo->prepare("UPDATE assignments SET status = 'done' WHERE id = ? AND driver_id = ?");
    $stmt->execute([$assignment_id, $_SESSION['user']['id']]);

    $stmt = $pdo->prepare("SELECT booking_id FROM assignments WHERE id = ?");
    $stmt->execute([$assignment_id]);
    $booking_id = $stmt->fetchColumn();

    if ($booking_id) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?");
        $stmt->execute([$booking_id]);
    }

    $success = "Status tugas diupdate menjadi selesai.";
}

// Ambil tugas aktif (status = 'in_progress')
$stmt = $pdo->prepare("SELECT a.id as assignment_id, b.pickup_location, b.destination, b.date, b.time, u.name as user_name,
                              v.vehicle_name, v.plate_number
    FROM assignments a
    JOIN bookings b ON a.booking_id = b.id
    JOIN users u ON b.user_id = u.id
    LEFT JOIN vehicles v ON a.vehicle_id = v.id
    WHERE a.driver_id = ? AND a.status = 'in_progress'");
$stmt->execute([$_SESSION['user']['id']]);
$tugas = $stmt->fetchAll();

// Pagination untuk riwayat tugas selesai
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Hitung total tugas selesai
$stmt = $pdo->prepare("SELECT COUNT(*) FROM assignments WHERE driver_id = ? AND status = 'done'");
$stmt->execute([$_SESSION['user']['id']]);
$totalRows = $stmt->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

// Ambil riwayat tugas selesai dengan limit & offset
$stmt = $pdo->prepare("SELECT b.pickup_location, b.destination, b.date, b.time, u.name as user_name,
                              v.vehicle_name, v.plate_number
    FROM assignments a
    JOIN bookings b ON a.booking_id = b.id
    JOIN users u ON b.user_id = u.id
    LEFT JOIN vehicles v ON a.vehicle_id = v.id
    WHERE a.driver_id = :driver_id AND a.status = 'done' 
    ORDER BY b.date DESC, b.time DESC
    LIMIT :limit OFFSET :offset");

$stmt->bindValue(':driver_id', $_SESSION['user']['id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$riwayat = $stmt->fetchAll();


// Build base URL for pagination (biar page bisa berganti tapi param lain tetap ada)
$queryParams = $_GET;
unset($queryParams['page']);
$baseUrl = $_SERVER['PHP_SELF'] . (http_build_query($queryParams) ? '?' . http_build_query($queryParams) . '&' : '?');
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tugas Driver</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body style="background-image: url('mobil.jpg'); background-repeat: no-repeat; background-size: cover; background-size: cover ;">
    <header class="py-3 mb-4 border-bottom">
        <div class="container d-flex flex-wrap justify-content-center">
            <h3 class="d-flex align-items-center mb-3 mb-lg-0 me-lg-auto text-decoration-none"> <svg class="bi me-2" width="40" height="32" aria-hidden="true">
                </svg> <span class="fs-4">Driver</span>
            </h3>
            <form class="col-12 col-lg-auto mb-3 mb-lg-0"> <a href="logout.php" type="button" class="btn btn-danger">Logout</a></form>
        </div>
    </header>

    <div class="container mt-4">
        <h3>Tugas Saya</h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (count($tugas) === 0): ?>
            <div class="alert alert-info">Tidak ada tugas aktif.</div>
        <?php else: ?>
            <table class="table table-striped-columns align-middle table table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Lokasi Jemput</th>
                        <th>Tujuan</th>
                        <th>Jam</th>
                        <th>Tanggal</th>
                        <th>Mobil</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tugas as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['user_name']) ?></td>
                            <td><?= htmlspecialchars($t['pickup_location']) ?></td>
                            <td><?= htmlspecialchars($t['destination']) ?></td>
                            <td><?= date('H:i', strtotime($t['time'])) ?> WIB</td>
                            <td><?= htmlspecialchars($t['date']) ?></td>
                            <td><?= htmlspecialchars($t['vehicle_name'] ? $t['vehicle_name'] . ' (' . $t['plate_number'] . ')' : '-') ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Konfirmasi tugas selesai?')">
                                    <input type="hidden" name="assignment_id" value="<?= $t['assignment_id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Tandai Selesai</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h4 class="mt-5">Riwayat Tugas Selesai</h4>

        <?php if (count($riwayat) === 0): ?>
            <div class="alert alert-info">Belum ada tugas yang diselesaikan.</div>
        <?php else: ?>
            <table class="table table-striped-columns align-middle table table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Pickup</th>
                        <th>Tujuan</th>
                        <th>Jam</th>
                        <th>Tanggal</th>
                        <th>Mobil</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['user_name']) ?></td>
                            <td><?= htmlspecialchars($r['pickup_location']) ?></td>
                            <td><?= htmlspecialchars($r['destination']) ?></td>
                            <td><?= date('H:i', strtotime($r['time'])) ?> WIB</td>
                            <td><?= htmlspecialchars($r['date']) ?></td>
                            <td><?= htmlspecialchars($r['vehicle_name'] ? $r['vehicle_name'] . ' (' . $r['plate_number'] . ')' : '-') ?></td>
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
        <?php endif; ?>
    </div>
</body>

</html>