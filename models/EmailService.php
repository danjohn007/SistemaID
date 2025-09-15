<?php
/**
 * Servicio de Email
 * Manejo de envío de correos electrónicos usando PHP mail() y SMTP
 */
class EmailService {
    private $config;
    
    public function __construct() {
        // Configuración por defecto
        $this->config = [
            'method' => 'mail', // 'mail' o 'smtp'
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_secure' => 'tls', // 'tls' o 'ssl'
            'from_email' => MAIL_FROM,
            'from_name' => MAIL_FROM_NAME,
            'reply_to' => MAIL_FROM,
            'charset' => 'UTF-8'
        ];
        
        // Cargar configuración desde base de datos si existe
        $this->loadConfig();
    }
    
    /**
     * Cargar configuración desde base de datos
     */
    private function loadConfig() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT clave, valor FROM configuraciones WHERE clave LIKE 'email_%'");
            $configuraciones = $stmt->fetchAll();
            
            foreach ($configuraciones as $config) {
                $key = str_replace('email_', '', $config['clave']);
                if (isset($this->config[$key])) {
                    $this->config[$key] = $config['valor'];
                }
            }
        } catch (Exception $e) {
            // Si no existe la tabla o hay error, usar configuración por defecto
        }
    }
    
    /**
     * Enviar email
     */
    public function enviar($destinatario, $asunto, $mensaje, $esHtml = true) {
        try {
            if ($this->config['method'] === 'smtp') {
                return $this->enviarSMTP($destinatario, $asunto, $mensaje, $esHtml);
            } else {
                return $this->enviarMail($destinatario, $asunto, $mensaje, $esHtml);
            }
        } catch (Exception $e) {
            error_log("Error enviando email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar usando función mail() de PHP
     */
    private function enviarMail($destinatario, $asunto, $mensaje, $esHtml) {
        $headers = [];
        $headers[] = "From: {$this->config['from_name']} <{$this->config['from_email']}>";
        $headers[] = "Reply-To: {$this->config['reply_to']}";
        $headers[] = "Return-Path: {$this->config['from_email']}";
        $headers[] = "X-Mailer: Sistema ID v" . SITE_VERSION;
        $headers[] = "MIME-Version: 1.0";
        
        if ($esHtml) {
            $headers[] = "Content-Type: text/html; charset={$this->config['charset']}";
            $mensaje = $this->convertirAHtml($mensaje);
        } else {
            $headers[] = "Content-Type: text/plain; charset={$this->config['charset']}";
        }
        
        $headers[] = "Content-Transfer-Encoding: 8bit";
        
        // Codificar asunto si contiene caracteres especiales
        $asunto = '=?UTF-8?B?' . base64_encode($asunto) . '?=';
        
        return mail($destinatario, $asunto, $mensaje, implode("\r\n", $headers));
    }
    
    /**
     * Enviar usando SMTP
     */
    private function enviarSMTP($destinatario, $asunto, $mensaje, $esHtml) {
        // Validar configuración SMTP
        if (empty($this->config['smtp_host']) || empty($this->config['smtp_username'])) {
            throw new Exception("Configuración SMTP incompleta");
        }
        
        // Crear socket de conexión
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        $protocol = $this->config['smtp_secure'] === 'ssl' ? 'ssl://' : '';
        $socket = stream_socket_client(
            $protocol . $this->config['smtp_host'] . ':' . $this->config['smtp_port'],
            $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context
        );
        
        if (!$socket) {
            throw new Exception("No se pudo conectar al servidor SMTP: $errstr ($errno)");
        }
        
        // Leer respuesta inicial
        $this->leerRespuestaSMTP($socket);
        
        // HELO/EHLO
        $this->enviarComandoSMTP($socket, "EHLO " . $_SERVER['SERVER_NAME']);
        
        // STARTTLS si es necesario
        if ($this->config['smtp_secure'] === 'tls') {
            $this->enviarComandoSMTP($socket, "STARTTLS");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->enviarComandoSMTP($socket, "EHLO " . $_SERVER['SERVER_NAME']);
        }
        
        // Autenticación
        $this->enviarComandoSMTP($socket, "AUTH LOGIN");
        $this->enviarComandoSMTP($socket, base64_encode($this->config['smtp_username']));
        $this->enviarComandoSMTP($socket, base64_encode($this->config['smtp_password']));
        
        // Envío del mensaje
        $this->enviarComandoSMTP($socket, "MAIL FROM: <{$this->config['from_email']}>");
        $this->enviarComandoSMTP($socket, "RCPT TO: <$destinatario>");
        $this->enviarComandoSMTP($socket, "DATA");
        
        // Construir mensaje completo
        $mensajeCompleto = $this->construirMensajeSMTP($destinatario, $asunto, $mensaje, $esHtml);
        
        fwrite($socket, $mensajeCompleto . "\r\n.\r\n");
        $this->leerRespuestaSMTP($socket);
        
        // Cerrar conexión
        $this->enviarComandoSMTP($socket, "QUIT");
        fclose($socket);
        
        return true;
    }
    
    /**
     * Enviar comando SMTP y leer respuesta
     */
    private function enviarComandoSMTP($socket, $comando) {
        fwrite($socket, $comando . "\r\n");
        $respuesta = $this->leerRespuestaSMTP($socket);
        
        $codigo = intval(substr($respuesta, 0, 3));
        if ($codigo >= 400) {
            throw new Exception("Error SMTP: $respuesta");
        }
        
        return $respuesta;
    }
    
    /**
     * Leer respuesta del servidor SMTP
     */
    private function leerRespuestaSMTP($socket) {
        $respuesta = '';
        while ($line = fgets($socket, 515)) {
            $respuesta .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $respuesta;
    }
    
    /**
     * Construir mensaje completo para SMTP
     */
    private function construirMensajeSMTP($destinatario, $asunto, $mensaje, $esHtml) {
        $headers = [];
        $headers[] = "Date: " . date('r');
        $headers[] = "From: {$this->config['from_name']} <{$this->config['from_email']}>";
        $headers[] = "To: $destinatario";
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($asunto) . "?=";
        $headers[] = "Reply-To: {$this->config['reply_to']}";
        $headers[] = "X-Mailer: Sistema ID v" . SITE_VERSION;
        $headers[] = "MIME-Version: 1.0";
        
        if ($esHtml) {
            $headers[] = "Content-Type: text/html; charset={$this->config['charset']}";
            $mensaje = $this->convertirAHtml($mensaje);
        } else {
            $headers[] = "Content-Type: text/plain; charset={$this->config['charset']}";
        }
        
        $headers[] = "Content-Transfer-Encoding: 8bit";
        $headers[] = "";
        
        return implode("\r\n", $headers) . "\r\n" . $mensaje;
    }
    
    /**
     * Convertir texto plano a HTML básico
     */
    private function convertirAHtml($texto) {
        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . SITE_NAME . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .highlight { background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>' . SITE_NAME . '</h2>
        </div>
        <div class="content">
            ' . nl2br(htmlspecialchars($texto)) . '
        </div>
        <div class="footer">
            <p>Este es un mensaje automático del ' . SITE_NAME . '</p>
            <p>No responda a este correo.</p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Validar dirección de email
     */
    public function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Actualizar configuración
     */
    public function actualizarConfig($nuevaConfig) {
        try {
            $db = Database::getInstance()->getConnection();
            
            foreach ($nuevaConfig as $key => $value) {
                if (isset($this->config[$key])) {
                    $clave = 'email_' . $key;
                    $stmt = $db->prepare("
                        INSERT INTO configuraciones (clave, valor, descripcion) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE valor = ?, fecha_actualizacion = NOW()
                    ");
                    $descripcion = $this->getDescripcionConfig($key);
                    $stmt->execute([$clave, $value, $descripcion, $value]);
                    
                    $this->config[$key] = $value;
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error actualizando configuración de email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener descripción de configuración
     */
    private function getDescripcionConfig($key) {
        $descripciones = [
            'method' => 'Método de envío de email (mail o smtp)',
            'smtp_host' => 'Servidor SMTP',
            'smtp_port' => 'Puerto SMTP',
            'smtp_username' => 'Usuario SMTP',
            'smtp_password' => 'Contraseña SMTP',
            'smtp_secure' => 'Seguridad SMTP (tls o ssl)',
            'from_email' => 'Email remitente',
            'from_name' => 'Nombre remitente',
            'reply_to' => 'Email para respuestas'
        ];
        
        return $descripciones[$key] ?? '';
    }
    
    /**
     * Probar configuración de email
     */
    public function probarConfiguracion($emailDestino) {
        $asunto = "Prueba de configuración - " . SITE_NAME;
        $mensaje = "Este es un mensaje de prueba para verificar la configuración de email.\n\n";
        $mensaje .= "Si recibe este mensaje, la configuración es correcta.\n\n";
        $mensaje .= "Fecha de envío: " . date('d/m/Y H:i:s');
        
        return $this->enviar($emailDestino, $asunto, $mensaje);
    }
}