import React from 'react';

export default function ConfirmModal({ show, title = '¿Estás seguro?', message = 'Esta acción no se puede deshacer.', onConfirm, onCancel }) {
    if (!show) return null;
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div className="bg-white rounded-lg shadow-lg p-8 max-w-sm w-full">
                <h2 className="text-lg font-semibold mb-2">{title}</h2>
                <p className="mb-6 text-gray-600">{message}</p>
                <div className="flex justify-end gap-2">
                    <button
                        className="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700"
                        onClick={onCancel}
                    >
                        Cancelar
                    </button>
                    <button
                        className="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white"
                        onClick={onConfirm}
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    );
} 