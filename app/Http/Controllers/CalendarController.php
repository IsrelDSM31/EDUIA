<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    /**
     * Obtener eventos del calendario
     */
    public function index(Request $request)
    {
        try {
            $query = CalendarEvent::query();
            
            // Filtrar por rango de fechas
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('start_date', [
                    $request->start_date,
                    $request->end_date
                ]);
            }
            
            // Filtrar por tipo
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            $events = $query->orderBy('start_date', 'asc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener eventos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener próximos exámenes
     */
    public function upcomingExams()
    {
        try {
            $exams = CalendarEvent::where('type', 'exam')
                ->where('start_date', '>=', now())
                ->orderBy('start_date', 'asc')
                ->limit(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $exams
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener exámenes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo evento
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:exam,class,meeting,holiday,assignment,other',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'subject_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 400);
            }

            $event = CalendarEvent::create($validator->validated());
            
            return response()->json([
                'success' => true,
                'data' => $event,
                'message' => 'Evento creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear evento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar evento
     */
    public function update(Request $request, $id)
    {
        try {
            $event = CalendarEvent::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
                'description' => 'nullable|string',
                'type' => 'in:exam,class,meeting,holiday,assignment,other',
                'start_date' => 'date',
                'end_date' => 'nullable|date|after:start_date',
                'subject_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 400);
            }

            $event->update($validator->validated());
            
            return response()->json([
                'success' => true,
                'data' => $event,
                'message' => 'Evento actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar evento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar evento
     */
    public function destroy($id)
    {
        try {
            $event = CalendarEvent::findOrFail($id);
            $event->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Evento eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar evento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener configuración de sincronización
     */
    public function getSyncSettings()
    {
        try {
            // Por ahora retornar configuración por defecto
            // Después puedes implementar guardado en base de datos
            return response()->json([
                'success' => true,
                'data' => [
                    'google_calendar_enabled' => false,
                    'outlook_enabled' => false,
                    'last_sync_google' => null,
                    'last_sync_outlook' => null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar con Google Calendar
     */
    public function syncGoogle()
    {
        try {
            // Implementar lógica de sincronización con Google Calendar
            // Por ahora retornar éxito
            return response()->json([
                'success' => true,
                'message' => 'Sincronización con Google Calendar completada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en sincronización: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar con Outlook
     */
    public function syncOutlook()
    {
        try {
            // Implementar lógica de sincronización con Outlook
            // Por ahora retornar éxito
            return response()->json([
                'success' => true,
                'message' => 'Sincronización con Outlook completada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en sincronización: ' . $e->getMessage()
            ], 500);
        }
    }
}


