import React, { useState, useRef, useCallback } from 'react';
import axios from 'axios';

export default function ChatBot() {
  const [open, setOpen] = useState(false);
  const [messages, setMessages] = useState([
    { from: 'bot', text: '¡Hola! Soy una IA, pregúntame lo que quieras.' }
  ]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);
  const [position, setPosition] = useState({ top: 120, right: 32 }); // px
  const dragData = useRef({ dragging: false, offsetX: 0, offsetY: 0, moved: false });
  const lastRequestTime = useRef(0);
  const requestTimeout = useRef(null);

  // Rate limiting: máximo 1 petición cada 3 segundos
  const RATE_LIMIT_DELAY = 3000;

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
    setMessages(msgs => [...msgs, userMsg]);
    setInput('');
    setLoading(true);
    lastRequestTime.current = now;

    // Agregar mensaje de "pensando" para mostrar que está procesando
    setMessages(msgs => [...msgs, { 
      from: 'bot', 
      text: '🤔 Pensando...',
      isThinking: true 
    }]);

    try {
      const res = await axios.post('/api/chatbot', { question: userMsg.text });
      const aiText = res.data.choices?.[0]?.message?.content || 'No entendí, ¿puedes repetir?';
      
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
            errorMessage = data.error || 'Demasiadas peticiones. Por favor espera un momento antes de hacer otra pregunta.';
            // Esperar 60 segundos antes de permitir otra petición
            lastRequestTime.current = Date.now() + 60000;
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

  return (
    <div>
      <button
        onMouseDown={onMouseDown}
        onClick={onClick}
        style={{ position: 'fixed', top: position.top, right: position.right, zIndex: 50, cursor: 'grab' }}
        className="bg-blue-600 text-white rounded-full p-5 shadow-lg hover:bg-blue-700 transition-colors"
        aria-label="¿Necesitas ayuda?"
      >
        Chat IA
      </button>
      {open && (
        <div
          className="fixed bg-white rounded-lg shadow-lg p-6 z-50 border border-gray-200 flex flex-col"
          style={{ top: position.top + 70, right: position.right, width: 400, maxHeight: '80vh' }}
        >
          <h3 className="font-bold mb-2 text-blue-600">Chat con IA</h3>
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