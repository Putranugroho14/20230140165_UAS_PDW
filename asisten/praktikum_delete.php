<?php
session_start();
require_once '../config.php';

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$messageType = '';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $praktikum_id = intval($_GET['id']);

    // Persiapkan statement untuk menghapus
    $sql_delete = "DELETE FROM mata_praktikum WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $praktikum_id);

    if ($stmt_delete->execute()) {
        $message = "Mata praktikum berhasil dihapus!";
        $messageType = 'success';
    } else {
        $message = "Gagal menghapus mata praktikum. Mungkin ada data terkait (misalnya pendaftaran) yang perlu dihapus terlebih dahulu.";
        $messageType = 'error';
    }
    $stmt_delete->close();
} else {
    $message = "ID praktikum tidak valid untuk dihapus.";
    $messageType = 'error';
}

$conn->close();

// Redirect kembali ke halaman daftar praktikum dengan pesan
header("Location: praktikum.php?status=" . $messageType . "&message=" . urlencode($message));
exit();
?>