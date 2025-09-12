import React from 'react';
import { Card } from '@/Components/UI/Card';

export default function EventsModule({ stats }) {
    return (
        <Card className="p-6">
            <h3 className="text-lg font-semibold mb-4">Eventos</h3>
            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridad</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {(stats?.events?.length > 0) ? (
                            stats.events.map(event => (
                                <tr key={event.id}>
                                    <td className="px-6 py-4 whitespace-nowrap">{event.title}</td>
                                    <td className="px-6 py-4 whitespace-nowrap">{event.description}</td>
                                    <td className="px-6 py-4 whitespace-nowrap">{event.date}</td>
                                    <td className="px-6 py-4 whitespace-nowrap">{event.type}</td>
                                    <td className="px-6 py-4 whitespace-nowrap">{event.priority}</td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="5" className="px-6 py-4 text-center text-gray-500">No hay eventos registrados.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </Card>
    );
} 