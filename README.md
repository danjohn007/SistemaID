# Sistema ID - Control de Servicios Digitales

Un sistema completo de gestión y control de vencimientos para servicios digitales, desarrollado en PHP puro con arquitectura MVC, MySQL y Bootstrap 5.

## 📋 Descripción

Este proyecto es un **sistema online de control de vencimientos y cobros periódicos** para servicios digitales como:
- Dominios y Hosting
- Certificados SSL
- Sistemas a la medida
- Otros conceptos personalizables

El sistema permite configurar vencimientos de forma **dinámica** (mensual, trimestral, semestral y anual), con vencimiento anual como valor por defecto.

## 🚀 Características Principales

- ✅ **Gestión de clientes** con múltiples servicios
- ✅ **Registro de servicios** con vencimientos dinámicos (Mensual, Trimestral, Semestral, Anual)
- ✅ **Conceptos adicionales** configurables para cobro periódico
- ✅ **Control de pagos** recurrentes con estados: Pagado, Pendiente, Por vencer, Vencido
- ✅ **Dashboard administrativo** con métricas de ingresos proyectados y vencimientos
- ✅ **Sistema de alertas** para servicios próximos a vencer
- ✅ **Interfaz responsiva** con Bootstrap 5
- ✅ **Autenticación segura** con roles (Admin, Cliente)
- ✅ **URLs amigables** y estructura MVC
- 🔄 Alertas y notificaciones automáticas (correo y WhatsApp API) - *En desarrollo*
- 🔄 Reportes exportables (Excel, PDF, CSV) - *En desarrollo*

## 💻 Tecnologías Utilizadas

- **Backend:** PHP 7+ (puro, sin framework)
- **Base de Datos:** MySQL 5.7
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Autenticación:** Sesiones PHP con password_hash()
- **Gráficas:** Chart.js
- **Iconos:** Font Awesome 6
- **Arquitectura:** MVC (Modelo-Vista-Controlador)

## 📦 Instalación

### Requisitos Previos

- PHP 7.0 o superior
- MySQL 5.7 o superior
- Servidor web Apache con mod_rewrite
- Extensiones PHP: PDO, PDO_MySQL, JSON, Sessions

### Pasos de Instalación

1. **Clonar o descargar el proyecto**
   ```bash
   git clone https://github.com/danjohn007/SistemaID.git
   cd SistemaID
   ```

2. **Configurar la base de datos**
   - Crear una base de datos MySQL llamada `sistema_id`
   - Importar el esquema: `sql/schema.sql`
   - Importar los datos de ejemplo: `sql/sample_data.sql`

3. **Configurar la conexión a la base de datos**
   Editar el archivo `config/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sistema_id');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   ```

4. **Configurar permisos**
   ```bash
   chmod 755 assets/
   chmod 755 exports/
   # Asegurar que el servidor web pueda escribir en estos directorios
   ```

5. **Configurar Apache**
   - Asegurar que mod_rewrite esté habilitado
   - El archivo `.htaccess` ya está configurado para URLs amigables

6. **Probar la instalación**
   - Acceder a `http://tu-dominio.com/test` para verificar la instalación
   - Login con las credenciales por defecto:
     - **Email:** admin@sistemaid.com
     - **Password:** password

## 🗂️ Estructura del Proyecto

```
SistemaID/
├── assets/                 # Archivos estáticos (CSS, JS, imágenes)
│   ├── css/
│   ├── js/
│   └── images/
├── config/                 # Configuraciones del sistema
│   ├── config.php         # Configuración principal
│   └── database.php       # Clase de conexión a BD
├── controllers/           # Controladores MVC
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── ClientesController.php
│   └── ...
├── models/                # Modelos de datos
│   ├── Usuario.php
│   ├── Cliente.php
│   ├── Servicio.php
│   └── ...
├── views/                 # Vistas del sistema
│   ├── layout/           # Plantillas base
│   ├── auth/             # Autenticación
│   ├── dashboard/        # Dashboard
│   ├── clients/          # Gestión de clientes
│   └── ...
├── sql/                   # Scripts de base de datos
│   ├── schema.sql        # Esquema de la BD
│   └── sample_data.sql   # Datos de ejemplo
├── .htaccess             # Configuración Apache
├── index.php             # Punto de entrada principal
├── test_connection.php   # Test de conexión y configuración
└── README.md            # Este archivo
```

## 🎯 Uso del Sistema

### Acceso Inicial

1. **Acceder al sistema:** `http://tu-dominio.com`
2. **Login con credenciales por defecto:**
   - Email: `admin@sistemaid.com`
   - Password: `password`

### Funcionalidades Principales

#### 1. **Dashboard**
- Vista general de estadísticas
- Servicios próximos a vencer
- Servicios vencidos
- Gráficas de ingresos y tipos de servicios

#### 2. **Gestión de Clientes**
- Alta, edición y consulta de clientes
- Información completa: nombre/razón social, RFC, contacto, email, teléfono
- Historial de servicios por cliente

#### 3. **Gestión de Servicios**
- Registro de servicios vinculados a clientes
- Configuración de períodos de vencimiento:
  - **Mensual:** Renovación cada mes
  - **Trimestral:** Renovación cada 3 meses
  - **Semestral:** Renovación cada 6 meses
  - **Anual:** Renovación cada año (por defecto)
- Cálculo automático de fechas de vencimiento
- Estados: Activo, Vencido, Cancelado, Suspendido

#### 4. **Control de Pagos**
- Registro de pagos manuales
- Renovación automática de servicios al registrar pago
- Estados: Pagado, Pendiente, Por vencer, Vencido

## ⚙️ Configuración

### Configuraciones Principales

El archivo `config/config.php` contiene las configuraciones principales:

- **Base de datos:** Credenciales y configuración de conexión
- **URL base:** Detección automática de la URL del sistema
- **Sesiones:** Tiempo de vida de las sesiones
- **Alertas:** Días antes del vencimiento para enviar alertas
- **Zona horaria:** Configuración de fecha y hora

### Tipos de Servicios

El sistema viene preconfigurado con los siguientes tipos de servicios:
- Dominio
- Hosting
- SSL
- Sistema a Medida
- Concepto Personalizado

Puede agregar más tipos directamente en la base de datos.

## 🔧 Test de Conexión

Para verificar que el sistema está correctamente configurado:

1. Acceder a `http://tu-dominio.com/test`
2. Verificar que todos los elementos estén en verde:
   - ✅ Información del sistema
   - ✅ Conexión a base de datos
   - ✅ Extensiones PHP requeridas
   - ✅ Permisos de escritura
   - ✅ URLs del sistema

## 📊 Base de Datos

### Tablas Principales

- **usuarios:** Usuarios del sistema (admin, clientes)
- **clientes:** Información de clientes
- **tipos_servicios:** Catálogo de tipos de servicios
- **servicios:** Servicios contratados por clientes
- **pagos:** Historial de pagos y renovaciones
- **notificaciones:** Sistema de notificaciones
- **configuraciones:** Configuraciones del sistema
- **logs:** Registro de actividades

### Credenciales por Defecto

- **Usuario:** admin@sistemaid.com
- **Contraseña:** password (Cambiar después del primer acceso)

## 🛡️ Seguridad

- Contraseñas hasheadas con `password_hash()`
- Validación de sesiones
- Protección contra inyección SQL con PDO
- Sanitización de entradas de usuario
- Archivos de configuración protegidos via `.htaccess`
- Headers de seguridad configurados

## 🚧 Roadmap

### Próximas Funcionalidades

- [ ] **Notificaciones automáticas**
  - Integración con APIs de email
  - Integración con WhatsApp Business API
  - Programación de recordatorios

- [ ] **Sistema de reportes**
  - Exportación a Excel, PDF, CSV
  - Reportes de ingresos proyectados
  - Análisis de vencimientos

- [ ] **Facturación electrónica**
  - Integración con CFDI (México)
  - Generación automática de facturas

- [ ] **Pasarelas de pago**
  - Stripe
  - PayPal
  - MercadoPago

- [ ] **API REST**
  - Endpoints para integración externa
  - Webhooks para notificaciones

- [ ] **App móvil**
  - Aplicación móvil nativa
  - Notificaciones push

## 🐛 Solución de Problemas

### Problemas Comunes

1. **Error de conexión a base de datos**
   - Verificar credenciales en `config/config.php`
   - Asegurar que MySQL esté ejecutándose
   - Verificar permisos del usuario de BD

2. **URLs no funcionan (404)**
   - Verificar que mod_rewrite esté habilitado
   - Comprobar que `.htaccess` esté en el directorio raíz
   - Verificar configuración del Virtual Host

3. **Problemas de permisos**
   - Establecer permisos 755 para directorios
   - Establecer permisos 644 para archivos
   - Verificar que el usuario web tenga permisos de escritura

## 📞 Soporte

Para soporte o preguntas sobre el sistema:
- **GitHub Issues:** [https://github.com/danjohn007/SistemaID/issues](https://github.com/danjohn007/SistemaID/issues)
- **Email:** [Contactar al desarrollador](mailto:danjohn007@example.com)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 🏆 Créditos

Desarrollado por **danjohn007** - Sistema ID v1.0.0

---

**¡Gracias por usar Sistema ID!** 🚀
