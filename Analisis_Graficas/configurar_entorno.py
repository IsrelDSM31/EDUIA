#!/usr/bin/env python3
"""
Script para configurar y verificar el entorno de análisis y gráficas.
Este script ayuda a solucionar errores de importación en el editor.
"""

import sys
import os
import subprocess
from pathlib import Path

def verificar_librerias():
    """Verifica que todas las librerías necesarias estén instaladas."""
    librerias = ['pandas', 'numpy', 'matplotlib', 'seaborn']
    faltantes = []
    
    print("🔍 Verificando librerías...")
    
    for lib in librerias:
        try:
            __import__(lib)
            print(f"✅ {lib} - OK")
        except ImportError:
            print(f"❌ {lib} - FALTANTE")
            faltantes.append(lib)
    
    return faltantes

def verificar_entorno_virtual():
    """Verifica si estamos en un entorno virtual."""
    in_venv = hasattr(sys, 'real_prefix') or (hasattr(sys, 'base_prefix') and sys.base_prefix != sys.prefix)
    
    if in_venv:
        print("✅ Entorno virtual activado")
        return True
    else:
        print("⚠️  No estás en un entorno virtual")
        return False

def activar_entorno_virtual():
    """Intenta activar el entorno virtual."""
    venv_path = Path("venv/Scripts/activate")
    
    if venv_path.exists():
        print("🔄 Activando entorno virtual...")
        if os.name == 'nt':  # Windows
            os.system("venv\\Scripts\\activate")
        else:  # Unix/Linux
            os.system("source venv/bin/activate")
        return True
    else:
        print("❌ No se encontró el entorno virtual")
        return False

def crear_archivo_configuracion():
    """Crea un archivo de configuración para VS Code."""
    config_content = '''{
    "python.defaultInterpreterPath": "./venv/Scripts/python.exe",
    "python.analysis.extraPaths": ["./venv/Lib/site-packages"],
    "python.analysis.typeCheckingMode": "off",
    "python.analysis.reportMissingImports": "none",
    "python.analysis.reportUnusedImport": "none",
    "python.analysis.reportUnusedClass": "none",
    "python.analysis.reportUnusedFunction": "none",
    "python.analysis.reportUnusedVariable": "none"
}'''
    
    config_path = Path(".vscode/settings.json")
    config_path.parent.mkdir(exist_ok=True)
    
    with open(config_path, 'w', encoding='utf-8') as f:
        f.write(config_content)
    
    print("✅ Archivo de configuración VS Code creado")

def main():
    """Función principal."""
    print("🚀 Configurando entorno de Análisis y Gráficas")
    print("=" * 50)
    
    # Verificar entorno virtual
    if not verificar_entorno_virtual():
        if not activar_entorno_virtual():
            print("\n💡 Para solucionar errores de importación:")
            print("1. Ejecuta: .\\venv\\Scripts\\activate")
            print("2. En VS Code, selecciona el intérprete del entorno virtual")
            print("3. Reinicia VS Code")
            return
    
    # Verificar librerías
    faltantes = verificar_librerias()
    
    if faltantes:
        print(f"\n📦 Instalando librerías faltantes: {', '.join(faltantes)}")
        subprocess.run([sys.executable, "-m", "pip", "install"] + faltantes)
    
    # Crear configuración
    crear_archivo_configuracion()
    
    print("\n" + "=" * 50)
    print("✅ Configuración completada")
    print("\n📋 Para eliminar errores del editor:")
    print("1. Reinicia VS Code")
    print("2. Selecciona el intérprete: ./venv/Scripts/python.exe")
    print("3. Los errores de importación deberían desaparecer")
    
    # Verificación final
    print("\n🔍 Verificación final...")
    try:
        import matplotlib.pyplot as plt
        import seaborn as sns
        import pandas as pd
        import numpy as np
        print("✅ Todas las librerías funcionan correctamente")
    except ImportError as e:
        print(f"❌ Error en verificación final: {e}")

if __name__ == "__main__":
    main() 