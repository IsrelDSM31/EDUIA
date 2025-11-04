import React from 'react';
import { AlertCircle, Brain, CheckCircle } from 'lucide-react';

// IA básica: reglas para recomendaciones
function getRecommendations({ riskLevel, metrics, riskData }) {
    // Si hay recomendaciones de la IA, usarlas
    if (riskData?.recommendations && Array.isArray(riskData.recommendations) && riskData.recommendations.length > 0) {
        return riskData.recommendations.map(rec => {
            if (typeof rec === 'string') {
                return { message: rec, type: 'info', priority: 'medium' };
            }
            return rec;
        });
    }

    // Fallback: reglas básicas
    const recs = [];
    if (riskLevel === 'alto') {
        recs.push({
            message: 'Programar tutorías académicas personalizadas.',
            type: 'critical',
            priority: 'high'
        });
        recs.push({
            message: 'Contactar a los padres o tutores para informar la situación.',
            type: 'critical',
            priority: 'high'
        });
        recs.push({
            message: 'Asignar actividades extra de recuperación.',
            type: 'academic',
            priority: 'high'
        });
        if (metrics && metrics.attendance_rate < 0.8) {
            recs.push({
                message: 'Implementar plan de mejora de asistencia.',
                type: 'attendance',
                priority: 'high'
            });
        }
        if (metrics && metrics.grade_average < 7) {
            recs.push({
                message: 'Reforzar materias con bajo desempeño.',
                type: 'academic',
                priority: 'high'
            });
        }
    } else if (riskLevel === 'medio') {
        recs.push({
            message: 'Monitorear el desempeño semanalmente.',
            type: 'info',
            priority: 'medium'
        });
        recs.push({
            message: 'Sugerir participación en talleres de hábitos de estudio.',
            type: 'academic',
            priority: 'medium'
        });
        if (metrics && metrics.attendance_rate < 0.9) {
            recs.push({
                message: 'Enviar recordatorios de asistencia.',
                type: 'attendance',
                priority: 'medium'
            });
        }
    } else {
        recs.push({
            message: 'Mantener seguimiento regular.',
            type: 'success',
            priority: 'low'
        });
    }
    return recs;
}

export default function InterventionPanel({ riskLevel, metrics, riskData }) {
    const recommendations = getRecommendations({ riskLevel, metrics, riskData });
    
    // Si es riesgo bajo y no hay recomendaciones específicas, mostrar mensaje positivo
    if (riskLevel === 'bajo' && (!recommendations || recommendations.length === 0)) {
        return (
            <div className="mt-4 p-4 bg-green-50 border-l-4 border-green-400 rounded">
                <div className="flex items-center">
                    <CheckCircle className="w-5 h-5 text-green-500 mr-2" />
                    <span className="font-semibold text-green-800">El estudiante mantiene un buen desempeño académico</span>
                </div>
            </div>
        );
    }

    if (!recommendations || recommendations.length === 0) return null;

    const getBorderColor = (riskLevel) => {
        switch(riskLevel) {
            case 'alto': return 'border-red-400 bg-red-50';
            case 'medio': return 'border-yellow-400 bg-yellow-50';
            default: return 'border-green-400 bg-green-50';
        }
    };

    const getTextColor = (riskLevel) => {
        switch(riskLevel) {
            case 'alto': return 'text-red-800';
            case 'medio': return 'text-yellow-800';
            default: return 'text-green-800';
        }
    };

    const getIconColor = (riskLevel) => {
        switch(riskLevel) {
            case 'alto': return 'text-red-500';
            case 'medio': return 'text-yellow-500';
            default: return 'text-green-500';
        }
    };

    return (
        <div className={`mt-4 p-4 border-l-4 rounded ${getBorderColor(riskLevel)}`}>
            <div className="flex items-center mb-2">
                {riskData?.source === 'ml' ? (
                    <Brain className={`w-5 h-5 mr-2 ${getIconColor(riskLevel)}`} />
                ) : (
                    <AlertCircle className={`w-5 h-5 mr-2 ${getIconColor(riskLevel)}`} />
                )}
                <span className={`font-semibold ${getTextColor(riskLevel)}`}>
                    {riskData?.source === 'ml' ? 'Recomendaciones de la IA' : 'Recomendaciones de Intervención'}
                </span>
            </div>
            <ul className={`list-disc pl-6 ${getTextColor(riskLevel)} space-y-1`}>
                {recommendations.map((rec, idx) => {
                    const message = typeof rec === 'string' ? rec : rec.message;
                    return (
                        <li key={idx} className="text-sm">
                            {message}
                        </li>
                    );
                })}
            </ul>
        </div>
    );
} 