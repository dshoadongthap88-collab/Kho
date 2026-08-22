<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Project;

class AssignProjectToUsersSeeder extends Seeder
{
    /**
     * Run the database seeder.
     * 
     * Seeder này gán project_id cho users hiện có dựa trên:
     * 1. current_house_id nếu có
     * 2. allowed_houses[0] nếu có
     * 3. Project đầu tiên trong hệ thống
     * 
     * Chạy seeder này sau khi migrate add_project_id_to_users_table
     */
    public function run(): void
    {
        $this->command->info('Bắt đầu gán project_id cho users hiện có...');

        // Lấy project đầu tiên làm fallback
        $defaultProject = Project::orderBy('id')->first();
        
        if (!$defaultProject) {
            $this->command->error('Không có project nào trong hệ thống! Vui lòng tạo projects trước.');
            return;
        }

        $users = User::withoutGlobalScope(\App\Scopes\ProjectScope::class)
                    ->whereNull('project_id')
                    ->get();

        $this->command->info("Tìm thấy {$users->count()} users cần cập nhật project_id...");

        $updated = 0;
        foreach ($users as $user) {
            $projectId = null;

            // Chiến lược 1: Dùng current_house_id
            if ($user->current_house_id && Project::find($user->current_house_id)) {
                $projectId = $user->current_house_id;
                $this->command->info("User {$user->name} (ID: {$user->id}) -> Project ID: {$projectId} (từ current_house_id)");
            }
            // Chiến lược 2: Dùng allowed_houses[0]
            elseif (is_array($user->allowed_houses) && !empty($user->allowed_houses)) {
                $firstAllowedHouse = $user->allowed_houses[0];
                if (Project::find($firstAllowedHouse)) {
                    $projectId = $firstAllowedHouse;
                    $this->command->info("User {$user->name} (ID: {$user->id}) -> Project ID: {$projectId} (từ allowed_houses[0])");
                }
            }
            // Chiến lược 3: Fallback về project đầu tiên
            if (!$projectId) {
                $projectId = $defaultProject->id;
                $this->command->warn("User {$user->name} (ID: {$user->id}) -> Project ID: {$projectId} (fallback - default project)");
            }

            // Cập nhật project_id
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'project_id' => $projectId,
                    'updated_at' => now(),
                ]);

            $updated++;
        }

        $this->command->info("✓ Đã cập nhật project_id cho {$updated} users.");
        
        // Thống kê phân bố users theo project
        $this->command->newLine();
        $this->command->info('Thống kê phân bố users theo dự án:');
        
        $distribution = DB::table('users')
            ->join('projects', 'users.project_id', '=', 'projects.id')
            ->select('projects.name', 'projects.id', DB::raw('COUNT(users.id) as user_count'))
            ->groupBy('projects.id', 'projects.name')
            ->orderBy('projects.id')
            ->get();

        foreach ($distribution as $item) {
            $this->command->info("  - Dự án: {$item->name} (ID: {$item->id}) -> {$item->user_count} users");
        }
    }
}
