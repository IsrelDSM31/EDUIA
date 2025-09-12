#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Análisis de Ordenamiento y Duplicados - IAEDU1
Sistema de Gestión Educativa

Este script demuestra el ordenamiento de DataFrames y la gestión de
valores duplicados utilizando diferentes estrategias (keep='first',
keep='last', keep=False), siguiendo el patrón de los ejemplos proporcionados.

Autor: IAEDU1 Team
Fecha: 2025
"""

import pandas as pd
import numpy as np

def main():
    """
    Función principal del script
    """
    print("📊 ANÁLISIS DE ORDENAMIENTO Y DUPLICADOS")
    print("=" * 60)
    
    # Crear DataFrame de ejemplo con duplicados intencionales
    # Usamos 'matricula' y 'calificacion' 
    datos = {
        'id': [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        'matricula': [
            'M001', 'M002', 'M003', 'M001', 'M004', 'M002', 'M005', 'M006', 'M003', 'M007',
            'M001', 'M004', 'M008', 'M009', 'M005'
        ],
        'nombre': [
            'Ana', 'Luis', 'Carlos', 'Ana', 'Sofía', 'Luis', 'Diego', 'Elena', 'Carlos', 'Pedro',
            'Ana', 'Sofía', 'Laura', 'Miguel', 'Diego'
        ],
        'materia': [
            'Matemáticas', 'Física', 'Química', 'Matemáticas', 'Historia', 'Física', 'Biología', 'Arte', 'Química', 'Literatura',
            'Matemáticas', 'Historia', 'Geografía', 'Música', 'Biología'
        ],
        'calificacion': [
            90, 85, 78, 92, 95, 80, 70, 88, 75, 91,
            88, 90, 82, 79, 72
        ]
    }
    df = pd.DataFrame(datos)
    
    print("\n--- DATAFRAME DE EJEMPLO ORIGINAL ---")
    print(df.to_string(index=True))
    print("-" * 40)

    # 1. ORDENAR DATAFRAME ASCENDENTE POR 'MATRICULA' Y 'CALIFICACION'
    
    print("\n1. ORDENAR DATAFRAME ASCENDENTE POR 'MATRICULA' Y 'CALIFICACION'")
    print("   (menor primero, mayor al último)")
    print("-" * 80)
    df_ordenado = df.sort_values(by=['matricula', 'calificacion'], ascending=[True, True])
    print(df_ordenado.to_string(index=True))
    print("-" * 80)

    # 2. REVISAR TODAS LAS CATEGORIAS DE LA COLUMNA 'MATRICULA'
    print("\n2. REVISAR TODAS LAS CATEGORIAS DE LA COLUMNA 'MATRICULA'")
    print("-" * 80)
    print("df['matricula'].value_counts()")
    print(df['matricula'].value_counts())
    print("-" * 80)

    # 3. VALORES DUPLICADOS EN LA COLUMNA 'MATRICULA' (CON KEEP = 'FIRST')
    print("\n3. VALORES DUPLICADOS EN LA COLUMNA 'MATRICULA' (CON KEEP = 'FIRST')")
    print("   (Marca True a partir de la segunda aparición de cada matrícula)")
    print("-" * 80)
    print("df.duplicated('matricula', keep='first')")
    duplicated_first = df.duplicated('matricula', keep='first')
    print(duplicated_first.to_string())
    print("-" * 80)

    # 4. MOSTRAR DATAFRAME CON LOS VALORES DUPLICADOS EN LA COLUMNA
    print("\n4. MOSTRAR DATAFRAME CON LOS VALORES DUPLICADOS EN LA COLUMNA")
    print("   (Filas donde df.duplicated('matricula', keep='first') es True)")
    print("-" * 80)
    df_solo_duplicados_first = df[duplicated_first]
    print(df_solo_duplicados_first.to_string(index=True))
    print("-" * 80)

    # 5. KEEP = 'FIRST' EJEMPLO (ALUMNOS CON MATRICULA ÚNICA - PRIMERA APARICIÓN)
    print("\n5. KEEP = 'FIRST' EJEMPLO (ALUMNOS CON MATRICULA ÚNICA - PRIMERA APARICIÓN)")
    print("   ( muestra la primera ocurrencia)")
    print("-" * 80)
    df_no_duplicados_first = df[~df.duplicated('matricula', keep='first')]
    print(df_no_duplicados_first.to_string(index=True))
    print("-" * 80)

    # 6. REVISAR TODAS LAS CATEGORIAS (DE NUEVO, para consistencia con el ejemplo)
    print("\n6. REVISAR TODAS LAS CATEGORIAS DE LA COLUMNA 'MATRICULA' (DE NUEVO)")
    print("-" * 80)
    print("df['matricula'].value_counts()")
    print(df['matricula'].value_counts())
    print("-" * 80)

    # 7. KEEP = 'LAST'
    print("\n7. VALORES DUPLICADOS EN LA COLUMNA 'MATRICULA' (CON KEEP = 'LAST')")
    print("   (Marca True hasta la penúltima aparición de cada matrícula)")
    print("-" * 80)
    print("df.duplicated('matricula', keep='last')")
    duplicated_last = df.duplicated('matricula', keep='last')
    print(duplicated_last.to_string())
    print("-" * 80)

    # 8. KEEP = 'LAST' EJEMPLO (ALUMNOS CON MATRICULA ÚNICA - ÚLTIMA APARICIÓN)
    print("\n8. KEEP = 'LAST' EJEMPLO (ALUMNOS CON MATRICULA ÚNICA - ÚLTIMA APARICIÓN)")
    print("   (Equivalente a 'laptops más caras por compañía' - muestra la última ocurrencia)")
    print("-" * 80)
    df_no_duplicados_last = df[~df.duplicated('matricula', keep='last')]
    print(df_no_duplicados_last.to_string(index=True))
    print("-" * 80)

    # 9. KEEP = FALSE (TODOS LOS DUPLICADOS SE VAN)
    print("\n9. KEEP = FALSE (TODOS LOS DUPLICADOS SE VAN)")
    print("   (Marca True si la matrícula aparece más de una vez, incluyendo todas las ocurrencias)")
    print("-" * 80)
    print("df.duplicated('matricula', keep=False)")
    duplicated_false = df.duplicated('matricula', keep=False)
    print(duplicated_false.to_string())
    print("-" * 80)

    # 10. MOSTRAR DATAFRAME CON VALORES NO DUPLICADOS EN COLUMNA
    print("\n10. MOSTRAR DATAFRAME CON VALORES NO DUPLICADOS EN COLUMNA")
    print("    (Filas donde df.duplicated('matricula', keep=False) es False - solo valores únicos)")
    print("-" * 80)
    df_unicos_completos = df[~df.duplicated('matricula', keep=False)]
    print(df_unicos_completos.to_string(index=True))
    print("-" * 80)

    # RESUMEN DE LOS 10 PUNTOS
    print(f"\n" + "="*60)
    print("RESUMEN DE LOS 10 PUNTOS IMPLEMENTADOS")
    print("="*60)
    
    print("✅ 1. ORDENAR DATAFRAME ASCENDENTE:")
    print("   • df.sort_values(by=['matricula', 'calificacion'], ascending=[True, True])")
    print("   • Más barato/menor primero, más caro/mayor al último")
    
    print("\n✅ 2. REVISAR TODAS LAS CATEGORIAS:")
    print("   • df['matricula'].value_counts()")
    print("   • Muestra conteo de cada categoría única")
    
    print("\n✅ 3. VALORES DUPLICADOS (KEEP='FIRST'):")
    print("   • df.duplicated('matricula', keep='first')")
    print("   • Marca True desde la segunda aparición")
    
    print("\n✅ 4. MOSTRAR DATAFRAME CON VALORES DUPLICADOS:")
    print("   • df[duplicated_first]")
    print("   • Filtra y muestra solo las filas duplicadas")
    
    print("\n✅ 5. KEEP='FIRST' EJEMPLO:")
    print("   • df[~df.duplicated('matricula', keep='first')]")
    print("   • Muestra primera ocurrencia de cada matrícula")
    
    print("\n✅ 6. REVISAR CATEGORIAS (DE NUEVO):")
    print("   • df['matricula'].value_counts()")
    print("   • Verificación de categorías")
    
    print("\n✅ 7. KEEP='LAST':")
    print("   • df.duplicated('matricula', keep='last')")
    print("   • Marca True hasta la penúltima aparición")
    
    print("\n✅ 8. KEEP='LAST' EJEMPLO:")
    print("   • df[~df.duplicated('matricula', keep='last')]")
    print("   • Muestra última ocurrencia de cada matrícula")
    
    print("\n✅ 9. KEEP=FALSE:")
    print("   • df.duplicated('matricula', keep=False)")
    print("   • Marca True para todas las ocurrencias de duplicados")
    
    print("\n✅ 10. MOSTRAR VALORES NO DUPLICADOS:")
    print("   • df[~df.duplicated('matricula', keep=False)]")
    print("   • Solo valores que aparecen una sola vez")
    
    print(f"\n🎉 ANÁLISIS DE ORDENAMIENTO Y DUPLICADOS COMPLETADO!")
    print("="*60)
    print("✅ Todos los 10 puntos implementados correctamente")

if __name__ == "__main__":
    main() 