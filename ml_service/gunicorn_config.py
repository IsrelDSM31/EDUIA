# Configuración de Gunicorn para producción
import multiprocessing
import os

# Número de workers (procesos)
# Fórmula recomendada: (2 x CPU cores) + 1
workers = multiprocessing.cpu_count() * 2 + 1

# Limitar workers si hay muchos CPUs
if workers > 8:
    workers = 8

# Tipo de worker
# 'sync' es bueno para I/O bound, 'gevent' para async
worker_class = 'sync'

# Timeout (segundos)
timeout = 30
graceful_timeout = 30

# Bind (puerto y host)
# En producción, usar 0.0.0.0 para permitir conexiones desde Laravel
# En Windows, usar 0.0.0.0 funciona bien
bind = os.getenv('GUNICORN_BIND', '0.0.0.0:5000')

# Logs
log_dir = os.path.join(os.path.dirname(__file__), 'logs')
os.makedirs(log_dir, exist_ok=True)

accesslog = os.path.join(log_dir, 'gunicorn-access.log')
errorlog = os.path.join(log_dir, 'gunicorn-error.log')
loglevel = os.getenv('GUNICORN_LOG_LEVEL', 'info')

# Preload app (para compartir memoria entre workers)
# Útil para modelos grandes
preload_app = True

# Max requests (reiniciar worker después de N requests)
# Ayuda a prevenir memory leaks
max_requests = 1000
max_requests_jitter = 50

# Worker timeout
# worker_tmp_dir solo para Linux, comentado para Windows
# worker_tmp_dir = '/dev/shm'  # Para Linux, usar memoria compartida

# User y Group (solo si se ejecuta como root en Linux)
# user = 'www-data'
# group = 'www-data'

# PID file
pidfile = os.path.join(log_dir, 'gunicorn.pid')

# Daemon mode (desactivar si usas systemd)
daemon = False

# Capturar output
capture_output = True
enable_stdio_inheritance = True

