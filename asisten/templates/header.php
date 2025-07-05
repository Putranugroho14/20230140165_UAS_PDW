<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login atau bukan asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Asisten - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a202c; /* Dark background */
            /* Pastikan body adalah flex container utama yang mengisi seluruh tinggi viewport */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        /* Custom scrollbar for a futuristic feel */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #2d3748; /* Darker track */
        }
        ::-webkit-scrollbar-thumb {
            background: #4a5568; /* Medium dark thumb */
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #6366f1; /* Purple accent on hover */
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100">

    <!-- Kontainer utama untuk sidebar dan main content - ini akan flex-grow -->
    <div class="flex flex-grow">
        <aside class="w-64 bg-gray-900 text-gray-100 flex flex-col shadow-xl border-r border-gray-700">
            <div class="p-6 text-center border-b border-gray-700">
                <h3 class="text-2xl font-bold text-blue-400">Panel Asisten</h3>
                <p class="text-sm text-gray-400 mt-1"><?php echo htmlspecialchars($_SESSION['nama']); ?></p>
            </div>
            <nav class="flex-grow py-4">
                <ul class="space-y-2 px-4">
                    <?php 
                        // Menyiapkan class untuk link aktif dan tidak aktif
                        $activeClass = 'bg-indigo-700 text-white shadow-md transform scale-105';
                        $inactiveClass = 'text-gray-300 hover:bg-indigo-700 hover:text-white';
                    ?>
                    <li>
                        <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-lg transition-all duration-300">
                            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="praktikum.php" class="<?php echo ($activePage == 'praktikum') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-lg transition-all duration-300">
                            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                            <span>Manajemen Mata Praktikum</span>
                        </a>
                    </li>
                    <li>
                        <a href="modul.php" class="<?php echo ($activePage == 'modul') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-lg transition-all duration-300">
                            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                            <span>Manajemen Modul</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan.php" class="<?php echo ($activePage == 'laporan') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-lg transition-all duration-300">
                            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75c0-.231-.035-.454-.1-.664M6.75 7.5h1.5M6.75 12h1.5m6.75 0h1.5m-1.5 3h1.5m-1.5 3h1.5M4.5 6.75h1.5v1.5H4.5v-1.5zM4.5 12h1.5v1.5H4.5v-1.5zM4.5 17.25h1.5v1.5H4.5v-1.5z" /></svg>
                            <span>Laporan Masuk</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="<?php echo ($activePage == 'users') ? $activeClass : $inactiveClass; ?> flex items-center px-4 py-3 rounded-lg transition-all duration-300">
                            <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a8.967 8.967 0 0015.002 0m-12.906-3.75h1.35m-1.35 0c-.354 0-.694.145-.943.402a1.25 1.25 0 01-.285.424l-2.52 3.03a.75.75 0 00.942 1.247l2.52-3.029a1.25 1.25 0 01.285-.424c.249-.257.59-.402.943-.402zm12.906 0h-1.35m1.35 0c.354 0 .694.145.943.402a1.25 1.25 0 01.285.424l2.52 3.03a.75.75 0 10.942-1.247l-2.52-3.029a1.25 1.25 0 01-.285-.424c-.249-.257-.59-.402-.943-.402z" /></svg>
                        <span>Manajemen Akun Pengguna</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="flex-1 p-6 lg:p-10 bg-gray-900 flex flex-col">
        <header class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-100"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
            <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-md">
                Logout
            </a>
        </header>
        <!-- Content Area Start -->
        <div class="flex-grow"> <!-- This div will grow to push footer down -->
