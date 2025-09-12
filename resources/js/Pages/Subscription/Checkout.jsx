import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Checkout({ auth, plan }) {
    const { data, setData, post, processing, errors } = useForm({
        plan_type: plan.type,
        payment_method: 'credit_card',
        card_number: '',
        expiry_date: '',
        cvv: '',
    });

    const [cardType, setCardType] = useState('');

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('subscription.process-payment'));
    };

    const detectCardType = (number) => {
        const num = number.replace(/\s/g, '');
        if (/^4/.test(num)) return 'Visa';
        if (/^5[1-5]/.test(num)) return 'Mastercard';
        if (/^3[47]/.test(num)) return 'American Express';
        return '';
    };

    const formatCardNumber = (value) => {
        const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        const matches = v.match(/\d{4,16}/g);
        const match = matches && matches[0] || '';
        const parts = [];
        for (let i = 0, len = match.length; i < len; i += 4) {
            parts.push(match.substring(i, i + 4));
        }
        if (parts.length) {
            return parts.join(' ');
        } else {
            return v;
        }
    };

    const formatExpiryDate = (value) => {
        const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        if (v.length >= 2) {
            return v.substring(0, 2) + '/' + v.substring(2, 4);
        }
        return v;
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Checkout - Suscripción" />

            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="text-center mb-8">
                                <h1 className="text-3xl font-bold text-gray-900 mb-4">
                                    Completar Compra
                                </h1>
                                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h3 className="font-semibold text-blue-900">
                                        {plan.type === 'monthly' ? 'Plan Mensual' : 'Plan Anual'}
                                    </h3>
                                    <p className="text-blue-700">
                                        ${plan.price} - {plan.days} días de acceso
                                    </p>
                                </div>
                            </div>

                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Método de Pago
                                    </label>
                                    <select
                                        value={data.payment_method}
                                        onChange={(e) => setData('payment_method', e.target.value)}
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        <option value="credit_card">Tarjeta de Crédito/Débito</option>
                                        <option value="paypal">PayPal</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Número de Tarjeta
                                    </label>
                                    <div className="relative">
                                        <input
                                            type="text"
                                            value={data.card_number}
                                            onChange={(e) => {
                                                const formatted = formatCardNumber(e.target.value);
                                                setData('card_number', formatted);
                                                setCardType(detectCardType(formatted));
                                            }}
                                            placeholder="1234 5678 9012 3456"
                                            className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-12"
                                        />
                                        {cardType && (
                                            <div className="absolute right-3 top-1/2 transform -translate-y-1/2 text-sm text-gray-500">
                                                {cardType}
                                            </div>
                                        )}
                                    </div>
                                    {errors.card_number && (
                                        <p className="mt-1 text-sm text-red-600">{errors.card_number}</p>
                                    )}
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Fecha de Expiración
                                        </label>
                                        <input
                                            type="text"
                                            value={data.expiry_date}
                                            onChange={(e) => setData('expiry_date', formatExpiryDate(e.target.value))}
                                            placeholder="MM/YY"
                                            className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                        {errors.expiry_date && (
                                            <p className="mt-1 text-sm text-red-600">{errors.expiry_date}</p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            CVV
                                        </label>
                                        <input
                                            type="text"
                                            value={data.cvv}
                                            onChange={(e) => setData('cvv', e.target.value.replace(/\D/g, ''))}
                                            placeholder="123"
                                            className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                        {errors.cvv && (
                                            <p className="mt-1 text-sm text-red-600">{errors.cvv}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <h4 className="font-semibold text-gray-900 mb-2">Resumen de Compra</h4>
                                    <div className="flex justify-between text-sm">
                                        <span>Plan {plan.type === 'monthly' ? 'Mensual' : 'Anual'}</span>
                                        <span>${plan.price}</span>
                                    </div>
                                    <div className="border-t border-gray-200 mt-2 pt-2 flex justify-between font-semibold">
                                        <span>Total</span>
                                        <span>${plan.price}</span>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold py-3 px-6 rounded-lg transition-colors"
                                >
                                    {processing ? 'Procesando...' : 'Completar Compra'}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 