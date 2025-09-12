import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import ChatBot from '@/Components/ChatBot';

export default function GuestLayout({ children }) {
    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style={{ background: 'linear-gradient(to bottom, #ffd6a7, #ff7eb3)' }}>
            <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {children}
            </div>
            <ChatBot />
        </div>
    );
}
