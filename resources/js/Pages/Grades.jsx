import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import GradesModule from '@/Components/Dashboard/GradesModule';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Grades({ auth, stats }) {
    const modules = [
        { id: 'main', name: 'Dashboard Principal', icon: '📊', href: '/dashboard' },
        { id: 'attendance', name: 'Sistema de Asistencias', icon: '📆', href: '/attendance' },
        { id: 'grades', name: 'Sistema de Calificaciones', icon: '📝', href: '/grades' },
        { id: 'schedule', name: 'Sistema de Horarios', icon: '🕒', href: '/schedule' },
        { id: 'students', name: 'Sistema de Alumnos', icon: '🎓', href: '/students' },
        { id: 'alerts', name: 'Sistema de Alertas', icon: '🚨', href: '/alerts' },
    ];

    const { grades, subjects, rubrics } = usePage().props;

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Sistema de Calificaciones" />
            <div className="py-6">
                <nav className="flex gap-2 mb-8">
                    {modules.map(module => (
                        <Link
                            key={module.id}
                            href={module.href}
                            className={`px-4 py-2 rounded-lg transition-colors flex items-center gap-2 ${
                                module.id === 'grades'
                                    ? 'bg-green-600 text-white'
                                    : 'bg-white text-gray-700 hover:bg-gray-50'
                            }`}
                        >
                            <span>{module.icon}</span>
                            <span>{module.name}</span>
                        </Link>
                    ))}
                </nav>
                <div className="w-full">
                    <GradesModule grades={grades} subjects={subjects} rubrics={rubrics} />
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 