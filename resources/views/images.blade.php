<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gambar - Sistem Manajemen File</title>
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

        .image-preview {
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
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
                    class="flex items-center px-3 py-3 text-sm font-medium rounded-lg bg-blue-50 text-blue-700 border-l-4 border-blue-500">
                    <i class="fas fa-images w-5 mr-3 text-blue-500"></i>
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
                    <h1 class="text-2xl font-bold text-gray-800">Gambar</h1>
                    <p class="text-gray-600">Kelola semua file gambar Anda</p>
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
            <!-- Image Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Gambar -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-purple-100 text-purple-600">
                            <i class="fas fa-images text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Gambar</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="totalImagesCount">0</h3>
                        </div>
                    </div>
                </div>

                <!-- Ukuran Total -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-blue-100 text-blue-600">
                            <i class="fas fa-weight text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Ukuran Total</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="totalSize">0 MB</h3>
                        </div>
                    </div>
                </div>

                <!-- Format Gambar -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-green-100 text-green-600">
                            <i class="fas fa-file-image text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Format Tersedia</p>
                            <h3 class="text-2xl font-bold text-gray-800" id="formatCount">5</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Files Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div
                    class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Semua Gambar</h3>
                        <p class="text-gray-600 mt-1">Kelola koleksi gambar Anda</p>
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
                            <input type="text" id="searchInput" placeholder="Cari gambar..."
                                class="w-full bg-gray-100 border-0 rounded-xl py-2.5 pl-4 pr-10 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all" />
                            <i class="fas fa-search absolute right-3 top-3.5 text-gray-500"></i>
                        </div>

                        <!-- Filter Format -->
                        <div class="relative inline-block text-left">
                            <button id="filterButton"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-4 rounded-xl font-medium transition-colors duration-200 flex items-center">
                                <i class="fas fa-filter mr-2"></i> Format
                                <i class="fas fa-chevron-down ml-2 text-gray-500"></i>
                            </button>

                            <div id="filterDropdown"
                                class="hidden absolute right-0 mt-2 max-h-64 overflow-y-auto w-48 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 z-10 transition-all duration-200">
                                <ul class="py-2 text-gray-700" id="filterList">
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="all" onclick="filterImages('all')">
                                            Semua Format
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="jpg" onclick="filterImages('jpg')">
                                            JPG
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="jpeg" onclick="filterImages('jpeg')">
                                            JPEG
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="png" onclick="filterImages('png')">
                                            PNG
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="gif" onclick="filterImages('gif')">
                                            GIF
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="svg" onclick="filterImages('svg')">
                                            SVG
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="webp" onclick="filterImages('webp')">
                                            WEBP
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
                        <!-- Image cards will be dynamically added here -->
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
                                                <span>Nama Gambar</span>
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
                                    <!-- Image rows will be dynamically added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-12 hidden">
                        <i class="fas fa-images text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Belum ada gambar</h3>
                        <p class="text-gray-500 mb-6">Upload gambar pertama Anda untuk memulai</p>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-6 flex justify-between items-center hidden">
                        <div class="text-sm text-gray-700">
                            Menampilkan <span id="startItem">1</span> - <span id="endItem">10</span> dari <span
                                id="totalItems">0</span> gambar
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

    <!-- Image Preview Modal -->
    <div id="imageModal"
        class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 hidden transition-opacity">
        <div class="relative max-w-4xl max-h-full mx-4">
            <button id="closeImageModal"
                class="absolute -top-12 right-0 text-white hover:text-gray-300 p-2 rounded-lg transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
            <img id="modalImage" src="" alt="Preview" class="max-w-full max-h-screen object-contain rounded-lg">
            <div
                class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-4 py-2 rounded-lg">
                <span id="imageName" class="text-sm"></span>
            </div>
        </div>
    </div>

    <!-- File Detail Modal -->
    <div id="fileModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 transform transition-transform">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">Detail Gambar</h3>
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
        let images = [];
        let filteredImages = [];
        let currentView = 'grid';
        let currentPage = 1;
        const itemsPerPage = 12;
        let currentSort = { field: 'date', direction: 'desc' };
        let activeFilterType = 'all';
        let searchTerm = '';

        // Image file types configuration
        const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'tiff'];

        const fileConfig = {
            jpg: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            jpeg: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            png: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            gif: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            svg: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            webp: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            bmp: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            tiff: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            default: { icon: 'file-image', color: 'text-gray-500 bg-gray-100', type: 'Gambar' },
        };

        // Ambil data file dari backend Laravel dan filter hanya gambar
        async function fetchImages() {
            try {
                const response = await fetch('/files');
                const allFiles = await response.json();

                // Filter hanya file gambar
                images = allFiles.filter(file => {
                    const ext = file.name?.split('.').pop()?.toLowerCase();
                    return imageTypes.includes(ext);
                });

                updateImageStats();
                applyFilters();
            } catch (error) {
                console.error('Gagal memuat data gambar:', error);
                // Fallback data untuk testing
                images = [
                    { name: 'sample1.jpg', size: '2.5 MB', date: '2023-10-15', created_at: '2023-10-15T00:00:00Z' },
                    { name: 'sample2.png', size: '1.8 MB', date: '2023-10-14', created_at: '2023-10-14T00:00:00Z' },
                    { name: 'sample3.gif', size: '3.2 MB', date: '2023-10-13', created_at: '2023-10-13T00:00:00Z' },
                    { name: 'sample4.jpg', size: '4.1 MB', date: '2023-10-12', created_at: '2023-10-12T00:00:00Z' },
                    { name: 'sample5.png', size: '2.9 MB', date: '2023-10-11', created_at: '2023-10-11T00:00:00Z' },
                    { name: 'sample6.webp', size: '1.5 MB', date: '2023-10-10', created_at: '2023-10-10T00:00:00Z' },
                    { name: 'sample7.jpg', size: '3.7 MB', date: '2023-10-09', created_at: '2023-10-09T00:00:00Z' },
                    { name: 'sample8.png', size: '2.2 MB', date: '2023-10-08', created_at: '2023-10-08T00:00:00Z' },
                    { name: 'sample9.gif', size: '4.5 MB', date: '2023-10-07', created_at: '2023-10-07T00:00:00Z' },
                    { name: 'sample10.jpg', size: '1.9 MB', date: '2023-10-06', created_at: '2023-10-06T00:00:00Z' },
                    { name: 'sample11.png', size: '3.1 MB', date: '2023-10-05', created_at: '2023-10-05T00:00:00Z' },
                    { name: 'sample12.webp', size: '2.8 MB', date: '2023-10-04', created_at: '2023-10-04T00:00:00Z' },
                    { name: 'sample13.jpg', size: '4.2 MB', date: '2023-10-03', created_at: '2023-10-03T00:00:00Z' },
                    { name: 'sample14.png', size: '1.6 MB', date: '2023-10-02', created_at: '2023-10-02T00:00:00Z' },
                    { name: 'sample15.gif', size: '3.9 MB', date: '2023-10-01', created_at: '2023-10-01T00:00:00Z' }
                ];
                updateImageStats();
                applyFilters();
            }
        }

        // Update image statistics
        function updateImageStats() {
            const totalImages = images.length;
            const totalSize = images.reduce((total, image) => {
                return total + parseSizeToBytes(image.size || '0');
            }, 0);

            const formats = new Set(images.map(image => {
                return image.name?.split('.').pop()?.toLowerCase();
            }));

            document.getElementById('totalImagesCount').textContent = totalImages;
            document.getElementById('totalSize').textContent = formatBytes(totalSize);
            document.getElementById('formatCount').textContent = formats.size;
        }

        // Format bytes to readable size
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Apply filters and search
        function applyFilters() {
            filteredImages = images.filter(image => {
                const ext = image.name?.split('.').pop()?.toLowerCase();
                const name = image.name?.toLowerCase() || '';

                // Filter by file type
                const typeMatch = activeFilterType === 'all' || ext === activeFilterType;

                // Filter by search term
                const searchMatch = searchTerm === '' || name.includes(searchTerm);

                return typeMatch && searchMatch;
            });

            // Reset to page 1 when filters change
            currentPage = 1;

            sortFiles();
            renderImages();
            setupPagination();
        }

        // Sort files
        function sortFiles() {
            filteredImages.sort((a, b) => {
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

            return parseFloat(size) || 0;
        }

        // Render image cards
        function renderImages() {
            const gridView = document.getElementById('gridView');
            const listView = document.getElementById('listView');
            const fileTableBody = document.getElementById('fileTableBody');
            const emptyState = document.getElementById('emptyState');
            const pagination = document.getElementById('pagination');

            gridView.innerHTML = '';
            fileTableBody.innerHTML = '';

            if (!filteredImages || filteredImages.length === 0) {
                emptyState.classList.remove('hidden');
                pagination.classList.add('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            pagination.classList.remove('hidden');

            // Calculate pagination
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filteredImages.length);
            const paginatedImages = filteredImages.slice(startIndex, endIndex);

            // Render grid view
            paginatedImages.forEach(image => {
                const ext = image.name?.split('.').pop()?.toLowerCase();
                const config = fileConfig[ext] || fileConfig.default;

                const uploadDate = image.date
                    ? image.date
                    : (image.created_at ? new Date(image.created_at).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) : 'Tidak diketahui');

                // Grid view card
                const imageCard = document.createElement('div');
                imageCard.className = 'file-card bg-white rounded-xl p-4 cursor-pointer hover:shadow-lg transition';
                imageCard.dataset.type = ext;
                imageCard.dataset.name = image.name;
                imageCard.innerHTML = `
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 rounded-xl ${config.color} file-type-icon">
                    <i class="fas fa-${config.icon} text-lg"></i>
                </div>
                <div class="relative dropdown">
                    <button class="dropdown-toggle text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                        type="button"
        onclick="event.stopPropagation()" toggleDropdown(this)">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-content bg-white rounded-xl shadow-lg border border-gray-200 py-2 w-48 hidden absolute right-0 z-10">
                        <a href="javascript:void(0);" 
                            onclick="previewImage('${image.name}')" 
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-eye mr-2"></i> Preview
                        </a>
                        <a href="javascript:void(0);" 
                            onclick="showFileDetails('${image.name}')" 
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-info-circle mr-2"></i> Detail
                        </a>
                        <a onclick="event.stopPropagation()" href="/storage/uploads/${encodeURIComponent(image.name)}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            download>
                            <i class="fas fa-download mr-2"></i> Unduh
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-share-alt mr-2"></i> Bagikan
                        </a>
                        <a href="javascript:void(0);" 
                            onclick="event.stopPropagation(); confirmDelete('${encodeURIComponent(image.name)}')" 
                            class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                            <i class="fas fa-trash-alt mr-2"></i> Hapus
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Image Preview -->
            <div class="mb-3 bg-gray-100 rounded-lg overflow-hidden">
                <img src="/storage/uploads/${encodeURIComponent(image.name)}" 
                     alt="${image.name}"
                     class="image-preview w-full h-32 object-cover cursor-pointer"
                     onclick="previewImage('${image.name}')"
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEyMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzljYTNkYiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIFByZXZpZXc8L3RleHQ+PC9zdmc+'">
            </div>
            
            <h4 class="font-semibold text-gray-800 mb-2 truncate" title="${image.name}">
                ${image.name}
            </h4>
            <div class="flex justify-between items-center text-sm text-gray-500 mb-3">
                <span>${image.size || '-'}</span>
                <span>${uploadDate}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
                    ${ext.toUpperCase()}
                </span>
                <a class="text-gray-400 hover:text-yellow-500 transition-colors favorite-btn">
                    <i class="far fa-star"></i>
                </a>
            </div>
        `;

                gridView.appendChild(imageCard);

                // Setup favorite button
                setupFavoriteButton(imageCard, image);
            });

            // Render list view
            paginatedImages.forEach(image => {
                const ext = image.name?.split('.').pop()?.toLowerCase();
                const config = fileConfig[ext] || fileConfig.default;

                const uploadDate = image.date
                    ? image.date
                    : (image.created_at ? new Date(image.created_at).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) : 'Tidak diketahui');

                const tableRow = document.createElement('tr');
                tableRow.className = 'table-row hover:bg-gray-50 transition-colors';
                tableRow.dataset.type = ext;
                tableRow.dataset.name = image.name;
                tableRow.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg overflow-hidden mr-3">
                        <img src="/storage/uploads/${encodeURIComponent(image.name)}" 
                             alt="${image.name}"
                             class="w-full h-full object-cover"
                             onerror="this.style.display='none'">
                    </div>
                    <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="${image.name}">
                        ${image.name}
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${ext.toUpperCase()}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${image.size || '-'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${uploadDate}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                    <button onclick="previewImage('${image.name}')" class="text-blue-600 hover:text-blue-900 p-1 rounded">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="showFileDetails('${image.name}')" class="text-green-600 hover:text-green-900 p-1 rounded">
                        <i class="fas fa-info-circle"></i>
                    </button>
                    <a href="/storage/uploads/${encodeURIComponent(image.name)}" download class="text-purple-600 hover:text-purple-900 p-1 rounded">
                        <i class="fas fa-download"></i>
                    </a>
                    <button onclick="confirmDelete('${encodeURIComponent(image.name)}')" class="text-red-600 hover:text-red-900 p-1 rounded">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        `;

                fileTableBody.appendChild(tableRow);
            });

            // Update pagination info
            document.getElementById('startItem').textContent = startIndex + 1;
            document.getElementById('endItem').textContent = endIndex;
            document.getElementById('totalItems').textContent = filteredImages.length;
        }

        // Setup pagination
        function setupPagination() {
            const totalPages = Math.ceil(filteredImages.length / itemsPerPage);
            const pageNumbers = document.getElementById('pageNumbers');
            const prevPage = document.getElementById('prevPage');
            const nextPage = document.getElementById('nextPage');

            pageNumbers.innerHTML = '';

            // Jika tidak ada halaman, sembunyikan pagination
            if (totalPages <= 1) {
                document.getElementById('pagination').classList.add('hidden');
                return;
            }

            // Tampilkan maksimal 5 nomor halaman
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);

            // Sesuaikan jika di akhir
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            // Tombol Previous
            prevPage.disabled = currentPage === 1;
            prevPage.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderImages();
                    setupPagination();
                }
            };

            // Nomor halaman
            for (let i = startPage; i <= endPage; i++) {
                const pageButton = document.createElement('button');
                pageButton.className = `px-3 py-2 rounded-lg transition-colors ${i === currentPage
                    ? 'bg-blue-500 text-white font-medium'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    }`;
                pageButton.textContent = i;
                pageButton.addEventListener('click', () => {
                    currentPage = i;
                    renderImages();
                    setupPagination();
                });
                pageNumbers.appendChild(pageButton);
            }

            // Tombol Next
            nextPage.disabled = currentPage === totalPages;
            nextPage.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderImages();
                    setupPagination();
                }
            };

            // Update info halaman
            const startItem = (currentPage - 1) * itemsPerPage + 1;
            const endItem = Math.min(currentPage * itemsPerPage, filteredImages.length);

            document.getElementById('startItem').textContent = startItem;
            document.getElementById('endItem').textContent = endItem;
            document.getElementById('totalItems').textContent = filteredImages.length;
        }

        // Setup favorite button
        function setupFavoriteButton(element, image) {
            const favoriteBtn = element.querySelector('.favorite-btn');
            const starIcon = favoriteBtn.querySelector('i');

            const localKey = `favorite_${image.name}`;
            let isFavorite = localStorage.getItem(localKey);

            if (isFavorite === null) {
                isFavorite = image.favorite === '1' || image.favorite === 1 ? '1' : '0';
                localStorage.setItem(localKey, isFavorite);
            }

            if (isFavorite === '1') {
                starIcon.classList.remove('far', 'text-gray-400');
                starIcon.classList.add('fas', 'text-yellow-500');
            } else {
                starIcon.classList.remove('fas', 'text-yellow-500');
                starIcon.classList.add('far', 'text-gray-400');
            }

            favoriteBtn.addEventListener('click', (event) => {
                event.stopPropagation();

                fetch('/files/toggle-favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ file_name: image.name })
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

                        setTimeout(() => {
                            window.location.reload();
                        }, 300);
                    })
                    .catch(err => console.error('❌ Gagal toggle favorit:', err));
            });
        }

        // Image preview functionality
        function previewImage(filename) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const imageName = document.getElementById('imageName');

            modalImage.src = `/storage/uploads/${encodeURIComponent(filename)}`;
            imageName.textContent = filename;
            modal.classList.remove('hidden');
        }

        // Close image preview
        document.getElementById('closeImageModal').addEventListener('click', () => {
            document.getElementById('imageModal').classList.add('hidden');
        });

        // Close image modal when clicking outside
        document.getElementById('imageModal').addEventListener('click', (e) => {
            if (e.target.id === 'imageModal') {
                document.getElementById('imageModal').classList.add('hidden');
            }
        });

        // Toggle dropdown
        function toggleDropdown(button) {
            const dropdown = button.nextElementSibling;
            dropdown.classList.toggle('hidden');

            document.querySelectorAll('.dropdown-content').forEach(other => {
                if (other !== dropdown) other.classList.add('hidden');
            });
        }

        // Confirm delete
        function confirmDelete(filename) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Gambar ini akan dihapus secara permanen!",
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
        function showFileDetails(filename) {
            const image = images.find(img => img.name === filename);
            if (!image) return;

            const modal = document.getElementById('fileModal');
            const modalContent = document.getElementById('modalContent');
            const ext = image.name?.split('.').pop()?.toLowerCase();
            const config = fileConfig[ext] || fileConfig.default;

            modalContent.innerHTML = `
        <div class="flex items-start">
            <div class="p-4 rounded-2xl ${config.color} mr-5 file-type-icon">
                <i class="fas fa-${config.icon} text-3xl"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-xl font-bold text-gray-800 mb-2">${image.name}</h4>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <p class="text-sm text-gray-500">Format</p>
                        <p class="font-medium">${ext.toUpperCase()}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Ukuran</p>
                        <p class="font-medium">${image.size}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Upload</p>
                        <p class="font-medium">${image.date}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Dimensi</p>
                        <p class="font-medium">${image.dimensions || 'Tidak tersedia'}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-sm text-gray-500 mb-2">Preview</p>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <img src="/storage/uploads/${encodeURIComponent(image.name)}" 
                             alt="${image.name}"
                             class="max-w-full max-h-48 object-contain mx-auto rounded"
                             onerror="this.style.display='none'">
                    </div>
                </div>

                <div class="mt-8 flex justify-end space-x-3">
                    <button onclick="previewImage('${image.name}')"
                        class="bg-blue-500 hover:bg-blue-600 text-white py-2.5 px-5 rounded-xl font-medium transition-colors duration-200 flex items-center">
                        <i class="fas fa-eye mr-2"></i> Preview
                    </button>
                    <a 
                        href="/download/${encodeURIComponent(image.name)}"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-5 rounded-xl font-medium transition-colors duration-200 flex items-center">
                        <i class="fas fa-download mr-2"></i> Unduh
                    </a>
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
                renderImages();
                setupPagination();
            });
        });

        // Filter images by format
        function filterImages(type) {
            activeFilterType = type;
            currentPage = 1; // Reset to first page when filtering
            applyFilters();
            document.getElementById('filterDropdown').classList.add('hidden');
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', (e) => {
            searchTerm = e.target.value.toLowerCase().trim();
            currentPage = 1; // Reset to first page when searching
            applyFilters();
        });

        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
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

        // Toggle filter dropdown
        document.getElementById('filterButton').addEventListener('click', () => {
            document.getElementById('filterDropdown').classList.toggle('hidden');
        });

        // Initialize the page
        document.addEventListener('DOMContentLoaded', () => {
            fetchImages();
        });

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