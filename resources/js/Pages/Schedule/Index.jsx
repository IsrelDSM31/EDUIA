import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ScheduleModule from '@/Components/Dashboard/ScheduleModule';
import { Head, Link } from '@inertiajs/react';

export default function ScheduleIndex({ auth, schedules, groups, subjects, teachers }) {
    const modules = [
        { id: 'main', name: 'Dashboard Principal', icon: '📊', href: '/dashboard' },
        { id: 'attendance', name: 'Sistema de Asistencias', icon: '📆', href: '/attendance' },
        { id: 'grades', name: 'Sistema de Calificaciones', icon: '📝', href: '/grades' },
        { id: 'schedule', name: 'Sistema de Horarios', icon: '🕒', href: '/schedule' },
        { id: 'students', name: 'Sistema de Alumnos', icon: '🎓', href: '/students' },
        { id: 'alerts', name: 'Sistema de Alertas', icon: '🚨', href: '/alerts' },
    ];

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Sistema de Horarios" />
            <div className="py-6">
                <nav className="flex gap-2 mb-8">
                    {modules.map(module => (
                        <Link
                            key={module.id}
                            href={module.href}
                            className={`px-4 py-2 rounded-lg transition-colors flex items-center gap-2 ${
                                module.id === 'schedule'
                                    ? 'bg-green-600 text-white'
                                    : 'bg-white text-gray-700 hover:bg-gray-50'
                            }`}
                        >
                            <span>{module.icon}</span>
                            <span>{module.name}</span>
                        </Link>
                    ))}
                </nav>
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <ScheduleModule 
                        schedules={schedules}
                        groups={groups}
                        subjects={subjects}
                        teachers={teachers}
                    />
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 