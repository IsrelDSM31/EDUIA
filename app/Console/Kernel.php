<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\GenerateAutomaticAlerts::class,
        Commands\GenerateSubscriptionInvoices::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Generar alertas automáticas cada día a las 6:00 AM
        $schedule->command('alerts:generate')
                ->dailyAt('06:00')
                ->appendOutputTo(storage_path('logs/alerts.log'));
        // Generar facturas de suscripciones el primer día de cada mes a las 7:00 AM
        $schedule->command('invoices:generate-subscriptions')
                ->monthlyOn(1, '07:00')
                ->appendOutputTo(storage_path('logs/invoices.log'));
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