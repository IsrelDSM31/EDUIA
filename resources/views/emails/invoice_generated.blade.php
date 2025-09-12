<p>Hola {{ $invoice->user->name ?? '' }},</p>
<p>Se ha generado una nueva factura a tu nombre.</p>
<ul>
    <li><b>Número:</b> {{ $invoice->number }}</li>
    <li><b>Monto:</b> ${{ number_format($invoice->amount, 2) }}</li>
    <li><b>Vencimiento:</b> {{ $invoice->due_date }}</li>
    <li><b>Estado:</b> {{ $invoice->status }}</li>
</ul>
<p>Adjunto encontrarás el PDF de tu factura.</p>
<p>Gracias por tu preferencia.</p> 