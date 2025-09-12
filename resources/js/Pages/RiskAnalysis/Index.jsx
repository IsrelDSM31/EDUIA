import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card } from '@/Components/UI/Card';
import { Link } from '@inertiajs/react';
// import { Badge } from '@/Components/UI/Badge'; // Componente no encontrado
// import { Progress } from '@/Components/UI/Progress'; // Componente no encontrado
// import { Alert, AlertDescription, AlertTitle } from '@/Components/UI/Alert'; // Componente no encontrado
import { AlertCircle, TrendingUp, TrendingDown, Minus } from 'lucide-react';
import InterventionPanel from './InterventionPanel';

export default function RiskAnalysis({ auth, riskData = [] }) {
    const getRiskLevelColor = (level) => {
        switch (level) {
            case 'alto':
                return 'bg-red-500 text-white';
            case 'medio':
                return 'bg-yellow-500 text-white';
            default:
                return 'bg-green-500 text-white';
        }
    };

    const getTrendIcon = (trend) => {
        switch (trend) {
            case 'improving':
                return <TrendingUp className="w-4 h-4 text-green-500" />;
            case 'declining':
                return <TrendingDown className="w-4 h-4 text-red-500" />;
            default:
                return <Minus className="w-4 h-4 text-gray-500" />;
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Análisis de Riesgo Académico</h2>}
        >
            <Head title="Análisis de Riesgo Académico" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {riskData.length > 0 ? riskData.map(({ student, risk, metrics }) => (
                            <Link href={route('students.show', student.id)} key={student.id} className="block">
                                <Card className="p-4 cursor-pointer hover:shadow-lg transition-shadow duration-200">
                                    <div className="pb-2">
                                        <div className="flex justify-between items-center">
                                            <h3 className="text-lg font-semibold">
                                                {student.nombre} {student.apellido_paterno} {student.apellido_materno}
                                            </h3>
                                            <span className={`px-2 py-1 text-xs font-semibold rounded-full ${getRiskLevelColor(risk?.risk_level || 'bajo')}`}>
                                                {risk?.risk_level || 'Bajo'}
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div className="space-y-4">
                                            <div>
                                                <h4 className="text-sm font-medium mb-2">Rendimiento Académico</h4>
                                                <div className="space-y-2">
                                                    <div>
                                                        <div className="flex justify-between text-sm mb-1">
                                                            <span>Promedio</span>
                                                            <span>{(metrics.grade_average || 0).toFixed(1)}</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div className="flex justify-between text-sm mb-1">
                                                            <span>Asistencia</span>
                                                            <span>{((metrics.attendance_rate || 0) * 100).toFixed(1)}%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {risk?.progress_metrics && (
                                                <div>
                                                    <h4 className="text-sm font-medium mb-2">Tendencias</h4>
                                                    <div className="grid grid-cols-2 gap-2">
                                                        <div className="flex items-center space-x-2">
                                                            {getTrendIcon(risk.progress_metrics.academic_progress?.trend)}
                                                            <span className="text-sm">Académica</span>
                                                        </div>
                                                        <div className="flex items-center space-x-2">
                                                            {getTrendIcon(risk.progress_metrics.attendance_progress?.trend)}
                                                            <span className="text-sm">Asistencia</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            )}

                                            {/* Panel de intervención IA */}
                                            <InterventionPanel riskLevel={risk?.risk_level} metrics={metrics} />
                                        </div>
                                    </div>
                                </Card>
                            </Link>
                        )) : (
                            <Card><p className="p-6">No hay datos de riesgo disponibles.</p></Card>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 