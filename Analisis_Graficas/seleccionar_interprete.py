#!/usr/bin/env python3
"""
Script para seleccionar automáticamente el intérprete correcto de Python.
Este script ayuda a VS Code a reconocer el entorno virtual.
"""

import os
import sys
import json
from pathlib import Path

def obtener_ruta_interprete():
    """Obtiene la ruta del intérprete de Python del entorno virtual."""
    venv_path = Path("venv/Scripts/python.exe")
    if venv_path.exists():
        return str(venv_path.absolute())
    else:
        return None

def crear_configuracion_vscode():
    """Crea la configuración específica para VS Code."""
    config = {
        "python.defaultInterpreterPath": obtener_ruta_interprete(),
        "python.analysis.extraPaths": [
            str(Path("venv/Lib/site-packages").absolute())
        ],
        "python.analysis.typeCheckingMode": "off",
        "python.analysis.diagnosticMode": "off",
        "python.analysis.reportMissingImports": "none",
        "python.analysis.reportUnusedImport": "none",
        "python.analysis.reportUnusedClass": "none",
        "python.analysis.reportUnusedFunction": "none",
        "python.analysis.reportUnusedVariable": "none",
        "python.linting.enabled": False,
        "python.terminal.activateEnvironment": True,
        "python.terminal.activateEnvInCurrentTerminal": True
    }
    
    # Crear directorio .vscode si no existe
    vscode_dir = Path(".vscode")
    vscode_dir.mkdir(exist_ok=True)
    
    # Escribir configuración
    with open(vscode_dir / "settings.json", "w", encoding="utf-8") as f:
        json.dump(config, f, indent=4)
    
    print("✅ Configuración de VS Code actualizada")

def crear_archivo_workspace():
    """Crea un archivo de workspace específico."""
    workspace_config = {
        "folders": [
            {
                "path": "."
            }
        ],
        "settings": {
            "python.defaultInterpreterPath": obtener_ruta_interprete(),
            "python.analysis.diagnosticMode": "off",
            "python.analysis.reportMissingImports": "none"
        }
    }
    
    with open("Analisis_Graficas.code-workspace", "w", encoding="utf-8") as f:
        json.dump(workspace_config, f, indent=4)
    
    print("✅ Archivo de workspace creado: Analisis_Graficas.code-workspace")

def verificar_entorno():
    """Verifica que el entorno virtual esté funcionando."""
    print("🔍 Verificando entorno virtual...")
    
    if hasattr(sys, 'real_prefix') or (hasattr(sys, 'base_prefix') and sys.base_prefix != sys.prefix):
        print("✅ Entorno virtual activado")
        print(f"📁 Intérprete: {sys.executable}")
        return True
    else:
        print("⚠️  No estás en un entorno virtual")
        print("💡 Ejecuta: .\\venv\\Scripts\\activate")
        return False

def main():
    """Función principal."""
    print("🚀 Configurando intérprete de Python para VS Code")
    print("=" * 60)
    
    # Verificar entorno
    if not verificar_entorno():
        return
    
    # Obtener ruta del intérprete
    interprete_path = obtener_ruta_interprete()
    if not interprete_path:
        print("❌ No se encontró el entorno virtual")
        return
    
    print(f"📁 Intérprete encontrado: {interprete_path}")
    
    # Crear configuraciones
    crear_configuracion_vscode()
    crear_archivo_workspace()
    
    print("\n" + "=" * 60)
    print("✅ Configuración completada")
    print("\n📋 Para aplicar los cambios:")
    print("1. Cierra VS Code completamente")
    print("2. Abre el archivo: Analisis_Graficas.code-workspace")
    print("3. O selecciona manualmente el intérprete:")
    print(f"   {interprete_path}")
    print("\n🔧 Comando para seleccionar intérprete en VS Code:")
    print("   Ctrl+Shift+P → 'Python: Select Interpreter' → Selecciona el intérprete del venv")

if __name__ == "__main__":
    main() 