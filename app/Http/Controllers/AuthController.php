<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Hiển thị trang đăng ký.
     * Route: GET /signup
     */
    public function showRegistrationForm()
    {
        return view('auth.signupPage');
    }

    /**
     * Xử lý dữ liệu từ form đăng ký.
     * Route: POST /signup
     */
    public function register(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            // Thêm 'confirmed' để Laravel tự động kiểm tra với ô password_confirmation
            'password' => 'required|string|confirmed',
        ]);

        // Create a new user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        // Mặc định vai trò là 'developer' khi đăng ký
        $user->role = 'developer';

        if ($user->save()) {
            return redirect()->route('login.auth')->with('success', 'User registered successfully, you can now login');
        } else {
            return back()->withErrors([
                'email' => 'Đăng ký không thành công, vui lòng thử lại.',
            ])->withInput();
        }
    }

    /**
     * Hiển thị trang đăng nhập.
     * Route: GET /login
     * ĐÂY LÀ HÀM BỊ THIẾU GÂY RA LỖI
     */
    public function showLoginForm()
    {
        return view('auth.loginPage');
    }

    /**
     * Xử lý dữ liệu từ form đăng nhập.
     * Route: POST /login
     */
    public function login(Request $request)
    {
        // Validate the request
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Check login
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate(); // Tránh session fixation
            return redirect()->intended('dashboard')->with('success', 'Login successful!');
        }

        // Nếu đăng nhập thất bại
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Xử lý đăng xuất.
     * Route: POST /logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.auth');
    }
}
