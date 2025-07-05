<?php
$pageTitle = 'Tambah Mata Praktikum';
$activePage = 'praktikum'; // Menandai menu 'Manajemen Mata Praktikum' aktif

require_once 'templates/header.php';
require_once '../config.php';

$message = '';
$messageType = '';

// Ambil daftar mata praktikum untuk dropdown
$mata_praktikum_list = [];
$sql_praktikum = "SELECT id, nama_praktikum, kode_praktikum FROM mata_praktikum ORDER BY nama_praktikum ASC";
$result_praktikum = $conn->query($sql_praktikum);

if ($result_praktikum && $result_praktikum->num_rows > 0) {
    while ($row = $result_praktikum->fetch_assoc()) {
        $mata_praktikum_list[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_praktikum = trim($_POST['nama_praktikum']);
    $kode_praktikum = trim($_POST['kode_praktikum']);
    $deskripsi = trim($_POST['deskripsi']);

    // Validasi sederhana
    if (empty($nama_praktikum) || empty($kode_praktikum)) {
        $message = "Nama praktikum dan Kode praktikum tidak boleh kosong.";
        $messageType = 'error';
    } else {
        // Cek apakah kode praktikum sudah ada
        $check_sql = "SELECT id FROM mata_praktikum WHERE kode_praktikum = ?";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("s", $kode_praktikum);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = "Kode Praktikum sudah ada. Gunakan kode lain.";
            $messageType = 'error';
        } else {
            // Masukkan data baru ke database
            $insert_sql = "INSERT INTO mata_praktikum (nama_praktikum, kode_praktikum, deskripsi) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_sql);
            $stmt_insert->bind_param("sss", $nama_praktikum, $kode_praktikum, $deskripsi);

            if ($stmt_insert->execute()) {
                $message = "Mata praktikum berhasil ditambahkan!";
                $messageType = 'success';
                // Opsional: Redirect ke halaman daftar praktikum setelah berhasil
                // header("Location: praktikum.php?status=success&message=" . urlencode($message));
                // exit();
            } else {
                $message = "Gagal menambahkan mata praktikum. Silakan coba lagi.";
                $messageType = 'error';
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
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
    <form action="praktikum_add.php" method="POST">
        <div class="mb-4">
            <label for="nama_praktikum" class="block text-gray-300 text-sm font-semibold mb-2">Nama Praktikum:</label>
            <input type="text" id="nama_praktikum" name="nama_praktikum" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
        </div>
        <div class="mb-4">
            <label for="kode_praktikum" class="block text-gray-300 text-sm font-semibold mb-2">Kode Praktikum:</label>
            <input type="text" id="kode_praktikum" name="kode_praktikum" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
        </div>
        <div class="mb-6">
            <label for="deskripsi" class="block text-gray-300 text-sm font-semibold mb-2">Deskripsi:</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" 
                      class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400"></textarea>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-all duration-300 transform hover:scale-105 shadow-md">
                Tambah Praktikum
            </button>
            <a href="praktikum.php" class="inline-block align-baseline font-bold text-sm text-gray-400 hover:text-gray-200 transition-colors duration-200">
                Batal
            </a>
        </div>
    </form>
</div>

<?php
require_once 'templates/footer.php';
?>
