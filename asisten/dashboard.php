<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// 2. Panggil Header
require_once 'templates/header.php'; 
require_once '../config.php'; // Koneksi database

// Inisialisasi variabel untuk statistik
$total_modul = 0;
$total_laporan_masuk = 0;
$laporan_belum_dinilai = 0;
$aktivitas_laporan_terbaru = [];

// Query untuk mendapatkan Total Modul Diajarkan
$sql_total_modul = "SELECT COUNT(id) AS total FROM modul_praktikum";
$result_total_modul = $conn->query($sql_total_modul);
if ($result_total_modul && $result_total_modul->num_rows > 0) {
    $row = $result_total_modul->fetch_assoc();
    $total_modul = $row['total'];
}

// Query untuk mendapatkan Total Laporan Masuk
$sql_total_laporan = "SELECT COUNT(id) AS total FROM laporan_praktikum";
$result_total_laporan = $conn->query($sql_total_laporan);
if ($result_total_laporan && $result_total_laporan->num_rows > 0) {
    $row = $result_total_laporan->fetch_assoc();
    $total_laporan_masuk = $row['total'];
}

// Query untuk mendapatkan Laporan Belum Dinilai
$sql_belum_dinilai = "SELECT COUNT(id) AS total FROM laporan_praktikum WHERE status_laporan = 'not_graded'"; // Menggunakan 'not_graded'
$result_belum_dinilai = $conn->query($sql_belum_dinilai);
if ($result_belum_dinilai && $result_belum_dinilai->num_rows > 0) {
    $row = $result_belum_dinilai->fetch_assoc();
    $laporan_belum_dinilai = $row['total'];
}

// Query untuk mendapatkan Aktivitas Laporan Terbaru (misal 5 laporan terakhir)
$sql_aktivitas_terbaru = "SELECT lp.tanggal_unggah, u.nama AS nama_mahasiswa, m.judul_modul
                          FROM laporan_praktikum lp
                          JOIN users u ON lp.user_id = u.id
                          JOIN modul_praktikum m ON lp.modul_id = m.id
                          ORDER BY lp.tanggal_unggah DESC
                          LIMIT 5"; // Batasi 5 laporan terbaru
$result_aktivitas_terbaru = $conn->query($sql_aktivitas_terbaru);
if ($result_aktivitas_terbaru && $result_aktivitas_terbaru->num_rows > 0) {
    while ($row = $result_aktivitas_terbaru->fetch_assoc()) {
        $aktivitas_laporan_terbaru[] = $row;
    }
}

$conn->close();
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <div class="bg-gray-800 p-6 rounded-xl shadow-lg flex items-center space-x-4 border border-gray-700">
        <div class="bg-blue-700 p-3 rounded-full shadow-md">
            <svg class="w-6 h-6 text-blue-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-400">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-gray-100"><?php echo $total_modul; ?></p>
        </div>
    </div>

    <div class="bg-gray-800 p-6 rounded-lg shadow-lg flex items-center space-x-4 border border-gray-700">
        <div class="bg-green-700 p-3 rounded-full shadow-md">
            <svg class="w-6 h-6 text-green-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-400">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-100"><?php echo $total_laporan_masuk; ?></p>
        </div>
    </div>

    <div class="bg-gray-800 p-6 rounded-lg shadow-lg flex items-center space-x-4 border border-gray-700">
        <div class="bg-yellow-700 p-3 rounded-full shadow-md">
            <svg class="w-6 h-6 text-yellow-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-400">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-100"><?php echo $laporan_belum_dinilai; ?></p>
        </div>
    </div>
</div>

<div class="bg-gray-800 p-6 rounded-lg shadow-lg mt-8 border border-gray-700">
    <h3 class="text-xl font-bold text-gray-100 mb-4">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-4">
        <?php if (!empty($aktivitas_laporan_terbaru)): ?>
            <?php foreach ($aktivitas_laporan_terbaru as $aktivitas): ?>
                <div class="flex items-center p-2 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                    <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center mr-4 text-gray-300 font-bold text-lg border border-gray-600">
                        <?php echo strtoupper(substr($aktivitas['nama_mahasiswa'], 0, 2)); ?>
                    </div>
                    <div>
                        <p class="text-gray-100"><strong><?php echo htmlspecialchars($aktivitas['nama_mahasiswa']); ?></strong> mengumpulkan laporan untuk <strong><?php echo htmlspecialchars($aktivitas['judul_modul']); ?></strong></p>
                        <p class="text-sm text-gray-400"><?php echo date('d M Y H:i', strtotime($aktivitas['tanggal_unggah'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-400">Belum ada aktivitas laporan terbaru.</p>
        <?php endif; ?>
    </div>
</div>


<?php
// 3. Panggil Footer
require_once 'templates/footer.php';
?>
