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
    $modul_id = intval($_GET['id']);

    // Ambil nama file materi sebelum menghapus record dari database
    $sql_fetch_file = "SELECT file_materi FROM modul_praktikum WHERE id = ?";
    $stmt_fetch_file = $conn->prepare($sql_fetch_file);
    $stmt_fetch_file->bind_param("i", $modul_id);
    $stmt_fetch_file->execute();
    $result_fetch_file = $stmt_fetch_file->get_result();
    $file_to_delete = null;
    if ($result_fetch_file->num_rows === 1) {
        $row = $result_fetch_file->fetch_assoc();
        $file_to_delete = $row['file_materi'];
    }
    $stmt_fetch_file->close();

    // Persiapkan statement untuk menghapus modul
    $sql_delete = "DELETE FROM modul_praktikum WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $modul_id);

    if ($stmt_delete->execute()) {
        $message = "Modul berhasil dihapus!";
        $messageType = 'success';
        // Hapus file fisik jika ada
        if ($file_to_delete && file_exists("../uploads/modul/" . $file_to_delete)) {
            unlink("../uploads/modul/" . $file_to_delete);
        }
    } else {
        $message = "Gagal menghapus modul. Silakan coba lagi.";
        $messageType = 'error';
    }
    $stmt_delete->close();
} else {
    $message = "ID modul tidak valid untuk dihapus.";
    $messageType = 'error';
}

$conn->close();

// Redirect kembali ke halaman daftar modul dengan pesan
header("Location: modul.php?status=" . $messageType . "&message=" . urlencode($message));
exit();
?>
