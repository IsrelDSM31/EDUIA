import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import { route } from 'ziggy-js';

export default function InvoicesIndex({ auth, invoices }) {
    const [filter, setFilter] = useState('');
    const [search, setSearch] = useState('');

    const filtered = invoices.filter(inv => {
        const statusMatch = !filter || inv.status === filter;
        const searchMatch = !search || inv.number.includes(search) || (inv.user_name && inv.user_name.toLowerCase().includes(search.toLowerCase()));
        return statusMatch && searchMatch;
    });

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Facturación" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-semibold">Facturas</h2>
                                <div className="flex gap-2 items-center">
                                    {auth.user && auth.user.role === 'admin' && (
                                        <button
                                            className="bg-indigo-600 text-white px-3 py-2 rounded hover:bg-indigo-700 transition"
                                            onClick={() => {
                                                if (window.confirm('¿Generar facturas para todas las suscripciones activas?')) {
                                                    router.post(route('invoices.generateFromSubscriptions'), {}, {
                                                        onSuccess: () => window.location.reload(),
                                                    });
                                                }
                                            }}
                                        >Generar Facturas de Suscripciones</button>
                                    )}
                                    <Link href={route('invoices.create')}>
                                        <PrimaryButton>Nueva Factura</PrimaryButton>
                                    </Link>
                                </div>
                            </div>
                            <div className="flex gap-4 mb-4">
                                <select value={filter} onChange={e => setFilter(e.target.value)} className="border rounded px-2 py-1">
                                    <option value="">Todos los estados</option>
                                    <option value="pending">Pendiente</option>
                                    <option value="paid">Pagada</option>
                                    <option value="cancelled">Cancelada</option>
                                </select>
                                <input type="text" placeholder="Buscar por número o usuario" value={search} onChange={e => setSearch(e.target.value)} className="border rounded px-2 py-1" />
                            </div>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th className="px-4 py-2">#</th>
                                            <th className="px-4 py-2">Usuario</th>
                                            <th className="px-4 py-2">Monto</th>
                                            <th className="px-4 py-2">Vencimiento</th>
                                            <th className="px-4 py-2">Estado</th>
                                            <th className="px-4 py-2">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {filtered.length === 0 ? (
                                            <tr><td colSpan={6} className="text-center py-8 text-gray-500">No hay facturas.</td></tr>
                                        ) : filtered.map(inv => (
                                            <tr key={inv.id} className="hover:bg-gray-50">
                                                <td className="px-4 py-2 font-mono">{inv.number}</td>
                                                <td className="px-4 py-2">{inv.user_name}</td>
                                                <td className="px-4 py-2">${inv.amount}</td>
                                                <td className="px-4 py-2">{inv.due_date}</td>
                                                <td className="px-4 py-2">
                                                    <span className={`px-2 py-1 rounded text-xs font-bold ${inv.status === 'paid' ? 'bg-green-100 text-green-700' : inv.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'}`}>{inv.status}</span>
                                                </td>
                                                <td className="px-4 py-2">
                                                    <Link href={route('invoices.show', inv.id)} className="text-blue-600 hover:underline mr-2">Ver</Link>
                                                    <Link href={route('invoices.edit', inv.id)} className="text-yellow-600 hover:underline mr-2">Editar</Link>
                                                    <button
                                                        className="text-red-600 hover:underline"
                                                        onClick={() => {
                                                            if (!inv.id) {
                                                                alert('ID de factura no válido.');
                                                                return;
                                                            }
                                                            if (window.confirm('¿Seguro que deseas eliminar esta factura?')) {
                                                                const deleteUrl = `/invoices/${inv.id}`;
                                                                console.log('Eliminando factura:', deleteUrl, 'ID:', inv.id);
                                                                router.delete(deleteUrl, {
                                                                    onSuccess: () => window.location.reload(),
                                                                });
                                                            }
                                                        }}
                                                    >Eliminar</button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
} 