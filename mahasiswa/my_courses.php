<?php
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses';
require_once 'templates/header_mahasiswa.php';
require_once '../config.php';

// Pastikan user_id tersedia dari session
$user_id = $_SESSION['user_id'] ?? null;

$my_praktikum = [];

if ($user_id) {
    // Query untuk mengambil praktikum yang diikuti oleh mahasiswa yang sedang login
    $sql = "SELECT mp.id, mp.nama_praktikum, mp.deskripsi, mp.kode_praktikum, rp.tanggal_daftar, rp.status_registrasi
            FROM registrasi_praktikum rp
            JOIN mata_praktikum mp ON rp.praktikum_id = mp.id
            WHERE rp.user_id = ?
            ORDER BY rp.tanggal_daftar DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $my_praktikum[] = $row;
        }
    }
    $stmt->close();
}
$conn->close();
?>

<h1 class="text-3xl font-bold text-gray-100 mb-6">Praktikum yang Saya Ikuti</h1>

<?php if (!empty($my_praktikum)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($my_praktikum as $item): ?>
            <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl transform hover:-translate-y-1 border border-gray-700">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-blue-400 mb-2"><?php echo htmlspecialchars($item['nama_praktikum']); ?></h2>
                    <p class="text-sm text-gray-400 mb-2">Kode: <span class="font-semibold text-gray-200"><?php echo htmlspecialchars($item['kode_praktikum']); ?></span></p>
                    <p class="text-sm text-gray-400 mb-4">Terdaftar Sejak: <span class="font-semibold text-gray-200"><?php echo date('d M Y', strtotime($item['tanggal_daftar'])); ?></span></p>
                    <p class="text-gray-300 text-justify mb-4"><?php echo nl2br(htmlspecialchars($item['deskripsi'])); ?></p>
                    <div class="flex justify-end">
                        <a href="detail_praktikum.php?id=<?php echo htmlspecialchars($item['id']); ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-md">
                            Lihat Detail & Tugas
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="bg-gray-800 p-6 rounded-xl shadow-lg text-center border border-gray-700">
        <p class="text-gray-400 text-lg">Anda belum terdaftar pada mata praktikum apa pun.</p>
        <a href="courses.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-md">
            Cari Praktikum untuk Mendaftar
        </a>
    </div>
<?php endif; ?>

<?php
require_once 'templates/footer_mahasiswa.php';
?>
