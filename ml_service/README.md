# 🤖 Servicio de Machine Learning - Análisis de Riesgo Académico

Este servicio Python proporciona predicciones de riesgo académico usando Random Forest.

## 📋 Requisitos

- Python 3.8 o superior
- pip (gestor de paquetes de Python)

## 🚀 Instalación

### 1. Instalar dependencias

```bash
cd ml_service
pip install -r requirements.txt
```

### 2. Entrenar el modelo

Antes de usar el servicio, necesitas entrenar el modelo:

```bash
python train_model.py
```

Esto generará:
- `models/risk_model.pkl` - Modelo entrenado
- `models/scaler.pkl` - Normalizador de características
- `models/feature_names.json` - Nombres de características
- `models/label_encoder.pkl` - Codificador de etiquetas

### 3. Iniciar el servicio

```bash
python predict_service.py
```

El servicio estará disponible en: `http://localhost:5000`

## 📡 Endpoints

### Health Check
```
GET /health
```

Respuesta:
```json
{
  "status": "ok",
  "model_loaded": true,
  "timestamp": "2024-01-15T10:30:00"
}
```

### Predicción
```
POST /predict
Content-Type: application/json

{
  "grade_average": 7.5,
  "attendance_rate": 0.85,
  "failed_subjects": 1,
  "recent_improvement": 0.1,
  "group_id": 5
}
```

Respuesta:
```json
{
  "status": "success",
  "risk_level": "medio",
  "risk_score": 0.75,
  "confidence": 0.75,
  "probabilities": {
    "bajo": 0.15,
    "medio": 0.75,
    "alto": 0.10
  }
}
```

### Información del Modelo
```
GET /model-info
```

## 🔧 Configuración

### Variables de Entorno

En Laravel, configura la URL del servicio en `.env`:

```env
ML_SERVICE_URL=http://localhost:5000
```

### Personalizar el Modelo

Puedes modificar los hiperparámetros en `train_model.py`:

```python
model = RandomForestClassifier(
    n_estimators=100,    # Número de árboles
    max_depth=10,        # Profundidad máxima
    min_samples_split=5,  # Mínimo de muestras para dividir
    min_samples_leaf=2,   # Mínimo de muestras en hoja
    random_state=42,
    n_jobs=-1            # Usar todos los CPUs
)
```

## 📊 Re-entrenamiento

Para mejorar el modelo con nuevos datos:

1. Actualiza los CSV en `../csv_bd/`
2. Ejecuta `python train_model.py` nuevamente
3. El modelo se actualizará automáticamente

## 🐛 Solución de Problemas

### Error: "Modelo no encontrado"
- Ejecuta `python train_model.py` primero

### Error: "Connection refused"
- Verifica que el servicio esté corriendo en `http://localhost:5000`
- Revisa el firewall y permisos

### Error: "Feature mismatch"
- Asegúrate de que el número de características coincida (5 features)
- Verifica el orden: `[grade_average, attendance_rate, failed_subjects, recent_improvement, group_id]`

## 📝 Notas

- El servicio usa Flask para desarrollo. Para producción, considera usar Gunicorn o uWSGI.
- El modelo se carga en memoria al iniciar el servicio para mayor velocidad.
- Las predicciones se realizan en tiempo real (< 50ms típicamente).


