import React, { useMemo } from 'react';
import { Card } from '@/Components/UI/Card';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, Title, BarElement } from 'chart.js';
import { Pie, Line, Bar } from 'react-chartjs-2';
import CalendarModule from './CalendarModule';
import CurrentClassModule from './CurrentClassModule';

ChartJS.register(ArcElement, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, BarElement);

export default function MainStats({ stats }) {
    const attendanceData = {
        labels: ['Presente', 'Ausente', 'Retardo', 'Justificado'],
        datasets: [{
            data: stats?.attendance_summary?.map(item => item.count) || [0, 0, 0, 0],
            backgroundColor: ['#4CAF50', '#F44336', '#FFC107', '#2196F3'],
        }],
    };

    const performanceData = {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
        datasets: [{
            label: 'Rendimiento Académico',
            data: stats?.performance_data || [0, 0, 0, 0, 0, 0],
            borderColor: '#2196F3',
            tension: 0.4,
        }],
    };

    // Calcular asistencias globales hoy (corrige comparación de fechas y muestra en consola)
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    const globalAttendanceToday = (() => {
        if (!stats?.students || !stats?.subjects || !stats?.attendances) return 0;
        let count = 0;
        stats.students.forEach(student => {
            // Contar materias a las que asistió hoy (comparando solo año-mes-día)
            const attendedToday = stats.attendances.filter(att => {
                if (att.student_id !== student.id || att.status !== 'present') return false;
                // Normaliza la fecha
                const attDate = (typeof att.date === 'string') ? att.date.split('T')[0] : new Date(att.date).toISOString().split('T')[0];
                return attDate === todayStr;
            }).length;
            console.log(`Alumno ${student.nombre} (${student.id}) asistencias hoy:`, attendedToday);
            // Si tiene al menos una asistencia presente hoy, cuenta como global
            if (attendedToday > 0) {
                count++;
            }
        });
        return count;
    })();

    // 1. Distribución de calificaciones por materia (Barras)
    const gradesBySubject = stats?.grades_by_subject || [
      { subject: 'Trigonometría', promedio: 8.2 },
      { subject: 'Inglés 2', promedio: 7.5 },
      { subject: 'Química 2', promedio: 6.9 },
      { subject: 'LEOYE', promedio: 8.0 },
      { subject: 'Módulo 1', promedio: 7.8 },
    ];
    
    // Colores diferentes para cada materia
    const subjectColors = [
      '#3B82F6', // Azul - Trigonometría
      '#10B981', // Verde - Inglés 2
      '#F59E0B', // Naranja - Química 2
      '#8B5CF6', // Púrpura - LEOYE
      '#EF4444', // Rojo - Módulo 1
    ];
    
    const barData = useMemo(() => ({
      labels: gradesBySubject.map(g => g.subject),
      datasets: [{
        label: 'Promedio',
        data: gradesBySubject.map(g => g.promedio),
        backgroundColor: subjectColors.slice(0, gradesBySubject.length),
        borderColor: subjectColors.slice(0, gradesBySubject.length),
        borderWidth: 1,
      }]
    }), [gradesBySubject]);

    // 2. Porcentaje de alumnos por estado (Pastel)
    const statusCounts = useMemo(() => stats?.grades_status_counts || { Aprobado: 10, Reprobado: 5, 'En riesgo': 3 }, [stats?.grades_status_counts]);
    
    const pieData = useMemo(() => ({
        labels: Object.keys(statusCounts),
        datasets: [{
            data: Object.values(statusCounts),
            backgroundColor: ['#10B981', '#F59E0B', '#EF4444'], // Verde, Naranja, Rojo
        }]
    }), [statusCounts]);

    // 3. Evolución del rendimiento por periodo (Líneas)
    const periods = stats?.periods || ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'];
    const periodAverages = stats?.period_averages || [7.2, 7.5, 7.8, 8.0, 7.9, 8.1];
    const lineData = {
      labels: periods,
      datasets: [{
        label: 'Promedio General',
        data: periodAverages,
        borderColor: '#0EA5E9',
        backgroundColor: '#38BDF8',
        tension: 0.4,
      }]
    };

    return (
        <div className="space-y-6">
            {/* Estadísticas Generales */}
            <div className="stats-container grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card className="p-6 bg-blue-200">
                    <h3 className="text-lg font-semibold text-gray-900">Estudiantes</h3>
                    <p className="mt-2 text-3xl font-bold text-indigo-600">{stats?.total_students || 0}</p>
                    <p className="mt-1 text-sm text-gray-500">Total de estudiantes registrados</p>
                </Card>

                <Card className="p-6 bg-green-200">
                    <h3 className="text-lg font-semibold text-gray-900">Docentes</h3>
                    <p className="mt-2 text-3xl font-bold text-indigo-600">{stats?.total_teachers || 0}</p>
                    <p className="mt-1 text-sm text-gray-500">Total de docentes activos</p>
                </Card>

                <Card className="p-6 bg-yellow-100">
                    <h3 className="text-lg font-semibold text-gray-900">Materias</h3>
                    <p className="mt-2 text-3xl font-bold text-indigo-600">{stats?.total_subjects || 0}</p>
                    <p className="mt-1 text-sm text-gray-500">Total de materias impartidas</p>
                </Card>

                <Card className="p-6 bg-pink-100">
                    <h3 className="text-lg font-semibold text-gray-900">Asistencia Hoy</h3>
                    <p className="mt-2 text-3xl font-bold text-green-600">
                        {globalAttendanceToday}
                    </p>
                    <p className="mt-1 text-sm text-gray-500">Estudiantes presentes</p>
                </Card>
            </div>

            {/* Divisor con imagen */}
            <div className="background-divider"></div>

            {/* Grid de 3 columnas para Calendario, Clases y Próximos Eventos */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {/* Calendario */}
                <CalendarModule />

                {/* Módulo de Clases Actual y Próxima */}
                <CurrentClassModule />
            </div>

            
            {/* Estadísticas Visuales */}
            <div className="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6">
                <Card className="p-6 flex flex-col items-center">
                    <h3 className="text-lg font-semibold mb-4 text-center">Distribución de Calificaciones por Materia</h3>
                    <div className="w-full" style={{ minHeight: 250 }}>
                        <Bar data={barData} options={{ responsive: true, maintainAspectRatio: false }} />
                    </div>
                </Card>
                <Card className="p-6 flex flex-col items-center">
                    <h3 className="text-lg font-semibold mb-4 text-center">Porcentaje de Alumnos por Estado</h3>
                    <div className="w-full" style={{ minHeight: 250 }}>
                        <Pie data={pieData} options={{ responsive: true, maintainAspectRatio: false }} />
                    </div>
                </Card>
                <Card className="p-6 flex flex-col items-center">
                    <h3 className="text-lg font-semibold mb-4 text-center">Evolución del Rendimiento por Periodo</h3>
                    <div className="w-full" style={{ minHeight: 250 }}>
                        <Line data={lineData} options={{ responsive: true, maintainAspectRatio: false }} />
                    </div>
                </Card>
            </div>
        </div>
    );
} 