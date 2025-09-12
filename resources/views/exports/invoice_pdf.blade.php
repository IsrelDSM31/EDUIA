<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura #{{ $invoice->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { font-size: 22px; font-weight: bold; margin-bottom: 20px; }
        .section { margin-bottom: 15px; }
        .label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
    </style>
</head>
<body>
    <div class="header">Factura #{{ $invoice->number }}</div>
    <div class="section">
        <span class="label">Usuario:</span> {{ $invoice->user->name ?? '' }}<br>
        <span class="label">Email:</span> {{ $invoice->user->email ?? '' }}<br>
    </div>
    <div class="section">
        <span class="label">Monto:</span> ${{ number_format($invoice->amount, 2) }}<br>
        <span class="label">Vencimiento:</span> {{ $invoice->due_date }}<br>
        <span class="label">Estado:</span> {{ $invoice->status }}<br>
    </div>
    <div class="section">
        <span class="label">Descripción:</span> {{ $invoice->description }}
    </div>
</body>
</html> 