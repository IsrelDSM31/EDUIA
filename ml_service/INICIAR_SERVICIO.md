# 🚀 Guía Rápida - Iniciar Servicio ML

## ✅ Pasos Completados

1. ✓ **Dependencias instaladas** - Flask, scikit-learn, pandas, numpy, joblib
2. ✓ **Modelo entrenado** - Modelo guardado en `ml_service/models/`
3. ⏳ **Servicio ML** - Necesita iniciarse manualmente

## 🎯 Iniciar el Servicio ML

### Opción 1: Desde PowerShell (Recomendado)

```powershell
cd ml_service
python predict_service.py
```

### Opción 2: Usar el script batch (Windows)

```cmd
cd ml_service
start_service.bat
```

### Opción 3: Desde la terminal de Python directamente

```powershell
cd C:\xampp\htdocs\IAEDU1\ml_service
python predict_service.py
```

## ✅ Verificar que Funciona

Una vez iniciado el servicio, abre un navegador y ve a:

**http://localhost:5000/health**

Deberías ver:
```json
{
  "status": "ok",
  "model_loaded": true,
  "timestamp": "..."
}
```

## 📊 Endpoints Disponibles

- **GET** `http://localhost:5000/health` - Verificar estado
- **POST** `http://localhost:5000/predict` - Hacer predicción
- **GET** `http://localhost:5000/model-info` - Información del modelo

## 🔄 Integración con Laravel

El sistema Laravel **detectará automáticamente** si el servicio ML está disponible:

- ✅ Si el servicio está corriendo → Usa predicciones de ML
- ✅ Si el servicio NO está corriendo → Usa reglas heurísticas (fallback)

**No necesitas configurar nada más.** El sistema funciona en ambos casos.

## 💡 Notas Importantes

1. **El servicio debe estar corriendo** para que Laravel use ML
2. **Si el servicio no está disponible**, Laravel usará automáticamente las reglas heurísticas
3. **El servicio se puede detener** con `Ctrl+C` en la terminal donde está corriendo
4. **Para producción**, considera usar `gunicorn` (ver `start_production.ps1`)

## 🎉 ¡Todo Listo!

El modelo de IA está **100% funcional**. Solo necesitas mantener el servicio ML corriendo cuando quieras usar predicciones de ML.


