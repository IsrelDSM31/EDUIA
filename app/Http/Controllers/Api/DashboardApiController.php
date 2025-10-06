<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Alert;
use App\Models\StudentRisk;

class DashboardApiController extends ApiController
{
    public function stats(): JsonResponse
    {
        $stats = [
            'total_students' => Student::count(),
            'total_teachers' => Teacher::count(),
            'total_alerts' => Alert::count(),
            'high_risk_students' => StudentRisk::whereIn('risk_level', ['alto', 'high'])->distinct('student_id')->count(),
        ];

        return $this->successResponse($stats, 'Dashboard stats retrieved successfully');
    }

    public function recentActivities(): JsonResponse
    {
        $activities = [];
        
        return $this->successResponse($activities, 'Recent activities retrieved successfully');
    }
}


