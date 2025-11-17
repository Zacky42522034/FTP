<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EarthController extends Controller
{
    public function earth(Request $request)
    {

        $user = Auth::user();

        // Pastikan user login
        if (!$user) {
            return redirect()->route('login');
        }

        // 🔍 Pencarian file
        $search = $request->input('search');

        $files = File::where('user_id', $user->id)
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->get();

        // Ambil daftar type unik
        $types = File::where('user_id', $user->id)
            ->select('type')
            ->distinct()
            ->pluck('type');

        // 🔢 Statistik dashboard umum
        $totalFiles = $files->count();
        $sharedFiles = $files->whereNotNull('share')->count();
        $favoriteFiles = $files->where('favorite', 1)->count();

        // 💾 Hitung penggunaan storage
        $totalSpaceBytes = 30 * 1024 * 1024 * 1024; // 30 GB
        $usedBytes = 0;

        foreach ($files as $file) {
            $usedBytes += $this->convertToBytes($file->size);
        }

        $usedSpace = $this->formatBytes($usedBytes);
        $totalSpace = $this->formatBytes($totalSpaceBytes);
        $usedPercent = round(($usedBytes / $totalSpaceBytes) * 100, 2) . '%';

        // 📊 Kategori file berdasarkan ekstensi/type
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
        $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];
        $videoExtensions = ['mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv'];
        $audioExtensions = ['mp3', 'wav', 'ogg', 'm4a', 'flac'];
        $archiveExtensions = ['zip', 'rar', '7z', 'tar', 'gz'];
        $mapExtensions = ['kmz','kml'];

        $totalImages = $files->whereIn('type', $imageExtensions)->count();
        $totalDocuments = $files->whereIn('type', $documentExtensions)->count();
        $totalVideos = $files->whereIn('type', $videoExtensions)->count();
        $totalAudios = $files->whereIn('type', $audioExtensions)->count();
        $totalArchives = $files->whereIn('type', $archiveExtensions)->count();
        $totalMap = $files->whereIn('type', $mapExtensions)->count();

        return view('earth', compact(
            'files',
            'types',
            'search',
            'totalFiles',
            'sharedFiles',
            'favoriteFiles',
            'usedSpace',
            'totalSpace',
            'usedPercent',
            'totalImages',
            'totalDocuments',
            'totalVideos',
            'totalAudios',
            'totalArchives',
            'totalMap'
        ));
    }

    private function convertToBytes($size)
    {
        $size = trim($size);
        $unit = strtolower(substr($size, -2));
        $number = (float) $size;

        switch ($unit) {
            case 'kb':
                return $number * 1024;
            case 'mb':
                return $number * 1024 * 1024;
            case 'gb':
                return $number * 1024 * 1024 * 1024;
            default:
                return $number; // asume bytes jika tanpa unit
        }
    }

    /**
     * Format byte jadi string readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
