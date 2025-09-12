#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Eliminación de Duplicados - CSV Alumnos
Sistema de Gestión Educativa IAEDU1

Este script elimina duplicados del CSV de alumnos siguiendo
específicos criterios de limpieza de datos.

Autor: IAEDU1 Team
Fecha: 2025
"""

import pandas as pd
import numpy as np
from pathlib import Path

def main():
    """
    Función principal del script
    """
    print("🧹 ELIMINACIÓN DE DUPLICADOS - CSV ALUMNOS")
    print("=" * 60)
    
    # 1. Cargar archivo alumnos_duplicados.csv
    print("\n📁 CARGANDO ARCHIVO...")
    ruta_csv = Path("alumnos_duplicados.csv")
    
    if not ruta_csv.exists():
        print(f"❌ No se encontró el archivo: {ruta_csv}")
        print("💡 Ejecuta primero: python crear_csv_alumnos_duplicados.py")
        return
    
    # Cargar datos originales
    df_original = pd.read_csv(ruta_csv)
    print(f"✅ Archivo cargado: {len(df_original)} filas originales")
    print(f"📊 Columnas disponibles: {list(df_original.columns)}")
    
    # Mostrar información inicial
    print(f"\n📋 INFORMACIÓN INICIAL:")
    print(f"   • Filas totales: {len(df_original)}")
    print(f"   • Columnas: {len(df_original.columns)}")
    print(f"   • Duplicados totales: {df_original.duplicated().sum()}")
    
    # 2. Mostrar duplicados por diferentes criterios
    print(f"\n🔍 ANÁLISIS DE DUPLICADOS:")
    
    # Duplicados por matrícula y materia (2 columnas)
    duplicados_matricula_materia = df_original.duplicated(subset=['matricula', 'materia']).sum()
    print(f"   • Duplicados por matrícula + materia: {duplicados_matricula_materia}")
    
    # Duplicados por nombre y apellido (2 columnas)
    duplicados_nombre_apellido = df_original.duplicated(subset=['nombre', 'apellido']).sum()
    print(f"   • Duplicados por nombre + apellido: {duplicados_nombre_apellido}")
    
    # Duplicados por matrícula, materia y fecha (3 columnas)
    duplicados_matricula_materia_fecha = df_original.duplicated(subset=['matricula', 'materia', 'fecha']).sum()
    print(f"   • Duplicados por matrícula + materia + fecha: {duplicados_matricula_materia_fecha}")
    
    # Duplicados por grupo y materia (2 columnas)
    duplicados_grupo_materia = df_original.duplicated(subset=['grupo', 'materia']).sum()
    print(f"   • Duplicados por grupo + materia: {duplicados_grupo_materia}")
    
    # Duplicados por todas las columnas
    duplicados_completos = df_original.duplicated().sum()
    print(f"   • Duplicados completos: {duplicados_completos}")
    
    # 3. DROP DUPLICATED - ELIMINAR DUPLICADOS EN 2 O MÁS COLUMNAS
    print(f"\n🧹 ELIMINANDO DUPLICADOS...")
    
    # Crear copia del dataframe original (inplace=False - devolviendo la copia)
    df_limpio = df_original.copy()
    
    # Eliminar duplicados basados en matrícula y materia (2 columnas)
    print(f"   • Eliminando duplicados por matrícula + materia (2 columnas)...")
    df_limpio = df_limpio.drop_duplicates(
        subset=['matricula', 'materia'],  # 2 o más columnas
        keep='first',                     # Mantener el primer registro
        ignore_index=True                 # El index resultante tendrá la etiqueta 0, 1, 2, etc.
    )
    
    print(f"   • Filas después de eliminar duplicados por 2 columnas: {len(df_limpio)}")
    
    # 4. ORDENAR DATAFRAME DE FORMA ASCENDENTE
    print(f"\n📊 ORDENANDO DATAFRAME...")
    
    # Ordenar por matrícula (ascendente) y calificación (ascendente)
    # Equivalente a ordenar por "compañía" (matrícula) y "precio" (calificación)
    # Con el más barato primero y más caro al último
    df_ordenado = df_limpio.sort_values(
        by=['matricula', 'calificacion'],  # "compañía" y "precio"
        ascending=[True, True]             # Ascendente para ambos (más barato primero)
    ).reset_index(drop=True)              # Resetear índices
    
    print(f"   • DataFrame ordenado por matrícula (ascendente) y calificación (ascendente)")
    print(f"   • (menor matrícula y calificación)")
    print(f"   • (mayor matrícula y calificación)")
    print(f"   • Filas ordenadas: {len(df_ordenado)}")
    
    # 5. MOSTRAR COMPARACIÓN ANTES Y DESPUÉS
    print(f"\n📈 COMPARACIÓN ANTES Y DESPUÉS:")
    print(f"   • Filas originales: {len(df_original)}")
    print(f"   • Filas después de limpieza: {len(df_ordenado)}")
    print(f"   • Duplicados eliminados: {len(df_original) - len(df_ordenado)}")
    print(f"   • Reducción: {((len(df_original) - len(df_ordenado)) / len(df_original) * 100):.1f}%")
    
    # 6. MOSTRAR EJEMPLOS DE DATOS LIMPIOS
    print(f"\n📋 MUESTRA DE DATOS LIMPIOS:")
    print(df_ordenado.head(10).to_string(index=True))
    
    # 7. VERIFICAR QUE NO HAY DUPLICADOS
    print(f"\n✅ VERIFICACIÓN FINAL:")
    
    # Verificar duplicados por matrícula y materia
    duplicados_finales = df_ordenado.duplicated(subset=['matricula', 'materia']).sum()
    print(f"   • Duplicados restantes por matrícula + materia: {duplicados_finales}")
    
    # Verificar duplicados completos
    duplicados_completos_finales = df_ordenado.duplicated().sum()
    print(f"   • Duplicados completos restantes: {duplicados_completos_finales}")
    
    # 8. GUARDAR DATOS LIMPIOS
    print(f"\n💾 GUARDANDO DATOS LIMPIOS...")
    
    # Crear nombre de archivo con timestamp
    from datetime import datetime
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    archivo_limpio = f"alumnos_sin_duplicados_{timestamp}.csv"
    
    # Guardar archivo limpio
    df_ordenado.to_csv(archivo_limpio, index=False)
    print(f"   • Archivo guardado: {archivo_limpio}")
    
    # 9. ESTADÍSTICAS FINALES
    print(f"\n📊 ESTADÍSTICAS FINALES:")
    print(f"   • Total de registros únicos: {len(df_ordenado)}")
    print(f"   • Alumnos únicos: {df_ordenado['matricula'].nunique()}")
    print(f"   • Materias únicas: {df_ordenado['materia'].nunique()}")
    print(f"   • Grupos únicos: {df_ordenado['grupo'].nunique()}")
    
    # Mostrar distribución por grupo
    print(f"\n📊 DISTRIBUCIÓN POR GRUPO:")
    distribucion_grupo = df_ordenado['grupo'].value_counts()
    for grupo, count in distribucion_grupo.items():
        porcentaje = (count / len(df_ordenado)) * 100
        print(f"   • {grupo}: {count} ({porcentaje:.1f}%)")
    
    # Mostrar rango de calificaciones
    print(f"\n📚 RANGO DE CALIFICACIONES:")
    print(f"   • Calificación mínima: {df_ordenado['calificacion'].min()}")
    print(f"   • Calificación máxima: {df_ordenado['calificacion'].max()}")
    print(f"   • Calificación promedio: {df_ordenado['calificacion'].mean():.2f}")
    
    # Mostrar distribución por asistencia
    print(f"\n📊 DISTRIBUCIÓN POR ASISTENCIA:")
    distribucion_asistencia = df_ordenado['asistencia'].value_counts()
    for asistencia, count in distribucion_asistencia.items():
        porcentaje = (count / len(df_ordenado)) * 100
        print(f"   • {asistencia}: {count} ({porcentaje:.1f}%)")
    
    # 10. VERIFICACIÓN DE REQUISITOS CUMPLIDOS
    print(f"\n🔧 VERIFICACIÓN DE REQUISITOS:")
    print("-" * 50)
    print(f"✅ DROP DUPLICATED: Eliminar duplicados en 2 o más columnas")
    print(f"   • Columnas usadas: ['matricula', 'materia'] (2 columnas)")
    print(f"   • Método: drop_duplicates(subset=['matricula', 'materia'])")
    
    print(f"\n✅ ORDENAR DATAFRAME: Forma ascendente")
    print(f"   • Ordenamiento: matrícula ASC, calificación ASC")
    print(f"   •  (menor matrícula y calificación)")
    print(f"   • (mayor matrícula y calificación)")
    
    print(f"\n✅ INPLACE: Eliminar duplicados devolviendo la copia")
    print(f"   • inplace=False (por defecto)")
    print(f"   • Retorna copia del dataframe original")
    print(f"   • No modifica el dataframe original")
    
    print(f"\n✅ IGNORE_INDEX=True: Índices secuenciales")
    print(f"   • ignore_index=True")
    print(f"   • El index resultante tendrá la etiqueta 0, 1, 2, etc.")
    print(f"   • Índices secuenciales sin gaps")
    
    # 11. DEMOSTRACIÓN DE FUNCIONALIDADES
    print(f"\n🎯 DEMOSTRACIÓN DE FUNCIONALIDADES:")
    print("-" * 50)
    
    # Mostrar primeros 5 registros ordenados 
    print("Primeros 5 registros (menor matrícula y calificación):")
    print(df_ordenado.head().to_string(index=True))
    
    # Mostrar últimos 5 registros ordenados (más caros)
    print("\nÚltimos 5 registros (mayor matrícula y calificación):")
    print(df_ordenado.tail().to_string(index=True))
    
    # Mostrar estadísticas por materia
    print(f"\n📚 ESTADÍSTICAS POR MATERIA:")
    print("-" * 30)
    for materia in df_ordenado['materia'].unique():
        materia_df = df_ordenado[df_ordenado['materia'] == materia]
        print(f"   • {materia}: {len(materia_df)} registros, promedio: {materia_df['calificacion'].mean():.1f}")
    
    print(f"\n🎉 PROCESO COMPLETADO EXITOSAMENTE!")
    print("=" * 60)
    print(f"📁 Archivo original: {ruta_csv}")
    print(f"📁 Archivo limpio: {archivo_limpio}")
    print(f"📊 Registros procesados: {len(df_original)} → {len(df_ordenado)}")
    print(f"✅ TODOS LOS REQUISITOS CUMPLIDOS")

if __name__ == "__main__":
    main() 