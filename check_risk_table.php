<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Columnas de student_risks:\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('student_risks');
print_r($columns);

echo "\n\nPrimer registro:\n";
$risk = \App\Models\StudentRisk::first();
if ($risk) {
    print_r($risk->toArray());
}


