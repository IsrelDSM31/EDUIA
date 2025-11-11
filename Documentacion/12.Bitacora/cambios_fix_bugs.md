# Registro de cambios de la rama `fix-bugs`

**Fecha de actualización:** 09/11/2025  
**Responsable:** Equipo de desarrollo IAEDU1

## Ajustes principales
- Recuperamos la base de datos SQLite dañada y añadimos scripts de respaldo y reparación (`fix_sqlite_direct.php`, migraciones ejecutadas nuevamente).
- Completamos la carga inicial de datos configurando `DatabaseSeeder.php` y creando `CompleteDataSeeder.php` para poblar todas las tablas necesarias.
- Automatizamos el arranque del proyecto sin XAMPP mediante los scripts `start-server.bat`, `start-server-red.bat`, `start-full.bat`, `start-dev.bat` y `start-mobile-server.bat`.
- Corregimos la compilación de assets configurando `vite.config.js`, actualizando `resources/views/app.blade.php` y creando `fix-assets-production.php`.
- Ajustamos el Service Worker (`public/sw.js`) para evitar que intercepte peticiones POST/PUT/DELETE y actualizamos el registro en `app.blade.php`.
- Mejoramos el módulo de estudiantes corrigiendo `StudentController.php` y `StudentsModule.jsx` para mostrar registros inmediatamente después de crearlos.
- Resolvimos errores 419 de CSRF con middleware actualizado (`VerifyCsrfToken.php`, `EncryptCookies.php`, `HandleInertiaRequests.php`) y mejoras en `resources/js/bootstrap.js`.
- Restablecimos la navegación de alertas agregando la ruta `alerts.show`, el método `show()` en `AlertController.php` y la página `resources/js/Pages/Alerts/Show.jsx`.
- Refinamos estilos globales habilitando Tailwind/PostCSS (`postcss.config.js`, `resources/css/app.css`) y actualizando componentes de layout e interfaz (`AuthenticatedLayout.jsx`, `GuestLayout.jsx`, `NavLink.jsx`, `Login.jsx`).

## Próximos pasos sugeridos
- Verificar periódicamente la integridad de `database.sqlite` y mantener respaldos actualizados.
- Ejecutar compilaciones de assets en cada despliegue para asegurar la coherencia con Vite.
- Mantener el registro de cambios sincronizado con cada corrección futura en la rama `fix-bugs`.


