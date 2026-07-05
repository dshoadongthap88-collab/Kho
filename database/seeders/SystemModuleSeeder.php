<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moduleGroups = [
            '1. Thông tin NCC/KH' => [
                'warehouse.contacts' => 'Quản lý thông tin Đối tác/Khách hàng'
            ],
            '2. Kho' => [
                'warehouse.stock-in' => 'Nhập kho',
                'warehouse.stock-out' => 'Xuất kho',
                'warehouse.inventory' => 'Tồn kho',
                'warehouse.stock-transfer.index' => 'Chuyển kho',
                'warehouse.stock-recovery-report' => 'Thu hồi phế phẩm',
                'warehouse.stock-count' => 'Kiểm kê kho',
                'warehouse.settings.warehouses' => 'Cấu hình kho'
            ],
            '3. Danh mục vật tư' => [
                'warehouse.product-catalog' => 'Danh mục vật tư',
                'warehouse.asset-manager' => 'Danh mục thiết bị & tài sản'
            ],
            '4. Theo dõi bảo dưỡng' => [
                'warehouse.maintenance-dashboard' => 'Dashboard Tổng Quan',
                'warehouse.asset-odo-log' => 'Cập nhật giờ Odo',
                'warehouse.maintenance-rules' => 'Cấp bảo dưỡng và chu kỳ',
                'warehouse.maintenance-tracking' => 'Theo dõi bảo dưỡng tự động',
                'warehouse.maintenance-plans' => 'Lập kế hoạch bảo dưỡng',
                'warehouse.maintenance-tickets' => 'Phiếu thực hiện bảo dưỡng'
            ],
            '5. Kế hoạch & Mua hàng' => [
                'warehouse.purchase-plan' => 'Quản lý Kế hoạch',
                'warehouse.purchase-plan.history' => 'Lịch sử mua hàng'
            ],
            '6. Báo cáo' => [
                'warehouse.reports.transaction-detail' => 'Báo cáo chi tiết giao dịch',
                'warehouse.reports.stock' => 'Báo cáo kho'
            ],
            '7. Khác' => [
                'warehouse.chat' => 'Chat kho'
            ],
            '8. Trung tâm HR' => [
                'hr.dashboard' => 'Bảng điều khiển HR',
                'hr.projects' => 'Quản lý Dự án (Ngôi nhà)',
                'hr.permissions' => 'Phân quyền Hệ thống',
                'hr.notifications' => 'Quản lý Thông báo',
                'hr.global-report' => 'Báo cáo Tổng hợp',
                'hr.users.index' => 'Quản lý Nhân viên'
            ]
        ];

        \App\Models\SystemModule::truncate();

        foreach ($moduleGroups as $group => $modules) {
            foreach ($modules as $route => $label) {
                \App\Models\SystemModule::create([
                    'group_name' => $group,
                    'route_name' => $route,
                    'label' => $label,
                    'is_active' => true,
                ]);
            }
        }
    }
}
