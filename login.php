<?php
session_start();
require_once 'config.php';

// Jika sudah login, redirect ke halaman yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'asisten') {
        header("Location: asisten/dashboard.php");
    } elseif ($_SESSION['role'] == 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
    }
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Email dan password harus diisi!";
    } else {
        $sql = "SELECT id, nama, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Password benar, simpan semua data penting ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'asisten') {
                    header("Location: asisten/dashboard.php");
                    exit();
                } elseif ($user['role'] == 'mahasiswa') {
                    header("Location: mahasiswa/dashboard.php");
                    exit();
                } else {
                    $message = "Peran pengguna tidak valid.";
                }

            } else {
                $message = "Password yang Anda masukkan salah.";
            }
        } else {
            $message = "Akun dengan email tersebut tidak ditemukan.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 flex justify-center items-center min-h-screen">
    <div class="bg-gray-800 p-8 md:p-10 rounded-2xl shadow-xl border border-gray-700 w-full max-w-md transform transition-all duration-300 hover:shadow-2xl">
        <h2 class="text-3xl font-bold text-center text-blue-400 mb-6">Login SIMPRAK</h2>
        
        <?php 
            if (isset($_GET['status']) && $_GET['status'] == 'registered') {
                echo '<p class="bg-green-600 text-white p-3 rounded-lg text-center mb-4 shadow-md">Registrasi berhasil! Silakan login.</p>';
            }
            if (!empty($message)) {
                echo '<p class="bg-red-600 text-white p-3 rounded-lg text-center mb-4 shadow-md">' . $message . '</p>';
            }
        ?>

        <form action="login.php" method="post" class="space-y-5">
            <div class="form-group">
                <label for="email" class="block text-gray-300 text-sm font-semibold mb-2">Email:</label>
                <input type="email" id="email" name="email" required 
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400">
            </div>
            <div class="form-group">
                <label for="password" class="block text-gray-300 text-sm font-semibold mb-2">Password:</label>
                <input type="password" id="password" name="password" required 
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400">
            </div>
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-bold py-3 rounded-lg shadow-lg transform transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                Login
            </button>
        </form>
        <div class="register-link text-center mt-6">
            <p class="text-gray-400">Belum punya akun? 
                <a href="register.php" class="text-blue-400 hover:text-blue-300 font-semibold transition-colors duration-200">Daftar di sini</a>
            </p>
        </div>
    </div>
</body>
</html>
