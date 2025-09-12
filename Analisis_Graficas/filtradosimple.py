#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Filtrado Simple con Método isin - IAEDU1
Sistema de Gestión Educativa

Este script demuestra claramente los 4 puntos específicos:
1. Método isin
2. Condición para el filtrado del dataframe
3. isin filtrado multiple
4. filtrar dataframes

Autor: IAEDU1 Team
Fecha: 2025
"""

import pandas as pd
import numpy as np

def main():
    """
    Función principal del script
    """
    print("🔍 FILTRADO SIMPLE CON MÉTODO ISIN")
    print("=" * 60)
    
    # Crear DataFrame de ejemplo
    datos = {
        'id': [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        'matricula': ['20240001', '20240002', '20240003', '20240004', '20240005', 
                     '20240006', '20240007', '20240008', '20240009', '20240010'],
        'nombre': ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Sofia', 'Diego', 'Elena', 'Pedro', 'Carmen'],
        'grupo': ['1ºA', '1ºB', '2ºA', '2ºB', '3ºA', '3ºB', '4ºA', '4ºB', '1ºA', '2ºA'],
        'materia': ['Matemáticas', 'Historia', 'Ciencias', 'Literatura', 'Inglés', 'Arte', 'Educación Física', 'Matemáticas', 'Historia', 'Ciencias'],
        'calificacion': [85, 92, 78, 95, 88, 82, 90, 87, 93, 79],
        'asistencia': ['presente', 'presente', 'ausente', 'presente', 'tardanza', 'presente', 'ausente', 'presente', 'presente', 'tardanza']
    }
    
    df = pd.DataFrame(datos)
    print(f"📊 DataFrame creado con {len(df)} filas")
    print(f"📋 Columnas: {list(df.columns)}")
    
    # Mostrar datos originales
    print(f"\n📋 DATOS ORIGINALES:")
    print(df.to_string(index=True))
    
    # ==========================================
    # 1. CON MÉTODO ISIN
    # ==========================================
    print(f"\n" + "="*60)
    print("1. CON MÉTODO ISIN")
    print("="*60)
    
    # Filtrar por grupos específicos usando isin
    grupos_filtrar = ['1ºA', '2ºA', '3ºA']
    df_filtrado_grupos = df[df['grupo'].isin(grupos_filtrar)]
    
    print(f"Filtro: grupos {grupos_filtrar}")
    print(f"Resultado: {len(df_filtrado_grupos)} registros")
    print(df_filtrado_grupos.to_string(index=True))
    
    # ==========================================
    # 2. CONDICIÓN PARA EL FILTRADO DEL DATAFRAME
    # ==========================================
    print(f"\n" + "="*60)
    print("2. CONDICIÓN PARA EL FILTRADO DEL DATAFRAME")
    print("="*60)
    
    # Condición: grupos específicos Y materias específicas
    grupos_condicion = ['1ºA', '2ºA']
    materias_condicion = ['Matemáticas', 'Historia']
    
    condicion_multiple = (df['grupo'].isin(grupos_condicion)) & (df['materia'].isin(materias_condicion))
    df_filtrado_multiple = df[condicion_multiple]
    
    print(f"Condición: grupo IN {grupos_condicion} Y materia IN {materias_condicion}")
    print(f"Resultado: {len(df_filtrado_multiple)} registros")
    print(df_filtrado_multiple.to_string(index=True))
    
    # ==========================================
    # 3. ISIN FILTRADO MÚLTIPLE
    # ==========================================
    print(f"\n" + "="*60)
    print("3. ISIN FILTRADO MÚLTIPLE")
    print("="*60)
    
    # Filtrar por múltiples criterios usando isin
    criterios_grupos = ['1ºA', '2ºA', '3ºA']
    criterios_materias = ['Matemáticas', 'Historia']
    criterios_asistencia = ['presente', 'tardanza']
    
    # Aplicar múltiples filtros isin
    df_multiple_filtros = df[
        (df['grupo'].isin(criterios_grupos)) &
        (df['materia'].isin(criterios_materias)) &
        (df['asistencia'].isin(criterios_asistencia))
    ]
    
    print(f"Criterios múltiples:")
    print(f"  • Grupos: {criterios_grupos}")
    print(f"  • Materias: {criterios_materias}")
    print(f"  • Asistencia: {criterios_asistencia}")
    print(f"Resultado: {len(df_multiple_filtros)} registros")
    print(df_multiple_filtros.to_string(index=True))
    
    # ==========================================
    # 4. FILTRAR DATAFRAMES
    # ==========================================
    print(f"\n" + "="*60)
    print("4. FILTRAR DATAFRAMES")
    print("="*60)
    
    # Crear DataFrames separados por grupo
    grupos_unicos = df['grupo'].unique()
    dataframes_por_grupo = {}
    
    print("Creando DataFrames separados por grupo:")
    for grupo in grupos_unicos:
        dataframes_por_grupo[grupo] = df[df['grupo'].isin([grupo])]
        print(f"\nDataFrame {grupo}: {len(dataframes_por_grupo[grupo])} registros")
        print(dataframes_por_grupo[grupo].to_string(index=True))
    
    # Crear DataFrames separados por materia
    materias_unicas = df['materia'].unique()
    dataframes_por_materia = {}
    
    print(f"\n" + "-"*40)
    print("Creando DataFrames separados por materia:")
    for materia in materias_unicas:
        dataframes_por_materia[materia] = df[df['materia'].isin([materia])]
        print(f"\nDataFrame {materia}: {len(dataframes_por_materia[materia])} registros")
        print(dataframes_por_materia[materia].to_string(index=True))
    
    # ==========================================
    # RESUMEN DE LOS 4 PUNTOS
    # ==========================================
    print(f"\n" + "="*60)
    print("RESUMEN DE LOS 4 PUNTOS IMPLEMENTADOS")
    print("="*60)
    
    print("✅ 1. CON MÉTODO ISIN:")
    print("   • df[df['grupo'].isin(['1ºA', '2ºA', '3ºA'])]")
    print("   • Filtra registros donde grupo esté en la lista")
    
    print("\n✅ 2. CONDICIÓN PARA EL FILTRADO DEL DATAFRAME:")
    print("   • condicion = (df['grupo'].isin(grupos)) & (df['materia'].isin(materias))")
    print("   • Aplica múltiples condiciones con operadores lógicos")
    
    print("\n✅ 3. ISIN FILTRADO MÚLTIPLE:")
    print("   • df[(df['grupo'].isin(grupos)) & (df['materia'].isin(materias)) & (df['asistencia'].isin(asistencia))]")
    print("   • Combina múltiples criterios isin en una sola operación")
    
    print("\n✅ 4. FILTRAR DATAFRAMES:")
    print("   • dataframes_por_grupo[grupo] = df[df['grupo'].isin([grupo])]")
    print("   • Crea múltiples DataFrames filtrados por diferentes criterios")
    
    print(f"\n🎉 FILTRADO SIMPLE CON ISIN COMPLETADO!")
    print("="*60)

if __name__ == "__main__":
    main() 