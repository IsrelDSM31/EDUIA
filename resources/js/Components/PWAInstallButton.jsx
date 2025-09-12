import React, { useState, useEffect } from 'react';

export default function PWAInstallButton() {
    const [deferredPrompt, setDeferredPrompt] = useState(null);
    const [showInstallButton, setShowInstallButton] = useState(true); // Siempre visible
    const [isInstalled, setIsInstalled] = useState(false);
    const [pwaStatus, setPwaStatus] = useState('checking');

    useEffect(() => {
        // Verificar si ya está instalada
        const checkIfInstalled = () => {
            if (window.matchMedia('(display-mode: standalone)').matches) {
                setIsInstalled(true);
                setPwaStatus('installed');
                return;
            }
        };

        const handleBeforeInstallPrompt = (e) => {
            e.preventDefault();
            setDeferredPrompt(e);
            setPwaStatus('ready');
            console.log('PWA instalación disponible');
        };

        const handleAppInstalled = () => {
            setShowInstallButton(false);
            setDeferredPrompt(null);
            setIsInstalled(true);
            setPwaStatus('installed');
            console.log('PWA instalada exitosamente');
        };

        // Verificar si ya está instalada
        checkIfInstalled();

        // Escuchar eventos de instalación
        window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
        window.addEventListener('appinstalled', handleAppInstalled);

        // Verificar estado de PWA después de 2 segundos
        const timer = setTimeout(() => {
            if (pwaStatus === 'checking') {
                setPwaStatus('manual');
            }
        }, 2000);

        return () => {
            window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
            window.removeEventListener('appinstalled', handleAppInstalled);
            clearTimeout(timer);
        };
    }, [pwaStatus]);

    const handleInstallClick = async () => {
        if (deferredPrompt) {
            // Instalación automática disponible
            try {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                
                if (outcome === 'accepted') {
                    console.log('Usuario aceptó la instalación');
                    setPwaStatus('installing');
                } else {
                    console.log('Usuario rechazó la instalación');
                    setPwaStatus('rejected');
                }
            } catch (error) {
                console.error('Error durante la instalación:', error);
                setPwaStatus('error');
            }
            
            setDeferredPrompt(null);
        } else {
            // Instalación manual
            showManualInstallInstructions();
        }
    };

    const showManualInstallInstructions = () => {
        const instructions = `
📱 Instalar IAEDU1 como App:

Chrome/Edge Desktop:
1. Haz clic en el ícono de instalación en la barra de direcciones
2. Selecciona "Instalar"

Chrome/Edge Móvil:
1. Toca el menú (⋮) en la esquina superior derecha
2. Selecciona "Instalar aplicación" o "Añadir a pantalla de inicio"

Safari (iOS):
1. Toca el botón de compartir (□↑)
2. Selecciona "Añadir a pantalla de inicio"

Firefox:
1. Toca el menú (☰)
2. Selecciona "Instalar aplicación"

¿Necesitas ayuda? Ve a: /pwa-diagnostic
        `;
        alert(instructions);
    };

    const getButtonText = () => {
        switch (pwaStatus) {
            case 'ready':
                return '📥 Instalar App';
            case 'installing':
                return '⏳ Instalando...';
            case 'installed':
                return '✅ App Instalada';
            case 'rejected':
                return '❌ Instalación Rechazada';
            case 'error':
                return '⚠️ Error de Instalación';
            case 'manual':
                return '📱 Instalar App';
            default:
                return '📱 Instalar App';
        }
    };

    const getButtonClass = () => {
        const baseClass = "fixed bottom-4 right-4 z-50 px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-all duration-200 transform hover:scale-105";
        
        switch (pwaStatus) {
            case 'ready':
                return `${baseClass} bg-green-600 hover:bg-green-700 text-white`;
            case 'installing':
                return `${baseClass} bg-yellow-600 text-white cursor-wait`;
            case 'installed':
                return `${baseClass} bg-green-600 text-white cursor-default`;
            case 'rejected':
                return `${baseClass} bg-red-600 hover:bg-red-700 text-white`;
            case 'error':
                return `${baseClass} bg-orange-600 hover:bg-orange-700 text-white`;
            case 'manual':
                return `${baseClass} bg-blue-600 hover:bg-blue-700 text-white`;
            default:
                return `${baseClass} bg-blue-600 hover:bg-blue-700 text-white`;
        }
    };

    const isButtonDisabled = () => {
        return pwaStatus === 'installing' || pwaStatus === 'installed';
    };

    if (isInstalled) {
        return (
            <div className="fixed bottom-4 right-4 z-50">
                <div className="bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
                    <span>✅</span>
                    <span>App Instalada</span>
                </div>
            </div>
        );
    }

    return (
        <div className="fixed bottom-4 right-4 z-50">
            <button
                onClick={handleInstallClick}
                className={getButtonClass()}
                disabled={isButtonDisabled()}
            >
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
                {getButtonText()}
            </button>
            
            {/* Botón de diagnóstico */}
            <button
                onClick={() => window.open('/pwa-diagnostic', '_blank')}
                className="fixed bottom-4 right-48 z-50 bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg shadow-lg text-sm transition-all duration-200"
                title="Diagnóstico PWA"
            >
                🔧
            </button>
        </div>
    );
} 