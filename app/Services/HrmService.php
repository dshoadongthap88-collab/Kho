<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\DB;

class HrmService
{
    /**
     * Lấy danh sách nhân viên
     * ProjectScope tự động lọc theo dự án của user hiện tại
     * Admin thấy tất cả, user thường chỉ thấy users cùng dự án
     */
    public function getEmployees(array $filters = [])
    {
        $query = User::query(); // ProjectScope tự động áp dụng

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->paginate(20);
    }

    /**
     * Cập nhật thông tin nhân viên
     * Kiểm tra quyền truy cập trước khi cho phép cập nhật
     */
    public function updateEmployee(User $user, array $data): User
    {
        // Kiểm tra quyền: chỉ được cập nhật user cùng dự án hoặc admin
        $currentUser = auth()->user();
        if (!$currentUser->canViewUser($user)) {
            throw new \Exception('Bạn không có quyền cập nhật nhân viên này');
        }

        $user->update($data);
        return $user->fresh();
    }

    public function getAttendances(array $filters = [])
    {
        $query = Attendance::with('user');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('work_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('work_date', '<=', $filters['to_date']);
        }

        return $query->orderBy('work_date', 'desc')->paginate(20);
    }

    public function clockIn(array $data): Attendance
    {
        $userId = $data['user_id'] ?? auth()->id();
        $workDate = $data['work_date'] ?? now()->toDateString();
        $clockInTime = $data['clock_in'] ?? now();

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $userId, 'work_date' => $workDate],
            ['clock_in' => $clockInTime]
        );

        return $attendance->load('user');
    }

    public function clockOut(array $data): Attendance
    {
        $userId = $data['user_id'] ?? auth()->id();
        $workDate = $data['work_date'] ?? now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('work_date', $workDate)
            ->firstOrFail();

        $clockOut = $data['clock_out'] ?? now();
        $attendance->update(['clock_out' => $clockOut]);

        return $attendance->load('user');
    }

    public function getLeaveRequests(array $filters = [])
    {
        $query = LeaveRequest::with(['user', 'approver']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function createLeaveRequest(array $data): LeaveRequest
    {
        $data['user_id'] = $data['user_id'] ?? auth()->id();
        $data['total_days'] = $this->calculateDays($data['start_date'], $data['end_date']);
        $data['status'] = 'pending';

        return DB::transaction(function () use ($data) {
            $request = LeaveRequest::create($data);
            return $request->load('user');
        });
    }

    public function approveLeaveRequest(int $requestId, int $approvedBy): LeaveRequest
    {
        return DB::transaction(function () use ($requestId, $approvedBy) {
            $request = LeaveRequest::where('id', $requestId)
                ->where('status', 'pending')
                ->firstOrFail();

            $request->update([
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            return $request->load('user', 'approver');
        });
    }

    public function rejectLeaveRequest(int $requestId, int $rejectedBy, ?string $reason = null): LeaveRequest
    {
        return DB::transaction(function () use ($requestId, $rejectedBy, $reason) {
            $request = LeaveRequest::where('id', $requestId)
                ->where('status', 'pending')
                ->firstOrFail();

            $request->update([
                'status' => 'rejected',
                'approved_by' => $rejectedBy,
                'approved_at' => now(),
                'reject_reason' => $reason,
            ]);

            return $request->load('user', 'approver');
        });
    }

    private function calculateDays(string $start, string $end): int
    {
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);
        $interval = $startDate->diff($endDate);
        return (int) $interval->days + 1;
    }
}