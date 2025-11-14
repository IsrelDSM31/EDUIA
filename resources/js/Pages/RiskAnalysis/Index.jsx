import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card } from '@/Components/UI/Card';
import { Link } from '@inertiajs/react';
import { AlertCircle, TrendingUp, TrendingDown, Minus, Brain, ChevronDown, ChevronUp } from 'lucide-react';
import InterventionPanel from './InterventionPanel';

export default function RiskAnalysis({ auth, riskData = [] }) {
    const [expandedCards, setExpandedCards] = useState({});

    const toggleCard = (studentId) => {
        setExpandedCards(prev => ({
            ...prev,
            [studentId]: !prev[studentId]
        }));
    };

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
                        {riskData.length > 0 ? riskData.map(({ student, risk, metrics, recommendations }) => {
                            const isExpanded = expandedCards[student.id];
                            const hasMLData = risk?.ml_service_available && risk?.source === 'ml';
                            
                            return (
                                <Card key={student.id} className="p-4 hover:shadow-lg transition-shadow duration-200">
                                    <div className="pb-2">
                                        <div className="flex justify-between items-center">
                                            <Link href={route('students.show', student.id)} className="flex-1">
                                                <h3 className="text-lg font-semibold hover:text-indigo-600 transition-colors">
                                                    {student.nombre} {student.apellido_paterno} {student.apellido_materno}
                                                </h3>
                                            </Link>
                                            <div className="flex items-center space-x-2">
                                                <span className={`px-2 py-1 text-xs font-semibold rounded-full ${getRiskLevelColor(risk?.risk_level || 'bajo')}`}>
                                                    {risk?.risk_level || 'Bajo'}
                                                </span>
                                                {hasMLData && (
                                                    <span className="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 flex items-center">
                                                        <Brain className="w-3 h-3 mr-1" />
                                                        IA
                                                    </span>
                                                )}
                                            </div>
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
                                                            <span>{(metrics?.grade_average || 0).toFixed(1)}</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div className="flex justify-between text-sm mb-1">
                                                            <span>Asistencia</span>
                                                            <span>{((metrics?.attendance_rate || 0) * 100).toFixed(1)}%</span>
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

                                            {/* Información del Modelo IA (colapsable) */}
                                            {hasMLData && (
                                                <div className="border-t pt-3">
                                                    <button
                                                        onClick={() => toggleCard(student.id)}
                                                        className="flex items-center justify-between w-full text-left text-sm font-medium text-blue-700 hover:text-blue-900"
                                                    >
                                                        <span className="flex items-center">
                                                            <Brain className="w-4 h-4 mr-2" />
                                                            Detalles del Modelo IA
                                                        </span>
                                                        {isExpanded ? <ChevronUp className="w-4 h-4" /> : <ChevronDown className="w-4 h-4" />}
                                                    </button>
                                                    
                                                    {isExpanded && (
                                                        <div className="mt-3 space-y-3">
                                                            {/* Confianza */}
                                                            {risk?.confidence && (
                                                                <div className="p-2 bg-gray-50 rounded-lg">
                                                                    <div className="flex justify-between items-center mb-1">
                                                                        <span className="text-xs font-medium text-gray-700">Confianza</span>
                                                                        <span className="text-xs font-bold text-gray-900">{(risk.confidence * 100).toFixed(1)}%</span>
                                                                    </div>
                                                                    <div className="w-full bg-gray-200 rounded-full h-1.5">
                                                                        <div 
                                                                            className={`h-1.5 rounded-full ${
                                                                                risk.confidence >= 0.7 ? 'bg-green-500' :
                                                                                risk.confidence >= 0.5 ? 'bg-yellow-500' :
                                                                                'bg-red-500'
                                                                            }`}
                                                                            style={{ width: `${risk.confidence * 100}%` }}
                                                                        ></div>
                                                                    </div>
                                                                </div>
                                                            )}

                                                            {/* Probabilidades */}
                                                            {risk?.probabilities && Object.keys(risk.probabilities).length > 0 && (
                                                                <div className="p-2 bg-gray-50 rounded-lg">
                                                                    <span className="text-xs font-medium text-gray-700 block mb-2">Probabilidades</span>
                                                                    <div className="space-y-1.5">
                                                                        {Object.entries(risk.probabilities).map(([level, prob]) => (
                                                                            <div key={level} className="flex items-center justify-between">
                                                                                <span className="text-xs text-gray-600 capitalize">{level}:</span>
                                                                                <div className="flex items-center space-x-2 flex-1 ml-2">
                                                                                    <div className="flex-1 bg-gray-200 rounded-full h-1">
                                                                                        <div 
                                                                                            className={`h-1 rounded-full ${
                                                                                                level === 'alto' ? 'bg-red-500' :
                                                                                                level === 'medio' ? 'bg-yellow-500' :
                                                                                                'bg-green-500'
                                                                                            }`}
                                                                                            style={{ width: `${(prob * 100)}%` }}
                                                                                        ></div>
                                                                                    </div>
                                                                                    <span className="text-xs font-semibold text-gray-900 w-10 text-right">{(prob * 100).toFixed(0)}%</span>
                                                                                </div>
                                                                            </div>
                                                                        ))}
                                                                    </div>
                                                                </div>
                                                            )}

                                                            {/* Características */}
                                                            {risk?.features_used && Object.keys(risk.features_used).length > 0 && (
                                                                <div className="p-2 bg-blue-50 rounded-lg border border-blue-200">
                                                                    <span className="text-xs font-medium text-blue-800 block mb-2">Características Analizadas</span>
                                                                    <div className="space-y-1">
                                                                        {Object.entries(risk.features_used).map(([feature, value]) => (
                                                                            <div key={feature} className="flex justify-between text-xs">
                                                                                <span className="text-blue-700">
                                                                                    {feature === 'grade_average' ? 'Promedio' :
                                                                                     feature === 'attendance_rate' ? 'Asistencia' :
                                                                                     feature === 'failed_subjects' ? 'Reprobadas' :
                                                                                     feature === 'recent_improvement' ? 'Mejora' :
                                                                                     feature === 'group_id' ? 'Grupo' :
                                                                                     feature}
                                                                                </span>
                                                                                <span className="font-semibold text-blue-900">
                                                                                    {typeof value === 'number' 
                                                                                        ? feature === 'attendance_rate' 
                                                                                            ? `${(value * 100).toFixed(0)}%`
                                                                                            : feature === 'grade_average'
                                                                                                ? value.toFixed(1)
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
                                                </div>
                                            )}

                                            {/* Panel de intervención IA */}
                                            <InterventionPanel 
                                                riskLevel={risk?.risk_level} 
                                                metrics={metrics} 
                                                riskData={{
                                                    source: risk?.source,
                                                    recommendations: recommendations
                                                }}
                                            />
                                        </div>
                                    </div>
                                </Card>
                            );
                        }) : (
                            <Card><p className="p-6">No hay datos de riesgo disponibles.</p></Card>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 