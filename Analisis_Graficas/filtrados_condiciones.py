#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Filtros de Condiciones - ATTENDANCES.CSV
Sistema de Gestión Educativa IAEDU1

Este script implementa filtros de condiciones adaptados al proyecto de asistencia.
Autor: IAEDU1 Team
Fecha: 2025
"""

import pandas as pd
import numpy as np

def main():
    """
    Función principal del script
    """
    print("FILTROS DE CONDICIONES - SISTEMA IAEDU1")
    print("=" * 60)
    
    # 1. CREAR UN DATAFRAME DE EJEMPLO
    print("\n1. CREANDO DATAFRAME DE EJEMPLO")
    print("-" * 40)
    
    # Crear dataframe de ejemplo con datos de asistencia del grupo 2ºGVL
    datos_ejemplo = {
        'student_id': [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        'porcentaje_ausencias': [15, 25, 35, 45, 55, 65, 75, 85, 95, 5, 30, 40, 60, 70, 80],
        'grupo': ['2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL', '2ºGVL'],
        'status': ['present', 'absent', 'present', 'late', 'absent', 'present', 'late', 'absent', 'present', 'present', 'absent', 'late', 'absent', 'absent', 'absent']
    }
    
    df_ejemplo = pd.DataFrame(datos_ejemplo)
    print("Dataframe de ejemplo creado (Grupo 2ºGVL):")
    print(df_ejemplo)
    
    # 2. FILTRAR FILAS DONDE COLUMNA 1 ES MAYOR QUE 2 O COLUMNA 2 ES IGUAL A A
    print("\n2. FILTRANDO CON CONDICIONES OR")
    print("-" * 40)
    
    # Filtrar donde student_id > 2 O grupo == '2ºGVL'
    filtro_or = (df_ejemplo['student_id'] > 2) | (df_ejemplo['grupo'] == '2ºGVL')
    df_filtrado_or = df_ejemplo[filtro_or]
    
    print("Filtro: student_id > 2 OR grupo == '2ºGVL'")
    print("Dataframe filtrado:")
    print(df_filtrado_or)
    
    # 3. ENCONTRAR DATOS IGUALES
    print("\n3. ENCONTRANDO DATOS IGUALES")
    print("-" * 40)
    
    # Encontrar filas donde status sea igual a 'present'
    datos_iguales = df_ejemplo[df_ejemplo['status'] == 'present']
    print("Filas donde status == 'present':")
    print(datos_iguales)
    
    # 4. ENCONTRAR ESTUDIANTES CON MÁS DE 50% AUSENCIAS (equivalente a laptops > 2000 euros)
    print("\n4. ENCONTRANDO ESTUDIANTES CON MÁS DE 50% AUSENCIAS")
    print("-" * 40)
    
    # Filtrar estudiantes con más de 50% ausencias
    filtro_ausencias = df_ejemplo['porcentaje_ausencias'] > 50
    estudiantes_alto_riesgo = df_ejemplo[filtro_ausencias]
    
    print("Estudiantes con más de 50% ausencias:")
    print(estudiantes_alto_riesgo)
    
    # 5. IMPRIMIR CUÁLES CUMPLEN LA CONDICIÓN
    print("\n5. ESTUDIANTES QUE CUMPLEN LA CONDICIÓN (>50% ausencias)")
    print("-" * 40)
    
    print("Los siguientes estudiantes cumplen la condición:")
    for _, estudiante in estudiantes_alto_riesgo.iterrows():
        print(f"- Estudiante {estudiante['student_id']}: {estudiante['porcentaje_ausencias']}% ausencias")
    
    # 6. CONTEO POR GRUPO (equivalente a conteo por compañía)
    print("\n6. CONTEO POR GRUPO")
    print("-" * 40)
    
    conteo_grupo = df_ejemplo['grupo'].value_counts()
    print("Conteo por grupo:")
    print(conteo_grupo)
    
    # 7. FILTRADO POR DOS CONDICIONES
    print("\n7. FILTRADO POR DOS CONDICIONES")
    print("-" * 40)
    
    # Encontrar estudiantes del grupo 2ºGVL que tengan más de 30% ausencias
    filtro_doble = (df_ejemplo['grupo'] == '2ºGVL') & (df_ejemplo['porcentaje_ausencias'] > 30)
    df_filtro_doble = df_ejemplo[filtro_doble]
    
    print("Estudiantes del grupo 2ºGVL con más de 30% ausencias:")
    print(df_filtro_doble)
    
    # 8. ENCONTRAR ESTUDIANTES DE 2ºGVL CON MÁS DE 30% AUSENCIAS
    print("\n8. ESTUDIANTES DE 2ºGVL CON MÁS DE 30% AUSENCIAS")
    print("-" * 40)
    
    print("Estudiantes que cumplen ambas condiciones:")
    for _, estudiante in df_filtro_doble.iterrows():
        print(f"- Estudiante {estudiante['student_id']} (Grupo {estudiante['grupo']}): {estudiante['porcentaje_ausencias']}% ausencias")
    
    # 9. DAR EL CONTEO
    print("\n9. CONTEO DE ESTUDIANTES QUE CUMPLEN CONDICIONES")
    print("-" * 40)
    
    print(f"Total de estudiantes con más de 50% ausencias: {len(estudiantes_alto_riesgo)}")
    print(f"Total de estudiantes 2ºGVL con más de 30% ausencias: {len(df_filtro_doble)}")
    
    # 10. IMPLEMENTAR OPERADOR DE NEGACIÓN
    print("\n10. IMPLEMENTANDO OPERADOR DE NEGACIÓN")
    print("-" * 40)
    
    # Negación: estudiantes que NO están en grupo 2ºGVL
    filtro_negacion = ~(df_ejemplo['grupo'] == '2ºGVL')
    df_negacion = df_ejemplo[filtro_negacion]
    
    print("Estudiantes que NO están en grupo 2ºGVL:")
    print(df_negacion)
    
    # Negación: estudiantes que NO tienen más de 50% ausencias
    filtro_negacion_ausencias = ~(df_ejemplo['porcentaje_ausencias'] > 50)
    df_negacion_ausencias = df_ejemplo[filtro_negacion_ausencias]
    
    print("\nEstudiantes que NO tienen más de 50% ausencias:")
    print(df_negacion_ausencias)
    
    # Negación combinada: NO grupo 2ºGVL Y NO más de 50% ausencias
    filtro_negacion_combinada = ~(df_ejemplo['grupo'] == '2ºGVL') & ~(df_ejemplo['porcentaje_ausencias'] > 50)
    df_negacion_combinada = df_ejemplo[filtro_negacion_combinada]
    
    print("\nEstudiantes que NO están en grupo 2ºGVL Y NO tienen más de 50% ausencias:")
    print(df_negacion_combinada)
    
    # 11. RESUMEN FINAL
    print("\n11. RESUMEN FINAL")
    print("-" * 40)
    
    print(f"Total de estudiantes en el dataframe: {len(df_ejemplo)}")
    print(f"Estudiantes con más de 50% ausencias: {len(estudiantes_alto_riesgo)}")
    print(f"Estudiantes 2ºGVL con más de 30% ausencias: {len(df_filtro_doble)}")
    print(f"Estudiantes que NO están en grupo 2ºGVL: {len(df_negacion)}")
    print(f"Estudiantes que NO tienen más de 50% ausencias: {len(df_negacion_ausencias)}")
    print(f"Estudiantes que NO están en grupo 2ºGVL Y NO tienen más de 50% ausencias: {len(df_negacion_combinada)}")
    
    print("\n" + "=" * 60)
    print("ANÁLISIS COMPLETADO EXITOSAMENTE")
    print("=" * 60)

if __name__ == "__main__":
    main() 