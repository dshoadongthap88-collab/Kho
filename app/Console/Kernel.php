<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ODO hàng ngày
        $schedule->command('maintenance:generate-daily-odos')->dailyAt('00:01');

        // Dọn dẹp data cũ hơn 6 tháng — chạy vào 2:00 sáng ngày đầu tháng
        $schedule->command('system:purge-old-data --months=6 --force')
                 ->monthlyOn(1, '02:00')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/purge-old-data.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
