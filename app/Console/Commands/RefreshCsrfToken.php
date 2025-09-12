<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class RefreshCsrfToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csrf:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh CSRF tokens and clear session cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Refreshing CSRF tokens and clearing session cache...');

        // Clear session cache
        Cache::flush();
        $this->info('✓ Cache cleared');

        // Clear session files
        $sessionPath = storage_path('framework/sessions');
        if (is_dir($sessionPath)) {
            $files = glob($sessionPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            $this->info('✓ Session files cleared');
        }

        // Clear application cache
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        $this->info('✓ All caches cleared');
        $this->info('CSRF tokens refreshed successfully!');
        $this->info('Please restart your web server and try again.');

        return Command::SUCCESS;
    }
}
