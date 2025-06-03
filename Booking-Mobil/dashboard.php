<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$role = $_SESSION['user']['role'];
switch ($role) {
    case 'superadmin':
        header("Location: superadmin.php");
        break;
    case 'admin':
        header("Location: admin.php");
        break;
    case 'user':
        header("Location: user.php");
        break;
    case 'driver':
        header("Location: driver.php");
        break;
    default:
        echo "Peran tidak dikenali.";
}
exit();
