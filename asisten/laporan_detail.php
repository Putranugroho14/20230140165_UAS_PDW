<?php
$pageTitle = 'Detail Laporan & Penilaian';
$activePage = 'laporan'; // Menandai menu 'Laporan Masuk' aktif

require_once 'templates/header.php'; // Header untuk panel asisten
require_once '../config.php'; // Koneksi database

$laporan_detail = null;
$laporan_id = $_GET['id'] ?? null;

// Pastikan ID laporan valid
if ($laporan_id) {
    // Query untuk mengambil detail laporan
    $sql = "SELECT lp.id AS laporan_id, lp.file_laporan, lp.tanggal_unggah, lp.nilai, lp.feedback, lp.status_laporan,
                   u.nama AS nama_mahasiswa, u.email AS email_mahasiswa,
                   m.judul_modul,
                   mp.nama_praktikum, mp.kode_praktikum
            FROM laporan_praktikum lp
            JOIN users u ON lp.user_id = u.id
            JOIN modul_praktikum m ON lp.modul_id = m.id
            JOIN mata_praktikum mp ON m.praktikum_id = mp.id
            WHERE lp.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $laporan_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $laporan_detail = $result->fetch_assoc();
    } else {
        echo '<div class="p-4 mb-4 text-sm border rounded-lg bg-red-600 border-red-500 text-white shadow-md" role="alert">Laporan tidak ditemukan.</div>';
        $laporan_detail = false; // Tandai bahwa laporan tidak valid
    }
    $stmt->close();
} else {
    echo '<div class="p-4 mb-4 text-sm border rounded-lg bg-red-600 border-red-500 text-white shadow-md" role="alert">ID Laporan tidak valid.</div>';
}

// Logika untuk menangani pengiriman form penilaian
if ($_SERVER["REQUEST_METHOD"] == "POST" && $laporan_detail) {
    $nilai = trim($_POST['nilai']);
    $feedback = trim($_POST['feedback']);
    $status_laporan_baru = trim($_POST['status_laporan_baru']); // Ambil status baru dari form

    // Validasi nilai
    if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
        $message = "Nilai harus angka antara 0 dan 100.";
        $messageType = 'error';
    } elseif (!in_array($status_laporan_baru, ['not_graded', 'graded'])) { // Validasi status baru
        $message = "Status laporan tidak valid.";
        $messageType = 'error';
    } else {
        $update_sql = "UPDATE laporan_praktikum SET nilai = ?, feedback = ?, status_laporan = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param("issi", $nilai, $feedback, $status_laporan_baru, $laporan_id);

        if ($stmt_update->execute()) {
            $message = "Nilai, feedback, dan status berhasil disimpan!";
            $messageType = 'success';
            // Perbarui detail laporan agar tampilan langsung terupdate
            $laporan_detail['nilai'] = $nilai;
            $laporan_detail['feedback'] = $feedback;
            $laporan_detail['status_laporan'] = $status_laporan_baru;
        } else {
            $message = "Gagal menyimpan nilai. Silakan coba lagi.";
            $messageType = 'error';
        }
        $stmt_update->close();
    }
}
$conn->close();
?>

<?php if (isset($message) && $message): ?>
    <div class="p-4 mb-4 text-sm border rounded-lg <?php echo $messageType == 'success' ? 'bg-green-600 border-green-500 text-white' : 'bg-red-600 border-red-500 text-white'; ?> shadow-md" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($laporan_detail && $laporan_detail !== false): ?>
    <div class="bg-gray-800 p-6 rounded-lg shadow-lg mb-6 border border-gray-700">
        <h2 class="text-2xl font-bold text-gray-100 mb-4">Informasi Laporan</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-gray-400"><strong>Praktikum:</strong> <span class="text-gray-100"><?php echo htmlspecialchars($laporan_detail['nama_praktikum']); ?> (<?php echo htmlspecialchars($laporan_detail['kode_praktikum']); ?>)</span></p>
                <p class="text-gray-400"><strong>Modul:</strong> <span class="text-gray-100"><?php echo htmlspecialchars($laporan_detail['judul_modul']); ?></span></p>
                <p class="text-gray-400"><strong>Mahasiswa:</strong> <span class="text-gray-100"><?php echo htmlspecialchars($laporan_detail['nama_mahasiswa']); ?> (<span class="text-blue-400"><?php echo htmlspecialchars($laporan_detail['email_mahasiswa']); ?></span>)</span></p>
                <p class="text-gray-400"><strong>Tanggal Unggah:</strong> <span class="text-gray-100"><?php echo date('d M Y H:i', strtotime($laporan_detail['tanggal_unggah'])); ?></span></p>
            </div>
            <div>
                <p class="text-gray-400"><strong>File Laporan:</strong> 
                    <a href="../uploads/laporan/<?php echo htmlspecialchars($laporan_detail['file_laporan']); ?>" target="_blank" class="text-blue-400 hover:underline transition-colors duration-200">Unduh Laporan</a>
                </p>
                <p class="text-gray-400"><strong>Status:</strong> 
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        <?php 
                            if ($laporan_detail['status_laporan'] == 'not_graded') echo 'bg-yellow-700 text-yellow-200';
                            elseif ($laporan_detail['status_laporan'] == 'graded') echo 'bg-green-700 text-green-200';
                        ?>">
                        <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($laporan_detail['status_laporan']))); ?>
                    </span>
                </p>
                <?php if ($laporan_detail['nilai'] !== null): ?>
                    <p class="text-gray-400"><strong>Nilai:</strong> <span class="font-bold text-xl text-green-400"><?php echo htmlspecialchars($laporan_detail['nilai']); ?></span></p>
                <?php endif; ?>
                <?php if (!empty($laporan_detail['feedback'])): ?>
                    <p class="text-gray-400"><strong>Feedback:</strong> <span class="text-gray-100"><?php echo nl2br(htmlspecialchars($laporan_detail['feedback'])); ?></span></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-700">
        <h2 class="text-2xl font-bold text-gray-100 mb-4">Beri Nilai & Feedback</h2>
        <form action="laporan_detail.php?id=<?php echo htmlspecialchars($laporan_detail['laporan_id']); ?>" method="POST">
            <input type="hidden" name="action" value="grade"> <!-- Hidden input for grading action -->
            <div class="mb-4">
                <label for="nilai" class="block text-gray-300 text-sm font-semibold mb-2">Nilai (0-100):</label>
                <input type="number" id="nilai" name="nilai" min="0" max="100" value="<?php echo htmlspecialchars($laporan_detail['nilai'] ?? ''); ?>" 
                       class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
            </div>
            <div class="mb-4">
                <label for="status_laporan_baru" class="block text-gray-300 text-sm font-semibold mb-2">Ubah Status Laporan:</label>
                <select id="status_laporan_baru" name="status_laporan_baru" 
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100" required>
                    <option value="not_graded" <?php echo ($laporan_detail['status_laporan'] == 'not_graded') ? 'selected' : ''; ?>>Not Graded</option>
                    <option value="graded" <?php echo ($laporan_detail['status_laporan'] == 'graded') ? 'selected' : ''; ?>>Graded</option>
                </select>
            </div>
            <div class="mb-6">
                <label for="feedback" class="block text-gray-300 text-sm font-semibold mb-2">Feedback:</label>
                <textarea id="feedback" name="feedback" rows="4" 
                          class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400"><?php echo htmlspecialchars($laporan_detail['feedback'] ?? ''); ?></textarea>
            </div>
            <div class="flex items-center justify-between space-x-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-all duration-300 transform hover:scale-105 shadow-md">
                    Simpan Nilai & Status
                </button>
                <a href="laporan.php" class="inline-block align-baseline font-bold text-sm text-gray-400 hover:text-gray-200 transition-colors duration-200 py-2 px-4">
                    Kembali ke Daftar Laporan
                </a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php
require_once 'templates/footer.php';
?>
