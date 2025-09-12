import React, { useState } from 'react';
import { Card } from '@/Components/UI/Card';
import { router } from '@inertiajs/react';
import toast from 'react-hot-toast';

export default function StudentsModule({ stats, showToast, showLoader }) {
    const [form, setForm] = useState({
        matricula: '',
        nombre: '',
        apellido_paterno: '',
        apellido_materno: '',
        group_id: '',
        birth_date: ''
    });
    const [error, setError] = useState('');
    const [search, setSearch] = useState('');
    const [modal, setModal] = useState({ open: false, mode: '', student: null });
    const [importMessage, setImportMessage] = useState('');
    const [importError, setImportError] = useState('');

    const students = stats?.students || [];

    const handleChange = (e) => {
        const { name, value } = e.target;
        setForm(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        
        // Usar Inertia.js para el envío del formulario
        router.post('/students', form, {
            onSuccess: () => {
                // Limpiar formulario después del éxito
                setForm({ 
                    matricula: '', 
                    nombre: '', 
                    apellido_paterno: '', 
                    apellido_materno: '', 
                    group_id: '', 
                    birth_date: '' 
                });
                setError('');
                // Mostrar mensaje de éxito
                toast.success('¡Alumno agregado correctamente!');
            },
            onError: (errors) => {
                // Manejar errores de validación
                const errorMessage = Object.values(errors).flat().join(', ');
                setError(errorMessage);
                toast.error(errorMessage);
            },
            onFinish: () => {
                // Ocultar loader si existe
                if (showLoader) {
                    showLoader(false);
                }
            }
        });
    };

    const handleView = async (student) => {
        router.visit(`/students/${student.id}`);
    };

    const handleEdit = async (student) => {
        setForm({
            ...student,
            group_id: student.group_id || (student.group ? student.group.id : ''),
        });
        setModal({ open: true, mode: 'edit', student: student });
    };

    const handleUpdate = async (e) => {
        e.preventDefault();
        setError('');
        
        router.put(`/students/${modal.student.id}`, form, {
            onSuccess: () => {
                toast.success('¡Alumno actualizado correctamente!');
                setModal({ open: false, mode: '', student: null });
                setForm({ 
                    matricula: '', 
                    nombre: '', 
                    apellido_paterno: '', 
                    apellido_materno: '', 
                    group_id: '', 
                    birth_date: '' 
                });
            },
            onError: (errors) => {
                const errorMessage = Object.values(errors).flat().join(', ');
                setError(errorMessage);
                toast.error(errorMessage);
            }
        });
    };

    const handleDelete = async (id) => {
        if (window.confirm('¿Seguro que deseas eliminar este estudiante?')) {
                    router.delete(`/students/${id}`, {
            onSuccess: () => {
                toast.success('¡Estudiante eliminado correctamente!');
            },
            onError: () => {
                toast.error('Error al eliminar estudiante.');
            }
        });
        }
    };

    const closeModal = () => {
        setModal({ open: false, mode: '', student: null });
        setForm({ matricula: '', nombre: '', apellido_paterno: '', apellido_materno: '', group_id: '', birth_date: '' });
    };

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
        
        router.post('/students/import', formData, {
            onSuccess: () => {
                setImportMessage('¡Importación completada!');
                toast.success('¡Importación completada!');
            },
            onError: () => {
                setImportError('Error al importar.');
                toast.error('Error al importar.');
            }
        });
    };

    // Filtrado automático por nombre o matrícula
    const filteredStudents = students.filter(student => {
        const fullName = `${student.apellido_paterno} ${student.apellido_materno} ${student.nombre}`.toLowerCase();
        return (
            student.matricula.toLowerCase().includes(search.toLowerCase()) ||
            fullName.includes(search.toLowerCase())
        );
    });

    return (
        <div className="space-y-6">
            <Card className="p-6">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                    <a
                        href="/students/export"
                        className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow text-xs font-semibold transition-colors duration-150"
                        download
                    >
                        Exportar Excel
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
                            Importar Excel
                        </button>
                    </form>
                </div>
                {importMessage && <div className="mt-2 text-green-700 text-xs">{importMessage}</div>}
                {importError && <div className="mt-2 text-red-600 text-xs">{importError}</div>}
                <h3 className="text-lg font-semibold mb-4">Agregar Alumno</h3>
                <form className="space-y-4" onSubmit={handleSubmit}>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Matrícula</label>
                        <input
                            type="text"
                            name="matricula"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={form.matricula}
                            onChange={handleChange}
                            required
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Nombre(s)</label>
                        <input
                            type="text"
                            name="nombre"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={form.nombre}
                            onChange={handleChange}
                            required
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Apellido Paterno</label>
                        <input
                            type="text"
                            name="apellido_paterno"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={form.apellido_paterno}
                            onChange={handleChange}
                            required
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Apellido Materno</label>
                        <input
                            type="text"
                            name="apellido_materno"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={form.apellido_materno}
                            onChange={handleChange}
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Fecha de Nacimiento</label>
                        <input
                            type="date"
                            name="birth_date"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={form.birth_date}
                            onChange={handleChange}
                            required
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Grupo</label>
                        <select
                            name="group_id"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={form.group_id}
                            onChange={handleChange}
                            required
                        >
                            <option value="">Selecciona un grupo</option>
                            {(stats?.groups || []).map(group => (
                                <option key={group.id} value={group.id}>
                                    {group.name}
                                </option>
                            ))}
                        </select>
                    </div>
                    {error && (
                        <div className="text-red-600 text-sm">{error}</div>
                    )}
                    <div>
                        <button type="submit" className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Registrar Alumno
                        </button>
                    </div>
                </form>
            </Card>

            <Card className="p-6">
                <h3 className="text-lg font-semibold mb-4">Estudiantes Registrados</h3>
                <div className="mb-4 flex flex-col sm:flex-row gap-2 items-start sm:items-center">
                    <input
                        type="text"
                        placeholder="Buscar por nombre o matrícula..."
                        className="border rounded px-3 py-2 w-full sm:w-64 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                    />
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-full border text-sm">
                        <thead className="bg-gray-100">
                            <tr>
                                <th className="border border-gray-300 px-2 py-1">Matrícula</th>
                                <th className="border border-gray-300 px-2 py-1">Nombre Completo</th>
                                <th className="border border-gray-300 px-2 py-1">Grupo</th>
                                <th className="border border-gray-300 px-2 py-1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredStudents.map(student => (
                                <tr key={student.id}>
                                    <td className="border border-gray-300 px-2 py-1">{student.matricula}</td>
                                    <td className="border border-gray-300 px-2 py-1">{student.apellido_paterno} {student.apellido_materno} {student.nombre}</td>
                                    <td className="border border-gray-300 px-2 py-1">{student.group?.name}</td>
                                    <td className="border border-gray-300 px-2 py-1">
                                        <button onClick={() => handleView(student)} className="text-blue-500 hover:text-blue-700">Ver</button>
                                        <button onClick={() => handleEdit(student)} className="ml-2 text-yellow-500 hover:text-yellow-700">Editar</button>
                                        <button onClick={() => handleDelete(student.id)} className="ml-2 text-red-500 hover:text-red-700">Eliminar</button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>
    );
} 