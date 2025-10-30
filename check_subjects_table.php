<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=========================================\n";
echo "INVESTIGANDO TABLA SUBJECTS\n";
echo "=========================================\n\n";

// 1. Ver estructura de la tabla
echo "📋 ESTRUCTURA DE LA TABLA:\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('subjects');
print_r($columns);

echo "\n\n📚 DATOS DE MATERIA ID 1:\n";
$subject = \App\Models\Subject::find(1);
if ($subject) {
    print_r($subject->toArray());
} else {
    echo "❌ No existe materia con ID 1\n";
}

echo "\n\n📊 TODAS LAS MATERIAS (5 primeras):\n";
$subjects = \App\Models\Subject::take(5)->get();
foreach ($subjects as $s) {
    echo "ID: {$s->id} - Nombre: '{$s->nombre}' - Clave: '{$s->clave}'\n";
}

echo "\n\n🔍 PROBANDO QUERY DIRECTO:\n";
$result = \Illuminate\Support\Facades\DB::table('subjects')->where('id', 1)->first();
if ($result) {
    echo "Resultado directo de DB:\n";
    print_r($result);
}

echo "\n\n🧪 PROBANDO ACTUALIZACIÓN:\n";
try {
    \App\Models\Subject::where('id', 1)->update(['nombre' => 'Matemáticas TEST']);
    echo "✅ Actualización exitosa\n";
    
    $verify = \App\Models\Subject::find(1);
    echo "Verificación: '{$verify->nombre}'\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=========================================\n";







