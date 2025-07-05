<?php
$pageTitle = 'Manajemen Mata Praktikum';
$activePage = 'praktikum'; // Menandai menu aktif di sidebar asisten

require_once 'templates/header.php'; // Header untuk panel asisten
require_once '../config.php'; // Koneksi database

$praktikum = [];

// Query untuk mengambil semua data mata praktikum
$sql = "SELECT id, nama_praktikum, kode_praktikum, deskripsi FROM mata_praktikum ORDER BY nama_praktikum ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $praktikum[] = $row;
    }
}

$conn->close();
?>

<?php
// Tampilkan pesan status jika ada dari redirect praktikum_add.php, praktikum_edit.php, praktikum_delete.php
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
    <a href="praktikum_add.php" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center transition-all duration-300 transform hover:scale-105 shadow-md">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0H6"></path></svg>
        Tambah Mata Praktikum Baru
    </a>
</div>

<?php if (!empty($praktikum)): ?>
    <div class="bg-gray-800 p-6 rounded-lg shadow-lg overflow-x-auto border border-gray-700">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Kode Praktikum
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Nama Praktikum
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Deskripsi
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                <?php foreach ($praktikum as $item): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-100">
                        <?php echo htmlspecialchars($item['kode_praktikum']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100">
                        <?php echo htmlspecialchars($item['nama_praktikum']); ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-400">
                        <?php echo nl2br(htmlspecialchars(substr($item['deskripsi'], 0, 100) . (strlen($item['deskripsi']) > 100 ? '...' : ''))); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="praktikum_edit.php?id=<?php echo htmlspecialchars($item['id']); ?>" class="text-indigo-400 hover:text-indigo-300 mr-4 transition-colors duration-200">Edit</a>
                        <a href="praktikum_delete.php?id=<?php echo htmlspecialchars($item['id']); ?>" class="text-red-400 hover:text-red-300 transition-colors duration-200" onclick="return confirm('Apakah Anda yakin ingin menghapus praktikum ini?');">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="bg-gray-800 p-6 rounded-lg shadow-md text-center border border-gray-700">
        <p class="text-gray-400 text-lg">Belum ada mata praktikum yang ditambahkan.</p>
    </div>
<?php endif; ?>

<?php
require_once 'templates/footer.php'; // Footer untuk panel asisten
?>
