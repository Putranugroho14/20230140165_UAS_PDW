<?php
$pageTitle = 'Unggah Laporan';
$activePage = 'my_courses'; // Tetap aktifkan menu 'Praktikum Saya'

require_once 'templates/header_mahasiswa.php';
require_once '../config.php';

$message = '';
$messageType = '';
$modul_id = $_GET['modul_id'] ?? null;
$praktikum_id = $_GET['praktikum_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// Inisialisasi status edit dan informasi laporan yang sudah ada
$is_existing_report = false; // Flag untuk menandai apakah laporan sudah ada
$existing_laporan_id = null; // ID laporan yang sudah ada
$existing_file_laporan = null; // Nama file laporan yang sudah ada

$modul_info = null;

// Pastikan modul_id, praktikum_id, dan user_id valid
if (!$modul_id || !$praktikum_id || !$user_id) {
    $message = "Parameter tidak lengkap.";
    $messageType = 'error';
} else {
    // Ambil informasi modul
    $sql_modul = "SELECT id, judul_modul FROM modul_praktikum WHERE id = ?";
    $stmt_modul = $conn->prepare($sql_modul);
    $stmt_modul->bind_param("i", $modul_id);
    $stmt_modul->execute();
    $result_modul = $stmt_modul->get_result();
    if ($result_modul->num_rows === 1) {
        $modul_info = $result_modul->fetch_assoc();
    } else {
        $message = "Modul tidak ditemukan.";
        $messageType = 'error';
    }
    $stmt_modul->close();

    // Cek apakah sudah ada laporan untuk modul ini dari user ini
    if ($modul_info) {
        $sql_check_laporan = "SELECT id, file_laporan, status_laporan FROM laporan_praktikum WHERE modul_id = ? AND user_id = ?";
        $stmt_check_laporan = $conn->prepare($sql_check_laporan);
        $stmt_check_laporan->bind_param("ii", $modul_id, $user_id);
        $stmt_check_laporan->execute();
        $result_check_laporan = $stmt_check_laporan->get_result();
        if ($result_check_laporan->num_rows > 0) {
            $existing_laporan_data = $result_check_laporan->fetch_assoc();
            $is_existing_report = true;
            $existing_laporan_id = $existing_laporan_data['id'];
            $existing_file_laporan = $existing_laporan_data['file_laporan'];
        }
        $stmt_check_laporan->close();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && $modul_info) {
    $uploaded_file_name = null;

    // Handle file upload
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/laporan/"; // Folder untuk menyimpan file laporan
        $file_extension = pathinfo($_FILES['file_laporan']['name'], PATHINFO_EXTENSION);
        $new_file_name = uniqid('laporan_') . '.' . $file_extension; // Nama file unik
        $target_file = $target_dir . $new_file_name;

        // Pastikan direktori uploads ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['file_laporan']['tmp_name'], $target_file)) {
            $uploaded_file_name = $new_file_name;
            // Hapus file lama jika ada dan ini adalah update
            if ($is_existing_report && !empty($existing_file_laporan) && file_exists($target_dir . $existing_file_laporan)) {
                unlink($target_dir . $existing_file_laporan);
            }
        } else {
            $message = "Gagal mengunggah file laporan.";
            $messageType = 'error';
        }
    } else { // Jika tidak ada file diunggah
        $message = "File laporan harus diunggah.";
        $messageType = 'error';
    }

    // Jika tidak ada error upload dan file_laporan berhasil diatur
    if ($messageType !== 'error' && $uploaded_file_name) {
        if ($is_existing_report) {
            // Lakukan UPDATE jika laporan sudah ada
            // Status diatur ke 'not_graded' saat diperbarui, nilai dan feedback dikosongkan
            $update_sql = "UPDATE laporan_praktikum SET file_laporan = ?, tanggal_unggah = CURRENT_TIMESTAMP, status_laporan = 'not_graded', nilai = NULL, feedback = NULL WHERE id = ?";
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param("si", $uploaded_file_name, $existing_laporan_id);

            if ($stmt_update->execute()) {
                $message = "Laporan berhasil diperbarui!";
                $messageType = 'success';
            } else {
                $message = "Gagal memperbarui laporan. Silakan coba lagi.";
                $messageType = 'error';
            }
            $stmt_update->close();
        } else {
            // Lakukan INSERT jika laporan belum ada
            // Status awal adalah 'not_graded'
            $insert_sql = "INSERT INTO laporan_praktikum (modul_id, user_id, file_laporan, status_laporan) VALUES (?, ?, ?, 'not_graded')";
            $stmt_insert = $conn->prepare($insert_sql);
            $stmt_insert->bind_param("iis", $modul_id, $user_id, $uploaded_file_name);

            if ($stmt_insert->execute()) {
                $message = "Laporan berhasil diunggah!";
                $messageType = 'success';
            } else {
                $message = "Gagal mengunggah laporan. Silakan coba lagi.";
                $messageType = 'error';
            }
            $stmt_insert->close();
        }
    }
}
$conn->close();

// Redirect kembali ke halaman detail praktikum dengan pesan
if ($messageType) {
    header("Location: detail_praktikum.php?id=" . htmlspecialchars($praktikum_id) . "&status=" . $messageType . "&message=" . urlencode($message));
    exit();
}
?>

<h1 class="text-3xl font-bold text-gray-100 mb-6"><?php echo $is_existing_report ? 'Edit Laporan' : 'Unggah Laporan'; ?>: <?php echo htmlspecialchars($modul_info['judul_modul'] ?? 'Modul'); ?></h1>

<?php if ($message): ?>
    <div class="p-4 mb-4 text-sm border rounded-lg <?php echo $messageType == 'success' ? 'bg-green-600 border-green-500 text-white' : 'bg-red-600 border-red-500 text-white'; ?> shadow-md" role="alert">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<?php if ($modul_info): ?>
<div class="bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-700">
    <form action="unggah_laporan.php?modul_id=<?php echo htmlspecialchars($modul_id); ?>&praktikum_id=<?php echo htmlspecialchars($praktikum_id); ?>" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="file_laporan" class="block text-gray-300 text-sm font-semibold mb-2">Pilih File Laporan (PDF/DOCX):</label>
            <input type="file" id="file_laporan" name="file_laporan" accept=".pdf,.doc,.docx" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100 placeholder-gray-400" required>
            <p class="text-xs text-gray-500 mt-1">Ukuran file maksimal: 5MB</p>
            <?php if ($is_existing_report && !empty($existing_file_laporan)): ?>
                <p class="text-sm text-gray-400 mt-2">File laporan saat ini: <a href="../uploads/laporan/<?php echo htmlspecialchars($existing_file_laporan); ?>" target="_blank" class="text-blue-400 hover:underline"><?php echo htmlspecialchars($existing_file_laporan); ?></a></p>
            <?php endif; ?>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-all duration-300 transform hover:scale-105 shadow-md">
                <?php echo $is_existing_report ? 'Perbarui Laporan' : 'Unggah Laporan'; ?>
            </button>
            <a href="detail_praktikum.php?id=<?php echo htmlspecialchars($praktikum_id); ?>" class="inline-block align-baseline font-bold text-sm text-gray-400 hover:text-gray-200 transition-colors duration-200">
                Batal
            </a>
        </div>
    </form>
</div>
<?php else: ?>
    <div class="p-4 mb-4 text-sm border rounded-lg bg-red-600 border-red-500 text-white shadow-md" role="alert">Informasi modul tidak tersedia.</div>
<?php endif; ?>

<?php
require_once 'templates/footer_mahasiswa.php';
?>
