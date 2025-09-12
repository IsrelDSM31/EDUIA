<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $plans = [
            [
                'id' => 'monthly',
                'name' => 'Plan Mensual',
                'price' => 29.99,
                'period' => '1 mes',
                'features' => [
                    'Acceso completo al sistema',
                    'Soporte técnico',
                    'Actualizaciones automáticas',
                    'Respaldo de datos'
                ]
            ],
            [
                'id' => 'yearly',
                'name' => 'Plan Anual',
                'price' => 299.99,
                'period' => '12 meses',
                'features' => [
                    'Acceso completo al sistema',
                    'Soporte técnico prioritario',
                    'Actualizaciones automáticas',
                    'Respaldo de datos',
                    '2 meses gratis',
                    'Descuento del 17%'
                ]
            ]
        ];

        return Inertia::render('Subscription/Plans', [
            'plans' => $plans
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan_type' => 'required|in:monthly,yearly',
        ]);

        $planType = $request->plan_type;
        $plans = [
            'monthly' => ['price' => 29.99, 'days' => 30],
            'yearly' => ['price' => 299.99, 'days' => 365]
        ];

        $plan = $plans[$planType];

        return Inertia::render('Subscription/Checkout', [
            'plan' => [
                'type' => $planType,
                'price' => $plan['price'],
                'days' => $plan['days']
            ]
        ]);
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'plan_type' => 'required|in:monthly,yearly',
            'payment_method' => 'required|string',
            'card_number' => 'required|string|min:13|max:19',
            'expiry_date' => 'required|string',
            'cvv' => 'required|string|min:3|max:4',
        ]);

        $plans = [
            'monthly' => ['price' => 29.99, 'days' => 30],
            'yearly' => ['price' => 299.99, 'days' => 365]
        ];

        $plan = $plans[$request->plan_type];
        $user = auth()->user();

        // Simular procesamiento de pago (en producción usarías Stripe, PayPal, etc.)
        $transactionId = 'TXN_' . time() . '_' . $user->id;

        // Crear la suscripción
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_type' => $request->plan_type,
            'amount' => $plan['price'],
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addDays($plan['days']),
            'payment_method' => $request->payment_method,
            'transaction_id' => $transactionId,
            'notes' => 'Pago procesado exitosamente'
        ]);

        return redirect()->route('subscription.success', $subscription->id)
            ->with('success', '¡Suscripción activada correctamente!');
    }

    public function success(Subscription $subscription)
    {
        return Inertia::render('Subscription/Success', [
            'subscription' => [
                'id' => $subscription->id,
                'plan_type' => $subscription->plan_type,
                'amount' => $subscription->amount,
                'start_date' => $subscription->start_date->format('d/m/Y'),
                'end_date' => $subscription->end_date->format('d/m/Y'),
                'days_remaining' => $subscription->daysRemaining()
            ]
        ]);
    }

    public function mySubscription()
    {
        $user = auth()->user();
        $subscription = $user->activeSubscription();

        return Inertia::render('Subscription/MySubscription', [
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'plan_type' => $subscription->plan_type,
                'amount' => $subscription->amount,
                'status' => $subscription->status,
                'start_date' => $subscription->start_date->format('d/m/Y'),
                'end_date' => $subscription->end_date->format('d/m/Y'),
                'days_remaining' => $subscription->daysRemaining(),
                'is_active' => $subscription->isActive()
            ] : null
        ]);
    }

    // Método para admin - ver todas las suscripciones
    public function adminIndex()
    {
        // Verificar si es admin
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $subscriptions = Subscription::with('user')
            ->orderByDesc('created_at')
            ->get()
            ->map(function($sub) {
                return [
                    'id' => $sub->id,
                    'user_name' => $sub->user->name,
                    'user_email' => $sub->user->email,
                    'plan_type' => $sub->plan_type,
                    'amount' => $sub->amount,
                    'status' => $sub->status,
                    'start_date' => $sub->start_date->format('d/m/Y'),
                    'end_date' => $sub->end_date->format('d/m/Y'),
                    'days_remaining' => $sub->daysRemaining(),
                    'payment_method' => $sub->payment_method,
                    'transaction_id' => $sub->transaction_id,
                    'created_at' => $sub->created_at->format('d/m/Y H:i')
                ];
            });

        return Inertia::render('Subscription/AdminIndex', [
            'subscriptions' => $subscriptions
        ]);
    }
}
