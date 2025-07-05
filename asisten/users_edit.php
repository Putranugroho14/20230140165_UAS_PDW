<?php
$pageTitle = 'Edit Akun Pengguna';
$activePage = 'users'; // Menandai menu 'Manajemen Akun Pengguna' aktif

require_once 'templates/header.php';
require_once '../config.php';

$message = '';
$messageType = '';
$user_data = null; // Variabel untuk menyimpan data pengguna yang akan diedit

// Pastikan ada ID pengguna yang dikirim melalui URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Ambil data pengguna dari database untuk diisi ke form
    $sql_fetch = "SELECT id, nama, email, role FROM users WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $user_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    if ($result_fetch->num_rows === 1) {
        $user_data = $result_fetch->fetch_assoc();
    } else {
        $message = "Pengguna tidak ditemukan.";
        $messageType = 'error';
    }
    $stmt_fetch->close();
} else {
    $message = "ID pengguna tidak valid.";
    $messageType = 'error';
}

// Logika untuk menangani pengiriman form UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && $user_data) {
    $id_to_update = intval($_POST['id']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = $_POST['password']; // Password bisa kosong jika tidak diubah

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($role)) {
        $message = "Nama, Email, dan Peran tidak boleh kosong.";
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
        $messageType = 'error';
    } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
        $message = "Peran tidak valid!";
        $messageType = 'error';
    } else {
        // Cek apakah email sudah terdaftar untuk pengguna lain
        $check_email_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt_check_email = $conn->prepare($check_email_sql);
        $stmt_check_email->bind_param("si", $email, $id_to_update);
        $stmt_check_email->execute();
        $stmt_check_email->store_result();

        if ($stmt_check_email->num_rows > 0) {
            $message = "Email sudah terdaftar untuk pengguna lain. Gunakan email lain.";
            $messageType = 'error';
        } else {
            // Bangun query update
            $update_sql = "UPDATE users SET nama = ?, email = ?, role = ?";
            $params = "sss";
            $param_values = [$nama, $email, $role];

            // Jika password diisi, hash dan tambahkan ke query
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $update_sql .= ", password = ?";
                $params .= "s";
                $param_values[] = $hashed_password;
            }

            $update_sql .= " WHERE id = ?";
            $params .= "i";
            $param_values[] = $id_to_update;

            $stmt_update = $conn->prepare($update_sql);

            // Perbaikan untuk Warning: mysqli_stmt::bind_param(): Argument must be passed by reference
            // Membuat array references dari $param_values
            $bind_params = [];
            $bind_params[] = $params; // String tipe parameter adalah argumen pertama
            foreach ($param_values as $key => $value) {
                $bind_params[] = &$param_values[$key]; // Melewatkan setiap nilai sebagai reference
            }
            
            call_user_func_array([$stmt_update, 'bind_param'], $bind_params);
            
            if ($stmt_update->execute()) {
                $message = "Pengguna berhasil diperbarui!";
                $messageType = 'success';
                // Perbarui user_data agar form menampilkan data terbaru
                $user_data['nama'] = $nama;
                $user_data['email'] = $email;
                $user_data['role'] = $role;
            } else {
                $message = "Gagal memperbarui pengguna. Silakan coba lagi.";
                $messageType = 'error';
            }
            $stmt_update->close();
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

<?php if ($user_data && $user_data !== false): ?>
<div class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
    <form action="users_edit.php?id=<?php echo htmlspecialchars($user_data['id']); ?>" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user_data['id']); ?>">
        <div class="mb-4">
            <label for="nama" class="block text-gray-300 text-sm font-semibold mb-2">Nama Lengkap:</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user_data['nama']); ?>" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-300 text-sm font-semibold mb-2">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
        </div>
        <div class="mb-4">
            <label for="password" class="block text-gray-300 text-sm font-semibold mb-2">Password (kosongkan jika tidak ingin mengubah):</label>
            <input type="password" id="password" name="password" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400">
            <p class="text-xs text-gray-500 mt-1">Isi hanya jika Anda ingin mengubah password.</p>
        </div>
        <div class="mb-6">
            <label for="role" class="block text-gray-300 text-sm font-semibold mb-2">Peran:</label>
            <select id="role" name="role" 
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100" required>
                <option value="mahasiswa" <?php echo ($user_data['role'] == 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                <option value="asisten" <?php echo ($user_data['role'] == 'asisten') ? 'selected' : ''; ?>>Asisten</option>
            </select>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-all duration-300 transform hover:scale-105 shadow-md">
                Perbarui Pengguna
            </button>
            <a href="users.php" class="inline-block align-baseline font-bold text-sm text-gray-400 hover:text-gray-200 transition-colors duration-200">
                Batal
            </a>
        </div>
    </form>
</div>
<?php elseif (!$user_data): // Jika user_data adalah false karena error di atas ?>
    <!-- Pesan error sudah ditampilkan di bagian atas -->
<?php endif; ?>

<?php
require_once 'templates/footer.php';
?>
