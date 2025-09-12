#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Creación de CSV con Alumnos Duplicados - IAEDU1
Sistema de Gestión Educativa

Este script crea un CSV específicamente con alumnos duplicados para
probar la funcionalidad de eliminación de duplicados.

Autor: IAEDU1 Team
Fecha: 2025
"""

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import random

def crear_csv_alumnos_duplicados():
    """
    Crea un CSV específicamente con alumnos duplicados para testing
    """
    print("📊 CREANDO CSV CON ALUMNOS DUPLICADOS")
    print("=" * 50)
    
    # 1. Datos de ejemplo de alumnos
    nombres = ['Juan', 'María', 'Carlos', 'Ana', 'Luis', 'Sofia', 'Diego', 'Elena', 'Pedro', 'Carmen']
    apellidos = ['García', 'Rodríguez', 'López', 'Martínez', 'González', 'Pérez', 'Sánchez', 'Ramírez', 'Torres', 'Flores']
    grupos = ['1ºA', '1ºB', '2ºA', '2ºB', '3ºA', '3ºB', '4ºA', '4ºB']
    materias = ['Matemáticas', 'Historia', 'Ciencias', 'Literatura', 'Inglés', 'Arte', 'Educación Física']
    
    # 2. Crear datos con duplicados intencionales
    datos = []
    
    # Generar datos base de alumnos (sin duplicados)
    for i in range(20):
        nombre = random.choice(nombres)
        apellido = random.choice(apellidos)
        grupo = random.choice(grupos)
        materia = random.choice(materias)
        calificacion = random.randint(60, 100)
        fecha = datetime.now() - timedelta(days=random.randint(1, 365))
        
        datos.append({
            'id': i + 1,
            'matricula': f"2024{str(i+1).zfill(4)}",
            'nombre': nombre,
            'apellido': apellido,
            'grupo': grupo,
            'materia': materia,
            'calificacion': calificacion,
            'fecha': fecha.strftime('%Y-%m-%d'),
            'asistencia': random.choice(['presente', 'ausente', 'tardanza']),
            'estado': random.choice(['activo', 'inactivo', 'graduado'])
        })
    
    # 3. AGREGAR DUPLICADOS INTENCIONALES PARA TESTING
    duplicados = [
        # DUPLICADOS POR MATRÍCULA Y MATERIA (2 columnas)
        {'id': 21, 'matricula': '20240001', 'nombre': 'Juan', 'apellido': 'García', 'grupo': '1ºA', 'materia': 'Matemáticas', 'calificacion': 85, 'fecha': '2024-01-15', 'asistencia': 'presente', 'estado': 'activo'},
        {'id': 22, 'matricula': '20240001', 'nombre': 'Juan', 'apellido': 'García', 'grupo': '1ºA', 'materia': 'Matemáticas', 'calificacion': 85, 'fecha': '2024-01-15', 'asistencia': 'presente', 'estado': 'activo'},
        {'id': 23, 'matricula': '20240001', 'nombre': 'Juan', 'apellido': 'García', 'grupo': '1ºA', 'materia': 'Matemáticas', 'calificacion': 85, 'fecha': '2024-01-15', 'asistencia': 'presente', 'estado': 'activo'},
        
        # DUPLICADOS POR NOMBRE Y APELLIDO (2 columnas)
        {'id': 24, 'matricula': '20240002', 'nombre': 'María', 'apellido': 'Rodríguez', 'grupo': '2ºB', 'materia': 'Historia', 'calificacion': 92, 'fecha': '2024-02-20', 'asistencia': 'presente', 'estado': 'activo'},
        {'id': 25, 'matricula': '20240003', 'nombre': 'María', 'apellido': 'Rodríguez', 'grupo': '2ºB', 'materia': 'Ciencias', 'calificacion': 88, 'fecha': '2024-02-20', 'asistencia': 'tardanza', 'estado': 'activo'},
        {'id': 26, 'matricula': '20240004', 'nombre': 'María', 'apellido': 'Rodríguez', 'grupo': '2ºB', 'materia': 'Literatura', 'calificacion': 90, 'fecha': '2024-02-20', 'asistencia': 'presente', 'estado': 'activo'},
        
        # DUPLICADOS COMPLETOS (todas las columnas)
        {'id': 27, 'matricula': '20240005', 'nombre': 'Carlos', 'apellido': 'López', 'grupo': '3ºA', 'materia': 'Inglés', 'calificacion': 78, 'fecha': '2024-03-10', 'asistencia': 'ausente', 'estado': 'activo'},
        {'id': 28, 'matricula': '20240005', 'nombre': 'Carlos', 'apellido': 'López', 'grupo': '3ºA', 'materia': 'Inglés', 'calificacion': 78, 'fecha': '2024-03-10', 'asistencia': 'ausente', 'estado': 'activo'},
        {'id': 29, 'matricula': '20240005', 'nombre': 'Carlos', 'apellido': 'López', 'grupo': '3ºA', 'materia': 'Inglés', 'calificacion': 78, 'fecha': '2024-03-10', 'asistencia': 'ausente', 'estado': 'activo'},
        {'id': 30, 'matricula': '20240005', 'nombre': 'Carlos', 'apellido': 'López', 'grupo': '3ºA', 'materia': 'Inglés', 'calificacion': 78, 'fecha': '2024-03-10', 'asistencia': 'ausente', 'estado': 'activo'},
        
        # MÁS DUPLICADOS VARIADOS
        {'id': 31, 'matricula': '20240006', 'nombre': 'Ana', 'apellido': 'Martínez', 'grupo': '4ºB', 'materia': 'Arte', 'calificacion': 95, 'fecha': '2024-04-05', 'asistencia': 'presente', 'estado': 'activo'},
        {'id': 32, 'matricula': '20240006', 'nombre': 'Ana', 'apellido': 'Martínez', 'grupo': '4ºB', 'materia': 'Arte', 'calificacion': 95, 'fecha': '2024-04-05', 'asistencia': 'presente', 'estado': 'activo'},
        
        {'id': 33, 'matricula': '20240007', 'nombre': 'Luis', 'apellido': 'González', 'grupo': '1ºB', 'materia': 'Educación Física', 'calificacion': 82, 'fecha': '2024-05-12', 'asistencia': 'presente', 'estado': 'activo'},
        {'id': 34, 'matricula': '20240007', 'nombre': 'Luis', 'apellido': 'González', 'grupo': '1ºB', 'materia': 'Educación Física', 'calificacion': 82, 'fecha': '2024-05-12', 'asistencia': 'presente', 'estado': 'activo'},
        {'id': 35, 'matricula': '20240007', 'nombre': 'Luis', 'apellido': 'González', 'grupo': '1ºB', 'materia': 'Educación Física', 'calificacion': 82, 'fecha': '2024-05-12', 'asistencia': 'presente', 'estado': 'activo'},
        
        # DUPLICADOS POR MATRÍCULA, MATERIA Y FECHA (3 columnas)
        {'id': 36, 'matricula': '20240008', 'nombre': 'Sofia', 'apellido': 'Pérez', 'grupo': '2ºA', 'materia': 'Matemáticas', 'calificacion': 88, 'fecha': '2024-06-01', 'asistencia': 'presente', 'estado': 'activo'},
        {'id': 37, 'matricula': '20240008', 'nombre': 'Sofia', 'apellido': 'Pérez', 'grupo': '2ºA', 'materia': 'Matemáticas', 'calificacion': 88, 'fecha': '2024-06-01', 'asistencia': 'presente', 'estado': 'activo'},
        
        # DUPLICADOS POR GRUPO Y MATERIA
        {'id': 38, 'matricula': '20240009', 'nombre': 'Diego', 'apellido': 'Sánchez', 'grupo': '3ºB', 'materia': 'Historia', 'calificacion': 75, 'fecha': '2024-07-15', 'asistencia': 'tardanza', 'estado': 'activo'},
        {'id': 39, 'matricula': '20240010', 'nombre': 'Elena', 'apellido': 'Ramírez', 'grupo': '3ºB', 'materia': 'Historia', 'calificacion': 89, 'fecha': '2024-07-15', 'asistencia': 'presente', 'estado': 'activo'},
        {'id': 40, 'matricula': '20240011', 'nombre': 'Pedro', 'apellido': 'Torres', 'grupo': '3ºB', 'materia': 'Historia', 'calificacion': 91, 'fecha': '2024-07-15', 'asistencia': 'presente', 'estado': 'activo'},
        
        # DUPLICADOS POR ESTADO Y ASISTENCIA
        {'id': 41, 'matricula': '20240012', 'nombre': 'Carmen', 'apellido': 'Flores', 'grupo': '4ºA', 'materia': 'Ciencias', 'calificacion': 93, 'fecha': '2024-08-20', 'asistencia': 'presente', 'estado': 'activo'},
        {'id': 42, 'matricula': '20240013', 'nombre': 'Juan', 'apellido': 'García', 'grupo': '4ºA', 'materia': 'Literatura', 'calificacion': 87, 'fecha': '2024-08-20', 'asistencia': 'presente', 'estado': 'activo'},
        {'id': 43, 'matricula': '20240014', 'nombre': 'María', 'apellido': 'Rodríguez', 'grupo': '4ºA', 'materia': 'Inglés', 'calificacion': 94, 'fecha': '2024-08-20', 'asistencia': 'presente', 'estado': 'activo'},
    ]
    
    # Agregar duplicados a los datos
    datos.extend(duplicados)
    
    # 4. Crear DataFrame
    df = pd.DataFrame(datos)
    
    print(f"✅ DataFrame creado con {len(df)} filas")
    print(f"📊 Columnas: {list(df.columns)}")
    
    return df

def mostrar_analisis_duplicados(df):
    """
    Muestra análisis detallado de duplicados en el DataFrame
    """
    print(f"\n🔍 ANÁLISIS DE DUPLICADOS:")
    print("-" * 40)
    
    # Duplicados por matrícula y materia (2 columnas)
    duplicados_matricula_materia = df.duplicated(subset=['matricula', 'materia']).sum()
    print(f"• Duplicados por matrícula + materia: {duplicados_matricula_materia}")
    
    # Duplicados por nombre y apellido (2 columnas)
    duplicados_nombre_apellido = df.duplicated(subset=['nombre', 'apellido']).sum()
    print(f"• Duplicados por nombre + apellido: {duplicados_nombre_apellido}")
    
    # Duplicados por matrícula, materia y fecha (3 columnas)
    duplicados_matricula_materia_fecha = df.duplicated(subset=['matricula', 'materia', 'fecha']).sum()
    print(f"• Duplicados por matrícula + materia + fecha: {duplicados_matricula_materia_fecha}")
    
    # Duplicados por grupo y materia (2 columnas)
    duplicados_grupo_materia = df.duplicated(subset=['grupo', 'materia']).sum()
    print(f"• Duplicados por grupo + materia: {duplicados_grupo_materia}")
    
    # Duplicados por estado y asistencia (2 columnas)
    duplicados_estado_asistencia = df.duplicated(subset=['estado', 'asistencia']).sum()
    print(f"• Duplicados por estado + asistencia: {duplicados_estado_asistencia}")
    
    # Duplicados completos
    duplicados_completos = df.duplicated().sum()
    print(f"• Duplicados completos: {duplicados_completos}")
    
    # Mostrar ejemplos de duplicados
    print(f"\n📋 EJEMPLOS DE DUPLICADOS:")
    print("-" * 40)
    
    # Duplicados por matrícula y materia
    duplicados_por_matricula_materia = df[df.duplicated(subset=['matricula', 'materia'], keep=False)]
    if len(duplicados_por_matricula_materia) > 0:
        print("Duplicados por matrícula + materia:")
        print(duplicados_por_matricula_materia[['id', 'matricula', 'nombre', 'apellido', 'materia', 'calificacion']].head(10))

def guardar_csv(df, nombre_archivo="alumnos_duplicados.csv"):
    """
    Guarda el DataFrame en un archivo CSV
    """
    df.to_csv(nombre_archivo, index=False)
    print(f"\n💾 CSV guardado como: {nombre_archivo}")
    print(f"📁 Ubicación: {nombre_archivo}")

def main():
    """
    Función principal del script
    """
    print("📊 CREACIÓN DE CSV CON ALUMNOS DUPLICADOS")
    print("=" * 60)
    
    # 1. Crear DataFrame con duplicados
    df_duplicados = crear_csv_alumnos_duplicados()
    
    # 2. Mostrar información inicial
    print(f"\n📋 INFORMACIÓN INICIAL:")
    print(f"   • Filas totales: {len(df_duplicados)}")
    print(f"   • Columnas: {len(df_duplicados.columns)}")
    print(f"   • Alumnos únicos: {df_duplicados['matricula'].nunique()}")
    print(f"   • Materias únicas: {df_duplicados['materia'].nunique()}")
    print(f"   • Grupos únicos: {df_duplicados['grupo'].nunique()}")
    print(f"   • Rango de calificaciones: {df_duplicados['calificacion'].min()} - {df_duplicados['calificacion'].max()}")
    
    # 3. Mostrar muestra de datos
    print(f"\n📋 MUESTRA DE DATOS:")
    print(df_duplicados.head(10).to_string(index=True))
    
    # 4. Análisis de duplicados
    mostrar_analisis_duplicados(df_duplicados)
    
    # 5. Guardar CSV
    guardar_csv(df_duplicados)
    
    # 6. Información para testing
    print(f"\n🧪 INFORMACIÓN PARA TESTING:")
    print("-" * 40)
    print("Este CSV contiene duplicados intencionales para probar:")
    print("• drop_duplicates() en 2 o más columnas")
    print("• Ordenamiento ascendente por matrícula y calificación")
    print("• inplace=False (devolviendo copia)")
    print("• ignore_index=True (índices 0, 1, 2, etc.)")
    
    print(f"\n📊 ESTADÍSTICAS DEL CSV:")
    print("-" * 40)
    print(f"• Total de filas: {len(df_duplicados)}")
    print(f"• Alumnos únicos: {df_duplicados['matricula'].nunique()}")
    print(f"• Materias: {df_duplicados['materia'].value_counts().to_dict()}")
    print(f"• Grupos: {df_duplicados['grupo'].value_counts().to_dict()}")
    print(f"• Estados: {df_duplicados['estado'].value_counts().to_dict()}")
    
    print(f"\n🎉 CSV CON ALUMNOS DUPLICADOS CREADO EXITOSAMENTE!")
    print("=" * 60)
    print("Ahora puedes usar este archivo para probar eliminarduplicados_alumnos.py")

if __name__ == "__main__":
    main() 