<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\MaintenanceService;
use Illuminate\Console\Command;

class GenerateDailyOdos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:generate-daily-odos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate pending daily ODO logs for all active assets if auto cron is enabled';

    /**
     * Execute the console command.
     */
    public function handle(MaintenanceService $service)
    {
        $isEnabled = Setting::getVal('auto_daily_odo_enabled', 'false') === 'true';

        if (!$isEnabled) {
            $this->info('Auto daily ODO generation is disabled in settings.');
            return;
        }

        $service->generatePendingDailyOdos(now()->format('Y-m-d'));
        
        $this->info('Successfully generated pending daily ODO logs for today.');
    }
}
