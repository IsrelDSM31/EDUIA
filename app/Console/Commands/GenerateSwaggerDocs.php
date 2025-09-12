<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GenerateSwaggerDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Swagger API documentation';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Generating Swagger API documentation...');

        try {
            // Generate the documentation
            Artisan::call('l5-swagger:generate');
            
            $this->info('Swagger documentation generated successfully!');
            $this->info('You can access it at: ' . url('/api/documentation'));
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error generating Swagger documentation: ' . $e->getMessage());
            return 1;
        }
    }
} 