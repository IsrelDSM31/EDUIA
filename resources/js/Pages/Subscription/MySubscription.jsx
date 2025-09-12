import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { CalendarIcon, CreditCardIcon, ClockIcon } from '@heroicons/react/24/outline';

export default function MySubscription({ auth, subscription }) {
    if (!subscription) {
        return (
            <AuthenticatedLayout user={auth.user}>
                <Head title="Mi Suscripción" />

                <div className="py-12">
                    <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6 text-gray-900 text-center">
                                <h1 className="text-3xl font-bold text-gray-900 mb-4">
                                    Mi Suscripción
                                </h1>
                                
                                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                                    <h3 className="font-semibold text-yellow-900 mb-2">
                                        No tienes una suscripción activa
                                    </h3>
                                    <p className="text-yellow-700 mb-4">
                                        Para acceder a todas las funcionalidades del sistema, necesitas una suscripción activa.
                                    </p>
                                    
                                    <Link
                                        href={route('subscription.plans')}
                                        className="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors inline-block"
                                    >
                                        Ver Planes Disponibles
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Mi Suscripción" />

            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="text-center mb-8">
                                <h1 className="text-3xl font-bold text-gray-900 mb-4">
                                    Mi Suscripción
                                </h1>
                            </div>

                            <div className="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-xl font-semibold text-blue-900">
                                        Plan {subscription.plan_type === 'monthly' ? 'Mensual' : 'Anual'}
                                    </h3>
                                    <span className={`px-3 py-1 rounded-full text-sm font-semibold ${
                                        subscription.is_active 
                                            ? 'bg-green-100 text-green-800' 
                                            : 'bg-red-100 text-red-800'
                                    }`}>
                                        {subscription.is_active ? 'Activa' : 'Expirada'}
                                    </span>
                                </div>

                                <div className="space-y-3">
                                    <div className="flex items-center">
                                        <CreditCardIcon className="h-5 w-5 text-blue-500 mr-3" />
                                        <span className="text-gray-600">Monto:</span>
                                        <span className="ml-auto font-semibold">${subscription.amount}</span>
                                    </div>
                                    
                                    <div className="flex items-center">
                                        <CalendarIcon className="h-5 w-5 text-blue-500 mr-3" />
                                        <span className="text-gray-600">Inicio:</span>
                                        <span className="ml-auto font-semibold">{subscription.start_date}</span>
                                    </div>
                                    
                                    <div className="flex items-center">
                                        <CalendarIcon className="h-5 w-5 text-blue-500 mr-3" />
                                        <span className="text-gray-600">Fin:</span>
                                        <span className="ml-auto font-semibold">{subscription.end_date}</span>
                                    </div>
                                    
                                    <div className="flex items-center">
                                        <ClockIcon className="h-5 w-5 text-blue-500 mr-3" />
                                        <span className="text-gray-600">Días restantes:</span>
                                        <span className={`ml-auto font-semibold ${
                                            subscription.days_remaining > 7 ? 'text-green-600' : 'text-red-600'
                                        }`}>
                                            {subscription.days_remaining} días
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {subscription.days_remaining <= 7 && subscription.is_active && (
                                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                                    <h4 className="font-semibold text-yellow-900 mb-2">
                                        Tu suscripción expira pronto
                                    </h4>
                                    <p className="text-yellow-700 mb-3">
                                        Renueva tu suscripción para mantener el acceso al sistema.
                                    </p>
                                    <Link
                                        href={route('subscription.plans')}
                                        className="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors inline-block"
                                    >
                                        Renovar Suscripción
                                    </Link>
                                </div>
                            )}

                            <div className="space-y-3">
                                <Link
                                    href={route('dashboard')}
                                    className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors inline-block text-center"
                                >
                                    Ir al Dashboard
                                </Link>
                                
                                <Link
                                    href={route('subscription.plans')}
                                    className="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg transition-colors inline-block text-center"
                                >
                                    Ver Otros Planes
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 