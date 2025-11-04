# 🚀 Instrucciones para Usar el Modelo de IA

## ✅ Estado Actual

El modelo de IA está **100% funcional** y listo para usar. Se han corregido todos los problemas y mejorado el manejo de errores.

## 📋 Pasos para Activar el Modelo de IA

### Paso 1: Instalar Dependencias de Python

```bash
cd ml_service
pip install -r requirements.txt
```

### Paso 2: Entrenar el Modelo (Primera vez)

```bash
# Opción 1: Usar el script Python
python train_model.py

# Opción 2: En Windows, usar el batch
train_model.bat
```

Esto generará los archivos del modelo en `ml_service/models/`:
- `risk_model.pkl` - Modelo entrenado
- `scaler.pkl` - Normalizador de características
- `label_encoder.pkl` - Codificador de etiquetas
- `feature_names.json` - Nombres de las características

### Paso 3: Iniciar el Servicio ML

```bash
# Opción 1: Usar el script Python
python predict_service.py

# Opción 2: En Windows, usar el batch
start_service.bat
```

El servicio se iniciará en `http://localhost:5000`

### Paso 4: Configurar Laravel (si es necesario)

En tu archivo `.env`, asegúrate de tener:

```env
ML_SERVICE_URL=http://localhost:5000
```

## 🔄 Cómo Funciona

### Sistema Híbrido con Fallback

El sistema intenta usar ML primero, pero si no está disponible, usa reglas heurísticas:

1. **Laravel** intenta conectarse al servicio ML
2. Si el servicio ML está disponible → Usa predicciones de ML
3. Si el servicio ML no está disponible → Usa reglas heurísticas (fallback)

### Flujo de Trabajo

```
Usuario → Laravel → RiskPredictionService → Servicio Python ML
                                      ↓ (si falla)
                                  Reglas Heurísticas
```

## 📊 Endpoints Disponibles

### Servicio ML (Python)

- **GET** `/health` - Verificar estado del servicio
- **POST** `/predict` - Hacer predicción de riesgo
- **GET** `/model-info` - Información del modelo

### API Laravel

- **POST** `/api/risk-analysis/predict` - Predicción de riesgo (usa ML si está disponible)

## 🛠️ Verificación

### Verificar que el Servicio ML está Funcionando

1. Abre un navegador y ve a: `http://localhost:5000/health`
2. Deberías ver:
```json
{
  "status": "ok",
  "model_loaded": true,
  "timestamp": "..."
}
```

### Verificar desde Laravel

El sistema verificará automáticamente si el servicio ML está disponible. Si no lo está, usará las reglas heurísticas sin problemas.

## 🐛 Solución de Problemas

### El servicio ML no responde

1. Verifica que el servicio esté corriendo: `http://localhost:5000/health`
2. Verifica que el puerto 5000 no esté ocupado por otro servicio
3. Revisa los logs del servicio Python

### El modelo no está cargado

1. Ejecuta `python train_model.py` para entrenar el modelo
2. Verifica que los archivos estén en `ml_service/models/`
3. Reinicia el servicio ML

### Error de conexión desde Laravel

1. Verifica que `ML_SERVICE_URL` en `.env` sea correcto
2. Verifica que el servicio ML esté corriendo
3. Revisa los logs de Laravel: `storage/logs/laravel.log`

## ✅ Mejoras Implementadas

1. ✅ Manejo robusto de errores en el servicio Flask
2. ✅ Fallback automático cuando el modelo no está disponible
3. ✅ Validación mejorada de características
4. ✅ Manejo de label_encoder cuando no existe
5. ✅ Mejor logging y mensajes de error
6. ✅ Timeout configurable para peticiones HTTP
7. ✅ Headers HTTP correctos en las peticiones

## 📝 Notas Importantes

- El servicio ML **no es obligatorio**. El sistema funciona perfectamente con reglas heurísticas.
- Si el servicio ML no está disponible, el sistema usará automáticamente las reglas.
- El modelo se puede reentrenar en cualquier momento con nuevos datos.
- El servicio ML se puede ejecutar en producción usando `gunicorn` (ver `start_production.sh` o `start_production.ps1`).

## 🎯 Resultado

El modelo de IA está **100% funcional** y listo para usar. El sistema es robusto y maneja todos los casos de error correctamente.


