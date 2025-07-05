<?php
$pageTitle = 'Tambah Pengguna Baru';
$activePage = 'users'; // Menandai menu 'Manajemen Akun Pengguna' aktif

require_once 'templates/header.php';
require_once '../config.php';

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $message = "Semua field harus diisi!";
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
        $messageType = 'error';
    } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
        $message = "Peran tidak valid!";
        $messageType = 'error';
    } else {
        // Cek apakah email sudah terdaftar
        $sql_check_email = "SELECT id FROM users WHERE email = ?";
        $stmt_check_email = $conn->prepare($sql_check_email);
        $stmt_check_email->bind_param("s", $email);
        $stmt_check_email->execute();
        $stmt_check_email->store_result();

        if ($stmt_check_email->num_rows > 0) {
            $message = "Email sudah terdaftar. Silakan gunakan email lain.";
            $messageType = 'error';
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke database
            $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                $message = "Pengguna berhasil ditambahkan!";
                $messageType = 'success';
                // Opsional: Redirect ke halaman daftar pengguna setelah berhasil
                // header("Location: users.php?status=success&message=" . urlencode($message));
                // exit();
            } else {
                $message = "Gagal menambahkan pengguna. Silakan coba lagi.";
                $messageType = 'error';
            }
            $stmt_insert->close();
        }
        $stmt_check_email->close();
    }
}
$conn->close();
?>


<?php if ($message): ?>
    <div class="p-4 mb-4 text-sm border rounded-lg <?php echo $messageType == 'success' ? 'bg-green-600 border-green-500 text-white' : 'bg-red-600 border-red-500 text-white'; ?> shadow-md" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
    <form action="users_add.php" method="POST">
        <div class="mb-4">
            <label for="nama" class="block text-gray-300 text-sm font-semibold mb-2">Nama Lengkap:</label>
            <input type="text" id="nama" name="nama" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-300 text-sm font-semibold mb-2">Email:</label>
            <input type="email" id="email" name="email" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
        </div>
        <div class="mb-4">
            <label for="password" class="block text-gray-300 text-sm font-semibold mb-2">Password:</label>
            <input type="password" id="password" name="password" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
        </div>
        <div class="mb-6">
            <label for="role" class="block text-gray-300 text-sm font-semibold mb-2">Peran:</label>
            <select id="role" name="role" 
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100" required>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asisten">Asisten</option>
            </select>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-all duration-300 transform hover:scale-105 shadow-md">
                Tambah Pengguna
            </button>
            <a href="users.php" class="inline-block align-baseline font-bold text-sm text-gray-400 hover:text-gray-200 transition-colors duration-200">
                Batal
            </a>
        </div>
    </form>
</div>

<?php
require_once 'templates/footer.php';
?>
