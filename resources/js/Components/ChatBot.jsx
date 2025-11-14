import React, { useState, useRef, useCallback, useEffect } from 'react';
import axios from 'axios';

export default function ChatBot() {
  const STORAGE_KEY = 'iaedu-chatbot-history';
  const STORAGE_OPEN_KEY = 'iaedu-chatbot-open';

  const defaultMessages = [
    { from: 'bot', text: '¡Hola! ¿En qué te puedo ayudar hoy?' },
    { from: 'bot', text: 'Puedo darte datos reales de alumnos, asistencias, calificaciones, horarios y alertas de riesgo. Selecciona una opción rápida o pregúntame lo que necesites.' },
  ];

  const [open, setOpen] = useState(() => {
    if (typeof window === 'undefined') return false;
    const saved = window.localStorage?.getItem(STORAGE_OPEN_KEY);
    return saved ? JSON.parse(saved) : false;
  });
  const [messages, setMessages] = useState(() => {
    if (typeof window === 'undefined') return defaultMessages;
    const saved = window.localStorage?.getItem(STORAGE_KEY);
    if (!saved) return defaultMessages;
    try {
      const parsed = JSON.parse(saved);
      return Array.isArray(parsed) && parsed.length ? parsed : defaultMessages;
    } catch (error) {
      console.warn('No se pudo leer el historial del chatbot:', error);
      return defaultMessages;
    }
  });
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);
  const [position, setPosition] = useState({ top: 120, right: 32 }); // px
  const dragData = useRef({ dragging: false, offsetX: 0, offsetY: 0, moved: false });
  const lastRequestTime = useRef(0);

  // Rate limiting: máximo 1 petición cada 5 segundos (más conservador para evitar 429)
  const RATE_LIMIT_DELAY = 5000;

  const [stats, setStats] = useState({
    dashboard: null,
    attendance: null,
    risk: null,
    grades: null,
    schedules: null,
  });
  const [statsLoading, setStatsLoading] = useState(false);

  const formatNumber = useCallback((value) => {
    if (value === null || value === undefined || Number.isNaN(Number(value))) {
      return '—';
    }
    try {
      return Number(value).toLocaleString('es-MX');
    } catch (_error) {
      return String(value);
    }
  }, []);

  const formatDecimal = useCallback((value, digits = 2) => {
    if (value === null || value === undefined || Number.isNaN(Number(value))) {
      return '—';
    }
    return Number(value).toFixed(digits);
  }, []);

  const formatPercent = useCallback((value) => {
    if (value === null || value === undefined || Number.isNaN(Number(value))) {
      return '—';
    }
    return `${Number(value).toFixed(1)}%`;
  }, []);

  useEffect(() => {
    let active = true;
    const fetchStats = async () => {
      setStatsLoading(true);
      try {
        const nextStats = {
          dashboard: null,
          attendance: null,
          risk: null,
          grades: null,
          schedules: null,
        };

        const fetchAndStore = async (key, request) => {
          try {
            const res = await request();
            const data = res?.data?.data ?? null;
            if (active) {
              nextStats[key] = data;
            }
          } catch (error) {
            console.warn(`No se pudo cargar ${key}:`, error?.response?.status ?? error?.message ?? error);
            if (active) {
              nextStats[key] = null;
            }
          }
        };

        await Promise.all([
          fetchAndStore('dashboard', () => axios.get('/api/dashboard/stats', { headers: { Accept: 'application/json' } })),
          fetchAndStore('attendance', () => axios.get('/api/attendance/statistics', { headers: { Accept: 'application/json' } })),
          fetchAndStore('risk', () => axios.get('/api/risk-analysis/statistics', { headers: { Accept: 'application/json' } })),
          fetchAndStore('grades', () => axios.get('/api/grades/statistics', { headers: { Accept: 'application/json' } })),
          fetchAndStore('schedules', () => axios.get('/api/schedules', {
            params: { per_page: 5, with: 'group,subject,teacher' },
            headers: { Accept: 'application/json' },
          })),
        ]);

        if (active) {
          setStats(nextStats);
        }
      } finally {
        if (active) {
          setStatsLoading(false);
        }
      }
    };

    fetchStats();

    return () => {
      active = false;
    };
  }, []);

  useEffect(() => {
    if (typeof window === 'undefined') return;
    window.localStorage?.setItem(STORAGE_KEY, JSON.stringify(messages));
  }, [messages]);

  useEffect(() => {
    if (typeof window === 'undefined') return;
    window.localStorage?.setItem(STORAGE_OPEN_KEY, JSON.stringify(open));
  }, [open]);

  const summaryCards = [
    {
      key: 'students',
      title: '📋 Alumnos activos',
      value: stats.dashboard
        ? formatNumber(stats.dashboard.total_students)
        : statsLoading ? 'Cargando...' : '—',
      hint: stats.dashboard
        ? `Docentes: ${formatNumber(stats.dashboard.total_teachers)} · Alertas activas: ${formatNumber(stats.dashboard.total_alerts)}`
        : statsLoading ? 'Consultando datos reales...' : 'Datos no disponibles.',
    },
    {
      key: 'attendance',
      title: '🕒 Asistencias',
      value: stats.attendance
        ? formatPercent(stats.attendance.attendance_rate ?? stats.attendance.attendanceRate ?? 0)
        : statsLoading ? 'Cargando...' : '—',
      hint: stats.attendance
        ? `Presentes: ${formatNumber(stats.attendance.present)} · Ausencias: ${formatNumber(stats.attendance.absent)} · Retardos: ${formatNumber(stats.attendance.late ?? 0)}`
        : statsLoading ? 'Calculando métricas de asistencia...' : 'Datos no disponibles.',
    },
    {
      key: 'risk',
      title: '⚠️ Riesgos',
      value: (() => {
        if (stats.risk && stats.risk.high_risk !== undefined) {
          return formatNumber(stats.risk.high_risk);
        }
        if (stats.dashboard && stats.dashboard.high_risk_students !== undefined) {
          return formatNumber(stats.dashboard.high_risk_students);
        }
        return statsLoading ? 'Cargando...' : '—';
      })(),
      hint: stats.risk
        ? `Alto: ${formatNumber(stats.risk.high_risk)} · Medio: ${formatNumber(stats.risk.medium_risk)} · Bajo: ${formatNumber(stats.risk.low_risk)}`
        : statsLoading ? 'Analizando niveles de riesgo...' : 'Datos no disponibles.',
    },
  ];

  const quickOptions = [
    { key: 'students', label: '📋 Información de alumnos' },
    { key: 'attendance', label: '🕒 Asistencias' },
    { key: 'grades', label: '📊 Calificaciones' },
    { key: 'schedules', label: '📅 Horarios' },
    { key: 'risk', label: '⚠️ Riesgos' },
  ];

  const appendMessage = useCallback((message) => {
    setMessages((msgs) => [...msgs, message]);
  }, []);

  const buildQuickResponse = useCallback((key, overrideData = null) => {
    if (statsLoading) {
      return 'Aún estoy recopilando los datos reales; inténtalo de nuevo en unos segundos.';
    }

    switch (key) {
      case 'students': {
        const data = overrideData ?? stats.dashboard;
        if (!data) {
          return 'No pude obtener los datos de alumnos en este momento. Intenta más tarde.';
        }
        const { total_students, total_teachers, total_alerts } = data;
        return [
          `Actualmente hay ${formatNumber(total_students)} alumnos activos y ${formatNumber(total_teachers)} docentes registrados.`,
          `Alertas abiertas: ${formatNumber(total_alerts)} que requieren seguimiento.`,
          'Abre el módulo de Alumnos para buscar por nombre, matrícula o grupo y revisar las fichas completas.'
        ].join('\n');
      }
      case 'attendance': {
        const data = overrideData ?? stats.attendance;
        if (!data) {
          return 'No pude obtener las estadísticas de asistencia ahora mismo.';
        }
        const attendanceRate = formatPercent(data.attendance_rate ?? data.attendanceRate ?? 0);
        return [
          `Asistencia general: ${attendanceRate}.`,
          `Presentes: ${formatNumber(data.present)} · Ausencias: ${formatNumber(data.absent)} · Retardos: ${formatNumber(data.late ?? 0)}.`,
          'Desde el módulo de Asistencias puedes justificar faltas, importar reportes o filtrar por grupo y rango de fechas.'
        ].join('\n');
      }
      case 'grades': {
        const data = overrideData ?? stats.grades;
        if (!data) {
          return 'No pude obtener las estadísticas de calificaciones en este momento.';
        }
        return [
          `Promedio general: ${formatDecimal(data.average)} (máxima ${formatDecimal(data.highest)}, mínima ${formatDecimal(data.lowest)}).`,
          `Aprobados: ${formatNumber(data.approved)} · Reprobados: ${formatNumber(data.failed)}.`,
          'Ingresa al módulo de Calificaciones para revisar el detalle por parcial, exportar reportes o actualizar evaluaciones.'
        ].join('\n');
      }
      case 'schedules': {
        const data = overrideData ?? stats.schedules;
        if (!data) {
          return 'No pude listar los horarios en este momento.';
        }
        const scheduleArray = Array.isArray(data.data)
          ? data.data
          : (Array.isArray(data?.data?.data) ? data.data.data : []);
        const total = data.total ?? data?.meta?.total ?? scheduleArray.length;
        const preview = scheduleArray.slice(0, 3).map((item) => {
          const day = item.day ?? 'Día';
          const start = item.start_time ?? item.hora_inicio ?? '';
          const end = item.end_time ?? item.hora_fin ?? '';
          const subject = item.subject?.name ?? 'Materia';
          const group = item.group?.name ?? 'Grupo';
          const teacher = item.teacher?.user?.name ?? item.teacher?.name ?? '';
          const teacherText = teacher ? ` · ${teacher}` : '';
          return `${day} ${start}-${end} · ${subject} (${group}${teacherText})`;
        });
        let message = `Hay ${formatNumber(total)} horarios registrados actualmente.`;
        if (preview.length) {
          message += `\nEjemplos:\n${preview.join('\n')}`;
        }
        message += '\nGestiona detalles, aulas y docentes desde el módulo de Horarios.';
        return message;
      }
      case 'risk': {
        const data = (overrideData ?? stats.risk) ?? stats.dashboard;
        if (!data) {
          return 'No pude obtener los niveles de riesgo por ahora.';
        }
        const high = data.high_risk ?? data.high_risk_students ?? 0;
        const medium = data.medium_risk ?? 0;
        const low = data.low_risk ?? 0;
        const total = data.total_students ?? stats.dashboard?.total_students ?? 0;
        return [
          `Alumnos analizados: ${formatNumber(total)}.`,
          `Riesgo alto: ${formatNumber(high)} · Riesgo medio: ${formatNumber(medium)} · Riesgo bajo: ${formatNumber(low)}.`,
          'En el módulo de Riesgos puedes revisar recomendaciones, registrar intervenciones y exportar reportes.'
        ].join('\n');
      }
      default:
        return 'Selecciona una de las opciones disponibles o escribe tu consulta.';
    }
  }, [formatDecimal, formatNumber, formatPercent, stats, statsLoading]);

  const clearChat = useCallback(() => {
    setMessages(defaultMessages);
    if (typeof window !== 'undefined') {
      window.localStorage?.removeItem(STORAGE_KEY);
    }
  }, [defaultMessages]);

  const sendMessage = useCallback(async () => {
    if (!input.trim() || loading) return;

    const now = Date.now();
    const timeSinceLastRequest = now - lastRequestTime.current;

    // Si no ha pasado suficiente tiempo desde la última petición
    if (timeSinceLastRequest < RATE_LIMIT_DELAY) {
      const remainingTime = RATE_LIMIT_DELAY - timeSinceLastRequest;
      setMessages(msgs => [...msgs, { 
        from: 'bot', 
        text: `Por favor espera ${Math.ceil(remainingTime / 1000)} segundos antes de hacer otra pregunta.` 
      }]);
      return;
    }

    const userMsg = { from: 'user', text: input };
    appendMessage(userMsg);
    setInput('');
    setLoading(true);
    lastRequestTime.current = now;

    // Agregar mensaje de "pensando" para mostrar que está procesando
    appendMessage({ 
      from: 'bot', 
      text: '🤔 Pensando...',
      isThinking: true 
    });

    try {
      // Obtener token CSRF del meta tag
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      
      const res = await axios.post('/api/chatbot', 
        { question: userMsg.text },
        {
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          withCredentials: true
        }
      );
      
      // Manejar respuesta con error en el formato esperado
      if (res.data.error && !res.data.choices) {
        throw new Error(res.data.error);
      }
      
      const aiText = res.data.choices?.[0]?.message?.content || res.data.error || 'No entendí, ¿puedes repetir?';
      
      // Remover mensaje de "pensando" y agregar respuesta
      setMessages(msgs => {
        const filteredMsgs = msgs.filter(msg => !msg.isThinking);
        return [...filteredMsgs, { from: 'bot', text: aiText }];
      });
    } catch (error) {
      // Remover mensaje de "pensando"
      setMessages(msgs => msgs.filter(msg => !msg.isThinking));
      
      let errorMessage = 'Error al conectar con la IA.';
      
      if (error.response) {
        const { status, data } = error.response;
        
        switch (status) {
          case 429:
            errorMessage = data.error || 'Demasiadas peticiones. Por favor espera 30 segundos antes de hacer otra pregunta.';
            // Esperar 30 segundos antes de permitir otra petición (reducido de 60)
            lastRequestTime.current = Date.now() + 30000;
            
            // Mostrar contador de tiempo restante
            let remainingSeconds = 30;
            const countdown = setInterval(() => {
              remainingSeconds--;
              if (remainingSeconds > 0) {
                setMessages(msgs => {
                  const lastMsg = msgs[msgs.length - 1];
                  if (lastMsg && lastMsg.from === 'bot' && lastMsg.text.includes('espera')) {
                    const newMsgs = [...msgs];
                    newMsgs[newMsgs.length - 1] = {
                      ...lastMsg,
                      text: `⏳ Demasiadas peticiones. Por favor espera ${remainingSeconds} segundos antes de hacer otra pregunta.`
                    };
                    return newMsgs;
                  }
                  return msgs;
                });
              } else {
                clearInterval(countdown);
              }
            }, 1000);
            
            break;
          case 500:
            errorMessage = data.error || 'Error del servidor. Por favor intenta de nuevo en unos momentos.';
            break;
          case 503:
            errorMessage = data.error || 'El servicio de IA está temporalmente no disponible. Intenta de nuevo en unos momentos.';
            break;
          case 400:
            errorMessage = data.error || 'Petición incorrecta.';
            break;
          default:
            errorMessage = data.error || `Error ${status}: Algo salió mal.`;
        }
      } else if (error.request) {
        errorMessage = 'No se pudo conectar con el servidor. Verifica tu conexión a internet.';
      }
      
      setMessages(msgs => [...msgs, { from: 'bot', text: errorMessage }]);
    } finally {
      setLoading(false);
    }
  }, [input, loading]);

  // Drag & Drop Handlers mejorados
  const onMouseDown = (e) => {
    dragData.current.dragging = true;
    dragData.current.moved = false;
    dragData.current.offsetX = e.clientX;
    dragData.current.offsetY = e.clientY;
    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
  };
  
  const onMouseMove = (e) => {
    if (!dragData.current.dragging) return;
    setPosition(pos => {
      const newTop = Math.max(0, pos.top + (e.clientY - dragData.current.offsetY));
      const newRight = Math.max(0, pos.right - (e.clientX - dragData.current.offsetX));
      dragData.current.offsetX = e.clientX;
      dragData.current.offsetY = e.clientY;
      dragData.current.moved = true;
      return { top: newTop, right: newRight };
    });
  };
  
  const onMouseUp = (e) => {
    document.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseup', onMouseUp);
    setTimeout(() => { dragData.current.dragging = false; }, 0);
  };
  
  const onClick = (e) => {
    // Solo abrir/cerrar si NO se arrastró
    if (!dragData.current.moved) setOpen(o => !o);
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  };

  const fetchStatByKey = useCallback(async (key) => {
    const requestConfig = { headers: { Accept: 'application/json' } };

    switch (key) {
      case 'students':
      case 'dashboard':
        return (await axios.get('/api/dashboard/stats', requestConfig)).data?.data ?? null;
      case 'attendance':
        return (await axios.get('/api/attendance/statistics', requestConfig)).data?.data ?? null;
      case 'risk':
        return (await axios.get('/api/risk-analysis/statistics', requestConfig)).data?.data ?? null;
      case 'grades':
        return (await axios.get('/api/grades/statistics', requestConfig)).data?.data ?? null;
      case 'schedules':
        return (await axios.get('/api/schedules', {
          params: { per_page: 5, with: 'group,subject,teacher' },
          headers: { Accept: 'application/json' },
        })).data?.data ?? null;
      default:
        return null;
    }
  }, []);

  const ensureStat = useCallback(async (key) => {
    const normalizedKey = key === 'students' ? 'dashboard' : key;
    if (stats[normalizedKey]) {
      return stats[normalizedKey];
    }

    try {
      const data = await fetchStatByKey(normalizedKey);
      setStats((prev) => ({
        ...prev,
        [normalizedKey]: data,
      }));
      return data;
    } catch (error) {
      console.warn(`Fallo al actualizar ${normalizedKey}:`, error?.response?.status ?? error?.message ?? error);
      return null;
    }
  }, [fetchStatByKey, stats]);

  const handleQuickOption = useCallback(async (option) => {
    if (loading) return;

    appendMessage({ from: 'user', text: option.label });

    const data = await ensureStat(option.key);
    const response = buildQuickResponse(option.key, data);
    appendMessage({ from: 'bot', text: response });
  }, [appendMessage, buildQuickResponse, ensureStat, loading]);

  return (
    <div>
      <button
        onMouseDown={onMouseDown}
        onClick={onClick}
        style={{ position: 'fixed', top: position.top, right: position.right, zIndex: 50, cursor: 'grab' }}
        className="bg-blue-600 text-white rounded-full py-4 px-6 shadow-2xl hover:bg-blue-700 transition-transform transform hover:scale-105"
        aria-label="¿Necesitas ayuda?"
      >
        Chat IA
      </button>
      {open && (
        <div
          className="fixed bg-white rounded-2xl shadow-2xl p-6 z-50 border border-blue-100 flex flex-col animate-[fadeIn_0.18s_ease-out]"
          style={{
            top: position.top + 70,
            right: position.right,
            width: 400,
            maxHeight: '80vh'
          }}
        >
          <div className="flex items-start justify-between mb-3">
            <div>
              <h3 className="font-bold text-blue-600 text-lg">Chat con IA</h3>
              <p className="text-xs text-gray-500">Asistente virtual de IAEDU</p>
            </div>
            <span className="text-xs text-gray-400">Activa 24/7</span>
          </div>

          <div className="grid grid-cols-1 gap-3 mb-4">
            {summaryCards.map((card, idx) => (
              <div key={idx} className="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl px-4 py-3 shadow-sm border border-blue-100">
                <p className="text-sm font-semibold text-blue-700">{card.title}</p>
                <p className="text-xl font-bold text-gray-800">{card.value}</p>
                <p className="text-xs text-gray-600">{card.hint}</p>
              </div>
            ))}
          </div>

          <div className="mb-4">
            <div className="flex items-center justify-between mb-2">
              <p className="text-sm font-semibold text-gray-700">Opciones rápidas</p>
              <button
                onClick={clearChat}
                className="text-xs text-blue-600 hover:underline"
              >
                Borrar chat
              </button>
            </div>
            <div className="flex flex-wrap gap-2">
              {quickOptions.map((option) => (
                <button
                  key={option.key}
                  onClick={() => handleQuickOption(option)}
                  className="bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-3 py-1 text-xs font-medium hover:bg-blue-100 transition-colors"
                  disabled={loading}
                >
                  {option.label}
                </button>
              ))}
            </div>
          </div>

          <div className="flex-1 overflow-y-auto mb-2 space-y-2" style={{ minHeight: 200 }}>
            {messages.map((msg, idx) => (
              <div key={idx} className={`text-sm p-2 rounded ${
                msg.from === 'bot' 
                  ? 'bg-blue-50 text-gray-800' 
                  : 'bg-blue-100 text-right text-blue-900 ml-auto'
              }`}>
                {msg.text}
              </div>
            ))}
            {loading && <div className="text-xs text-gray-400">La IA está escribiendo...</div>}
          </div>
          <div className="flex gap-2">
            <input
              className="flex-1 border rounded px-2 py-1 text-sm"
              value={input}
              onChange={e => setInput(e.target.value)}
              onKeyDown={handleKeyDown}
              placeholder="Escribe tu pregunta..."
              disabled={loading}
            />
            <button
              onClick={sendMessage}
              className={`px-3 py-1 rounded text-sm transition-colors ${
                loading 
                  ? 'bg-gray-400 text-gray-600 cursor-not-allowed' 
                  : 'bg-blue-600 text-white hover:bg-blue-700'
              }`}
              disabled={loading}
            >
              Enviar
            </button>
          </div>
          <div className="mt-3 text-xs text-gray-500 text-center">
            ¿Necesitas ayuda humana? <a href="mailto:soporte@iaedu.com" className="text-blue-600 hover:underline">Contacta al soporte</a>
          </div>
          <button
            onClick={() => setOpen(false)}
            className="mt-2 text-xs text-gray-500 hover:underline"
          >
            Cerrar
          </button>
        </div>
      )}
    </div>
  );
}