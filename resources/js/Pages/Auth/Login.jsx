import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import ApplicationLogo from '@/Components/ApplicationLogo';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <GuestLayout>
            <Head title="Iniciar Sesión" />

            <div className="w-full max-w-sm mx-auto">
                <div className="text-center mb-8">
                    <ApplicationLogo className="h-20 w-auto mx-auto mb-4" />
                    <h2 className="text-2xl font-bold text-[#1E88E5]">EDUAI</h2>
                    <p className="text-gray-600 mt-2">Inicia sesión para continuar</p>
                </div>

                {status && (
                    <div className="mb-4 text-sm font-medium text-green-600">
                        {status}
                    </div>
                )}

                <form onSubmit={submit} className="space-y-6">
                    <div>
                        <div className="relative">
                            <span className="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </span>
                            <input
                                type="email"
                                name="email"
                                value={data.email}
                                placeholder="Correo electrónico"
                                className="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#1E88E5] focus:border-transparent text-black"
                                onChange={e => setData('email', e.target.value)}
                            />
                        </div>
                        {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                    </div>

                    <div>
                        <div className="relative">
                            <span className="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clipRule="evenodd" />
                                </svg>
                            </span>
                            <input
                                type="password"
                                name="password"
                                value={data.password}
                                placeholder="Contraseña"
                                className="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#1E88E5] focus:border-transparent text-black"
                                onChange={e => setData('password', e.target.value)}
                            />
                            <button
                                type="button"
                                className="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500"
                                onClick={() => {
                                    const input = document.querySelector('input[name="password"]');
                                    input.type = input.type === 'password' ? 'text' : 'password';
                                }}
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fillRule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clipRule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-[#1E88E5] text-white py-2 px-4 rounded-lg hover:bg-[#1976D2] focus:outline-none focus:ring-2 focus:ring-[#1E88E5] focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Iniciar Sesión
                    </button>
                </form>
            </div>
        </GuestLayout>
    );
}
