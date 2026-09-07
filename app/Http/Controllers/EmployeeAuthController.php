<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('employee')->check()) {
            return redirect()->route('portal.index');
        }

        return view('portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'employee_number' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // 'employment_status' => 'active' ikut jadi syarat login — otomatis
        // menolak karyawan yang sudah resign/dinonaktifkan, tanpa perlu
        // pengecekan manual terpisah (Laravel menambahkan semua key selain
        // 'password' sebagai kondisi WHERE saat mencari user).
        $attempted = Auth::guard('employee')->attempt([
            'employee_number' => $credentials['employee_number'],
            'password' => $credentials['password'],
            'employment_status' => 'active',
        ], $request->boolean('remember'));

        if (!$attempted) {
            return back()
                ->withErrors(['employee_number' => 'Nomor Karyawan atau password salah, atau akun belum aktif/tersedia. Hubungi HRD kalau belum punya akses.'])
                ->onlyInput('employee_number');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('portal.index'));
    }

    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
