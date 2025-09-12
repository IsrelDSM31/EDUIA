import React from 'react';

export default function Welcome() {
    return (
        <div className="min-h-screen flex flex-col items-center justify-center bg-[#131313] text-white">
            <header className="w-full flex justify-between items-center px-8 py-4 bg-[#1a1a1a] shadow-md">
                <span className="text-2xl font-bold tracking-tight">IAEDU</span>
                <nav className="space-x-8 text-[#86868B] text-sm">
                    <a href="#" className="hover:text-white transition">Inicio</a>
                    <a href="#" className="hover:text-white transition">Funcionalidades</a>
                    <a href="#" className="hover:text-white transition">Contacto</a>
                </nav>
            </header>
            <main className="flex-1 flex flex-col items-center justify-center text-center px-4">
                <h1 className="text-5xl font-extrabold mb-4">Elegancia con tonos oscuros</h1>
                <p className="text-xl text-[#86868B] mb-8 max-w-xl">Un ejemplo clásico de minimalismo bien hecho. El azul eléctrico de los botones CTA resplandece sobre el fondo oscuro, llamando la atención del visitante inmediatamente. La web utiliza poco texto, lo que mantiene su aspecto general limpio y ordenado.</p>
                <button className="btn-primary text-lg px-8 py-3 mt-2">Comenzar ahora</button>
            </main>
            <footer className="w-full text-center py-4 text-[#86868B] bg-[#1a1a1a] text-xs">© 2024 IAEDU. Todos los derechos reservados.</footer>
        </div>
    );
}
