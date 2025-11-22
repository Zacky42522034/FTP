<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berbagi File - Sistem Manajemen File</title>
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

        /* Tab Styles */
        .tab-active {
            background-color: #3b82f6;
            color: white;
        }

        .tab-inactive {
            background-color: #f3f4f6;
            color: #6b7280;
        }

        .tab-inactive:hover {
            background-color: #e5e7eb;
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-expired {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        /* Share Badge */
        .share-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(59, 130, 246, 0.9);
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 500;
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
                    class="flex items-center px-3 py-3 text-sm font-medium rounded-lg bg-blue-50 text-blue-700 border-l-4 border-blue-500">
                    <i class="fas fa-share-alt w-5 mr-3 text-blue-500"></i>
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
                <a href="/earth"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-earth-asia w-5 mr-3"></i>
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
                    <h1 class="text-2xl font-bold text-gray-800">Berbagi File</h1>
                    <p class="text-gray-600">Kelola file yang dibagikan dengan Anda dan yang Anda bagikan</p>
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
            <!-- Share Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Shared With Me -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-blue-100 text-blue-600">
                            <i class="fas fa-download text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Dibagikan ke Saya</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="sharedWithMeCount">0</h3>
                        </div>
                    </div>
                </div>

                <!-- Shared By Me -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-green-100 text-green-600">
                            <i class="fas fa-upload text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Saya Bagikan</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="sharedByMeCount">0</h3>
                        </div>
                    </div>
                </div>

                <!-- Active Shares -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-purple-100 text-purple-600">
                            <i class="fas fa-link text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Berbagi Aktif</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="activeSharesCount">0</h3>
                        </div>
                    </div>
                </div>

                <!-- Expired Soon -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-orange-100 text-orange-600">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Kadaluarsa Segera</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="expiringSoonCount">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="border-b border-gray-200">
                    <div class="flex p-1">
                        <button id="sharedWithMeTab"
                            class="tab-active flex-1 py-2.5 px-4 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-download mr-2"></i>Dibagikan ke Saya
                        </button>
                        <button id="sharedByMeTab"
                            class="tab-inactive flex-1 py-2.5 px-4 rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-upload mr-2"></i>Saya Bagikan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Files Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div
                    class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800" id="sectionTitle">File yang Dibagikan ke Saya</h3>
                        <p class="text-gray-600 mt-1" id="sectionDescription">File yang dibagikan oleh pengguna lain dengan Anda</p>
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

                        <!-- Search -->
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Cari file..."
                                class="w-full bg-gray-100 border-0 rounded-xl py-2.5 pl-4 pr-10 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all" />
                            <i class="fas fa-search absolute right-3 top-3.5 text-gray-500"></i>
                        </div>

                        <!-- Filter Status -->
                        <div class="relative inline-block text-left">
                            <button id="filterButton"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-4 rounded-xl font-medium transition-colors duration-200 flex items-center">
                                <i class="fas fa-filter mr-2"></i> Status
                                <i class="fas fa-chevron-down ml-2 text-gray-500"></i>
                            </button>

                            <div id="filterDropdown"
                                class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 z-10 transition-all duration-200">
                                <ul class="py-2 text-gray-700" id="filterList">
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-status="all" onclick="filterShares('all')">
                                            Semua Status
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-status="active" onclick="filterShares('active')">
                                            Aktif
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-status="expired" onclick="filterShares('expired')">
                                            Kadaluarsa
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-status="pending" onclick="filterShares('pending')">
                                            Tertunda
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Files Content -->
                <div class="p-6">
                    <!-- Grid View -->
                    <div id="gridView" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <!-- Shared file cards will be dynamically added here -->
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
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                            data-sort="sharedBy">
                                            <div class="flex items-center">
                                                <span>Dibagikan Oleh</span>
                                                <i class="fas fa-sort ml-2 text-gray-400"></i>
                                            </div>
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                            data-sort="date">
                                            <div class="flex items-center">
                                                <span>Tanggal Berbagi</span>
                                                <i class="fas fa-sort ml-2 text-gray-400"></i>
                                            </div>
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                            data-sort="expires">
                                            <div class="flex items-center">
                                                <span>Kadaluarsa</span>
                                                <i class="fas fa-sort ml-2 text-gray-400"></i>
                                            </div>
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="fileTableBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Shared file rows will be dynamically added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-12 hidden">
                        <div class="mb-4">
                            <i class="fas fa-share-alt text-5xl text-gray-300 mb-3"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700 mb-2" id="emptyStateTitle">Belum ada file yang dibagikan</h3>
                        <p class="text-gray-500 mb-6" id="emptyStateDescription">File yang dibagikan dengan Anda akan muncul di sini</p>
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
                <h3 class="text-xl font-bold text-gray-800">Detail File</h3>
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
                            <i class="fas fa-file text-blue-500 mr-3"></i>
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
                        <input type="text" id="username" name="username" value="{{ Auth::user()->username }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <div id="usernameError" class="error-message text-red-500 text-xs mt-1"></div>
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-gray-500"></i>Email
                        </label>
                        <input type="email" id="email" name="email" value="{{ Auth::user()->email }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <div id="emailError" class="error-message text-red-500 text-xs mt-1"></div>
                    </div>

                    <!-- Password Baru + Konfirmasi -->
                    <div class="flex flex-col space-y-6">
                        <!-- Password Baru -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-gray-500"></i>Password Baru
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password"
                                    class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="Masukkan password baru">
                                <button type="button" id="togglePassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordStrength" class="password-strength mt-2"></div>
                            <div id="passwordError" class="error-message text-red-500 text-xs mt-1"></div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-gray-500"></i>Konfirmasi Password
                            </label>
                            <div class="relative">
                                <input type="password" id="confirmPassword" name="password_confirmation"
                                    class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    placeholder="Konfirmasi password baru">
                                <button type="button" id="toggleConfirmPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div id="confirmPasswordError" class="error-message text-red-500 text-xs mt-1"></div>
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
        // Data contoh untuk halaman berbagi
        const sharedWithMe = [
            {
                id: 1,
                name: 'Laporan_Keuangan_Q1.pdf',
                type: 'pdf',
                size: '2.4 MB',
                sharedBy: 'Ahmad Wijaya',
                sharedDate: '2023-10-15',
                expires: '2023-11-15',
                status: 'active',
                sharedByAvatar: 'AW',
                sharedByColor: 'bg-blue-500'
            },
            {
                id: 2,
                name: 'Presentasi_Proyek_Final.pptx',
                type: 'pptx',
                size: '8.7 MB',
                sharedBy: 'Sari Indah',
                sharedDate: '2023-10-10',
                expires: '2023-10-25',
                status: 'expired',
                sharedByAvatar: 'SI',
                sharedByColor: 'bg-pink-500'
            },
            {
                id: 3,
                name: 'Data_Analisis.xlsx',
                type: 'xlsx',
                size: '5.2 MB',
                sharedBy: 'Budi Santoso',
                sharedDate: '2023-10-20',
                expires: '2023-11-20',
                status: 'active',
                sharedByAvatar: 'BS',
                sharedByColor: 'bg-green-500'
            },
            {
                id: 4,
                name: 'Gambar_Produk_New.jpg',
                type: 'jpg',
                size: '3.1 MB',
                sharedBy: 'Dewi Lestari',
                sharedDate: '2023-10-18',
                expires: '2023-11-18',
                status: 'active',
                sharedByAvatar: 'DL',
                sharedByColor: 'bg-purple-500'
            },
            {
                id: 5,
                name: 'Dokumen_Kontrak.docx',
                type: 'docx',
                size: '1.8 MB',
                sharedBy: 'Rizki Pratama',
                sharedDate: '2023-10-22',
                expires: '2023-10-29',
                status: 'pending',
                sharedByAvatar: 'RP',
                sharedByColor: 'bg-orange-500'
            }
        ];

        const sharedByMe = [
            {
                id: 1,
                name: 'Manual_Penggunaan_Software.pdf',
                type: 'pdf',
                size: '4.2 MB',
                sharedTo: 'Tim Development',
                sharedDate: '2023-10-12',
                expires: '2023-11-12',
                status: 'active',
                accessCount: 12
            },
            {
                id: 2,
                name: 'Database_Backup.zip',
                type: 'zip',
                size: '45.8 MB',
                sharedTo: 'Admin Server',
                sharedDate: '2023-10-05',
                expires: '2023-10-20',
                status: 'expired',
                accessCount: 3
            },
            {
                id: 3,
                name: 'Template_Desain.fig',
                type: 'fig',
                size: '12.3 MB',
                sharedTo: 'Tim Desain',
                sharedDate: '2023-10-25',
                expires: '2023-11-25',
                status: 'active',
                accessCount: 8
            },
            {
                id: 4,
                name: 'Video_Tutorial_Produk.mp4',
                type: 'mp4',
                size: '156.7 MB',
                sharedTo: 'Tim Marketing',
                sharedDate: '2023-10-08',
                expires: '2023-11-08',
                status: 'active',
                accessCount: 24
            }
        ];

        let currentTab = 'sharedWithMe';
        let currentView = 'grid';
        let currentPage = 1;
        const itemsPerPage = 8;
        let currentFilter = 'all';
        let currentSearch = '';

        // Variabel untuk fitur berbagi
        let currentFileToShare = null;
        let users = [];
        let selectedUsers = [];
        let filteredUsers = [];

        // File type configuration
        const fileConfig = {
            pdf: {
                icon: 'file-pdf',
                color: 'text-red-500 bg-red-100',
                type: 'PDF Document'
            },
            docx: {
                icon: 'file-word',
                color: 'text-blue-500 bg-blue-100',
                type: 'Word Document'
            },
            xlsx: {
                icon: 'file-excel',
                color: 'text-green-500 bg-green-100',
                type: 'Excel Spreadsheet'
            },
            pptx: {
                icon: 'file-powerpoint',
                color: 'text-orange-500 bg-orange-100',
                type: 'PowerPoint Presentation'
            },
            jpg: {
                icon: 'file-image',
                color: 'text-purple-500 bg-purple-100',
                type: 'JPEG Image'
            },
            png: {
                icon: 'file-image',
                color: 'text-indigo-500 bg-indigo-100',
                type: 'PNG Image'
            },
            zip: {
                icon: 'file-archive',
                color: 'text-yellow-500 bg-yellow-100',
                type: 'ZIP Archive'
            },
            mp4: {
                icon: 'file-video',
                color: 'text-pink-500 bg-pink-100',
                type: 'MP4 Video'
            },
            fig: {
                icon: 'file-image',
                color: 'text-purple-500 bg-purple-100',
                type: 'Figma File'
            },
            default: {
                icon: 'file',
                color: 'text-gray-500 bg-gray-100',
                type: 'File'
            },
        };

        // ==================== FUNGSI UTAMA ====================

        // Initialize the page
        function initSharePage() {
            updateShareStats();
            renderFiles();
            setupPagination();
            initializeShareModal();
            fetchUsers();
            
            // Set up event listeners
            setupEventListeners();
        }

        // Update share statistics
        function updateShareStats() {
            const sharedWithMeCount = sharedWithMe.length;
            const sharedByMeCount = sharedByMe.length;
            const activeSharesCount = sharedWithMe.filter(file => file.status === 'active').length + 
                                    sharedByMe.filter(file => file.status === 'active').length;
            const expiringSoonCount = sharedWithMe.filter(file => {
                const expires = new Date(file.expires);
                const today = new Date();
                const diffTime = expires - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                return diffDays <= 7 && diffDays > 0;
            }).length;

            document.getElementById('sharedWithMeCount').textContent = sharedWithMeCount;
            document.getElementById('sharedByMeCount').textContent = sharedByMeCount;
            document.getElementById('activeSharesCount').textContent = activeSharesCount;
            document.getElementById('expiringSoonCount').textContent = expiringSoonCount;
        }

        // Get current files based on active tab
        function getCurrentFiles() {
            return currentTab === 'sharedWithMe' ? sharedWithMe : sharedByMe;
        }

        // Filter files based on status
        function filterFiles(files) {
            if (currentFilter === 'all') {
                return files;
            }
            return files.filter(file => file.status === currentFilter);
        }

        // Search files
        function searchFiles(files) {
            if (!currentSearch) {
                return files;
            }
            return files.filter(file => 
                file.name.toLowerCase().includes(currentSearch) ||
                (file.sharedBy && file.sharedBy.toLowerCase().includes(currentSearch)) ||
                (file.sharedTo && file.sharedTo.toLowerCase().includes(currentSearch))
            );
        }

        // Render files based on current view
        function renderFiles() {
            const gridView = document.getElementById('gridView');
            const fileTableBody = document.getElementById('fileTableBody');
            const emptyState = document.getElementById('emptyState');
            const emptyStateTitle = document.getElementById('emptyStateTitle');
            const emptyStateDescription = document.getElementById('emptyStateDescription');

            gridView.innerHTML = '';
            fileTableBody.innerHTML = '';

            let files = getCurrentFiles();
            files = filterFiles(files);
            files = searchFiles(files);

            if (files.length === 0) {
                emptyState.classList.remove('hidden');
                document.getElementById('pagination').classList.add('hidden');
                
                if (currentTab === 'sharedWithMe') {
                    emptyStateTitle.textContent = 'Belum ada file yang dibagikan dengan Anda';
                    emptyStateDescription.textContent = 'File yang dibagikan oleh pengguna lain akan muncul di sini';
                } else {
                    emptyStateTitle.textContent = 'Anda belum membagikan file';
                    emptyStateDescription.textContent = 'File yang Anda bagikan dengan pengguna lain akan muncul di sini';
                }
                return;
            }

            emptyState.classList.add('hidden');
            document.getElementById('pagination').classList.remove('hidden');

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, files.length);
            const paginatedFiles = files.slice(startIndex, endIndex);

            // Update section titles
            updateSectionTitles();

            // Render based on current view
            if (currentView === 'grid') {
                renderGridView(paginatedFiles, gridView);
            } else {
                renderListView(paginatedFiles, fileTableBody);
            }

            // Update pagination info
            document.getElementById('startItem').textContent = startIndex + 1;
            document.getElementById('endItem').textContent = endIndex;
            document.getElementById('totalItems').textContent = files.length;
        }

        // Update section titles based on current tab
        function updateSectionTitles() {
            const sectionTitle = document.getElementById('sectionTitle');
            const sectionDescription = document.getElementById('sectionDescription');

            if (currentTab === 'sharedWithMe') {
                sectionTitle.textContent = 'File yang Dibagikan ke Saya';
                sectionDescription.textContent = 'File yang dibagikan oleh pengguna lain dengan Anda';
            } else {
                sectionTitle.textContent = 'File yang Saya Bagikan';
                sectionDescription.textContent = 'File yang Anda bagikan dengan pengguna lain';
            }
        }

        // Render grid view
        function renderGridView(files, container) {
            files.forEach(file => {
                const ext = file.name.split('.').pop().toLowerCase();
                const config = fileConfig[ext] || fileConfig.default;

                const card = document.createElement('div');
                card.className = 'file-card bg-white rounded-xl p-4 cursor-pointer hover:shadow-lg transition relative';
                
                // Status badge
                const statusBadge = getStatusBadge(file.status);
                
                // Shared info
                const sharedInfo = currentTab === 'sharedWithMe' 
                    ? `<p class="text-xs text-gray-500 mt-1">Dibagikan oleh: ${file.sharedBy}</p>`
                    : `<p class="text-xs text-gray-500 mt-1">Dibagikan ke: ${file.sharedTo}</p>`;

                card.innerHTML = `
                    <div class="share-badge">${statusBadge}</div>
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
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 detail-btn">
                                    <i class="fas fa-info-circle mr-2"></i> Detail
                                </button>
                                <button type="button" 
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 download-btn">
                                    <i class="fas fa-download mr-2"></i> Unduh
                                </button>
                                ${currentTab === 'sharedByMe' ? `
                                <button type="button" 
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 manage-access-btn">
                                    <i class="fas fa-users-cog mr-2"></i> Kelola Akses
                                </button>
                                <button type="button" 
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 stop-share-btn">
                                    <i class="fas fa-ban mr-2"></i> Hentikan Berbagi
                                </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-2 truncate">${file.name}</h4>
                    ${sharedInfo}
                    <div class="flex justify-between items-center text-sm text-gray-500 mt-3">
                        <span>${file.size}</span>
                        <span>${formatDate(file.sharedDate)}</span>
                    </div>
                    <div class="flex justify-between items-center mt-3">
                        <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">${config.type}</span>
                        ${currentTab === 'sharedByMe' ? `
                        <span class="text-xs text-blue-600 bg-blue-100 px-2.5 py-1 rounded-full">
                            <i class="fas fa-eye mr-1"></i>${file.accessCount}x dilihat
                        </span>
                        ` : ''}
                    </div>
                `;

                // Event listeners
                const detailBtn = card.querySelector('.detail-btn');
                const downloadBtn = card.querySelector('.download-btn');
                const dropdownToggle = card.querySelector('.dropdown-toggle');

                detailBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showFileDetails(file);
                });

                downloadBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    downloadFile(file);
                });

                if (currentTab === 'sharedByMe') {
                    const manageAccessBtn = card.querySelector('.manage-access-btn');
                    const stopShareBtn = card.querySelector('.stop-share-btn');

                    manageAccessBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        manageAccess(file);
                    });

                    stopShareBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        stopSharing(file);
                    });
                }

                dropdownToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleDropdown(dropdownToggle);
                });

                card.addEventListener('click', () => showFileDetails(file));
                container.appendChild(card);
            });
        }

        // Render list view
        function renderListView(files, container) {
            files.forEach(file => {
                const ext = file.name.split('.').pop().toLowerCase();
                const config = fileConfig[ext] || fileConfig.default;
                const statusBadge = getStatusBadge(file.status);

                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors';
                
                const sharedInfo = currentTab === 'sharedWithMe' 
                    ? `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 ${file.sharedByColor || 'bg-blue-500'} rounded-full flex items-center justify-center text-white font-medium mr-3">
                                    ${file.sharedByAvatar || file.sharedBy.substring(0, 2).toUpperCase()}
                                </div>
                                <div class="text-sm text-gray-900">${file.sharedBy}</div>
                            </div>
                        </td>
                    `
                    : `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${file.sharedTo}</td>
                    `;

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap flex items-center">
                        <div class="p-2 rounded-lg ${config.color} mr-3">
                            <i class="fas fa-${config.icon}"></i>
                        </div>
                        <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="${file.name}">
                            ${file.name}
                        </div>
                    </td>
                    ${sharedInfo}
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDate(file.sharedDate)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${formatDate(file.expires)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${statusBadge}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            <button class="text-blue-600 hover:text-blue-900 p-1 rounded detail-btn">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <button class="text-green-600 hover:text-green-900 p-1 rounded download-btn">
                                <i class="fas fa-download"></i>
                            </button>
                            ${currentTab === 'sharedByMe' ? `
                            <button class="text-purple-600 hover:text-purple-900 p-1 rounded manage-access-btn">
                                <i class="fas fa-users-cog"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-900 p-1 rounded stop-share-btn">
                                <i class="fas fa-ban"></i>
                            </button>
                            ` : ''}
                        </div>
                    </td>
                `;

                // Event listeners
                const detailBtn = row.querySelector('.detail-btn');
                const downloadBtn = row.querySelector('.download-btn');

                detailBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showFileDetails(file);
                });

                downloadBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    downloadFile(file);
                });

                if (currentTab === 'sharedByMe') {
                    const manageAccessBtn = row.querySelector('.manage-access-btn');
                    const stopShareBtn = row.querySelector('.stop-share-btn');

                    manageAccessBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        manageAccess(file);
                    });

                    stopShareBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        stopSharing(file);
                    });
                }

                container.appendChild(row);
            });
        }

        // Get status badge HTML
        function getStatusBadge(status) {
            switch(status) {
                case 'active':
                    return '<span class="status-badge status-active">Aktif</span>';
                case 'expired':
                    return '<span class="status-badge status-expired">Kadaluarsa</span>';
                case 'pending':
                    return '<span class="status-badge status-pending">Tertunda</span>';
                default:
                    return '<span class="status-badge status-active">Aktif</span>';
            }
        }

        // Format date
        function formatDate(dateString) {
            const options = { day: '2-digit', month: 'short', year: 'numeric' };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        }

        // Setup pagination
        function setupPagination() {
            const totalPages = Math.ceil(getCurrentFiles().length / itemsPerPage);
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
                    renderFiles();
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
                    renderFiles();
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
                    renderFiles();
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
                    renderFiles();
                    setupPagination();
                }
            });

            document.getElementById('nextPage').addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderFiles();
                    setupPagination();
                }
            });
        }

        // Show file details
        function showFileDetails(file) {
            const modal = document.getElementById('fileModal');
            const modalContent = document.getElementById('modalContent');
            const ext = file.name.split('.').pop().toLowerCase();
            const config = fileConfig[ext] || fileConfig.default;

            const sharedInfo = currentTab === 'sharedWithMe' 
                ? `
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Dibagikan oleh</p>
                        <div class="flex items-center mt-1">
                            <div class="w-8 h-8 ${file.sharedByColor || 'bg-blue-500'} rounded-full flex items-center justify-center text-white font-medium mr-3">
                                ${file.sharedByAvatar || file.sharedBy.substring(0, 2).toUpperCase()}
                            </div>
                            <p class="font-medium">${file.sharedBy}</p>
                        </div>
                    </div>
                `
                : `
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Dibagikan kepada</p>
                        <p class="font-medium">${file.sharedTo}</p>
                    </div>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">Jumlah akses</p>
                        <p class="font-medium">${file.accessCount} kali</p>
                    </div>
                `;

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
                                <p class="text-sm text-gray-500">Ukuran</p>
                                <p class="font-medium">${file.size}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tanggal Berbagi</p>
                                <p class="font-medium">${formatDate(file.sharedDate)}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Kadaluarsa</p>
                                <p class="font-medium">${formatDate(file.expires)}</p>
                            </div>
                        </div>

                        ${sharedInfo}

                        <div class="mt-8 flex justify-end space-x-3">
                            <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-5 rounded-xl font-medium flex items-center download-btn">
                                <i class="fas fa-download mr-2"></i> Unduh
                            </button>
                            ${currentTab === 'sharedByMe' ? `
                            <button class="bg-blue-500 hover:bg-blue-600 text-white py-2.5 px-5 rounded-xl font-medium flex items-center manage-access-btn">
                                <i class="fas fa-users-cog mr-2"></i> Kelola Akses
                            </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

            // Add event listeners to modal buttons
            const downloadBtn = modalContent.querySelector('.download-btn');
            downloadBtn.addEventListener('click', () => {
                downloadFile(file);
                modal.classList.add('hidden');
            });

            if (currentTab === 'sharedByMe') {
                const manageAccessBtn = modalContent.querySelector('.manage-access-btn');
                manageAccessBtn.addEventListener('click', () => {
                    manageAccess(file);
                    modal.classList.add('hidden');
                });
            }

            modal.classList.remove('hidden');
        }

        // Download file
        function downloadFile(file) {
            // Simulate download
            Swal.fire({
                title: 'Mengunduh...',
                text: `File "${file.name}" sedang diunduh`,
                icon: 'info',
                showConfirmButton: false,
                timer: 1500
            });
        }

        // Manage access
        function manageAccess(file) {
            Swal.fire({
                title: 'Kelola Akses',
                text: `Mengelola akses untuk file "${file.name}"`,
                icon: 'info',
                confirmButtonText: 'Tutup'
            });
        }

        // Stop sharing
        function stopSharing(file) {
            Swal.fire({
                title: 'Hentikan Berbagi?',
                text: `File "${file.name}" tidak akan lagi dapat diakses oleh penerima`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hentikan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire(
                        'Berhasil!',
                        'File tidak lagi dibagikan',
                        'success'
                    );
                }
            });
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

        // Filter shares
        function filterShares(status) {
            currentFilter = status;
            currentPage = 1;
            renderFiles();
            setupPagination();
        }

        // Apply search
        function applySearch() {
            currentSearch = document.getElementById('searchInput').value.toLowerCase().trim();
            currentPage = 1;
            renderFiles();
            setupPagination();
        }

        // Switch tabs
        function switchTab(tab) {
            currentTab = tab;
            currentPage = 1;
            currentFilter = 'all';
            currentSearch = '';
            
            // Update tab UI
            if (tab === 'sharedWithMe') {
                document.getElementById('sharedWithMeTab').classList.remove('tab-inactive');
                document.getElementById('sharedWithMeTab').classList.add('tab-active');
                document.getElementById('sharedByMeTab').classList.remove('tab-active');
                document.getElementById('sharedByMeTab').classList.add('tab-inactive');
            } else {
                document.getElementById('sharedByMeTab').classList.remove('tab-inactive');
                document.getElementById('sharedByMeTab').classList.add('tab-active');
                document.getElementById('sharedWithMeTab').classList.remove('tab-active');
                document.getElementById('sharedWithMeTab').classList.add('tab-inactive');
            }
            
            // Reset search
            document.getElementById('searchInput').value = '';
            
            renderFiles();
            setupPagination();
        }

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
                const recipients = selectedUsers.map(u => u.email);
                const recipientsString = recipients.join(', ');

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
                                size: "0 MB"
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
                    // Refresh the page to show updated shares
                    window.location.reload();
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
                    { id: 4, name: 'Dewi Lestari', email: 'dewi@example.com', avatar: 'DL', color: 'bg-purple-500' },
                    { id: 5, name: 'Rizki Pratama', email: 'rizki@example.com', avatar: 'RP', color: 'bg-orange-500' },
                    { id: 6, name: 'Tim Development', email: 'dev@example.com', avatar: 'TD', color: 'bg-indigo-500' },
                    { id: 7, name: 'Tim Desain', email: 'design@example.com', avatar: 'TD', color: 'bg-teal-500' },
                    { id: 8, name: 'Tim Marketing', email: 'marketing@example.com', avatar: 'TM', color: 'bg-rose-500' }
                ];
                filteredUsers = [...users];
                renderUsersList();
            }
        }

        // Setup event listeners
        function setupEventListeners() {
            // Tab switching
            document.getElementById('sharedWithMeTab').addEventListener('click', () => switchTab('sharedWithMe'));
            document.getElementById('sharedByMeTab').addEventListener('click', () => switchTab('sharedByMe'));

            // View toggle
            document.getElementById('gridViewBtn').addEventListener('click', () => {
                currentView = 'grid';
                document.getElementById('gridView').classList.remove('hidden');
                document.getElementById('listView').classList.add('hidden');
                document.getElementById('gridViewBtn').classList.add('active');
                document.getElementById('listViewBtn').classList.remove('active');
                renderFiles();
            });

            document.getElementById('listViewBtn').addEventListener('click', () => {
                currentView = 'list';
                document.getElementById('gridView').classList.add('hidden');
                document.getElementById('listView').classList.remove('hidden');
                document.getElementById('gridViewBtn').classList.remove('active');
                document.getElementById('listViewBtn').classList.add('active');
                renderFiles();
            });

            // Search
            document.getElementById('searchInput').addEventListener('input', applySearch);

            // Filter dropdown
            document.getElementById('filterButton').addEventListener('click', () => {
                document.getElementById('filterDropdown').classList.toggle('hidden');
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.dropdown') && !e.target.closest('#filterButton')) {
                    document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                        dropdown.classList.add('hidden');
                    });
                    document.getElementById('filterDropdown').classList.add('hidden');
                }
            });

            // Close modals
            document.getElementById('closeModal').addEventListener('click', () => {
                document.getElementById('fileModal').classList.add('hidden');
            });

            document.getElementById('fileModal').addEventListener('click', (e) => {
                if (e.target.id === 'fileModal') {
                    document.getElementById('fileModal').classList.add('hidden');
                }
            });

            // Mobile menu
            document.getElementById('mobileMenuBtn').addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });
        }

        // Initialize the page when DOM is loaded
        document.addEventListener('DOMContentLoaded', initSharePage);

        // Make functions globally available
        window.openShareModal = openShareModal;
        window.filterShares = filterShares;
        window.showFileDetails = showFileDetails;
        window.toggleDropdown = toggleDropdown;
    </script>
</body>

</html>