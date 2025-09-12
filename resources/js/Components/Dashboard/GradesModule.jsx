import React, { useState, useEffect } from 'react';
import { Card } from '@/Components/UI/Card';
import Modal from '@/Components/Modal';
import { PencilIcon, TrashIcon, EyeIcon } from '@heroicons/react/24/outline';
import axios from 'axios';
import { toast } from 'react-hot-toast';
import { usePage } from '@inertiajs/react';

export default function GradesModule({ grades, subjects, rubrics }) {
    const { csrf_token } = usePage().props;

    const getToday = () => {
        const today = new Date();
        return today.toISOString().split('T')[0];
    };

    const [selectedGroup, setSelectedGroup] = useState('');
    const [selectedSubject, setSelectedSubject] = useState('');
    const [form, setForm] = useState({
        student_id: '',
        subject_id: '',
        date: getToday(),
        teamwork: '',
        project: '',
        attendance: '',
        exam: '',
        extra: '',
    });
    const [message, setMessage] = useState('');
    const [modal, setModal] = useState({ open: false, mode: '', grade: null });
    const [selectedStudentId, setSelectedStudentId] = useState('');
    const [selectedStudent, setSelectedStudent] = useState(null);
    const [importMessage, setImportMessage] = useState('');
    const [importError, setImportError] = useState('');
    const [editingCell, setEditingCell] = useState({});
    const [cellValue, setCellValue] = useState('');
    const [modalOpen, setModalOpen] = useState(false);
    const [modalForm, setModalForm] = useState({
        teamwork: '',
        project: '',
        attendance: '',
        exam: '',
        extra: ''
    });
    const [modalContext, setModalContext] = useState({});
    const [studentsGrades, setStudentsGrades] = useState(grades || []);
    const [editContext, setEditContext] = useState({ evaluations: [], evalIdx: 0, gradeId: null, studentId: '', subjectId: '' });
    const [evaluations, setEvaluations] = useState([
        {
            P: '-',    // Proyecto
            Pr: '-',   // Práctica
            A: '-',    // Asistencia
            E: '-',    // Examen
            Ex: '-',   // Extra
            Prom: '-', // Promedio
        },
        {
            P: '-',
            Pr: '-',
            A: '-',
            E: '-',
            Ex: '-',
            Prom: '-',
        },
        {
            P: '-',
            Pr: '-',
            A: '-',
            E: '-',
            Ex: '-',
            Prom: '-',
        },
        {
            P: '-',
            Pr: '-',
            A: '-',
            E: '-',
            Ex: '-',
            Prom: '-',
        }
    ]);

    useEffect(() => {
        if (grades) {
            setStudentsGrades(grades);
        }
    }, [grades]);

    const handleStudentChange = (e) => {
        const studentId = e.target.value;
        setSelectedStudentId(studentId);
        const student = studentsGrades.find(s => s.id === parseInt(studentId));
        setSelectedStudent(student);
    };

    const handleChange = (e) => {
        setForm(prev => ({
            ...prev,
            [e.target.name]: e.target.value
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const defaultEval = { P: '-', Pr: '-', A: '-', E: '-', Ex: '-', Prom: '-' };
        try {
            const response = await axios.post('/grades', {
                student_id: selectedStudentId,
                evaluations: [defaultEval, defaultEval, defaultEval, defaultEval],
            });
            
            if (response.data.success) {
                setMessage('¡Alumno registrado con todas las materias!');
                setSelectedStudentId('');
                setSelectedStudent(null);
                // Opcional: Actualizar el estado local en lugar de recargar
                // findAndMergeGrades(response.data.grade); 
                window.location.reload(); 
            } else {
                setMessage(response.data.message || 'Error al registrar.');
            }
        } catch (err) {
            console.error('Error al registrar:', err);
            const errorMessage = err.response?.data?.message || 'Error al registrar.';
            setMessage(errorMessage);
        }
    };

    const handleView = async (grade) => {
        const response = await fetch(`/grades/${grade.id}`);
        const data = await response.json();
        setModal({ open: true, mode: 'view', grade: data });
    };

    const handleEdit = async (grade) => {
        const response = await fetch(`/grades/${grade.id}`);
        const data = await response.json();
        const evalIdx = 0;
        const evals = Array.isArray(data.evaluations) ? data.evaluations : [
            {teamwork:0, project:0, attendance:0, exam:0, extra:0},
            {teamwork:0, project:0, attendance:0, exam:0, extra:0},
            {teamwork:0, project:0, attendance:0, exam:0, extra:0},
            {teamwork:0, project:0, attendance:0, exam:0, extra:0}
        ];
        setForm({
            student_id: data.student_id,
            subject_id: data.subject_id,
            date: data.evaluation_date || getToday(),
            teamwork: evals[evalIdx]?.teamwork ?? '',
            project: evals[evalIdx]?.project ?? '',
            attendance: evals[evalIdx]?.attendance ?? '',
            exam: evals[evalIdx]?.exam ?? '',
            extra: evals[evalIdx]?.extra ?? ''
        });
        setEditContext({
            evaluations: evals,
            evalIdx,
            gradeId: data.id,
            studentId: data.student_id,
            subjectId: data.subject_id
        });
        setModal({ open: true, mode: 'edit', grade: data });
    };

    const handleUpdate = async (e) => {
        e.preventDefault();
        const newEvals = [...editContext.evaluations];
        newEvals[editContext.evalIdx] = {
            teamwork: parseFloat(form.teamwork) || 0,
            project: parseFloat(form.project) || 0,
            attendance: parseFloat(form.attendance) || 0,
            exam: parseFloat(form.exam) || 0,
            extra: parseFloat(form.extra) || 0
        };
        try {
            const response = await fetch(`/grades/${editContext.gradeId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf_token,
                },
                body: JSON.stringify({
                    student_id: editContext.studentId,
                    subject_id: editContext.subjectId,
                    evaluations: newEvals,
                    evaluation_date: form.date
                }),
            });
            const result = await response.json();
            if (response.ok && result.success) {
                setStudentsGrades(prevGrades => {
                    return prevGrades.map(student => {
                        if (student.id === editContext.studentId) {
                            const updatedGradesBySubject = { ...student.grades_by_subject };
                            updatedGradesBySubject[editContext.subjectId] = {
                                ...updatedGradesBySubject[editContext.subjectId],
                                id: editContext.gradeId,
                                evaluations: newEvals,
                                score: result.grade.score,
                                estado: result.estado,
                                faltantes: result.faltantes
                            };
                            return {
                                ...student,
                                grades_by_subject: updatedGradesBySubject
                            };
                        }
                        return student;
                    });
                });
                setModal({ open: false, mode: '', grade: null });
                setMessage('¡Calificación actualizada correctamente!');
            } else {
                setMessage(result.message || 'Error al actualizar calificación.');
            }
        } catch (err) {
            setMessage('Error al actualizar calificación.');
        }
    };

    const handleDelete = async (id) => {
        if (window.confirm('¿Seguro que deseas eliminar esta calificación?')) {
            await fetch(`/grades/${id}`, { 
                method: 'DELETE', 
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest', 
                    'X-CSRF-TOKEN': csrf_token 
                } 
            });
            window.location.reload();
        }
    };

    const closeModal = () => {
        setModal({ open: false, mode: '', grade: null });
        setForm({ student_id: '', subject_id: '', date: '', teamwork: '', project: '', attendance: '', exam: '', extra: '' });
    };

    const calcScore = () => {
        const teamwork = parseFloat(form.teamwork) || 0;
        const project = parseFloat(form.project) || 0;
        const attendance = parseFloat(form.attendance) || 0;
        const exam = parseFloat(form.exam) || 0;
        const extra = parseFloat(form.extra) || 0;
        return Math.min(teamwork * 0.3 + project * 0.3 + attendance * 0.1 + exam * 0.3 + extra, 10).toFixed(2);
    };

    const finalScore = calcScore();
    const estado = finalScore >= 7 ? 'Aprobado' : 'Reprobado';

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
            const response = await fetch('/grades/import', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf_token,
                },
                body: formData,
            });
            if (response.ok) {
                setImportMessage('¡Importación de calificaciones completada!');
                window.location.reload();
            } else {
                setImportError('Error al importar.');
            }
        } catch (err) {
            setImportError('Error al importar.');
        }
    };

    const handleCellSave = async (studentId, subjectId, evalIdx, value) => {
        const student = studentsGrades.find(s => s.id === studentId);
        let gradeObj = null;
        if (student) {
            const gradesArr = Object.values(student.grades_by_subject);
            gradeObj = gradesArr.find(g => (Array.isArray(g) ? g[0]?.subject_id : g.subject_id) == subjectId);
            if (Array.isArray(gradeObj)) gradeObj = gradeObj[0];
        }
        if (!gradeObj) return;
        const newEvaluations = [...(gradeObj.evaluations || [])];
        if (!newEvaluations[evalIdx]) newEvaluations[evalIdx] = {teamwork:0, project:0, attendance:0, exam:0, extra:0};
        newEvaluations[evalIdx] = { ...newEvaluations[evalIdx], exam: parseFloat(value) };
        await fetch(`/grades/${gradeObj.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf_token,
            },
            body: JSON.stringify({
                student_id: studentId,
                subject_id: subjectId,
                evaluations: newEvaluations,
            }),
        });
        setEditingCell({});
        window.location.reload();
    };

    const openUnitModal = (studentId, subjectId, evalIdx, evalObj, gradeObj) => {
        const defaultEval = {
            teamwork: 0,
            project: 0,
            attendance: 0,
            exam: 0,
            extra: 0
        };

        const currentEval = evalObj || defaultEval;

        setModalForm({
            teamwork: currentEval.teamwork !== undefined ? currentEval.teamwork.toString() : '0',
            project: currentEval.project !== undefined ? currentEval.project.toString() : '0',
            attendance: currentEval.attendance !== undefined ? currentEval.attendance.toString() : '0',
            exam: currentEval.exam !== undefined ? currentEval.exam.toString() : '0',
            extra: currentEval.extra !== undefined ? currentEval.extra.toString() : '0'
        });

        setModalContext({ 
            studentId, 
            subjectId, 
            evalIdx, 
            gradeObj: {
                ...gradeObj,
                evaluations: Array.isArray(gradeObj.evaluations) ? 
                    gradeObj.evaluations : 
                    Array(4).fill(defaultEval)
            }
        });
        
        setModalOpen(true);
    };

    const handleModalChange = e => {
        const { name, value } = e.target;
        let numValue = value === '' ? '' : Math.max(0, Math.min(10, parseFloat(value) || 0));
        setModalForm(prev => ({
            ...prev,
            [name]: numValue.toString()
        }));
    };

    const handleModalSave = async () => {
        try {
            if (!modalContext.studentId || !modalContext.subjectId) {
                toast.error('Información del estudiante o materia no disponible');
                return;
            }

            // Obtener las evaluaciones existentes o crear un array nuevo
            let evaluationsToSend = modalContext.gradeObj?.evaluations || Array(4).fill({
                P: '-',
                Pr: '-',
                A: '-',
                E: '-',
                Ex: '-',
                Prom: '-'
            });
            
            // Actualizar la evaluación específica con los valores del formulario
            evaluationsToSend = evaluationsToSend.map((evaluation, index) => {
                if (index === modalContext.evalIdx) {
                    // Convertir los valores del formulario al nuevo formato
                    const P = modalForm.teamwork || '-';
                    const Pr = modalForm.project || '-';
                    const A = modalForm.attendance || '-';
                    const E = modalForm.exam || '-';
                    const Ex = modalForm.extra || '-';
                    
                    // Calcular el promedio solo si hay al menos un valor numérico
                    const values = [P, Pr, A, E, Ex].map(v => parseFloat(v) || 0);
                    const validValues = values.filter(v => v > 0);
                    const Prom = validValues.length > 0 
                        ? (validValues.reduce((a, b) => a + b, 0) / validValues.length).toFixed(2)
                        : '-';

                    return { P, Pr, A, E, Ex, Prom };
                }
                // Mantener las otras evaluaciones como están o usar el formato por defecto
                return evaluation || {
                    P: '-',
                    Pr: '-',
                    A: '-',
                    E: '-',
                    Ex: '-',
                    Prom: '-'
                };
            });

            const response = await axios.post('/grades', {
                student_id: parseInt(modalContext.studentId),
                subject_id: parseInt(modalContext.subjectId),
                evaluations: evaluationsToSend
            });

            if (response.data.success) {
                toast.success(response.data.message);
                // Notificación inteligente para estados de riesgo o reprobado
                const estado = response.data.grade?.estado?.toLowerCase();
                if (estado === 'reprobado') {
                    toast.error('¡Atención! El alumno ha quedado REPROBADO en esta materia.');
                } else if (estado === 'en riesgo' || estado === 'en riesgo') {
                    toast('El alumno está EN RIESGO en esta materia.', { icon: '⚠️', style: { background: '#F59E0B', color: '#78350f' } });
                }
                setModalOpen(false);

                // Actualizar el estado local con las nuevas calificaciones
                setStudentsGrades(prevGrades => {
                    return prevGrades.map(student => {
                        if (student.id === parseInt(modalContext.studentId)) {
                            const updatedGradesBySubject = { ...student.grades_by_subject };
                            updatedGradesBySubject[modalContext.subjectId] = {
                                ...updatedGradesBySubject[modalContext.subjectId],
                                evaluations: evaluationsToSend,
                                promedio_final: response.data.grade?.promedio_final ?? 0,
                                estado: response.data.grade?.estado ?? 'Pendiente',
                                puntos_faltantes: response.data.grade?.puntos_faltantes ?? 0
                            };
                            return {
                                ...student,
                                grades_by_subject: updatedGradesBySubject
                            };
                        }
                        return student;
                    });
                });

                // Limpiar el contexto del modal
                setModalContext({});
                setModalForm({
                    teamwork: '',
                    project: '',
                    attendance: '',
                    exam: '',
                    extra: ''
                });
            } else {
                throw new Error(response.data.message || 'Error al guardar las calificaciones');
            }
        } catch (error) {
            console.error('Error al guardar en el modal:', error);
            const errorMessage = error.response?.data?.message || 
                               (error.response?.data?.errors ? Object.values(error.response.data.errors).flat().join(', ') : null) ||
                               error.message ||
                               'Error al guardar las calificaciones';
            toast.error(errorMessage);
        }
    };

    const onModalSubmit = (e) => {
        e.preventDefault();
        handleModalSave();
    };

    const calcularPromedioUnidad = (evalObj) => {
        if (!evalObj) return 0;
        const teamwork = parseFloat(evalObj.teamwork) || 0;
        const project = parseFloat(evalObj.project) || 0;
        const attendance = parseFloat(evalObj.attendance) || 0;
        const exam = parseFloat(evalObj.exam) || 0;
        const extra = parseFloat(evalObj.extra) || 0;
        return teamwork + project + attendance + exam + extra;
    };

    const determinarEstado = (promedio) => {
        const promedioNum = parseFloat(promedio);
        if (promedioNum >= 7) return { text: 'Aprobado', color: 'text-green-600' };
        if (promedioNum >= 6) return { text: 'En Riesgo', color: 'text-yellow-600' };
        return { text: 'Reprobado', color: 'text-red-600' };
    };

    const calcularPuntosFaltantes = (promedio) => {
        const promedioNum = parseFloat(promedio);
        return promedioNum < 7 ? (7 - promedioNum).toFixed(2) : 0;
    };

    const mapEvaluationFields = (evalObj) => {
        if (!evalObj) return {teamwork: 0, project: 0, attendance: 0, exam: 0, extra: 0};
        // Si ya tiene los campos correctos, retorna igual
        if ('teamwork' in evalObj) return evalObj;
        // Si viene del backend con P, Pr, A, E, Ex
        return {
            teamwork: evalObj.P ?? 0,
            project: evalObj.Pr ?? 0,
            attendance: evalObj.A ?? 0,
            exam: evalObj.E ?? 0,
            extra: evalObj.Ex ?? 0
        };
    };

    const renderGradesTable = () => {
        if (!Array.isArray(studentsGrades) || studentsGrades.length === 0) {
            return (
                <tr>
                    <td colSpan="11" className="border border-gray-300 px-1 py-1 text-center">
                        No hay calificaciones registradas
                    </td>
                </tr>
            );
        }

        return studentsGrades.map((student, studentIdx) => {
            if (!student?.grades_by_subject) return null;

            // Paleta de colores suaves
            const rowColors = [
                'bg-white',
                'bg-blue-50',
                'bg-green-50',
                'bg-yellow-50',
                'bg-pink-50',
                'bg-purple-50',
                'bg-indigo-50',
                'bg-gray-50',
            ];
            const colorClass = rowColors[studentIdx % rowColors.length];

            const gradesArr = Object.entries(student.grades_by_subject || {}).map(([subjectId, grade], idx) => {
                if (!grade) return null;
                if (Array.isArray(grade)) grade = grade[0];
                if (!grade) return null;

                const promediosPonderados = [0,1,2,3].map(i => {
                    const evalObj = mapEvaluationFields(grade.evaluations?.[i]);
                    return (
                        (parseFloat(evalObj.teamwork) || 0) * 0.3 +
                        (parseFloat(evalObj.project) || 0) * 0.3 +
                        (parseFloat(evalObj.attendance) || 0) * 0.1 +
                        (parseFloat(evalObj.exam) || 0) * 0.3 +
                        (parseFloat(evalObj.extra) || 0) * 0.0
                    );
                });
                const promedioFinal = (promediosPonderados.reduce((a, b) => a + b, 0) / 4).toFixed(2);
                const puntosFaltantes = calcularPuntosFaltantes(promedioFinal);
                const estado = determinarEstado(promedioFinal);

                return (
                    <tr key={`${student.id}-${subjectId}`} className={`align-middle ${colorClass} hover:bg-blue-100 transition-colors duration-150 text-xs`}>
                        {idx === 0 && (
                            <React.Fragment>
                                <td className="border border-gray-300 px-0.5 py-0.5 whitespace-nowrap align-middle font-semibold" 
                                    rowSpan={Object.keys(student.grades_by_subject).length}>
                                    {student.matricula}
                                </td>
                                <td className="border border-gray-300 px-0.5 py-0.5 whitespace-nowrap align-middle font-semibold" 
                                    rowSpan={Object.keys(student.grades_by_subject).length}>
                                    {student.nombre} {student.apellido_paterno}
                                </td>
                            </React.Fragment>
                        )}
                        <td className="border border-gray-300 px-0.5 py-0.5 whitespace-nowrap align-middle w-12 max-w-[48px] text-[9px] overflow-hidden text-ellipsis">
                            {grade.subject_name}
                        </td>
                        {[0,1,2,3].map((evalIndex) => {
                            const realGrade = Array.isArray(grade) ? grade[0] : grade;
                            const evalObj = mapEvaluationFields(Array.isArray(realGrade?.evaluations) && realGrade.evaluations[evalIndex]
                                ? realGrade.evaluations[evalIndex]
                                : null);
                            return (
                                <td key={evalIndex}
                                    className="border border-gray-300 px-0.5 py-0.5 whitespace-nowrap align-middle text-center cursor-pointer hover:bg-blue-100"
                                    onClick={() => openUnitModal(student.id, subjectId, evalIndex, evalObj, realGrade)}>
                                    <div className="space-y-1 text-sm">
                                        <div><span className="font-bold text-blue-600">P:</span> <span style={{color: 'black'}}>{evalObj.teamwork}</span></div>
                                        <div><span className="font-bold text-green-600">Pr:</span> <span style={{color: 'black'}}>{evalObj.project}</span></div>
                                        <div><span className="font-bold text-yellow-600">A:</span> <span style={{color: 'black'}}>{evalObj.attendance}</span></div>
                                        <div><span className="font-bold text-purple-600">E:</span> <span style={{color: 'black'}}>{evalObj.exam}</span></div>
                                        <div><span className="font-bold text-red-600">Ex:</span> <span style={{color: 'black'}}>{evalObj.extra}</span></div>
                                        <div className="mt-2 pt-1 border-t border-gray-300 font-bold text-blue-600">
                                            Prom: {(
                                                (parseFloat(evalObj.teamwork) || 0) * 0.3 +
                                                (parseFloat(evalObj.project) || 0) * 0.3 +
                                                (parseFloat(evalObj.attendance) || 0) * 0.1 +
                                                (parseFloat(evalObj.exam) || 0) * 0.3 +
                                                (parseFloat(evalObj.extra) || 0) * 0.0
                                            ).toFixed(2)}
                                        </div>
                                    </div>
                                </td>
                            );
                        })}
                        <td className="border border-gray-300 px-0.5 py-0.5 whitespace-nowrap align-middle text-center font-bold">
                            {promedioFinal}
                        </td>
                        <td className={`border border-gray-300 px-0.5 py-0.5 whitespace-nowrap align-middle text-center font-bold ${estado.color}`}>
                            {estado.text}
                        </td>
                        <td className="border border-gray-300 px-0.5 py-0.5 whitespace-nowrap align-middle text-center">
                            {puntosFaltantes > 0 ? puntosFaltantes : '-'}
                        </td>
                        <td className="border border-gray-300 px-0.5 py-0.5 whitespace-nowrap align-middle text-center">
                            <button
                                className="text-red-600 hover:bg-red-100 hover:text-red-800 px-1 py-1 rounded transition-colors duration-150 text-xs"
                                onClick={() => handleDelete(grade.id)}>
                                Eliminar
                            </button>
                            {idx === 0 && (
                                <button
                                    className="ml-2 text-white bg-red-600 hover:bg-red-800 px-1 py-1 rounded transition-colors duration-150 text-xs"
                                    onClick={() => {
                                        if (window.confirm('¿Seguro que deseas eliminar al alumno y todas sus calificaciones?')) {
                                            handleDeleteStudent(student.id);
                                        }
                                    }}>
                                    Eliminar Alumno
                                </button>
                            )}
                        </td>
                    </tr>
                );
            }).filter(Boolean);

            if (gradesArr.length === 0) return null;

            const promediosPorUnidad = [0,1,2,3].map(unitIdx => {
                let sum = 0, count = 0;
                Object.values(student.grades_by_subject || {}).forEach(grade => {
                    if (!grade) return;
                    if (Array.isArray(grade)) grade = grade[0];
                    if (!grade?.evaluations?.[unitIdx]) return;
                    
                    const promedio = calcularPromedioUnidad(grade.evaluations[unitIdx]);
                    if (promedio > 0) {
                        sum += promedio;
                        count++;
                    }
                });
                return count > 0 ? (sum / count).toFixed(2) : '-';
            });

            return (
                <React.Fragment key={student.id}>
                    {gradesArr}
                    <tr className={`${colorClass} font-bold`}>
                        <td colSpan={3} className="border border-gray-300 px-0.5 py-0.5">Promedio por Unidad</td>
                        {promediosPorUnidad.map((prom, idx) => (
                            <td key={idx} className="border border-gray-300 px-0.5 py-0.5 text-center">
                                {prom}
                            </td>
                        ))}
                        <td colSpan={4} className="border border-gray-300"></td>
                    </tr>
                </React.Fragment>
            );
        }).filter(Boolean);
    };

    const handleDeleteStudent = async (studentId) => {
        if (window.confirm('¿Seguro que deseas eliminar al alumno y todas sus calificaciones?')) {
            await fetch(`/students/${studentId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf_token,
                },
            });
        window.location.reload();
        }
    };

    // Calcular suma de la calificación de la unidad en el modal
    const unitSum =
        (parseFloat(modalForm.teamwork) || 0) +
        (parseFloat(modalForm.project) || 0) +
        (parseFloat(modalForm.attendance) || 0) +
        (parseFloat(modalForm.exam) || 0) +
        (parseFloat(modalForm.extra) || 0);

    const handleEvaluationChange = (evalIndex, field, value) => {
        const newEvaluations = [...evaluations];
        newEvaluations[evalIndex] = {
            ...newEvaluations[evalIndex],
            [field]: value
        };

        // Calcular promedio si todos los campos tienen valor numérico
        const fields = ['P', 'Pr', 'A', 'E', 'Ex'];
        const values = fields.map(f => newEvaluations[evalIndex][f])
                           .filter(v => v !== '-')
                           .map(v => parseFloat(v));

        if (values.length === fields.length) {
            const promedio = values.reduce((a, b) => a + b, 0) / values.length;
            newEvaluations[evalIndex].Prom = promedio.toFixed(2);
        } else {
            newEvaluations[evalIndex].Prom = '-';
        }

        setEvaluations(newEvaluations);
    };

    const handleSave = async () => {
        try {
            // Validar que tengamos un estudiante y materia seleccionados
            if (!selectedStudent || !selectedSubject) {
                toast.error('Por favor selecciona un estudiante y una materia');
                return;
            }

            // Asegurarse de que las evaluaciones estén en el formato correcto
            const evaluationsToSend = evaluations.map(evaluation => ({
                P: evaluation.P || '-',
                Pr: evaluation.Pr || '-',
                A: evaluation.A || '-',
                E: evaluation.E || '-',
                Ex: evaluation.Ex || '-',
                Prom: evaluation.Prom || '-'
            }));

            const response = await axios.post('/grades', {
                student_id: parseInt(selectedStudent),
                subject_id: parseInt(selectedSubject),
                evaluations: evaluationsToSend
            });

            if (response.data.message) {
                toast.success(response.data.message, {
                    duration: 4000,
                    position: 'top-right',
                });

                // Actualizar el estado local con las nuevas calificaciones
                setStudentsGrades(prevGrades => {
                    return prevGrades.map(student => {
                        if (student.id === parseInt(selectedStudent)) {
                            const updatedGradesBySubject = { ...student.grades_by_subject };
                            updatedGradesBySubject[selectedSubject] = {
                                ...updatedGradesBySubject[selectedSubject],
                                evaluations: evaluationsToSend,
                                promedio_final: response.data.grade.promedio_final,
                                estado: response.data.grade.estado,
                                puntos_faltantes: response.data.grade.puntos_faltantes
                            };
                            return {
                                ...student,
                                grades_by_subject: updatedGradesBySubject
                            };
                        }
                        return student;
                    });
                });

                // Mantener las evaluaciones actuales en el estado
                setEvaluations(evaluationsToSend);

                // Actualizar la vista si es necesario
                if (typeof onSuccess === 'function') {
                    onSuccess();
                }
            }
        } catch (error) {
            console.error('Error al guardar calificaciones:', error);
            const errorMessage = error.response?.data?.message || 
                               error.response?.data?.errors ? Object.values(error.response.data.errors).flat().join(', ') :
                               'Error al guardar las calificaciones. Por favor intenta de nuevo.';
            toast.error(errorMessage, {
                duration: 4000,
                position: 'top-right',
            });
        }
    };

    // Renderizado de la tabla de evaluaciones
    const renderEvaluationsTable = () => (
        <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
                <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Eval.</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pr</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">A</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ex</th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prom</th>
                </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
                {evaluations.map((evaluation, index) => (
                    <tr key={index}>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {index + 1}
                        </td>
                        {['P', 'Pr', 'A', 'E', 'Ex', 'Prom'].map((field) => (
                            <td key={field} className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {field === 'Prom' ? (
                                    evaluation[field]
                                ) : (
                                    <>
                                        <label htmlFor={`eval-${index}-${field}`} className="sr-only">{field}</label>
                                        <input
                                            id={`eval-${index}-${field}`}
                                            name={field}
                                            type="text"
                                            className="w-16 p-1 border rounded"
                                            value={evaluation[field]}
                                            onChange={(e) => handleEvaluationChange(index, field, e.target.value)}
                                        />
                                    </>
                                )}
                            </td>
                        ))}
                    </tr>
                ))}
            </tbody>
        </table>
    );

    return (
        <div className="space-y-6">
                <Card className="p-6">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <a
                        href="/grades/export"
                        className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow text-xs font-semibold transition-colors duration-150"
                        download
                    >
                        Exportar Calificaciones
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
                            Importar Calificaciones
                        </button>
                    </form>
                        </div>
                {importMessage && <div className="mt-2 text-green-700 text-xs">{importMessage}</div>}
                {importError && <div className="mt-2 text-red-600 text-xs">{importError}</div>}
                <h3 className="text-lg font-semibold mb-4">Registro de Evaluaciones</h3>
                <form className="space-y-4" onSubmit={handleSubmit}>
                        <div>
                        <label className="block text-sm font-medium text-gray-700">Matrícula</label>
                        <select
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={selectedStudentId}
                            onChange={handleStudentChange}
                            required
                        >
                            <option value="">Seleccionar Matrícula</option>
                            {studentsGrades.map(student => (
                                <option key={student.id} value={student.id}>{student.matricula}</option>
                            ))}
                        </select>
                        </div>
                        <div>
                        <label className="block text-sm font-medium text-gray-700">Nombre</label>
                                    <input
                                        type="text"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100"
                            value={selectedStudent ? `${selectedStudent.nombre} ${selectedStudent.apellido_paterno} ${selectedStudent.apellido_materno}` : ''}
                            readOnly
                                    />
                                </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Fecha</label>
                                    <input
                            type="date"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            value={form.date}
                            onChange={handleChange}
                            name="date"
                            required
                                    />
                                </div>
                    <div>
                        <button type="submit" className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Registrar Alumno
                        </button>
                    </div>
                    </form>
                {message && (
                    <div className="mt-4 p-4 rounded-md bg-green-50 text-green-700">
                        {message}
                    </div>
                )}
                </Card>

            <Card className="p-6">
                <h3 className="text-lg font-semibold mb-4">Calificaciones Registradas</h3>
                <div className="w-full">
                    <table className="w-full border text-xs">
                        <thead>
                            <tr className="bg-gray-100">
                                <th className="border border-gray-300 px-0.5 py-0.5 w-12 max-w-[48px] text-[9px] whitespace-nowrap overflow-hidden text-ellipsis">Mat.</th>
                                <th className="border border-gray-300 px-0.5 py-0.5 w-32 text-[11px] whitespace-nowrap">Alumno</th>
                                <th className="border border-gray-300 px-0.5 py-0.5 w-20 text-[10px] whitespace-nowrap overflow-hidden text-ellipsis">Materia</th>
                                <th className="border border-gray-300 px-0.5 py-0.5 text-[11px] whitespace-nowrap">E1</th>
                                <th className="border border-gray-300 px-0.5 py-0.5 text-[11px] whitespace-nowrap">E2</th>
                                <th className="border border-gray-300 px-0.5 py-0.5 text-[11px] whitespace-nowrap">E3</th>
                                <th className="border border-gray-300 px-0.5 py-0.5 text-[11px] whitespace-nowrap">E4</th>
                                <th className="border border-gray-300 px-0.5 py-0.5 text-[11px] whitespace-nowrap">Prom.</th>
                                <th className="border border-gray-300 px-0.5 py-0.5 text-[11px] whitespace-nowrap">Estado</th>
                                <th className="border border-gray-300 px-0.5 py-0.5 text-[11px] whitespace-nowrap">Faltan</th>
                                <th className="border border-gray-300 px-0.5 py-0.5 text-[11px] whitespace-nowrap">Acc.</th>
                            </tr>
                        </thead>
                        <tbody>
                            {renderGradesTable()}
                        </tbody>
                    </table>
                </div>
            </Card>

            <Modal show={modal.open} onClose={closeModal}>
                <div className="p-6">
                    <h3 className="text-lg font-semibold mb-4">
                        {modal.mode === 'view' ? 'Detalles de la Calificación' : 'Editar Calificación'}
                    </h3>
                    {modal.mode === 'view' ? (
                        <div className="space-y-4">
                            <div>
                                <h4 className="font-medium">{modal.grade?.student?.nombre} {modal.grade?.student?.apellido_paterno}</h4>
                                <p className="text-sm text-gray-600">{modal.grade?.subject?.name}</p>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm">Trabajo en Equipo: {modal.grade?.teamwork || '-'}</p>
                                    <p className="text-sm">Proyecto: {modal.grade?.project || '-'}</p>
                                    <p className="text-sm">Asistencia: {modal.grade?.attendance || '-'}</p>
                                    <p className="text-sm">Examen: {modal.grade?.exam || '-'}</p>
                                    <p className="text-sm">Extra: {modal.grade?.extra || '-'}</p>
                                </div>
                                <div>
                                    <p className="text-sm font-medium">Calificación Final: {modal.grade?.score || '-'}</p>
                                    <p className={`text-sm font-medium ${modal.grade?.score >= 7 ? 'text-green-600' : 'text-red-600'}`}>
                                        Estado: {modal.grade?.score >= 7 ? 'Aprobado' : 'Reprobado'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    ) : (
                        <form onSubmit={e => {e.preventDefault(); handleModalSave();}} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Estudiante</label>
                                <select name="student_id" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value={form.student_id} onChange={handleChange} required>
                                    <option value="">Seleccionar Estudiante</option>
                                    {rubrics?.map(student => (
                                        <option key={student.id} value={student.id}>{`${student.apellido_paterno} ${student.apellido_materno} ${student.nombre}`}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Materia</label>
                                <select name="subject_id" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value={form.subject_id} onChange={handleChange} required>
                                    <option value="">Seleccionar Materia</option>
                                    {subjects?.map(subject => (
                                        <option key={subject.id} value={subject.id}>{subject.name}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Fecha</label>
                                <input type="date" name="date" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value={form.date} onChange={handleChange} required />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Trabajo en Equipo (30%)</label>
                                    <input type="number" step="0.1" min="0" max="10" name="teamwork" id="teamwork" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style={{color: 'black'}} value={form.teamwork} onChange={handleChange} required />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Proyecto (30%)</label>
                                    <input type="number" step="0.1" min="0" max="10" name="project" id="project" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style={{color: 'black'}} value={form.project} onChange={handleChange} required />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Asistencia (10%)</label>
                                    <input type="number" step="0.1" min="0" max="10" name="attendance" id="attendance" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style={{color: 'black'}} value={form.attendance} onChange={handleChange} required />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Examen (30%)</label>
                                    <input type="number" step="0.1" min="0" max="10" name="exam" id="exam" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style={{color: 'black'}} value={form.exam} onChange={handleChange} required />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Puntos Extra</label>
                                    <input type="number" step="0.1" min="0" max="10" name="extra" id="extra" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" style={{color: 'black'}} value={form.extra} onChange={handleChange} />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Calificación Final</label>
                                    <input type="text" className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50" value={finalScore} readOnly />
                                </div>
                            </div>
                            <div style={{ marginTop: 8, fontWeight: 'bold' }}>
                                Total: {unitSum.toFixed(2)}/10
                            </div>
                            {unitSum < 7 && (
                                <div style={{ color: 'red', marginTop: 8 }}>
                                    Advertencia: La calificación total de esta unidad es menor a 7.
                                </div>
                            )}
                            <div className="flex justify-end gap-4 mt-6">
                                <button
                                    type="button"
                                    className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    onClick={() => setModalOpen(false)}
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Guardar
                                </button>
                            </div>
                        </form>
                    )}
                </div>
            </Modal>
            <Modal show={modalOpen} onClose={() => setModalOpen(false)}>
                <div className="p-6">
                    <h3 className="text-lg font-semibold mb-4">Editar Unidad</h3>
                    <form className="space-y-4" onSubmit={onModalSubmit}>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label htmlFor="modal-teamwork" className="block text-sm font-medium text-gray-700">Trabajo en equipo (30%)</label>
                                <input
                                    type="number"
                                    name="teamwork"
                                    id="modal-teamwork"
                                    min="0"
                                    max="10"
                                    step="0.1"
                                    value={modalForm.teamwork}
                                    onChange={handleModalChange}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style={{color: 'black'}}
                                />
                            </div>
                            <div>
                                <label htmlFor="modal-project" className="block text-sm font-medium text-gray-700">Proyecto (30%)</label>
                                <input
                                    type="number"
                                    name="project"
                                    id="modal-project"
                                    min="0"
                                    max="10"
                                    step="0.1"
                                    value={modalForm.project}
                                    onChange={handleModalChange}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style={{color: 'black'}}
                                />
                            </div>
                            <div>
                                <label htmlFor="modal-attendance" className="block text-sm font-medium text-gray-700">Asistencia (10%)</label>
                                <input
                                    type="number"
                                    name="attendance"
                                    id="modal-attendance"
                                    min="0"
                                    max="10"
                                    step="0.1"
                                    value={modalForm.attendance}
                                    onChange={handleModalChange}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style={{color: 'black'}}
                                />
                            </div>
                            <div>
                                <label htmlFor="modal-exam" className="block text-sm font-medium text-gray-700">Examen (30%)</label>
                                <input
                                    type="number"
                                    name="exam"
                                    id="modal-exam"
                                    min="0"
                                    max="10"
                                    step="0.1"
                                    value={modalForm.exam}
                                    onChange={handleModalChange}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style={{color: 'black'}}
                                />
                            </div>
                            <div>
                                <label htmlFor="modal-extra" className="block text-sm font-medium text-gray-700">Puntos extra</label>
                                <input
                                    type="number"
                                    name="extra"
                                    id="modal-extra"
                                    min="0"
                                    max="10"
                                    step="0.1"
                                    value={modalForm.extra}
                                    onChange={handleModalChange}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style={{color: 'black'}}
                                />
                            </div>
                        </div>
                        <div className="flex justify-end gap-4 mt-6">
                            <button
                                type="button"
                                className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                onClick={() => setModalOpen(false)}
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>
        </div>
    );
} 