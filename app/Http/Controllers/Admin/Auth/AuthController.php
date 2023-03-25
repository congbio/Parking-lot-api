<?php

namespace App\Http\Controllers\Admin\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Hiển thị form đăng nhập
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Xử lý đăng nhập
    public function login(Request $request)
    {
        $this->validate($request, [
            'email'           => 'required|max:255|email',
            'password'           => 'required',
        ]);
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return 'dang nhap khong thanh cong';
        // redirect()->back()->withErrors(['message' => 'Đăng nhập không thành công.']);
    }

    // Đăng xuất
    public function logout(Request $request)
    {
        Auth::logout();

        return redirect()->route('admin.login');
    }
}
