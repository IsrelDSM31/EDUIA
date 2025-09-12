import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import SecondaryButton from '@/Components/SecondaryButton';

export default function Show({ auth, changeLog }) {
    const getActionBadge = (action) => {
        const badges = {
            'update': 'bg-blue-100 text-blue-800',
            'delete': 'bg-red-100 text-red-800',
            'create': 'bg-green-100 text-green-800',
        };
        return badges[action] || 'bg-gray-100 text-gray-800';
    };

    const getModelTypeLabel = (modelType) => {
        const labels = {
            'App\\Models\\Grade': 'Calificación',
            'App\\Models\\Attendance': 'Asistencia',
        };
        return labels[modelType] || modelType;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Detalles del Cambio</h2>}
        >
            <Head title="Detalles del Cambio" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    {/* Información general */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6">
                            <div className="flex justify-between items-start mb-6">
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        Información del Cambio
                                    </h3>
                                    <p className="text-sm text-gray-600">
                                        ID del registro: {changeLog.id}
                                    </p>
                                </div>
                                <Link href={route('change-log.index')}>
                                    <SecondaryButton>
                                        ← Volver al Historial
                                    </SecondaryButton>
                                </Link>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 className="font-medium text-gray-900 mb-2">Detalles Generales</h4>
                                    <dl className="space-y-2">
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Fecha y Hora:</dt>
                                            <dd className="text-sm text-gray-900">
                                                {new Date(changeLog.created_at).toLocaleString('es-ES')}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Usuario:</dt>
                                            <dd className="text-sm text-gray-900">
                                                {changeLog.user?.name || 'Usuario eliminado'}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Tipo de Registro:</dt>
                                            <dd className="text-sm text-gray-900">
                                                {getModelTypeLabel(changeLog.model_type)}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Acción:</dt>
                                            <dd className="text-sm text-gray-900">
                                                <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getActionBadge(changeLog.action)}`}>
                                                    {changeLog.action === 'update' ? 'Modificación' : 'Eliminación'}
                                                </span>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <div>
                                    <h4 className="font-medium text-gray-900 mb-2">Información del Registro</h4>
                                    <dl className="space-y-2">
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Estudiante:</dt>
                                            <dd className="text-sm text-gray-900">
                                                {changeLog.model_info?.student_name || 'N/A'}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Materia:</dt>
                                            <dd className="text-sm text-gray-900">
                                                {changeLog.model_info?.subject_name || 'N/A'}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">ID del Modelo:</dt>
                                            <dd className="text-sm text-gray-900">
                                                {changeLog.model_id}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Cambios realizados */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">
                                Cambios Realizados
                            </h3>

                            {changeLog.formatted_changes && changeLog.formatted_changes.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Campo
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Valor Anterior
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Valor Nuevo
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {changeLog.formatted_changes.map((change, index) => (
                                                <tr key={index} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {change.field}
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900">
                                                        <div className="max-w-xs overflow-hidden">
                                                            <span className="text-red-600">
                                                                {change.old_value}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900">
                                                        <div className="max-w-xs overflow-hidden">
                                                            <span className="text-green-600">
                                                                {change.new_value}
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <p className="text-gray-500">
                                        No se encontraron cambios específicos para mostrar.
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Datos completos (para debugging) */}
                    {process.env.NODE_ENV === 'development' && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Datos Completos (Solo Desarrollo)
                                </h3>
                                <pre className="bg-gray-100 p-4 rounded text-xs overflow-auto">
                                    {JSON.stringify(changeLog, null, 2)}
                                </pre>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 