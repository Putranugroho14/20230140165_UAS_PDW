<?php
// Setel judul halaman dan halaman aktif untuk header
$pageTitle = 'Cari Praktikum';
$activePage = 'courses';

// Panggil header mahasiswa
require_once 'templates/header_mahasiswa.php';
require_once '../config.php'; // Panggil konfigurasi database

// Tampilkan pesan status jika ada dari redirect enroll_praktikum.php
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

// Inisialisasi variabel $praktikum untuk menampung hasil query
$praktikum = [];

// Query untuk mengambil semua data mata praktikum
$sql = "SELECT id, nama_praktikum, deskripsi, kode_praktikum FROM mata_praktikum ORDER BY nama_praktikum ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $praktikum[] = $row;
    }
}

// Tutup koneksi database
$conn->close();
?>

<h1 class="text-3xl font-bold text-gray-100 mb-6">Daftar Mata Praktikum Tersedia</h1>

<?php if (!empty($praktikum)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($praktikum as $item): ?>
            <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl transform hover:-translate-y-1 border border-gray-700">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-blue-400 mb-2"><?php echo htmlspecialchars($item['nama_praktikum']); ?></h2>
                    <p class="text-sm text-gray-400 mb-4">Kode: <span class="font-semibold text-gray-200"><?php echo htmlspecialchars($item['kode_praktikum']); ?></span></p>
                    <p class="text-gray-300 text-justify mb-4"><?php echo nl2br(htmlspecialchars($item['deskripsi'])); ?></p>
                    <div class="flex justify-end">
                        <a href="enroll_praktikum.php?id=<?php echo htmlspecialchars($item['id']); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-md">
                            Daftar Praktikum
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="bg-gray-800 p-6 rounded-xl shadow-lg text-center border border-gray-700">
        <p class="text-gray-400 text-lg">Belum ada mata praktikum yang tersedia saat ini.</p>
    </div>
<?php endif; ?>

<?php
// Panggil footer mahasiswa
require_once 'templates/footer_mahasiswa.php';
?>
