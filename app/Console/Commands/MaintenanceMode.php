<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MaintenanceMode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:toggle {action : enable or disable maintenance mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle maintenance mode (only allows admin access)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        if (!in_array($action, ['enable', 'disable'])) {
            $this->error('Invalid action. Use "enable" or "disable".');
            return 1;
        }

        if ($action === 'enable') {
            file_put_contents(storage_path('framework/maintenance.json'), json_encode(['enabled' => true, 'time' => now()->timestamp]));
            $this->info('Maintenance mode enabled. Only admin users can access the system.');
        } else {
            if (file_exists(storage_path('framework/maintenance.json'))) {
                unlink(storage_path('framework/maintenance.json'));
            }
            $this->info('Maintenance mode disabled. All users can now access the system.');
        }

        return 0;
    }
}
