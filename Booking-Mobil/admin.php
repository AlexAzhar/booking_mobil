<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'superadmin'])) {
    header("Location: login.php");
    exit();
}

$success = null;

// Proses assign jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $driver_id = (int)($_POST['driver_id'] ?? 0);
    $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);

    if ($booking_id && $driver_id && $vehicle_id) {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND status = 'pending'");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT 1 FROM assignments a JOIN bookings b ON a.booking_id = b.id 
            WHERE a.driver_id = ? AND b.date = ? AND b.time = ? AND b.status = 'assigned'");
        $stmt->execute([$driver_id, $booking['date'], $booking['time']]);
        $driver_conflict = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT 1 FROM assignments a JOIN bookings b ON a.booking_id = b.id 
            WHERE a.vehicle_id = ? AND b.date = ? AND b.time = ? AND b.status = 'assigned'");
        $stmt->execute([$vehicle_id, $booking['date'], $booking['time']]);
        $vehicle_conflict = $stmt->fetch();

        if ($booking && !$driver_conflict && !$vehicle_conflict) {
            $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
            $stmt->execute([$vehicle_id]);
            $vehicle = $stmt->fetch();

            if ($vehicle) {
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'assigned' WHERE id = ?");
                $stmt->execute([$booking_id]);

                $stmt = $pdo->prepare("INSERT INTO assignments (booking_id, driver_id, vehicle_id) VALUES (?, ?, ?)");
                $stmt->execute([$booking_id, $driver_id, $vehicle_id]);

                $success = "Driver dan mobil berhasil ditugaskan.";
            }
        } else {
            $error_msg = [];
            if ($driver_conflict) $error_msg[] = "Driver tidak tersedia.";
            if ($vehicle_conflict) $error_msg[] = "Mobil tidak tersedia.";
            $success = "<b style='color:red;'>" . implode(" ", $error_msg) . "</b>";
        }
    }
}

// Ambil booking pending
$stmt = $pdo->query("SELECT b.*, u.name AS user_name FROM bookings b 
    JOIN users u ON b.user_id = u.id WHERE b.status = 'pending' ORDER BY b.date ASC, b.time ASC");
$pending_bookings = $stmt->fetchAll();

// PAGINASI RIWAYAT
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
$totalBookings = $stmt->fetchColumn();
$totalPages = ceil($totalBookings / $perPage);

$stmt = $pdo->prepare("SELECT b.pickup_location, b.destination, b.date, b.time, b.status, 
                            b.passenger_count, b.agenda,
                            u.name AS user_name, d.name AS driver_name, v.vehicle_name, v.plate_number
                     FROM bookings b
                     JOIN users u ON b.user_id = u.id
                     LEFT JOIN assignments a ON b.id = a.booking_id
                     LEFT JOIN users d ON a.driver_id = d.id
                     LEFT JOIN vehicles v ON a.vehicle_id = v.id
                     ORDER BY b.date DESC, b.time DESC
                     LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$booking_history = $stmt->fetchAll();
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
$baseUrl = $_SERVER['PHP_SELF'] . ($queryString ? '?' . $queryString . '&' : '?');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Admin Booking - Assign</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body style="background-image: url('mobil.jpg'); background-repeat: no-repeat; background-size: cover;">
    <header class="py-3 mb-4 border-bottom">
        <div class="container d-flex flex-wrap justify-content-center">
            <h3 class="d-flex align-items-center me-lg-auto">Admin</h3>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </header>

    <div class="container mt-4">
        <h3>Booking Pending - Assign Driver & Mobil</h3>

        <?php if ($success): ?>
            <div class="alert alert-info"><?= $success ?></div>
        <?php endif; ?>

        <?php if (count($pending_bookings) === 0): ?>
            <div class="alert alert-warning">Tidak ada booking pending.</div>
        <?php else: ?>
            <table class="table table-striped-columns align-middle table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Lokasi Jemput</th>
                        <th>Tujuan</th>
                        <th>Jam</th>
                        <th>Tanggal</th>
                        <th>Penumpang</th>
                        <th>Agenda</th>
                        <th>Assign</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_bookings as $b): ?>
                        <?php
                        // Driver tersedia
                        $stmt = $pdo->prepare("
                    SELECT u.id, u.name FROM users u
                    WHERE u.role = 'driver' AND u.id NOT IN (
                        SELECT a.driver_id FROM assignments a
                        JOIN bookings b ON a.booking_id = b.id
                        WHERE b.date = ? AND b.time = ? AND b.status = 'assigned'
                    )
                ");
                        $stmt->execute([$b['date'], $b['time']]);
                        $available_drivers = $stmt->fetchAll();

                        // Mobil tersedia
                        $stmt = $pdo->prepare("
                    SELECT v.id, v.vehicle_name, v.plate_number FROM vehicles v
                    WHERE v.id NOT IN (
                        SELECT a.vehicle_id FROM assignments a
                        JOIN bookings b ON a.booking_id = b.id
                        WHERE b.date = ? AND b.time = ? AND b.status = 'assigned'
                    )
                    ORDER BY v.vehicle_name
                ");
                        $stmt->execute([$b['date'], $b['time']]);
                        $available_vehicles = $stmt->fetchAll();
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($b['user_name']) ?></td>
                            <td><?= htmlspecialchars($b['pickup_location']) ?></td>
                            <td><?= htmlspecialchars($b['destination']) ?></td>
                            <td><?= date('H:i', strtotime($b['time'])) ?> WIB</td>
                            <td><?= htmlspecialchars($b['date']) ?></td>
                            <td><?= htmlspecialchars($b['passenger_count'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($b['agenda'] ?? '-') ?></td>
                            <td>
                                <form method="POST" class="d-flex gap-2 align-items-center">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <select name="driver_id" class="form-select" required>
                                        <option value="">Pilih Driver</option>
                                        <?php foreach ($available_drivers as $d): ?>
                                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="vehicle_id" class="form-select" required>
                                        <option value="">Pilih Mobil</option>
                                        <?php foreach ($available_vehicles as $v): ?>
                                            <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['vehicle_name'] . ' (' . $v['plate_number'] . ')') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Assign</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h4 class="mt-5">Mobil Tidak Sedang Bertugas Hari Ini (<?= date('Y-m-d') ?>)</h4>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Mobil</th>
                    <th>Plat Nomor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $today = date('Y-m-d');
                $stmt = $pdo->prepare("
            SELECT v.vehicle_name, v.plate_number
            FROM vehicles v
            WHERE v.id NOT IN (
                SELECT a.vehicle_id
                FROM assignments a
                JOIN bookings b ON a.booking_id = b.id
                WHERE b.date = ? AND b.status = 'assigned'
            )
            ORDER BY v.vehicle_name
        ");
                $stmt->execute([$today]);
                $free_vehicles = $stmt->fetchAll();
                ?>

                <?php if (count($free_vehicles) === 0): ?>
                    <tr>
                        <td colspan="2" class="text-center">Semua mobil sedang digunakan hari ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($free_vehicles as $v): ?>
                        <tr>
                            <td><?= htmlspecialchars($v['vehicle_name']) ?></td>
                            <td><?= htmlspecialchars($v['plate_number']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>


        <a href="tambah_mobil.php" class="btn btn-success mb-4">+ Tambah Mobil</a>

        <h4 class="mt-5" style="text-shadow: 2px 2px 5px white;">Riwayat Booking</h4>

        <table class="table table-striped-columns table-hover">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Pickup</th>
                    <th>Tujuan</th>
                    <th>Jam</th>
                    <th>Tanggal</th>
                    <th>Penumpang</th>
                    <th>Agenda</th>
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
                        <td><?= date('H:i', strtotime($b['time'])) ?> WIB</td>
                        <td><?= htmlspecialchars($b['date']) ?></td>
                        <td><?= htmlspecialchars($b['passenger_count'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($b['agenda'] ?? '-') ?></td>
                        <td><?= htmlspecialchars(ucfirst($b['status'])) ?></td>
                        <td><?= htmlspecialchars($b['driver_name'] ?? '-') ?></td>
                        <td><?= $b['vehicle_name'] ? htmlspecialchars($b['vehicle_name'] . ' (' . $b['plate_number'] . ')') : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $page <= 1 ? '#' : $baseUrl . 'page=' . ($page - 1) ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl . 'page=' . $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $page >= $totalPages ? '#' : $baseUrl . 'page=' . ($page + 1) ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</body>

</html>