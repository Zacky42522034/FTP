<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video - Sistem Manajemen File</title>
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

        .video-preview {
            height: 140px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .video-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .video-duration {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .play-button:hover {
            background: white;
            transform: translate(-50%, -50%) scale(1.1);
        }

        .play-button i {
            color: #3b82f6;
            font-size: 1.25rem;
            margin-left: 2px;
        }

        .duration-loading {
            opacity: 0.7;
        }

        .duration-badge {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
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

                <a href="#"
                    class="flex items-center px-3 py-3 text-sm font-medium rounded-lg bg-blue-50 text-blue-700 border-l-4 border-blue-500">
                    <i class="fas fa-file-video w-5 mr-3 text-blue-500"></i>
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

                <a href="#"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <i class="fas fa-share-alt w-5 mr-3"></i>
                    <span>Berbagi</span>
                    <span class="ml-auto bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full font-medium">24</span>
                </a>

                <a href="/favorites"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-star w-5 mr-3"></i>
                    <span>Favorit</span>
                    <span class="ml-auto bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">{{ $favoriteFiles }}</span>
                </a>
                <a href="/earth"
                    class="flex items-center px-3 py-3 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-earth-asia w-5 mr-3"></i>
                    <span>Google Earth</span>
                    <span class="ml-auto bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded-full font-medium">{{ $totalMap }}</span>
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
                    <h1 class="text-2xl font-bold text-gray-800">Video</h1>
                    <p class="text-gray-600">Kelola semua file video Anda</p>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Search Bar -->
                    

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
            <!-- Video Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Video -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-pink-100 text-pink-600">
                            <i class="fas fa-video text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Video</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="totalVideosCount">0</h3>
                        </div>
                    </div>
                </div>

                <!-- Rata-rata Ukuran -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-purple-100 text-purple-600">
                            <i class="fas fa-weight text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Rata-rata Ukuran</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="averageSize">0 MB</h3>
                        </div>
                    </div>
                </div>

                <!-- Rata-rata Durasi -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-blue-100 text-blue-600">
                            <i class="fas fa-film text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Rata-rata Durasi</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="averageDuration">0:00</h3>
                        </div>
                    </div>
                </div>

                <!-- Ukuran Total -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-green-100 text-green-600">
                            <i class="fas fa-weight text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Ukuran Total</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="totalSize">0 GB</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Files Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div
                    class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Semua Video</h3>
                        <p class="text-gray-600 mt-1">Kelola koleksi video Anda</p>
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
                            <input type="text" id="searchInput" placeholder="Cari video..."
                                class="w-full bg-gray-100 border-0 rounded-xl py-2.5 pl-4 pr-10 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all" />
                            <i class="fas fa-search absolute right-3 top-3.5 text-gray-500"></i>
                        </div>

                        <!-- Filter Format -->
                        <div class="relative inline-block text-left">
                            <button id="filterButton"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-4 rounded-xl font-medium transition-colors duration-200 flex items-center">
                                <i class="fas fa-filter mr-2"></i> Format Video
                                <i class="fas fa-chevron-down ml-2 text-gray-500"></i>
                            </button>

                            <div id="filterDropdown"
                                class="hidden absolute right-0 mt-2 max-h-64 overflow-y-auto w-48 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 z-10 transition-all duration-200">
                                <ul class="py-2 text-gray-700" id="filterList">
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="all" onclick="filterVideos('all')">
                                            Semua Format
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="mp4" onclick="filterVideos('mp4')">
                                            MP4
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="avi" onclick="filterVideos('avi')">
                                            AVI
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="mov" onclick="filterVideos('mov')">
                                            MOV
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="mkv" onclick="filterVideos('mkv')">
                                            MKV
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="wmv" onclick="filterVideos('wmv')">
                                            WMV
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="webm" onclick="filterVideos('webm')">
                                            WebM
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
                        <!-- Video cards will be dynamically added here -->
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
                                                <span>Nama Video</span>
                                                <i class="fas fa-sort ml-2 text-gray-400"></i>
                                            </div>
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                            data-sort="type">
                                            <div class="flex items-center">
                                                <span>Format</span>
                                                <i class="fas fa-sort ml-2 text-gray-400"></i>
                                            </div>
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sortable"
                                            data-sort="duration">
                                            <div class="flex items-center">
                                                <span>Durasi</span>
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
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="fileTableBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Video rows will be dynamically added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-12 hidden">
                        <i class="fas fa-video text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Belum ada video</h3>
                        <p class="text-gray-500 mb-6">Upload video pertama Anda untuk memulai</p>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-6 flex justify-between items-center hidden">
                        <div class="text-sm text-gray-700">
                            Menampilkan <span id="startItem">1</span> - <span id="endItem">10</span> dari <span
                                id="totalItems">0</span> video
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

    <!-- Video Player Modal -->
    <div id="videoModal"
        class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 hidden transition-opacity">
        <div class="relative max-w-4xl max-h-full mx-4 w-full">
            <button id="closeVideoModal"
                class="absolute -top-12 right-0 text-white hover:text-gray-300 p-2 rounded-lg transition-colors z-10">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <video id="modalVideo" controls class="w-full max-h-screen rounded-lg">
                Your browser does not support the video tag.
            </video>
            <div
                class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-4 py-2 rounded-lg">
                <span id="videoName" class="text-sm"></span>
            </div>
        </div>
    </div>

    <!-- File Detail Modal -->
    <div id="fileModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 transform transition-transform">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Detail Video</h3>
                <button
                    class="closeModal text-gray-500 hover:text-gray-700 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6" id="modalContent">
                <!-- Modal content will be dynamically added here -->
            </div>
        </div>
    </div>

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
                                <input type="password" id="confirmPassword"  name="password_confirmation" class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-xl 
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
        let videos = [];
        let currentView = 'grid';
        let currentPage = 1;
        const itemsPerPage = 12;
        let currentSort = { field: 'date', direction: 'desc' };
        let filteredVideos = [];
        let searchTimeout = null;
        let activeFilterType = 'all';
        let videoDurations = new Map(); // Cache untuk durasi video yang sudah di-load

        // Video file types configuration
        const videoTypes = [
            'mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv', 'webm',
            'm4v', '3gp', 'mpg', 'mpeg', 'ogg'
        ];

        const fileConfig = {
            mp4: {
                icon: 'file-video',
                color: 'text-pink-500 bg-pink-100',
                type: 'MP4 Video',
                previewColor: 'bg-gradient-to-br from-pink-500 to-pink-600'
            },
            avi: {
                icon: 'file-video',
                color: 'text-blue-500 bg-blue-100',
                type: 'AVI Video',
                previewColor: 'bg-gradient-to-br from-blue-500 to-blue-600'
            },
            mov: {
                icon: 'file-video',
                color: 'text-green-500 bg-green-100',
                type: 'QuickTime Video',
                previewColor: 'bg-gradient-to-br from-green-500 to-green-600'
            },
            mkv: {
                icon: 'file-video',
                color: 'text-purple-500 bg-purple-100',
                type: 'Matroska Video',
                previewColor: 'bg-gradient-to-br from-purple-500 to-purple-600'
            },
            wmv: {
                icon: 'file-video',
                color: 'text-orange-500 bg-orange-100',
                type: 'Windows Media Video',
                previewColor: 'bg-gradient-to-br from-orange-500 to-orange-600'
            },
            flv: {
                icon: 'file-video',
                color: 'text-red-500 bg-red-100',
                type: 'Flash Video',
                previewColor: 'bg-gradient-to-br from-red-500 to-red-600'
            },
            webm: {
                icon: 'file-video',
                color: 'text-indigo-500 bg-indigo-100',
                type: 'WebM Video',
                previewColor: 'bg-gradient-to-br from-indigo-500 to-indigo-600'
            },
            m4v: {
                icon: 'file-video',
                color: 'text-teal-500 bg-teal-100',
                type: 'iTunes Video',
                previewColor: 'bg-gradient-to-br from-teal-500 to-teal-600'
            },
            '3gp': {
                icon: 'file-video',
                color: 'text-yellow-500 bg-yellow-100',
                type: '3GPP Video',
                previewColor: 'bg-gradient-to-br from-yellow-500 to-yellow-600'
            },
            mpg: {
                icon: 'file-video',
                color: 'text-gray-500 bg-gray-100',
                type: 'MPEG Video',
                previewColor: 'bg-gradient-to-br from-gray-500 to-gray-600'
            },
            mpeg: {
                icon: 'file-video',
                color: 'text-gray-500 bg-gray-100',
                type: 'MPEG Video',
                previewColor: 'bg-gradient-to-br from-gray-500 to-gray-600'
            },
            ogg: {
                icon: 'file-video',
                color: 'text-amber-500 bg-amber-100',
                type: 'Ogg Video',
                previewColor: 'bg-gradient-to-br from-amber-500 to-amber-600'
            },
            default: {
                icon: 'file-video',
                color: 'text-gray-500 bg-gray-100',
                type: 'Video File',
                previewColor: 'bg-gradient-to-br from-gray-500 to-gray-600'
            },
        };

        // Ambil data file dari backend Laravel dan filter hanya video
        async function fetchVideos() {
            try {
                showLoading();
                console.log('📥 Fetching video files...');

                const response = await fetch('/files');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const allFiles = await response.json();

                // Filter hanya file video
                videos = allFiles.filter(file => {
                    if (!file || !file.name) return false;
                    const ext = file.name.split('.').pop()?.toLowerCase();
                    return videoTypes.includes(ext);
                });

                // Set filteredVideos sama dengan videos awal
                filteredVideos = [...videos];

                console.log('🎬 Filtered video files:', videos.length);

                updateVideoStats();
                sortFiles();
                renderVideos();
                setupPagination();
                hideLoading();

                // Load durasi video setelah render
                loadVideoDurations();
            } catch (error) {
                console.error('❌ Gagal memuat data video:', error);
                // Fallback data untuk testing
                videos = [
                    { name: 'video-tutorial.mp4', size: '45.2 MB', date: '2023-10-15', created_at: '2023-10-15T00:00:00Z' },
                    { name: 'presentation.mov', size: '78.1 MB', date: '2023-10-14', created_at: '2023-10-14T00:00:00Z' },
                    { name: 'documentary.avi', size: '156.7 MB', date: '2023-10-13', created_at: '2023-10-13T00:00:00Z' },
                    { name: 'music-video.mp4', size: '32.5 MB', date: '2023-10-12', created_at: '2023-10-12T00:00:00Z' },
                    { name: 'interview.mkv', size: '89.3 MB', date: '2023-10-11', created_at: '2023-10-11T00:00:00Z' },
                    { name: 'tutorial-part1.webm', size: '67.8 MB', date: '2023-10-10', created_at: '2023-10-10T00:00:00Z' },
                    { name: 'event-highlights.mp4', size: '123.4 MB', date: '2023-10-09', created_at: '2023-10-09T00:00:00Z' },
                    { name: 'product-demo.mov', size: '54.6 MB', date: '2023-10-08', created_at: '2023-10-08T00:00:00Z' },
                    { name: 'training-video.avi', size: '98.2 MB', date: '2023-10-07', created_at: '2023-10-07T00:00:00Z' },
                    { name: 'vlog-episode.mp4', size: '76.9 MB', date: '2023-10-06', created_at: '2023-10-06T00:00:00Z' },
                    { name: 'company-profile.mkv', size: '145.3 MB', date: '2023-10-05', created_at: '2023-10-05T00:00:00Z' },
                    { name: 'webinar-recording.webm', size: '167.8 MB', date: '2023-10-04', created_at: '2023-10-04T00:00:00Z' }
                ];
                filteredVideos = [...videos];
                updateVideoStats();
                sortFiles();
                renderVideos();
                setupPagination();
                hideLoading();

                // Load durasi video untuk data fallback
                loadVideoDurations();
            }
        }

        // Fungsi untuk memuat durasi video dari file sebenarnya
        async function loadVideoDurations() {
            console.log('⏱️ Loading video durations...');

            const videoPromises = filteredVideos.map(async (video, index) => {
                try {
                    // Cek cache dulu
                    if (videoDurations.has(video.name)) {
                        return videoDurations.get(video.name);
                    }

                    const duration = await getVideoDuration(`/storage/uploads/${encodeURIComponent(video.name)}`);

                    if (duration && duration > 0) {
                        // Simpan ke cache
                        videoDurations.set(video.name, duration);

                        // Update video object
                        video.duration = duration;

                        // Update UI untuk video yang sedang ditampilkan
                        updateVideoDurationUI(video.name, duration);

                        return duration;
                    }
                } catch (error) {
                    console.warn(`⚠️ Gagal mendapatkan durasi untuk ${video.name}:`, error);
                    return 0;
                }
            });

            // Tunggu semua promise selesai
            await Promise.allSettled(videoPromises);

            // Update statistik setelah semua durasi selesai di-load
            updateVideoStats();
            console.log('✅ Video durations loaded');
        }

        // Fungsi untuk mendapatkan durasi video dari file
        function getVideoDuration(url) {
            return new Promise((resolve, reject) => {
                const video = document.createElement('video');

                // Event handlers
                video.addEventListener('loadedmetadata', function () {
                    console.log(`🎬 ${url}: ${video.duration} seconds`);
                    window.URL.revokeObjectURL(video.src);
                    resolve(video.duration);
                });

                video.addEventListener('error', function (e) {
                    console.warn(`❌ Error loading ${url}:`, e);
                    window.URL.revokeObjectURL(video.src);
                    reject(new Error('Gagal memuat video'));
                });

                // Timeout untuk mencegah hanging
                const timeout = setTimeout(() => {
                    window.URL.revokeObjectURL(video.src);
                    reject(new Error('Timeout loading video'));
                }, 10000); // 10 detik timeout

                video.addEventListener('loadedmetadata', () => clearTimeout(timeout));
                video.addEventListener('error', () => clearTimeout(timeout));

                // Set source dan preload metadata
                video.preload = 'metadata';
                video.src = url + '?t=' + Date.now(); // Tambah timestamp untuk bypass cache
            });
        }

        // Fungsi untuk update UI durasi video
        function updateVideoDurationUI(filename, duration) {
            // Update grid view
            const gridCards = document.querySelectorAll('.file-card');
            gridCards.forEach(card => {
                if (card.dataset.name === filename) {
                    const durationElement = card.querySelector('.video-duration');
                    if (durationElement) {
                        durationElement.textContent = formatDurationShort(duration);
                    }
                }
            });

            // Update list view
            const listRows = document.querySelectorAll('.table-row');
            listRows.forEach(row => {
                if (row.dataset.name === filename) {
                    const durationCell = row.querySelector('td:nth-child(3)'); // Kolom durasi
                    if (durationCell) {
                        durationCell.textContent = formatDuration(duration);
                    }
                }
            });
        }

        function showLoading() {
            const gridView = document.getElementById('gridView');
            if (gridView) {
                gridView.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                        <p class="mt-4 text-gray-500 text-lg">Memuat video...</p>
                    </div>
                `;
            }
        }

        function hideLoading() {
            // Loading akan diganti dengan konten saat renderVideos() dipanggil
        }

        // Update video statistics - VERSI DIPERBAIKI
        function updateVideoStats() {
            const totalVideos = filteredVideos.length;

            // Hitung total ukuran semua video
            const totalSize = filteredVideos.reduce((total, video) => {
                return total + parseSizeToBytes(video.size || '0');
            }, 0);

            // Hitung rata-rata ukuran
            const averageSize = totalVideos > 0 ? totalSize / totalVideos : 0;

            // Hitung durasi total dari video yang sudah memiliki durasi
            const videosWithDuration = filteredVideos.filter(video => video.duration && video.duration > 0);
            const totalDuration = videosWithDuration.reduce((total, video) => {
                return total + (video.duration || 0);
            }, 0);

            const averageDuration = videosWithDuration.length > 0 ? totalDuration / videosWithDuration.length : 0;

            // Update DOM elements dengan safety check
            const updateIfExists = (id, value) => {
                const element = document.getElementById(id);
                if (element) element.textContent = value;
            };

            updateIfExists('totalVideosCount', totalVideos);
            updateIfExists('averageSize', formatBytes(averageSize));
            updateIfExists('averageDuration', formatDurationShort(averageDuration));
            updateIfExists('totalSize', formatBytes(totalSize));
        }

        // Format bytes to readable size
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Parse size string to bytes
        function parseSizeToBytes(size) {
            if (!size) return 0;
            if (!isNaN(size)) return parseInt(size);

            const units = {
                'B': 1,
                'KB': 1024,
                'MB': 1024 * 1024,
                'GB': 1024 * 1024 * 1024
            };

            const match = size.toString().match(/^([\d.]+)\s*([KMG]?B)$/);
            if (match) {
                const value = parseFloat(match[1]);
                const unit = match[2];
                return value * (units[unit] || 1);
            }

            return 0;
        }

        // Sort files
        function sortFiles() {
            filteredVideos.sort((a, b) => {
                let aValue = a[currentSort.field];
                let bValue = b[currentSort.field];

                if (currentSort.field === 'date') {
                    aValue = new Date(a.created_at || a.date || 0);
                    bValue = new Date(b.created_at || b.date || 0);
                }

                if (currentSort.field === 'size') {
                    aValue = parseSizeToBytes(a.size || '0');
                    bValue = parseSizeToBytes(b.size || '0');
                }

                if (currentSort.field === 'duration') {
                    aValue = parseInt(a.duration) || 0;
                    bValue = parseInt(b.duration) || 0;
                }

                if (aValue < bValue) return currentSort.direction === 'asc' ? -1 : 1;
                if (aValue > bValue) return currentSort.direction === 'asc' ? 1 : -1;
                return 0;
            });
        }

        // Render video cards
        function renderVideos() {
            const gridView = document.getElementById('gridView');
            const listView = document.getElementById('listView');
            const fileTableBody = document.getElementById('fileTableBody');
            const emptyState = document.getElementById('emptyState');
            const pagination = document.getElementById('pagination');

            if (!gridView || !listView || !fileTableBody || !emptyState || !pagination) {
                console.error('❌ Required DOM elements not found');
                return;
            }

            gridView.innerHTML = '';
            fileTableBody.innerHTML = '';

            if (!filteredVideos || filteredVideos.length === 0) {
                emptyState.classList.remove('hidden');
                pagination.classList.add('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            pagination.classList.remove('hidden');

            // Hitung pagination
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filteredVideos.length);
            const paginatedVideos = filteredVideos.slice(startIndex, endIndex);

            // Render grid view
            paginatedVideos.forEach((video, index) => {
                if (!video || !video.name) return;

                const globalIndex = startIndex + index;
                const ext = video.name.split('.').pop()?.toLowerCase();
                const config = fileConfig[ext] || fileConfig.default;

                const uploadDate = video.date
                    ? video.date
                    : (video.created_at ? new Date(video.created_at).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) : 'Tidak diketahui');

                // Gunakan durasi dari cache jika ada, atau tampilkan loading
                const currentDuration = videoDurations.has(video.name)
                    ? videoDurations.get(video.name)
                    : (video.duration || 0);

                const videoCard = document.createElement('div');
                videoCard.className = 'file-card bg-white rounded-xl p-4 cursor-pointer hover:shadow-lg transition';
                videoCard.dataset.type = ext;
                videoCard.dataset.name = video.name;

                videoCard.innerHTML = `
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-xl ${config.color} file-type-icon">
                            <i class="fas fa-${config.icon} text-lg"></i>
                        </div>
                        <div class="relative dropdown">
                            <button class="dropdown-toggle text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                                onclick="event.preventDefault(); event.stopPropagation(); toggleDropdown(this)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-content bg-white rounded-xl shadow-lg border border-gray-200 py-2 w-48 hidden absolute right-0 z-10">
                                <a href="javascript:void(0);" 
                                    onclick="event.preventDefault(); playVideo('${video.name}')" 
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-play mr-2"></i> Putar
                                </a>
                                <a href="javascript:void(0);" 
                                    onclick="event.preventDefault(); showFileDetails(${globalIndex})" 
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-info-circle mr-2"></i> Detail
                                </a>
                                <a href="/storage/uploads/${encodeURIComponent(video.name)}"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" download
                                    onclick="event.stopPropagation()">
                                    <i class="fas fa-download mr-2"></i> Unduh
                                </a>
                                <a href="javascript:void(0);" onclick="event.preventDefault()"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-share-alt mr-2"></i> Bagikan
                                </a>
                                <a href="javascript:void(0);" 
                                    onclick="event.preventDefault(); confirmDelete('${encodeURIComponent(video.name)}')" 
                                    class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-trash-alt mr-2"></i> Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Video Preview -->
                    <div class="video-preview ${config.previewColor} mb-3" 
                         onclick="event.preventDefault(); playVideo('${video.name}')">
                        <div class="w-full h-full flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-${config.icon} text-3xl mb-2"></i>
                                <div class="text-sm">${ext.toUpperCase()}</div>
                            </div>
                        </div>
                        <div class="play-button">
                            <i class="fas fa-play"></i>
                        </div>
                        <div class="video-duration ${!videoDurations.has(video.name) ? 'duration-loading' : ''}">
                            ${videoDurations.has(video.name) ? formatDurationShort(currentDuration) : 'Loading...'}
                        </div>
                    </div>
                    
                    <h4 class="font-semibold text-gray-800 mb-2 truncate" title="${video.name}">
                        ${video.name}
                    </h4>
                    <div class="flex justify-between items-center text-sm text-gray-500 mb-3">
                        <span>${video.size || '-'}</span>
                        <span>${uploadDate}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
                            ${config.type}
                        </span>
                        <a class="text-gray-400 hover:text-yellow-500 transition-colors favorite-btn" href="javascript:void(0)">
                            <i class="far fa-star"></i>
                        </a>
                    </div>
                `;

                gridView.appendChild(videoCard);
                setupFavoriteButton(videoCard, video);
            });

            // Render list view
            paginatedVideos.forEach((video, index) => {
                if (!video || !video.name) return;

                const globalIndex = startIndex + index;
                const ext = video.name.split('.').pop()?.toLowerCase();
                const config = fileConfig[ext] || fileConfig.default;

                const uploadDate = video.date
                    ? video.date
                    : (video.created_at ? new Date(video.created_at).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) : 'Tidak diketahui');

                // Gunakan durasi dari cache jika ada
                const currentDuration = videoDurations.has(video.name)
                    ? videoDurations.get(video.name)
                    : (video.duration || 0);

                const tableRow = document.createElement('tr');
                tableRow.className = 'table-row hover:bg-gray-50 transition-colors';
                tableRow.dataset.type = ext;
                tableRow.dataset.name = video.name;

                tableRow.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="p-2 rounded-lg ${config.color} mr-3">
                                <i class="fas fa-${config.icon}"></i>
                            </div>
                            <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="${video.name}">
                                ${video.name}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${ext.toUpperCase()}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 ${!videoDurations.has(video.name) ? 'duration-loading' : ''}">
                        ${videoDurations.has(video.name) ? formatDuration(currentDuration) : 'Loading...'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${video.size || '-'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            <button onclick="event.preventDefault(); playVideo('${video.name}')" 
                                    class="text-green-600 hover:text-green-900 p-2 rounded-lg hover:bg-green-50 transition-colors">
                                <i class="fas fa-play"></i>
                            </button>
                            <button onclick="event.preventDefault(); showFileDetails(${globalIndex})" 
                                    class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50 transition-colors">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <a href="/storage/uploads/${encodeURIComponent(video.name)}" download
                               class="text-purple-600 hover:text-purple-900 p-2 rounded-lg hover:bg-purple-50 transition-colors"
                               onclick="event.stopPropagation()">
                                <i class="fas fa-download"></i>
                            </a>
                            <button onclick="event.preventDefault(); confirmDelete('${encodeURIComponent(video.name)}')"
                                    class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                `;

                fileTableBody.appendChild(tableRow);
            });

            // Update pagination info
            updatePaginationInfo();

            // Setup pagination setelah render
            setupPagination();
        }

        // Update pagination information
        function updatePaginationInfo() {
            const startItem = document.getElementById('startItem');
            const endItem = document.getElementById('endItem');
            const totalItems = document.getElementById('totalItems');

            if (!startItem || !endItem || !totalItems) return;

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filteredVideos.length);

            startItem.textContent = filteredVideos.length > 0 ? startIndex + 1 : 0;
            endItem.textContent = endIndex;
            totalItems.textContent = filteredVideos.length;
        }

        // Format duration short (for display in cards)
        function formatDurationShort(seconds) {
            if (!seconds || isNaN(seconds) || seconds === 0) return '0:00';

            const totalSeconds = Math.floor(seconds);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const secs = totalSeconds % 60;

            if (hours > 0) {
                return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            } else {
                return `${minutes}:${secs.toString().padStart(2, '0')}`;
            }
        }

        // Format duration to readable time
        function formatDuration(seconds) {
            if (!seconds || isNaN(seconds) || seconds === 0) return '0 detik';

            const totalSeconds = Math.floor(seconds);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const secs = totalSeconds % 60;

            let result = '';
            if (hours > 0) result += `${hours} jam `;
            if (minutes > 0) result += `${minutes} menit `;
            if (secs > 0 || result === '') result += `${secs} detik`;

            return result.trim();
        }

        // Setup pagination
        function setupPagination() {
            const pageNumbers = document.getElementById('pageNumbers');
            const prevPage = document.getElementById('prevPage');
            const nextPage = document.getElementById('nextPage');

            if (!pageNumbers || !prevPage || !nextPage) {
                console.error('❌ Pagination elements not found');
                return;
            }

            const totalPages = Math.ceil(filteredVideos.length / itemsPerPage);

            // Clear existing page numbers
            pageNumbers.innerHTML = '';

            // Generate page numbers
            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.className = `px-3 py-2 rounded-lg transition-colors ${i === currentPage
                    ? 'bg-blue-500 text-white hover:bg-blue-600'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    }`;
                pageButton.textContent = i;
                pageButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    currentPage = i;
                    renderVideos();
                    setupPagination();
                });
                pageNumbers.appendChild(pageButton);
            }

            // Update button states
            prevPage.disabled = currentPage === 1;
            nextPage.disabled = currentPage === totalPages || totalPages === 0;

            // Previous page handler
            prevPage.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (currentPage > 1) {
                    currentPage--;
                    renderVideos();
                    setupPagination();
                }
            };

            // Next page handler
            nextPage.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (currentPage < totalPages) {
                    currentPage++;
                    renderVideos();
                    setupPagination();
                }
            };

            console.log(`📄 Pagination: Page ${currentPage} of ${totalPages}, Total items: ${filteredVideos.length}`);
        }

        // Video player functionality
        function playVideo(filename) {
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');
            const videoName = document.getElementById('videoName');

            modalVideo.src = `/storage/uploads/${encodeURIComponent(filename)}`;
            videoName.textContent = filename;
            modal.classList.remove('hidden');

            // Play video when modal opens
            modalVideo.play().catch(e => {
                console.log('Autoplay prevented:', e);
            });
        }

        // Close video player
        document.getElementById('closeVideoModal').addEventListener('click', (e) => {
            e.preventDefault();
            const modal = document.getElementById('videoModal');
            const modalVideo = document.getElementById('modalVideo');

            modalVideo.pause();
            modalVideo.src = '';
            modal.classList.add('hidden');
        });

        // Close video modal when clicking outside
        document.getElementById('videoModal').addEventListener('click', (e) => {
            if (e.target.id === 'videoModal') {
                const modalVideo = document.getElementById('modalVideo');
                modalVideo.pause();
                modalVideo.src = '';
                document.getElementById('videoModal').classList.add('hidden');
            }
        });

        // Setup favorite button
        function setupFavoriteButton(element, video) {
            const favoriteBtn = element.querySelector('.favorite-btn');
            if (!favoriteBtn) return;

            const starIcon = favoriteBtn.querySelector('i');
            if (!starIcon) return;

            const localKey = `favorite_${video.name}`;
            let isFavorite = localStorage.getItem(localKey);

            if (isFavorite === null) {
                isFavorite = video.favorite === '1' || video.favorite === 1 ? '1' : '0';
                localStorage.setItem(localKey, isFavorite);
            }

            if (isFavorite === '1') {
                starIcon.className = 'fas fa-star text-yellow-500';
            } else {
                starIcon.className = 'far fa-star text-gray-400';
            }

            favoriteBtn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                fetch('/files/toggle-favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        file_name: video.name
                    })
                })
                    .then(async (res) => {
                        if (!res.ok) {
                            throw new Error(`HTTP ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.status === 'added') {
                            starIcon.className = 'fas fa-star text-yellow-500';
                            localStorage.setItem(localKey, '1');
                        } else if (data.status === 'removed') {
                            starIcon.className = 'far fa-star text-gray-400';
                            localStorage.setItem(localKey, '0');
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 300);
                    })
                    .catch(err => {
                        console.error('❌ Gagal toggle favorit:', err);
                    });
            });
        }

        // Toggle dropdown
        function toggleDropdown(button) {
            const dropdown = button.nextElementSibling;
            if (dropdown) {
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
        }

        // Confirm delete
        function confirmDelete(filename) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Video ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `/files/delete/${filename}`;
                }
            });
        }

        // Show file details in modal
        function showFileDetails(index) {
            if (index < 0 || index >= filteredVideos.length) return;

            const video = filteredVideos[index];
            const modal = document.getElementById('fileModal');
            const modalContent = document.getElementById('modalContent');
            const ext = video.name?.split('.').pop()?.toLowerCase();
            const config = fileConfig[ext] || fileConfig.default;

            if (!modal || !modalContent) return;

            // Gunakan durasi dari cache jika ada
            const currentDuration = videoDurations.has(video.name)
                ? videoDurations.get(video.name)
                : (video.duration || 0);

            modalContent.innerHTML = `
                <div class="flex items-start">
                    <div class="p-4 rounded-2xl ${config.color} mr-5">
                        <i class="fas fa-${config.icon} text-3xl"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-xl font-bold text-gray-800 mb-2 break-words">${video.name}</h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <p class="text-sm text-gray-500">Tipe Video</p>
                                <p class="font-medium">${config.type}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Format</p>
                                <p class="font-medium">${ext.toUpperCase()}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ukuran</p>
                                <p class="font-medium">${video.size || '-'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Durasi</p>
                                <p class="font-medium">${formatDuration(currentDuration)}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tanggal Upload</p>
                                <p class="font-medium">${video.date || 'Tidak diketahui'}</p>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                            <button onclick="event.preventDefault(); playVideo('${video.name}')"
                                class="bg-green-500 hover:bg-green-600 text-white py-2.5 px-5 rounded-xl font-medium transition-colors duration-200 flex items-center justify-center">
                                <i class="fas fa-play mr-2"></i> Putar Video
                            </button>
                            <a 
                                href="/storage/uploads/${encodeURIComponent(video.name)}"
                                download
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-5 rounded-xl font-medium transition-colors duration-200 flex items-center justify-center">
                                <i class="fas fa-download mr-2"></i> Unduh
                            </a>
                        </div>
                    </div>
                </div>
            `;

            modal.classList.remove('hidden');
        }

        // Close modal
        document.querySelectorAll('.closeModal').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('fileModal').classList.add('hidden');
            });
        });


        // Filter videos by type
        function filterVideos(type) {
            activeFilterType = type;

            if (type === 'all') {
                filteredVideos = [...videos];
            } else {
                filteredVideos = videos.filter(video => {
                    const ext = video.name?.split('.').pop()?.toLowerCase();
                    return ext === type;
                });
            }

            currentPage = 1; // Reset ke halaman 1 saat filter
            updateVideoStats();
            sortFiles();
            renderVideos();
            setupPagination();

            // Load durasi untuk video yang difilter
            loadVideoDurations();

            // Update filter button text
            const filterButton = document.getElementById('filterButton');
            if (filterButton) {
                const filterText = type === 'all' ? 'Format Video' : type.toUpperCase();
                filterButton.innerHTML = `<i class="fas fa-filter mr-2"></i> ${filterText} <i class="fas fa-chevron-down ml-2 text-gray-500"></i>`;
            }

            // Hide filter dropdown
            const filterDropdown = document.getElementById('filterDropdown');
            if (filterDropdown) {
                filterDropdown.classList.add('hidden');
            }
        }

        // Toggle view between grid and list
        function toggleView(view) {
            const gridView = document.getElementById('gridView');
            const listView = document.getElementById('listView');
            const gridViewBtn = document.getElementById('gridViewBtn');
            const listViewBtn = document.getElementById('listViewBtn');

            if (view === 'grid') {
                gridView.classList.remove('hidden');
                listView.classList.add('hidden');
                gridViewBtn.classList.add('active');
                listViewBtn.classList.remove('active');
                currentView = 'grid';
            } else {
                gridView.classList.add('hidden');
                listView.classList.remove('hidden');
                gridViewBtn.classList.remove('active');
                listViewBtn.classList.add('active');
                currentView = 'list';
            }

            renderVideos();
        }

        // Sort files by column
        function sortBy(column) {
            if (currentSort.field === column) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.field = column;
                currentSort.direction = 'asc';
            }

            sortFiles();
            renderVideos();
        }

        // Initialize everything when DOM is ready
        function initializeApp() {
            console.log('🚀 Initializing video player app...');

            // Setup event listeners for UI elements
            document.getElementById('gridViewBtn').addEventListener('click', (e) => {
                e.preventDefault();
                toggleView('grid');
            });

            document.getElementById('listViewBtn').addEventListener('click', (e) => {
                e.preventDefault();
                toggleView('list');
            });

            // Setup sortable columns
            document.querySelectorAll('.sortable').forEach(column => {
                column.addEventListener('click', (e) => {
                    e.preventDefault();
                    const sortField = column.getAttribute('data-sort');
                    sortBy(sortField);
                });
            });

            // Setup filter dropdown toggle
            document.getElementById('filterButton').addEventListener('click', (e) => {
                e.preventDefault();
                const filterDropdown = document.getElementById('filterDropdown');
                filterDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.relative.inline-block.text-left')) {
                    const filterDropdown = document.getElementById('filterDropdown');
                    if (filterDropdown) filterDropdown.classList.add('hidden');
                }

                // Close all dropdowns when clicking outside
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                        dropdown.classList.add('hidden');
                    });
                }
            });

            // Setup search functionality dengan debounce
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    e.preventDefault();

                    // Clear previous timeout
                    if (searchTimeout) {
                        clearTimeout(searchTimeout);
                    }

                    // Set new timeout untuk debounce
                    searchTimeout = setTimeout(() => {
                        const searchTerm = e.target.value.toLowerCase();

                        if (searchTerm.length === 0) {
                            filteredVideos = [...videos];
                        } else {
                            filteredVideos = videos.filter(video =>
                                video.name.toLowerCase().includes(searchTerm)
                            );
                        }

                        currentPage = 1;
                        updateVideoStats();
                        sortFiles();
                        renderVideos();
                        setupPagination();

                        // Load durasi untuk hasil pencarian
                        loadVideoDurations();
                    }, 300); // 300ms debounce
                });
            }

            // Setup global search dengan debounce
            const globalSearch = document.getElementById('globalSearch');
            if (globalSearch) {
                globalSearch.addEventListener('input', (e) => {
                    e.preventDefault();

                    // Clear previous timeout
                    if (searchTimeout) {
                        clearTimeout(searchTimeout);
                    }

                    // Set new timeout untuk debounce
                    searchTimeout = setTimeout(() => {
                        const searchTerm = e.target.value.toLowerCase();

                        if (searchTerm.length === 0) {
                            filteredVideos = [...videos];
                        } else {
                            filteredVideos = videos.filter(video =>
                                video.name.toLowerCase().includes(searchTerm)
                            );
                        }

                        currentPage = 1;
                        updateVideoStats();
                        sortFiles();
                        renderVideos();
                        setupPagination();

                        // Load durasi untuk hasil pencarian
                        loadVideoDurations();
                    }, 300); // 300ms debounce
                });
            }

            // Setup mobile menu
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.querySelector('.sidebar').classList.toggle('active');
                });
            }

            // Load video data
            fetchVideos();

            console.log('✅ App initialized successfully');
        }

        // Start the app when DOM is fully loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeApp);
        } else {
            initializeApp();
        }

        // Make functions globally available
        window.playVideo = playVideo;
        window.toggleDropdown = toggleDropdown;
        window.confirmDelete = confirmDelete;
        window.showFileDetails = showFileDetails;
        window.filterVideos = filterVideos;
        window.toggleView = toggleView;
        window.sortBy = sortBy;

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
                checkPasswordStrength(passwordInput.value); // <-- FIX: panggil strength
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