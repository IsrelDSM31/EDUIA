#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Análisis de Riesgo de Asistencia - IAEDU1
Sistema de Gestión Educativa

Este script analiza el archivo attendances.csv específicamente,
similar al análisis de laptops pero adaptado a riesgos de asistencia.

Autor: IAEDU1 Team
Fecha: 2025
"""

import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from pathlib import Path

# Configurar estilo de gráficas
plt.style.use('default')
sns.set_palette("husl")

def main():
    """
    Función principal del script
    """
    print("📊 ANÁLISIS DE RIESGOS DE ASISTENCIA - ATTENDANCES.CSV")
    print("=" * 70)
    
    # 1. Cargar archivo attendances.csv
    print("🔍 Cargando archivo attendances.csv...")
    ruta_csv = Path("../csv_bd/attendances.csv")
    
    if not ruta_csv.exists():
        print(f"❌ No se encontró el archivo: {ruta_csv}")
        return
    
    df_attendances = pd.read_csv(ruta_csv)
    print(f"✅ Archivo cargado: {len(df_attendances)} filas")
    
    # 2. Cargar datos de estudiantes y grupos
    print("🔍 Cargando datos de estudiantes y grupos...")
    df_students = pd.read_csv("../csv_bd/students.csv")
    df_groups = pd.read_csv("../csv_bd/groups.csv")
    
    # 3. Enriquecer datos
    df_enriquecido = df_attendances.merge(
        df_students[['id', 'matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'group_id']], 
        left_on='student_id', right_on='id', how='left'
    ).merge(
        df_groups[['id', 'name']], 
        left_on='group_id', right_on='id', how='left', suffixes=('', '_group')
    )
    
    # Crear nombre completo y limpiar columnas
    df_enriquecido['Nombre_Completo'] = df_enriquecido['nombre'] + ' ' + df_enriquecido['apellido_paterno'] + ' ' + df_enriquecido['apellido_materno']
    df_enriquecido = df_enriquecido.rename(columns={'name': 'Grupo'})
    
    # 4. ANÁLISIS POR ESTUDIANTE (CLASIFICACIÓN ÚNICA)
    print("\n📊 ANÁLISIS POR ESTUDIANTE")
    print("=" * 50)
    
    # Calcular estadísticas por estudiante
    estadisticas_estudiante = df_enriquecido.groupby(['student_id', 'Nombre_Completo', 'matricula', 'Grupo']).agg({
        'status': ['count', lambda x: (x == 'absent').sum(), lambda x: (x == 'present').sum(), lambda x: (x == 'late').sum()]
    }).reset_index()
    
    # Renombrar columnas
    estadisticas_estudiante.columns = ['student_id', 'Nombre_Completo', 'matricula', 'Grupo', 
                                      'Total_Registros', 'Total_Ausencias', 'Total_Presentes', 'Total_Retardos']
    
    # Calcular porcentaje de ausencias
    estadisticas_estudiante['Porcentaje_Ausencias'] = (estadisticas_estudiante['Total_Ausencias'] / estadisticas_estudiante['Total_Registros']) * 100
    
    # Clasificar riesgo por estudiante
    # Riesgo Alto: > 50% de ausencias
    # Riesgo Pequeño: ≤ 50% de ausencias
    estadisticas_estudiante['Clasificacion_Riesgo'] = np.where(
        estadisticas_estudiante['Porcentaje_Ausencias'] > 50, 'Riesgo Alto', 'Riesgo Pequeño'
    )
    
    print(f"📏 Criterio: Riesgo Alto (>50% ausencias), Riesgo Pequeño (≤50% ausencias)")
    print(f"📊 Estudiantes analizados: {len(estadisticas_estudiante)}")
    
    # 5. Mostrar primeras 5 filas
    print("\n📋 Primeras 5 filas del análisis:")
    columnas_mostrar = ['Nombre_Completo', 'matricula', 'Grupo', 'Total_Ausencias', 
                        'Porcentaje_Ausencias', 'Clasificacion_Riesgo']
    print(estadisticas_estudiante[columnas_mostrar].head())
    
    # 6. Contar valores
    print("\n🔢 Conteo de niveles de riesgo:")
    conteo_riesgo = estadisticas_estudiante['Clasificacion_Riesgo'].value_counts()
    print(conteo_riesgo)
    
    # 7. Mostrar ejemplos de cada categoría
    print("\n📋 Ejemplos de estudiantes con Riesgo Pequeño:")
    riesgo_pequeno = estadisticas_estudiante[estadisticas_estudiante['Clasificacion_Riesgo'] == 'Riesgo Pequeño'].head(3)
    for _, estudiante in riesgo_pequeno.iterrows():
        print(f"   • {estudiante['Nombre_Completo']} - {estudiante['Porcentaje_Ausencias']:.1f}% ausencias")
    
    print("\n📋 Ejemplos de estudiantes con Riesgo Alto:")
    riesgo_alto = estadisticas_estudiante[estadisticas_estudiante['Clasificacion_Riesgo'] == 'Riesgo Alto'].head(3)
    for _, estudiante in riesgo_alto.iterrows():
        print(f"   • {estudiante['Nombre_Completo']} - {estudiante['Porcentaje_Ausencias']:.1f}% ausencias")
    
    # 8. Generar gráfica simple
    print("\n📊 Generando gráfica...")
    fig, (ax1, ax2) = plt.subplots(1, 2, figsize=(12, 5))
    
    # Gráfica 1: Distribución por nivel de riesgo
    conteo_riesgo.plot(kind='bar', ax=ax1, color=['#2ecc71', '#e74c3c'], alpha=0.7)
    ax1.set_title('Distribución por Nivel de Riesgo')
    ax1.set_ylabel('Cantidad de Estudiantes')
    
    # Gráfica 2: Gráfica de pastel
    conteo_riesgo.plot(kind='pie', ax=ax2, autopct='%1.1f%%', colors=['#2ecc71', '#e74c3c'])
    ax2.set_title('Proporción de Niveles de Riesgo')
    
    plt.tight_layout()
    plt.savefig("analisis_riesgos_asistencia.png", dpi=300, bbox_inches='tight')
    print("💾 Gráfica guardada: analisis_riesgos_asistencia.png")
    plt.close()
    
    print(f"\n🎉 ANÁLISIS COMPLETADO!")
    print("=" * 70)
    print(f"📊 Resumen:")
    print(f"   • Total de estudiantes: {len(estadisticas_estudiante)}")
    print(f"   • Riesgo Pequeño: {len(estadisticas_estudiante[estadisticas_estudiante['Clasificacion_Riesgo'] == 'Riesgo Pequeño'])}")
    print(f"   • Riesgo Alto: {len(estadisticas_estudiante[estadisticas_estudiante['Clasificacion_Riesgo'] == 'Riesgo Alto'])}")

if __name__ == "__main__":
    main() 