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
                const response = await axios.get(`/api/student-risk/${student.id}`);
                setRiskData(response.data);
            } catch (error) {
                console.error('Error fetching risk data:', error);
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
    const riskLevel = riskData?.risk_level || 'bajo';

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
                                        <div className="mb-4">
                                            <span className={`inline-flex px-3 py-1 text-sm font-semibold rounded-full ${
                                                riskLevel === 'alto' ? 'bg-red-100 text-red-800' :
                                                riskLevel === 'medio' ? 'bg-yellow-100 text-yellow-800' :
                                                'bg-green-100 text-green-800'
                                            }`}>
                                                Riesgo {riskLevel}
                                            </span>
                                        </div>

                                        {/* Panel de intervención IA */}
                                        <InterventionPanel riskLevel={riskLevel} metrics={metrics} />
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