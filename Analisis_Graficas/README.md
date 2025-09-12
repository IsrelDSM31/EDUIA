# Análisis de Datos y Gráficas

Este directorio contiene scripts para el análisis de minería de datos y generación de gráficas para el sistema educativo.

## Archivos Incluidos

- `analisis_mineria_datos.py` - Script principal de análisis de datos
- `graficas_analisis_datos.py` - Script para generar gráficas con Matplotlib
- `requirements.txt` - Dependencias necesarias
- `pyrightconfig.json` - Configuración para el editor

## Gráficas Generadas

El script `graficas_analisis_datos.py` genera las siguientes gráficas:

1. **Gráfica de líneas** - Evolución de promedios por grupo
2. **Gráfica de barras** - Número de alumnos por grupo
3. **Gráfica de barras horizontales** - Alumnos reprobados por grupo
4. **Gráfica de pastel** - Distribución de estados académicos
5. **Histograma** - Distribución de calificaciones
6. **Diagrama de dispersión** - Relación entre variables
7. **Gráfica de caja (boxplot)** - Distribución de calificaciones por grupo
8. **Gráfico de área** - Acumulado de alumnos por nivel
9. **Mapa de calor** - Matriz de correlación
10. **Comparación general** - Resumen de todas las métricas

## Instalación de Dependencias

### Opción 1: Usar el entorno virtual (Recomendado)
```bash
# Activar el entorno virtual
.\venv\Scripts\activate

# O usar el script de activación
activar_entorno.bat

# Las dependencias ya están instaladas en el entorno virtual
```

### Opción 2: Instalación global
```bash
pip install -r requirements.txt
```

## Ejecución

Para ejecutar el análisis de datos:
```bash
python analisis_mineria_datos.py
```

Para generar las gráficas:
```bash
python graficas_analisis_datos.py
```

## Solución de Problemas

### Errores de Importación en el Editor

Si ves errores de importación en el editor (Pylance), sigue estos pasos:

#### 🔧 Solución Automática (Recomendada):
```bash
python seleccionar_interprete.py
```

#### 🔧 Solución Manual:
1. **Activar entorno virtual**:
   ```bash
   .\venv\Scripts\activate
   ```

2. **Abrir workspace específico**:
   - Abre el archivo: `Analisis_Graficas.code-workspace`
   - O en VS Code: `Ctrl+Shift+P` → "Python: Select Interpreter"
   - Selecciona: `C:\xampp\htdocs\IAEDU1\Analisis_Graficas\venv\Scripts\python.exe`

3. **Reiniciar VS Code**:
   - Cierra completamente VS Code
   - Vuelve a abrir el workspace
   - Los errores deberían desaparecer completamente

#### 🔍 Verificación:
```bash
python -c "import matplotlib.pyplot as plt; import seaborn as sns; print('✅ OK')"
```

#### 📁 Archivos de Configuración Creados:
- `.vscode/settings.json` - Configuración VS Code
- `.vscode/launch.json` - Configuración de depuración
- `pyrightconfig.json` - Configuración Pylance
- `pyproject.toml` - Configuración del proyecto

### Rutas de Archivos

Los scripts buscan los archivos CSV en `../csv_bd/` (directorio padre). Asegúrate de que los archivos estén en la ubicación correcta:
- `../csv_bd/students.csv`
- `../csv_bd/groups.csv`
- `../csv_bd/grades.csv`
- `../csv_bd/subjects.csv`

## Notas

- Todas las gráficas se guardan como archivos PNG en el directorio actual
- El script maneja automáticamente los problemas de encoding UTF-8
- Las gráficas incluyen títulos, etiquetas y leyendas en español 