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
    $user_id_to_delete = intval($_GET['id']);

    // Pencegahan: Asisten tidak bisa menghapus akunnya sendiri
    if ($user_id_to_delete == $_SESSION['user_id']) {
        $message = "Anda tidak bisa menghapus akun Anda sendiri.";
        $messageType = 'error';
    } else {
        // Persiapkan statement untuk menghapus
        $sql_delete = "DELETE FROM users WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $user_id_to_delete);

        if ($stmt_delete->execute()) {
            $message = "Pengguna berhasil dihapus!";
            $messageType = 'success';
        } else {
            $message = "Gagal menghapus pengguna. Mungkin ada data terkait (praktikum, laporan) yang perlu dihapus terlebih dahulu.";
            $messageType = 'error';
        }
        $stmt_delete->close();
    }
} else {
    $message = "ID pengguna tidak valid untuk dihapus.";
    $messageType = 'error';
}

$conn->close();

// Redirect kembali ke halaman daftar pengguna dengan pesan
header("Location: users.php?status=" . $messageType . "&message=" . urlencode($message));
exit();
?>
