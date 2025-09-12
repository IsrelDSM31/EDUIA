import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Card } from '@/Components/UI/Card';

export default function Show({ auth, student }) {
    const riskLevelColor = {
        alto: 'bg-red-100 text-red-800',
        medio: 'bg-yellow-100 text-yellow-800',
        bajo: 'bg-green-100 text-green-800',
        'no evaluado': 'bg-gray-100 text-gray-800'
    };

    const urgencyColor = {
        high: 'bg-red-100 text-red-800',
        medium: 'bg-yellow-100 text-yellow-800',
        low: 'bg-green-100 text-green-800'
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Historial del Estudiante</h2>}
        >
            <Head title={`Historial - ${student.name}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Información General */}
                    <Card className="p-6 mb-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 className="text-lg font-semibold mb-4">Información del Estudiante</h3>
                                <div className="space-y-2">
                                    <p><span className="font-medium">Nombre:</span> {student.name}</p>
                                    <p><span className="font-medium">Grupo:</span> {student.group}</p>
                                    <p>
                                        <span className="font-medium">Nivel de Riesgo:</span>{' '}
                                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${riskLevelColor[student.risk_data.level]}`}>
                                            {student.risk_data.level}
                                        </span>
                                    </p>
                                    <p><span className="font-medium">Puntaje de Riesgo:</span> {student.risk_data.score}</p>
                                </div>
                            </div>
                            <div>
                                <h3 className="text-lg font-semibold mb-4">Resumen de Asistencia (30 días)</h3>
                                <div className="space-y-2">
                                    <p><span className="font-medium">Total de Clases:</span> {student.attendance_summary.total}</p>
                                    <p><span className="font-medium">Asistencias:</span> {student.attendance_summary.present}</p>
                                    <p><span className="font-medium">Faltas:</span> {student.attendance_summary.absences}</p>
                                    <p><span className="font-medium">Retardos:</span> {student.attendance_summary.late}</p>
                                    <p><span className="font-medium">Justificadas:</span> {student.attendance_summary.justified}</p>
                                </div>
                            </div>
                        </div>
                    </Card>

                    {/* Métricas de Rendimiento */}
                    <Card className="p-6 mb-6">
                        <h3 className="text-lg font-semibold mb-4">Métricas de Rendimiento</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 className="font-medium mb-2">Calificaciones (30 días)</h4>
                                <div className="space-y-2">
                                    <p><span className="font-medium">Promedio:</span> {student.grade_summary.average?.toFixed(2)}</p>
                                    <p><span className="font-medium">Materias en Riesgo:</span> {student.grade_summary.subjects_at_risk.join(', ') || 'Ninguna'}</p>
                                    <p><span className="font-medium">Evaluaciones Bajas:</span> {student.grade_summary.below_seven}</p>
                                </div>
                            </div>
                            {student.risk_data.performance_metrics && (
                                <div>
                                    <h4 className="font-medium mb-2">Indicadores de Riesgo</h4>
                                    <div className="space-y-2">
                                        {Object.entries(student.risk_data.performance_metrics).map(([key, value]) => (
                                            key !== 'last_updated' && (
                                                <p key={key}>
                                                    <span className="font-medium">{key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')}:</span>{' '}
                                                    {value}
                                                </p>
                                            )
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </Card>

                    {/* Recomendaciones */}
                    {student.risk_data.recommendations && student.risk_data.recommendations.length > 0 && (
                        <Card className="p-6 mb-6">
                            <h3 className="text-lg font-semibold mb-4">Recomendaciones de Intervención</h3>
                            <div className="space-y-4">
                                {student.risk_data.recommendations.map((rec, index) => (
                                    <div key={index} className={`p-4 rounded-lg ${rec.priority === 'high' ? 'bg-red-50' : 'bg-yellow-50'}`}>
                                        <p className="font-medium">{rec.type === 'attendance' ? 'Asistencia' : rec.type === 'academic' ? 'Académico' : 'Monitoreo'}</p>
                                        <p className="text-sm mt-1">{rec.message}</p>
                                    </div>
                                ))}
                            </div>
                        </Card>
                    )}

                    {/* Historial de Alertas */}
                    <Card className="p-6 mb-6">
                        <h3 className="text-lg font-semibold mb-4">Historial de Alertas</h3>
                        <div className="space-y-4">
                            {student.alerts.map(alert => (
                                <div key={alert.id} className="border rounded-lg p-4">
                                    <div className="flex justify-between items-start">
                                        <div>
                                            <h4 className="font-medium">{alert.title}</h4>
                                            <p className="text-sm text-gray-600 mt-1">{alert.description}</p>
                                        </div>
                                        <span className={`px-2 py-1 text-xs font-semibold rounded-full ${urgencyColor[alert.urgency]}`}>
                                            {alert.urgency === 'high' ? 'Alta' : alert.urgency === 'medium' ? 'Media' : 'Baja'}
                                        </span>
                                    </div>
                                    <div className="mt-4">
                                        <p className="text-sm"><span className="font-medium">Acciones Sugeridas:</span></p>
                                        <ul className="list-disc list-inside text-sm ml-4 mt-1">
                                            {alert.suggested_actions.map((action, index) => (
                                                <li key={index}>{action}</li>
                                            ))}
                                        </ul>
                                    </div>
                                    <div className="mt-2 text-sm text-gray-500">
                                        {alert.created_at}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </Card>

                    {/* Asistencias Recientes */}
                    <Card className="p-6 mb-6">
                        <h3 className="text-lg font-semibold mb-4">Asistencias Recientes</h3>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Justificación</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {student.recent_attendances.map((attendance, index) => (
                                        <tr key={index}>
                                            <td className="px-6 py-4 whitespace-nowrap">{attendance.date}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">{attendance.status}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">{attendance.justification || '-'}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </Card>

                    {/* Calificaciones Recientes */}
                    <Card className="p-6">
                        <h3 className="text-lg font-semibold mb-4">Calificaciones Recientes</h3>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materia</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calificación</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {student.recent_grades.map((grade, index) => (
                                        <tr key={index}>
                                            <td className="px-6 py-4 whitespace-nowrap">{grade.subject}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">{grade.score}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">{grade.evaluation_type}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">{grade.date}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 