<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\GradeApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\AlertApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\RiskAnalysisApiController;
use App\Http\Controllers\Api\TeacherApiController;
use App\Http\Controllers\Api\GradeManagementApiController;
use App\Http\Controllers\Api\AttendanceManagementApiController;
use App\Http\Controllers\Api\ChatbotApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\GoogleClassroomApiController;
use App\Http\Controllers\Api\GamificationApiController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\MessagingController;

// Auth API
Route::post('/auth/login', [App\Http\Controllers\Api\AuthApiController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [App\Http\Controllers\Api\AuthApiController::class, 'logout']);
    Route::get('/auth/user', [App\Http\Controllers\Api\AuthApiController::class, 'user']);
    
    // Profile API
    Route::get('/profile', [ProfileApiController::class, 'show']);
    Route::put('/profile', [ProfileApiController::class, 'update']);
    Route::post('/profile/avatar', [ProfileApiController::class, 'uploadAvatar']);
});

// Dashboard API
Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);
Route::get('/dashboard/recent-activities', [DashboardApiController::class, 'recentActivities']);

// Users API
Route::get('/users', [UserApiController::class, 'index']);
Route::get('/users/{id}', [UserApiController::class, 'show']);

// Students API
Route::get('/students', [StudentApiController::class, 'index']);
Route::get('/students/{id}', [StudentApiController::class, 'show']);
Route::post('/students', [StudentApiController::class, 'store']);
Route::put('/students/{id}', [StudentApiController::class, 'update']);
Route::delete('/students/{id}', [StudentApiController::class, 'destroy']);
Route::get('/students/{id}/grades', [StudentApiController::class, 'grades']);
Route::get('/students/{id}/attendance', [StudentApiController::class, 'attendance']);
Route::get('/students/{id}/alerts', [StudentApiController::class, 'alerts']);
Route::get('/students/{id}/risk-analysis', [StudentApiController::class, 'riskAnalysis']);

// Teachers API
Route::get('/teachers', [TeacherApiController::class, 'index']);
Route::get('/teachers/{id}', [TeacherApiController::class, 'show']);
Route::post('/teachers', [TeacherApiController::class, 'store']);
Route::put('/teachers/{id}', [TeacherApiController::class, 'update']);
Route::delete('/teachers/{id}', [TeacherApiController::class, 'destroy']);
Route::get('/teachers/{id}/students', [TeacherApiController::class, 'students']);
Route::get('/teachers/{id}/courses', [TeacherApiController::class, 'courses']);

// Grades API
Route::get('/grades', [GradeApiController::class, 'index']);
Route::get('/grades/{id}', [GradeApiController::class, 'show']);
Route::post('/grades', [GradeApiController::class, 'store']);
Route::put('/grades/{id}', [GradeApiController::class, 'update']);
Route::delete('/grades/{id}', [GradeApiController::class, 'destroy']);

// Attendance API
Route::get('/attendance', [AttendanceApiController::class, 'index']);
Route::get('/attendance/{id}', [AttendanceApiController::class, 'show']);
Route::post('/attendance', [AttendanceApiController::class, 'store']);
Route::post('/attendance/bulk', [AttendanceApiController::class, 'bulkStore']);
Route::post('/attendance/justify', [AttendanceApiController::class, 'justify']);
Route::put('/attendance/{id}', [AttendanceApiController::class, 'update']);
Route::delete('/attendance/{id}', [AttendanceApiController::class, 'destroy']);
Route::get('/attendance/statistics', [AttendanceApiController::class, 'statistics']);

// Alerts API
Route::get('/alerts', [AlertApiController::class, 'index']);
Route::get('/alerts/{id}', [AlertApiController::class, 'show']);
Route::get('/alerts/unread', [AlertApiController::class, 'unread']);
Route::patch('/alerts/{id}/read', [AlertApiController::class, 'markAsRead']);
Route::post('/alerts', [AlertApiController::class, 'store']);
Route::delete('/alerts/{id}', [AlertApiController::class, 'destroy']);

// Risk Analysis API
Route::get('/risk-analysis', [RiskAnalysisApiController::class, 'index']);
Route::get('/risk-analysis/statistics', [RiskAnalysisApiController::class, 'statistics']);
Route::post('/risk-analysis/predict', [RiskAnalysisApiController::class, 'predict']);

// Grade Management API (Sistema completo de calificaciones)
Route::get('/grade-management/students', [GradeManagementApiController::class, 'students']);
Route::get('/grade-management/students/{id}/grades', [GradeManagementApiController::class, 'studentGrades']);
Route::post('/grade-management/students/{id}/grades', [GradeManagementApiController::class, 'storeOrUpdateGrade']);
Route::delete('/grade-management/students/{studentId}/subjects/{subjectId}', [GradeManagementApiController::class, 'deleteGrade']);

// Attendance Management API (Sistema completo de asistencias)
Route::get('/attendance-management/students/{id}/attendance', [AttendanceManagementApiController::class, 'studentAttendance']);
Route::post('/attendance-management/students/{id}/attendance', [AttendanceManagementApiController::class, 'storeOrUpdate']);
Route::delete('/attendance-management/students/{studentId}/attendance/{attendanceId}', [AttendanceManagementApiController::class, 'destroy']);

// Chatbot API
Route::post('/chatbot/message', [ChatbotApiController::class, 'sendMessage']);
Route::get('/chatbot/history', [ChatbotApiController::class, 'getConversationHistory']);

// Google Classroom API
Route::prefix('google-classroom')->middleware('auth:sanctum')->group(function () {
    Route::get('/auth-config', [GoogleClassroomApiController::class, 'getAuthConfig']);
    Route::post('/exchange-code', [GoogleClassroomApiController::class, 'exchangeCode']);
    Route::get('/courses', [GoogleClassroomApiController::class, 'getCourses']);
    Route::get('/courses/{courseId}/students', [GoogleClassroomApiController::class, 'getCourseStudents']);
    Route::get('/courses/{courseId}/coursework', [GoogleClassroomApiController::class, 'getCourseWork']);
    Route::post('/courses/{courseId}/sync-students', [GoogleClassroomApiController::class, 'syncCourseStudents']);
    Route::post('/disconnect', [GoogleClassroomApiController::class, 'disconnect']);
    Route::get('/status', [GoogleClassroomApiController::class, 'getConnectionStatus']);
});

// Gamification API
Route::prefix('gamification')->middleware('auth:sanctum')->group(function () {
    Route::get('/students/{studentId}/points', [GamificationApiController::class, 'getStudentPoints']);
    Route::get('/students/{studentId}/achievements', [GamificationApiController::class, 'getStudentAchievements']);
    Route::get('/students/{studentId}/rank', [GamificationApiController::class, 'getStudentRank']);
    Route::get('/students/{studentId}/history', [GamificationApiController::class, 'getPointsHistory']);
    Route::get('/achievements', [GamificationApiController::class, 'getAllAchievements']);
    Route::get('/ranking', [GamificationApiController::class, 'getGlobalRanking']);
    Route::post('/points/add', [GamificationApiController::class, 'addPoints']);
});

// Calendar API
Route::prefix('calendar')->middleware('auth:sanctum')->group(function () {
    Route::get('/events', [CalendarController::class, 'index']);
    Route::get('/events/upcoming-exams', [CalendarController::class, 'upcomingExams']);
    Route::post('/events', [CalendarController::class, 'store']);
    Route::put('/events/{id}', [CalendarController::class, 'update']);
    Route::delete('/events/{id}', [CalendarController::class, 'destroy']);
    Route::get('/sync/settings', [CalendarController::class, 'getSyncSettings']);
    Route::post('/sync/google', [CalendarController::class, 'syncGoogle']);
    Route::post('/sync/outlook', [CalendarController::class, 'syncOutlook']);
});

// Messaging API
Route::prefix('messages')->middleware('auth:sanctum')->group(function () {
    Route::get('/conversations', [MessagingController::class, 'getConversations']);
    Route::post('/conversations', [MessagingController::class, 'createConversation']);
    Route::get('/conversations/{id}', [MessagingController::class, 'getMessages']);
    Route::post('/conversations/{id}/messages', [MessagingController::class, 'sendMessage']);
    Route::post('/conversations/{id}/read', [MessagingController::class, 'markAsRead']);
    Route::get('/channels', [MessagingController::class, 'getChannels']);
    Route::get('/unread-count', [MessagingController::class, 'getUnreadCount']);
    Route::get('/users/search', [MessagingController::class, 'searchUsers']);
}); 