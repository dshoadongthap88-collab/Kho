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
        $projects = \App\Models\Project::all();
        
        if ($user->role === 'admin') {
            $allowedHouses = $projects->pluck('id')->toArray();
        } else {
            // Dùng project_id nếu có (cột mới), fallback về allowed_houses (cột cũ)
            $projectId = null;
            try {
                $projectId = $user->project_id ?? null;
            } catch (\Exception $e) {
                // Cột project_id chưa tồn tại (chưa migrate) — bỏ qua
            }

            if ($projectId) {
                $allowedHouses = [$projectId];
            } else {
                $allowedHouses = is_array($user->allowed_houses) ? $user->allowed_houses : [];
            }

            // Nếu vẫn rỗng thì gán tất cả để user không bị kẹt
            if (empty($allowedHouses)) {
                $allowedHouses = $projects->pluck('id')->toArray();
            }
        }
        
        return view('tenant.select', compact('allowedHouses', 'projects'));
    }

    public function verifyHouse(Request $request)
    {
        $request->validate([
            'house_id' => 'required|integer|exists:projects,id',
            'password' => 'required|string',
        ]);

        $user = Auth::user();
        
        // Xác định các dự án mà user có quyền truy cập
        if ($user->role === 'admin') {
            $allowedHouses = \App\Models\Project::pluck('id')->toArray();
        } else {
            // Dùng project_id nếu có (cột mới), fallback về allowed_houses (cột cũ)
            $projectId = null;
            try {
                $projectId = $user->project_id ?? null;
            } catch (\Exception $e) {
                // Cột project_id chưa tồn tại — bỏ qua
            }

            if ($projectId) {
                $allowedHouses = [$projectId];
            } else {
                $allowedHouses = is_array($user->allowed_houses) ? $user->allowed_houses : [];
            }

            // Nếu vẫn rỗng thì gán tất cả để user không bị kẹt
            if (empty($allowedHouses)) {
                $allowedHouses = \App\Models\Project::pluck('id')->toArray();
            }
        }

        // Kiểm tra quyền truy cập dự án
        if (!in_array((int)$request->house_id, $allowedHouses)) {
            $projectName = 'không xác định';
            try {
                $projectName = $user->project->name ?? 'không xác định';
            } catch (\Exception $e) {
                // relation hoặc cột chưa tồn tại
            }
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập vào Dự án này. Chỉ được truy cập dự án: ' . $projectName
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

        // Sync current_house_id to user model
        $user->current_house_id = $request->house_id;
        $user->save();

        // Redirect to HR Module if House ID 5 is selected
        if ((int)$request->house_id === 5) {
            return response()->json([
                'success' => true,
                'redirect' => route('hr.projects')
            ]);
        }

        // Determine the best redirect route for warehouse
        $redirectRoute = 'warehouse.inventory'; // Fallback
        if ($user->role !== 'admin') {
            $permissions = is_array($user->permissions) ? $user->permissions : [];
            
            $routeMap = [
                'warehouse.inventory' => 'warehouse.inventory',
                'warehouse.stock-out' => 'warehouse.stock-out',
                'warehouse.stock-in' => 'warehouse.stock-in',
                'warehouse.stock-transfer.index' => 'warehouse.stock-transfer.index',
                'warehouse.stock-count' => 'warehouse.stock-count',
                'warehouse.product-catalog' => 'warehouse.product-catalog',
                'warehouse.contacts' => 'warehouse.contacts',
                'warehouse.asset-manager' => 'warehouse.asset-manager',
                'warehouse.maintenance-dashboard' => 'warehouse.maintenance-dashboard',
            ];

            foreach ($routeMap as $perm => $routeName) {
                if (in_array($perm, $permissions)) {
                    $redirectRoute = $routeName;
                    break;
                }
            }
        }

        return response()->json([
            'success' => true,
            'redirect' => route($redirectRoute)
        ]);
    }
}
