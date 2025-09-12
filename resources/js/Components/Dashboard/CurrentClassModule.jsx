import React, { useState, useEffect } from 'react';
import { Card } from '@/Components/UI/Card';

const CurrentClassModule = () => {
    const [currentClass, setCurrentClass] = useState(null);
    const [remainingClasses, setRemainingClasses] = useState([]);

    const schedule = {
        'Lunes': [
            { start: '14:00', end: '15:00', subject: 'Módulo I' },
            { start: '15:00', end: '16:00', subject: 'Módulo I' },
            { start: '16:00', end: '17:00', subject: 'Módulo I' },
            { start: '17:00', end: '18:00', subject: 'Módulo I' },
            { start: '18:00', end: '19:00', subject: 'Lectura, Exp. Oral y Escrita II' },
            { start: '19:00', end: '20:00', subject: 'Geometría y Trigonometría' }
        ],
        'Martes': [
            { start: '14:00', end: '15:00', subject: 'Módulo I' },
            { start: '15:00', end: '16:00', subject: 'Módulo I' },
            { start: '16:00', end: '17:00', subject: 'Módulo I' },
            { start: '17:00', end: '18:00', subject: 'Módulo I' },
            { start: '18:00', end: '19:00', subject: 'Química II' },
            { start: '19:00', end: '20:00', subject: 'Lectura, Exp. Oral y Escrita II' }
        ],
        'Miércoles': [
            { start: '14:00', end: '15:00', subject: 'Geometría y Trigonometría' },
            { start: '15:00', end: '16:00', subject: 'Geometría y Trigonometría' },
            { start: '16:00', end: '17:00', subject: 'Química II' },
            { start: '17:00', end: '18:00', subject: 'Química II' },
            { start: '18:00', end: '19:00', subject: 'Módulo I' },
            { start: '19:00', end: '20:00', subject: 'Módulo I' }
        ],
        'Jueves': [
            { start: '14:00', end: '15:00', subject: 'Módulo I' },
            { start: '15:00', end: '16:00', subject: 'Módulo I' },
            { start: '16:00', end: '17:00', subject: 'Módulo I' },
            { start: '17:00', end: '18:00', subject: 'Módulo I' },
            { start: '18:00', end: '19:00', subject: 'Inglés II' },
            { start: '19:00', end: '20:00', subject: 'Química II' }
        ],
        'Viernes': [
            { start: '14:00', end: '15:00', subject: 'Inglés II' },
            { start: '15:00', end: '16:00', subject: 'Inglés II' },
            { start: '16:00', end: '17:00', subject: 'Lectura, Exp. Oral y Escrita II' },
            { start: '17:00', end: '18:00', subject: 'Lectura, Exp. Oral y Escrita II' },
            { start: '18:00', end: '19:00', subject: 'Módulo I' },
            { start: '19:00', end: '20:00', subject: 'Módulo I' }
        ]
    };

    const getDayClasses = () => {
        const now = new Date();
        const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const currentDay = days[now.getDay()];
        
        if (!schedule[currentDay]) {
            return { current: null, remaining: [] };
        }

        const currentTime = now.getHours().toString().padStart(2, '0') + ':' + 
                          now.getMinutes().toString().padStart(2, '0');

        let current = null;
        let remaining = [];

        for (let i = 0; i < schedule[currentDay].length; i++) {
            const clase = schedule[currentDay][i];
            if (currentTime >= clase.start && currentTime < clase.end) {
                current = { ...clase, day: currentDay };
            } else if (currentTime < clase.start) {
                remaining.push({ ...clase, day: currentDay });
            }
        }

        return { current, remaining };
    };

    useEffect(() => {
        const updateClasses = () => {
            const { current, remaining } = getDayClasses();
            setCurrentClass(current);
            setRemainingClasses(remaining);
        };

        updateClasses();
        const interval = setInterval(updateClasses, 60000); // Actualizar cada minuto

        return () => clearInterval(interval);
    }, []);

    return (
        <Card className="shadow-lg rounded-2xl bg-white">
            <div className="p-4">
                <h2 className="text-2xl font-bold mb-4 text-gray-800 flex items-center gap-2">
                    <span role="img" aria-label="clases">📚</span> Clases
                </h2>
                <div className="space-y-4">
                    {/* Clase Actual */}
                    <div className="bg-blue-50 p-4 rounded-lg shadow flex items-center gap-3">
                        <span className="text-2xl">🕒</span>
                        <div>
                            <h3 className="text-lg font-semibold text-blue-900 mb-1">Clase Actual</h3>
                            {currentClass ? (
                                <div>
                                    <p className="text-blue-700 font-semibold">{currentClass.subject}</p>
                                    <p className="text-sm text-blue-600">
                                        {currentClass.start} - {currentClass.end}
                                    </p>
                                </div>
                            ) : (
                                <p className="text-blue-600">No hay clase en curso</p>
                            )}
                        </div>
                    </div>

                    {/* Próximas Clases */}
                    <div className="bg-orange-50 p-4 rounded-lg shadow flex items-start gap-3">
                        <span className="text-2xl mt-1">⏭️</span>
                        <div>
                            <h3 className="text-lg font-semibold text-orange-900 mb-1">Próximas Clases</h3>
                            {remainingClasses.length > 0 ? (
                                <div className="space-y-3">
                                    {remainingClasses.map((clase, index) => (
                                        <div key={index} className="border-b border-orange-100 last:border-0 pb-2 last:pb-0">
                                            <p className="text-orange-700 font-semibold">{clase.subject}</p>
                                            <p className="text-sm text-orange-600">
                                                {clase.start} - {clase.end}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-orange-600">No hay más clases por hoy</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    );
};

export default CurrentClassModule; 