<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|string',

        ]);
        //creae a new user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        if($user->save()){
            return redirect()->route('login.auth')->with('success', 'User registered successfully, you can now login');
        }else{
            return back()->withErrors([
                'email' => 'Đăng ký không thành công, vui lòng thử lại.',
            ])->withInput();
        }

    }

    public function login(Request $request){
        // Validate the request
        $login = $request->validate([
           'email'=>'required|string|email',
           'password'=>'required|string',
        ]);
         // Check login
        if (Auth::attempt($login, $request->remember)) {// Check if 'remember' is set
            $request->session()->regenerate(); // tránh session fixation, tạo một session mới thay cho session cũ
            return redirect()->route('dashboard')->with('success', 'Login successful!');
        }

        return back()->withErrors([
            'email' => 'email do not match.',
            'password' => 'Password do not match.',
        ])->withInput();
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.auth');
    }
}
