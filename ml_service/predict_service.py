#!/usr/bin/env python3
"""
Servicio Flask para predicción de riesgo académico
Usa el modelo entrenado para hacer predicciones en tiempo real
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import numpy as np
import os
import json
from datetime import datetime

app = Flask(__name__)
CORS(app)  # Permitir CORS para Laravel

# Configuración
MODEL_DIR = 'models'
MODEL_FILE = os.path.join(MODEL_DIR, 'risk_model.pkl')
SCALER_FILE = os.path.join(MODEL_DIR, 'scaler.pkl')
FEATURE_NAMES_FILE = os.path.join(MODEL_DIR, 'feature_names.json')
LABEL_ENCODER_FILE = os.path.join(MODEL_DIR, 'label_encoder.pkl')

# Cargar modelo al iniciar (si existe)
model = None
scaler = None
label_encoder = None
feature_names = None

def load_model():
    """Cargar modelo y componentes necesarios"""
    global model, scaler, label_encoder, feature_names
    
    try:
        if os.path.exists(MODEL_FILE) and os.path.exists(SCALER_FILE):
            model = joblib.load(MODEL_FILE)
            scaler = joblib.load(SCALER_FILE)
            
            # Cargar label_encoder si existe (opcional)
            if os.path.exists(LABEL_ENCODER_FILE):
                label_encoder = joblib.load(LABEL_ENCODER_FILE)
            else:
                # Si no existe, crear uno simple con valores por defecto
                from sklearn.preprocessing import LabelEncoder
                label_encoder = LabelEncoder()
                label_encoder.classes_ = np.array(['bajo', 'medio', 'alto'])
                print("⚠ Label encoder no encontrado, usando valores por defecto")
            
            # Cargar feature_names si existe
            if os.path.exists(FEATURE_NAMES_FILE):
                with open(FEATURE_NAMES_FILE, 'r') as f:
                    feature_names = json.load(f)
            else:
                # Si no existe, usar valores por defecto
                feature_names = ['grade_average', 'attendance_rate', 'failed_subjects', 'recent_improvement', 'group_id']
                print("⚠ Feature names no encontrado, usando valores por defecto")
            
            print("✓ Modelo cargado exitosamente")
            return True
        else:
            print("⚠ Modelo no encontrado. Ejecuta train_model.py primero.")
            return False
    except Exception as e:
        print(f"❌ Error al cargar modelo: {e}")
        import traceback
        traceback.print_exc()
        return False

@app.route('/health', methods=['GET'])
def health_check():
    """Verificar estado del servicio"""
    return jsonify({
        'status': 'ok',
        'model_loaded': model is not None,
        'timestamp': datetime.now().isoformat()
    })

@app.route('/predict', methods=['POST'])
def predict():
    """
    Endpoint para predicción de riesgo
    
    Espera JSON con:
    {
        "features": [grade_average, attendance_rate, failed_subjects, recent_improvement, group_id]
    }
    
    O con objeto student:
    {
        "grade_average": float,
        "attendance_rate": float,
        "failed_subjects": int,
        "recent_improvement": float,
        "group_id": int
    }
    """
    if model is None or scaler is None:
        return jsonify({
            'error': 'Modelo no disponible. Ejecuta train_model.py primero.',
            'status': 'error',
            'message': 'El modelo ML no está cargado. Por favor, ejecuta train_model.py para entrenar el modelo.'
        }), 503
    
    try:
        data = request.get_json()
        
        # Manejar dos formatos de entrada
        if 'features' in data:
            features = np.array(data['features']).reshape(1, -1)
        else:
            # Construir features desde objeto
            features = np.array([
                data.get('grade_average', 0),
                data.get('attendance_rate', 0),
                data.get('failed_subjects', 0),
                data.get('recent_improvement', 0),
                data.get('group_id', 1)
            ]).reshape(1, -1)
        
        # Validar número de features
        if features.shape[1] != len(feature_names):
            return jsonify({
                'error': f'Número de características incorrecto. Esperado: {len(feature_names)}, Recibido: {features.shape[1]}',
                'status': 'error',
                'expected_features': feature_names,
                'received_count': features.shape[1]
            }), 400
        
        # Normalizar
        features_scaled = scaler.transform(features)
        
        # Predecir
        prediction_encoded = model.predict(features_scaled)[0]
        probabilities = model.predict_proba(features_scaled)[0]
        
        # Decodificar etiqueta
        if label_encoder and hasattr(label_encoder, 'inverse_transform'):
            try:
                risk_level = label_encoder.inverse_transform([prediction_encoded])[0]
                # Mapear probabilidades a clases
                class_names = label_encoder.classes_
                probabilities_dict = {
                    class_name: float(prob) 
                    for class_name, prob in zip(class_names, probabilities)
                }
            except Exception as e:
                # Fallback si hay error con el label encoder
                risk_levels = ['bajo', 'medio', 'alto']
                risk_level = risk_levels[int(prediction_encoded)] if int(prediction_encoded) < len(risk_levels) else 'medio'
                probabilities_dict = {
                    'bajo': float(probabilities[0]) if len(probabilities) > 0 else 0.33,
                    'medio': float(probabilities[1]) if len(probabilities) > 1 else 0.33,
                    'alto': float(probabilities[2]) if len(probabilities) > 2 else 0.34
                }
        else:
            # Fallback sin label encoder
            risk_levels = ['bajo', 'medio', 'alto']
            risk_level = risk_levels[int(prediction_encoded)] if int(prediction_encoded) < len(risk_levels) else 'medio'
            probabilities_dict = {
                'bajo': float(probabilities[0]) if len(probabilities) > 0 else 0.33,
                'medio': float(probabilities[1]) if len(probabilities) > 1 else 0.33,
                'alto': float(probabilities[2]) if len(probabilities) > 2 else 0.34
            }
        
        # Calcular confianza (probabilidad máxima)
        confidence = float(max(probabilities))
        
        return jsonify({
            'status': 'success',
            'risk_level': risk_level,
            'risk_score': confidence,
            'confidence': confidence,
            'probabilities': probabilities_dict,
            'features_used': {
                name: float(val) for name, val in zip(feature_names, features[0])
            }
        })
        
    except Exception as e:
        return jsonify({
            'error': str(e),
            'status': 'error'
        }), 500

@app.route('/model-info', methods=['GET'])
def model_info():
    """Información del modelo cargado"""
    if model is None:
        return jsonify({
            'error': 'Modelo no disponible',
            'status': 'error'
        }), 503
    
    return jsonify({
        'status': 'success',
        'model_type': type(model).__name__,
        'feature_names': feature_names,
        'n_features': len(feature_names),
        'classes': label_encoder.classes_.tolist() if label_encoder else [],
        'n_estimators': model.n_estimators if hasattr(model, 'n_estimators') else None,
        'max_depth': model.max_depth if hasattr(model, 'max_depth') else None
    })

def create_app():
    """Factory function para crear la aplicación Flask"""
    # Cargar modelo al crear la app
    model_loaded = load_model()
    
    if not model_loaded:
        print("\n⚠ ADVERTENCIA: El modelo no está cargado.")
        print("   El servicio responderá pero las predicciones fallarán.")
        print("   Para cargar el modelo, ejecuta: python train_model.py")
    
    return app

if __name__ == '__main__':
    print("=" * 60)
    print("🚀 Iniciando Servicio de Predicción ML")
    print("=" * 60)
    
    # Cargar modelo
    model_loaded = load_model()
    
    print("✓ Servicio iniciado en http://localhost:5000")
    print("✓ Endpoints disponibles:")
    print("  - GET  /health      - Estado del servicio")
    print("  - POST /predict     - Predicción de riesgo")
    print("  - GET  /model-info  - Información del modelo")
    
    if not model_loaded:
        print("\n⚠ ADVERTENCIA: El modelo no está cargado.")
        print("   El servicio responderá pero las predicciones fallarán.")
        print("   Para cargar el modelo, ejecuta: python train_model.py")
    
    print("\n💡 Para detener el servicio, presiona Ctrl+C")
    print("=" * 60)
    
    # NOTA: Este código solo se ejecuta si se llama directamente
    # En producción, Waitress/Gunicorn usará create_app()
    # Iniciar servidor de desarrollo solo como fallback
    import sys
    if 'waitress' not in sys.modules and 'gunicorn' not in sys.modules:
        app.run(host='0.0.0.0', port=5000, debug=False)

