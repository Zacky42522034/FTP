<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\File;
use App\Models\User;
use App\Models\SharedFile;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class FileController extends Controller
{
    public function upload(Request $request)
    {
        try {

            // Validasi manual berdasarkan ekstensi
            $request->validate([
                'file' => [
                    'required',
                    'file',
                    function ($attribute, $value, $fail) {
                        $ext = strtolower($value->getClientOriginalExtension());

                        $allowed = [
                            'pdf',
                            'doc',
                            'docx',
                            'jpg',
                            'jpeg',
                            'png',
                            'mp4',
                            'mp3',
                            'zip',
                            'txt',
                            'xlsx',
                            'rar',
                            'pptx',
                            'kmz',
                            'avi',
                            'mkv',
                            '7z',
                            'kml'
                        ];

                        if (!in_array($ext, $allowed)) {
                            return $fail("File dengan ekstensi .$ext tidak didukung.");
                        }
                    }
                ],
            ]);

            $file = $request->file('file');

            // Gunakan nama asli file
            $originalName = $file->getClientOriginalName();

            // Simpan sesuai nama asli
            $path = $file->storeAs('uploads', $originalName, 'public');

            $fileSize = round($file->getSize() / 1024 / 1024, 2) . ' MB';
            $extension = strtolower($file->getClientOriginalExtension());
            $tanggalUpload = now()->translatedFormat('d M Y');

            $deskripsi = "File {$extension} ini diupload pada {$tanggalUpload}";

            // Simpan ke database
            $fileData = File::create([
                'user_id' => auth()->id(),
                'name' => $originalName,
                'type' => $extension,
                'size' => $fileSize,
                'status' => 'uploaded',
                'desc' => $deskripsi,
            ]);

            return response()->json([
                'message' => 'Upload berhasil!',
                'file' => $fileData,
                'path' => asset('storage/' . $path),
            ]);

        } catch (\Exception $e) {

            Log::error('Upload Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Upload gagal: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function getFiles()
    {
        // Ambil user yang sedang login
        $user = Auth::user();

        // Pastikan user login
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Ambil file berdasarkan user yang upload (user_id)
        $files = File::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'type' => $file->type,
                    'size' => $file->size,
                    'status' => $file->status,
                    'desc' => $file->desc,
                    'created_at' => $file->created_at, // Data mentah
                    'date' => $file->created_at->format('d M Y'),
                ];
            });

        return response()->json($files);
    }


    public function download($filename)
    {
        // Decode URL (mengatasi encodeURIComponent di JS)
        $decodedName = urldecode($filename);

        // Path lengkap file
        $path = storage_path('app/public/uploads/' . $decodedName);

        // Cek apakah file ada
        if (file_exists($path)) {
            return response()->download($path, $decodedName);
        }

        return abort(404, 'File tidak ditemukan.');

    }

    public function delete($filename)
    {
        $decodedName = urldecode($filename);
        $filePath = storage_path('app/public/uploads/' . $decodedName);

        // 🔹 Hapus file fisik dari storage
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // 🔹 Hapus juga dari database (berdasarkan nama file)
        $deleted = File::where('name', $decodedName)->delete();

        if ($deleted) {
            return redirect()->back()->with('success', 'File berhasil dihapus dari storage dan database.');
        }

        return redirect()->back()->with('error', 'File tidak ditemukan di database.');
    }

    // Toggle favorite
    public function toggleFavorite(Request $request)
    {
        $fileName = $request->input('file_name');

        $file = File::where('name', $fileName)->first();

        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $file->favorite = $file->favorite === '1' ? '0' : '1';
        $file->save();

        return response()->json([
            'status' => $file->favorite === '1' ? 'added' : 'removed'
        ]);
    }

    // Ambil semua file favorit
    public function getFavorites()
    {
        $favorites = File::where('favorite', '1')->pluck('name');
        return response()->json($favorites);
    }

    public function storageInfo()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'User not logged in'
            ], 401);
        }

        $totalSpace = 30 * 1024 * 1024 * 1024; // 30 GB

        // Pastikan size di-convert ke numeric jika tersimpan sebagai string
        $usedSpace = File::where('user_id', $user->id)
            ->sum(DB::raw('CAST(size AS UNSIGNED)'));

        $freeSpace = max($totalSpace - $usedSpace, 0);
        $usedPercent = $totalSpace > 0 ? ($usedSpace / $totalSpace) * 100 : 0;

        return response()->json([
            'user' => $user->username,
            'total_space' => $this->formatBytes($totalSpace),
            'used_space' => $this->formatBytes($usedSpace),
            'free_space' => $this->formatBytes($freeSpace),
            'used_percent' => round($usedPercent, 2) . '%',
        ]);
    }

    /**
     * Format bytes menjadi readable string
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

    public function share(Request $request)
    {
        try {
            // Validasi input
            if (!$request->has('files') || !$request->has('to_email')) {
                return response()->json(['error' => 'Input invalid'], 422);
            }

            $files = $request->input('files');
            $toEmail = $request->input('to_email');
            $fromUser = auth()->id();

            if (!$files || count($files) === 0) {
                return response()->json(['error' => 'Files empty'], 422);
            }

            foreach ($files as $file) {

                // FIX UTAMA -> name wajib ada
                $name = $file['name'] ?? null;
                if (!$name) {
                    return response()->json(['error' => 'File name missing'], 422);
                }

                $type = $name; // mengikuti permintaan: type = name
                $size = $file['size'] ?? '0 MB';

                SharedFile::create([
                    'from_user' => $fromUser,
                    'to_email' => $toEmail,
                    'name' => $name,
                    'type' => $type,
                    'size' => $size,
                    'status' => 'terkirim',
                    'desc' => 'File ' . $name . ' dibagikan oleh '
                        . auth()->user()->name
                        . ' pada ' . now()->format('d M Y H:i'),
                    'favorite' => 0,
                ]);
            }

            return response()->json(['success' => true], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }





}
