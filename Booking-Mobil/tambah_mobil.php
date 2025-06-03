<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'superadmin'])) {
    header("Location: login.php");
    exit();
}

$success = null;
$error = null;

// PROSES HAPUS
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    // Ambil data mobil
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([$delete_id]);
    $vehicle = $stmt->fetch();

    if ($vehicle) {
        // Update semua assignment dengan snapshot data
        $stmt = $pdo->prepare("UPDATE assignments SET vehicle_name_snapshot = ?, plate_number_snapshot = ? WHERE vehicle_id = ?");
        $stmt->execute([$vehicle['vehicle_name'], $vehicle['plate_number'], $delete_id]);

        // Hapus dari tabel vehicles
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
        if ($stmt->execute([$delete_id])) {
            $success = "Mobil berhasil dihapus. Data riwayat tetap disimpan.";
            header("Location: tambah_mobil.php");
            exit();
        } else {
            $error = "Gagal menghapus mobil.";
        }
    } else {
        $error = "Mobil tidak ditemukan.";
    }
}

// PROSES EDIT - Tampilkan data edit jika ada parameter edit_id
$edit_vehicle = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_vehicle = $stmt->fetch();
}

// PROSES SUBMIT EDIT
if (isset($_POST['edit_id'])) {
    $edit_id = (int)$_POST['edit_id'];
    $vehicle_name = trim($_POST['vehicle_name']);
    $plate_number = trim($_POST['plate_number']);

    if ($vehicle_name && $plate_number) {
        $stmt = $pdo->prepare("UPDATE vehicles SET vehicle_name = ?, plate_number = ? WHERE id = ?");
        if ($stmt->execute([$vehicle_name, $plate_number, $edit_id])) {
            $success = "Mobil berhasil diupdate.";
            header("Location: tambah_mobil.php");
            exit();
        } else {
            $error = "Gagal mengupdate mobil.";
        }
    } else {
        $error = "Semua field wajib diisi.";
    }
}

// PROSES TAMBAH (jika bukan edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    $vehicle_name = trim($_POST['vehicle_name']);
    $plate_number = trim($_POST['plate_number']);

    if ($vehicle_name && $plate_number) {
        $stmt = $pdo->prepare("INSERT INTO vehicles (vehicle_name, plate_number) VALUES (?, ?)");
        if ($stmt->execute([$vehicle_name, $plate_number])) {
            $success = "Mobil berhasil ditambahkan.";
        } else {
            $error = "Gagal menambahkan mobil.";
        }
    } else {
        $error = "Semua field wajib diisi.";
    }
}

// Ambil semua kendaraan
$stmt = $pdo->query("SELECT id, vehicle_name, plate_number FROM vehicles ORDER BY id DESC");
$vehicles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tambah Mobil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body style="background-image: url('mobil.jpg'); background-repeat: no-repeat; background-size: cover; background-size: cover ;">
    <header class="py-3 mb-4 border-bottom">
        <div class="container d-flex flex-wrap justify-content-center">
            <h3 class="d-flex align-items-center mb-3 mb-lg-0 me-lg-auto text-decoration-none"> <svg class="bi me-2" width="40" height="32" aria-hidden="true">
                </svg> <span class="fs-4">Admin</span>
            </h3>

        </div>
    </header>

    <div class="container mt-4">
        <h3><?= $edit_vehicle ? "Edit Mobil" : "Tambah Mobil Baru" ?></h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="mb-5">
            <?php if ($edit_vehicle): ?>
                <input type="hidden" name="edit_id" value="<?= $edit_vehicle['id'] ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label for="vehicle_name" class="form-label">Nama Kendaraan</label>
                <input type="text" class="form-control" name="vehicle_name" required
                    value="<?= $edit_vehicle ? htmlspecialchars($edit_vehicle['vehicle_name']) : '' ?>">
            </div>
            <div class="mb-3">
                <label for="plate_number" class="form-label">Nomor Plat</label>
                <input type="text" class="form-control" name="plate_number" required
                    value="<?= $edit_vehicle ? htmlspecialchars($edit_vehicle['plate_number']) : '' ?>">
            </div>
            <button type="submit" class="btn btn-success">
                <?= $edit_vehicle ? "Update" : "Simpan" ?>
            </button>
            <?php if ($edit_vehicle): ?>
                <a href="tambah_mobil.php" class="btn btn-secondary ms-2">Batal</a>

            <?php endif; ?>
            <a href="admin.php" class="btn btn-secondary me-2">Kembali</a>
        </form>

        <h4>Daftar Mobil</h4>
        <?php if (count($vehicles) === 0): ?>
            <div class="alert alert-info">Belum ada mobil yang terdaftar.</div>
        <?php else: ?>
            <table class="table table-striped align-middle table table-hover">
                <thead>
                    <tr>
                        <th>Nama Kendaraan</th>
                        <th>Nomor Plat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><?= htmlspecialchars($vehicle['vehicle_name']) ?></td>
                            <td><?= htmlspecialchars($vehicle['plate_number']) ?></td>
                            <td>
                                <a href="tambah_mobil.php?edit_id=<?= $vehicle['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <!-- <a href="tambah_mobil.php?delete_id=<?= $vehicle['id'] ?>" onclick="return confirm('Yakin ingin menghapus mobil ini?');" class="btn btn-danger btn-sm">Hapus</a> -->
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>