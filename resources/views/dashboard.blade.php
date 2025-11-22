<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Manajemen File</title>
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

        /* Animasi untuk modal */
        .modal-enter {
            opacity: 0;
            transform: scale(0.95);
        }

        .modal-enter-active {
            opacity: 1;
            transform: scale(1);
            transition: opacity 200ms, transform 200ms;
        }

        .modal-exit {
            opacity: 1;
            transform: scale(1);
        }

        .modal-exit-active {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 200ms, transform 200ms;
        }

        /* Tambahan untuk styling filter aktif */
        .filter-option.active {
            background-color: #eff6ff;
            color: #3b82f6;
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
                <a href="#"
                    class="flex items-center px-3 py-3 text-sm font-medium rounded-lg bg-blue-50 text-blue-700 border-l-4 border-blue-500">
                    <i class="fas fa-home w-5 mr-3 text-blue-500"></i>
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
                <button id="settingsBtn"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg text-sm font-medium text-center transition">
                    <i class="fas fa-cog mr-1"></i> Settings
                </button>
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
                    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                    <p class="text-gray-600">Selamat datang kembali, {{ Auth::user()->username }}!</p>
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
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total File -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-blue-100 text-blue-600">
                            <i class="fas fa-file text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total File</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $totalFiles }}</h3>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i> {{ $totalFilesPercent ?? '0' }}% dari bulan lalu
                    </div>
                </div>

                <!-- Penyimpanan -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-green-100 text-green-600">
                            <i class="fas fa-hdd text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Penyimpanan</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $usedSpace }}</h3>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-gray-500">
                        {{ $usedPercent }} dari {{ $totalSpace }} digunakan
                    </div>
                </div>

                <!-- File Dibagikan -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-purple-100 text-purple-600">
                            <i class="fas fa-share-alt text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">File Dibagikan</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $sharedFiles }}</h3>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-blue-600">
                        <i class="fas fa-eye mr-1"></i> {{ $sharedViews ?? 0 }} views
                    </div>
                </div>

                <!-- Favorit -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-amber-100 text-amber-600">
                            <i class="fas fa-star text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Favorit</p>
                            <h3 class="text-2xl font-bold text-gray-800">{{ $favoriteFiles }}</h3>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-gray-500">
                        File yang ditandai
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Upload Section -->
            <div class="flex items-center justify-center py-5 bg-gray-50">
                <!-- Upload Section -->
                <div class="lg:col-span-2 w-full max-w-2xl">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Upload File</h3>
                            <p class="text-gray-600 mt-1">Unggah file dari komputer Anda</p>
                        </div>
                        <div class="p-6">
                            <div id="dropZone"
                                class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center transition-all duration-300 bg-gray-50 hover:bg-gray-100">
                                <i class="fas fa-cloud-upload-alt text-5xl text-gray-400 mb-4"></i>
                                <p class="text-gray-700 font-medium mb-2">Seret file ke sini atau klik untuk mengunggah
                                </p>
                                <p class="text-sm text-gray-500 mb-6">Maksimal ukuran file: 100MB. Support: PDF, DOC,
                                    JPG, PNG, MP4, MP3</p>
                                <button id="uploadButton"
                                    class="bg-blue-500 hover:bg-blue-600 text-white py-3 px-8 rounded-xl font-medium transition-colors duration-200 shadow-md hover:shadow-lg">
                                    <i class="fas fa-folder-open mr-2"></i> Pilih File
                                </button>
                                <input type="file" id="fileInput" multiple>
                            </div>

                            <!-- Upload Progress -->
                            <div id="uploadProgress" class="mt-6 hidden">
                                <div class="flex justify-between text-sm text-gray-700 mb-2">
                                    <span class="font-medium">Mengunggah file...</span>
                                    <span id="progressPercent" class="font-semibold">0%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div id="progressBar"
                                        class="progress-bar bg-gradient-to-r from-blue-500 to-indigo-600 h-2.5 rounded-full"
                                        style="width: 0%"></div>
                                </div>
                                <p id="uploadStatus" class="text-xs text-gray-500 mt-2">Memulai upload...</p>
                            </div>
                            <div id="multiUploadContainer" class="hidden mt-6 space-y-4"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Files Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div
                    class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">File Terbaru</h3>
                        <p class="text-gray-600 mt-1">File yang baru saja diakses atau diupload</p>
                    </div>
                    <div class="flex space-x-2">
                        <div class="relative w-full max-w-sm">
                            <input type="text" id="searchInput" placeholder="Cari file..."
                                class="w-full bg-gray-100 border-0 rounded-xl py-2.5 pl-4 pr-10 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all" />
                            <i class="fas fa-search absolute right-3 top-3.5 text-gray-500"></i>
                        </div>

                        <div class="relative inline-block text-left">
                            <!-- Tombol Filter -->
                            <button id="filterButton"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-4 rounded-xl font-medium transition-colors duration-200 flex items-center">
                                <i class="fas fa-filter mr-2"></i> Filter
                                <i class="fas fa-chevron-down ml-2 text-gray-500"></i>
                            </button>

                            <!-- Dropdown Filter -->
                            @php
                                // Ambil 8 file terakhir
                                $latestFiles = collect($files)->sortByDesc('created_at')->take(8);
                                // Ambil tipe dari 8 file terakhir saja
                                $latestTypes = $latestFiles->pluck('type')->unique()->values();
                                // Cek apakah ada gambar di antara 8 file terakhir
                                $hasImage = $latestTypes->contains(function ($type) {
                                    return in_array(strtolower($type), ['jpg', 'jpeg', 'png']);
                                });
                            @endphp

                            <div id="filterDropdown"
                                class="hidden absolute right-0 mt-2 max-h-64 overflow-y-auto w-48 bg-white rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 z-10 transition-all duration-200">
                                <ul class="py-2 text-gray-700" id="filterList">
                                    <li>
                                        <button
                                            class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                            data-type="all" onclick="filterFiles('all')">
                                            Semua
                                        </button>
                                    </li>

                                    @if ($hasImage)
                                        <li>
                                            <button
                                                class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                                data-type="gambar" onclick="filterFiles('gambar')">
                                                Gambar
                                            </button>
                                        </li>
                                    @endif

                                    {{-- Render hanya 8 tipe terakhir --}}
                                    @foreach ($latestTypes as $type)
                                        @if (!in_array(strtolower($type), ['jpg', 'jpeg', 'png']))
                                            <li>
                                                <button
                                                    class="filter-option w-full text-left px-4 py-2 hover:bg-gray-100 rounded-md"
                                                    data-type="{{ strtolower($type) }}"
                                                    onclick="filterFiles('{{ strtolower($type) }}')">
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
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="fileList">
                        <!-- File cards will be dynamically added here -->
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-12 hidden">
                        <i class="fas fa-folder-open text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Belum ada file</h3>
                        <p class="text-gray-500 mb-6">Mulai dengan mengupload file pertama Anda</p>
                        <button id="chooseBtn"
                            class="bg-blue-500 hover:bg-blue-600 text-white py-2.5 px-6 rounded-xl font-medium transition-colors">
                            <i class="fas fa-upload mr-2"></i> Upload File
                        </button>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Inisialisasi variabel global
        let files = [];
        let allFiles = [];
        let currentFileToShare = null;
        let users = [];
        let selectedUsers = [];
        let filteredUsers = [];
        let activeFilterType = 'all';

        // File type icons and colors - SAMA PERSIS dengan di halaman Semua File
        const fileConfig = {
            pdf: { icon: 'file-pdf', color: 'text-red-500 bg-red-100', type: 'Dokumen' },
            ppt: { icon: 'file-powerpoint', color: 'text-orange-500 bg-orange-100', type: 'Presentasi' },
            pptx: { icon: 'file-powerpoint', color: 'text-orange-500 bg-orange-100', type: 'Presentasi' },
            doc: { icon: 'file-word', color: 'text-blue-500 bg-blue-100', type: 'Dokumen' },
            docx: { icon: 'file-word', color: 'text-blue-500 bg-blue-100', type: 'Dokumen' },
            xls: { icon: 'file-excel', color: 'text-green-500 bg-green-100', type: 'Spreadsheet' },
            xlsx: { icon: 'file-excel', color: 'text-green-500 bg-green-100', type: 'Spreadsheet' },

            // GAMBAR
            jpg: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            jpeg: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            png: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            gif: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            svg: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },
            webp: { icon: 'file-image', color: 'text-purple-500 bg-purple-100', type: 'Gambar' },

            // VIDEO
            mp4: { icon: 'file-video', color: 'text-pink-500 bg-pink-100', type: 'Video' },
            mov: { icon: 'file-video', color: 'text-pink-500 bg-pink-100', type: 'Video' },
            avi: { icon: 'file-video', color: 'text-pink-500 bg-pink-100', type: 'Video' },
            mkv: { icon: 'file-video', color: 'text-pink-500 bg-pink-100', type: 'Video' },
            wmv: { icon: 'file-video', color: 'text-pink-500 bg-pink-100', type: 'Video' },
            webm: { icon: 'file-video', color: 'text-pink-500 bg-pink-100', type: 'Video' },

            // AUDIO
            mp3: { icon: 'file-audio', color: 'text-yellow-500 bg-yellow-100', type: 'Audio' },
            wav: { icon: 'file-audio', color: 'text-yellow-500 bg-yellow-100', type: 'Audio' },
            flac: { icon: 'file-audio', color: 'text-yellow-500 bg-yellow-100', type: 'Audio' },
            aac: { icon: 'file-audio', color: 'text-yellow-500 bg-yellow-100', type: 'Audio' },
            ogg: { icon: 'file-audio', color: 'text-yellow-500 bg-yellow-100', type: 'Audio' },
            mp4a: { icon: 'file-audio', color: 'text-yellow-500 bg-yellow-100', type: 'Audio' },

            // ARCHIVE
            zip: { icon: 'file-archive', color: 'text-orange-600 bg-orange-100', type: 'Archive' },
            rar: { icon: 'file-archive', color: 'text-orange-700 bg-orange-100', type: 'Archive' },
            '7z': { icon: 'file-archive', color: 'text-orange-800 bg-orange-100', type: 'Archive' },
            tar: { icon: 'file-archive', color: 'text-orange-700 bg-orange-100', type: 'Archive' },
            gz: { icon: 'file-archive', color: 'text-orange-700 bg-orange-100', type: 'Archive' },

            // TEKS
            txt: { icon: 'file-alt', color: 'text-gray-500 bg-gray-100', type: 'Teks' },

            // KML / KMZ
            kml: { icon: 'map-marked-alt', color: 'text-blue-500 bg-blue-100', type: 'Peta' },
            kmz: { icon: 'layer-group', color: 'text-purple-500 bg-purple-100', type: 'Peta' },

            // DEFAULT
            default: { icon: 'file', color: 'text-gray-500 bg-gray-100', type: 'File' },
        };

        // Fungsi untuk mengambil data users dari backend
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

        // Ambil data file dari backend Laravel
        async function fetchFiles() {
            try {
                const response = await fetch('/files');
                if (!response.ok) throw new Error('Failed to fetch files');
                files = await response.json();
                allFiles = [...files];
                renderFiles();
            } catch (error) {
                console.error('Gagal memuat data file:', error);
                // Tampilkan state kosong
                document.getElementById('emptyState').classList.remove('hidden');
            }
        }

        // Initialize aplikasi
        document.addEventListener('DOMContentLoaded', function () {
            fetchFiles();
            fetchUsers();
            initializeEventListeners();
            initializeModals();
            
            // Terapkan filter yang disimpan
            const savedFilter = localStorage.getItem('selectedFileType');
            if (savedFilter) {
                activeFilterType = savedFilter;
                document.querySelectorAll('.filter-option').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.type === savedFilter);
                });
            }
            
            applySearchAndFilter();
        });

        // Render file cards
        function renderFiles() {
            const fileList = document.getElementById('fileList');
            const emptyState = document.getElementById('emptyState');

            fileList.innerHTML = '';

            if (!files || files.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');

            // Urutkan berdasarkan created_at (terbaru di atas)
            files.sort((a, b) => {
                const dateA = new Date(a.created_at || a.date);
                const dateB = new Date(b.created_at || b.date);
                return dateB - dateA;
            });

            // Batasi hanya 8 file terbaru
            const latestFiles = files.slice(0, 8);

            latestFiles.forEach(file => {
                const ext = file.name?.split('.').pop()?.toLowerCase();
                const config = fileConfig[ext] || fileConfig.default;

                const uploadDate = file.date
                    ? file.date
                    : (file.created_at ? new Date(file.created_at).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) : 'Tidak diketahui');

                const fileCard = document.createElement('div');
                fileCard.className = 'file-card bg-white rounded-xl p-4 cursor-pointer hover:shadow-lg transition';
                fileCard.dataset.type = file.type || ext;
                fileCard.dataset.name = file.name;
                fileCard.innerHTML = `
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-3 rounded-xl ${config.color} file-type-icon">
                            <i class="fas fa-${config.icon} text-lg"></i>
                        </div>
                        <div class="relative dropdown">
                            <button 
                                class="dropdown-toggle text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                                onclick="event.stopPropagation(); toggleDropdown(this)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-content bg-white rounded-xl shadow-lg border border-gray-200 py-2 w-48 hidden absolute right-0 z-10">
                                <a href="javascript:void(0);" 
                                    onclick="showFileDetails(${JSON.stringify(file).replace(/"/g, '&quot;')})" 
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-eye mr-2"></i> Lihat Detail
                                </a>
                                <a onclick="event.stopPropagation()" href="/storage/uploads/${encodeURIComponent(file.name)}"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    download>
                                    <i class="fas fa-download mr-2"></i> Unduh
                                </a>
                                <a href="javascript:void(0);" 
                                    onclick="event.stopPropagation(); openShareModal('${file.name}')" 
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-share-alt mr-2"></i> Bagikan
                                </a>
                                <a href="javascript:void(0);" 
                                    onclick="event.stopPropagation(); confirmDelete('${encodeURIComponent(file.name)}')" 
                                    class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-trash-alt mr-2"></i> Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-2 truncate" title="${file.name}">
                        ${file.name}
                    </h4>
                    <div class="flex justify-between items-center text-sm text-gray-500 mb-3">
                        <span>${file.size || '-'}</span>
                        <span>${uploadDate}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">
                            ${file.status || 'Tersimpan'}
                        </span>
                        <a class="text-gray-400 hover:text-yellow-500 transition-colors favorite-btn">
                            <i class="far fa-star"></i>
                        </a>
                    </div>
                `;

                fileCard.addEventListener('click', () => showFileDetails(file));
                fileList.appendChild(fileCard);

                // Setup favorite button
                setupFavoriteButton(fileCard, file);
            });
        }

        // Setup favorite button functionality
        function setupFavoriteButton(fileCard, file) {
            const favoriteBtn = fileCard.querySelector('.favorite-btn');
            const starIcon = favoriteBtn.querySelector('i');
            const localKey = `favorite_${file.name}`;

            // Cek status favorit
            let isFavorite = localStorage.getItem(localKey);
            if (isFavorite === null) {
                isFavorite = file.favorite === '1' || file.favorite === 1 ? '1' : '0';
                localStorage.setItem(localKey, isFavorite);
            }

            // Terapkan tampilan
            if (isFavorite === '1') {
                starIcon.classList.remove('far', 'text-gray-400');
                starIcon.classList.add('fas', 'text-yellow-500');
            }

            // Event listener untuk toggle favorit
            favoriteBtn.addEventListener('click', (event) => {
                event.stopPropagation();
                toggleFavorite(file, starIcon, localKey);
            });
        }

        // Toggle favorite status
        async function toggleFavorite(file, starIcon, localKey) {
            try {
                const response = await fetch('/files/toggle-favorite', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ file_name: file.name })
                });

                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();

                if (data.status === 'added') {
                    starIcon.classList.remove('far', 'text-gray-400');
                    starIcon.classList.add('fas', 'text-yellow-500');
                    localStorage.setItem(localKey, '1');
                } else if (data.status === 'removed') {
                    starIcon.classList.remove('fas', 'text-yellow-500');
                    starIcon.classList.add('far', 'text-gray-400');
                    localStorage.setItem(localKey, '0');
                }

                // Reload setelah sukses
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            } catch (error) {
                console.error('Gagal toggle favorit:', error);
                Swal.fire('Error', 'Gagal mengupdate favorit', 'error');
            }
        }

        // SHARE MODAL FUNCTIONS
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

        function closeShareModal() {
            const modal = document.getElementById('shareModal');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            resetShareForm();
        }

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

        function renderUsersList() {
            const usersList = document.querySelector('.users-list');
            if (!usersList) return;

            usersList.innerHTML = '';

            // Jika kosong
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

        function updateShareButton() {
            const shareButton = document.querySelector('.share-button');
            if (shareButton) {
                shareButton.disabled = selectedUsers.length === 0;
            }
        }

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
                const recipient = selectedUsers[0];

                const body = {
                    files: [
                        {
                            name: currentFileToShare, // WAJIB
                            size: "0 MB"
                        }
                    ],
                    to_email: recipient.email
                };

                const response = await fetch('/files/share', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(body)
                });

                const data = await response.json();

                if (!response.ok) {
                    Swal.fire('Error', data.error || 'Input invalid', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Berhasil!',
                    text: `File "${currentFileToShare}" telah dibagikan kepada ${recipient.name}`,
                    icon: 'success'
                }).then(() => {
                    closeShareModal();
                    fetchFiles();
                });

            } catch (error) {
                console.error('Error sharing file:', error);
                Swal.fire('Error', 'Gagal membagikan file', 'error');
            }
        }

        // OTHER UTILITY FUNCTIONS
        function toggleDropdown(button) {
            const dropdown = button.closest('.dropdown').querySelector('.dropdown-content');
            const isHidden = dropdown.classList.contains('hidden');

            // Tutup semua dropdown lain
            document.querySelectorAll('.dropdown-content').forEach(other => {
                other.classList.add('hidden');
            });

            // Buka/tutup dropdown ini
            if (isHidden) {
                dropdown.classList.remove('hidden');
            }
        }

        function confirmDelete(filename) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "File ini akan dihapus secara permanen!",
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

        function showFileDetails(file) {
            const modal = document.getElementById('fileModal');
            const modalContent = document.getElementById('modalContent');
            const ext = file.name?.split('.').pop()?.toLowerCase();
            const config = fileConfig[ext] || fileConfig.default;

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
                                <p class="font-medium">${config.type} (${ext?.toUpperCase()})</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Ukuran</p>
                                <p class="font-medium">${file.size || '-'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Tanggal Upload</p>
                                <p class="font-medium">${file.date || '-'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <p class="font-medium">${file.shared ? 'Dibagikan' : 'Pribadi'}</p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <p class="text-sm text-gray-500 mb-2">Deskripsi</p>
                            <p class="text-gray-700">
                                File ${config.type} ini diupload pada ${file.date || 'tanggal tidak diketahui'}.
                                ${file.shared ? file.shared : ""}
                            </p>
                        </div>
                        <div class="mt-8 flex justify-end space-x-3">
                            <a href="/download/${encodeURIComponent(file.name)}"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-5 rounded-xl font-medium transition-colors duration-200 flex items-center">
                                <i class="fas fa-download mr-2"></i> Unduh
                            </a>
                            <button onclick="openShareModal('${file.name}')"
                                class="bg-blue-500 hover:bg-blue-600 text-white py-2.5 px-5 rounded-xl font-medium transition-colors duration-200 flex items-center">
                                <i class="fas fa-share-alt mr-2"></i> Bagikan
                            </button>
                        </div>
                    </div>
                </div>
            `;
            modal.classList.remove('hidden');
        }

        // SEARCH AND FILTER FUNCTIONS - SAMA PERSIS dengan di halaman Semua File
        function applySearchAndFilter() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase().trim();

            // Filter dan search pada data asli
            files = allFiles.filter(file => {
                const ext = file.name?.split('.').pop()?.toLowerCase() || '';
                const type = file.type?.toLowerCase() || ext;
                const name = (file.name || '').toLowerCase();

                const matchSearch = name.includes(searchValue) || type.includes(searchValue);

                const matchFilter =
                    activeFilterType === 'all' ||
                    type === activeFilterType ||
                    (activeFilterType === 'gambar' && ['jpg', 'jpeg', 'png', 'gif', 'svg'].includes(ext));

                return matchSearch && matchFilter;
            });

            renderFiles();
        }

        function filterFiles(type) {
            activeFilterType = type;
            localStorage.setItem('selectedFileType', type);

            // Update UI
            document.querySelectorAll('.filter-option').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.type === type);
            });

            document.getElementById('filterDropdown').classList.add('hidden');
            applySearchAndFilter();
        }

        // INITIALIZATION FUNCTIONS
        function initializeEventListeners() {
            // Mobile menu
            document.getElementById('mobileMenuBtn').addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });

            // File upload
            document.getElementById('uploadButton').addEventListener('click', () => {
                document.getElementById('fileInput').click();
            });
            document.getElementById('chooseBtn').addEventListener('click', () => {
                document.getElementById('fileInput').click();
            });

            // Search and filter
            const searchInput = document.getElementById('searchInput');
            const filterButton = document.getElementById('filterButton');
            const filterDropdown = document.getElementById('filterDropdown');

            if (searchInput) {
                searchInput.addEventListener('input', applySearchAndFilter);
            }

            if (filterButton && filterDropdown) {
                filterButton.addEventListener('click', (event) => {
                    event.stopPropagation();
                    filterDropdown.classList.toggle('hidden');
                });
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', () => {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
                if (filterDropdown) {
                    filterDropdown.classList.add('hidden');
                }
            });
        }

        function initializeModals() {
            // File modal
            document.getElementById('closeModal').addEventListener('click', () => {
                document.getElementById('fileModal').classList.add('hidden');
            });
            document.getElementById('fileModal').addEventListener('click', (e) => {
                if (e.target.id === 'fileModal') {
                    document.getElementById('fileModal').classList.add('hidden');
                }
            });

            // Settings modal
            const settingsModal = document.getElementById('settingsModal');
            const settingsBtn = document.getElementById('settingsBtn');
            const closeSettingsModal = document.getElementById('closeSettingsModal');
            const cancelSettings = document.getElementById('cancelSettings');

            if (settingsBtn) settingsBtn.addEventListener('click', () => settingsModal.classList.remove('hidden'));
            if (closeSettingsModal) closeSettingsModal.addEventListener('click', () => settingsModal.classList.add('hidden'));
            if (cancelSettings) cancelSettings.addEventListener('click', () => settingsModal.classList.add('hidden'));
            if (settingsModal) {
                settingsModal.addEventListener('click', (e) => {
                    if (e.target === settingsModal) settingsModal.classList.add('hidden');
                });
            }

            // Share modal event listeners
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

        // FILE UPLOAD FUNCTIONALITY
        document.getElementById('fileInput').addEventListener('change', handleFileUpload);

        // Drag and drop functionality
        const dropZone = document.getElementById('dropZone');
        if (dropZone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            dropZone.addEventListener('drop', handleDrop, false);
        }

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight() {
            dropZone.classList.add('drag-over');
        }

        function unhighlight() {
            dropZone.classList.remove('drag-over');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) uploadMultiple(files);
        }

        function handleFileUpload(e) {
            const files = e.target.files;
            if (files.length > 0) uploadMultiple(files);
        }

        function uploadMultiple(files) {
            const container = document.getElementById('multiUploadContainer');
            container.classList.remove('hidden');

            let completedUploads = 0;
            const totalFiles = files.length;

            for (let i = 0; i < totalFiles; i++) {
                const file = files[i];
                const uploadItem = createUploadItem(file.name);
                container.appendChild(uploadItem.element);
                uploadToLaravel(file, uploadItem, () => {
                    completedUploads++;
                    if (completedUploads === totalFiles) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 800);
                    }
                });
            }
        }

        function createUploadItem(fileName) {
            const template = document.getElementById('uploadProgress');
            const clone = template.cloneNode(true);
            clone.classList.remove('hidden');
            clone.removeAttribute('id');

            const fileNameEl = document.createElement('p');
            fileNameEl.className = 'text-xs text-gray-700 font-medium mt-1 mb-1';
            fileNameEl.textContent = fileName;

            const progressBar = clone.querySelector('#progressBar');
            const progressPercent = clone.querySelector('#progressPercent');
            const uploadStatus = clone.querySelector('#uploadStatus');

            progressBar.id = '';
            progressPercent.id = '';
            uploadStatus.id = '';

            clone.prepend(fileNameEl);

            return { element: clone, bar: progressBar, percent: progressPercent, status: uploadStatus };
        }

        function uploadToLaravel(file, ui, onComplete) {
            ui.status.textContent = 'Mempersiapkan upload...';

            const formData = new FormData();
            formData.append('file', file);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/upload', true);
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    ui.bar.style.width = percent + '%';
                    ui.percent.textContent = Math.round(percent) + '%';

                    if (percent < 30) {
                        ui.status.textContent = 'Mempersiapkan upload...';
                    } else if (percent < 70) {
                        ui.status.textContent = 'Mengunggah file...';
                    } else {
                        ui.status.textContent = 'Menyimpan file...';
                    }
                }
            });

            xhr.onload = function () {
                if (xhr.status === 200) {
                    ui.status.textContent = 'Upload selesai!';
                    ui.bar.classList.remove('from-blue-500', 'to-indigo-600');
                    ui.bar.classList.add('from-green-500', 'to-emerald-600');
                } else {
                    ui.status.textContent = 'Upload gagal.';
                    ui.bar.classList.remove('from-blue-500', 'to-indigo-600');
                    ui.bar.classList.add('from-red-500', 'to-pink-600');
                }
                if (typeof onComplete === 'function') onComplete();
            };

            xhr.onerror = () => {
                ui.status.textContent = 'Terjadi kesalahan jaringan.';
                ui.bar.classList.remove('from-blue-500', 'to-indigo-600');
                ui.bar.classList.add('from-red-500', 'to-pink-600');
                if (typeof onComplete === 'function') onComplete();
            };

            xhr.send(formData);
        }
    </script>
</body>

</html>