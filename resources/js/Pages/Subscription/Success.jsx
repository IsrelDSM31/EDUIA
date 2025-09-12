import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { CheckCircleIcon } from '@heroicons/react/24/outline';

export default function Success({ auth, subscription }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Suscripción Activada" />

            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 text-center">
                            <div className="mb-6">
                                <CheckCircleIcon className="h-16 w-16 text-green-500 mx-auto mb-4" />
                                <h1 className="text-3xl font-bold text-gray-900 mb-4">
                                    ¡Suscripción Activada!
                                </h1>
                                <p className="text-lg text-gray-600">
                                    Tu suscripción ha sido procesada exitosamente
                                </p>
                            </div>

                            <div className="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                                <h3 className="font-semibold text-green-900 mb-4">
                                    Detalles de tu Suscripción
                                </h3>
                                <div className="space-y-2 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Plan:</span>
                                        <span className="font-semibold">
                                            {subscription.plan_type === 'monthly' ? 'Mensual' : 'Anual'}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Monto:</span>
                                        <span className="font-semibold">${subscription.amount}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Fecha de inicio:</span>
                                        <span className="font-semibold">{subscription.start_date}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Fecha de fin:</span>
                                        <span className="font-semibold">{subscription.end_date}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">Días restantes:</span>
                                        <span className="font-semibold text-green-600">
                                            {subscription.days_remaining} días
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <Link
                                    href={route('dashboard')}
                                    className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors inline-block"
                                >
                                    Ir al Dashboard
                                </Link>
                                
                                <Link
                                    href={route('subscription.my-subscription')}
                                    className="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg transition-colors inline-block"
                                >
                                    Ver mi Suscripción
                                </Link>
                            </div>

                            <div className="mt-8 text-sm text-gray-500">
                                <p>
                                    Recibirás un correo de confirmación con los detalles de tu suscripción.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 