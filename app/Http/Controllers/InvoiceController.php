<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Mail\InvoiceGenerated;
use Illuminate\Support\Facades\Mail;
use App\Notifications\InvoiceGeneratedNotification;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Verificar si el usuario es admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $invoices = Invoice::with('user')
            ->orderByDesc('created_at')
            ->get()
            ->map(function($inv) {
                return [
                    'id' => $inv->id,
                    'number' => $inv->number,
                    'user_name' => $inv->user ? $inv->user->name : '',
                    'amount' => $inv->amount,
                    'due_date' => $inv->due_date,
                    'status' => $inv->status,
                ];
            });
        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Verificar si el usuario es admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $users = User::all(['id', 'name', 'email']);
        return Inertia::render('Invoices/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verificar si el usuario es admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'number' => 'required|string|unique:invoices,number',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
        ]);
        $invoice = Invoice::create($request->only(['user_id','number','amount','due_date','description']));
        // Notificar al usuario
        $user = User::find($invoice->user_id);
        if ($user) {
            $user->notify(new InvoiceGeneratedNotification($invoice));
        }
        return redirect()->route('invoices.index')->with('success', 'Factura creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Invoice $invoice)
    {
        // Verificar si el usuario es admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $invoice->load('user', 'payments');
        return Inertia::render('Invoices/Show', [
            'invoice' => [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'user_name' => $invoice->user ? $invoice->user->name : '',
                'amount' => $invoice->amount,
                'due_date' => $invoice->due_date,
                'status' => $invoice->status,
                'description' => $invoice->description,
                'pdf_path' => $invoice->pdf_path,
                'payments' => $invoice->payments->map(function($p) {
                    return [
                        'id' => $p->id,
                        'amount' => $p->amount,
                        'payment_date' => $p->payment_date,
                        'method' => $p->method,
                        'reference' => $p->reference,
                        'description' => $p->description,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Invoice $invoice)
    {
        // Solo admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
        $users = \App\Models\User::all(['id', 'name', 'email']);
        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Solo admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'number' => 'required|string|unique:invoices,number,' . $invoice->id,
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'required|string',
        ]);
        $invoice->update($request->only(['user_id','number','amount','due_date','description','status']));
        return redirect()->route('invoices.index')->with('success', 'Factura actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Invoice $invoice)
    {
        // Solo admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
        
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Factura eliminada correctamente.');
    }

    /**
     * Generar facturas a partir de suscripciones activas.
     */
    public function generateFromSubscriptions(Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
        $now = now();
        $count = 0;
        $subs = \App\Models\Subscription::where('status', 'active')
            ->where('end_date', '>', $now)
            ->get();
        foreach ($subs as $sub) {
            // Evitar duplicados: buscar factura para este usuario, monto y mes
            $exists = \App\Models\Invoice::where('user_id', $sub->user_id)
                ->where('amount', $sub->amount)
                ->whereMonth('due_date', $now->month)
                ->whereYear('due_date', $now->year)
                ->exists();
            if (!$exists) {
                $baseNumber = 'SUSC-' . $sub->user_id . '-' . $now->format('Ym');
                $number = $baseNumber;
                $suffix = 2;
                while (\App\Models\Invoice::where('number', $number)->exists()) {
                    $number = $baseNumber . '-' . $suffix;
                    $suffix++;
                }
                $invoice = \App\Models\Invoice::create([
                    'user_id' => $sub->user_id,
                    'number' => $number,
                    'amount' => $sub->amount,
                    'due_date' => $sub->end_date,
                    'description' => 'Factura generada por suscripción',
                    'status' => 'pending',
                ]);
                // Notificar al usuario
                $user = \App\Models\User::find($invoice->user_id);
                if ($user) {
                    $user->notify(new InvoiceGeneratedNotification($invoice));
                }
                // Enviar correo con PDF
                try {
                    Mail::to($invoice->user->email)->send(new InvoiceGenerated($invoice));
                } catch (\Exception $e) {
                    // Loguear error pero continuar
                    \Log::error('Error enviando factura por correo: ' . $e->getMessage());
                }
                $count++;
            }
        }
        return redirect()->route('invoices.index')->with('success', "$count factura(s) generadas a partir de suscripciones.");
    }

    /**
     * Devuelve todas las notificaciones de facturas para el admin (global).
     */
    public function globalInvoiceNotifications(Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            abort(403, 'No tienes permiso para acceder a esta función.');
        }
        $notifications = \DB::table('notifications')
            ->where('type', 'App\\Notifications\\InvoiceGeneratedNotification')
            ->orderByDesc('created_at')
            ->take(50)
            ->get();
        $userIds = $notifications->pluck('notifiable_id')->unique();
        $users = \App\Models\User::whereIn('id', $userIds)->pluck('name', 'id');
        $result = $notifications->map(function($n) use ($users) {
            $data = json_decode($n->data, true);
            return [
                'id' => $n->id,
                'data' => $data,
                'user_name' => $users[$n->notifiable_id] ?? 'Usuario desconocido',
                'created_at' => \Carbon\Carbon::parse($n->created_at)->diffForHumans(),
                'read_at' => $n->read_at,
            ];
        });
        return response()->json(['notifications' => $result]);
    }
}
