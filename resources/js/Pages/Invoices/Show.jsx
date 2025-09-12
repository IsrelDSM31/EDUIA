import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';

export default function InvoiceShow({ auth, invoice }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        amount: '',
        payment_date: '',
        method: '',
        reference: '',
        description: '',
    });
    const submit = e => {
        e.preventDefault();
        post(route('payments.store'), {
            onSuccess: () => reset(),
        });
    };
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Factura #${invoice.number}`} />
            <div className="py-12">
                <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-semibold">Factura #{invoice.number}</h2>
                                <Link href={route('invoices.index')} className="text-blue-600 hover:underline">Volver</Link>
                            </div>
                            <div className="mb-4">
                                <p><b>Usuario:</b> {invoice.user_name}</p>
                                <p><b>Monto:</b> ${invoice.amount}</p>
                                <p><b>Vencimiento:</b> {invoice.due_date}</p>
                                <p><b>Estado:</b> <span className={`px-2 py-1 rounded text-xs font-bold ${invoice.status === 'paid' ? 'bg-green-100 text-green-700' : invoice.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'}`}>{invoice.status}</span></p>
                                <p><b>Descripción:</b> {invoice.description}</p>
                                {invoice.pdf_path && <a href={`/storage/${invoice.pdf_path}`} className="text-indigo-600 hover:underline" target="_blank" rel="noopener noreferrer">Descargar PDF</a>}
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Pagos</h3>
                            <div className="mb-6">
                                {invoice.payments.length === 0 ? <p className="text-gray-500">No hay pagos registrados.</p> : (
                                    <table className="min-w-full divide-y divide-gray-200 mb-4">
                                        <thead>
                                            <tr>
                                                <th className="px-4 py-2">Fecha</th>
                                                <th className="px-4 py-2">Monto</th>
                                                <th className="px-4 py-2">Método</th>
                                                <th className="px-4 py-2">Referencia</th>
                                                <th className="px-4 py-2">Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {invoice.payments.map(p => (
                                                <tr key={p.id}>
                                                    <td className="px-4 py-2">{p.payment_date}</td>
                                                    <td className="px-4 py-2">${p.amount}</td>
                                                    <td className="px-4 py-2">{p.method}</td>
                                                    <td className="px-4 py-2">{p.reference}</td>
                                                    <td className="px-4 py-2">{p.description}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                )}
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Registrar Pago</h3>
                            <form onSubmit={submit} className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel htmlFor="amount" value="Monto" />
                                        <TextInput id="amount" type="number" value={data.amount} onChange={e => setData('amount', e.target.value)} className="mt-1 block w-full" required min="0" step="0.01" />
                                        <InputError message={errors.amount} className="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel htmlFor="payment_date" value="Fecha de Pago" />
                                        <TextInput id="payment_date" type="date" value={data.payment_date} onChange={e => setData('payment_date', e.target.value)} className="mt-1 block w-full" required />
                                        <InputError message={errors.payment_date} className="mt-2" />
                                    </div>
                                </div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel htmlFor="method" value="Método" />
                                        <TextInput id="method" value={data.method} onChange={e => setData('method', e.target.value)} className="mt-1 block w-full" />
                                        <InputError message={errors.method} className="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel htmlFor="reference" value="Referencia" />
                                        <TextInput id="reference" value={data.reference} onChange={e => setData('reference', e.target.value)} className="mt-1 block w-full" />
                                        <InputError message={errors.reference} className="mt-2" />
                                    </div>
                                </div>
                                <div>
                                    <InputLabel htmlFor="description" value="Descripción" />
                                    <textarea id="description" value={data.description} onChange={e => setData('description', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows={2} />
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