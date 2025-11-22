<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Earth - Sistem Manajemen File</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
        }

        .sidebar {
            transition: all 0.3s ease;
            height: 100vh;
            overflow-y: auto;
        }

        .file-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }

        .file-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: #3b82f6;
        }

        .progress-bar {
            transition: width 0.5s ease-in-out;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            z-index: 50;
            min-width: 160px;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        #fileInput {
            display: none;
        }

        .drag-over {
            border-color: #3b82f6 !important;
            background-color: #eff6ff !important;
            transform: scale(1.02);
        }

        .main-content {
            margin-left: 16rem;
            min-height: 100vh;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 40;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
            }
        }

        .storage-progress {
            background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 50%, #93c5fd 100%);
        }

        .notification-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .file-type-icon {
            transition: transform 0.3s ease;
        }

        .file-card:hover .file-type-icon {
            transform: scale(1.1);
        }

        .table-row:hover {
            background-color: #f9fafb;
        }

        .sortable:hover {
            cursor: pointer;
            background-color: #f3f4f6;
        }

        .earth-preview {
            height: 120px;
            background: linear-gradient(135deg, #0f9b0f 0%, #3ddb3d 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-bottom: 12px;
            position: relative;
            overflow: hidden;
        }

        .earth-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="%230f9b0f" stroke="%233ddb3d" stroke-width="2"/><path d="M50 10 A40 40 0 0 1 90 50 A40 40 0 0 1 50 90 A40 40 0 0 1 10 50 A40 40 0 0 1 50 10 Z" fill="none" stroke="%233ddb3d" stroke-width="1"/></svg>') center/cover no-repeat;
            opacity: 0.3;
        }

        .earth-glow {
            filter: drop-shadow(0 0 8px rgba(61, 219, 61, 0.6));
        }

        /* Tambahan untuk styling filter aktif */
        .filter-option.active {
            background-color: #eff6ff;
            color: #3b82f6;
            font-weight: 500;
        }

        /* Modal Share Styles */
        .fade-in {
            animation: fadeIn 0.2s ease-in-out;
        }

        .slide-down {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .selected-user {
            background-color: #e0f2fe;
            border-color: #0ea5e9;
        }

        /* Scrollbar lembut dan minimalis */
        #filterDropdown::-webkit-scrollbar {
            width: 6px;
        }

        #filterDropdown::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.6);
            border-radius: 3px;
        }

        #filterDropdown::-webkit-scrollbar-thumb:hover {
            background-color: rgba(107, 114, 128, 0.8);
        }

        /* Styling untuk fitur pencarian yang diperbaiki */
        .search-container {
            position: relative;
            min-width: 250px;
        }

        .search-loading {
            position: absolute;
            right: 3rem;
            top: 50%;
            transform: translateY(-50%);
            color: #3b82f6;
        }

        .search-results-info {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 0.875rem;
            color: #0369a1;
        }

        .search-highlight {
            background-color: #fef3c7;
            padding: 0 2px;
            border-radius: 2px;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Mobile Menu Button -->
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="mobileMenuBtn" class="bg-white p-2 rounded-lg shadow-lg">
            <i class="fas fa-bars text-gray-700"></i>
        </button>
    </div>

    <!-- Sidebar -->
    <div class="sidebar fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-xl flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-center h-20 bg-gradient-to-r from-blue-500 to-indigo-600 text-white">
            <i class="fas fa-cloud text-2xl mr-3"></i>
            <h1 class="text-xl font-bold">CloudStorage</h1>
        </div>

        <!-- User Info -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                @php
                    $username = Auth::user()->username ?? 'User';
                    $initials = collect(explode(' ', $username))
                        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                        ->join('');
                    if (strlen($initials) > 2) {
                        $initials = substr($initials, 0, 2);
                    }
                @endphp

                <img class="h-10 w-10 rounded-full"
                    src="https://ui-avatars.com/api/?name={{ $initials }}&background=3B82F6&color=ffffff&bold=true"
                    alt="{{ $initials }}">

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->username }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>

        <!-- Menu Section -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Section Title -->
            <div class="px-6 py-3">
                <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Menu Utama
                </h2>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 pb-20 space-y-1">
                <a href="/dashboard"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-home w-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>

                <a href="/all-file"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-file w-5 mr-3"></i>
                    <span>Semua File</span>
                    <span
                        class="ml-auto bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">{{ $totalFiles }}</span>
                </a>

                <a href="/images"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-images w-5 mr-3"></i>
                    <span>Gambar</span>
                    <span
                        class="ml-auto bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">{{ $totalImages }}</span>
                </a>

                <a href="/docks"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-file-pdf w-5 mr-3"></i>
                    <span>Dokumen</span>
                    <span
                        class="ml-auto bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">{{ $totalDocuments }}</span>
                </a>

                <a href="/video"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-file-video w-5 mr-3"></i>
                    <span>Video</span>
                    <span
                        class="ml-auto bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">{{ $totalVideos }}</span>
                </a>

                <a href="/audio"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-music w-5 mr-3"></i>
                    <span>Audio</span>
                    <span
                        class="ml-auto bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">{{ $totalAudios }}</span>
                </a>
                <a href="/archive"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-file-zipper w-5 mr-3"></i>
                    <span>Archive</span>
                    <span
                        class="ml-auto bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">{{ $totalArchives }}</span>
                </a>

                <a href="/share"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-share-alt w-5 mr-3"></i>
                    <span>Berbagi</span>
                    <span class="ml-auto bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">24</span>
                </a>

                <a href="/favorites"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-star w-5 mr-3"></i>
                    <span>Favorit</span>
                    <span
                        class="ml-auto bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">{{ $favoriteFiles }}</span>
                </a>

                <!-- Google Earth Menu Item -->
                <a href="/google-earth"
                    class="flex items-center px-3 py-3 text-sm font-medium rounded-lg bg-blue-50 text-blue-700 border-l-4 border-blue-500">
                    <i class="fas fa-globe-americas w-5 mr-3 text-blue-500"></i>
                    <span>Google Earth</span>
                    <span
                        class="ml-auto bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">{{ $totalMap }}</span>
                </a>
            </nav>
        </div>

        <!-- Bottom Section -->
        <div class="sticky bottom-0 w-full p-4 border-t border-gray-200 bg-white">
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Penyimpanan</span>
                    <span>{{ $usedSpace }} / {{ $totalSpace }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="storage-progress h-2 rounded-full bg-blue-500 transition-all duration-500"
                        style="width: {{ $usedPercent }};">
                    </div>
                </div>
            </div>

            <div class="flex space-x-3">
                <a id="settingsBtn"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg text-sm font-medium text-center transition">
                    <i class="fas fa-cog mr-1"></i> Settings
                </a>
                <a href="/logout"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded-lg text-sm font-medium text-center transition">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="flex justify-between items-center px-6 py-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Google Earth</h1>
                    <p class="text-gray-600">Kelola file Google Earth (KML, KMZ) Anda</p>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Quick Actions -->
                    <div class="flex space-x-2">
                        <button onclick="window.location.reload()"
                            class="p-2.5 text-gray-600 hover:text-blue-600 bg-gray-100 hover:bg-blue-100 rounded-xl transition-colors">
                            <i class="fas fa-sync-alt text-lg"></i>
                        </button>
                        <button
                            class="p-2.5 text-gray-600 hover:text-blue-600 bg-gray-100 hover:bg-blue-100 rounded-xl transition-colors">
                            <i class="fas fa-question-circle text-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="p-6">
            <!-- Google Earth Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total File Earth -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-green-100 text-green-600">
                            <i class="fas fa-globe-americas text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total File</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="totalEarthFilesCount">0</h3>
                        </div>
                    </div>
                </div>

                <!-- KML Files -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-blue-100 text-blue-600">
                            <i class="fas fa-map-marked-alt text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">File KML</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="kmlCount">0</h3>
                        </div>
                    </div>
                </div>

                <!-- KMZ Files -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-purple-100 text-purple-600">
                            <i class="fas fa-layer-group text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">File KMZ</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="kmzCount">0</h3>
                        </div>
                    </div>
                </div>

                <!-- Total Size -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-orange-100 text-orange-600">
                            <i class="fas fa-database text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Ukuran</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="totalSize">0 MB</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Files Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div
                    class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Semua File Google Earth</h3>
                        <p class="text-gray-600 mt-1">Kelola koleksi file peta dan geolokasi Anda</p>
                    </div>
                    <div class="flex space-x-2">
                        <!-- View Toggle -->
                        <div class="flex bg-gray-100 rounded-xl p-1">
                            <button id="gridViewBtn"
                                class="p-2 rounded-lg text-gray-700 hover:bg-white hover:shadow-sm transition-all view-toggle active">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button id="listViewBtn"
                                class="p-2 rounded-lg text-gray-700 hover:bg-white hover:shadow-sm transition-all view-toggle">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>

                        <!-- Search - Diperbaiki -->
                        <div class="search-container">
                            <input type="text" id="searchInput" placeholder="Cari file Google Earth..."
                                class="w-full bg-gray-100 border-0 rounded-xl py-2.5 pl-4 pr-10 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all" />
                            <i class="fas fa-search absolute right-3 top-3.5 text-gray-500"></i>
                            <div id="searchLoading" class="search-loading hidden">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>

                        <!-- Filter Format -->
                        <div class="relative inline-block text-left">
                            <!-- Tombol Filter -->
                            <button id="filterButton"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-4 rounded-xl font-medium transition-colors duration-200 flex items-center">
                                <i class="fas fa-filter mr-2"></i> Filter
                                <i class="fas fa-chevron-down ml-2 text-gray-500"></i>
                            </button>

                            <!-- Dropdown Filter -->
                            @php
                                // Ambil 8 file Google Earth terakhir
                                $latestEarthFiles = collect($files)->filter(function($file) {
                                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                                    return in_array(strtolower($ext), ['kml', 'kmz']);
                                })->sortByDesc('created_at')->take(8);

                                // Ambil tipe dari 8 file Google Earth terakhir saja
                                $latestTypes = $latestEarthFiles->pluck('type')->unique()->values();

                                // Cek apakah ada KML di antara 8 file terakhir
                                $hasKML = $latestTypes->contains(function ($type) {
                                    return in_array(strtolower($type), ['kml']);
                                });

                                // Cek apakah ada KMZ di antara 8 file terakhir
                                $hasKMZ = $latestTypes->contains(function ($type) {
                                    return in_array(strtolower($type), ['kmz']);
                                });
                            @endphp

                            <div id="filterDropdown"
                                class="hidden absolute right-0 mt-2 max-h-64 overflow-y-auto w-48 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 z-10 transition-all duration-200">
                                <ul class="py-2 text-gray-700" id="filterList">
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md active"
                                            data-type="all" onclick="filterEarthFiles('all')">
                                            Semua
                                        </button>
                                    </li>

                                    @if ($hasKML)
                                        <li>
                                            <button
                                                class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                                data-type="kml" onclick="filterEarthFiles('kml')">
                                                KML Files
                                            </button>
                                        </li>
                                    @endif

                                    @if ($hasKMZ)
                                        <li>
                                            <button
                                                class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                                data-type="kmz" onclick="filterEarthFiles('kmz')">
                                                KMZ Files
                                            </button>
                                        </li>
                                    @endif

                                    {{-- 🔹 Render hanya tipe file Google Earth yang tersedia --}}
                                    @foreach ($latestTypes as $type)
                                        @if (!in_array(strtolower($type), ['kml', 'kmz']))
                                            <li>
                                                <button
                                                    class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                                    data-type="{{ strtolower($type) }}"
                                                    onclick="filterEarthFiles('{{ strtolower($type) }}')">
                                                    {{ strtoupper($type) }}
                                                </button>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Files Content -->
                <div class="p-6">
                    <!-- Search Results Info -->
                    <div id="searchResultsInfo" class="search-results-info hidden mb-4">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span id="searchResultsText"></span>
                    </div>

                    <!-- Grid View -->
                    <div id="gridView" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <!-- Google Earth cards will be dynamically added here -->
                    </div>

                    <!-- List View -->
                    <div id="listView" class="hidden">
                        <div class="overflow-x-auto rounded-xl border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                            data-sort="name">
                                            <div class="flex items-center">
                                                <span>Nama File</span>
                                                <i class="fas fa-sort ml-2 text-gray-400"></i>
                                            </div>
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                            data-sort="type">
                                            <div class="flex items-center">
                                                <span>Tipe</span>
                                                <i class="fas fa-sort ml-2 text-gray-400"></i>
                                            </div>
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                            data-sort="size">
                                            <div class="flex items-center">
                                                <span>Ukuran</span>
                                                <i class="fas fa-sort ml-2 text-gray-400"></i>
                                            </div>
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                            data-sort="date">
                                            <div class="flex items-center">
                                                <span>Tanggal</span>
                                                <i class="fas fa-sort ml-2 text-gray-400"></i>
                                            </div>
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="fileTableBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Google Earth rows will be dynamically added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-12 hidden">
                        <div class="mb-4">
                            <i class="fas fa-globe-americas text-5xl text-gray-300 mb-3"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Belum ada file Google Earth</h3>
                        <p class="text-gray-500 mb-6">Upload file KML atau KMZ pertama Anda untuk memulai</p>
                    </div>

                    <!-- No Search Results State -->
                    <div id="noSearchResults" class="text-center py-12 hidden">
                        <div class="mb-4">
                            <i class="fas fa-search text-5xl text-gray-300 mb-3"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Tidak ada hasil pencarian</h3>
                        <p class="text-gray-500 mb-6">Coba gunakan kata kunci lain atau hapus filter</p>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-6 flex justify-between items-center hidden">
                        <div class="text-sm text-gray-700">
                            Menampilkan <span id="startItem">1</span> - <span id="endItem">10</span> dari <span
                                id="totalItems">0</span> file
                        </div>
                        <div class="flex space-x-2">
                            <button id="prevPage"
                                class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div id="pageNumbers" class="flex space-x-1"></div>
                            <button id="nextPage"
                                class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- File Detail Modal -->
    <div id="fileModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 transform transition-transform">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Detail File Google Earth</h3>
                <button id="closeModal"
                    class="text-gray-500 hover:text-gray-700 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6" id="modalContent">
                <!-- Modal content will be dynamically added here -->
            </div>
        </div>
    </div>

    <!-- Share Modal -->
    <div id="shareModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden transition-opacity duration-300">
        <div
            class="modal-box bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 transform transition-all duration-300 slide-down">

            <!-- HEADER -->
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Bagikan File</h3>
                    <p class="text-sm text-gray-500 mt-1">Pilih penerima untuk berbagi</p>
                </div>
                <button
                    class="close-share-modal text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- BODY -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- KIRI: File + Search + Users -->
                <div class="space-y-6">
                    <!-- FILE INFO -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">File yang akan dibagikan:</h4>
                        <div id="sharedFileInfo" class="bg-gray-50 p-3 rounded-lg flex items-center">
                            <i class="fas fa-globe-americas text-blue-500 mr-3"></i>
                            <span id="sharedFileName" class="font-medium">Nama File</span>
                        </div>
                    </div>

                    <!-- SEARCH -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-search mr-2 text-gray-500"></i>Cari Penerima
                        </label>
                        <div class="relative">
                            <input type="text"
                                class="search-users w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                placeholder="Cari nama atau email...">
                            <button type="button"
                                class="clear-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 hidden">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- USER LIST -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-users mr-2 text-gray-500"></i>Pilih Penerima
                        </label>
                        <div class="border border-gray-300 rounded-xl max-h-64 overflow-y-auto">
                            <div class="users-list divide-y divide-gray-200"></div>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Selected Users -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <i class="fas fa-user-check mr-2 text-gray-500"></i>
                            Penerima Dipilih <span class="selected-count text-blue-500 ml-1">(0)</span>
                        </label>
                        <div
                            class="selected-users flex flex-wrap gap-2 min-h-12 p-3 border border-gray-300 rounded-xl bg-gray-50">
                            <p class="placeholder text-gray-500 text-sm py-2 px-3">Belum ada penerima dipilih</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER BUTTONS -->
            <div class="px-6 pb-6 flex space-x-3">
                <button type="button"
                    class="cancel-share flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-4 rounded-xl font-medium transition-colors">
                    Batal
                </button>
                <button type="button"
                    class="share-button flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded-xl font-medium flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    <i class="fas fa-share-alt mr-2"></i>Bagikan
                </button>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div id="settingsModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden transition-opacity">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 transform transition-transform flex flex-col">

            <!-- Header -->
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Pengaturan Akun</h3>
                <button id="closeSettingsModal"
                    class="text-gray-500 hover:text-gray-700 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 flex flex-col space-y-6">

                <form id="settingsForm" class="flex flex-col space-y-6" action="/settings/update" method="POST">
                    @csrf
                    <!-- Username -->
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-gray-500"></i>Username
                        </label>
                        <input type="text" id="username" name="username" value="{{ Auth::user()->username }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl 
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <div id="usernameError" class="error-message"></div>
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-gray-500"></i>Email
                        </label>
                        <input type="email" id="email" name="email" value="{{ Auth::user()->email }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl 
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <div id="emailError" class="error-message"></div>
                    </div>

                    <!-- Password Baru + Konfirmasi -->
                    <div class="flex flex-col space-y-6">

                        <!-- Password Baru -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-gray-500"></i>Password Baru
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-xl 
                                focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="Masukkan password baru">
                                <button type="button" id="togglePassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordStrength" class="password-strength mt-2"></div>
                            <div id="passwordError" class="error-message"></div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-gray-500"></i>Konfirmasi Password
                            </label>
                            <div class="relative">
                                <input type="password" id="confirmPassword" name="password_confirmation" class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-xl 
                                focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="Konfirmasi password baru">
                                <button type="button" id="toggleConfirmPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="confirmPasswordError" class="error-message"></div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex space-x-3 pt-4">
                        <button type="button" id="cancelSettings"
                            class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-4 rounded-xl font-medium">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded-xl font-medium flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let earthFiles = [];
        let allEarthFiles = [];
        let currentView = 'grid';
        let currentPage = 1;
        const itemsPerPage = 12;
        let currentSort = { field: 'date', direction: 'desc' };
        let activeFilterType = 'all';
        let searchTimeout = null;

        // Variabel untuk fitur berbagi
        let currentFileToShare = null;
        let users = [];
        let selectedUsers = [];
        let filteredUsers = [];

        // Google Earth file types configuration
        const earthFileTypes = ['kml', 'kmz'];

        const fileConfig = {
            kml: {
                icon: 'map-marked-alt',
                color: 'text-blue-500 bg-blue-100',
                type: 'KML File',
                previewColor: 'bg-gradient-to-br from-blue-500 to-blue-600',
                description: 'Keyhole Markup Language - Format file untuk menyimpan data geospasial'
            },
            kmz: {
                icon: 'layer-group',
                color: 'text-purple-500 bg-purple-100',
                type: 'KMZ File',
                previewColor: 'bg-gradient-to-br from-purple-500 to-purple-600',
                description: 'Kompresi KML - File ZIP yang berisi KML dan resource terkait'
            },
            default: {
                icon: 'globe-americas',
                color: 'text-green-500 bg-green-100',
                type: 'Earth File',
                previewColor: 'bg-gradient-to-br from-green-500 to-green-600',
                description: 'File data geospasial untuk Google Earth'
            },
        };

        // ==================== FUNGSI BERBAGI FILE ====================

        // Fungsi untuk membuka modal berbagi
        function openShareModal(fileName) {
            currentFileToShare = fileName;
            const modal = document.getElementById('shareModal');
            const fileNameElement = document.getElementById('sharedFileName');

            fileNameElement.textContent = fileName;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Reset dan render ulang daftar pengguna
            selectedUsers = [];
            filteredUsers = [...users];
            renderUsersList();
            renderSelectedUsers();
            updateShareButton();

            setTimeout(() => {
                const searchInput = document.querySelector('.search-users');
                if (searchInput) searchInput.focus();
            }, 300);
        }

        // Fungsi untuk menutup modal berbagi
        function closeShareModal() {
            const modal = document.getElementById('shareModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            resetShareForm();
        }

        // Fungsi untuk merender daftar pengguna
        function renderUsersList() {
            const usersList = document.querySelector('.users-list');
            if (!usersList) return;

            usersList.innerHTML = '';

            if (filteredUsers.length === 0) {
                usersList.innerHTML = `
                    <div class="p-4 text-center text-gray-500">
                        <i class="fas fa-user-slash text-2xl mb-2"></i>
                        <p>Tidak ada pengguna</p>
                    </div>`;
                return;
            }

            filteredUsers.forEach(user => {
                const isSelected = selectedUsers.some(u => u.id === user.id);

                const div = document.createElement('div');
                div.className = `
                    p-3 cursor-pointer transition-all duration-200 
                    ${isSelected ? 'selected-user bg-blue-50' : 'hover:bg-gray-50'}
                `;

                div.innerHTML = `
                    <div class="flex items-center">
                        <div class="w-10 h-10 
                            ${user.color || 'bg-blue-500'} 
                            rounded-full flex items-center justify-center 
                            text-white font-medium mr-3">
                            ${user.avatar || user.name.substring(0, 2).toUpperCase()}
                        </div>

                        <div class="flex-1">
                            <div class="font-medium">${user.name}</div>
                            <div class="text-sm text-gray-500">${user.email}</div>
                        </div>

                        <div class="w-5 h-5 rounded-full border-2 
                            ${isSelected ? 'bg-blue-500 border-blue-500' : 'border-gray-300'} 
                            flex items-center justify-center">
                            ${isSelected ? '<i class="fas fa-check text-white text-xs"></i>' : ''}
                        </div>
                    </div>
                `;

                div.addEventListener('click', () => toggleUser(user));
                usersList.appendChild(div);
            });
        }

        // Fungsi untuk toggle pemilihan pengguna
        function toggleUser(user) {
            const index = selectedUsers.findIndex(u => u.id === user.id);
            if (index === -1) {
                selectedUsers.push(user);
            } else {
                selectedUsers.splice(index, 1);
            }
            renderUsersList();
            renderSelectedUsers();
            updateShareButton();
        }

        // Fungsi untuk merender pengguna yang dipilih
        function renderSelectedUsers() {
            const selectedUsersBox = document.querySelector('.selected-users');
            const selectedCount = document.querySelector('.selected-count');

            if (!selectedUsersBox || !selectedCount) return;

            selectedUsersBox.innerHTML = '';
            selectedCount.textContent = `(${selectedUsers.length})`;

            if (selectedUsers.length === 0) {
                selectedUsersBox.innerHTML = '<p class="placeholder text-gray-500 text-sm py-2 px-3">Belum ada penerima dipilih</p>';
                return;
            }

            selectedUsers.forEach(user => {
                const chip = document.createElement('div');
                chip.className = 'bg-blue-100 text-blue-800 rounded-full py-1 px-3 text-sm flex items-center';
                chip.innerHTML = `
                    <span>${user.name}</span>
                    <button class="ml-2 text-blue-600 hover:text-blue-800">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                `;
                chip.querySelector('button').addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleUser(user);
                });
                selectedUsersBox.appendChild(chip);
            });
        }

        // Fungsi untuk update tombol berbagi
        function updateShareButton() {
            const shareButton = document.querySelector('.share-button');
            if (shareButton) {
                shareButton.disabled = selectedUsers.length === 0;
            }
        }

        // Fungsi untuk reset form berbagi
        function resetShareForm() {
            selectedUsers = [];
            filteredUsers = [...users];
            const searchInput = document.querySelector('.search-users');
            if (searchInput) searchInput.value = '';
            const clearSearch = document.querySelector('.clear-search');
            if (clearSearch) clearSearch.classList.add('hidden');
            renderUsersList();
            renderSelectedUsers();
            updateShareButton();
        }

        // Fungsi untuk menangani proses berbagi
        async function handleShare() {
            if (selectedUsers.length === 0) {
                Swal.fire('Peringatan', 'Pilih setidaknya satu penerima.', 'warning');
                return;
            }

            if (!currentFileToShare) {
                Swal.fire('Peringatan', 'Nama file tidak ditemukan.', 'warning');
                return;
            }

            try {
                const recipients = selectedUsers.map(u => u.email); // ambil email penerima
                const recipientsString = recipients[0]; // untuk sementara ambil 1 dulu

                const response = await fetch('/files/share', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        files: [
                            {
                                name: currentFileToShare,
                                size: "0 MB" // bisa diubah sesuai data asli
                            }
                        ],
                        to_email: recipientsString
                    })
                });

                const data = await response.json();

                if (!response.ok || data.error) {
                    Swal.fire('Error', data.error || 'Gagal membagikan file', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Berhasil!',
                    text: `File "${currentFileToShare}" telah dibagikan kepada ${recipientsString}`,
                    icon: 'success'
                }).then(() => {
                    closeShareModal();
                    fetchEarthFiles();
                });

            } catch (error) {
                console.error('Error sharing file:', error);
                Swal.fire('Error', 'Gagal membagikan file', 'error');
            }
        }

        // Inisialisasi event listeners untuk modal berbagi
        function initializeShareModal() {
            const closeShareBtn = document.querySelector('.close-share-modal');
            const cancelShareBtn = document.querySelector('.cancel-share');
            const shareBtn = document.querySelector('.share-button');
            const searchInput = document.querySelector('.search-users');
            const clearSearch = document.querySelector('.clear-search');

            if (closeShareBtn) closeShareBtn.addEventListener('click', closeShareModal);
            if (cancelShareBtn) cancelShareBtn.addEventListener('click', closeShareModal);
            if (shareBtn) shareBtn.addEventListener('click', handleShare);

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    const term = searchInput.value.toLowerCase();
                    if (clearSearch) clearSearch.classList.toggle('hidden', term.length === 0);
                    filteredUsers = users.filter(user =>
                        user.name.toLowerCase().includes(term) ||
                        user.email.toLowerCase().includes(term)
                    );
                    renderUsersList();
                });
            }

            if (clearSearch) {
                clearSearch.addEventListener('click', () => {
                    if (searchInput) searchInput.value = '';
                    clearSearch.classList.add('hidden');
                    filteredUsers = [...users];
                    renderUsersList();
                });
            }

            const shareModal = document.getElementById('shareModal');
            if (shareModal) {
                shareModal.addEventListener('click', (e) => {
                    if (e.target === shareModal) closeShareModal();
                });
            }
        }

        // Ambil data users untuk fitur berbagi
        async function fetchUsers() {
            try {
                const response = await fetch('/users');
                if (!response.ok) throw new Error('Failed to fetch users');
                users = await response.json();
                filteredUsers = [...users];
                renderUsersList();
            } catch (error) {
                console.error('Error memuat users:', error);
                // Fallback data jika API tidak tersedia
                users = [
                    { id: 1, name: 'Ahmad Wijaya', email: 'ahmad@example.com', avatar: 'AW', color: 'bg-blue-500' },
                    { id: 2, name: 'Sari Indah', email: 'sari@example.com', avatar: 'SI', color: 'bg-pink-500' },
                    { id: 3, name: 'Budi Santoso', email: 'budi@example.com', avatar: 'BS', color: 'bg-green-500' },
                    { id: 4, name: 'Dewi Lestari', email: 'dewi@example.com', avatar: 'DL', color: 'bg-purple-500' }
                ];
                filteredUsers = [...users];
                renderUsersList();
            }
        }

        // ==================== FUNGSI UTAMA GOOGLE EARTH ====================

        // Ambil data file dari backend Laravel dan filter hanya file Google Earth
        async function fetchEarthFiles() {
            try {
                const response = await fetch('/files');
                const allFiles = await response.json();

                // Filter hanya file Google Earth
                earthFiles = allFiles.filter(file => {
                    const ext = file.name?.split('.').pop()?.toLowerCase();
                    return earthFileTypes.includes(ext);
                });

                // Inisialisasi allEarthFiles dengan semua file earth
                allEarthFiles = [...earthFiles];

                updateEarthStats();
                sortFiles();
                renderEarthFiles();
                setupPagination();
            } catch (error) {
                console.error('Gagal memuat data file Google Earth:', error);
            }
        }

        // Update earth files statistics
        function updateEarthStats() {
            const totalEarthFiles = earthFiles.length;
            const kmlCount = earthFiles.filter(file => {
                const ext = file.name?.split('.').pop()?.toLowerCase();
                return ext === 'kml';
            }).length;

            const kmzCount = earthFiles.filter(file => {
                const ext = file.name?.split('.').pop()?.toLowerCase();
                return ext === 'kmz';
            }).length;

            // Calculate total size
            let totalSize = 0;
            earthFiles.forEach(file => {
                if (file.size) {
                    const sizeMatch = file.size.match(/([\d.]+)\s*(B|KB|MB|GB)/i);
                    if (sizeMatch) {
                        const value = parseFloat(sizeMatch[1]);
                        const unit = sizeMatch[2].toUpperCase();
                        const multiplier = {
                            'B': 1 / (1024 * 1024),
                            'KB': 1 / 1024,
                            'MB': 1,
                            'GB': 1024
                        }[unit] || 0;
                        totalSize += value * multiplier;
                    }
                }
            });

            document.getElementById('totalEarthFilesCount').textContent = totalEarthFiles;
            document.getElementById('kmlCount').textContent = kmlCount;
            document.getElementById('kmzCount').textContent = kmzCount;
            document.getElementById('totalSize').textContent = totalSize.toFixed(2) + ' MB';
        }

        // Sort files
        function sortFiles() {
            earthFiles.sort((a, b) => {
                let aValue = a[currentSort.field];
                let bValue = b[currentSort.field];

                if (currentSort.field === 'date') {
                    aValue = new Date(a.created_at || a.date);
                    bValue = new Date(b.created_at || b.date);
                }

                if (currentSort.field === 'size') {
                    aValue = parseSizeToBytes(a.size);
                    bValue = parseSizeToBytes(b.size);
                }

                if (aValue < bValue) return currentSort.direction === 'asc' ? -1 : 1;
                if (aValue > bValue) return currentSort.direction === 'asc' ? 1 : -1;
                return 0;
            });
        }

        // Parse size string to bytes
        function parseSizeToBytes(size) {
            if (!size) return 0;

            const units = {
                'B': 1,
                'KB': 1024,
                'MB': 1024 * 1024,
                'GB': 1024 * 1024 * 1024
            };

            const match = size.match(/^([\d.]+)\s*([KMG]?B)$/);
            if (match) {
                const value = parseFloat(match[1]);
                const unit = match[2];
                return value * (units[unit] || 1);
            }

            return 0;
        }

        // Filter earth files by type
        function filterEarthFiles(type) {
            activeFilterType = type;
            localStorage.setItem('selectedFileType', type);

            // Update UI
            document.querySelectorAll('.filter-option').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.type === type);
            });

            document.getElementById('filterDropdown').classList.add('hidden');
            applySearchAndFilter();
        }

        // ==================== FUNGSI PENCARIAN YANG DIPERBAIKI ====================

        // Fungsi pencarian yang lebih baik
        function performSearch(searchTerm) {
            const searchLoading = document.getElementById('searchLoading');
            const searchResultsInfo = document.getElementById('searchResultsInfo');
            const searchResultsText = document.getElementById('searchResultsText');
            const noSearchResults = document.getElementById('noSearchResults');

            // Tampilkan loading indicator
            searchLoading.classList.remove('hidden');

            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Debounce search untuk menghindari terlalu banyak request
            searchTimeout = setTimeout(() => {
                // Filter data berdasarkan pencarian
                earthFiles = allEarthFiles.filter(file => {
                    const ext = file.name?.split('.').pop()?.toLowerCase() || '';
                    const type = file.type?.toLowerCase() || ext;
                    const name = (file.name || '').toLowerCase();

                    // Cari di nama file dan tipe file
                    const matchSearch = name.includes(searchTerm) || 
                                       type.includes(searchTerm) ||
                                       (file.size && file.size.toLowerCase().includes(searchTerm));

                    const matchFilter =
                        activeFilterType === 'all' ||
                        type === activeFilterType ||
                        (activeFilterType === 'kml' && ext === 'kml') ||
                        (activeFilterType === 'kmz' && ext === 'kmz');

                    return matchSearch && matchFilter;
                });

                // Sembunyikan loading
                searchLoading.classList.add('hidden');

                // Update UI berdasarkan hasil pencarian
                if (searchTerm.length > 0) {
                    if (earthFiles.length === 0) {
                        // Tampilkan state tidak ada hasil
                        noSearchResults.classList.remove('hidden');
                        document.getElementById('emptyState').classList.add('hidden');
                        searchResultsInfo.classList.add('hidden');
                    } else {
                        // Tampilkan info hasil pencarian
                        noSearchResults.classList.add('hidden');
                        document.getElementById('emptyState').classList.add('hidden');
                        searchResultsInfo.classList.remove('hidden');
                        searchResultsText.textContent = `Ditemukan ${earthFiles.length} file untuk pencarian "${searchTerm}"`;
                    }
                } else {
                    // Reset ke state normal
                    noSearchResults.classList.add('hidden');
                    searchResultsInfo.classList.add('hidden');
                    
                    if (earthFiles.length === 0) {
                        document.getElementById('emptyState').classList.remove('hidden');
                    } else {
                        document.getElementById('emptyState').classList.add('hidden');
                    }
                }

                currentPage = 1; // Reset ke halaman pertama
                sortFiles();
                renderEarthFiles();
                setupPagination();
            }, 300); // Debounce 300ms
        }

        // Apply search and filter
        function applySearchAndFilter() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();
            performSearch(searchValue);
        }

        // Fungsi untuk highlight teks pencarian
        function highlightSearchText(text, searchTerm) {
            if (!searchTerm) return text;
            
            const regex = new RegExp(`(${searchTerm})`, 'gi');
            return text.replace(regex, '<span class="search-highlight">$1</span>');
        }

        // Render earth file cards
        function renderEarthFiles() {
            const gridView = document.getElementById('gridView');
            const fileTableBody = document.getElementById('fileTableBody');
            const emptyState = document.getElementById('emptyState');
            const noSearchResults = document.getElementById('noSearchResults');
            const searchResultsInfo = document.getElementById('searchResultsInfo');

            gridView.innerHTML = '';
            fileTableBody.innerHTML = '';

            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();

            if (!earthFiles || earthFiles.length === 0) {
                if (searchTerm.length > 0) {
                    noSearchResults.classList.remove('hidden');
                    emptyState.classList.add('hidden');
                } else {
                    emptyState.classList.remove('hidden');
                    noSearchResults.classList.add('hidden');
                }
                searchResultsInfo.classList.add('hidden');
                document.getElementById('pagination').classList.add('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            noSearchResults.classList.add('hidden');
            document.getElementById('pagination').classList.remove('hidden');

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, earthFiles.length);
            const paginatedFiles = earthFiles.slice(startIndex, endIndex);

            // Render grid view
            paginatedFiles.forEach((file, index) => {
                const ext = file.name.split('.').pop().toLowerCase();
                const config = fileConfig[ext] || fileConfig.default;

                const uploadDate = file.date || (file.created_at ? new Date(file.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Tidak diketahui');

                // Highlight nama file jika ada pencarian
                const fileName = searchTerm ? highlightSearchText(file.name, searchTerm) : file.name;

                // Grid card
                const card = document.createElement('div');
                card.className = 'file-card bg-white rounded-xl p-4 cursor-pointer hover:shadow-lg transition';
                card.dataset.name = file.name;
                card.dataset.type = ext;
                
                // Event handler untuk card (membuka detail saat klik di area card)
                card.addEventListener('click', (e) => {
                    // Hanya buka detail jika tidak mengklik tombol dropdown atau aksi lainnya
                    if (!e.target.closest('.dropdown') && !e.target.closest('.favorite-btn')) {
                        showFileDetails(file);
                    }
                });

                card.innerHTML = `
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-xl ${config.color} file-type-icon">
                            <i class="fas fa-${config.icon} text-lg"></i>
                        </div>
                        <div class="relative dropdown">
                            <button 
                                type="button"
                                class="dropdown-toggle text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>

                            <div class="dropdown-content bg-white rounded-xl shadow-lg border border-gray-200 py-2 w-48 hidden absolute right-0 z-10">
                                <button type="button" 
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 earth-detail-btn">
                                    <i class="fas fa-info-circle mr-2"></i> Detail
                                </button>

                                <a href="/storage/uploads/${encodeURIComponent(file.name)}" 
                                download 
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                onclick="event.stopPropagation()">
                                    <i class="fas fa-download mr-2"></i> Unduh
                                </a>

                                <button type="button" 
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 earth-share-btn">
                                    <i class="fas fa-share-alt mr-2"></i> Bagikan
                                </button>

                                <button type="button" 
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 earth-delete-btn">
                                    <i class="fas fa-trash-alt mr-2"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="earth-preview mb-3 text-center">
                        <i class="fas fa-${config.icon} text-3xl mb-2 earth-glow"></i>
                        <div class="text-sm">${ext.toUpperCase()}</div>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-2 truncate" title="${file.name}">${fileName}</h4>
                    <div class="flex justify-between items-center text-sm text-gray-500 mb-3">
                        <span>${file.size || '-'}</span>
                        <span>${uploadDate}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">${config.type}</span>
                        <button class="text-gray-400 hover:text-yellow-500 transition-colors favorite-btn">
                            <i class="far fa-star"></i>
                        </button>
                    </div>
                `;

                // Event listeners untuk tombol dropdown
                const detailBtn = card.querySelector('.earth-detail-btn');
                const shareBtn = card.querySelector('.earth-share-btn');
                const deleteBtn = card.querySelector('.earth-delete-btn');
                const dropdownToggle = card.querySelector('.dropdown-toggle');

                // Detail button
                detailBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    showFileDetails(file);
                });

                // Share button
                shareBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    openShareModal(file.name);
                    // Tutup dropdown setelah memilih opsi
                    const dropdown = card.querySelector('.dropdown-content');
                    dropdown.classList.add('hidden');
                });

                // Delete button
                deleteBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    confirmDelete(file.name);
                });

                // Dropdown toggle
                dropdownToggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleDropdown(dropdownToggle);
                });

                setupFavoriteButton(card, file);
                gridView.appendChild(card);
            });

            // Render list view
            paginatedFiles.forEach((file, index) => {
                const ext = file.name.split('.').pop().toLowerCase();
                const config = fileConfig[ext] || fileConfig.default;
                const uploadDate = file.date || (file.created_at ? new Date(file.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Tidak diketahui');

                // Highlight nama file jika ada pencarian
                const fileName = searchTerm ? highlightSearchText(file.name, searchTerm) : file.name;

                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors';
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap flex items-center">
                        <div class="p-2 rounded-lg ${config.color} mr-3">
                            <i class="fas fa-${config.icon}"></i>
                        </div>
                        <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="${file.name}">
                            ${fileName}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${config.type}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${file.size || '-'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${uploadDate}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            <button class="text-blue-600 hover:text-blue-900 p-1 rounded list-detail-btn">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <a href="/storage/uploads/${encodeURIComponent(file.name)}" download class="text-green-600 hover:text-green-900 p-1 rounded">
                                <i class="fas fa-download"></i>
                            </a>
                            <button class="text-purple-600 hover:text-purple-900 p-1 rounded list-share-btn">
                                <i class="fas fa-share-alt"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-900 p-1 rounded list-delete-btn">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <button class="text-yellow-500 favorite-btn p-1 rounded">
                                <i class="far fa-star"></i>
                            </button>
                        </div>
                    </td>
                `;

                // Event listeners untuk list view
                const detailBtn = row.querySelector('.list-detail-btn');
                const shareBtn = row.querySelector('.list-share-btn');
                const deleteBtn = row.querySelector('.list-delete-btn');

                detailBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    showFileDetails(file);
                });

                shareBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    openShareModal(file.name);
                });

                deleteBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    confirmDelete(file.name);
                });

                setupFavoriteButton(row, file);
                fileTableBody.appendChild(row);
            });

            // Update pagination info
            document.getElementById('startItem').textContent = startIndex + 1;
            document.getElementById('endItem').textContent = endIndex;
            document.getElementById('totalItems').textContent = earthFiles.length;
        }

        // Setup pagination
        function setupPagination() {
            const totalPages = Math.ceil(earthFiles.length / itemsPerPage);
            const pageNumbers = document.getElementById('pageNumbers');
            pageNumbers.innerHTML = '';

            // Tampilkan maksimal 5 nomor halaman
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            
            // Sesuaikan jika kita dekat dengan akhir
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            // Tombol halaman pertama jika diperlukan
            if (startPage > 1) {
                const firstPageButton = document.createElement('button');
                firstPageButton.className = 'px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200';
                firstPageButton.textContent = '1';
                firstPageButton.addEventListener('click', () => {
                    currentPage = 1;
                    renderEarthFiles();
                    setupPagination();
                });
                pageNumbers.appendChild(firstPageButton);
                
                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'px-2 py-2';
                    ellipsis.textContent = '...';
                    pageNumbers.appendChild(ellipsis);
                }
            }

            // Tombol halaman
            for (let i = startPage; i <= endPage; i++) {
                const pageButton = document.createElement('button');
                pageButton.className = `px-3 py-2 rounded-lg ${i === currentPage ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'}`;
                pageButton.textContent = i;
                pageButton.addEventListener('click', () => {
                    currentPage = i;
                    renderEarthFiles();
                    setupPagination();
                });
                pageNumbers.appendChild(pageButton);
            }

            // Tombol halaman terakhir jika diperlukan
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'px-2 py-2';
                    ellipsis.textContent = '...';
                    pageNumbers.appendChild(ellipsis);
                }
                
                const lastPageButton = document.createElement('button');
                lastPageButton.className = 'px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200';
                lastPageButton.textContent = totalPages;
                lastPageButton.addEventListener('click', () => {
                    currentPage = totalPages;
                    renderEarthFiles();
                    setupPagination();
                });
                pageNumbers.appendChild(lastPageButton);
            }

            // Tombol navigasi sebelumnya dan selanjutnya
            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage').disabled = currentPage === totalPages;

            document.getElementById('prevPage').addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderEarthFiles();
                    setupPagination();
                }
            });

            document.getElementById('nextPage').addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderEarthFiles();
                    setupPagination();
                }
            });
        }

        // Setup favorite button
        function setupFavoriteButton(element, file) {
            const favoriteBtn = element.querySelector('.favorite-btn');
            const starIcon = favoriteBtn.querySelector('i');

            const localKey = `favorite_${file.name}`;
            let isFavorite = localStorage.getItem(localKey);

            if (isFavorite === null) {
                isFavorite = file.favorite === '1' || file.favorite === 1 ? '1' : '0';
                localStorage.setItem(localKey, isFavorite);
            }

            updateStarIcon();

            favoriteBtn.addEventListener('click', e => {
                e.stopPropagation();
                fetch('/files/toggle-favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ file_name: file.name })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'added') {
                            starIcon.classList.remove('far', 'text-gray-400');
                            starIcon.classList.add('fas', 'text-yellow-500');
                            localStorage.setItem(localKey, '1');
                        } else if (data.status === 'removed') {
                            starIcon.classList.remove('fas', 'text-yellow-500');
                            starIcon.classList.add('far', 'text-gray-400');
                            localStorage.setItem(localKey, '0');
                        }

                        // Reload page after toggle
                        setTimeout(() => window.location.reload(), 300);
                    })
                    .catch(err => console.error('❌ Gagal toggle favorit:', err));
            });

            function updateStarIcon() {
                if (isFavorite === '1') {
                    starIcon.classList.remove('far', 'text-gray-400');
                    starIcon.classList.add('fas', 'text-yellow-500');
                } else {
                    starIcon.classList.remove('fas', 'text-yellow-500');
                    starIcon.classList.add('far', 'text-gray-400');
                }
            }
        }

        // Toggle dropdown
        function toggleDropdown(button) {
            const dropdown = button.nextElementSibling;
            const isHidden = dropdown.classList.contains('hidden');

            // Hide all other dropdowns
            document.querySelectorAll('.dropdown-content').forEach(other => {
                other.classList.add('hidden');
            });

            // Toggle current dropdown
            if (isHidden) {
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
        }

        // Confirm delete
        function confirmDelete(filename) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "File Google Earth ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `/files/delete/${filename}`;
                }
            });
        }

        // Show file details in modal
        function showFileDetails(file) {
            const modal = document.getElementById('fileModal');
            const modalContent = document.getElementById('modalContent');
            const ext = file.name.split('.').pop().toLowerCase();
            const config = fileConfig[ext] || fileConfig.default;

            const uploadDate = file.date || (file.created_at
                ? new Date(file.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                : 'Tidak diketahui');

            modalContent.innerHTML = `
                <div class="flex items-start">
                    <div class="p-4 rounded-2xl ${config.color} mr-5 file-type-icon">
                        <i class="fas fa-${config.icon} text-3xl"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-xl font-bold text-gray-800 mb-2">${file.name}</h4>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <p class="text-sm text-gray-500">Tipe File</p>
                                <p class="font-medium">${config.type}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Format</p>
                                <p class="font-medium">${ext.toUpperCase()}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ukuran</p>
                                <p class="font-medium">${file.size || '-'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tanggal Upload</p>
                                <p class="font-medium">${uploadDate}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <p class="text-sm text-gray-500 mb-2">Deskripsi</p>
                            <p class="text-gray-700">
                                ${config.description}
                            </p>
                        </div>

                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="/storage/uploads/${encodeURIComponent(file.name)}" download
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-5 rounded-xl font-medium flex items-center">
                                <i class="fas fa-download mr-2"></i> Unduh
                            </a>

                            <button class="bg-blue-500 hover:bg-blue-600 text-white py-2.5 px-5 rounded-xl font-medium flex items-center" onclick="openShareModal('${file.name}')">
                                <i class="fas fa-share-alt mr-2"></i> Bagikan
                            </button>
                        </div>
                    </div>
                </div>
            `;

            modal.classList.remove('hidden');
        }

        // Close modal
        document.getElementById('closeModal').addEventListener('click', () => {
            document.getElementById('fileModal').classList.add('hidden');
        });

        // Close modal when clicking outside
        document.getElementById('fileModal').addEventListener('click', (e) => {
            if (e.target.id === 'fileModal') {
                document.getElementById('fileModal').classList.add('hidden');
            }
        });

        // View toggle functionality
        document.getElementById('gridViewBtn').addEventListener('click', () => {
            currentView = 'grid';
            document.getElementById('gridView').classList.remove('hidden');
            document.getElementById('listView').classList.add('hidden');
            document.getElementById('gridViewBtn').classList.add('active');
            document.getElementById('listViewBtn').classList.remove('active');
        });

        document.getElementById('listViewBtn').addEventListener('click', () => {
            currentView = 'list';
            document.getElementById('gridView').classList.add('hidden');
            document.getElementById('listView').classList.remove('hidden');
            document.getElementById('gridViewBtn').classList.remove('active');
            document.getElementById('listViewBtn').classList.add('active');
        });

        // Sort functionality
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', () => {
                const field = header.dataset.sort;

                if (currentSort.field === field) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.field = field;
                    currentSort.direction = 'asc';
                }

                sortFiles();
                renderEarthFiles();
            });
        });

        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Toggle filter dropdown
        document.getElementById('filterButton').addEventListener('click', (event) => {
            event.stopPropagation();
            document.getElementById('filterDropdown').classList.toggle('hidden');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
            document.getElementById('filterDropdown').classList.add('hidden');
        });

        // Prevent dropdown close when clicking inside
        document.querySelectorAll('.dropdown, #filterButton').forEach(element => {
            element.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Initialize the page
        document.addEventListener('DOMContentLoaded', () => {
            fetchEarthFiles();
            fetchUsers();
            initializeShareModal();

            // Terapkan filter yang disimpan
            const savedFilter = localStorage.getItem('selectedFileType');
            if (savedFilter) {
                activeFilterType = savedFilter;
                document.querySelectorAll('.filter-option').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.type === savedFilter);
                });
            }

            applySearchAndFilter();

            // Set up search input listener - DIPERBAIKI
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                performSearch(searchTerm);
            });

            // Clear search dengan tombol ESC
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    performSearch('');
                }
            });
        });

        // Make functions globally available
        window.openShareModal = openShareModal;
        window.filterEarthFiles = filterEarthFiles;
        window.showFileDetails = showFileDetails;
        window.confirmDelete = confirmDelete;
        window.toggleDropdown = toggleDropdown;

        document.addEventListener('DOMContentLoaded', function () {

            const settingsModal = document.getElementById('settingsModal');
            const settingsBtn = document.getElementById('settingsBtn');
            const closeSettingsModal = document.getElementById('closeSettingsModal');
            const cancelSettings = document.getElementById('cancelSettings');
            const settingsForm = document.getElementById('settingsForm');

            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');

            const passwordError = document.getElementById('passwordError');
            const confirmPasswordError = document.getElementById('confirmPasswordError');

            const passwordStrength = document.getElementById('passwordStrength');

            // Buka modal
            settingsBtn.addEventListener('click', function () {
                settingsModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });

            // Tutup modal
            function closeModal() {
                settingsModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                resetForm();
            }

            closeSettingsModal.addEventListener('click', closeModal);
            cancelSettings.addEventListener('click', closeModal);

            settingsModal.addEventListener('click', function (e) {
                if (e.target === settingsModal) closeModal();
            });

            // Toggle password visibility
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                togglePassword.querySelector('i').classList.toggle('fa-eye');
                togglePassword.querySelector('i').classList.toggle('fa-eye-slash');
            });

            toggleConfirmPassword.addEventListener('click', function () {
                const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
                confirmPasswordInput.type = type;
                toggleConfirmPassword.querySelector('i').classList.toggle('fa-eye');
                toggleConfirmPassword.querySelector('i').classList.toggle('fa-eye-slash');
            });

            // Real-time cek password match + strength
            passwordInput.addEventListener('input', function () {
                checkPasswordMatch();
                checkPasswordStrength(passwordInput.value);
            });

            confirmPasswordInput.addEventListener('input', function () {
                checkPasswordMatch();
            });

            function checkPasswordMatch() {
                const password = passwordInput.value.trim();
                const confirmPassword = confirmPasswordInput.value.trim();

                // Reset class & text
                passwordError.textContent = "";
                confirmPasswordError.textContent = "";

                passwordError.className = "";
                confirmPasswordError.className = "";

                if (confirmPassword === "") return;

                if (password !== confirmPassword) {
                    passwordError.textContent = "Password dan konfirmasi password tidak cocok.";
                    passwordError.classList.add("text-red-500", "text-xs", "mt-1");
                } else {
                    confirmPasswordError.textContent = "✓ Password cocok";
                    confirmPasswordError.classList.add("text-green-600", "text-xs", "mt-1");
                }
            }

            // Validasi submit
            settingsForm.addEventListener('submit', function (e) {
                const password = passwordInput.value.trim();
                const confirmPassword = confirmPasswordInput.value.trim();

                passwordError.textContent = "";
                confirmPasswordError.textContent = "";

                if (password !== "" && password !== confirmPassword) {
                    e.preventDefault();
                    passwordError.textContent = "Password dan konfirmasi password tidak cocok.";
                    passwordError.classList.add("text-red-500", "text-xs", "mt-1");
                }
            });

            function resetForm() {
                settingsForm.reset();
                passwordError.textContent = "";
                confirmPasswordError.textContent = "";
                passwordStrength.innerHTML = "";
            }

        });


        // Password strength system (Tailwind only)
        function checkPasswordStrength(password) {
            const strengthDisplay = document.getElementById("passwordStrength");

            strengthDisplay.innerHTML = "";

            if (!password) return;

            let score = 0;

            if (password.length >= 6) score++;
            if (password.length >= 10) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            let level = "";
            let color = "";

            if (score <= 1) {
                level = "Weak";
                color = "bg-red-500";
            } else if (score <= 3) {
                level = "Medium";
                color = "bg-yellow-400";
            } else {
                level = "Strong";
                color = "bg-green-600";
            }

            strengthDisplay.innerHTML = `
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-2 ${color} transition-all duration-300" style="width: ${score * 20}%;"></div>
                </div>
                <p class="text-xs mt-1 ${color.replace('bg', 'text')} font-medium">${level}</p>
            `;
        }

    </script>
</body>

</html>