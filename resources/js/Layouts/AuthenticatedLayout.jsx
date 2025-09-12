import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import PWAInstallButton from '@/Components/PWAInstallButton';
import { Link } from '@inertiajs/react';
import { Toaster } from 'react-hot-toast';
import ChatBot from '@/Components/ChatBot';
import AdminGlobalNotifications from '@/Components/AdminGlobalNotifications';

export default function Authenticated({ header, children }) {
    const { auth } = usePage().props;
    const user = auth?.user;
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
    const [showNotifications, setShowNotifications] = useState(false);
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);

    useEffect(() => {
        if (user && Array.isArray(user.notifications)) {
            setNotifications(user.notifications);
            setUnreadCount(user.notifications.length);
        } else {
            setNotifications([]);
            setUnreadCount(0);
        }
    }, [user]);

    const handleShowNotifications = async () => {
        if (!showNotifications && unreadCount > 0) {
            await fetch(route('notifications.markAsRead'), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
            });
            setNotifications([]);
            setUnreadCount(0);
        }
        setShowNotifications(!showNotifications);
    };

    if (!user) return null;

    return (
        <div className="min-h-screen">
            <Toaster position="top-right" />
            <PWAInstallButton />
            <nav className="bg-white border-b border-gray-100 shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16 items-center">
                        <div className="flex items-center">
                            <div className="shrink-0 flex items-center">
                                <Link href="/">
                                    <ApplicationLogo className="block h-9 w-auto fill-current text-[var(--primary-color)]" />
                                </Link>
                            </div>
                            <div className="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex items-center">
                                <NavLink href={route('dashboard')} active={route().current('dashboard')} className="nav-link">
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    href={route('risk.analysis')}
                                    active={route().current('risk.analysis')}
                                    className=""
                                >
                                    Análisis de Riesgo
                                </NavLink>
                                <div className="relative flex items-center ml-2">
                                    <button
                                        className="relative focus:outline-none"
                                        onClick={handleShowNotifications}
                                        aria-label="Ver notificaciones"
                                        style={{ background: 'none', border: 'none', padding: 0 }}
                                    >
                                        <svg className={`w-8 h-8 ${unreadCount > 0 ? 'text-blue-500 animate-bounce' : 'text-gray-400'}`} fill="none" stroke="currentColor" strokeWidth="2" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        <span className="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-sm font-bold leading-none text-white bg-blue-600 rounded-full shadow-lg border-2 border-white">
                                            {unreadCount}
                                        </span>
                                    </button>
                                    {showNotifications && (
                                        <div className="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                            <div className="py-2 px-4 border-b font-semibold text-gray-700">Notificaciones</div>
                                            <div className="max-h-80 overflow-y-auto">
                                                {notifications.length === 0 ? (
                                                    <div className="px-4 py-6 text-gray-500 text-center">No tienes notificaciones nuevas.</div>
                                                ) : notifications.map(n => (
                                                    <div key={n.id} className="px-4 py-3 border-b last:border-b-0 hover:bg-gray-50">
                                                        <div className="text-sm text-gray-800">{n.data.message}</div>
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
                            </div>
                        </div>
                        <div className="flex items-center gap-4">
                            <div className="ml-3 relative">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {user.name}
                                                <svg
                                                    className="ml-2 -mr-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>
                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('profile.edit')} className="nav-link">
                                            Perfil
                                        </Dropdown.Link>
                                        <Dropdown.Link href={route('logout')} method="post" as="button" className="nav-link text-red-600 hover:text-red-700">
                                            Cerrar Sesión
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                            {user.role === 'admin' && (
                                <AdminGlobalNotifications />
                            )}
                        </div>
                        <div className="-mr-2 flex items-center sm:hidden">
                            <button
                                onClick={() => setShowingNavigationDropdown((previousState) => !previousState)}
                                className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        className={!showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div className={(showingNavigationDropdown ? 'block' : 'hidden') + ' sm:hidden'}>
                    <div className="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink href={route('dashboard')} active={route().current('dashboard')}>
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href={route('risk.analysis')} active={route().current('risk.analysis')}>
                            Análisis de Riesgo
                        </ResponsiveNavLink>
                        {/* Historial de Cambios eliminado del menú móvil */}
                    </div>

                    <div className="pt-4 pb-1 border-t border-gray-200">
                        <div className="px-4">
                            <div className="font-medium text-base text-gray-800">{user.name}</div>
                            <div className="font-medium text-sm text-gray-500">{user.email}</div>
                        </div>

                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')}>Perfil</ResponsiveNavLink>
                            <ResponsiveNavLink method="post" href={route('logout')} as="button">
                                Cerrar Sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-white shadow">
                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{header}</div>
                </header>
            )}

            <main className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">{children}</div>
                    </div>
                </div>
            </main>

            <footer className="bg-white border-t border-gray-100 shadow-sm mt-auto">
                <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <div className="text-center text-sm text-gray-500">
                        © {new Date().getFullYear()} IAEDU. Todos los derechos reservados.
                    </div>
                </div>
            </footer>

            <ChatBot />
        </div>
    );
}
