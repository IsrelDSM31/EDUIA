<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Teacher;

class TeacherApiController extends ApiController
{
    public function index(): JsonResponse
    {
        $teachers = Teacher::with('user')->get()->map(function($teacher) {
            return [
                'id' => $teacher->id,
                'name' => $teacher->user->name ?? $teacher->name ?? 'N/A',
                'email' => $teacher->user->email ?? $teacher->email ?? 'N/A',
                'phone' => $teacher->phone ?? '',
                'subject' => $teacher->subject->name ?? 'N/A',
                'specialty' => $teacher->specialty ?? '',
                'address' => $teacher->address ?? '',
            ];
        });

        return $this->successResponse($teachers, 'Teachers retrieved successfully');
    }

    public function show($id): JsonResponse
    {
        $teacher = Teacher::with('user')->find($id);
        
        if (!$teacher) {
            return $this->errorResponse('Teacher not found', 404);
        }

        $data = [
            'id' => $teacher->id,
            'name' => $teacher->user->name ?? $teacher->name ?? 'N/A',
            'email' => $teacher->user->email ?? $teacher->email ?? 'N/A',
            'phone' => $teacher->phone ?? '',
            'subject' => $teacher->subject->name ?? 'N/A',
            'specialty' => $teacher->specialty ?? '',
            'address' => $teacher->address ?? '',
        ];

        return $this->successResponse($data, 'Teacher retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        // Implementar creación de profesor
        return $this->successResponse([], 'Teacher created successfully', 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        // Implementar actualización de profesor
        return $this->successResponse([], 'Teacher updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        // Implementar eliminación de profesor
        return $this->successResponse([], 'Teacher deleted successfully');
    }

    public function students($id): JsonResponse
    {
        return $this->successResponse([], 'Teacher students retrieved successfully');
    }

    public function courses($id): JsonResponse
    {
        return $this->successResponse([], 'Teacher courses retrieved successfully');
    }
}
