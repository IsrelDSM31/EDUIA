import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';

export default function PaymentsCreate({ auth, invoice }) {
    const { data, setData, post, processing, errors } = useForm({
        invoice_id: invoice.id,
        amount: '',
        payment_date: '',
        method: '',
        reference: '',
        description: '',
    });
    const submit = e => { e.preventDefault(); post(route('payments.store')); };
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Registrar Pago" />
            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h2 className="text-2xl font-semibold mb-6">Registrar Pago para Factura #{invoice.number}</h2>
                            <form onSubmit={submit} className="space-y-6">
                                <div>
                                    <InputLabel htmlFor="amount" value="Monto" />
                                    <TextInput id="amount" type="number" name="amount" value={data.amount} onChange={e=>setData('amount',e.target.value)} className="mt-1 block w-full" required min="0" step="0.01" />
                                    <InputError message={errors.amount} className="mt-2" />
                                </div>
                                <div>
                                    <InputLabel htmlFor="payment_date" value="Fecha de Pago" />
                                    <TextInput id="payment_date" type="date" name="payment_date" value={data.payment_date} onChange={e=>setData('payment_date',e.target.value)} className="mt-1 block w-full" required />
                                    <InputError message={errors.payment_date} className="mt-2" />
                                </div>
                                <div>
                                    <InputLabel htmlFor="method" value="Método de Pago" />
                                    <TextInput id="method" type="text" name="method" value={data.method} onChange={e=>setData('method',e.target.value)} className="mt-1 block w-full" />
                                    <InputError message={errors.method} className="mt-2" />
                                </div>
                                <div>
                                    <InputLabel htmlFor="reference" value="Referencia" />
                                    <TextInput id="reference" type="text" name="reference" value={data.reference} onChange={e=>setData('reference',e.target.value)} className="mt-1 block w-full" />
                                    <InputError message={errors.reference} className="mt-2" />
                                </div>
                                <div>
                                    <InputLabel htmlFor="description" value="Descripción" />
                                    <textarea id="description" name="description" value={data.description} onChange={e=>setData('description',e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md" rows={3} />
                                    <InputError message={errors.description} className="mt-2" />
                                </div>
                                <div className="flex justify-end">
                                    <PrimaryButton disabled={processing}>{processing ? 'Registrando...' : 'Registrar Pago'}</PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 