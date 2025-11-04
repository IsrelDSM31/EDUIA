import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Card } from '@/Components/UI/Card';

export default function Show({ student, alerts }) {
    const urgencyColor = {
        high: 'bg-red-100 text-red-800',
        medium: 'bg-yellow-100 text-yellow-800',
        low: 'bg-green-100 text-green-800'
    };

    const urgencyLabels = {
        high: 'Alta',
        medium: 'Media',
        low: 'Baja'
    };

    const typeLabels = {
        attendance: 'Asistencia',
        academic: 'Académico',
        behavioral: 'Conductual'
    };

    return (
        <AuthenticatedLayout
            user={student.user}
            header={
                <div className="flex items-center justify-between">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                        Alertas de {student.nombre} {student.apellido_paterno}
                    </h2>
                    <Link
                        href="/alerts"
                        className="text-blue-600 hover:text-blue-800 underline"
                    >
                        Volver a Alertas
                    </Link>
                </div>
            }
        >
            <Head title={`Alertas - ${student.nombre} ${student.apellido_paterno}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <Card className="p-6 bg-white">
                        <div className="mb-6">
                            <h3 className="text-lg font-semibold text-gray-800 mb-2">
                                Información del Estudiante
                            </h3>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm text-gray-600">Matrícula:</p>
                                    <p className="font-medium">{student.matricula}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Grupo:</p>
                                    <p className="font-medium">{student.group?.name || 'Sin grupo'}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-4">
                                Historial de Alertas ({alerts.length})
                            </h3>
                            
                            {alerts.length === 0 ? (
                                <div className="text-center py-8 text-gray-500">
                                    No hay alertas registradas para este estudiante.
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {alerts.map((alert) => (
                                        <div
                                            key={alert.id}
                                            className={`border rounded-lg p-4 ${
                                                alert.urgency === 'high' 
                                                    ? 'border-red-300 bg-red-50' 
                                                    : alert.urgency === 'medium'
                                                    ? 'border-yellow-300 bg-yellow-50'
                                                    : 'border-green-300 bg-green-50'
                                            }`}
                                        >
                                            <div className="flex justify-between items-start mb-2">
                                                <div>
                                                    <h4 className="font-semibold text-gray-800">
                                                        {alert.title}
                                                    </h4>
                                                    <p className="text-sm text-gray-600">
                                                        {typeLabels[alert.type] || alert.type}
                                                    </p>
                                                </div>
                                                <span
                                                    className={`px-3 py-1 rounded-full text-xs font-semibold ${
                                                        urgencyColor[alert.urgency] || 'bg-gray-100 text-gray-800'
                                                    }`}
                                                >
                                                    {urgencyLabels[alert.urgency] || alert.urgency}
                                                </span>
                                            </div>
                                            
                                            <p className="text-gray-700 mb-3">
                                                {alert.description}
                                            </p>
                                            
                                            <div className="flex justify-between items-center text-sm text-gray-500">
                                                <span>
                                                    Fecha: {new Date(alert.created_at).toLocaleDateString('es-ES', {
                                                        year: 'numeric',
                                                        month: 'long',
                                                        day: 'numeric'
                                                    })}
                                                </span>
                                                <span
                                                    className={`px-2 py-1 rounded ${
                                                        alert.status === 'resolved' 
                                                            ? 'bg-green-100 text-green-800'
                                                            : 'bg-yellow-100 text-yellow-800'
                                                    }`}
                                                >
                                                    {alert.status === 'resolved' ? 'Resuelta' : 'Pendiente'}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
