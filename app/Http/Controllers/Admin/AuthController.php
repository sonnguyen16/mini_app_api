<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Hiển thị trang đăng nhập
     */
    public function showLogin()
    {
        // Nếu đã đăng nhập thì redirect về dashboard
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return Inertia::render('Auth/Login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ]);
        }

        // Kiểm tra quyền admin hoặc app_owner
        if (!$user->hasRole(['admin', 'app_owner'])) {
            return back()->withErrors([
                'email' => 'Bạn không có quyền truy cập trang quản trị.',
            ]);
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended('/admin/dashboard');
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
