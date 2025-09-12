#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Identificación de Filas Duplicadas - IAEDU1
Sistema de Gestión Educativa

Este script identifica y analiza filas duplicadas siguiendo exactamente los 6 puntos:
1. Identificar campos duplicados
2. Mostrar elementos duplicados
3. Contabilizar elementos duplicados
4. Duplicados en dos o más columnas
5. Asignar a una variable
6. Mostrar valores duplicados

Autor: IAEDU1 Team
Fecha: 2025
"""

import pandas as pd
import numpy as np

def main():
    """
    Función principal del script
    """
    print("🔍 IDENTIFICACIÓN DE FILAS DUPLICADAS")
    print("=" * 60)
    
    # Crear DataFrame con duplicados intencionales
    datos = {
        'id': [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        'matricula': ['20240001', '20240002', '20240003', '20240004', '20240005', 
                     '20240006', '20240007', '20240008', '20240009', '20240010',
                     '20240001', '20240002', '20240003', '20240004', '20240005'],
        'nombre': ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Sofia', 'Diego', 'Elena', 'Pedro', 'Carmen',
                  'Juan', 'María', 'Carlos', 'Ana', 'Luis'],
        'apellido': ['García', 'Rodríguez', 'López', 'Martínez', 'González', 'Pérez', 'Sánchez', 'Ramírez', 'Torres', 'Flores',
                    'García', 'Rodríguez', 'López', 'Martínez', 'González'],
        'grupo': ['1ºA', '1ºB', '2ºA', '2ºB', '3ºA', '3ºB', '4ºA', '4ºB', '1ºA', '2ºA',
                 '1ºA', '1ºB', '2ºA', '2ºB', '3ºA'],
        'materia': ['Matemáticas', 'Historia', 'Ciencias', 'Literatura', 'Inglés', 'Arte', 'Educación Física', 'Matemáticas', 'Historia', 'Ciencias',
                   'Matemáticas', 'Historia', 'Ciencias', 'Literatura', 'Inglés'],
        'calificacion': [85, 92, 78, 95, 88, 82, 90, 87, 93, 79, 85, 92, 78, 95, 88],
        'asistencia': ['presente', 'presente', 'ausente', 'presente', 'tardanza', 'presente', 'ausente', 'presente', 'presente', 'tardanza',
                      'presente', 'presente', 'ausente', 'presente', 'tardanza']
    }
    
    df = pd.DataFrame(datos)
    print(f"📊 DataFrame creado con {len(df)} filas")
    print(f"📋 Columnas: {list(df.columns)}")
    
    # Mostrar datos originales
    print(f"\n📋 DATOS ORIGINALES:")
    print(df.head(3).to_string(index=True))
    
    # ==========================================
    # 1. IDENTIFICAR CAMPOS DUPLICADOS
    # ==========================================
    print(f"\n" + "="*60)
    print("1. IDENTIFICAR CAMPOS DUPLICADOS")
    print("="*60)
    
    # Identificar duplicados por matrícula (como en las imágenes)
    print("df.duplicated('matricula')")
    duplicados_matricula = df.duplicated('matricula')
    print(duplicados_matricula)
    
    # ==========================================
    # 2. MOSTRAR ELEMENTOS DUPLICADOS
    # ==========================================
    print(f"\n" + "="*60)
    print("2. MOSTRAR ELEMENTOS DUPLICADOS")
    print("="*60)
    
    # Mostrar elementos duplicados por matrícula
    print("df[df.duplicated('matricula')]")
    elementos_duplicados = df[df.duplicated('matricula')]
    print(elementos_duplicados.to_string(index=True))
    
    # ==========================================
    # 3. CONTABILIZAR ELEMENTOS DUPLICADOS
    # ==========================================
    print(f"\n" + "="*60)
    print("3. CONTABILIZAR ELEMENTOS DUPLICADOS")
    print("="*60)
    
    # Contabilizar elementos duplicados
    print("df[df.duplicated('matricula')].value_counts()")
    contabilizar_duplicados = df[df.duplicated('matricula')]['matricula'].value_counts()
    print(contabilizar_duplicados)
    
    # ==========================================
    # 4. DUPLICADOS EN DOS O MÁS COLUMNAS
    # ==========================================
    print(f"\n" + "="*60)
    print("4. DUPLICADOS EN DOS O MÁS COLUMNAS")
    print("="*60)
    
    # Duplicados en dos o más columnas (como en las imágenes)
    print("df.duplicated(['nombre', 'apellido', 'grupo'])")
    duplicados_multiple = df.duplicated(['nombre', 'apellido', 'grupo'])
    print(duplicados_multiple)
    
    # ==========================================
    # 5. ASIGNAR A UNA VARIABLE
    # ==========================================
    print(f"\n" + "="*60)
    print("5. ASIGNAR A UNA VARIABLE")
    print("="*60)
    
    # Asignar a una variable (como en las imágenes)
    print("duplicated = df.duplicated(['nombre', 'apellido', 'grupo'])")
    duplicated = df.duplicated(['nombre', 'apellido', 'grupo'])
    print("Variable 'duplicated' asignada correctamente")
    print(f"Tipo: {type(duplicated)}")
    print(f"Longitud: {len(duplicated)}")
    
    # ==========================================
    # 6. MOSTRAR VALORES DUPLICADOS
    # ==========================================
    print(f"\n" + "="*60)
    print("6. MOSTRAR VALORES DUPLICADOS")
    print("="*60)
    
    # Mostrar valores duplicados usando la variable asignada
    print("df[duplicated]")
    valores_duplicados = df[duplicated]
    print(valores_duplicados.to_string(index=True))
    
    # Mostrar valores duplicados ordenados
    print(f"\ndf[duplicated].sort_values(['nombre', 'apellido'])")
    valores_duplicados_ordenados = df[duplicated].sort_values(['nombre', 'apellido'])
    print(valores_duplicados_ordenados.to_string(index=True))
    
    # ==========================================
    # RESUMEN DE LOS 6 PUNTOS
    # ==========================================
    print(f"\n" + "="*60)
    print("RESUMEN DE LOS 6 PUNTOS IMPLEMENTADOS")
    print("="*60)
    
    print("✅ 1. IDENTIFICAR CAMPOS DUPLICADOS:")
    print("   • df.duplicated('matricula')")
    print("   • Retorna Series booleana indicando duplicados")
    
    print("\n✅ 2. MOSTRAR ELEMENTOS DUPLICADOS:")
    print("   • df[df.duplicated('matricula')]")
    print("   • Filtra y muestra solo las filas duplicadas")
    
    print("\n✅ 3. CONTABILIZAR ELEMENTOS DUPLICADOS:")
    print("   • df[df.duplicated('matricula')].value_counts()")
    print("   • Cuenta las ocurrencias de valores duplicados")
    
    print("\n✅ 4. DUPLICADOS EN DOS O MÁS COLUMNAS:")
    print("   • df.duplicated(['nombre', 'apellido', 'grupo'])")
    print("   • Identifica duplicados basados en múltiples columnas")
    
    print("\n✅ 5. ASIGNAR A UNA VARIABLE:")
    print("   • duplicated = df.duplicated(['nombre', 'apellido', 'grupo'])")
    print("   • Guarda el resultado en una variable para reutilizar")
    
    print("\n✅ 6. MOSTRAR VALORES DUPLICADOS:")
    print("   • df[duplicated]")
    print("   • Usa la variable para mostrar los valores duplicados")
    
    print(f"\n🎉 IDENTIFICACIÓN DE FILAS DUPLICADAS COMPLETADA!")
    print("="*60)
    print("✅ Todos los 6 puntos implementados correctamente")

if __name__ == "__main__":
    main() 