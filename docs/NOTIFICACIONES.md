# Sistema de Notificaciones - Sistema ID

## Descripción

El sistema de notificaciones implementa un sistema completo de alertas automáticas para vencimientos de servicios, con soporte para:

- ✅ **Notificaciones por Email** - SMTP y PHP mail()
- ✅ **Notificaciones por WhatsApp** - WhatsApp Business API
- ✅ **Recordatorios Programados** - 30, 15, 7, 1 días antes del vencimiento
- ✅ **Historial Completo** - Registro de todas las notificaciones enviadas
- ✅ **Procesamiento Automático** - Cron jobs para envío automático
- ✅ **Interfaz de Gestión** - Panel completo de administración

## Instalación

### 1. Requisitos

- PHP 7.4+
- MySQL 5.7+
- Extensiones PHP: `curl`, `openssl`, `mbstring`
- Cron jobs (para automatización)

### 2. Configuración de Base de Datos

Ejecutar el script de mejoras:

```sql
-- Aplicar mejoras a la base de datos
SOURCE sql/notifications_enhancement.sql;
```

### 3. Configuración Inicial

1. **Acceder a la configuración:**
   - Ir a `http://tu-dominio.com/notificaciones/configuracion`
   - Solo accesible para administradores

2. **Configurar Email:**
   - Método recomendado: SMTP
   - Configurar servidor, puerto, usuario y contraseña
   - Probar la configuración con el botón "Probar Email"

3. **Configurar WhatsApp (Opcional):**
   - Obtener credenciales de WhatsApp Business API
   - Configurar Phone Number ID y Access Token
   - Configurar webhook si se desea recibir confirmaciones

## Configuración de Email

### Método 1: SMTP (Recomendado)

```php
// Ejemplo para Gmail
Servidor SMTP: smtp.gmail.com
Puerto: 587
Seguridad: TLS
Usuario: tu-email@gmail.com
Contraseña: tu-app-password (no la contraseña normal)
```

### Método 2: PHP mail()

Solo requiere configurar el email remitente. Funciona si el servidor tiene `sendmail` configurado.

## Configuración de WhatsApp Business API

### 1. Obtener Credenciales

1. Crear una aplicación en Facebook Developers
2. Agregar el producto WhatsApp Business API
3. Obtener el Phone Number ID y Access Token
4. Configurar el webhook (opcional)

### 2. Configuración en el Sistema

```
Phone Number ID: 123456789012345
Access Token: EAAxxxxxxxxxx
Verify Token: mi_token_secreto_123
Webhook URL: https://tu-dominio.com/notificaciones/webhook
```

## Automatización con Cron Jobs

### Configuración Recomendada

```bash
# Editar crontab
crontab -e

# Agregar las siguientes líneas:

# Procesar notificaciones cada 5 minutos
0,5,10,15,20,25,30,35,40,45,50,55 * * * * /usr/bin/php /ruta/al/proyecto/cron_notifications.php procesar >/dev/null 2>&1

# Programar notificaciones diariamente a las 6:00 AM
0 6 * * * /usr/bin/php /ruta/al/proyecto/cron_notifications.php programar >/dev/null 2>&1

# Limpiar notificaciones antiguas semanalmente (domingos a las 2:00 AM)
0 2 * * 0 /usr/bin/php /ruta/al/proyecto/cron_notifications.php limpiar >/dev/null 2>&1

# Generar resumen diario a las 23:30
30 23 * * * /usr/bin/php /ruta/al/proyecto/cron_notifications.php resumen >/dev/null 2>&1
```

### Comandos Disponibles

```bash
# Procesar notificaciones pendientes
php cron_notifications.php procesar

# Programar notificaciones para servicios próximos a vencer
php cron_notifications.php programar

# Limpiar notificaciones antiguas
php cron_notifications.php limpiar

# Mostrar resumen del día
php cron_notifications.php resumen

# Modo de prueba (solo muestra lo que haría)
php cron_notifications.php test
```

## Intervalos de Recordatorio

Por defecto, las notificaciones se envían en los siguientes intervalos antes del vencimiento:

- **30 días** - Primera alerta
- **15 días** - Segunda alerta  
- **7 días** - Tercera alerta
- **1 día** - Alerta final

### Personalizar Intervalos

Editar en `config/config.php`:

```php
// Configuración de alertas de vencimiento (días antes)
define('ALERT_DAYS', [30, 15, 7, 1]);
```

## Uso del Sistema

### 1. Panel de Notificaciones

Acceder desde el menú principal → Notificaciones

**Funciones disponibles:**
- Ver historial de notificaciones
- Filtrar por tipo, estado, fechas
- Procesar notificaciones pendientes manualmente
- Programar notificaciones automáticas
- Ver próximas notificaciones

### 2. Configuración

En Notificaciones → Configuración:
- Configurar credenciales de email y WhatsApp
- Probar configuraciones
- Ver intervalos de recordatorio

### 3. Automatización

Las notificaciones se crean automáticamente cuando:
- Se registra un nuevo servicio
- Se actualiza un servicio existente
- Se ejecuta el comando de programación

## Plantillas de Mensajes

### Email
```
Estimado/a [CLIENTE],

Le recordamos que su servicio vence [TIEMPO]:

═══════════════════════════════════════
📋 Servicio: [SERVICIO]
💰 Monto: $[MONTO] MXN
📅 Fecha de vencimiento: [FECHA]
═══════════════════════════════════════

Para renovar su servicio, contáctenos lo antes posible.

Atentamente,
Sistema ID
```

### WhatsApp
```
🔔 *RECORDATORIO DE VENCIMIENTO*

Hola [CLIENTE],

Su servicio *[SERVICIO]* vence [TIEMPO].

💰 *Monto:* $[MONTO]
📅 *Vencimiento:* [FECHA]

Contáctenos para renovar. ¡Gracias! 🙏
```

## Monitoreo y Logs

### Ubicación de Logs
```
logs/cron_notifications.log
```

### Ver Logs en Tiempo Real
```bash
tail -f logs/cron_notifications.log
```

### Estadísticas

En el panel de notificaciones se muestran:
- Total de notificaciones enviadas
- Notificaciones pendientes
- Notificaciones fallidas
- Distribución por tipo (Email/WhatsApp)

## Solución de Problemas

### Notificaciones No Se Envían

1. **Verificar configuración:**
   - Ir a Configuración de Notificaciones
   - Probar email y WhatsApp

2. **Verificar cron jobs:**
   ```bash
   # Ver si los cron jobs están configurados
   crontab -l
   
   # Ver logs del cron
   tail -f logs/cron_notifications.log
   ```

3. **Verificar base de datos:**
   ```sql
   -- Ver notificaciones pendientes
   SELECT * FROM notificaciones WHERE estado = 'pendiente';
   
   -- Ver errores recientes
   SELECT * FROM logs WHERE accion LIKE '%notificacion%' ORDER BY fecha_creacion DESC LIMIT 10;
   ```

### Error de Conexión SMTP

- Verificar credenciales
- Verificar que el puerto esté abierto
- Para Gmail, usar contraseña de aplicación
- Verificar configuración de firewall

### Error de WhatsApp API

- Verificar que el token sea válido
- Verificar que el Phone Number ID sea correcto
- Verificar que el webhook esté configurado (opcional)

## Pruebas

### Ejecutar Pruebas Automáticas
```bash
php test_notifications.php
```

### Prueba Manual
1. Crear un servicio que venza en 1-30 días
2. Ejecutar: `php cron_notifications.php programar`
3. Ejecutar: `php cron_notifications.php procesar`
4. Verificar que las notificaciones se enviaron

## Seguridad

- Las credenciales se almacenan encriptadas en la base de datos
- El webhook de WhatsApp requiere token de verificación
- Solo administradores pueden acceder a la configuración
- Los logs no contienen información sensible

## Soporte

Para problemas o preguntas:
- Revisar este documento
- Verificar logs del sistema
- Ejecutar el script de pruebas
- Contactar al desarrollador

---

**Desarrollado para Sistema ID v1.0.0**