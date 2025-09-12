<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\InvoiceController;
use Illuminate\Http\Request;
use App\Models\User;

class GenerateSubscriptionInvoices extends Command
{
    protected $signature = 'invoices:generate-subscriptions';
    protected $description = 'Genera facturas de suscripciones y notifica a los usuarios';

    public function handle()
    {
        // Ejecutar como admin (primer admin encontrado)
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->error('No hay usuario admin para ejecutar la generación.');
            return 1;
        }
        $request = Request::create('/invoices/generate-from-subscriptions', 'POST');
        $request->setUserResolver(fn() => $admin);
        (new InvoiceController)->generateFromSubscriptions($request);
        $this->info('Facturas de suscripciones generadas y notificadas.');
        return 0;
    }
} 