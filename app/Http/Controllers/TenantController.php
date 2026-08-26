<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    public function selectHouse(Request $request)
    {
        $user = Auth::user();
        $projects = \App\Models\Project::all();

        // Phân quyền theo allowed_houses — đây là cơ chế chính
        // Admin: thấy và vào được tất cả houses
        // User thường: chỉ thấy các house trong allowed_houses của mình
        $allowedHouses = $user->role === 'admin'
            ? $projects->pluck('id')->toArray()
            : (is_array($user->allowed_houses) ? $user->allowed_houses : []);

        // Đổi dự án từ menu trên thanh tiêu đề: mở sẵn ô nhập PIN của đúng dự án
        // được chọn, khỏi phải bấm lại lần nữa ở màn này.
        //
        // Vẫn phải nhập PIN như thường — chỉ bỏ bớt một cú bấm, không bỏ bước
        // xác thực. Quyền vào dự án do verifyHouse() kiểm tra.
        // allowed_houses lưu dạng JSON nên phần tử có thể là chuỗi ("1") hoặc
        // số (1) tuỳ dữ liệu từng tài khoản — quy về số trước khi đối chiếu,
        // nếu không nhân viên có quyền hợp lệ vẫn bị chặn.
        $preselect = $request->integer('house') ?: null;

        if ($preselect !== null && !in_array($preselect, array_map('intval', $allowedHouses), true)) {
            $preselect = null;
        }

        return view('tenant.select', compact('allowedHouses', 'projects', 'preselect'));
    }

    public function verifyHouse(Request $request)
    {
        $request->validate([
            'house_id' => 'required|integer|exists:projects,id',
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        // Phân quyền theo allowed_houses
        $allowedHouses = $user->role === 'admin'
            ? \App\Models\Project::pluck('id')->toArray()
            : (is_array($user->allowed_houses) ? $user->allowed_houses : []);

        // Kiểm tra quyền truy cập house
        if (!in_array((int)$request->house_id, $allowedHouses)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập vào chi nhánh này.'
            ], 403);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã PIN (mật khẩu) không chính xác'
            ], 401);
        }

        // Lưu house đã chọn vào session
        session(['current_house' => $request->house_id]);

        // Sync current_house_id vào user model
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->update(['current_house_id' => $request->house_id]);
        $user->current_house_id = $request->house_id;

        // Redirect sang HR Module nếu chọn House ID 5
        if ((int)$request->house_id === 5) {
            return response()->json([
                'success' => true,
                'redirect' => route('hr.projects')
            ]);
        }

        // Xác định route phù hợp nhất dựa trên permissions của user
        $redirectRoute = 'warehouse.inventory';
        if ($user->role !== 'admin') {
            $permissions = is_array($user->permissions) ? $user->permissions : [];

            $routeMap = [
                'warehouse.inventory'           => 'warehouse.inventory',
                'warehouse.stock-out'           => 'warehouse.stock-out',
                'warehouse.stock-in'            => 'warehouse.stock-in',
                'warehouse.stock-transfer.index'=> 'warehouse.stock-transfer.index',
                'warehouse.stock-count'         => 'warehouse.stock-count',
                'warehouse.product-catalog'     => 'warehouse.product-catalog',
                'warehouse.contacts'            => 'warehouse.contacts',
                'warehouse.asset-manager'       => 'warehouse.asset-manager',
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
