<?php

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php'; 
require_once '../config.php'; // Koneksi database

// Inisialisasi variabel untuk statistik
$praktikum_diikuti = 0;
$tugas_selesai = 0;
$tugas_menunggu = 0;
$notifikasi_terbaru = [];

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    // Query untuk mendapatkan Total Praktikum Diikuti
    $sql_praktikum_diikuti = "SELECT COUNT(id) AS total FROM registrasi_praktikum WHERE user_id = ?";
    $stmt_praktikum_diikuti = $conn->prepare($sql_praktikum_diikuti);
    $stmt_praktikum_diikuti->bind_param("i", $user_id);
    $stmt_praktikum_diikuti->execute();
    $result_praktikum_diikuti = $stmt_praktikum_diikuti->get_result();
    if ($result_praktikum_diikuti && $result_praktikum_diikuti->num_rows > 0) {
        $row = $result_praktikum_diikuti->fetch_assoc();
        $praktikum_diikuti = $row['total'];
    }
    $stmt_praktikum_diikuti->close();

    // Query untuk mendapatkan Total Tugas Selesai (status 'graded')
    $sql_tugas_selesai = "SELECT COUNT(id) AS total FROM laporan_praktikum WHERE user_id = ? AND status_laporan = 'graded'";
    $stmt_tugas_selesai = $conn->prepare($sql_tugas_selesai);
    $stmt_tugas_selesai->bind_param("i", $user_id);
    $stmt_tugas_selesai->execute();
    $result_tugas_selesai = $stmt_tugas_selesai->get_result();
    if ($result_tugas_selesai && $result_tugas_selesai->num_rows > 0) {
        $row = $result_tugas_selesai->fetch_assoc();
        $tugas_selesai = $row['total'];
    }
    $stmt_tugas_selesai->close();

    // Query untuk mendapatkan Total Tugas Menunggu (status 'not_graded')
    $sql_tugas_menunggu = "SELECT COUNT(id) AS total FROM laporan_praktikum WHERE user_id = ? AND status_laporan = 'not_graded'";
    $stmt_tugas_menunggu = $conn->prepare($sql_tugas_menunggu);
    $stmt_tugas_menunggu->bind_param("i", $user_id);
    $stmt_tugas_menunggu->execute();
    $result_tugas_menunggu = $stmt_tugas_menunggu->get_result();
    if ($result_tugas_menunggu && $result_tugas_menunggu->num_rows > 0) {
        $row = $result_tugas_menunggu->fetch_assoc();
        $tugas_menunggu = $row['total'];
    }
    $stmt_tugas_menunggu->close();

    // Query untuk Notifikasi Terbaru (misal 3 notifikasi terkait laporan atau pendaftaran praktikum)
    $sql_notifikasi = "
        (SELECT 'laporan' AS type, lp.tanggal_unggah AS date, m.judul_modul AS item_name, lp.status_laporan AS status, lp.nilai, lp.feedback, mp.id AS praktikum_id
         FROM laporan_praktikum lp
         JOIN modul_praktikum m ON lp.modul_id = m.id
         JOIN mata_praktikum mp ON m.praktikum_id = mp.id
         WHERE lp.user_id = ?
         ORDER BY lp.tanggal_unggah DESC LIMIT 3)
        UNION ALL
        (SELECT 'registrasi' AS type, rp.tanggal_daftar AS date, mp.nama_praktikum AS item_name, NULL AS status, NULL AS nilai, NULL AS feedback, mp.id AS praktikum_id
         FROM registrasi_praktikum rp
         JOIN mata_praktikum mp ON rp.praktikum_id = mp.id
         WHERE rp.user_id = ?
         ORDER BY rp.tanggal_daftar DESC LIMIT 3)
        ORDER BY date DESC
        LIMIT 3"; // Batasi total 3 notifikasi

    $stmt_notifikasi = $conn->prepare($sql_notifikasi);
    $stmt_notifikasi->bind_param("ii", $user_id, $user_id);
    $stmt_notifikasi->execute();
    $result_notifikasi = $stmt_notifikasi->get_result();
    if ($result_notifikasi && $result_notifikasi->num_rows > 0) {
        while ($row = $result_notifikasi->fetch_assoc()) {
            $notifikasi_terbaru[] = $row;
        }
    }
    $stmt_notifikasi->close();
}

$conn->close();

?>


<div class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white p-8 rounded-xl shadow-lg mb-8">
    <h1 class="text-3xl font-bold">Selamat Datang Kembali, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
    <p class="mt-2 opacity-90">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-gray-800 p-6 rounded-xl shadow-lg flex flex-col items-center justify-center border border-gray-700">
        <div class="text-5xl font-extrabold text-blue-400"><?php echo $praktikum_diikuti; ?></div>
        <div class="mt-2 text-lg text-gray-400">Praktikum Diikuti</div>
    </div>
    
    <div class="bg-gray-800 p-6 rounded-xl shadow-lg flex flex-col items-center justify-center border border-gray-700">
        <div class="text-5xl font-extrabold text-green-400"><?php echo $tugas_selesai; ?></div>
        <div class="mt-2 text-lg text-gray-400">Tugas Selesai</div>
    </div>
    
    <div class="bg-gray-800 p-6 rounded-xl shadow-lg flex flex-col items-center justify-center border border-gray-700">
        <div class="text-5xl font-extrabold text-yellow-400"><?php echo $tugas_menunggu; ?></div>
        <div class="mt-2 text-lg text-gray-400">Tugas Menunggu</div>
    </div>
    
</div>

<div class="bg-gray-800 p-6 rounded-xl shadow-lg border border-gray-700">
    <h3 class="text-2xl font-bold text-gray-100 mb-4">Notifikasi Terbaru</h3>
    <ul class="space-y-4">
        <?php if (!empty($notifikasi_terbaru)): ?>
            <?php foreach ($notifikasi_terbaru as $notif): ?>
                <li class="flex items-start p-3 border-b border-gray-700 last:border-b-0">
                    <?php 
                    $icon = '🔔'; // Default icon
                    $notification_text = '';
                    $link_url = '';

                    // Pastikan praktikum_id tersedia untuk link
                    $current_praktikum_id_for_link = htmlspecialchars($notif['praktikum_id'] ?? '');

                    if ($notif['type'] == 'laporan') {
                        $link_url = 'detail_praktikum.php?id=' . $current_praktikum_id_for_link;
                        if ($notif['status'] == 'graded') {
                            $icon = '✅';
                            $notification_text = 'Nilai untuk <a href="' . $link_url . '" class="font-semibold text-blue-400 hover:underline">' . htmlspecialchars($notif['item_name']) . '</a> telah diberikan (Nilai: ' . htmlspecialchars($notif['nilai']) . ').';
                        } elseif ($notif['status'] == 'not_graded') {
                            $icon = '⏳';
                            $notification_text = 'Laporan untuk <a href="' . $link_url . '" class="font-semibold text-blue-400 hover:underline">' . htmlspecialchars($notif['item_name']) . '</a> telah dikumpulkan dan menunggu penilaian.';
                        }
                    } elseif ($notif['type'] == 'registrasi') {
                        $icon = '🎉';
                        $link_url = 'detail_praktikum.php?id=' . $current_praktikum_id_for_link;
                        $notification_text = 'Anda berhasil mendaftar pada mata praktikum <a href="' . $link_url . '" class="font-semibold text-blue-400 hover:underline">' . htmlspecialchars($notif['item_name']) . '</a>.';
                    }
                    ?>
                    <span class="text-xl mr-4"><?php echo $icon; ?></span>
                    <div><?php echo $notification_text; ?></div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="p-3 text-gray-400">Belum ada notifikasi terbaru.</li>
        <?php endif; ?>
    </ul>
</div>


<?php
// Panggil Footer
require_once 'templates/footer_mahasiswa.php';
?>
