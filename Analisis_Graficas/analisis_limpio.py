#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Analisis de Asistencia - ATTENDANCES.CSV
Sistema IAEDU1
"""

import pandas as pd
import numpy as np
import matplotlib.pyplot as plt

# Cargar datos
df_attendances = pd.read_csv("../csv_bd/attendances.csv")
df_students = pd.read_csv("../csv_bd/students.csv")
df_groups = pd.read_csv("../csv_bd/groups.csv")

# Enriquecer datos
df_enriquecido = df_attendances.merge(
    df_students[['id', 'matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'group_id']], 
    left_on='student_id', right_on='id', how='left'
).merge(
    df_groups[['id', 'name']], 
    left_on='group_id', right_on='id', how='left', suffixes=('', '_group')
)

df_enriquecido['Nombre_Completo'] = df_enriquecido['nombre'] + ' ' + df_enriquecido['apellido_paterno'] + ' ' + df_enriquecido['apellido_materno']
df_enriquecido = df_enriquecido.rename(columns={'name': 'Grupo'})

# Calcular estadisticas
estadisticas_estudiante = df_enriquecido.groupby(['student_id', 'Nombre_Completo', 'matricula', 'Grupo']).agg({
    'status': ['count', lambda x: (x == 'absent').sum(), lambda x: (x == 'present').sum(), lambda x: (x == 'late').sum()]
}).reset_index()

estadisticas_estudiante.columns = ['student_id', 'Nombre_Completo', 'matricula', 'Grupo', 
                                  'Total_Registros', 'Total_Ausencias', 'Total_Presentes', 'Total_Retardos']

estadisticas_estudiante['Porcentaje_Ausencias'] = (estadisticas_estudiante['Total_Ausencias'] / estadisticas_estudiante['Total_Registros']) * 100

# 1. CREAR CONDICIONES para múltiples niveles de riesgo
condiciones = [
    estadisticas_estudiante['Porcentaje_Ausencias'] > 80,
    (estadisticas_estudiante['Porcentaje_Ausencias'] > 50) & (estadisticas_estudiante['Porcentaje_Ausencias'] <= 80),
    (estadisticas_estudiante['Porcentaje_Ausencias'] > 20) & (estadisticas_estudiante['Porcentaje_Ausencias'] <= 50),
    estadisticas_estudiante['Porcentaje_Ausencias'] <= 20
]

# 2. CREAR VALORES para asignarlos
valores = ['Muy Alto Riesgo', 'Alto Riesgo', 'Riesgo Moderado', 'Bajo Riesgo']

# 3. AÑADIR A NUEVA COLUMNA
# 4. SE INGRESA EL VALOR DE DEFAULT para evitar error de tipo de dato
estadisticas_estudiante['Clasificacion_Riesgo'] = np.select(condiciones, valores, default='No especificado')

# 5. CONTEO EN BASE A LA CLASIFICACION
conteo_clasificacion = estadisticas_estudiante['Clasificacion_Riesgo'].value_counts()

# 6. REORDENAR CATEGORIA
conteo_reordenado = conteo_clasificacion.reindex(valores)

# Mostrar resultados
print("ANALISIS DE ASISTENCIA - SISTEMA IAEDU1")
print("=" * 50)

print("\nDATOS CARGADOS:")
print(f"Asistencias: {df_attendances.shape[0]} filas, {df_attendances.shape[1]} columnas")
print(f"Estudiantes: {df_students.shape[0]} filas, {df_students.shape[1]} columnas")
print(f"Grupos: {df_groups.shape[0]} filas, {df_groups.shape[1]} columnas")

print("\nESTADISTICAS GENERALES:")
print(f"Estudiantes analizados: {len(estadisticas_estudiante)}")
print(f"Total registros: {estadisticas_estudiante['Total_Registros'].sum()}")
print(f"Total ausencias: {estadisticas_estudiante['Total_Ausencias'].sum()}")
print(f"Total presentes: {estadisticas_estudiante['Total_Presentes'].sum()}")
print(f"Total retardos: {estadisticas_estudiante['Total_Retardos'].sum()}")

print("\nDISTRIBUCION DE RIESGO (CONTEO REORDENADO):")
for categoria in valores:
    cantidad = conteo_reordenado.get(categoria, 0)
    porcentaje = (cantidad / len(estadisticas_estudiante)) * 100
    print(f"{categoria}: {cantidad} estudiantes ({porcentaje:.1f}%)")

print("\nPRIMERAS 5 FILAS DE ESTADISTICAS:")
print(estadisticas_estudiante.head())

print("\nGRUPOS DISPONIBLES:")
grupos = estadisticas_estudiante['Grupo'].unique().tolist()
for i, grupo in enumerate(grupos, 1):
    print(f"{i}. {grupo}")

print("\nESTUDIANTES CON MAYOR RIESGO (Top 10):")
alto_riesgo = estadisticas_estudiante[estadisticas_estudiante['Clasificacion_Riesgo'].isin(['Muy Alto Riesgo', 'Alto Riesgo'])]
alto_riesgo_sorted = alto_riesgo.sort_values('Porcentaje_Ausencias', ascending=False)
print(alto_riesgo_sorted[['Nombre_Completo', 'matricula', 'Grupo', 'Porcentaje_Ausencias', 'Clasificacion_Riesgo']].head(10))

print("\nANALISIS POR GRUPO:")
for grupo in grupos:
    datos_grupo = estadisticas_estudiante[estadisticas_estudiante['Grupo'] == grupo]
    print(f"\n{grupo}:")
    print(f"  Estudiantes: {len(datos_grupo)}")
    print(f"  Ausencias promedio: {datos_grupo['Porcentaje_Ausencias'].mean():.1f}%")
    alto_riesgo = len(datos_grupo[datos_grupo['Clasificacion_Riesgo'].isin(['Muy Alto Riesgo', 'Alto Riesgo'])]) 
    print(f"  Estudiantes en alto riesgo: {alto_riesgo}")

# 7. GENERAR CONTEO con matplotlib
print("\nGENERANDO GRAFICO DE CONTEO...")

# Crear figura
plt.figure(figsize=(10, 6))

# Crear gráfico de barras
categorias = conteo_reordenado.index
cantidades = conteo_reordenado.values
colores = ['#ff6961', '#ffb347', '#fdfd96', '#77dd77']

plt.bar(categorias, cantidades, color=colores)

# Personalizar gráfico
plt.title('Distribucion de Clasificacion de Riesgo de Asistencia', fontsize=14, fontweight='bold')
plt.xlabel('Categoria de Riesgo', fontsize=12)
plt.ylabel('Cantidad de Estudiantes', fontsize=12)

# Agregar valores en las barras
for i, v in enumerate(cantidades):
    plt.text(i, v + 0.5, str(v), ha='center', va='bottom', fontweight='bold')

# Rotar etiquetas del eje x para mejor legibilidad
plt.xticks(rotation=45, ha='right')

# Ajustar layout
plt.tight_layout()

# Guardar gráfico
plt.savefig('distribucion_riesgo_asistencia.png', dpi=300, bbox_inches='tight')
print("Grafico guardado como 'distribucion_riesgo_asistencia.png'")

# Mostrar gráfico
plt.show()

print("\n" + "=" * 50)
print("ANALISIS COMPLETADO")
print("=" * 50) 