<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Hiển thị form thay đổi mật khẩu
     */
    public function edit()
    {
        return view('password.edit');
    }

    /**
     * Cập nhật mật khẩu của user hiện tại
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới',
            'new_password.min' => 'Mật khẩu mới phải ít nhất 6 ký tự',
            'new_password.confirmed' => 'Mật khẩu xác nhận không khớp',
        ]);

        $user = Auth::user();

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Mật khẩu hiện tại không đúng',
            ]);
        }

        // Cập nhật mật khẩu mới
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Mật khẩu đã được cập nhật thành công!');
    }

    /**
     * Admin reset mật khẩu cho nhân viên (chỉ admin)
     */
    public function resetUserPassword(Request $request, $userId)
    {
        // Chỉ admin mới có quyền
        if (Auth::user()->role !== 'admin') {
            return back()->with('error', 'Bạn không có quyền thực hiện hành động này');
        }

        $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $user = \App\Models\User::findOrFail($userId);
        
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', "Mật khẩu của {$user->name} đã được đặt lại thành công!");
    }
}
