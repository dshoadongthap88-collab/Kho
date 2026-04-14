<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Xác thực người dùng và cấp token (Mobile Login)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không chính xác.'
            ], 401);
        }

        $user = Auth::user();
        
        // Xoá tất cả token cũ của thiết bị di động (nếu muốn 1 thiết bị login 1 lúc thì mở dòng này)
        // $user->tokens()->where('name', 'mobile-app')->delete();

        // Tạo token mới cho App
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token
            ]
        ], 200);
    }

    /**
     * Đăng xuất và huỷ token hiện tại
     */
    public function logout(Request $request)
    {
        // Xóa token đang kích hoạt
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng xuất thành công'
        ], 200);
    }
}
