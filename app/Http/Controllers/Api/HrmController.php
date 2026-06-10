<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\HrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrmController extends Controller
{
    public function employees(Request $request)
    {
        $service = app(HrmService::class);
        $data = $service->getEmployees($request->only(['search', 'department', 'role', 'status']));

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                ],
            ],
        ], 200);
    }

    public function showEmployee(User $user)
    {
        $user->load('attendances', 'leaveRequests');

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ], 200);
    }

    public function updateEmployee(Request $request, User $user)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'role' => 'nullable|in:admin,sales,production,warehouse,hr,viewer',
            'status' => 'nullable|in:active,inactive,on_leave',
            'hire_date' => 'nullable|date',
            'avatar' => 'nullable|url',
        ]);

        $service = app(HrmService::class);
        $user = $service->updateEmployee($user, $request->only(['name', 'email', 'phone', 'department', 'role', 'status', 'hire_date', 'avatar']));

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật nhân viên thành công',
            'data' => $user,
        ], 200);
    }

    public function attendances(Request $request)
    {
        $service = app(HrmService::class);
        $data = $service->getAttendances($request->only(['user_id', 'from_date', 'to_date']));

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                ],
            ],
        ], 200);
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'work_date' => 'nullable|date',
            'clock_in' => 'nullable|date_format:H:i',
        ]);

        $service = app(HrmService::class);
        $attendance = $service->clockIn($request->only(['work_date', 'clock_in']));

        return response()->json([
            'status' => 'success',
            'message' => 'Chấm công vào thành công',
            'data' => $attendance,
        ], 201);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'work_date' => 'nullable|date',
            'clock_out' => 'nullable|date_format:H:i',
        ]);

        $service = app(HrmService::class);
        $attendance = $service->clockOut($request->only(['work_date', 'clock_out']));

        return response()->json([
            'status' => 'success',
            'message' => 'Chấm công ra thành công',
            'data' => $attendance,
        ], 200);
    }

    public function leaveRequests(Request $request)
    {
        $service = app(HrmService::class);
        $data = $service->getLeaveRequests($request->only(['user_id', 'status', 'type']));

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                ],
            ],
        ], 200);
    }

    public function storeLeaveRequest(Request $request)
    {
        $request->validate([
            'type' => 'required|in:annual,sick,maternity,unpaid,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $service = app(HrmService::class);
        $leave = $service->createLeaveRequest($request->only(['type', 'start_date', 'end_date', 'reason']));

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo đơn xin nghỉ thành công',
            'data' => $leave,
        ], 201);
    }

    public function showLeaveRequest(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load('user', 'approver');

        return response()->json([
            'status' => 'success',
            'data' => $leaveRequest,
        ], 200);
    }

    public function approveLeaveRequest($id)
    {
        $service = app(HrmService::class);
        $leave = $service->approveLeaveRequest((int) $id, Auth::id());

        return response()->json([
            'status' => 'success',
            'message' => 'Duyệt đơn xin nghỉ thành công',
            'data' => $leave,
        ], 200);
    }

    public function rejectLeaveRequest(Request $request, $id)
    {
        $request->validate([
            'reject_reason' => 'nullable|string',
        ]);

        $service = app(HrmService::class);
        $leave = $service->rejectLeaveRequest((int) $id, Auth::id(), $request->reject_reason);

        return response()->json([
            'status' => 'success',
            'message' => 'Từ chối đơn xin nghỉ thành công',
            'data' => $leave,
        ], 200);
    }
}