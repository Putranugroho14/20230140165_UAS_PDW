<?php
$pageTitle = 'Edit Modul Praktikum';
$activePage = 'modul'; // Menandai menu 'Manajemen Modul' aktif

require_once 'templates/header.php';
require_once '../config.php';

$message = '';
$messageType = '';
$modul_data = null; // Variabel untuk menyimpan data modul yang akan diedit
$mata_praktikum_list = []; // Daftar mata praktikum untuk dropdown

// Ambil daftar mata praktikum untuk dropdown
$sql_praktikum = "SELECT id, nama_praktikum, kode_praktikum FROM mata_praktikum ORDER BY nama_praktikum ASC";
$result_praktikum = $conn->query($sql_praktikum);

if ($result_praktikum && $result_praktikum->num_rows > 0) {
    while ($row = $result_praktikum->fetch_assoc()) {
        $mata_praktikum_list[] = $row;
    }
}

// Pastikan ada ID modul yang dikirim melalui URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $modul_id = intval($_GET['id']);

    // Ambil data modul dari database untuk diisi ke form
    $sql_fetch = "SELECT id, praktikum_id, judul_modul, deskripsi_modul, file_materi FROM modul_praktikum WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $modul_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    if ($result_fetch->num_rows === 1) {
        $modul_data = $result_fetch->fetch_assoc();
    } else {
        $message = "Modul tidak ditemukan.";
        $messageType = 'error';
    }
    $stmt_fetch->close();
} else {
    $message = "ID modul tidak valid.";
    $messageType = 'error';
}

// Logika untuk menangani pengiriman form UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST" && $modul_data) {
    $id_to_update = intval($_POST['id']);
    $praktikum_id = intval($_POST['praktikum_id']);
    $judul_modul = trim($_POST['judul_modul']);
    $deskripsi_modul = trim($_POST['deskripsi_modul']);
    $current_file_materi = $_POST['current_file_materi'] ?? null; // File materi yang sudah ada

    $file_materi = $current_file_materi; // Default ke file yang sudah ada

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
                // Hapus file lama jika ada dan berbeda dengan yang baru
                if ($current_file_materi && file_exists($target_dir . $current_file_materi)) {
                    unlink($target_dir . $current_file_materi);
                }
            } else {
                $message = "Gagal mengunggah file materi baru.";
                $messageType = 'error';
            }
        }

        // Jika tidak ada error upload, lanjutkan update ke database
        if ($messageType !== 'error') {
            $update_sql = "UPDATE modul_praktikum SET praktikum_id = ?, judul_modul = ?, deskripsi_modul = ?, file_materi = ? WHERE id = ?";
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param("isssi", $praktikum_id, $judul_modul, $deskripsi_modul, $file_materi, $id_to_update);

            if ($stmt_update->execute()) {
                $message = "Modul berhasil diperbarui!";
                $messageType = 'success';
                // Perbarui modul_data agar form menampilkan data terbaru
                $modul_data['praktikum_id'] = $praktikum_id;
                $modul_data['judul_modul'] = $judul_modul;
                $modul_data['deskripsi_modul'] = $deskripsi_modul;
                $modul_data['file_materi'] = $file_materi;
            } else {
                $message = "Gagal memperbarui modul. Silakan coba lagi.";
                $messageType = 'error';
            }
            $stmt_update->close();
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

<?php if ($modul_data && $modul_data !== false): ?>
<div class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
    <form action="modul_edit.php?id=<?php echo htmlspecialchars($modul_data['id']); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($modul_data['id']); ?>">
        <input type="hidden" name="current_file_materi" value="<?php echo htmlspecialchars($modul_data['file_materi'] ?? ''); ?>">

        <div class="mb-4">
            <label for="praktikum_id" class="block text-gray-300 text-sm font-semibold mb-2">Mata Praktikum:</label>
            <select id="praktikum_id" name="praktikum_id" 
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100" required>
                <option value="">Pilih Mata Praktikum</option>
                <?php foreach ($mata_praktikum_list as $praktikum_item): ?>
                    <option value="<?php echo htmlspecialchars($praktikum_item['id']); ?>" <?php echo ($praktikum_item['id'] == $modul_data['praktikum_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($praktikum_item['nama_praktikum'] . ' (' . $praktikum_item['kode_praktikum'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="judul_modul" class="block text-gray-300 text-sm font-semibold mb-2">Judul Modul:</label>
            <input type="text" id="judul_modul" name="judul_modul" value="<?php echo htmlspecialchars($modul_data['judul_modul']); ?>" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
        </div>
        <div class="mb-4">
            <label for="deskripsi_modul" class="block text-gray-300 text-sm font-semibold mb-2">Deskripsi Modul:</label>
            <textarea id="deskripsi_modul" name="deskripsi_modul" rows="4" 
                      class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400"><?php echo htmlspecialchars($modul_data['deskripsi_modul']); ?></textarea>
        </div>
        <div class="mb-6">
            <label for="file_materi" class="block text-gray-300 text-sm font-semibold mb-2">File Materi (PDF/DOCX):</label>
            <?php if (!empty($modul_data['file_materi'])): ?>
                <p class="text-sm text-gray-400 mb-2">File saat ini: <a href="../uploads/modul/<?php echo htmlspecialchars($modul_data['file_materi']); ?>" target="_blank" class="text-blue-400 hover:underline"><?php echo htmlspecialchars($modul_data['file_materi']); ?></a></p>
            <?php endif; ?>
            <input type="file" id="file_materi" name="file_materi" accept=".pdf,.doc,.docx" 
                   class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100">
            <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah file. Ukuran file maksimal: 2MB</p>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-all duration-300 transform hover:scale-105 shadow-md">
                Perbarui Modul
            </button>
            <a href="modul.php" class="inline-block align-baseline font-bold text-sm text-gray-400 hover:text-gray-200 transition-colors duration-200">
                Batal
            </a>
        </div>
    </form>
</div>
<?php elseif (!$modul_data): // Jika modul_data adalah false karena error di atas ?>
    <!-- Pesan error sudah ditampilkan di bagian atas -->
<?php endif; ?>

<?php
require_once 'templates/footer.php';
?>
