#!/usr/bin/env python3
"""
Script de entrenamiento del modelo de Machine Learning
para predicción de riesgo académico usando Random Forest
"""

import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.preprocessing import StandardScaler, LabelEncoder
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
import joblib
import os
import json
from datetime import datetime

# Configuración
MODEL_DIR = 'models'
MODEL_FILE = os.path.join(MODEL_DIR, 'risk_model.pkl')
SCALER_FILE = os.path.join(MODEL_DIR, 'scaler.pkl')
FEATURE_NAMES_FILE = os.path.join(MODEL_DIR, 'feature_names.json')
LABEL_ENCODER_FILE = os.path.join(MODEL_DIR, 'label_encoder.pkl')

def create_model_dir():
    """Crear directorio de modelos si no existe"""
    os.makedirs(MODEL_DIR, exist_ok=True)

def load_data_from_csv():
    """
    Cargar datos desde archivos CSV
    Si no existen o hay errores, generar datos sintéticos para entrenamiento inicial
    """
    try:
        # Intentar cargar desde CSV
        print("📥 Intentando cargar datos desde CSV...")
        students = pd.read_csv('../csv_bd/students.csv', encoding='utf-8', on_bad_lines='skip', low_memory=False)
        grades = pd.read_csv('../csv_bd/grades.csv', encoding='utf-8', on_bad_lines='skip', low_memory=False)
        attendances = pd.read_csv('../csv_bd/attendances.csv', encoding='utf-8', on_bad_lines='skip', low_memory=False)
        
        # Verificar que los datos no estén vacíos
        if len(students) == 0 or len(grades) == 0:
            print("⚠ Los archivos CSV están vacíos. Generando datos sintéticos...")
            return generate_synthetic_data()
        
        print(f"✓ Datos cargados desde CSV: {len(students)} estudiantes, {len(grades)} calificaciones, {len(attendances)} asistencias")
        return students, grades, attendances
    except (FileNotFoundError, pd.errors.ParserError, pd.errors.EmptyDataError, MemoryError) as e:
        print(f"⚠ Error al cargar CSV ({type(e).__name__}): {e}")
        print("📊 Generando datos sintéticos para entrenamiento...")
        return generate_synthetic_data()
    except Exception as e:
        print(f"⚠ Error inesperado al cargar CSV: {e}")
        print("📊 Generando datos sintéticos para entrenamiento...")
        return generate_synthetic_data()

def generate_synthetic_data():
    """Generar datos sintéticos para entrenamiento inicial"""
    np.random.seed(42)
    n_students = 500
    
    students = pd.DataFrame({
        'id': range(1, n_students + 1),
        'group_id': np.random.randint(1, 10, n_students)
    })
    
    # Generar calificaciones
    grades_data = []
    for student_id in range(1, n_students + 1):
        n_subjects = np.random.randint(5, 10)
        for subject_id in range(1, n_subjects + 1):
            # Simular diferentes niveles de rendimiento
            base_grade = np.random.uniform(5, 10)
            grades_data.append({
                'student_id': student_id,
                'subject_id': subject_id,
                'promedio_final': round(base_grade, 2)
            })
    
    grades = pd.DataFrame(grades_data)
    
    # Generar asistencias
    attendances_data = []
    for student_id in range(1, n_students + 1):
        n_classes = np.random.randint(30, 60)
        attendance_rate = np.random.uniform(0.6, 1.0)
        n_present = int(n_classes * attendance_rate)
        
        for i in range(n_classes):
            attendances_data.append({
                'student_id': student_id,
                'status': 'present' if i < n_present else 'absent',
                'date': f"2024-{np.random.randint(1, 13):02d}-{np.random.randint(1, 28):02d}"
            })
    
    attendances = pd.DataFrame(attendances_data)
    
    print("✓ Datos sintéticos generados")
    return students, grades, attendances

def extract_features(students, grades, attendances):
    """
    Extraer características (features) de los datos
    """
    features_list = []
    labels = []
    
    for student_id in students['id'].unique():
        student_data = students[students['id'] == student_id].iloc[0]
        student_grades = grades[grades['student_id'] == student_id]
        student_attendances = attendances[attendances['student_id'] == student_id]
        
        # Calcular métricas
        grade_avg = student_grades['promedio_final'].mean() if len(student_grades) > 0 else 0
        failed_subjects = len(student_grades[student_grades['promedio_final'] < 7])
        
        total_classes = len(student_attendances)
        present_classes = len(student_attendances[student_attendances['status'] == 'present'])
        attendance_rate = present_classes / total_classes if total_classes > 0 else 0
        
        # Tendencias
        if len(student_grades) >= 2:
            recent_grades = student_grades.sort_values('promedio_final', ascending=False).head(5)
            if len(recent_grades) >= 2:
                old_avg = recent_grades.iloc[1:]['promedio_final'].mean()
                new_avg = recent_grades.iloc[0]['promedio_final']
                recent_improvement = (new_avg - old_avg) / old_avg if old_avg > 0 else 0
            else:
                recent_improvement = 0
        else:
            recent_improvement = 0
        
        # Features
        features = [
            grade_avg,              # 0: Promedio de calificaciones
            attendance_rate,        # 1: Tasa de asistencia
            failed_subjects,        # 2: Materias reprobadas
            recent_improvement,     # 3: Mejora reciente
            student_data['group_id'] if 'group_id' in student_data else 1,  # 4: Grupo
        ]
        
        # Etiquetar basado en reglas mejoradas (para entrenamiento inicial)
        # Esto simula el etiquetado que haría un experto de forma más balanceada
        
        # Caso 1: Sin datos académicos
        # IMPORTANTE: Si no hay calificaciones, NUNCA puede ser riesgo "bajo"
        # La asistencia sola no es suficiente para determinar buen desempeño académico
        if grade_avg == 0 and attendance_rate == 0:
            label = 'alto'  # Sin datos de ningún tipo
        elif grade_avg == 0 and attendance_rate > 0:
            # Tiene asistencia pero no calificaciones aún
            # Sin calificaciones, no puede ser "bajo". Al menos "medio" si tiene buena asistencia
            if attendance_rate >= 0.9:
                label = 'medio'  # Buena asistencia pero falta evaluar desempeño académico
            elif attendance_rate >= 0.7:
                label = 'medio'  # Asistencia regular, falta evaluar desempeño académico
            else:
                label = 'alto'  # Baja asistencia y sin calificaciones = alto riesgo
        elif grade_avg > 0 and attendance_rate == 0:
            # Tiene calificaciones pero no asistencia registrada
            if grade_avg >= 9 and failed_subjects == 0:
                label = 'medio'  # Excelente promedio, solo falta asistencia
            elif grade_avg >= 8 and failed_subjects <= 1:
                label = 'medio'
            elif grade_avg < 7 or failed_subjects >= 2:
                label = 'alto'  # Bajo promedio o muchas reprobadas
            else:
                label = 'medio'
        else:
            # Caso normal: tiene ambos datos
            # Evaluar combinación de promedio, asistencia y materias reprobadas
            
            # Excelente
            if grade_avg >= 9 and attendance_rate >= 0.95 and failed_subjects == 0:
                label = 'bajo'
            # Muy bueno
            elif grade_avg >= 8 and attendance_rate >= 0.9 and failed_subjects <= 1:
                label = 'bajo'
            # Bueno pero asistencia regular
            elif grade_avg >= 8 and attendance_rate >= 0.8:
                label = 'medio'
            # Promedio alto pero asistencia baja
            elif grade_avg >= 8 and attendance_rate < 0.8:
                label = 'medio'
            # Promedio regular pero asistencia alta
            elif grade_avg >= 7 and grade_avg < 8 and attendance_rate >= 0.9 and failed_subjects <= 1:
                label = 'medio'
            # Promedio regular pero asistencia baja o muchas reprobadas
            elif grade_avg >= 7 and grade_avg < 8 and (attendance_rate < 0.8 or failed_subjects >= 2):
                label = 'alto'
            # Bajo promedio o muchas reprobadas
            elif grade_avg < 7 or failed_subjects >= 3:
                label = 'alto'
            # Bajo promedio con asistencia regular
            elif grade_avg < 7 and attendance_rate >= 0.8:
                label = 'alto'
            # Bajo promedio con asistencia baja
            elif grade_avg < 7 and attendance_rate < 0.8:
                label = 'alto'
            # Caso por defecto
            else:
                label = 'medio'
        
        features_list.append(features)
        labels.append(label)
    
    return np.array(features_list), np.array(labels)

def train_model(X, y):
    """
    Entrenar modelo Random Forest
    """
    print("\n🔄 Entrenando modelo Random Forest...")
    
    # Codificar etiquetas
    label_encoder = LabelEncoder()
    y_encoded = label_encoder.fit_transform(y)
    
    # Verificar si podemos usar estratificación (necesita al menos 2 ejemplos por clase)
    unique, counts = np.unique(y_encoded, return_counts=True)
    can_stratify = all(count >= 2 for count in counts)
    
    # Dividir datos
    if can_stratify and len(unique) > 1:
        X_train, X_test, y_train, y_test = train_test_split(
            X, y_encoded, test_size=0.2, random_state=42, stratify=y_encoded
        )
    else:
        # Si no podemos estratificar, dividir sin estratificación
        X_train, X_test, y_train, y_test = train_test_split(
            X, y_encoded, test_size=0.2, random_state=42
        )
    
    # Normalizar features
    scaler = StandardScaler()
    X_train_scaled = scaler.fit_transform(X_train)
    X_test_scaled = scaler.transform(X_test)
    
    # Entrenar modelo
    model = RandomForestClassifier(
        n_estimators=100,
        max_depth=10,
        min_samples_split=5,
        min_samples_leaf=2,
        random_state=42,
        n_jobs=-1
    )
    
    model.fit(X_train_scaled, y_train)
    
    # Evaluar
    y_pred = model.predict(X_test_scaled)
    accuracy = accuracy_score(y_test, y_pred)
    
    print(f"\n✓ Modelo entrenado exitosamente")
    print(f"  Precisión: {accuracy:.2%}")
    
    # Validación cruzada
    cv_scores = cross_val_score(model, X_train_scaled, y_train, cv=5)
    print(f"  Precisión CV (5-fold): {cv_scores.mean():.2%} (+/- {cv_scores.std() * 2:.2%})")
    
    # Reporte de clasificación (solo si hay suficientes clases en el test)
    unique_test = np.unique(y_test)
    if len(unique_test) > 1:
        print("\n📊 Reporte de Clasificación:")
        try:
            print(classification_report(y_test, y_pred, target_names=label_encoder.classes_, labels=unique_test))
        except:
            # Si falla, usar labels explícitos
            print(classification_report(y_test, y_pred, labels=unique_test))
    else:
        print(f"\n📊 Clase única en test: {label_encoder.inverse_transform([unique_test[0]])[0]}")
    
    # Matriz de confusión
    print("\n📈 Matriz de Confusión:")
    cm = confusion_matrix(y_test, y_pred)
    print(cm)
    
    # Guardar modelo y componentes
    joblib.dump(model, MODEL_FILE)
    joblib.dump(scaler, SCALER_FILE)
    joblib.dump(label_encoder, LABEL_ENCODER_FILE)
    
    # Guardar nombres de features
    feature_names = [
        'grade_average',
        'attendance_rate',
        'failed_subjects',
        'recent_improvement',
        'group_id'
    ]
    
    with open(FEATURE_NAMES_FILE, 'w') as f:
        json.dump(feature_names, f)
    
    print(f"\n✓ Modelo guardado en: {MODEL_FILE}")
    print(f"✓ Scaler guardado en: {SCALER_FILE}")
    print(f"✓ Label encoder guardado en: {LABEL_ENCODER_FILE}")
    
    return model, scaler, label_encoder

def main():
    """Función principal"""
    print("=" * 60)
    print("🚀 Entrenamiento del Modelo de Riesgo Académico")
    print("=" * 60)
    
    # Crear directorio
    create_model_dir()
    
    # Cargar datos
    print("\n📥 Cargando datos...")
    students, grades, attendances = load_data_from_csv()
    
    # Extraer features
    print("\n🔧 Extrayendo características...")
    X, y = extract_features(students, grades, attendances)
    
    print(f"✓ Datos preparados: {len(X)} estudiantes, {X.shape[1]} características")
    print(f"✓ Distribución de clases: {pd.Series(y).value_counts().to_dict()}")
    
    # Entrenar modelo
    model, scaler, label_encoder = train_model(X, y)
    
    print("\n" + "=" * 60)
    print("✅ Entrenamiento completado exitosamente")
    print("=" * 60)
    print("\n📝 Para usar el modelo, ejecuta: python predict_service.py")
    print("📝 O llama al servicio desde PHP usando RiskPredictionService")

if __name__ == '__main__':
    main()

