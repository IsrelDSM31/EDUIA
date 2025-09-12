# 📱 Configurar Acceso Móvil - IAEDU1

## 🚀 **Configuración Rápida**

### **Paso 1: Obtener IP Local**
```bash
# Windows CMD:
ipconfig

# Busca "IPv4 Address" - ejemplo: 192.168.1.100
```

### **Paso 2: Iniciar Servidor con IP**
```bash
# En lugar de: php artisan serve
# Usa:
php artisan serve --host=0.0.0.0 --port=8000
```

### **Paso 3: Acceder desde Móvil**
- **URL:** `http://192.168.1.100:8000`
- **Reemplaza** 192.168.1.100 con tu IP real

## 📋 **Métodos de Acceso**

### **Método 1: Laravel Serve (Recomendado)**
```bash
# Terminal en tu computadora:
cd C:\xampp\htdocs\IAEDU1
php artisan serve --host=0.0.0.0 --port=8000
```

### **Método 2: XAMPP Apache**
1. **Configurar Apache:**
   - Edita: `C:\xampp\apache\conf\httpd.conf`
   - Busca: `Listen 80`
   - Cambia a: `Listen 0.0.0.0:80`

2. **Acceder via:**
   - `http://192.168.1.100/IAEDU1/public`

### **Método 3: ngrok (Acceso Externo)**
```bash
# Instalar ngrok
# Ejecutar:
ngrok http 8000

# Usar la URL que genera ngrok
```

## 📱 **Instalación PWA en Móvil**

### **Android (Chrome/Edge):**
1. Abre `http://192.168.1.100:8000`
2. Toca menú (⋮) → **"Instalar aplicación"**
3. Confirma instalación
4. La app aparecerá en pantalla de inicio

### **iOS (Safari):**
1. Abre `http://192.168.1.100:8000`
2. Toca botón compartir (□↑)
3. Selecciona **"Añadir a pantalla de inicio"**
4. Confirma instalación

## 🔧 **Solución de Problemas**

### **Problema: "No se puede acceder"**
**Solución:**
1. Verifica que estés en la misma red WiFi
2. Desactiva firewall temporalmente
3. Usa `--host=0.0.0.0` en el comando

### **Problema: "Conexión rechazada"**
**Solución:**
1. Verifica que el servidor esté corriendo
2. Usa la IP correcta
3. Verifica el puerto (8000)

### **Problema: "PWA no se instala"**
**Solución:**
1. Ve a: `http://192.168.1.100:8000/pwa-diagnostic`
2. Verifica que todos los criterios estén ✅
3. Genera íconos si faltan

## 🎯 **Comandos Útiles**

### **Iniciar Servidor:**
```bash
# Acceso local
php artisan serve

# Acceso desde móvil
php artisan serve --host=0.0.0.0 --port=8000

# Puerto específico
php artisan serve --host=0.0.0.0 --port=8080
```

### **Verificar IP:**
```bash
# Windows
ipconfig

# Linux/Mac
ifconfig
```

### **Probar Conexión:**
```bash
# Desde móvil, abre navegador y ve a:
http://TU_IP:8000

# Ejemplo:
http://192.168.1.100:8000
```

## 📊 **URLs Importantes**

| Función | URL |
|---------|-----|
| **App Principal** | `http://192.168.1.100:8000` |
| **Diagnóstico PWA** | `http://192.168.1.100:8000/pwa-diagnostic` |
| **Generar Íconos** | `http://192.168.1.100:8000/generate-all-icons` |
| **Test PWA** | `http://192.168.1.100:8000/pwa-test` |

## 🎉 **Resultado Final**

Después de la configuración:
- ✅ **App accesible desde móvil**
- ✅ **PWA instalable**
- ✅ **Funciona offline**
- ✅ **Ícono en pantalla de inicio**
- ✅ **Experiencia nativa**

## 🚀 **Pasos Rápidos:**

1. **Obtener IP:** `ipconfig` en CMD
2. **Iniciar servidor:** `php artisan serve --host=0.0.0.0 --port=8000`
3. **Acceder desde móvil:** `http://TU_IP:8000`
4. **Instalar PWA:** Menú → Instalar aplicación
5. **¡Listo!** Usar como app nativa 