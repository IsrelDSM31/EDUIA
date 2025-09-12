import React, { useState } from 'react';
import { usePage } from '@inertiajs/react';

export default function CsrfTest() {
    let { csrf_token } = usePage().props;
    // Fallback: obtener del meta tag si no viene por props
    if (!csrf_token) {
        csrf_token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
    const [result, setResult] = useState('');
    const [loading, setLoading] = useState(false);

    const testCsrf = async () => {
        setResult('');
        setLoading(true);
        try {
            const response = await fetch('/test-csrf', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf_token,
                },
                body: JSON.stringify({ test: 'data' }),
            });
            if (response.status === 419) {
                setResult('❌ Error 419: Token CSRF inválido o expirado');
            } else if (response.ok) {
                const data = await response.json();
                setResult(`✅ Éxito: ${data.message}`);
            } else {
                setResult(`❌ Error ${response.status}: ${response.statusText}`);
            }
        } catch (error) {
            setResult(`❌ Error: ${error.message}`);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="p-4 bg-white rounded-lg shadow">
            <h3 className="text-lg font-semibold mb-4">Prueba de Token CSRF</h3>
            <div className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        Token CSRF:
                    </label>
                    <input
                        type="text"
                        value={csrf_token || 'No disponible'}
                        readOnly
                        className="w-full p-2 border rounded bg-gray-50 text-sm font-mono"
                    />
                </div>
                <button
                    onClick={testCsrf}
                    disabled={loading}
                    className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    {loading ? 'Probando...' : 'Probar Token CSRF'}
                </button>
                {result && (
                    <div className={`p-3 rounded text-sm ${
                        result.includes('✅') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    }`}>
                        {result}
                    </div>
                )}
            </div>
        </div>
    );
} 