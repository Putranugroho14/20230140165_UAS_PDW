<?php
// Letakkan error_reporting dan ini_set di paling atas setelah tag <?php
error_reporting(E_ALL); // Penting: Aktifkan semua laporan error
ini_set('display_errors', 1); // Penting: Tampilkan error langsung di browser

session_start(); // Pastikan sesi dimulai untuk mengakses $_SESSION
require_once '../config.php'; // Panggil konfigurasi database

// Periksa apakah pengguna sudah login dan perannya adalah mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$message = ''; // Variabel untuk pesan sukses/error
$messageType = ''; // Tipe pesan: success atau error

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $praktikum_id = intval($_GET['id']); // Ambil ID praktikum dari URL
    $user_id = $_SESSION['user_id']; // Ambil user ID dari sesi

    // Periksa apakah mahasiswa sudah terdaftar di praktikum ini
    $check_sql = "SELECT id FROM registrasi_praktikum WHERE user_id = ? AND praktikum_id = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("ii", $user_id, $praktikum_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $message = "Anda sudah terdaftar di praktikum ini.";
        $messageType = 'error';
    } else {
        // Lakukan pendaftaran
        $insert_sql = "INSERT INTO registrasi_praktikum (user_id, praktikum_id) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("ii", $user_id, $praktikum_id);

        if ($stmt_insert->execute()) {
            $message = "Berhasil mendaftar ke praktikum!";
            $messageType = 'success';
        } else {
            $message = "Gagal mendaftar ke praktikum. Silakan coba lagi.";
            $messageType = 'error';
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
} else {
    // Jika tidak ada ID praktikum di URL
    $message = "ID praktikum tidak valid.";
    $messageType = 'error';
}

$conn->close();

// Redirect kembali ke halaman courses.php dengan pesan
// Menggunakan header location agar pesan bisa ditampilkan setelah redirect
header("Location: courses.php?status=" . $messageType . "&message=" . urlencode($message));
exit();
// Pastikan tidak ada karakter, spasi, atau baris kosong setelah ini di berkas ini.
// Berkas PHP yang melakukan redirect di awal sebaiknya tidak memiliki tag penutup `?>`