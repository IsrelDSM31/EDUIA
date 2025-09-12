import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
import warnings
warnings.filterwarnings('ignore')

# Configurar el estilo de las gráficas
plt.style.use('default')
sns.set_palette("husl")

# -----------------------------
# CARGA DE DATOS
# -----------------------------
students = pd.read_csv('../csv_bd/students.csv', encoding='utf-8')
groups = pd.read_csv('../csv_bd/groups.csv', encoding='utf-8')
grades = pd.read_csv('../csv_bd/grades.csv', encoding='utf-8')
subjects = pd.read_csv('../csv_bd/subjects.csv', encoding='utf-8')

# -----------------------------
# PROCESAMIENTO DE DATOS
# -----------------------------
# Unir grades con subjects para obtener el nombre de la materia
grades_subjects = pd.merge(grades, subjects[['id', 'name']], left_on='subject_id', right_on='id', how='left')
grades_subjects = grades_subjects.rename(columns={'name': 'materia'})

# 1. Distribución de Calificaciones por Materia (Barra Vertical)
promedio_materia = grades_subjects.groupby('materia')['promedio_final'].mean().reset_index()

# Colores diferentes para cada materia
subject_colors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444']  # Azul, Verde, Naranja, Púrpura, Rojo

plt.figure(figsize=(7, 5))
bars = plt.bar(promedio_materia['materia'], promedio_materia['promedio_final'], 
               color=subject_colors[:len(promedio_materia)], 
               edgecolor='white', linewidth=1)
plt.xlabel('Materia')
plt.ylabel('Promedio')
plt.title('Distribución de Calificaciones por Materia')
plt.legend()
plt.tight_layout()
plt.savefig('distribucion_calificaciones_materia.png', dpi=300, bbox_inches='tight')
plt.show()

# 2. Porcentaje de Alumnos por Estado (Pastel)
# Definir estados: Aprobado (>=7), Reprobado (<7), En riesgo (>=6 y <7)
def estado_alumno(prom):
    if prom >= 7:
        return 'Aprobado'
    elif prom >= 6:
        return 'En riesgo'
    else:
        return 'Reprobado'

grades_subjects['estado'] = grades_subjects['promedio_final'].apply(estado_alumno)
conteo_estados = grades_subjects['estado'].value_counts().reindex(['Aprobado', 'Reprobado', 'En riesgo']).fillna(0)

plt.figure(figsize=(6, 6))
colors = ['#10B981', '#F59E0B', '#EF4444']  # Verde, Naranja, Rojo
plt.pie(conteo_estados, labels=conteo_estados.index, autopct='%1.1f%%', colors=colors, startangle=90)
plt.title('Porcentaje de Alumnos por Estado')
plt.axis('equal')
plt.tight_layout()
plt.savefig('porcentaje_alumnos_estado.png', dpi=300, bbox_inches='tight')
plt.show()

# 3. Evolución del Rendimiento por Periodo (Línea)
# Se asume que hay una columna 'periodo' o 'mes' en grades o se simula para ejemplo
def obtener_mes(row):
    # Si existe columna 'mes', usarla. Si no, simular con IDs
    if 'mes' in grades_subjects.columns:
        return row['mes']
    elif 'periodo' in grades_subjects.columns:
        return row['periodo']
    else:
        # Simular meses para ejemplo
        return np.random.choice(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'], p=[0.15,0.15,0.15,0.15,0.2,0.2])

grades_subjects['mes'] = grades_subjects.apply(obtener_mes, axis=1)
promedio_mes = grades_subjects.groupby('mes')['promedio_final'].mean().reindex(['Ene','Feb','Mar','Abr','May','Jun'])

plt.figure(figsize=(7, 5))
plt.plot(promedio_mes.index, promedio_mes.values, marker='o', color='#17a2b8', linewidth=2, label='Promedio General')
plt.xlabel('Mes')
plt.ylabel('Promedio General')
plt.title('Evolución del Rendimiento por Periodo')
plt.legend()
plt.tight_layout()
plt.savefig('evolucion_rendimiento_periodo.png', dpi=300, bbox_inches='tight')
plt.show()

print("¡Gráficas generadas: distribución_calificaciones_materia.png, porcentaje_alumnos_estado.png, evolucion_rendimiento_periodo.png!") 