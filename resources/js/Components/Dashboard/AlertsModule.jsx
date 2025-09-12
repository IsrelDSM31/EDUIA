import React, { useState } from 'react';
import { Card } from '@/Components/UI/Card';

export default function AlertsModule({ stats }) {
    const [search, setSearch] = useState('');
    const [urgency, setUrgency] = useState('');

    // ML para estado global de asistencias
    function mlRiskStatus(absentsBySubject) {
        const extraordinaryCount = Object.values(absentsBySubject).filter(a => a > 6).length;
        if (extraordinaryCount >= 3) return 'Baja';
        if (extraordinaryCount === 2) return 'En riesgo';
        if (extraordinaryCount === 1) return 'Extraordinario';
        return 'Completas';
    }

    // Resumen de asistencias por alumno
    const attendanceSummary = (stats?.students || []).map(student => {
        const att = (stats?.attendances || []).filter(a => a.student_id === student.id);
        const absentsBySubject = {};
        let present = 0, absent = 0, late = 0, justified = 0;
        (stats?.subjects || []).forEach(subject => {
            const attForSubject = att.filter(a => a.subject_id === subject.id);
            const abs = attForSubject.filter(a => a.status === 'absent').length;
            absentsBySubject[subject.name] = abs;
        });
        att.forEach(a => {
            if (a.status === 'present') present++;
            if (a.status === 'absent') {
                absent++;
                if (a.justification_type) justified++;
            }
            if (a.status === 'late') late++;
        });
        return {
            nombre: student.nombre + ' ' + student.apellido_paterno,
            present, absent, late, justified,
            estado: mlRiskStatus(absentsBySubject)
        };
    });

    // ML mejorado para alertas de asistencia
    function mlAlertasAsistencia(stats) {
        if (!stats?.students || !stats?.attendances) return [];
        const alerts = [];
        stats.students.forEach(student => {
            const att = stats.attendances.filter(a => a.student_id === student.id);
            if (att.length === 0) return;
            // Ordenar por fecha
            att.sort((a, b) => new Date(a.date) - new Date(b.date));
            // Calcular ausencias consecutivas
            let maxConsec = 0, currentConsec = 0;
            att.forEach(a => {
                if (a.status === 'absent') currentConsec++;
                else currentConsec = 0;
                if (currentConsec > maxConsec) maxConsec = currentConsec;
            });
            // Calcular porcentaje de asistencia
            const total = att.length;
            const presents = att.filter(a => a.status === 'present').length;
            const percent = total > 0 ? (presents / total) * 100 : 100;
            // Patrones de inasistencia: más de 3 ausencias en el último mes
            const lastMonth = new Date();
            lastMonth.setMonth(lastMonth.getMonth() - 1);
            const recentAbs = att.filter(a => a.status === 'absent' && new Date(a.date) >= lastMonth).length;
            // Estado de riesgo
            let estado = null, urgencia = 'media', desc = '';
            if (maxConsec >= 3) {
                estado = 'En riesgo por ausencias consecutivas';
                urgencia = 'alta';
                desc = `El alumno tiene ${maxConsec} ausencias consecutivas.`;
            } else if (percent < 80) {
                estado = 'En riesgo por bajo porcentaje de asistencia';
                urgencia = 'media';
                desc = `El alumno tiene un porcentaje de asistencia bajo (${percent.toFixed(1)}%).`;
            } else if (recentAbs >= 3) {
                estado = 'Patrón de inasistencias detectado';
                urgencia = 'media';
                desc = `El alumno tiene ${recentAbs} ausencias en el último mes.`;
            }
            if (estado) {
                alerts.push({
                    student_name: student.nombre + ' ' + student.apellido_paterno,
                    title: estado,
                    type: 'asistencia',
                    urgency: urgencia,
                    description: desc,
                    status: 'Detectada por ML',
                    id: 'ml-' + student.id + '-' + estado.replace(/\s/g, '-')
                });
            }
        });
        return alerts;
    }

    const mlAlerts = mlAlertasAsistencia(stats);

    const filteredAlerts = mlAlerts.filter(alert =>
            (!search || alert.title.toLowerCase().includes(search.toLowerCase()) || alert.description.toLowerCase().includes(search.toLowerCase())) &&
        (!urgency || (urgency === 'alta' && alert.urgency === 'alta') || (urgency === 'media' && alert.urgency === 'media') || (urgency === 'baja' && alert.urgency === 'baja'))
    );

    // Unir alertas reales y automáticas, pero solo mostrar las de riesgo ML
    const allAlerts = [
        ...mlAlerts,
        ...(stats?.alerts || []).filter(alert =>
            alert.type === 'asistencia' &&
            (alert.title?.toLowerCase().includes('extraordinario') || alert.title?.toLowerCase().includes('riesgo') || alert.title?.toLowerCase().includes('baja'))
        )
    ];

    // SVG de alerta para usar en la tabla
    const AlertIcon = () => (
        <svg className="w-5 h-5 text-red-600 inline mr-1" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24" aria-hidden="true">
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.054 0 1.658-1.14 1.105-2.045l-6.928-11.99c-.526-.91-1.684-.91-2.21 0l-6.928 11.99c-.553.905.051 2.045 1.105 2.045z" />
        </svg>
    );

    // --- ALERTAS DE CALIFICACIONES: EN RIESGO, REPROBADOS Y EXTRAORDINARIOS ---
    function getGradeAlertsDB() {
        if (!stats?.grades) return [];
        const alerts = [];
        stats.grades.forEach(grade => {
            const promedioFinal = parseFloat(grade.promedio_final ?? grade.score ?? 0);
            const estado = (grade.estado || '').toLowerCase();
            let tipo = '', color = '', icon = '';
            if (promedioFinal >= 6 && promedioFinal < 7) {
                tipo = 'En riesgo';
                color = 'text-yellow-700';
                icon = <span className="text-yellow-500 mr-1">&#9888;&#65039;</span>;
            } else if (promedioFinal < 6 || estado === 'reprobado') {
                tipo = 'Reprobado';
                color = 'text-red-700';
                icon = <span className="text-red-500 mr-1">&#128308;</span>;
            } else if (estado.includes('extraordinario')) {
                tipo = 'Extraordinario';
                color = 'text-pink-700';
                icon = <span className="text-pink-500 mr-1">&#128144;</span>;
            }
            if (tipo) {
                alerts.push({
                    id: grade.id,
                    matricula: grade.student_matricula || '',
                    nombre: grade.student_name,
                    subject: grade.subject_name,
                    promedioFinal: promedioFinal.toFixed(2),
                    tipo,
                    color,
                    icon
                });
            }
        });
        return alerts;
    }
    const gradeAlerts = getGradeAlertsDB();

    return (
        <div className="space-y-6">
            <Card className="p-6">
                <h3 className="text-lg font-semibold mb-4">Alertas de Asistencia Detectadas por ML</h3>
                <div className="flex flex-col md:flex-row gap-4 mb-4">
                    <input
                        type="text"
                        className="border rounded px-3 py-2 w-full md:w-1/3"
                        placeholder="Buscar por título o descripción..."
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                    />
                    <select
                        className="border rounded px-3 py-2 w-full md:w-1/4"
                        value={urgency}
                        onChange={e => setUrgency(e.target.value)}
                    >
                        <option value="">Todas las urgencias</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Urgencia</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {filteredAlerts.length > 0 ? (
                                filteredAlerts.map(alert => (
                                    <tr key={alert.id} className={alert.urgency === 'alta' ? 'bg-red-100 border-l-4 border-red-400' : ''}>
                                        <td className="px-6 py-4 whitespace-nowrap">{alert.student_name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">{alert.title}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">{alert.type}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            {alert.urgency === 'alta' ? (
                                                <span className="flex items-center">
                                                    <AlertIcon />
                                                    <span className="ml-1">alta</span>
                                                </span>
                                            ) : (
                                                alert.urgency
                                            )}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">{alert.description}</td>
                                        <td className="px-6 py-4 whitespace-nowrap">{alert.status}</td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="6" className="px-6 py-4 text-center text-gray-500">No hay alertas de riesgo detectadas.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </Card>
            {/* Tabla de alertas de calificaciones */}
            <Card className="p-6 mt-6">
                <h3 className="text-lg font-semibold mb-4">Alertas de Calificaciones (Riesgo, Reprobados y Extraordinarios)</h3>
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matrícula</th>
                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materia</th>
                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promedio</th>
                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de alerta</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {gradeAlerts.length > 0 ? gradeAlerts.map(alert => (
                                <tr key={alert.id}>
                                    <td className="px-4 py-2">{alert.matricula}</td>
                                    <td className="px-4 py-2">{alert.nombre}</td>
                                    <td className="px-4 py-2">{alert.subject}</td>
                                    <td className="px-4 py-2">{alert.promedioFinal}</td>
                                    <td className={`px-4 py-2 font-bold flex items-center gap-1 ${alert.color}`}>{alert.icon} {alert.tipo}</td>
                                </tr>
                            )) : (
                                <tr>
                                    <td colSpan="5" className="px-4 py-2 text-center text-gray-500">No hay alumnos en riesgo, reprobados o extraordinarios detectados.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>
    );
} 