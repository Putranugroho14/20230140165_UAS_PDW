<?php
$pageTitle = 'Laporan Masuk Mahasiswa';
$activePage = 'laporan'; // Menandai menu 'Laporan Masuk' aktif

require_once 'templates/header.php'; // Header untuk panel asisten
require_once '../config.php'; // Koneksi database

$laporan = [];

// Ambil parameter filter jika ada
$filter_praktikum_id = $_GET['praktikum_id'] ?? '';
$filter_modul_id = $_GET['modul_id'] ?? '';
$filter_user_id = $_GET['user_id'] ?? '';
$filter_status = $_GET['status_laporan'] ?? '';

// Query dasar untuk mengambil laporan
$sql = "SELECT lp.id AS laporan_id, lp.file_laporan, lp.tanggal_unggah, lp.nilai, lp.feedback, lp.status_laporan,
               u.nama AS nama_mahasiswa, u.email AS email_mahasiswa,
               m.judul_modul,
               mp.nama_praktikum, mp.kode_praktikum
        FROM laporan_praktikum lp
        JOIN users u ON lp.user_id = u.id
        JOIN modul_praktikum m ON lp.modul_id = m.id
        JOIN mata_praktikum mp ON m.praktikum_id = mp.id
        WHERE 1=1"; // Kondisi awal yang selalu benar untuk memudahkan penambahan filter

// Tambahkan kondisi filter
if (!empty($filter_praktikum_id)) {
    $sql .= " AND mp.id = " . intval($filter_praktikum_id);
}
if (!empty($filter_modul_id)) {
    $sql .= " AND m.id = " . intval($filter_modul_id);
}
if (!empty($filter_user_id)) {
    // Untuk filter user_id, kita bisa mencari berdasarkan ID atau nama/email jika diperlukan
    // Untuk saat ini, asumsikan filter_user_id adalah ID pengguna
    $sql .= " AND u.id = " . intval($filter_user_id);
}
if (!empty($filter_status)) {
    $sql .= " AND lp.status_laporan = '" . $conn->real_escape_string($filter_status) . "'";
}

$sql .= " ORDER BY lp.tanggal_unggah DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $laporan[] = $row;
    }
}

// Ambil data untuk dropdown filter
$praktikum_filter_list = [];
$modul_filter_list = [];
$mahasiswa_filter_list = [];

$sql_filter_praktikum = "SELECT id, nama_praktikum, kode_praktikum FROM mata_praktikum ORDER BY nama_praktikum ASC";
$res_filter_praktikum = $conn->query($sql_filter_praktikum);
if ($res_filter_praktikum) {
    while ($row = $res_filter_praktikum->fetch_assoc()) {
        $praktikum_filter_list[] = $row;
    }
}

$sql_filter_modul = "SELECT m.id, m.judul_modul, mp.nama_praktikum FROM modul_praktikum m JOIN mata_praktikum mp ON m.praktikum_id = mp.id ORDER BY mp.nama_praktikum, m.judul_modul ASC";
$res_filter_modul = $conn->query($sql_filter_modul);
if ($res_filter_modul) {
    while ($row = $res_filter_modul->fetch_assoc()) {
        $modul_filter_list[] = $row;
    }
}

$sql_filter_mahasiswa = "SELECT id, nama, email FROM users WHERE role = 'mahasiswa' ORDER BY nama ASC";
$res_filter_mahasiswa = $conn->query($sql_filter_mahasiswa);
if ($res_filter_mahasiswa) {
    while ($row = $res_filter_mahasiswa->fetch_assoc()) {
        $mahasiswa_filter_list[] = $row;
    }
}


$conn->close();
?>


<?php
// Tampilkan pesan status jika ada dari redirect laporan_detail.php
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

<div class="bg-gray-800 p-6 rounded-lg shadow-lg mb-6 border border-gray-700">
    <h3 class="text-xl font-bold text-gray-100 mb-4">Filter Laporan</h3>
    <form action="laporan.php" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label for="praktikum_id" class="block text-gray-300 text-sm font-semibold mb-2">Mata Praktikum:</label>
            <select id="praktikum_id" name="praktikum_id" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100">
                <option value="">Semua Praktikum</option>
                <?php foreach ($praktikum_filter_list as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['id']); ?>" <?php echo ($filter_praktikum_id == $item['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['nama_praktikum'] . ' (' . $item['kode_praktikum'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="modul_id" class="block text-gray-300 text-sm font-semibold mb-2">Modul:</label>
            <select id="modul_id" name="modul_id" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100">
                <option value="">Semua Modul</option>
                <?php foreach ($modul_filter_list as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['id']); ?>" <?php echo ($filter_modul_id == $item['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['nama_praktikum'] . ' - ' . $item['judul_modul']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="user_id" class="block text-gray-300 text-sm font-semibold mb-2">Mahasiswa:</label>
            <select id="user_id" name="user_id" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100">
                <option value="">Semua Mahasiswa</option>
                <?php foreach ($mahasiswa_filter_list as $item): ?>
                    <option value="<?php echo htmlspecialchars($item['id']); ?>" <?php echo ($filter_user_id == $item['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($item['nama'] . ' (' . $item['email'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="status_laporan" class="block text-gray-300 text-sm font-semibold mb-2">Status:</label>
            <select id="status_laporan" name="status_laporan" class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 text-gray-100">
                <option value="">Semua Status</option>
                <option value="not_graded" <?php echo ($filter_status == 'not_graded') ? 'selected' : ''; ?>>Not Graded</option>
                <option value="graded" <?php echo ($filter_status == 'graded') ? 'selected' : ''; ?>>Graded</option>
            </select>
        </div>
        <div class="md:col-span-2 lg:col-span-4 flex justify-end mt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-all duration-300 transform hover:scale-105 shadow-md">
                Terapkan Filter
            </button>
            <a href="laporan.php" class="ml-2 bg-gray-700 hover:bg-gray-600 text-gray-200 font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-all duration-300 transform hover:scale-105 shadow-md">
                Reset Filter
            </a>
        </div>
    </form>
</div>

<?php if (!empty($laporan)): ?>
    <div class="bg-gray-800 p-6 rounded-lg shadow-lg overflow-x-auto border border-gray-700">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Praktikum
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Modul
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Mahasiswa
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        File Laporan
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Tanggal Unggah
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Nilai
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                <?php foreach ($laporan as $item): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-100">
                        <?php echo htmlspecialchars($item['nama_praktikum']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100">
                        <?php echo htmlspecialchars($item['judul_modul']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100">
                        <?php echo htmlspecialchars($item['nama_mahasiswa']); ?><br>
                        <span class="text-xs text-gray-400"><?php echo htmlspecialchars($item['email_mahasiswa']); ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                        <a href="../uploads/laporan/<?php echo htmlspecialchars($item['file_laporan']); ?>" target="_blank" class="text-blue-400 hover:underline transition-colors duration-200">Unduh Laporan</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                        <?php echo date('d M Y H:i', strtotime($item['tanggal_unggah'])); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100">
                        <?php echo ($item['nilai'] !== null) ? htmlspecialchars($item['nilai']) : '-'; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php 
                                if ($item['status_laporan'] == 'not_graded') echo 'bg-yellow-700 text-yellow-200';
                                elseif ($item['status_laporan'] == 'graded') echo 'bg-green-700 text-green-200';
                            ?>">
                            <?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($item['status_laporan']))); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="laporan_detail.php?id=<?php echo htmlspecialchars($item['laporan_id']); ?>" class="text-indigo-400 hover:text-indigo-300 transition-colors duration-200">Lihat/Nilai</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="bg-gray-800 p-6 rounded-lg shadow-md text-center border border-gray-700">
        <p class="text-gray-400 text-lg">Belum ada laporan yang dikumpulkan.</p>
    </div>
<?php endif; ?>

<?php
require_once 'templates/footer.php'; // Footer untuk panel asisten
?>
