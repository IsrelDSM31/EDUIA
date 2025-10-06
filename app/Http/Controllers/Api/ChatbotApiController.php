<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\StudentRisk;
use App\Models\Alert;
use App\Models\Attendance;
use App\Models\Grade;

class ChatbotApiController extends ApiController
{
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'nullable|string',
        ]);

        $message = strtolower(trim($validated['message']));
        $response = $this->processMessage($message);

        return $this->successResponse([
            'user_message' => $validated['message'],
            'bot_response' => $response,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'conversation_id' => $validated['conversation_id'] ?? uniqid('conv_'),
        ], 'Message processed successfully');
    }

    private function processMessage($message)
    {
        // Saludos
        if (preg_match('/(hola|hello|hi|buenos|buenas)/i', $message)) {
            return "¡Hola! 👋 Soy el asistente inteligente de EduIA.\n\n" .
                   "Puedo ayudarte con:\n" .
                   "📊 Estadísticas de estudiantes\n" .
                   "🎓 Información de calificaciones\n" .
                   "📅 Datos de asistencias\n" .
                   "🛡️ Análisis de riesgo\n" .
                   "🔔 Alertas del sistema\n\n" .
                   "¿Qué necesitas saber?";
        }

        // Estadísticas generales
        if (preg_match('/(cuántos|cuantos|total|estadísticas|estadisticas)/i', $message)) {
            $totalStudents = Student::count();
            $totalTeachers = Teacher::count();
            $totalAlerts = Alert::count();
            $highRisk = StudentRisk::whereIn('risk_level', ['alto', 'high'])->distinct('student_id')->count();
            $mediumRisk = StudentRisk::whereIn('risk_level', ['medio', 'medium'])->distinct('student_id')->count();
            $lowRisk = StudentRisk::whereIn('risk_level', ['bajo', 'low'])->distinct('student_id')->count();

            return "📊 **Estadísticas del Sistema:**\n\n" .
                   "👥 Total de Estudiantes: {$totalStudents}\n" .
                   "👨‍🏫 Total de Profesores: {$totalTeachers}\n" .
                   "🔔 Alertas Activas: {$totalAlerts}\n\n" .
                   "**Análisis de Riesgo:**\n" .
                   "🔴 Riesgo Alto: {$highRisk} estudiantes\n" .
                   "🟡 Riesgo Medio: {$mediumRisk} estudiantes\n" .
                   "🟢 Riesgo Bajo: {$lowRisk} estudiantes";
        }

        // Estudiantes en riesgo
        if (preg_match('/(riesgo|peligro|atención|urgente)/i', $message)) {
            $highRiskStudents = StudentRisk::whereIn('risk_level', ['alto', 'high'])
                ->with('student')
                ->take(5)
                ->get();

            $response = "🛡️ **Estudiantes en Riesgo Alto:**\n\n";
            
            if ($highRiskStudents->isEmpty()) {
                return "✅ ¡Excelente! No hay estudiantes en riesgo alto.";
            }

            foreach ($highRiskStudents as $risk) {
                if ($risk->student) {
                    $name = trim($risk->student->nombre . ' ' . $risk->student->apellido_paterno);
                    $response .= "🔴 {$name} ({$risk->student->matricula})\n";
                }
            }

            $total = StudentRisk::whereIn('risk_level', ['alto', 'high'])->count();
            $response .= "\n**Total:** {$total} estudiantes requieren atención inmediata.";
            
            return $response;
        }

        // Asistencias
        if (preg_match('/(asistencia|ausencia|falta)/i', $message)) {
            $totalAttendance = Attendance::count();
            $present = Attendance::whereIn('status', ['present', 'presente'])->count();
            $absent = Attendance::whereIn('status', ['absent', 'ausente'])->count();
            $late = Attendance::whereIn('status', ['late', 'tarde', 'tardanza'])->count();
            
            $rate = $totalAttendance > 0 ? round(($present / $totalAttendance) * 100, 1) : 0;

            return "📅 **Reporte de Asistencias:**\n\n" .
                   "Total de registros: {$totalAttendance}\n" .
                   "✅ Presentes: {$present}\n" .
                   "❌ Ausentes: {$absent}\n" .
                   "⏰ Tardanzas: {$late}\n\n" .
                   "**Tasa de asistencia general:** {$rate}%";
        }

        // Calificaciones
        if (preg_match('/(calificación|calificacion|nota|promedio|rendimiento)/i', $message)) {
            $totalGrades = Grade::count();
            $avgGrade = Grade::avg('promedio_final');
            $approved = Grade::where('promedio_final', '>=', 7)->count();
            $failed = Grade::where('promedio_final', '<', 7)->count();
            
            $avgGrade = round($avgGrade, 2);

            return "📊 **Reporte Académico:**\n\n" .
                   "Total de calificaciones: {$totalGrades}\n" .
                   "📈 Promedio General: {$avgGrade}\n\n" .
                   "✅ Aprobados: {$approved}\n" .
                   "❌ Reprobados: {$failed}";
        }

        // Alertas
        if (preg_match('/(alerta|notificación|notificacion)/i', $message)) {
            $totalAlerts = Alert::count();
            $highUrgency = Alert::where('urgency', 'high')->count();
            $mediumUrgency = Alert::where('urgency', 'medium')->count();
            $lowUrgency = Alert::where('urgency', 'low')->count();

            return "🔔 **Alertas del Sistema:**\n\n" .
                   "Total: {$totalAlerts} alertas\n" .
                   "🔴 Urgencia Alta: {$highUrgency}\n" .
                   "🟡 Urgencia Media: {$mediumUrgency}\n" .
                   "🟢 Urgencia Baja: {$lowUrgency}";
        }

        // Buscar estudiante específico
        if (preg_match('/(estudiante|alumno|buscar)\s+(\w+)/i', $message, $matches)) {
            $searchTerm = $matches[2];
            $student = Student::where('nombre', 'like', "%{$searchTerm}%")
                ->orWhere('apellido_paterno', 'like', "%{$searchTerm}%")
                ->orWhere('apellido_materno', 'like', "%{$searchTerm}%")
                ->orWhere('matricula', $searchTerm)
                ->with('group')
                ->first();

            if ($student) {
                $name = trim($student->nombre . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno);
                $risk = StudentRisk::where('student_id', $student->id)->first();
                $riskLevel = $risk ? $risk->risk_level : 'sin análisis';
                
                return "👤 **{$name}**\n\n" .
                       "📋 Matrícula: {$student->matricula}\n" .
                       "🏫 Grupo: " . ($student->group->name ?? 'Sin grupo') . "\n" .
                       "🛡️ Nivel de Riesgo: {$riskLevel}\n\n" .
                       "¿Quieres saber más sobre este estudiante?";
            } else {
                return "❌ No encontré ningún estudiante con ese nombre o matrícula.\n\n" .
                       "Intenta con otro nombre o matrícula.";
            }
        }

        // Ayuda
        if (preg_match('/(ayuda|help|comandos|qué puedes|que puedes)/i', $message)) {
            return "🤖 **Comandos Disponibles:**\n\n" .
                   "📊 'estadísticas' o 'total' - Ver datos generales\n" .
                   "🛡️ 'riesgo' - Ver estudiantes en riesgo\n" .
                   "📅 'asistencia' - Reporte de asistencias\n" .
                   "📚 'calificaciones' - Reporte académico\n" .
                   "🔔 'alertas' - Ver alertas del sistema\n" .
                   "👤 'estudiante [nombre]' - Buscar estudiante\n\n" .
                   "Escribe cualquier pregunta y te ayudaré.";
        }

        // Respuesta por defecto con IA básica
        return "🤖 Entiendo que preguntas sobre: \"" . $message . "\"\n\n" .
               "💡 Puedo ayudarte con:\n" .
               "• Estadísticas del sistema\n" .
               "• Información de estudiantes\n" .
               "• Análisis de riesgo\n" .
               "• Calificaciones y asistencias\n\n" .
               "Escribe 'ayuda' para ver todos los comandos disponibles.";
    }

    public function getConversationHistory(Request $request): JsonResponse
    {
        // Retornar historial vacío por ahora (se puede implementar guardado en BD)
        return $this->successResponse([], 'Conversation history retrieved successfully');
    }
}



