<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Hiển thị form login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập - dùng phone hoặc email
     */
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', // Phone, Email hoặc Username
            'password' => 'required|string',
        ], [
            'identifier.required' => 'Vui lòng nhập tên đăng nhập, số điện thoại hoặc email',
            'password.required' => 'Vui lòng nhập mật khẩu',
        ]);

        $identifier = $request->input('identifier');
        $password = $request->input('password');

        // Tìm user bằng phone, email hoặc username
        $user = User::where('phone', $identifier)
                    ->orWhere('email', $identifier)
                    ->orWhere('username', $identifier)
                    ->first();

        // Kiểm tra user tồn tại và mật khẩu đúng
        if (!$user || !Hash::check($password, $user->password)) {
            return back()->withErrors([
                'login_error' => 'Tên đăng nhập, số điện thoại/Email hoặc mật khẩu không đúng',
            ])->withInput($request->only('identifier'));
        }

        // Kiểm tra user active
        if ($user->status !== 'active') {
            return back()->withErrors([
                'login_error' => 'Tài khoản của bạn đã bị khóa',
            ])->withInput($request->only('identifier'));
        }

        // Đăng nhập
        Auth::login($user, remember: $request->boolean('remember'));

        return redirect()->route('tenant.select-house');
    }

    /**
     * Đăng xuất
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('success', 'Đã đăng xuất');
    }
}
