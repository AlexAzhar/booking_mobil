<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Jangan izinkan menghapus superadmin
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role != 'superadmin'");
$stmt->execute([$id]);
$user = $stmt->fetch();

if ($user) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: superadmin.php");
exit();
