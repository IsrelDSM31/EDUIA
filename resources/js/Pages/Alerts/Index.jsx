import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Card } from '@/Components/UI/Card';

// SVG de alerta para usar en la tabla
const AlertIcon = () => (
    <svg className="w-5 h-5 text-red-600 inline mr-1" fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24" aria-hidden="true">
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.054 0 1.658-1.14 1.105-2.045l-6.928-11.99c-.526-.91-1.684-.91-2.21 0l-6.928 11.99c-.553.905.051 2.045 1.105 2.045z" />
    </svg>
);

export default function Index({ auth, alerts, students }) {
    const [filters, setFilters] = useState({
        search: '',
        urgency: '',
        type: '',
        riskLevel: ''
    });

    const safeAlerts = Array.isArray(alerts) ? alerts : [];
    const filteredAlerts = safeAlerts.filter(alert => {
        return (
            (filters.search === '' || 
                alert.student.name.toLowerCase().includes(filters.search.toLowerCase()) ||
                alert.title.toLowerCase().includes(filters.search.toLowerCase()) ||
                alert.description.toLowerCase().includes(filters.search.toLowerCase())) &&
            (filters.urgency === '' || alert.urgency === filters.urgency) &&
            (filters.type === '' || alert.type === filters.type) &&
            (filters.riskLevel === '' || alert.student.risk_level === filters.riskLevel)
        );
    });

    const urgencyColor = {
        high: 'bg-red-100 text-red-800',
        medium: 'bg-yellow-100 text-yellow-800',
        low: 'bg-green-100 text-green-800'
    };

    const riskLevelColor = {
        alto: 'bg-red-100 text-red-800',
        medio: 'bg-yellow-100 text-yellow-800',
        bajo: 'bg-green-100 text-green-800',
        'no evaluado': 'bg-gray-100 text-gray-800'
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Sistema de Alertas</h2>}
        >
            <Head title="Alertas" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <Card className="p-6">
                        <div className="flex flex-col md:flex-row gap-4 mb-6">
                            <label htmlFor="search" className="sr-only">Buscar</label>
                            <input
                                id="search"
                                type="text"
                                className="border rounded px-3 py-2 w-full md:w-1/4"
                                placeholder="Buscar por nombre o descripción..."
                                value={filters.search}
                                onChange={e => setFilters({...filters, search: e.target.value})}
                            />
                            <label htmlFor="urgency" className="sr-only">Urgencia</label>
                            <select
                                id="urgency"
                                className="border rounded px-3 py-2 w-full md:w-1/6"
                                value={filters.urgency}
                                onChange={e => setFilters({...filters, urgency: e.target.value})}
                            >
                                <option value="">Todas las urgencias</option>
                                <option value="high">Alta</option>
                                <option value="medium">Media</option>
                                <option value="low">Baja</option>
                            </select>
                            <label htmlFor="type" className="sr-only">Tipo</label>
                            <select
                                id="type"
                                className="border rounded px-3 py-2 w-full md:w-1/6"
                                value={filters.type}
                                onChange={e => setFilters({...filters, type: e.target.value})}
                            >
                                <option value="">Todos los tipos</option>
                                <option value="attendance">Asistencia</option>
                                <option value="academic">Académico</option>
                                <option value="behavioral">Conductual</option>
                            </select>
                            <select
                                className="border rounded px-3 py-2 w-full md:w-1/6"
                                value={filters.riskLevel}
                                onChange={e => setFilters({...filters, riskLevel: e.target.value})}
                            >
                                <option value="">Todos los niveles de riesgo</option>
                                <option value="alto">Alto</option>
                                <option value="medio">Medio</option>
                                <option value="bajo">Bajo</option>
                                <option value="no evaluado">No evaluado</option>
                            </select>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Estudiante
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Grupo
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nivel de Riesgo
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tipo
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Título
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Urgencia
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Fecha
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {filteredAlerts.map(alert => (
                                        <tr key={alert.id} className={alert.urgency === 'high' ? 'bg-red-100 border-l-4 border-red-400' : ''}>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <a 
                                                    href={route('alerts.show', alert.student.id)}
                                                    className="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    {alert.student.name}
                                                </a>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {alert.student.group}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${riskLevelColor[alert.student.risk_level]}`}>
                                                    {alert.student.risk_level}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {alert.type === 'attendance' ? 'Asistencia' :
                                                 alert.type === 'academic' ? 'Académico' : 'Conductual'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {alert.title}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {alert.urgency === 'high' ? (
                                                    <span className="flex items-center">
                                                        <AlertIcon />
                                                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${urgencyColor[alert.urgency]}`}>Alta</span>
                                                    </span>
                                                ) : (
                                                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${urgencyColor[alert.urgency]}`}>{alert.urgency === 'medium' ? 'Media' : 'Baja'}</span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {alert.created_at}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button
                                                    onClick={() => window.location.href = route('alerts.show', alert.student.id)}
                                                    className="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Ver Detalles
                                                </button>
                                            </td>
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