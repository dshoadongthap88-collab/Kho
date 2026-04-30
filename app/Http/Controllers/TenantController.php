<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    public function selectHouse()
    {
        $user = Auth::user();
        $allowedHouses = $user->allowed_houses ?? [1]; // Default to house 1 if null
        
        return view('tenant.select', compact('allowedHouses'));
    }

    public function verifyHouse(Request $request)
    {
        $request->validate([
            'house_id' => 'required|integer|in:1,2,3,4',
            'password' => 'required|string',
        ]);

        $user = Auth::user();
        $allowedHouses = $user->allowed_houses ?? [1];

        // Check if user has permission for this house
        if (!in_array((int)$request->house_id, $allowedHouses)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập vào Nhà số ' . $request->house_id
            ], 403);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã PIN (mật khẩu) không chính xác'
            ], 401);
        }

        // Store selected house in session
        session(['current_house' => $request->house_id]);

        return response()->json([
            'success' => true,
            'redirect' => route('warehouse.inventory')
        ]);
    }
}
