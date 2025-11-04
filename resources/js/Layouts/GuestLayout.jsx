import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import ChatBot from '@/Components/ChatBot';

export default function GuestLayout({ children }) {
    return (
        <div 
            className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" 
            style={{ 
                background: 'linear-gradient(180deg, #FFD6A5 0%, #FF61A6 100%)',
                minHeight: '100vh',
                width: '100%'
            }}
        >
            <div 
                className="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-lg overflow-hidden sm:rounded-lg" 
                style={{ 
                    borderRadius: '12px', 
                    boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
                    maxWidth: '28rem',
                    margin: '0 auto'
                }}
            >
                {children}
            </div>
            <ChatBot />
        </div>
    );
}
