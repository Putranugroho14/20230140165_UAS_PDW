<?php
$pageTitle = 'Edit Mata Praktikum';
$activePage = 'praktikum'; // Menandai menu 'Manajemen Mata Praktikum' aktif

require_once 'templates/header.php';
require_once '../config.php';

$message = '';
$messageType = '';
$praktikum_data = null; // Variabel untuk menyimpan data praktikum yang akan diedit

// Pastikan ada ID praktikum yang dikirim melalui URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $praktikum_id = intval($_GET['id']);

    // Ambil data praktikum dari database untuk diisi ke form
    $sql_fetch = "SELECT id, nama_praktikum, kode_praktikum, deskripsi FROM mata_praktikum WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $praktikum_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    if ($result_fetch->num_rows === 1) {
        $praktikum_data = $result_fetch->fetch_assoc();
    } else {
        $message = "Mata praktikum tidak ditemukan.";
        $messageType = 'error';
    }
    $stmt_fetch->close();
} else {
    $message = "ID praktikum tidak valid.";
    $messageType = 'error';
}

// Logika untuk menangani pengiriman form UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && $praktikum_data) {
    $id_to_update = intval($_POST['id']);
    $nama_praktikum = trim($_POST['nama_praktikum']);
    $kode_praktikum = trim($_POST['kode_praktikum']);
    $deskripsi = trim($_POST['deskripsi']);

    // Validasi sederhana
    if (empty($nama_praktikum) || empty($kode_praktikum)) {
        $message = "Nama praktikum dan Kode praktikum tidak boleh kosong.";
        $messageType = 'error';
    } else {
        // Cek apakah kode praktikum sudah ada dan bukan milik praktikum yang sedang diedit
        $check_sql = "SELECT id FROM mata_praktikum WHERE kode_praktikum = ? AND id != ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("si", $kode_praktikum, $id_to_update);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = "Kode Praktikum sudah ada. Gunakan kode lain.";
            $messageType = 'error';
        } else {
            // Lakukan update data
            $update_sql = "UPDATE mata_praktikum SET nama_praktikum = ?, kode_praktikum = ?, deskripsi = ? WHERE id = ?";
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param("sssi", $nama_praktikum, $kode_praktikum, $deskripsi, $id_to_update);

            if ($stmt_update->execute()) {
                $message = "Mata praktikum berhasil diperbarui!";
                $messageType = 'success';
                // Perbarui data praktikum_data agar form menampilkan data terbaru
                $praktikum_data['nama_praktikum'] = $nama_praktikum;
                $praktikum_data['kode_praktikum'] = $kode_praktikum;
                $praktikum_data['deskripsi'] = $deskripsi;
            } else {
                $message = "Gagal memperbarui mata praktikum. Silakan coba lagi.";
                $messageType = 'error';
            }
            $stmt_update->close();
        }
        $stmt_check->close();
    }
}
$conn->close();
?>

<h1 class="text-3xl font-bold text-gray-100 mb-6">Edit Mata Praktikum</h1>

<?php if ($message): ?>
    <div class="p-4 mb-4 text-sm border rounded-lg <?php echo $messageType == 'success' ? 'bg-green-600 border-green-500 text-white' : 'bg-red-600 border-red-500 text-white'; ?> shadow-md" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($praktikum_data && $praktikum_data !== false): ?>
<div class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
    <form action="praktikum_edit.php?id=<?php echo htmlspecialchars($praktikum_data['id']); ?>" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($praktikum_data['id']); ?>">
        <div class="mb-4">
            <label for="nama_praktikum" class="block text-gray-300 text-sm font-semibold mb-2">Nama Praktikum:</label>
            <input type="text" id="nama_praktikum" name="nama_praktikum" value="<?php echo htmlspecialchars($praktikum_data['nama_praktikum']); ?>" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
        </div>
        <div class="mb-4">
            <label for="kode_praktikum" class="block text-gray-300 text-sm font-semibold mb-2">Kode Praktikum:</label>
            <input type="text" id="kode_praktikum" name="kode_praktikum" value="<?php echo htmlspecialchars($praktikum_data['kode_praktikum']); ?>" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
        </div>
        <div class="mb-6">
            <label for="deskripsi" class="block text-gray-300 text-sm font-semibold mb-2">Deskripsi:</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" 
                      class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400"><?php echo htmlspecialchars($praktikum_data['deskripsi']); ?></textarea>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-all duration-300 transform hover:scale-105 shadow-md">
                Perbarui Praktikum
            </button>
            <a href="praktikum.php" class="inline-block align-baseline font-bold text-sm text-gray-400 hover:text-gray-200 transition-colors duration-200">
                Batal
            </a>
        </div>
    </form>
</div>
<?php elseif (!$praktikum_data): // Jika praktikum_data adalah false karena error di atas ?>
    <!-- Pesan error sudah ditampilkan di bagian atas -->
<?php endif; ?>

<?php
require_once 'templates/footer.php';
?>
