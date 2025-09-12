<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
// use Maatwebsite\Excel\Concerns\FromView;

class ChangeLogExport // implements FromView
{
    public $changeLogs;
    public function __construct($changeLogs)
    {
        $this->changeLogs = $changeLogs;
    }
    public function view(): View
    {
        return view('exports.change_logs', [
            'changeLogs' => $this->changeLogs
        ]);
    }
} 