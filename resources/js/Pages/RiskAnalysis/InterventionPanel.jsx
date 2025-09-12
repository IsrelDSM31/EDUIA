import React from 'react';
import { AlertCircle } from 'lucide-react';

// IA básica: reglas para recomendaciones
function getRecommendations({ riskLevel, metrics }) {
    const recs = [];
    if (riskLevel === 'alto') {
        recs.push('Programar tutorías académicas personalizadas.');
        recs.push('Contactar a los padres o tutores para informar la situación.');
        recs.push('Asignar actividades extra de recuperación.');
        if (metrics.attendance_rate < 0.8) {
            recs.push('Implementar plan de mejora de asistencia.');
        }
        if (metrics.grade_average < 7) {
            recs.push('Reforzar materias con bajo desempeño.');
        }
    } else if (riskLevel === 'medio') {
        recs.push('Monitorear el desempeño semanalmente.');
        recs.push('Sugerir participación en talleres de hábitos de estudio.');
        if (metrics.attendance_rate < 0.9) {
            recs.push('Enviar recordatorios de asistencia.');
        }
    } else {
        recs.push('Mantener seguimiento regular.');
    }
    return recs;
}

export default function InterventionPanel({ riskLevel, metrics }) {
    const recommendations = getRecommendations({ riskLevel, metrics });
    if (riskLevel === 'bajo') return null;
    return (
        <div className="mt-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
            <div className="flex items-center mb-2">
                <AlertCircle className="w-5 h-5 text-yellow-500 mr-2" />
                <span className="font-semibold text-yellow-800">Recomendaciones de Intervención</span>
            </div>
            <ul className="list-disc pl-6 text-yellow-900">
                {recommendations.map((rec, idx) => (
                    <li key={idx}>{rec}</li>
                ))}
            </ul>
        </div>
    );
} 