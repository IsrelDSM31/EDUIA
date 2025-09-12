import { useEffect, useState } from 'react';
import { route } from 'ziggy-js';

export default function AdminGlobalNotifications() {
    const [show, setShow] = useState(false);
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);

    useEffect(() => {
        fetch('/admin/global-invoice-notifications')
            .then(res => res.json())
            .then(data => {
                setNotifications(data.notifications);
                setUnreadCount(data.notifications.filter(n => !n.read_at).length);
            });
    }, []);

    const handleShow = () => setShow(!show);

    return (
        <div className="relative flex items-center ml-2">
            <button
                className="relative focus:outline-none"
                onClick={handleShow}
                aria-label="Ver notificaciones globales"
                style={{ background: 'none', border: 'none', padding: 0 }}
            >
                <svg className={`w-8 h-8 ${unreadCount > 0 ? 'text-purple-500 animate-bounce' : 'text-gray-400'}`} fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span className="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-sm font-bold leading-none text-white bg-purple-600 rounded-full shadow-lg border-2 border-white">
                    {unreadCount}
                </span>
            </button>
            {show && (
                <div className="origin-top-right absolute right-0 mt-2 w-96 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                    <div className="py-2 px-4 border-b font-semibold text-gray-700">Notificaciones de Facturas (Global)</div>
                    <div className="max-h-96 overflow-y-auto">
                        {notifications.length === 0 ? (
                            <div className="px-4 py-6 text-gray-500 text-center">No hay notificaciones de facturas.</div>
                        ) : notifications.map(n => (
                            <div key={n.id} className="px-4 py-3 border-b last:border-b-0 hover:bg-gray-50">
                                <div className="text-sm text-gray-800">{n.data.message}</div>
                                <div className="text-xs text-gray-400">Usuario: {n.user_name}</div>
                                <div className="text-xs text-gray-400">{n.created_at}</div>
                                {n.data.invoice_id && (
                                    <a
                                        href={route('invoices.show', n.data.invoice_id)}
                                        className="text-blue-600 hover:underline text-xs mt-1 block"
                                    >
                                        Ver factura
                                    </a>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
} 