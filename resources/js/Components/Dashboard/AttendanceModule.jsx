import React, { useState, useMemo, useEffect } from 'react';
import { Card } from '@/Components/UI/Card';
import Modal from '@/Components/Modal';
import { usePage } from '@inertiajs/react';

// Simulación de ML para riesgo (puedes reemplazar por un modelo real)
function mlRiskStatus(absentsBySubject) {
    // Si tiene 3 o más extraordinarios: baja
    const extraordinaryCount = Object.values(absentsBySubject).filter(a => a > 6).length;
    if (extraordinaryCount >= 3) return 'Baja';
    if (extraordinaryCount === 2) return 'En riesgo';
    if (extraordinaryCount === 1) return 'Extraordinario';
    return 'Completas';
}

export default function AttendanceModule({ stats }) {
    let { csrf_token } = usePage().props;
    // Fallback: obtener del meta tag si no viene por props
    if (!csrf_token) {
        csrf_token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }
    const [selectedGroup, setSelectedGroup] = useState('');
    const [selectedSubject, setSelectedSubject] = useState('');
    const [modal, setModal] = useState({ open: false, mode: '', data: null });
    const [attendanceDate, setAttendanceDate] = useState('');
    const [attendanceStatus, setAttendanceStatus] = useState('present');
    const [message, setMessage] = useState('');
    const [importMessage, setImportMessage] = useState('');
    const [importError, setImportError] = useState('');
    const [justStudent, setJustStudent] = useState('');
    const [justType, setJustType] = useState('medical');
    const [justFile, setJustFile] = useState(null);
    const [justObs, setJustObs] = useState('');
    const [justMessage, setJustMessage] = useState('');
    const [justError, setJustError] = useState('');
    const [justSubject, setJustSubject] = useState('');
    const [localAttendances, setLocalAttendances] = useState(stats?.attendances || []);
    const [quickJustify, setQuickJustify] = useState({ open: false, student: null, subject: null });

    const getToday = () => {
        const today = new Date();
        return today.toISOString().split('T')[0];
    };

    // Agrupar asistencias por alumno y materia
    const attendanceSummary = useMemo(() => {
        const summary = [];
        if (!stats?.students || !stats?.subjects || !localAttendances) return summary;
        stats.students.forEach(student => {
            const absentsBySubject = {};
            stats.subjects.forEach(subject => {
                const attForSubject = localAttendances.filter(att => att.student_name === `${student.nombre} ${student.apellido_paterno}` && att.subject_name === subject.name);
                const total = attForSubject.length;
                const presents = attForSubject.filter(a => a.status === 'present').length;
                const absents = attForSubject.filter(a => a.status === 'absent').length;
                const percent = total > 0 ? Math.round((presents / total) * 100) : 0;
                absentsBySubject[subject.name] = absents;
                summary.push({
                    matricula: student.matricula,
                    nombre: `${student.nombre} ${student.apellido_paterno}`,
                    subject: subject.name,
                    presents,
                    absents,
                    total,
                    percent,
                    estado: absents > 6 ? 'Extraordinario' : (absents > 3 ? 'En riesgo' : 'Completas'),
                    studentId: student.id,
                    subjectId: subject.id
                });
            });
            // ML: Estado global del alumno
            const globalEstado = mlRiskStatus(absentsBySubject);
            summary.push({
                isGlobal: true,
                matricula: student.matricula,
                nombre: `${student.nombre} ${student.apellido_paterno}`,
                subject: 'Estado Global',
                presents: '-',
                absents: '-',
                total: '-',
                percent: '-',
                estado: globalEstado,
                studentId: student.id,
                subjectId: null
            });
        });
        return summary;
    }, [stats, localAttendances]);

    // Calcular inasistencias justificadas por materia
    const justifiedAbsentsByStudentSubject = useMemo(() => {
        const map = {};
        if (!localAttendances) return map;
        localAttendances.forEach(att => {
            if (att.status === 'absent' && att.justification_type) {
                const key = att.student_id + '-' + att.subject_id;
                map[key] = (map[key] || 0) + 1;
            }
        });
        return map;
    }, [localAttendances]);

    // Calcular asistencias globales por alumno
    const globalAttendanceByStudent = useMemo(() => {
        if (!stats?.students || !localAttendances) return {};
        const map = {};
        stats.students.forEach(student => {
            // Agrupar asistencias por fecha
            const attByDate = {};
            localAttendances.filter(att => att.student_id === student.id && att.status === 'present').forEach(att => {
                attByDate[att.date] = (attByDate[att.date] || 0) + 1;
            });
            // Para cada fecha, si asistió a la mayoría de materias, cuenta como asistencia global
            let globalCount = 0;
            Object.entries(attByDate).forEach(([date, count]) => {
                const totalMaterias = stats.subjects.length;
                if (count >= Math.ceil(totalMaterias / 2)) {
                    globalCount++;
                }
            });
            map[student.id] = globalCount;
        });
        return map;
    }, [stats, localAttendances]);

    // Calcular resumen de asistencia global por alumno
    const attendanceSummaryByStudent = useMemo(() => {
        if (!stats?.students || !localAttendances) return { present: 0, absent: 0, late: 0, justified: 0 };
        let present = 0, absent = 0, late = 0, justified = 0;
        stats.students.forEach(student => {
            // Agrupar asistencias por fecha
            const attByDate = {};
            localAttendances.filter(att => att.student_id === student.id).forEach(att => {
                if (!attByDate[att.date]) attByDate[att.date] = [];
                attByDate[att.date].push(att);
            });
            Object.values(attByDate).forEach(dayAtts => {
                // Si el alumno estuvo presente en la mayoría de materias ese día, cuenta como asistencia global
                const totalMaterias = stats.subjects.length;
                const presentes = dayAtts.filter(a => a.status === 'present').length;
                const ausentes = dayAtts.filter(a => a.status === 'absent').length;
                const retardos = dayAtts.filter(a => a.status === 'late').length;
                const justificados = dayAtts.filter(a => a.status === 'absent' && a.justification_type).length;
                if (presentes >= Math.ceil(totalMaterias / 2)) present++;
                if (ausentes >= Math.ceil(totalMaterias / 2)) absent++;
                if (retardos >= Math.ceil(totalMaterias / 2)) late++;
                if (justificados >= Math.ceil(totalMaterias / 2)) justified++;
            });
        });
        return { present, absent, late, justified };
    }, [stats, localAttendances]);

    // Calcular extraordinarios y bajas por alumno (estado global)
    const extraBajasSummary = useMemo(() => {
        let extraordinarios = 0, bajas = 0;
        if (!stats?.students || !localAttendances) return { extraordinarios, bajas };
        stats.students.forEach(student => {
            // Agrupar asistencias por materia
            const absentsBySubject = {};
            stats.subjects.forEach(subject => {
                const attForSubject = localAttendances.filter(att => att.student_id === student.id && att.subject_id === subject.id);
                const absents = attForSubject.filter(a => a.status === 'absent').length;
                absentsBySubject[subject.name] = absents;
            });
            // Lógica: 3+ extraordinarios = baja, 2 = extraordinario
            const extraordinaryCount = Object.values(absentsBySubject).filter(a => a > 6).length;
            if (extraordinaryCount >= 3) bajas++;
            else if (extraordinaryCount >= 1) extraordinarios++;
        });
        return { extraordinarios, bajas };
    }, [stats, localAttendances]);

    useEffect(() => {
        if (modal.open && modal.mode === 'register') {
            setAttendanceDate(getToday());
            setAttendanceStatus('present');
        }
    }, [modal.open, modal.mode]);

    // Registrar asistencia
    const handleRegister = (row) => {
        setModal({ open: true, mode: 'register', data: row });
    };

    const submitRegister = async (e) => {
        e.preventDefault();
        setMessage('');
        try {
            console.log('Sending attendance request with CSRF token:', csrf_token);
            const response = await fetch('/attendance', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf_token,
                },
                body: JSON.stringify({
                    student_id: modal.data.studentId,
                    subject_id: modal.data.subjectId,
                    date: attendanceDate,
                    status: attendanceStatus,
                }),
            });
            
            console.log('Response status:', response.status);
            
            if (response.status === 419) {
                setMessage('Error: Token CSRF expirado. Por favor, recarga la página e intenta de nuevo.');
                return;
            }
            
            if (response.ok) {
                const newAttendance = await response.json();
                setLocalAttendances(prev => [...prev, newAttendance.attendance]);
                setMessage('¡Asistencia registrada!');
                setModal({ open: false, mode: '', data: null });
            } else {
                const errorData = await response.json().catch(() => ({}));
                setMessage(`Error al registrar asistencia: ${errorData.error || response.statusText}`);
            }
        } catch (err) {
            console.error('Error in submitRegister:', err);
            setMessage('Error al registrar asistencia: ' + err.message);
        }
    };

    // Historial de asistencias
    const handleHistory = (row) => {
        setModal({ open: true, mode: 'history', data: row });
    };

    const getHistory = (studentId, subjectId) => {
        return stats.attendances.filter(att =>
            att.student_name && att.subject_name &&
            stats.students.find(s => s.id === studentId && `${s.nombre} ${s.apellido_paterno}` === att.student_name) &&
            stats.subjects.find(sub => sub.id === subjectId && sub.name === att.subject_name)
        );
    };

    // Importar asistencias
    const handleImport = async (e) => {
        e.preventDefault();
        setImportMessage('');
        setImportError('');
        const fileInput = e.target.elements.file;
        if (!fileInput.files.length) {
            setImportError('Selecciona un archivo Excel.');
            return;
        }
        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        try {
            const response = await fetch('/attendance/import', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf_token,
                },
                body: formData,
            });
            if (response.ok) {
                setImportMessage('¡Importación completada!');
                window.location.reload();
            } else {
                setImportError('Error al importar.');
            }
        } catch (err) {
            setImportError('Error al importar.');
        }
    };

    // Enviar justificación
    const handleJustify = async (e) => {
        e.preventDefault();
        setJustMessage('');
        setJustError('');
        if (!justStudent) { setJustError('Selecciona un estudiante.'); return; }
        const formData = new FormData();
        formData.append('student_id', justStudent);
        formData.append('subject_id', justSubject);
        formData.append('justification_type', justType);
        if (justFile) formData.append('file', justFile);
        formData.append('observaciones', justObs);
        try {
            const response = await fetch('/attendance/justify', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf_token,
                },
                body: formData,
            });
            if (response.ok) {
                setJustMessage('¡Justificación enviada!');
                setJustStudent(''); setJustType('medical'); setJustFile(null); setJustObs('');
                window.location.reload();
            } else {
                setJustError('Error al enviar justificación.');
            }
        } catch (err) {
            setJustError('Error al enviar justificación.');
        }
    };

    const handleQuickJustify = (row) => {
        setQuickJustify({ open: true, student: row.studentId, subject: row.subjectId });
        setJustStudent(row.studentId);
        setJustSubject(row.subjectId);
        setJustType('medical');
        setJustFile(null);
        setJustObs('');
        setJustMessage('');
        setJustError('');
    };

    return (
        <div className="space-y-6">
            <Card className="p-6">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <a
                        href="/attendance/export"
                        className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow text-xs font-semibold transition-colors duration-150"
                        onClick={(e) => {
                            e.preventDefault();
                            window.location.href = '/attendance/export';
                        }}
                    >
                        Exportar Asistencias
                    </a>
                    <form onSubmit={handleImport} className="flex items-center gap-2" encType="multipart/form-data">
                        <input
                            type="file"
                            name="file"
                            accept=".xlsx,.xls"
                            className="text-xs border rounded px-2 py-1"
                            required
                        />
                        <button
                            type="submit"
                            className="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-semibold transition-colors duration-150"
                        >
                            Importar Asistencias
                        </button>
                    </form>
                </div>
                {importMessage && <div className="mt-2 text-green-700 text-xs">{importMessage}</div>}
                {importError && <div className="mt-2 text-red-600 text-xs">{importError}</div>}
            </Card>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card className="p-6">
                    <h3 className="text-lg font-semibold mb-4">Resumen de Asistencia</h3>
                    <div className="space-y-4 grid grid-cols-1 gap-3">
                        <div className="flex justify-between items-center p-3 rounded bg-green-50 border border-green-200">
                            <span className="font-medium">Presentes</span>
                            <span className="font-bold text-green-700 text-lg">{attendanceSummaryByStudent.present}</span>
                        </div>
                        <div className="flex justify-between items-center p-3 rounded bg-red-50 border border-red-200">
                            <span className="font-medium">Ausentes</span>
                            <span className="font-bold text-red-700 text-lg">{attendanceSummaryByStudent.absent}</span>
                        </div>
                        <div className="flex justify-between items-center p-3 rounded bg-yellow-50 border border-yellow-200">
                            <span className="font-medium">Retardos</span>
                            <span className="font-bold text-yellow-700 text-lg">{attendanceSummaryByStudent.late}</span>
                        </div>
                        <div className="flex justify-between items-center p-3 rounded bg-blue-50 border border-blue-200">
                            <span className="font-medium">Justificados</span>
                            <span className="font-bold text-blue-700 text-lg">{attendanceSummaryByStudent.justified}</span>
                        </div>
                        <div className="flex justify-between items-center p-3 rounded bg-orange-50 border border-orange-200">
                            <span className="font-medium">Extraordinarios</span>
                            <span className="font-bold text-orange-700 text-lg">{extraBajasSummary.extraordinarios}</span>
                        </div>
                        <div className="flex justify-between items-center p-3 rounded bg-gray-100 border border-gray-300">
                            <span className="font-medium">Bajas</span>
                            <span className="font-bold text-gray-700 text-lg">{extraBajasSummary.bajas}</span>
                        </div>
                    </div>
                </Card>
            </div>

            <Card className="p-6">
                <h3 className="text-lg font-semibold mb-4">Asistencias por Alumno y Materia</h3>
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 text-xs">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-2 py-1">Matrícula</th>
                                <th className="px-2 py-1">Nombre</th>
                                <th className="px-2 py-1">Materia</th>
                                <th className="px-2 py-1">Asistencias</th>
                                <th className="px-2 py-1">Inasistencias</th>
                                <th className="px-2 py-1">Total</th>
                                <th className="px-2 py-1">% Asist.</th>
                                <th className="px-2 py-1">Estado</th>
                                <th className="px-2 py-1">Acciones</th>
                                <th className="px-2 py-1">Asist. Global</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {(() => {
                                let lastMatricula = null;
                                let colorToggle = false;
                                return attendanceSummary.map((row, idx) => {
                                    // Cambia el color cuando cambia el alumno
                                    if (row.matricula !== lastMatricula && !row.isGlobal) {
                                        colorToggle = !colorToggle;
                                        lastMatricula = row.matricula;
                                    }
                                    const justified = justifiedAbsentsByStudentSubject[row.studentId + '-' + row.subjectId] || 0;
                                    return (
                                        <tr
                                            key={row.matricula + row.subject + idx}
                                            className={
                                                (row.isGlobal ? 'bg-yellow-100 font-bold ' : '') +
                                                (!row.isGlobal && colorToggle ? 'bg-gray-200' : '')
                                            }
                                        >
                                            <td className="px-2 py-1">{row.matricula}</td>
                                            <td className="px-2 py-1">{row.nombre}</td>
                                            <td className="px-2 py-1">{row.subject}</td>
                                            <td className="px-2 py-1 text-center">{row.presents}</td>
                                            <td className="px-2 py-1 text-center">
                                                {row.absents}
                                                {justified > 0 && (
                                                    <span className="ml-1 inline-block bg-blue-100 text-blue-700 px-1 rounded text-[10px] align-middle" title="Justificadas">+{justified} <span className="hidden md:inline">just.</span></span>
                                                )}
                                            </td>
                                            <td className="px-2 py-1 text-center">{row.total}</td>
                                            <td className="px-2 py-1 text-center">{row.percent}</td>
                                            <td className="px-2 py-1 text-center">
                                                {row.estado === 'Baja' ? <span className="text-red-600">Baja</span> :
                                                 row.estado === 'Extraordinario' ? <span className="text-orange-600">Extraordinario</span> :
                                                 row.estado === 'En riesgo' ? <span className="text-yellow-600">En riesgo</span> :
                                                 <span className="text-green-600">Completas</span>}
                                            </td>
                                            <td className="px-2 py-1 text-center">
                                                {!row.isGlobal && <>
                                                    <button className="text-blue-600 hover:underline mr-2" onClick={() => handleRegister(row)}>Registrar</button>
                                                    <button className="text-indigo-600 hover:underline mr-2" onClick={() => handleHistory(row)}>Historial</button>
                                                    <button className="text-green-600 hover:underline" onClick={() => handleQuickJustify(row)}>Justificar Falta</button>
                                                </>}
                                            </td>
                                            <td className="px-2 py-1 text-center">
                                                {row.isGlobal ? '' : (globalAttendanceByStudent[row.studentId] || 0)}
                                            </td>
                                        </tr>
                                    );
                                });
                            })()}
                        </tbody>
                    </table>
                </div>
            </Card>

            {/* Modal para registrar o ver historial */}
            <Modal show={modal.open} onClose={() => setModal({ open: false, mode: '', data: null })}>
                {modal.mode === 'register' && (
                    <form className="p-6 space-y-4" onSubmit={submitRegister}>
                        <h3 className="text-lg font-semibold mb-4 text-black">Registrar Asistencia</h3>
                        <div>
                            <label className="block text-sm font-medium text-black">Fecha</label>
                            <input type="date" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-black" value={attendanceDate} onChange={e => setAttendanceDate(e.target.value)} required />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-black">Estado</label>
                            <select className="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-black" value={attendanceStatus} onChange={e => setAttendanceStatus(e.target.value)} required>
                                <option value="present">Presente</option>
                                <option value="absent">Ausente</option>
                                <option value="late">Retardo</option>
                            </select>
                        </div>
                        <div className="flex justify-end gap-2">
                            <button type="button" className="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-black" onClick={() => setModal({ open: false, mode: '', data: null })}>Cancelar</button>
                            <button type="submit" className="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">Guardar</button>
                        </div>
                        {message && <div className="text-green-600 text-sm mt-2">{message}</div>}
                    </form>
                )}
                {modal.mode === 'history' && (
                    <div className="p-6">
                        <h3 className="text-lg font-semibold mb-4 text-black">Historial de Asistencias</h3>
                        <table className="min-w-full divide-y divide-gray-200 text-xs mb-4">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-2 py-1 text-black">Fecha</th>
                                    <th className="px-2 py-1 text-black">Estado</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {(getHistory && modal.data) && getHistory(modal.data.studentId, modal.data.subjectId).length > 0 ? (
                                    getHistory(modal.data.studentId, modal.data.subjectId).map((att, idx) => (
                                    <tr key={att.id + idx}>
                                            <td className="px-2 py-1 text-black">{att.date}</td>
                                            <td className="px-2 py-1 text-black">
                                            {att.status === 'present' ? 'Presente' : att.status === 'absent' ? 'Ausente' : 'Retardo'}
                                            {att.status === 'absent' && att.justification_type && (
                                                <span className="ml-1 inline-block bg-blue-100 text-blue-700 px-1 rounded text-[10px] align-middle">Justificada</span>
                                            )}
                                        </td>
                                    </tr>
                                    ))
                                ) : (
                                    <tr><td colSpan="2" className="text-center text-black">Sin registros</td></tr>
                                )}
                            </tbody>
                        </table>
                        <div className="flex justify-end">
                            <button className="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-black" onClick={() => setModal({ open: false, mode: '', data: null })}>Cerrar</button>
                        </div>
                    </div>
                )}
            </Modal>

            <Modal show={quickJustify.open} onClose={() => setQuickJustify({ open: false, student: null, subject: null })}>
                <form className="p-6 space-y-4" onSubmit={handleJustify} encType="multipart/form-data">
                    <h3 className="text-lg font-semibold mb-4 text-black">Justificar Falta</h3>
                    <div>
                        <label className="block text-sm font-medium text-black">Tipo de Justificación</label>
                        <select
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-black"
                            value={justType}
                            onChange={e => setJustType(e.target.value)}
                        >
                            <option value="medical">Médica</option>
                            <option value="family">Familiar</option>
                            <option value="academic">Académica</option>
                            <option value="other">Otra</option>
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-black">Documento</label>
                        <input
                            type="file"
                            className="mt-1 block w-full text-sm text-black file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100"
                            onChange={e => setJustFile(e.target.files[0])}
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-black">Observaciones</label>
                        <textarea
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-black"
                            rows="3"
                            value={justObs}
                            onChange={e => setJustObs(e.target.value)}
                        ></textarea>
                    </div>
                    {justMessage && <div className="text-green-700 text-xs">{justMessage}</div>}
                    {justError && <div className="text-red-600 text-xs">{justError}</div>}
                    <div className="flex justify-end gap-2">
                        <button type="button" className="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-black" onClick={() => setQuickJustify({ open: false, student: null, subject: null })}>Cancelar</button>
                        <button type="submit" className="px-4 py-2 rounded bg-green-600 hover:bg-green-700 text-white">Justificar</button>
                    </div>
                </form>
            </Modal>
        </div>
    );
} 