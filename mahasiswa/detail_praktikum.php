<?php
// Setel judul halaman dan halaman aktif untuk header
$pageTitle = 'Detail Praktikum';
$activePage = 'my_courses'; // Tetap aktifkan menu 'Praktikum Saya'

// Panggil header mahasiswa dan konfigurasi database
require_once 'templates/header_mahasiswa.php';
require_once '../config.php';

// Inisialisasi variabel
$praktikum_detail = null;
$modul_list = []; // Daftar modul untuk praktikum ini
$laporan_mahasiswa = []; // Laporan yang sudah dikumpulkan oleh mahasiswa untuk praktikum ini

// Ambil ID praktikum dan user ID dari URL dan sesi
$praktikum_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// Pastikan ID praktikum dan user ID tersedia
if ($praktikum_id && $user_id) {
    // Query untuk mengambil detail mata praktikum
    $sql = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi, mp.kode_praktikum
            FROM mata_praktikum mp
            JOIN registrasi_praktikum rp ON mp.id = rp.praktikum_id
            WHERE mp.id = ? AND rp.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $praktikum_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $praktikum_detail = $result->fetch_assoc();

        // Jika detail praktikum ditemukan, ambil daftar modul terkait
        $sql_modul = "SELECT id, judul_modul, deskripsi_modul, file_materi FROM modul_praktikum WHERE praktikum_id = ? ORDER BY judul_modul ASC";
        $stmt_modul = $conn->prepare($sql_modul);
        $stmt_modul->bind_param("i", $praktikum_id);
        $stmt_modul->execute();
        $result_modul = $stmt_modul->get_result();

        if ($result_modul && $result_modul->num_rows > 0) {
            while ($row_modul = $result_modul->fetch_assoc()) {
                $modul_list[] = $row_modul;
            }
        }
        $stmt_modul->close();

        // Ambil laporan yang sudah dikumpulkan oleh mahasiswa untuk praktikum ini
        // Menggunakan modul_id sebagai kunci array untuk akses mudah nanti
        $sql_laporan = "SELECT lp.id, lp.modul_id, lp.file_laporan, lp.tanggal_unggah, lp.nilai, lp.feedback, lp.status_laporan, m.judul_modul
                        FROM laporan_praktikum lp
                        JOIN modul_praktikum m ON lp.modul_id = m.id
                        WHERE lp.user_id = ? AND m.praktikum_id = ?
                        ORDER BY m.judul_modul ASC";
        $stmt_laporan = $conn->prepare($sql_laporan);
        $stmt_laporan->bind_param("ii", $user_id, $praktikum_id);
        $stmt_laporan->execute();
        $result_laporan = $stmt_laporan->get_result();

        if ($result_laporan && $result_laporan->num_rows > 0) {
            while ($row_laporan = $result_laporan->fetch_assoc()) {
                $laporan_mahasiswa[$row_laporan['modul_id']] = $row_laporan; // Gunakan modul_id sebagai kunci
            }
        }
        $stmt_laporan->close();

    } else {
        // Jika praktikum tidak ditemukan atau mahasiswa tidak terdaftar pada praktikum ini
        echo '<div class="p-4 mb-4 text-sm border rounded-lg bg-red-600 border-red-500 text-white shadow-md" role="alert">Praktikum tidak ditemukan atau Anda tidak terdaftar pada praktikum ini.</div>';
        $praktikum_detail = false; // Tandai bahwa praktikum tidak valid
    }
    $stmt->close();
} else {
    // Jika ID praktikum tidak valid di URL
    echo '<div class="p-4 mb-4 text-sm border rounded-lg bg-red-600 border-red-500 text-white shadow-md" role="alert">ID Praktikum tidak valid.</div>';
}

// Tutup koneksi database
$conn->close();
?>

<?php
// Tampilkan pesan status jika ada dari redirect unggah_laporan.php
if (isset($_GET['status']) && isset($_GET['message'])) {
    $status = htmlspecialchars($_GET['status']);
    $message = htmlspecialchars($_GET['message']);
    $alertClass = '';
    if ($status == 'success') {
        $alertClass = 'bg-green-600 border-green-500 text-white';
    } elseif ($status == 'error') {
        $alertClass = 'bg-red-600 border-red-500 text-white';
    }
    echo '<div class="p-4 mb-4 text-sm border rounded-lg ' . $alertClass . ' shadow-md" role="alert">' . $message . '</div>';
}
?>

<?php if ($praktikum_detail && $praktikum_detail !== false): ?>
    <!-- Tombol Kembali -->
    <div class="mb-6">
        <a href="my_courses.php" class="inline-flex items-center bg-gray-700 hover:bg-gray-600 text-gray-200 font-bold py-2 px-4 rounded-lg transition-colors duration-300 transform hover:scale-105 shadow-md">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Praktikum Saya
        </a>
    </div>

    <div class="bg-gradient-to-r from-indigo-700 to-purple-800 text-white p-8 rounded-xl shadow-lg mb-8 border border-indigo-600">
        <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($praktikum_detail['nama_praktikum']); ?></h1>
        <p class="text-lg opacity-90">Kode Praktikum: <?php echo htmlspecialchars($praktikum_detail['kode_praktikum']); ?></p>
        <p class="mt-4 text-base opacity-90"><?php echo nl2br(htmlspecialchars($praktikum_detail['deskripsi'])); ?></p>
    </div>

    <div class="bg-gray-800 p-6 rounded-xl shadow-lg mb-8 border border-gray-700">
        <h3 class="text-2xl font-bold text-gray-100 mb-4">Materi Praktikum</h3>
        <ul class="space-y-4">
            <?php if (!empty($modul_list)): ?>
                <?php foreach ($modul_list as $modul_item): ?>
                    <li class="flex items-center p-3 border-b border-gray-700 last:border-b-0">
                        <span class="text-xl mr-4 text-green-400">📄</span>
                        <div>
                            <span class="font-semibold text-gray-100"><?php echo htmlspecialchars($modul_item['judul_modul']); ?></span>
                            <p class="text-sm text-gray-400"><?php echo nl2br(htmlspecialchars($modul_item['deskripsi_modul'])); ?></p>
                            <?php if (!empty($modul_item['file_materi'])): ?>
                                <a href="../uploads/modul/<?php echo htmlspecialchars($modul_item['file_materi']); ?>" target="_blank" class="text-blue-400 hover:underline text-sm transition-colors duration-200">Unduh Materi</a>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">Tidak ada file materi.</p>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="p-3 text-gray-400">Belum ada modul yang tersedia untuk praktikum ini.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="bg-gray-800 p-6 rounded-xl shadow-lg mb-8 border border-gray-700">
        <h3 class="text-2xl font-bold text-gray-100 mb-4">Pengumpulan Laporan & Nilai</h3>
        <div class="space-y-4">
            <?php if (!empty($modul_list)): ?>
                <?php foreach ($modul_list as $modul_item): ?>
                    <?php 
                        // Cek apakah mahasiswa sudah mengumpulkan laporan untuk modul ini
                        $laporan_status_data = $laporan_mahasiswa[$modul_item['id']] ?? null;
                        $status_text = 'Belum Ada Laporan';
                        $status_color = 'text-gray-400';
                        $action_button_text = 'Unggah Laporan';
                        $action_button_class = 'bg-blue-600 hover:bg-blue-700';
                        $action_button_link = 'unggah_laporan.php?modul_id=' . htmlspecialchars($modul_item['id']) . '&praktikum_id=' . htmlspecialchars($praktikum_detail['id']);
                        $file_link_button = ''; // Tombol untuk melihat/mengunduh laporan yang sudah diunggah

                        if ($laporan_status_data) {
                            // Jika laporan sudah ada
                            $file_link_button = '<a href="../uploads/laporan/' . htmlspecialchars($laporan_status_data['file_laporan']) . '" target="_blank" class="bg-gray-700 text-gray-200 px-4 py-2 rounded-lg text-sm hover:bg-gray-600 transition-colors duration-200 shadow-md">Lihat Laporan</a>';
                            
                            if ($laporan_status_data['status_laporan'] == 'not_graded') {
                                $status_text = 'Not Graded'; // Ubah teks di sini
                                $status_color = 'text-yellow-400';
                                $action_button_text = 'Perbarui Laporan';
                                $action_button_class = 'bg-indigo-600 hover:bg-indigo-700';
                                $action_button_link .= '&edit=true';
                            } elseif ($laporan_status_data['status_laporan'] == 'graded') {
                                $status_text = 'Graded (Nilai: ' . htmlspecialchars($laporan_status_data['nilai']) . ')'; // Ubah teks di sini
                                $status_color = 'text-green-400';
                                $action_button_text = 'Lihat Detail Nilai'; // Tombol ini bisa diubah sesuai kebutuhan
                                $action_button_class = 'bg-gray-700 hover:bg-gray-600';
                                $action_button_link = '#'; // Atau link ke halaman detail nilai jika ada
                            }
                            // Jika ada feedback, tambahkan ke status text
                            if (!empty($laporan_status_data['feedback'])) {
                                $status_text .= ' (Feedback: ' . htmlspecialchars($laporan_status_data['feedback']) . ')';
                            }
                        }
                    ?>
                    <div class="border border-gray-700 p-4 rounded-lg flex flex-col md:flex-row justify-between items-start md:items-center bg-gray-900">
                        <div>
                            <p class="font-semibold text-gray-100 mb-1">Laporan <?php echo htmlspecialchars($modul_item['judul_modul']); ?></p>
                            <p class="text-sm text-gray-400">Status: <span class="<?php echo $status_color; ?> font-semibold"><?php echo $status_text; ?></span></p>
                        </div>
                        <div class="mt-3 md:mt-0 flex items-center space-x-2">
                            <?php if ($laporan_status_data && $laporan_status_data['status_laporan'] == 'graded'): ?>
                                <?php echo $file_link_button; ?>
                            <?php else: ?>
                                <a href="<?php echo $action_button_link; ?>" class="<?php echo $action_button_class; ?> text-white px-4 py-2 rounded-lg text-sm transition-all duration-300 transform hover:scale-105 shadow-md">
                                    <?php echo $action_button_text; ?>
                                </a>
                                <?php if (!empty($laporan_status_data['file_laporan'])): ?>
                                    <?php echo $file_link_button; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-3 text-gray-400">Belum ada modul yang tersedia untuk pengumpulan laporan.</div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php
// Panggil footer mahasiswa
require_once 'templates/footer_mahasiswa.php';
?>
