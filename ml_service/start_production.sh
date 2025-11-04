#!/bin/bash

# Script para iniciar el servicio ML en producción con Gunicorn
# Uso: ./start_production.sh

set -e  # Salir si hay error

echo "=========================================="
echo "🚀 Iniciando Servicio ML en Producción"
echo "=========================================="

# Variables
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Verificar Python
if ! command -v python3 &> /dev/null; then
    echo "❌ Error: Python 3 no encontrado"
    exit 1
fi

# Verificar Gunicorn
if ! python3 -c "import gunicorn" 2>/dev/null; then
    echo "⚠ Gunicorn no encontrado. Instalando..."
    pip3 install gunicorn
fi

# Verificar modelo
if [ ! -f "models/risk_model.pkl" ]; then
    echo "⚠ Modelo no encontrado. Entrenando modelo..."
    python3 train_model.py
fi

# Crear directorio de logs
mkdir -p logs

# Variables de entorno
export PYTHONUNBUFFERED=1
export FLASK_ENV=production

# Iniciar Gunicorn
echo "✓ Iniciando Gunicorn..."
echo "📊 Workers: $(python3 -c 'import multiprocessing; print(multiprocessing.cpu_count() * 2 + 1)')"
echo "🌐 URL: http://127.0.0.1:5000"
echo "📝 Logs: logs/gunicorn-*.log"
echo ""
echo "💡 Para detener: pkill -f gunicorn"
echo "=========================================="

exec gunicorn -c gunicorn_config.py predict_service:app


