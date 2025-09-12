<?php

use App\Http\Controllers\AcademicPeriodController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentRiskController;
use App\Http\Controllers\ChangeLogController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de prueba PWA (accesibles sin autenticación)
Route::get('/pwa-test', function () {
    return view('pwa-test');
})->name('pwa.test');

Route::get('/pwa-icons', function () {
    return view('pwa-icons');
})->name('pwa.icons');

Route::get('/pwa-diagnostic', function () {
    return view('pwa-diagnostic');
})->name('pwa.diagnostic');

Route::get('/generate-all-icons', function () {
    return view('generate-all-icons');
})->name('pwa.generate-icons');

// Ruta de prueba CSRF
Route::post('/test-csrf', function () {
    return response()->json(['success' => true, 'message' => 'CSRF token válido']);
})->name('test.csrf');

// Sistema de suscripciones (debe estar fuera del middleware de subscription)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('subscription')->group(function () {
        Route::get('/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
        Route::get('/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
        Route::post('/process-payment', [SubscriptionController::class, 'processPayment'])->name('subscription.process-payment');
        Route::get('/success/{subscription}', [SubscriptionController::class, 'success'])->name('subscription.success');
        Route::get('/my-subscription', [SubscriptionController::class, 'mySubscription'])->name('subscription.my-subscription');
        Route::get('/admin', [SubscriptionController::class, 'adminIndex'])->name('subscription.admin');
    });
    // Nueva ruta para generar facturas desde suscripciones
    Route::post('/invoices/generate-from-subscriptions', [\App\Http\Controllers\InvoiceController::class, 'generateFromSubscriptions'])->name('invoices.generateFromSubscriptions');
    
    // Facturación (solo admin) - movido fuera del middleware CheckSubscription
    Route::resource('invoices', InvoiceController::class);
    Route::resource('payments', PaymentController::class);

    // Ruta para notificaciones globales de facturas (solo admin)
    Route::get('/admin/global-invoice-notifications', [\App\Http\Controllers\InvoiceController::class, 'globalInvoiceNotifications']);
});

Route::middleware(['auth', 'verified', \App\Http\Middleware\CheckSubscription::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Rutas del sistema de asistencias
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/justify', [AttendanceController::class, 'justify'])->name('attendance.justify');
        Route::get('/export', [AttendanceController::class, 'export'])->name('attendance.export');
        Route::post('/import', [AttendanceController::class, 'import'])->name('attendance.import');
    });

    // Rutas del sistema de calificaciones
    Route::prefix('grades')->group(function () {
        Route::get('/', [GradeController::class, 'index'])->name('grades.index');
        Route::post('/', [GradeController::class, 'store'])->name('grades.store');
        Route::post('/rubric', [GradeController::class, 'storeRubric'])->name('grades.rubric.store');
        Route::get('/{id}', [GradeController::class, 'show'])->name('grades.show');
        Route::put('/{id}', [GradeController::class, 'update'])->name('grades.update');
        Route::delete('/{id}', [GradeController::class, 'destroy'])->name('grades.destroy');
        Route::get('/export', [\App\Http\Controllers\GradeController::class, 'export']);
        Route::post('/import', [\App\Http\Controllers\GradeController::class, 'import']);
    });

    // Rutas del sistema de horarios
    Route::prefix('schedule')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('schedule.index');
        Route::post('/', [ScheduleController::class, 'store'])->name('schedule.store');
    });

    // Rutas del sistema de alertas
    Route::prefix('alerts')->group(function () {
        Route::get('/', [AlertController::class, 'index'])->name('alerts.index');
        Route::post('/', [AlertController::class, 'store'])->name('alerts.store');
    });

    // Rutas de eventos
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('events.index');
        Route::post('/', [EventController::class, 'store'])->name('events.store');
    });

    // Rutas de gestión de estudiantes
    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('students.index');
        Route::post('/', [StudentController::class, 'store'])->name('students.store');
        Route::get('/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::put('/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/{id}', [\App\Http\Controllers\StudentController::class, 'destroy']);
        Route::get('/export', [\App\Http\Controllers\StudentController::class, 'export']);
        Route::post('/import', [\App\Http\Controllers\StudentController::class, 'import']);
    });

    // Rutas de gestión de docentes
    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'index'])->name('teachers.index');
        Route::post('/', [TeacherController::class, 'store'])->name('teachers.store');
        Route::get('/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
        Route::put('/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    });

    // Rutas de configuración de periodos académicos
    Route::prefix('academic-periods')->group(function () {
        Route::get('/', [AcademicPeriodController::class, 'index'])->name('academic-periods.index');
        Route::post('/', [AcademicPeriodController::class, 'store'])->name('academic-periods.store');
    });

    Route::get('/risk-analysis', [StudentRiskController::class, 'index'])->name('risk.analysis');

    // Rutas del historial de cambios (Bitácora)
    Route::prefix('change-log')->group(function () {
        Route::get('/', [ChangeLogController::class, 'index'])->name('change-log.index');
        Route::get('/{id}', [ChangeLogController::class, 'show'])->name('change-log.show');
        Route::get('/export/excel', [ChangeLogController::class, 'export'])->name('change-log.export');
        Route::post('/{id}/comment', [ChangeLogController::class, 'addComment'])->name('change-log.comment');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/notifications/mark-as-read', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.markAsRead');
});

require __DIR__.'/auth.php';
