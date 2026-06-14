<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrmApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $hrUser;
    private string $token;
    private string $hrToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'code' => 'EMP001',
            'name' => 'Nguyen Van A',
            'email' => 'nguyenvana@example.com',
            'password' => bcrypt('password'),
            'role' => 'sales',
            'department' => 'Kinh doanh',
            'status' => 'active',
            'permissions' => ['*'],
            'hire_date' => '2024-01-15',
        ]);

        $this->hrUser = User::create([
            'code' => 'HR001',
            'name' => 'Tran Thi HR',
            'email' => 'hr@example.com',
            'password' => bcrypt('password'),
            'role' => 'hr',
            'department' => 'Nhan su',
            'status' => 'active',
            'permissions' => ['*'],
            'hire_date' => '2023-06-01',
        ]);

        $this->token = $this->user->createToken('test')->plainTextToken;
        $this->hrToken = $this->hrUser->createToken('hr')->plainTextToken;
    }

    private function withAuth(array $headers = []): array
    {
        return array_merge($headers, ['Authorization' => 'Bearer ' . $this->token]);
    }

    private function withHrAuth(array $headers = []): array
    {
        return array_merge($headers, ['Authorization' => 'Bearer ' . $this->hrToken]);
    }

    // ==================== EMPLOYEES ====================

    public function test_can_list_employees(): void
    {
        $response = $this->getJson('/api/hrm/employees', $this->withAuth());
        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_filter_employees_by_department(): void
    {
        $response = $this->getJson('/api/hrm/employees?department=Kinh+doanh', $this->withAuth());
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.department', 'Kinh doanh');
    }

    public function test_can_show_employee(): void
    {
        $response = $this->getJson('/api/hrm/employees/' . $this->user->id, $this->withAuth());
        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonPath('data.code', 'EMP001');
    }

    public function test_can_update_employee(): void
    {
        $response = $this->putJson('/api/hrm/employees/' . $this->user->id, [
            'phone' => '0909123456',
            'status' => 'on_leave',
        ], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Cập nhật nhân viên thành công']);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'phone' => '0909123456',
            'status' => 'on_leave',
        ]);
    }

    // ==================== ATTENDANCES ====================

    public function test_can_clock_in(): void
    {
        $response = $this->postJson('/api/hrm/attendances/clock-in', [
            'work_date' => '2026-06-10',
            'clock_in' => '08:00',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Chấm công vào thành công']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'work_date' => '2026-06-10',
        ]);
    }

    public function test_can_clock_out(): void
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-06-10',
            'clock_in' => '08:00',
        ]);

        $response = $this->postJson('/api/hrm/attendances/clock-out', [
            'work_date' => '2026-06-10',
            'clock_out' => '17:00',
        ], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Chấm công ra thành công']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'work_date' => '2026-06-10',
            'clock_out' => '17:00:00',
        ]);
    }

    public function test_can_list_attendances(): void
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-06-09',
            'clock_in' => '08:00',
            'clock_out' => '17:00',
        ]);
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-06-10',
            'clock_in' => '08:15',
        ]);

        $response = $this->getJson('/api/hrm/attendances', $this->withAuth());
        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_filter_attendances_by_date_range(): void
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-06-01',
            'clock_in' => '08:00',
        ]);
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => '2026-06-10',
            'clock_in' => '08:15',
        ]);

        $response = $this->getJson('/api/hrm/attendances?from_date=2026-06-05&to_date=2026-06-15', $this->withAuth());
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items');
    }

    // ==================== LEAVE REQUESTS ====================

    public function test_can_create_leave_request(): void
    {
        $response = $this->postJson('/api/hrm/leave-requests', [
            'type' => 'annual',
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-17',
            'reason' => 'Nghi phep nam',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Tạo đơn xin nghỉ thành công']);

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $this->user->id,
            'type' => 'annual',
            'status' => 'pending',
            'total_days' => 3,
        ]);
    }

    public function test_can_list_leave_requests(): void
    {
        LeaveRequest::create([
            'user_id' => $this->user->id,
            'type' => 'sick',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-02',
            'total_days' => 2,
            'reason' => 'Om',
            'status' => 'pending',
        ]);
        LeaveRequest::create([
            'user_id' => $this->user->id,
            'type' => 'annual',
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
            'total_days' => 3,
            'reason' => 'Nghi phep',
            'status' => 'approved',
        ]);

        $response = $this->getJson('/api/hrm/leave-requests', $this->withAuth());
        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_filter_leave_requests_by_status(): void
    {
        LeaveRequest::create([
            'user_id' => $this->user->id,
            'type' => 'sick',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-02',
            'total_days' => 2,
            'reason' => 'Om',
            'status' => 'pending',
        ]);
        LeaveRequest::create([
            'user_id' => $this->user->id,
            'type' => 'annual',
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
            'total_days' => 3,
            'reason' => 'Nghi phep',
            'status' => 'approved',
        ]);

        $response = $this->getJson('/api/hrm/leave-requests?status=approved', $this->withAuth());
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items');
    }

    public function test_can_show_leave_request(): void
    {
        $leave = LeaveRequest::create([
            'user_id' => $this->user->id,
            'type' => 'annual',
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-17',
            'total_days' => 3,
            'reason' => 'Nghi phep',
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/hrm/leave-requests/' . $leave->id, $this->withAuth());
        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonPath('data.type', 'annual');
    }

    public function test_can_approve_leave_request(): void
    {
        $leave = LeaveRequest::create([
            'user_id' => $this->user->id,
            'type' => 'annual',
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-17',
            'total_days' => 3,
            'reason' => 'Nghi phep',
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/hrm/leave-requests/' . $leave->id . '/approve', [], $this->withHrAuth());
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Duyệt đơn xin nghỉ thành công']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'approved',
            'approved_by' => $this->hrUser->id,
        ]);
    }

    public function test_can_reject_leave_request(): void
    {
        $leave = LeaveRequest::create([
            'user_id' => $this->user->id,
            'type' => 'annual',
            'start_date' => '2026-06-15',
            'end_date' => '2026-06-17',
            'total_days' => 3,
            'reason' => 'Nghi phep',
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/hrm/leave-requests/' . $leave->id . '/reject', [
            'reject_reason' => 'Khong du nhan su',
        ], $this->withHrAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Từ chối đơn xin nghỉ thành công']);

        $this->assertDatabaseHas('leave_requests', [
            'id' => $leave->id,
            'status' => 'rejected',
            'reject_reason' => 'Khong du nhan su',
        ]);
    }

    public function test_leave_request_validates_end_date_after_start(): void
    {
        $response = $this->postJson('/api/hrm/leave-requests', [
            'type' => 'annual',
            'start_date' => '2026-06-20',
            'end_date' => '2026-06-15',
            'reason' => 'Test',
        ], $this->withAuth());

        $response->assertStatus(422);
    }

    public function test_hrm_endpoints_require_auth(): void
    {
        $response = $this->getJson('/api/hrm/employees');
        $response->assertStatus(401);
    }
}