import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { CalendarIcon, CreditCardIcon, UserIcon } from '@heroicons/react/24/outline';

export default function AdminIndex({ auth, subscriptions }) {
    const getStatusColor = (status) => {
        switch (status) {
            case 'active': return 'bg-green-100 text-green-800';
            case 'expired': return 'bg-red-100 text-red-800';
            case 'cancelled': return 'bg-gray-100 text-gray-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getPlanTypeLabel = (type) => {
        return type === 'monthly' ? 'Mensual' : 'Anual';
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Gestión de Suscripciones" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-center mb-8">
                                <h1 className="text-3xl font-bold text-gray-900">
                                    Gestión de Suscripciones
                                </h1>
                                <div className="text-sm text-gray-600">
                                    Total: {subscriptions.length} suscripciones
                                </div>
                            </div>

                            {subscriptions.length === 0 ? (
                                <div className="text-center py-12">
                                    <p className="text-gray-500 text-lg">
                                        No hay suscripciones registradas
                                    </p>
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Usuario
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Plan
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Monto
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Estado
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Fechas
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Pago
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {subscriptions.map((subscription) => (
                                                <tr key={subscription.id} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="flex items-center">
                                                            <UserIcon className="h-5 w-5 text-gray-400 mr-3" />
                                                            <div>
                                                                <div className="text-sm font-medium text-gray-900">
                                                                    {subscription.user_name}
                                                                </div>
                                                                <div className="text-sm text-gray-500">
                                                                    {subscription.user_email}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {getPlanTypeLabel(subscription.plan_type)}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        ${subscription.amount}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(subscription.status)}`}>
                                                            {subscription.status === 'active' ? 'Activa' : 
                                                             subscription.status === 'expired' ? 'Expirada' : 
                                                             subscription.status === 'cancelled' ? 'Cancelada' : subscription.status}
                                                        </span>
                                                        {subscription.days_remaining > 0 && (
                                                            <div className="text-xs text-gray-500 mt-1">
                                                                {subscription.days_remaining} días restantes
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="flex items-center">
                                                            <CalendarIcon className="h-4 w-4 text-gray-400 mr-2" />
                                                            <div className="text-sm text-gray-900">
                                                                <div>Inicio: {subscription.start_date}</div>
                                                                <div>Fin: {subscription.end_date}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm text-gray-900">
                                                            <div className="flex items-center">
                                                                <CreditCardIcon className="h-4 w-4 text-gray-400 mr-2" />
                                                                {subscription.payment_method}
                                                            </div>
                                                            <div className="text-xs text-gray-500 mt-1">
                                                                {subscription.transaction_id}
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 