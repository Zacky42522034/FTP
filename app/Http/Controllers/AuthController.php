<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Halaman login
    public function showLoginForm()
    {
        return view('auth.Login');
    }

    // Halaman register
    public function showRegisterForm()
    {
        return view('auth.Register');
    }

    // Proses register
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|min:2',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('dashboard')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    // Proses login
    public function login(Request $request)
    {

        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Coba login dengan 'remember me'
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'))->with('success', 'Berhasil login!');
        }

        // Jika gagal
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }


    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        // VALIDATION RULES
        $rules = [
            'username' => 'required|min:2',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
        ];

        $validated = $request->validate($rules);

        // SIAPKAN DATA UNTUK UPDATE
        $data = [
            'username' => $validated['username'],
            'email' => $validated['email'],
        ];

        // Jika password diisi → hash & masukkan ke $data
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // UPDATE MODEL
        $user->update($data);

        return back()->with('success', 'Pengaturan akun berhasil diperbarui.');
    }

    public function users()
    {
        $currentUserId = auth()->id(); // user yang sedang login

        $users = User::select('id', 'username', 'email')
            ->where('id', '!=', $currentUserId) // exclude self
            ->get();

        $users = $users->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->username, // supaya JS tetap pakai user.name
                'email' => $u->email,
                'avatar' => strtoupper(substr($u->username, 0, 2)),
                'color' => collect([
                    'bg-blue-500',
                    'bg-pink-500',
                    'bg-green-500',
                    'bg-purple-500',
                    'bg-yellow-500',
                    'bg-red-500',
                    'bg-indigo-500',
                    'bg-teal-500'
                ])->random(),
            ];
        });

        return response()->json($users);
    }




}
