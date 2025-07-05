<?php
$pageTitle = 'Tambah Modul Praktikum';
$activePage = 'modul'; // Menandai menu 'Manajemen Modul' aktif

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
    $praktikum_id = intval($_POST['praktikum_id']);
    $judul_modul = trim($_POST['judul_modul']);
    $deskripsi_modul = trim($_POST['deskripsi_modul']);
    $file_materi = null;

    // Validasi sederhana
    if (empty($praktikum_id) || empty($judul_modul)) {
        $message = "Mata Praktikum dan Judul Modul tidak boleh kosong.";
        $messageType = 'error';
    } else {
        // Handle file upload
        if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../uploads/modul/"; // Folder untuk menyimpan file materi
            $file_extension = pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid('modul_') . '.' . $file_extension; // Nama file unik
            $target_file = $target_dir . $new_file_name;

            // Pastikan direktori uploads ada
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (move_uploaded_file($_FILES['file_materi']['tmp_name'], $target_file)) {
                $file_materi = $new_file_name;
            } else {
                $message = "Gagal mengunggah file materi.";
                $messageType = 'error';
            }
        }

        // Jika tidak ada error upload, lanjutkan insert ke database
        if ($messageType !== 'error') {
            $insert_sql = "INSERT INTO modul_praktikum (praktikum_id, judul_modul, deskripsi_modul, file_materi) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_sql);
            $stmt_insert->bind_param("isss", $praktikum_id, $judul_modul, $deskripsi_modul, $file_materi);

            if ($stmt_insert->execute()) {
                $message = "Modul berhasil ditambahkan!";
                $messageType = 'success';
                // Opsional: Redirect ke halaman daftar modul setelah berhasil
                // header("Location: modul.php?status=success&message=" . urlencode($message));
                // exit();
            } else {
                $message = "Gagal menambahkan modul. Silakan coba lagi.";
                $messageType = 'error';
            }
            $stmt_insert->close();
        }
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
    <form action="modul_add.php" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="praktikum_id" class="block text-gray-300 text-sm font-semibold mb-2">Mata Praktikum:</label>
            <select id="praktikum_id" name="praktikum_id" 
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100" required>
                <option value="">Pilih Mata Praktikum</option>
                <?php foreach ($mata_praktikum_list as $praktikum_item): ?>
                    <option value="<?php echo htmlspecialchars($praktikum_item['id']); ?>">
                        <?php echo htmlspecialchars($praktikum_item['nama_praktikum'] . ' (' . $praktikum_item['kode_praktikum'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="judul_modul" class="block text-gray-300 text-sm font-semibold mb-2">Judul Modul:</label>
            <input type="text" id="judul_modul" name="judul_modul" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
        </div>
        <div class="mb-4">
            <label for="deskripsi_modul" class="block text-gray-300 text-sm font-semibold mb-2">Deskripsi Modul:</label>
            <textarea id="deskripsi_modul" name="deskripsi_modul" rows="4" 
                      class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400"></textarea>
        </div>
        <div class="mb-6">
            <label for="file_materi" class="block text-gray-300 text-sm font-semibold mb-2">File Materi (PDF/DOCX):</label>
            <input type="file" id="file_materi" name="file_materi" accept=".pdf,.doc,.docx" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100">
            <p class="text-xs text-gray-500 mt-1">Ukuran file maksimal: 2MB</p>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-all duration-300 transform hover:scale-105 shadow-md">
                Tambah Modul
            </button>
            <a href="modul.php" class="inline-block align-baseline font-bold text-sm text-gray-400 hover:text-gray-200 transition-colors duration-200">
                Batal
            </a>
        </div>
    </form>
</div>

<?php
require_once 'templates/footer.php';
?>
