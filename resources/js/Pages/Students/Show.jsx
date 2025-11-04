import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card } from '@/Components/UI/Card';
import SecondaryButton from '@/Components/SecondaryButton';
import InterventionPanel from '../RiskAnalysis/InterventionPanel';
import axios from 'axios';

export default function StudentShow({ auth, student }) {
    const [riskData, setRiskData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchRiskData = async () => {
            try {
                const response = await axios.get(`/api/students/${student.id}/risk-analysis`);
                setRiskData(response.data);
            } catch (error) {
                // Error fetching risk data
            } finally {
                setLoading(false);
            }
        };

        fetchRiskData();
    }, [student.id]);

    const calculateMetrics = () => {
        if (!student.grades || !student.attendances) {
            return {
                grade_average: 0,
                attendance_rate: 0,
                failed_subjects: 0
            };
        }

        const gradeAverage = student.grades.length > 0 
            ? student.grades.reduce((sum, grade) => sum + (grade.promedio_final || 0), 0) / student.grades.length 
            : 0;

        const totalAttendance = student.attendances.length;
        const presentAttendance = student.attendances.filter(a => a.status === 'present').length;
        const attendanceRate = totalAttendance > 0 ? presentAttendance / totalAttendance : 0;

        const failedSubjects = student.grades.filter(grade => (grade.promedio_final || 0) < 7).length;

        return {
            grade_average: gradeAverage,
            attendance_rate: attendanceRate,
            failed_subjects: failedSubjects
        };
    };

    const metrics = calculateMetrics();
    const riskLevel = riskData?.data?.risk_level || riskData?.risk_level || 'bajo';
    
    // Extraer datos del response si están en data
    const riskDataFormatted = riskData?.data || riskData;

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Ficha del Estudiante</h2>}
        >
            <Head title={`Estudiante - ${student.nombre} ${student.apellido_paterno}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Botón de regreso */}
                    <div className="mb-6">
                        <Link href={route('students.index')}>
                            <SecondaryButton>
                                ← Volver a Estudiantes
                            </SecondaryButton>
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Información básica del estudiante */}
                        <div className="lg:col-span-2">
                            <Card className="p-6">
                                <h3 className="text-xl font-semibold mb-4">
                                    {student.nombre} {student.apellido_paterno} {student.apellido_materno}
                                </h3>
                                
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h4 className="font-medium text-gray-900 mb-2">Información Personal</h4>
                                        <dl className="space-y-2">
                                            <div>
                                                <dt className="text-sm font-medium text-gray-500">Matrícula:</dt>
                                                <dd className="text-sm text-gray-900">{student.matricula}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-sm font-medium text-gray-500">Fecha de Nacimiento:</dt>
                                                <dd className="text-sm text-gray-900">
                                                    {student.birth_date ? new Date(student.birth_date).toLocaleDateString('es-ES') : 'No especificada'}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt className="text-sm font-medium text-gray-500">Grupo:</dt>
                                                <dd className="text-sm text-gray-900">{student.group?.name || 'No asignado'}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div>
                                        <h4 className="font-medium text-gray-900 mb-2">Métricas Académicas</h4>
                                        <dl className="space-y-2">
                                            <div>
                                                <dt className="text-sm font-medium text-gray-500">Promedio General:</dt>
                                                <dd className="text-sm text-gray-900">{isNaN(metrics.grade_average) ? 0 : metrics.grade_average.toFixed(2)}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-sm font-medium text-gray-500">Tasa de Asistencia:</dt>
                                                <dd className="text-sm text-gray-900">{(metrics.attendance_rate * 100).toFixed(1)}%</dd>
                                            </div>
                                            <div>
                                                <dt className="text-sm font-medium text-gray-500">Materias Reprobadas:</dt>
                                                <dd className="text-sm text-gray-900">{metrics.failed_subjects}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </Card>
                        </div>

                        {/* Panel de intervención */}
                        <div className="lg:col-span-1">
                            <Card className="p-6">
                                <h3 className="text-lg font-semibold mb-4">Análisis de Riesgo</h3>
                                
                                {loading ? (
                                    <div className="text-center py-4">
                                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
                                        <p className="text-sm text-gray-500 mt-2">Cargando análisis...</p>
                                    </div>
                                ) : (
                                    <div>
                                        {/* Indicador de fuente (ML o Reglas) */}
                                        {riskDataFormatted?.source === 'ml' && (
                                            <div className="mb-3 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                                                <div className="flex items-center">
                                                    <svg className="w-4 h-4 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                                        <path fillRule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clipRule="evenodd" />
                                                    </svg>
                                                    <span className="text-xs font-semibold text-blue-800">Inteligencia Artificial</span>
                                                </div>
                                                <p className="text-xs text-blue-600 mt-1">{riskDataFormatted?.message || 'Predicción realizada usando Machine Learning'}</p>
                                            </div>
                                        )}

                                        {riskDataFormatted?.source === 'rules' && (
                                            <div className="mb-3 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                                <div className="flex items-center">
                                                    <svg className="w-4 h-4 text-gray-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fillRule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clipRule="evenodd" />
                                                    </svg>
                                                    <span className="text-xs font-semibold text-gray-700">Reglas Heurísticas</span>
                                                </div>
                                                <p className="text-xs text-gray-600 mt-1">{riskDataFormatted?.message || 'Servicio ML no disponible'}</p>
                                            </div>
                                        )}

                                        <div className="mb-4">
                                            <span className={`inline-flex px-3 py-1 text-sm font-semibold rounded-full ${
                                                riskLevel === 'alto' ? 'bg-red-100 text-red-800' :
                                                riskLevel === 'medio' ? 'bg-yellow-100 text-yellow-800' :
                                                'bg-green-100 text-green-800'
                                            }`}>
                                                Riesgo {riskLevel}
                                            </span>
                                        </div>

                                        {/* Información detallada de la IA */}
                                        {riskDataFormatted?.source === 'ml' && (
                                            <div className="mb-4 space-y-3">
                                                {/* Confianza */}
                                                {riskDataFormatted?.confidence && (
                                                    <div className="p-3 bg-gray-50 rounded-lg">
                                                        <div className="flex justify-between items-center mb-1">
                                                            <span className="text-xs font-medium text-gray-700">Confianza del Modelo</span>
                                                            <span className="text-xs font-bold text-gray-900">{(riskDataFormatted.confidence * 100).toFixed(1)}%</span>
                                                        </div>
                                                        <div className="w-full bg-gray-200 rounded-full h-2">
                                                            <div 
                                                                className={`h-2 rounded-full ${
                                                                    riskDataFormatted.confidence >= 0.7 ? 'bg-green-500' :
                                                                    riskDataFormatted.confidence >= 0.5 ? 'bg-yellow-500' :
                                                                    'bg-red-500'
                                                                }`}
                                                                style={{ width: `${riskDataFormatted.confidence * 100}%` }}
                                                            ></div>
                                                        </div>
                                                    </div>
                                                )}

                                                {/* Probabilidades */}
                                                {riskDataFormatted?.probabilities && Object.keys(riskDataFormatted.probabilities).length > 0 && (
                                                    <div className="p-3 bg-gray-50 rounded-lg">
                                                        <span className="text-xs font-medium text-gray-700 block mb-2">Probabilidades por Nivel</span>
                                                        <div className="space-y-2">
                                                            {Object.entries(riskDataFormatted.probabilities).map(([level, prob]) => (
                                                                <div key={level} className="flex items-center justify-between">
                                                                    <span className="text-xs text-gray-600 capitalize">{level}:</span>
                                                                    <div className="flex items-center space-x-2 flex-1 ml-2">
                                                                        <div className="flex-1 bg-gray-200 rounded-full h-1.5">
                                                                            <div 
                                                                                className={`h-1.5 rounded-full ${
                                                                                    level === 'alto' ? 'bg-red-500' :
                                                                                    level === 'medio' ? 'bg-yellow-500' :
                                                                                    'bg-green-500'
                                                                                }`}
                                                                                style={{ width: `${(prob * 100)}%` }}
                                                                            ></div>
                                                                        </div>
                                                                        <span className="text-xs font-semibold text-gray-900 w-12 text-right">{(prob * 100).toFixed(1)}%</span>
                                                                    </div>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    </div>
                                                )}

                                                {/* Características usadas */}
                                                {riskDataFormatted?.features_used && Object.keys(riskDataFormatted.features_used).length > 0 && (
                                                    <div className="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                                        <span className="text-xs font-medium text-blue-800 block mb-2">Características Analizadas por la IA</span>
                                                        <div className="space-y-1.5">
                                                            {Object.entries(riskDataFormatted.features_used).map(([feature, value]) => (
                                                                <div key={feature} className="flex justify-between items-center text-xs">
                                                                    <span className="text-blue-700 capitalize">
                                                                        {feature === 'grade_average' ? 'Promedio General' :
                                                                         feature === 'attendance_rate' ? 'Tasa de Asistencia' :
                                                                         feature === 'failed_subjects' ? 'Materias Reprobadas' :
                                                                         feature === 'recent_improvement' ? 'Mejora Reciente' :
                                                                         feature === 'group_id' ? 'Grupo' :
                                                                         feature}
                                                                    </span>
                                                                    <span className="font-semibold text-blue-900">
                                                                        {typeof value === 'number' 
                                                                            ? feature === 'attendance_rate' 
                                                                                ? `${(value * 100).toFixed(1)}%`
                                                                                : feature === 'grade_average'
                                                                                    ? value.toFixed(2)
                                                                                    : value.toString()
                                                                            : value}
                                                                    </span>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        )}

                                        {/* Panel de intervención IA */}
                                        <InterventionPanel riskLevel={riskLevel} metrics={metrics} riskData={riskDataFormatted} />
                                    </div>
                                )}
                            </Card>
                        </div>
                    </div>

                    {/* Calificaciones recientes */}
                    {student.grades && student.grades.length > 0 && (
                        <Card className="p-6 mt-6">
                            <h3 className="text-lg font-semibold mb-4">Calificaciones Recientes</h3>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Materia
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Promedio Final
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Estado
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Riesgo
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {student.grades.map((grade) => {
                                            let riesgoLabel = '';
                                            let riesgoClass = '';
                                            if ((grade.promedio_final || 0) < 7) {
                                                riesgoLabel = 'Reprobada';
                                                riesgoClass = 'bg-red-100 text-red-800';
                                            } else if ((grade.promedio_final || 0) < 8) {
                                                riesgoLabel = 'En riesgo';
                                                riesgoClass = 'bg-yellow-100 text-yellow-800';
                                            } else {
                                                riesgoLabel = 'Aprobada';
                                                riesgoClass = 'bg-green-100 text-green-800';
                                            }
                                            return (
                                                <tr key={grade.id}>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {grade.subject?.name || 'Materia no especificada'}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {typeof grade.promedio_final === 'number' && !isNaN(grade.promedio_final)
                                                            ? grade.promedio_final.toFixed(2)
                                                            : 'N/A'}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                            (grade.promedio_final || 0) >= 7 
                                                                ? 'bg-green-100 text-green-800' 
                                                                : 'bg-red-100 text-red-800'
                                                        }`}>
                                                            {(grade.promedio_final || 0) >= 7 ? 'Aprobado' : 'Reprobado'}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${riesgoClass}`}>
                                                            {riesgoLabel}
                                                        </span>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        </Card>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 