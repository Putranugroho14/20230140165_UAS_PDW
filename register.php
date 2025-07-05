<?php
require_once 'config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $message = "Semua field harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
    } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
        $message = "Peran tidak valid!";
    } else {
        // Cek apakah email sudah terdaftar
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke database
            $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                header("Location: login.php?status=registered");
                exit();
            } else {
                $message = "Terjadi kesalahan. Silakan coba lagi.";
            }
            $stmt_insert->close();
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
    <title>Registrasi Pengguna SIMPRAK</title>
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
        <h2 class="text-3xl font-bold text-center text-blue-400 mb-6">Registrasi SIMPRAK</h2>
        
        <?php if (!empty($message)): ?>
            <p class="bg-red-600 text-white p-3 rounded-lg text-center mb-4 shadow-md"><?php echo $message; ?></p>
        <?php endif; ?>

        <form action="register.php" method="post" class="space-y-5">
            <div class="form-group">
                <label for="nama" class="block text-gray-300 text-sm font-semibold mb-2">Nama Lengkap:</label>
                <input type="text" id="nama" name="nama" required 
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400">
            </div>
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
            <div class="form-group">
                <label for="role" class="block text-gray-300 text-sm font-semibold mb-2">Daftar Sebagai:</label>
                <select id="role" name="role" required 
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100">
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="asisten">Asisten</option>
                </select>
            </div>
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 text-white font-bold py-3 rounded-lg shadow-lg transform transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                Daftar
            </button>
        </form>
        <div class="login-link text-center mt-6">
            <p class="text-gray-400">Sudah punya akun? 
                <a href="login.php" class="text-green-400 hover:text-green-300 font-semibold transition-colors duration-200">Login di sini</a>
            </p>
        </div>
    </div>
</body>
</html>
