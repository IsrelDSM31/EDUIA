import React, { useEffect } from 'react';

export default function Toast({ message, type = 'success', onClose }) {
    useEffect(() => {
        if (message) {
            const timer = setTimeout(() => {
                onClose();
            }, 3000);
            return () => clearTimeout(timer);
        }
    }, [message, onClose]);

    if (!message) return null;

    return (
        <div className={`fixed top-6 right-6 z-50 px-6 py-4 rounded shadow-lg text-white transition-all ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`}>
            {message}
        </div>
    );
} 