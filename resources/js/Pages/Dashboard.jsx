import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import StudentsModule from '@/Components/Dashboard/StudentsModule';
import AttendanceModule from '@/Components/Dashboard/AttendanceModule';
import GradesModule from '@/Components/Dashboard/GradesModule';
import ScheduleModule from '@/Components/Dashboard/ScheduleModule';
import AlertsModule from '@/Components/Dashboard/AlertsModule';
import EventsModule from '@/Components/Dashboard/EventsModule';
import CalendarModule from '@/Components/Dashboard/CalendarModule';
import CurrentClassModule from '@/Components/Dashboard/CurrentClassModule';
import MainStats from '@/Components/Dashboard/MainStats';
import CsrfTest from '@/Components/CsrfTest';

export default function Dashboard({ auth, stats }) {
    const [activeModule, setActiveModule] = useState('main');

    const modules = [
        { id: 'main', name: 'Dashboard Principal', icon: '📊' },
        { id: 'students', name: 'Sistema de Alumnos', icon: '🎓' },
        { id: 'attendance', name: 'Sistema de Asistencias', icon: '📆' },
        { id: 'grades', name: 'Sistema de Calificaciones', icon: '📝', href: '/grades' },
        { id: 'invoices', name: 'Facturación', icon: '💳', href: '/invoices' },
        { id: 'subscriptions', name: 'Suscripciones', icon: '🔐', href: '/subscription/admin' },
        { id: 'schedule', name: 'Sistema de Horarios', icon: '🕒' },
    ];

    const handleModuleChange = (moduleId) => {
        setActiveModule(moduleId);
    };

    const renderActiveModule = () => {
        switch (activeModule) {
            case 'main':
                return (
                    <div className="space-y-6">
                        <MainStats stats={stats} />
                        <CsrfTest />
                    </div>
                );
            case 'students':
                return <StudentsModule stats={stats} />;
            case 'attendance':
                return <AttendanceModule stats={stats} />;
            case 'schedule':
                return <ScheduleModule 
                    schedules={stats.schedules}
                    groups={stats.groups}
                    subjects={stats.subjects}
                    teachers={stats.teachers}
                />;
            default:
                return (
                    <div className="space-y-6">
                        <MainStats stats={stats} />
                        <CsrfTest />
                    </div>
                );
        }
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex gap-2 mb-8 overflow-x-auto whitespace-nowrap scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 pr-2" style={{ WebkitOverflowScrolling: 'touch' }}>
                                {modules.map(module => (
                                    module.href ? (
                                        <Link
                                            key={module.id}
                                            href={module.href}
                                            className={`px-4 py-2 rounded-lg transition-colors flex items-center gap-2 bg-white text-gray-700 hover:bg-gray-50`}
                                        >
                                            <span className="mr-2">{module.icon}</span>
                                            {module.name}
                                        </Link>
                                    ) : (
                                        <button
                                            key={module.id}
                                            onClick={() => handleModuleChange(module.id)}
                                            className={`px-4 py-2 rounded-lg transition-colors flex items-center gap-2 ${
                                                activeModule === module.id
                                                    ? 'bg-green-600 text-white'
                                                    : 'bg-white text-gray-700 hover:bg-gray-50'
                                            }`}
                                        >
                                            <span className="mr-2">{module.icon}</span>
                                            {module.name}
                                        </button>
                                    )
                                ))}
                            </div>
                            {renderActiveModule()}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
