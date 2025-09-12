<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;

class CreateTeacherUser extends Command
{
    protected $signature = 'user:create-teacher';
    protected $description = 'Crea un usuario profesor con suscripción activa';

    public function handle()
    {
        $this->info('Creando usuario profesor...');

        // Verificar si el usuario ya existe
        $existingUser = User::where('email', 'jesus@eduai.com')->first();
        if ($existingUser) {
            $this->error('El usuario jesus@eduai.com ya existe.');
            return 1;
        }

        // Crear el usuario
        $user = User::create([
            'name' => 'Jesús',
            'email' => 'jesus@eduai.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        $this->info("Usuario creado: {$user->name} ({$user->email})");

        // Crear suscripción activa
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => 'premium',
            'amount' => 99.99,
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'payment_method' => 'manual',
            'transaction_id' => 'MANUAL-' . $user->id,
        ]);

        $this->info("Suscripción creada: {$subscription->plan_type} - Válida hasta: {$subscription->end_date}");

        $this->info('Usuario profesor creado exitosamente con suscripción activa.');
        $this->info('Credenciales de acceso:');
        $this->info('- Email: jesus@eduai.com');
        $this->info('- Contraseña: password');
        $this->info('- Rol: teacher');

        return 0;
    }
} 