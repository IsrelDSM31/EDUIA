<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Invoice;
use App\Notifications\InvoiceGeneratedNotification;
use Illuminate\Support\Facades\DB;

class TestGlobalNotifications extends Command
{
    protected $signature = 'notifications:test-global';
    protected $description = 'Genera notificaciones de prueba para el sistema global';

    public function handle()
    {
        $this->info('Generando notificaciones de prueba...');

        // Verificar si hay usuarios
        $users = User::where('role', '!=', 'admin')->take(3)->get();
        $this->info('Usuarios encontrados: ' . $users->count());
        
        if ($users->isEmpty()) {
            $this->error('No hay usuarios no-admin para generar notificaciones.');
            return 1;
        }

        // Verificar tabla de notificaciones
        $this->info('Notificaciones antes: ' . DB::table('notifications')->count());

        // Crear facturas de prueba y notificaciones
        foreach ($users as $user) {
            $this->info("Procesando usuario: {$user->name} (ID: {$user->id})");
            
            // Crear factura de prueba
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'number' => 'TEST-' . $user->id . '-' . now()->format('YmdHis'),
                'amount' => rand(100, 1000),
                'due_date' => now()->addDays(30),
                'description' => 'Factura de prueba para notificaciones globales',
                'status' => 'pending',
            ]);

            $this->info("Factura creada: {$invoice->number}");

            try {
                // Generar notificación
                $user->notify(new InvoiceGeneratedNotification($invoice));
                $this->info("Notificación enviada para: {$user->name}");
            } catch (\Exception $e) {
                $this->error("Error enviando notificación: " . $e->getMessage());
            }
        }

        $this->info('Notificaciones después: ' . DB::table('notifications')->count());
        $this->info('Notificaciones de facturas: ' . DB::table('notifications')->where('type', 'App\\Notifications\\InvoiceGeneratedNotification')->count());

        $this->info('Notificaciones de prueba generadas correctamente.');
        return 0;
    }
} 