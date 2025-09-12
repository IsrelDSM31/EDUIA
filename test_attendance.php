<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing complete attendance creation with ChangeLog:\n";
try {
    $firstStudent = App\Models\Student::first();
    $firstSubject = App\Models\Subject::first();
    $firstUser = App\Models\User::first();
    
    if ($firstStudent && $firstSubject && $firstUser) {
        // Simular el proceso del controlador
        $attendance = new App\Models\Attendance([
            'student_id' => $firstStudent->id,
            'subject_id' => $firstSubject->id,
            'date' => '2024-01-03',
            'status' => 'present'
        ]);
        $attendance->save();
        
        // Crear el ChangeLog
        $changeLog = new App\Models\ChangeLog([
            'user_id' => $firstUser->id,
            'model_type' => 'App\Models\Attendance',
            'model_id' => $attendance->id,
            'action' => 'create',
            'changes' => [
                'after' => $attendance->toArray(),
            ],
        ]);
        $changeLog->save();
        
        echo "Complete Success: Attendance ID " . $attendance->id . ", ChangeLog ID " . $changeLog->id . "\n";
    } else {
        echo "Missing required data\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} 