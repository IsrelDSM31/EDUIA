import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { CheckIcon } from '@heroicons/react/24/outline';

export default function Plans({ auth, plans }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Planes de Suscripción" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="text-center mb-8">
                                <h1 className="text-3xl font-bold text-gray-900 mb-4">
                                    Elige tu Plan de Suscripción
                                </h1>
                                <p className="text-lg text-gray-600">
                                    Accede a todas las funcionalidades del sistema educativo
                                </p>
                            </div>

                            <div className="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                                {plans.map((plan) => (
                                    <div key={plan.id} className="relative bg-white border-2 border-gray-200 rounded-lg p-8 shadow-lg hover:shadow-xl transition-shadow">
                                        {plan.id === 'yearly' && (
                                            <div className="absolute -top-4 left-1/2 transform -translate-x-1/2">
                                                <span className="bg-green-500 text-white px-4 py-2 rounded-full text-sm font-semibold">
                                                    Más Popular
                                                </span>
                                            </div>
                                        )}
                                        
                                        <div className="text-center">
                                            <h3 className="text-2xl font-bold text-gray-900 mb-2">
                                                {plan.name}
                                            </h3>
                                            <div className="mb-6">
                                                <span className="text-4xl font-bold text-blue-600">
                                                    ${plan.price}
                                                </span>
                                                <span className="text-gray-500 ml-2">/{plan.period}</span>
                                            </div>
                                            
                                            <ul className="space-y-3 mb-8">
                                                {plan.features.map((feature, index) => (
                                                    <li key={index} className="flex items-center">
                                                        <CheckIcon className="h-5 w-5 text-green-500 mr-3 flex-shrink-0" />
                                                        <span className="text-gray-700">{feature}</span>
                                                    </li>
                                                ))}
                                            </ul>
                                            
                                            <Link
                                                href={route('subscription.checkout', { plan_type: plan.id })}
                                                className="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors inline-block text-center"
                                            >
                                                Seleccionar Plan
                                            </Link>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="mt-12 text-center">
                                <p className="text-gray-600 mb-4">
                                    ¿Ya tienes una suscripción?
                                </p>
                                <Link
                                    href={route('subscription.my-subscription')}
                                    className="text-blue-600 hover:text-blue-700 font-semibold"
                                >
                                    Ver mi suscripción
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 