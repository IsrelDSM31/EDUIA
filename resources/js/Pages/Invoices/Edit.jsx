import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';

export default function InvoicesEdit({ auth, invoice, users }) {
    const { data, setData, put, processing, errors } = useForm({
        user_id: invoice.user_id || '',
        number: invoice.number || '',
        amount: invoice.amount || '',
        due_date: invoice.due_date || '',
        description: invoice.description || '',
        status: invoice.status || 'pending',
    });
    const submit = e => {
        e.preventDefault();
        put(route('invoices.update', invoice.id));
    };
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={`Editar Factura #${invoice.number}`} />
            <div className="py-12">
                <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div className="p-6 text-gray-900">
                        <div className="flex justify-between items-center mb-6">
                            <h2 className="text-2xl font-semibold">Editar Factura #{invoice.number}</h2>
                            <Link href={route('invoices.index')} className="text-blue-600 hover:underline">Volver</Link>
                        </div>
                        <form onSubmit={submit} className="space-y-6">
                            <div>
                                <InputLabel htmlFor="user_id" value="Usuario" />
                                <select id="user_id" value={data.user_id} onChange={e => setData('user_id', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Seleccionar usuario</option>
                                    {users.map(u => <option key={u.id} value={u.id}>{u.name} ({u.email})</option>)}
                                </select>
                                <InputError message={errors.user_id} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="number" value="Número de Factura" />
                                <TextInput id="number" value={data.number} readOnly className="mt-1 block w-full bg-gray-100" />
                                <InputError message={errors.number} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="amount" value="Monto" />
                                <TextInput id="amount" type="number" value={data.amount} onChange={e => setData('amount', e.target.value)} className="mt-1 block w-full" required min="0" step="0.01" />
                                <InputError message={errors.amount} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="due_date" value="Fecha de Vencimiento" />
                                <TextInput id="due_date" type="date" value={data.due_date} onChange={e => setData('due_date', e.target.value)} className="mt-1 block w-full" required />
                                <InputError message={errors.due_date} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="description" value="Descripción" />
                                <textarea id="description" value={data.description} onChange={e => setData('description', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows={3} />
                                <InputError message={errors.description} className="mt-2" />
                            </div>
                            <div>
                                <InputLabel htmlFor="status" value="Estado" />
                                <select id="status" value={data.status} onChange={e => setData('status', e.target.value)} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="pending">Pendiente</option>
                                    <option value="paid">Pagada</option>
                                    <option value="cancelled">Cancelada</option>
                                </select>
                                <InputError message={errors.status} className="mt-2" />
                            </div>
                            <div className="flex justify-end">
                                <PrimaryButton disabled={processing}>{processing ? 'Guardando...' : 'Guardar Cambios'}</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 