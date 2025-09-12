<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Invoice;
use PDF;

class InvoiceGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function build()
    {
        $pdf = PDF::loadView('exports.invoice_pdf', ['invoice' => $this->invoice]);
        return $this->subject('Nueva factura generada')
            ->view('emails.invoice_generated')
            ->with(['invoice' => $this->invoice])
            ->attachData($pdf->output(), 'Factura-'.$this->invoice->number.'.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
} 