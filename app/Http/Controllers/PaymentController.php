<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'method' => 'nullable|string',
            'reference' => 'nullable|string',
            'description' => 'nullable|string',
            'invoice_id' => 'required|exists:invoices,id',
        ]);
        $payment = Payment::create($request->only(['invoice_id','amount','payment_date','method','reference','description']));
        // Marcar factura como pagada si el total de pagos >= monto
        $invoice = Invoice::find($request->invoice_id);
        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->amount) {
            $invoice->status = 'paid';
            $invoice->save();
        }
        return Redirect::route('invoices.show', $invoice->id)->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
