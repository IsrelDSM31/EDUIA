import React from 'react';
import { Card } from '@/Components/UI/Card';

export default function ScheduleModule({ stats }) {
    const weeklySchedule = {
        hours: ['14:00 - 15:00', '15:00 - 16:00', '16:00 - 17:00', '17:00 - 18:00', '18:00 - 19:00', '19:00 - 20:00'],
        days: {
            'Lunes': [
                'Módulo I',
                'Módulo I',
                'Módulo I',
                'Módulo I',
                'Lectura, Exp. Oral y Escrita II',
                'Geometría y Trigonometría'
            ],
            'Martes': [
                'Módulo I',
                'Módulo I',
                'Módulo I',
                'Módulo I',
                'Química II',
                'Lectura, Exp. Oral y Escrita II'
            ],
            'Miércoles': [
                'Geometría y Trigonometría',
                'Geometría y Trigonometría',
                'Química II',
                'Química II',
                'Módulo I',
                'Módulo I'
            ],
            'Jueves': [
                'Módulo I',
                'Módulo I',
                'Módulo I',
                'Módulo I',
                'Inglés II',
                'Química II'
            ],
            'Viernes': [
                'Inglés II',
                'Inglés II',
                'Lectura, Exp. Oral y Escrita II',
                'Lectura, Exp. Oral y Escrita II',
                'Módulo I',
                'Módulo I'
            ]
        }
    };

    const weeklyHours = {
        'Geometría y Trigonometría': '4 h',
        'Inglés II': '3 h',
        'Química II': '4 h',
        'Lectura, Expresión Oral y Escrita II': '4 h',
        'Módulo I (Programación estructurada)': '17 h'
    };

    const getSubjectColor = (subject) => {
        const colors = {
            'Módulo I': 'text-orange-600',
            'Geometría y Trigonometría': 'text-blue-600',
            'Química II': 'text-purple-600',
            'Inglés II': 'text-green-600',
            'Lectura, Exp. Oral y Escrita II': 'text-red-600'
        };
        return colors[subject] || 'text-gray-600';
    };

    const schoolCalendar = [
        {
            month: 'AGOSTO 2024',
            startDay: 6,
            days: Array.from({ length: 31 }, (_, i) => ({
                day: i + 1,
                isStartDay: i + 1 === 28,
                isEndDay: false,
                isHoliday: false,
                isCTE: i + 1 === 25,
                isVacation: [21, 22, 23, 24, 25].includes(i + 1),
                isDeliveryDay: false,
                isTrainingDay: false,
                isAdminDay: false
            }))
        },
        {
            month: 'SEPTIEMBRE 2024',
            startDay: 2,
            days: Array.from({ length: 30 }, (_, i) => ({
                day: i + 1,
                isStartDay: false,
                isEndDay: false,
                isHoliday: i + 1 === 15,
                isCTE: [22, 29].includes(i + 1),
                isVacation: false,
                isDeliveryDay: false,
                isTrainingDay: false,
                isAdminDay: false
            }))
        },
        {
            month: 'OCTUBRE 2024',
            startDay: 4,
            days: Array.from({ length: 31 }, (_, i) => ({
                day: i + 1,
                isStartDay: false,
                isEndDay: false,
                isHoliday: false,
                isCTE: i + 1 === 27,
                isVacation: false,
                isDeliveryDay: false,
                isTrainingDay: false,
                isAdminDay: false
            }))
        },
        {
            month: 'NOVIEMBRE 2024',
            startDay: 0,
            days: Array.from({ length: 30 }, (_, i) => ({
                day: i + 1,
                isStartDay: false,
                isEndDay: false,
                isHoliday: [2, 20].includes(i + 1),
                isCTE: [17, 24].includes(i + 1),
                isVacation: false,
                isDeliveryDay: i + 1 === 27,
                isTrainingDay: false,
                isAdminDay: false
            }))
        },
        {
            month: 'DICIEMBRE 2024',
            startDay: 2,
            days: Array.from({ length: 31 }, (_, i) => ({
                day: i + 1,
                isStartDay: false,
                isEndDay: false,
                isHoliday: i + 1 === 25,
                isCTE: false,
                isVacation: i + 1 >= 21 && i + 1 <= 31,
                isDeliveryDay: false,
                isTrainingDay: false,
                isAdminDay: false
            }))
        },
        {
            month: 'ENERO 2025',
            startDay: 5,
            days: Array.from({ length: 31 }, (_, i) => ({
                day: i + 1,
                isStartDay: false,
                isEndDay: false,
                isHoliday: i + 1 === 1,
                isCTE: i + 1 === 26,
                isVacation: i + 1 <= 7,
                isDeliveryDay: false,
                isTrainingDay: i + 1 === 3,
                isAdminDay: false
            }))
        },
        {
            month: 'FEBRERO 2025',
            startDay: 1,
            days: Array.from({ length: 28 }, (_, i) => ({
                day: i + 1,
                isStartDay: false,
                isEndDay: false,
                isHoliday: i + 1 === 5,
                isCTE: i + 1 === 23,
                isVacation: false,
                isDeliveryDay: false,
                isTrainingDay: false,
                isAdminDay: false,
                isPreregistration: [9, 16].includes(i + 1)
            }))
        },
        {
            month: 'MARZO 2025',
            startDay: 2,
            days: Array.from({ length: 31 }, (_, i) => ({
                day: i + 1,
                isStartDay: false,
                isEndDay: false,
                isHoliday: i + 1 === 18,
                isCTE: false,
                isVacation: false,
                isDeliveryDay: i + 1 === 21,
                isTrainingDay: false,
                isAdminDay: i + 1 === 15
            }))
        },
        {
            month: 'ABRIL 2025',
            startDay: 5,
            days: Array.from({ length: 30 }, (_, i) => ({
                day: i + 1,
                isStartDay: false,
                isEndDay: false,
                isHoliday: false,
                isCTE: i + 1 === 26,
                isVacation: false,
                isDeliveryDay: false,
                isTrainingDay: false,
                isAdminDay: false
            }))
        },
        {
            month: 'MAYO 2025',
            startDay: 0,
            days: Array.from({ length: 31 }, (_, i) => ({
                day: i + 1,
                isStartDay: false,
                isEndDay: false,
                isHoliday: [1, 15].includes(i + 1),
                isCTE: i + 1 === 31,
                isVacation: false,
                isDeliveryDay: false,
                isTrainingDay: i + 1 === 14,
                isAdminDay: false
            }))
        },
        {
            month: 'JUNIO 2025',
            startDay: 3,
            days: Array.from({ length: 30 }, (_, i) => ({
                day: i + 1,
                isStartDay: false,
                isEndDay: false,
                isHoliday: false,
                isCTE: i + 1 === 28,
                isVacation: false,
                isDeliveryDay: false,
                isTrainingDay: false,
                isAdminDay: false
            }))
        },
        {
            month: 'JULIO 2025',
            startDay: 5,
            days: Array.from({ length: 31 }, (_, i) => ({
                day: i + 1,
                isStartDay: false,
                isEndDay: i + 1 === 16,
                isHoliday: false,
                isCTE: false,
                isVacation: [28, 29, 30, 31].includes(i + 1),
                isDeliveryDay: false,
                isTrainingDay: false,
                isAdminDay: i + 1 === 12
            }))
        }
    ];

    const weekDays = ['D', 'L', 'M', 'M', 'J', 'V', 'S'];

    const renderCalendarDay = (day) => {
        let className = 'text-center text-sm p-1 rounded ';
        if (day.isStartDay) {
            className += 'bg-black text-white';
        } else if (day.isEndDay) {
            className += 'bg-black text-white';
        } else if (day.isHoliday) {
            className += 'bg-black text-white';
        } else if (day.isCTE) {
            className += 'bg-[#8B1538] text-white';
        } else if (day.isVacation) {
            className += 'bg-[#D4B483]';
        } else if (day.isDeliveryDay) {
            className += 'border-2 border-dashed border-gray-400';
        } else if (day.isTrainingDay) {
            className += 'bg-yellow-300';
        } else if (day.isAdminDay) {
            className += 'bg-blue-300';
        } else if (day.isPreregistration) {
            className += 'border-2 border-red-500';
        }
        return className;
    };

    return (
        <div className="space-y-6">
            <div className="grid grid-cols-1 gap-4">
                <Card className="p-6">
                    <h3 className="text-lg font-semibold mb-4">Horario de Clases</h3>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                                    {Object.keys(weeklySchedule.days).map(day => (
                                        <th key={day} className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{day}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {weeklySchedule.hours.map((hour, hourIndex) => (
                                    <tr key={hour}>
                                        <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{hour}</td>
                                        {Object.keys(weeklySchedule.days).map(day => (
                                            <td key={`${day}-${hour}`} className={`px-4 py-3 whitespace-nowrap text-sm font-medium ${getSubjectColor(weeklySchedule.days[day][hourIndex])}`}>
                                                {weeklySchedule.days[day][hourIndex]}
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="mt-6">
                        <h4 className="text-lg font-semibold mb-3">Total de horas semanales:</h4>
                        <ul className="space-y-2">
                            {Object.entries(weeklyHours).map(([subject, hours]) => (
                                <li key={subject} className="flex items-center text-sm">
                                    <span className="mr-2">•</span>
                                    <span className={`font-medium ${getSubjectColor(subject.split(' (')[0])}`}>{subject}:</span>
                                    <span className="ml-2">{hours}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </Card>

                {/* Calendario Escolar */}
                <Card className="p-6">
                    <div className="text-center mb-6">
                        <h3 className="text-xl font-semibold text-[#8B1538]">IAEDU</h3>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        {schoolCalendar.map((month, monthIndex) => (
                            <div key={monthIndex} className="bg-white rounded-lg shadow">
                                <div className="bg-[#8B1538] text-white p-2 text-center font-semibold rounded-t-lg">
                                    {month.month}
                                </div>
                                <div className="p-2">
                                    <div className="grid grid-cols-7 gap-1 mb-2">
                                        {weekDays.map((day, index) => (
                                            <div key={index} className="text-center text-xs font-semibold">
                                                {day}
                                            </div>
                                        ))}
                                    </div>
                                    <div className="grid grid-cols-7 gap-1">
                                        {/* Espacios vacíos para alinear el primer día */}
                                        {Array.from({ length: month.startDay }).map((_, index) => (
                                            <div key={`empty-${index}`} className="text-center text-sm p-1"></div>
                                        ))}
                                        {/* Días del mes */}
                                        {month.days.map((day, dayIndex) => (
                                            <div
                                                key={dayIndex}
                                                className={renderCalendarDay(day)}
                                            >
                                                {day.day}
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                        <div className="flex items-center">
                            <div className="w-4 h-4 bg-black rounded mr-2"></div>
                            <span>INICIO/FIN DE CLASES Y SUSPENSIÓN</span>
                        </div>
                        <div className="flex items-center">
                            <div className="w-4 h-4 bg-[#8B1538] rounded mr-2"></div>
                            <span>CONSEJO TÉCNICO ESCOLAR</span>
                        </div>
                        <div className="flex items-center">
                            <div className="w-4 h-4 bg-[#D4B483] rounded mr-2"></div>
                            <span>VACACIONES</span>
                        </div>
                        <div className="flex items-center">
                            <div className="w-4 h-4 border-2 border-dashed border-gray-400 rounded mr-2"></div>
                            <span>ENTREGA DE EVALUACIONES</span>
                        </div>
                        <div className="flex items-center">
                            <div className="w-4 h-4 bg-yellow-300 rounded mr-2"></div>
                            <span>FORMACIÓN CONTINUA</span>
                        </div>
                        <div className="flex items-center">
                            <div className="w-4 h-4 bg-blue-300 rounded mr-2"></div>
                            <span>DESCARGA ADMINISTRATIVA</span>
                        </div>
                        <div className="flex items-center">
                            <div className="w-4 h-4 border-2 border-red-500 rounded mr-2"></div>
                            <span>PREINSCRIPCIÓN</span>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    );
} 