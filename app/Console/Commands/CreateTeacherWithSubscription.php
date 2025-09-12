<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CreateTeacherWithSubscription extends Command
{
    protected $signature = 'teacher:create-with-subscription {name} {email} {password}';
    protected $description = 'Crea un maestro con usuario y suscripción activa, sin dañar datos existentes.';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Verificar si ya existe el usuario
        if (User::where('email', $email)->exists()) {
            $this->error('Ya existe un usuario con ese email.');
            return 1;
        }

        // Crear usuario
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'teacher',
        ]);

        // Crear maestro
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'professional_license' => 'DEMO-0001',
            'specialization' => 'General',
            'education_level' => 'Licenciatura',
            'experience_years' => 1,
            'availability' => [],
        ]);

        // Crear suscripción activa (1 año)
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => 'anual',
            'amount' => 0,
            'status' => 'active',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addYear(),
            'payment_method' => 'demo',
            'transaction_id' => null,
            'notes' => 'Suscripción de prueba creada por comando',
        ]);

        $this->info("Maestro registrado con éxito. Usuario: $email, Contraseña: $password");
        return 0;
    }
} 