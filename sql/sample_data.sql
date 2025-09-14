-- Datos de ejemplo para el Sistema de Control de Servicios Digitales
USE sistema_id;

-- Insertar usuario administrador
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@sistemaid.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insertar tipos de servicios
INSERT INTO tipos_servicios (nombre, descripcion) VALUES 
('Dominio', 'Registro y renovación de nombres de dominio'),
('Hosting', 'Servicios de alojamiento web'),
('SSL', 'Certificados de seguridad SSL/TLS'),
('Sistema a Medida', 'Desarrollo y mantenimiento de sistemas personalizados'),
('Concepto Personalizado', 'Otros servicios configurables');

-- Insertar clientes de ejemplo
INSERT INTO clientes (nombre_razon_social, rfc, contacto, email, telefono, direccion) VALUES 
('Empresa ABC S.A. de C.V.', 'ABC123456789', 'Juan Pérez', 'contacto@empresaabc.com', '555-1234567', 'Av. Principal 123, Ciudad, Estado'),
('Comercial XYZ', 'XYZ987654321', 'María García', 'info@comercialxyz.com', '555-7654321', 'Calle Comercio 456, Ciudad, Estado'),
('Servicios Digitales MN', 'MN567890123', 'Carlos López', 'admin@serviciosmn.com', '555-9876543', 'Blvd. Tecnología 789, Ciudad, Estado');

-- Insertar servicios de ejemplo
INSERT INTO servicios (cliente_id, tipo_servicio_id, nombre, descripcion, monto, periodo_vencimiento, fecha_inicio, fecha_vencimiento, fecha_proximo_vencimiento) VALUES 
(1, 1, 'empresaabc.com', 'Dominio principal de la empresa', 350.00, 'anual', '2024-01-15', '2025-01-15', '2025-01-15'),
(1, 2, 'Hosting Empresarial', 'Plan de hosting para sitio web corporativo', 1200.00, 'anual', '2024-01-15', '2025-01-15', '2025-01-15'),
(1, 3, 'SSL Wildcard', 'Certificado SSL para subdominios', 800.00, 'anual', '2024-02-01', '2025-02-01', '2025-02-01'),
(2, 1, 'comercialxyz.com', 'Dominio de comercio electrónico', 350.00, 'anual', '2024-03-10', '2025-03-10', '2025-03-10'),
(2, 2, 'Hosting E-commerce', 'Hosting especializado para tienda online', 2400.00, 'anual', '2024-03-10', '2025-03-10', '2025-03-10'),
(3, 4, 'Sistema de Inventarios', 'Sistema personalizado de control de inventarios', 5000.00, 'semestral', '2024-06-01', '2024-12-01', '2025-06-01'),
(3, 2, 'Hosting para Sistema', 'Hosting dedicado para sistema personalizado', 3600.00, 'anual', '2024-06-01', '2025-06-01', '2025-06-01');

-- Insertar pagos de ejemplo
INSERT INTO pagos (servicio_id, monto, fecha_pago, fecha_vencimiento, estado, metodo_pago, referencia) VALUES 
(1, 350.00, '2024-01-15', '2025-01-15', 'pagado', 'Transferencia', 'REF001'),
(2, 1200.00, '2024-01-15', '2025-01-15', 'pagado', 'Tarjeta', 'REF002'),
(3, 800.00, '2024-02-01', '2025-02-01', 'pagado', 'PayPal', 'REF003'),
(4, 350.00, '2024-03-10', '2025-03-10', 'pagado', 'Stripe', 'REF004'),
(5, 2400.00, '2024-03-10', '2025-03-10', 'pagado', 'MercadoPago', 'REF005'),
(6, 5000.00, '2024-06-01', '2024-12-01', 'pagado', 'Transferencia', 'REF006'),
(7, 3600.00, '2024-06-01', '2025-06-01', 'pagado', 'Cheque', 'REF007');

-- Insertar configuraciones del sistema
INSERT INTO configuraciones (clave, valor, descripcion) VALUES 
('dias_alerta_vencimiento', '30,15,7,1', 'Días antes del vencimiento para enviar alertas'),
('email_notificaciones', 'noreply@sistemaid.com', 'Email para envío de notificaciones'),
('whatsapp_api_token', '', 'Token para API de WhatsApp'),
('moneda', 'MXN', 'Moneda del sistema'),
('formato_fecha', 'd/m/Y', 'Formato de fecha para mostrar'),
('timezone', 'America/Mexico_City', 'Zona horaria del sistema');

-- Insertar algunas notificaciones de ejemplo
INSERT INTO notificaciones (servicio_id, tipo, asunto, mensaje, destinatario, estado, fecha_programada) VALUES 
(6, 'email', 'Próximo vencimiento - Sistema de Inventarios', 'Su servicio Sistema de Inventarios vence el 01/12/2024', 'admin@serviciosmn.com', 'enviado', '2024-11-01 09:00:00'),
(1, 'email', 'Renovación próxima - empresaabc.com', 'Su dominio empresaabc.com vence el 15/01/2025', 'contacto@empresaabc.com', 'pendiente', '2024-12-15 09:00:00');