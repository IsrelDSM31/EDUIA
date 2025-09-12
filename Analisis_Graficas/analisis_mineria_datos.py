import pandas as pd
import numpy as np

# -----------------------------
# CARGA DE DATOS
# -----------------------------
# Usar encoding para evitar problemas de caracteres
students = pd.read_csv('csv_bd/students.csv', encoding='utf-8')
groups = pd.read_csv('csv_bd/groups.csv', encoding='utf-8')
grades = pd.read_csv('csv_bd/grades.csv', encoding='utf-8')
subjects = pd.read_csv('csv_bd/subjects.csv', encoding='utf-8')

# -----------------------------
# MINERÍA DE DATOS
# -----------------------------
# 1. Número de alumnos por grupo
alumnos_por_grupo = students.groupby('group_id').size().reset_index(name='total_alumnos')

# 2. Promedio de calificaciones por grupo (minería de datos)
grades_students = pd.merge(grades, students[['id', 'group_id', 'nombre', 'matricula']], left_on='student_id', right_on='id', how='left')
promedio_por_grupo = grades_students.groupby('group_id')['promedio_final'].mean().reset_index(name='promedio_grupo')

# 3. Top 5 alumnos con mejor promedio (minería de datos)
mejores_alumnos = grades_students.sort_values('promedio_final', ascending=False).drop_duplicates(['student_id']).head(5)[['matricula', 'nombre', 'promedio_final', 'group_id']]

# Unir grades_students con subjects para obtener el nombre de la materia
grades_students = pd.merge(grades_students, subjects[['id', 'name']], left_on='subject_id', right_on='id', how='left', suffixes=('', '_materia'))
grades_students = grades_students.rename(columns={'name': 'materia'})

# 4. Alumnos reprobados por grupo y materia (minería de datos)
reprobados_por_grupo_materia = grades_students[grades_students['promedio_final'] < 7].groupby(['group_id', 'materia']).size().reset_index(name='reprobados')
# 4b. Alumnos reprobados por grupo (solo group_id, para comparacion)
reprobados_por_grupo = grades_students[grades_students['promedio_final'] < 7].groupby('group_id').size().reset_index(name='reprobados')

# 5. Promedio general de todos los alumnos (minería de datos)
promedio_general = grades_students['promedio_final'].mean()

# 6. Alumnos con promedio mayor a 8 (filtrado)
alumnos_destacados = grades_students[grades_students['promedio_final'] > 8][['matricula', 'nombre', 'promedio_final', 'group_id']]

# 7. Alumnos con promedio menor a 6 (filtrado, mostrando materia)
alumnos_en_riesgo = grades_students[grades_students['promedio_final'] < 6][['matricula', 'nombre', 'materia', 'promedio_final', 'group_id']]

# 8. Comparación: alumnos por grupo, promedio por grupo, reprobados por grupo (tabla de comparaciones)
comparacion = pd.merge(alumnos_por_grupo, promedio_por_grupo, on='group_id', how='outer')
comparacion = pd.merge(comparacion, reprobados_por_grupo, on='group_id', how='outer').fillna(0)

# 9. Comparación: promedio por grupo vs promedio general
comparacion['diferencia_vs_general'] = comparacion['promedio_grupo'] - promedio_general

# 10. Alumnos con el mismo nombre (minería de datos)
alumnos_nombres_repetidos = students[students.duplicated(['nombre'], keep=False)].sort_values('nombre')

# -----------------------------
# FILTRADO CON OPERADORES DIFERENTES Y VALUE_COUNTS
# -----------------------------
# 11. Alumnos con promedio diferente de 7 (no exactamente 7, mostrando materia)
alumnos_no_7 = grades_students[grades_students['promedio_final'] != 7][['matricula', 'nombre', 'materia', 'promedio_final', 'group_id']]

# 12. Alumnos con promedio mayor o igual a 6 y menor o igual a 8 (mostrando materia)
alumnos_6_a_8 = grades_students[(grades_students['promedio_final'] >= 6) & (grades_students['promedio_final'] <= 8)][['matricula', 'nombre', 'materia', 'promedio_final', 'group_id']]

# 13. Alumnos con promedio mayor que 5 y menor que 9 (mostrando materia)
alumnos_5_a_9 = grades_students[(grades_students['promedio_final'] > 5) & (grades_students['promedio_final'] < 9)][['matricula', 'nombre', 'materia', 'promedio_final', 'group_id']]

# 14. Conteo de alumnos por grupo usando value_counts
conteo_alumnos_grupo = students['group_id'].value_counts().reset_index()
conteo_alumnos_grupo.columns = ['group_id', 'total_alumnos']

# 15. Conteo de promedios por rangos usando value_counts
grades_students['rango_promedio'] = pd.cut(grades_students['promedio_final'], bins=[0, 6, 7, 8, 10], labels=['Reprobado', 'Aprobado', 'Bueno', 'Excelente'])
conteo_rangos = grades_students['rango_promedio'].value_counts()

# 16. Alumnos que NO están en el grupo 15 (diferente de)
alumnos_no_grupo_15 = students[students['group_id'] != 15]

# 17. Alumnos con promedio diferente de 0 y mayor que 0 (mostrando materia)
alumnos_con_calificacion = grades_students[(grades_students['promedio_final'] != 0) & (grades_students['promedio_final'] > 0)][['matricula', 'nombre', 'materia', 'promedio_final', 'group_id']]

# -----------------------------
# NUMPY: MANEJO DE NÚMEROS Y NUEVAS COLUMNAS
# -----------------------------
# 18. Añadir nueva columna: calificación redondeada usando numpy
grades_students['promedio_redondeado'] = np.round(grades_students['promedio_final'], 1)

# 19. Añadir nueva columna: estado académico usando numpy
grades_students['estado_academico'] = np.where(grades_students['promedio_final'] >= 7, 'Aprobado', 'Reprobado')

# 20. Añadir nueva columna: nivel de rendimiento usando numpy
grades_students['nivel_rendimiento'] = np.where(grades_students['promedio_final'] >= 9, 'Excelente',
                                               np.where(grades_students['promedio_final'] >= 8, 'Muy Bueno',
                                                       np.where(grades_students['promedio_final'] >= 7, 'Bueno',
                                                               np.where(grades_students['promedio_final'] >= 6, 'Regular', 'Bajo'))))

# 21. Añadir nueva columna: diferencia con el promedio general usando numpy
grades_students['diferencia_promedio'] = grades_students['promedio_final'] - promedio_general

# 22. Añadir nueva columna: calificación normalizada (0-100) usando numpy
grades_students['calificacion_100'] = np.round(grades_students['promedio_final'] * 10, 0)

# 23. Añadir nueva columna: año de nacimiento usando numpy (si existe birth_date)
if 'birth_date' in students.columns:
    students['año_nacimiento'] = pd.to_datetime(students['birth_date'], errors='coerce').dt.year
    students['edad_aproximada'] = 2024 - students['año_nacimiento']

# -----------------------------
# CONTEOS CON VALUE_COUNTS
# -----------------------------
# 24. Conteo de estados académicos
conteo_estados = grades_students['estado_academico'].value_counts()

# 25. Conteo de niveles de rendimiento
conteo_niveles = grades_students['nivel_rendimiento'].value_counts()

# 26. Conteo de promedios redondeados
conteo_redondeados = grades_students['promedio_redondeado'].value_counts().sort_index()

# 27. Conteo de calificaciones por rangos de 10 (0-10, 10-20, etc.)
grades_students['rango_calificacion'] = pd.cut(grades_students['calificacion_100'], bins=[0, 50, 60, 70, 80, 90, 100], labels=['0-50', '51-60', '61-70', '71-80', '81-90', '91-100'])
conteo_rangos_calificacion = grades_students['rango_calificacion'].value_counts()

# 28. Conteo de diferencias con el promedio (positivas/negativas)
grades_students['tipo_diferencia'] = np.where(grades_students['diferencia_promedio'] > 0, 'Arriba del promedio', 'Abajo del promedio')
conteo_diferencias = grades_students['tipo_diferencia'].value_counts()

# -----------------------------
# COMPARACIONES UTILIZANDO DECISIONES
# -----------------------------
# 29. ¿Quiénes aprobaron todas sus materias? (alumnos que no tienen ninguna materia reprobada)
aprobados_todas = grades_students.groupby('matricula').apply(lambda df: (df['promedio_final'] >= 7).all()).reset_index(name='aprobado_todas')
aprobados_todas = aprobados_todas[aprobados_todas['aprobado_todas']]
aprobados_todas = pd.merge(aprobados_todas, students[['matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'group_id']], on='matricula', how='left')

# 30. ¿Quiénes reprobaron al menos una materia? (decisión)
reprobo_alguna = grades_students.groupby('matricula').apply(lambda df: (df['promedio_final'] < 7).any()).reset_index(name='reprobo_alguna')
reprobo_alguna = reprobo_alguna[reprobo_alguna['reprobo_alguna']]
reprobo_alguna = pd.merge(reprobo_alguna, students[['matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'group_id']], on='matricula', how='left')

# 31. ¿Quiénes tienen al menos una materia con promedio excelente (>=9)?
tiene_excelente = grades_students.groupby('matricula').apply(lambda df: (df['promedio_final'] >= 9).any()).reset_index(name='tiene_excelente')
tiene_excelente = tiene_excelente[tiene_excelente['tiene_excelente']]
tiene_excelente = pd.merge(tiene_excelente, students[['matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'group_id']], on='matricula', how='left')

# 32. ¿Quiénes tienen todas sus materias en riesgo (<6)?
todas_en_riesgo = grades_students.groupby('matricula').apply(lambda df: (df['promedio_final'] < 6).all()).reset_index(name='todas_en_riesgo')
todas_en_riesgo = todas_en_riesgo[todas_en_riesgo['todas_en_riesgo']]
todas_en_riesgo = pd.merge(todas_en_riesgo, students[['matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'group_id']], on='matricula', how='left')

# 33. Clasificación de alumnos según su mejor materia (decisión)
def mejor_materia(df):
    idx = df['promedio_final'].idxmax()
    return df.loc[idx, ['materia', 'promedio_final']]
mejor_materia_alumno = grades_students.groupby('matricula').apply(mejor_materia).reset_index()
mejor_materia_alumno = pd.merge(mejor_materia_alumno, students[['matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'group_id']], on='matricula', how='left')

# 34. Clasificación de alumnos según su peor materia (decisión)
def peor_materia(df):
    idx = df['promedio_final'].idxmin()
    return df.loc[idx, ['materia', 'promedio_final']]
peor_materia_alumno = grades_students.groupby('matricula').apply(peor_materia).reset_index()
peor_materia_alumno = pd.merge(peor_materia_alumno, students[['matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'group_id']], on='matricula', how='left')

# -----------------------------
# RESULTADOS
# -----------------------------
print('--- Alumnos por grupo ---')
print(alumnos_por_grupo.to_string(index=False))

print('\n--- Promedio de calificaciones por grupo ---')
print(promedio_por_grupo.to_string(index=False))

print('\n--- Top 5 alumnos con mejor promedio ---')
print(mejores_alumnos.to_string(index=False))

print('\n--- Alumnos reprobados por grupo y materia ---')
print(reprobados_por_grupo_materia.to_string(index=False))

print('\n--- Promedio general de todos los alumnos ---')
print(promedio_general)

print('\n--- Alumnos con promedio mayor a 8 (destacados) ---')
print(alumnos_destacados.to_string(index=False))

print('\n--- Alumnos con promedio menor a 6 (en riesgo, mostrando materia) ---')
print(alumnos_en_riesgo.to_string(index=False))

print('\n--- Tabla de comparación por grupo ---')
print(comparacion.to_string(index=False))

print('\n--- Alumnos con el mismo nombre (posibles duplicados) ---')
print(alumnos_nombres_repetidos[['matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'group_id']].to_string(index=False))

print('\n--- Alumnos con promedio diferente de 7 (mostrando materia) ---')
print(alumnos_no_7.to_string(index=False))

print('\n--- Alumnos con promedio entre 6 y 8 (inclusive, mostrando materia) ---')
print(alumnos_6_a_8.to_string(index=False))

print('\n--- Alumnos con promedio entre 5 y 9 (exclusive, mostrando materia) ---')
print(alumnos_5_a_9.to_string(index=False))

print('\n--- Conteo de alumnos por grupo (value_counts) ---')
print(conteo_alumnos_grupo.to_string(index=False))

print('\n--- Conteo de promedios por rangos (value_counts) ---')
print(conteo_rangos.to_string())

print('\n--- Alumnos que NO están en el grupo 15 ---')
print(alumnos_no_grupo_15[['matricula', 'nombre', 'group_id']].to_string(index=False))

print('\n--- Alumnos con calificación válida (diferente de 0 y mayor que 0, mostrando materia) ---')
print(alumnos_con_calificacion.to_string(index=False))

print('\n--- Muestra de datos con nuevas columnas (numpy) ---')
print(grades_students[['matricula', 'nombre', 'promedio_final', 'promedio_redondeado', 'estado_academico', 'nivel_rendimiento', 'calificacion_100']].head(10).to_string(index=False))

print('\n--- Conteo de estados académicos (value_counts) ---')
print(conteo_estados.to_string())

print('\n--- Conteo de niveles de rendimiento (value_counts) ---')
print(conteo_niveles.to_string())

print('\n--- Conteo de promedios redondeados (value_counts) ---')
print(conteo_redondeados.to_string())

print('\n--- Conteo de calificaciones por rangos (value_counts) ---')
print(conteo_rangos_calificacion.to_string())

print('\n--- Conteo de diferencias con el promedio (value_counts) ---')
print(conteo_diferencias.to_string())

# -----------------------------
# RESULTADOS DE COMPARACIONES AVANZADAS
# -----------------------------
print('\n--- Alumnos que aprobaron todas sus materias ---')
print(aprobados_todas.to_string(index=False))

print('\n--- Alumnos que reprobaron al menos una materia ---')
print(reprobo_alguna.to_string(index=False))

print('\n--- Alumnos con al menos una materia excelente (>=9) ---')
print(tiene_excelente.to_string(index=False))

print('\n--- Alumnos con todas sus materias en riesgo (<6) ---')
print(todas_en_riesgo.to_string(index=False))

print('\n--- Mejor materia de cada alumno ---')
print(mejor_materia_alumno.to_string(index=False))

print('\n--- Peor materia de cada alumno ---')
print(peor_materia_alumno.to_string(index=False)) 