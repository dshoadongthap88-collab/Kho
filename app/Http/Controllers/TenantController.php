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
        $allowedHouses = $user->allowed_houses ?? []; // Default empty
        $projects = \App\Models\Project::all();
        
        return view('tenant.select', compact('allowedHouses', 'projects'));
    }

    public function verifyHouse(Request $request)
    {
        $request->validate([
            'house_id' => 'required|integer|exists:projects,id',
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

        // Redirect to HR Module if House ID 5 is selected
        if ((int)$request->house_id === 5) {
            return response()->json([
                'success' => true,
                'redirect' => route('hr.projects')
            ]);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('warehouse.inventory')
        ]);
    }
}
