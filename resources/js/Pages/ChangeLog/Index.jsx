import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';

export default function Index({ auth, changeLogs, filters, students, subjects }) {
    const [formData, setFormData] = useState({
        model_type: filters.model_type || '',
        action: filters.action || '',
        student_id: filters.student_id || '',
        subject_id: filters.subject_id || '',
    });

    const handleFilterChange = (field, value) => {
        setFormData(prev => ({ ...prev, [field]: value }));
    };

    const applyFilters = () => {
        router.get(route('change-log.index'), formData, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setFormData({
            model_type: '',
            action: '',
            student_id: '',
            subject_id: '',
        });
        router.get(route('change-log.index'), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

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

    const safeChangeLogs = changeLogs || { data: [], links: [], from: 0, to: 0, total: 0 };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Historial de Cambios</h2>}
        >
            <Head title="Historial de Cambios" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Filtros */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <InputLabel htmlFor="model_type" value="Tipo de Registro" />
                                    <select
                                        id="model_type"
                                        value={formData.model_type}
                                        onChange={(e) => handleFilterChange('model_type', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Todos</option>
                                        <option value="App\Models\Grade">Calificaciones</option>
                                        <option value="App\Models\Attendance">Asistencias</option>
                                    </select>
                                </div>

                                <div>
                                    <InputLabel htmlFor="action" value="Acción" />
                                    <select
                                        id="action"
                                        value={formData.action}
                                        onChange={(e) => handleFilterChange('action', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Todas</option>
                                        <option value="create">Creación</option>
                                        <option value="update">Modificación</option>
                                        <option value="delete">Eliminación</option>
                                    </select>
                                </div>

                                <div>
                                    <InputLabel htmlFor="student_id" value="Estudiante" />
                                    <select
                                        id="student_id"
                                        value={formData.student_id}
                                        onChange={(e) => handleFilterChange('student_id', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Todos</option>
                                        {students.map((student) => (
                                            <option key={student.id} value={student.id}>
                                                {student.nombre} {student.apellido_paterno}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <InputLabel htmlFor="subject_id" value="Materia" />
                                    <select
                                        id="subject_id"
                                        value={formData.subject_id}
                                        onChange={(e) => handleFilterChange('subject_id', e.target.value)}
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Todas</option>
                                        {subjects.map((subject) => (
                                            <option key={subject.id} value={subject.id}>
                                                {subject.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div className="flex gap-2 mt-4">
                                <PrimaryButton onClick={applyFilters}>
                                    Aplicar Filtros
                                </PrimaryButton>
                                <SecondaryButton onClick={clearFilters}>
                                    Limpiar Filtros
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>

                    {/* Tabla de cambios */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Fecha
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Usuario
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tipo
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Acción
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Estudiante
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Materia
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Acciones
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {safeChangeLogs.data && safeChangeLogs.data.length > 0 ? (
                                            safeChangeLogs.data.map((log) => (
                                                <tr key={log.id} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {new Date(log.created_at).toLocaleString('es-ES')}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {log.user?.name || 'Usuario eliminado'}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {getModelTypeLabel(log.model_type)}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getActionBadge(log.action)}`}>
                                                            {log.action === 'update' ? 'Modificación' : log.action === 'delete' ? 'Eliminación' : log.action === 'create' ? 'Creación' : log.action}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {log.model_info?.student_name || 'N/A'}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {log.model_info?.subject_name || 'N/A'}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <Link
                                                            href={route('change-log.show', log.id)}
                                                            className="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Ver Detalles
                                                        </Link>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="7" className="px-6 py-4 text-center text-sm text-gray-500">
                                                    No hay registros de cambios para mostrar.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Paginación */}
                            {safeChangeLogs.links && safeChangeLogs.data && safeChangeLogs.data.length > 0 && (
                                <div className="mt-6">
                                    <nav className="flex items-center justify-between">
                                        <div className="flex-1 flex justify-between sm:hidden">
                                            {safeChangeLogs.prev_page_url && (
                                                <Link
                                                    href={safeChangeLogs.prev_page_url}
                                                    className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                                >
                                                    Anterior
                                                </Link>
                                            )}
                                            {safeChangeLogs.next_page_url && (
                                                <Link
                                                    href={safeChangeLogs.next_page_url}
                                                    className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                                >
                                                    Siguiente
                                                </Link>
                                            )}
                                        </div>
                                        <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                            <div>
                                                <p className="text-sm text-gray-700">
                                                    Mostrando{' '}
                                                    <span className="font-medium">{safeChangeLogs.from || 0}</span>
                                                    {' '}a{' '}
                                                    <span className="font-medium">{safeChangeLogs.to || 0}</span>
                                                    {' '}de{' '}
                                                    <span className="font-medium">{safeChangeLogs.total || 0}</span>
                                                    {' '}resultados
                                                </p>
                                            </div>
                                            <div>
                                                <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                                    {safeChangeLogs.links.map((link, index) => (
                                                        link.url ? (
                                                            <Link
                                                                key={index}
                                                                href={link.url}
                                                                className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                                                                    link.active
                                                                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                                                }`}
                                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                                            />
                                                        ) : (
                                                            <span
                                                                key={index}
                                                                className={
                                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium cursor-not-allowed opacity-50 bg-white border-gray-300 text-gray-500'
                                                                }
                                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                                            />
                                                        )
                                                    ))}
                                                </nav>
                                            </div>
                                        </div>
                                    </nav>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 