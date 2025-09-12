import React, { useState } from 'react';
import Calendar from 'react-calendar';
import 'react-calendar/dist/Calendar.css';
import './calendar-custom.css'; // Importar estilos personalizados
import Card from '@/Components/UI/Card';

const CalendarModule = () => {
    const [date, setDate] = useState(new Date());

    return (
        <Card className="p-6 shadow-lg rounded-2xl bg-white">
            <div className="p-4">
                <h2 className="text-2xl font-bold mb-4 text-gray-800">Calendario</h2>
                <div className="calendar-container">
                    <Calendar
                        onChange={setDate}
                        value={date}
                        locale="es-ES"
                        className="w-full custom-calendar"
                    />
                </div>
            </div>
        </Card>
    );
};

export default CalendarModule; 