<?php
/**
 * Servicio de WhatsApp
 * Integración con WhatsApp Business API
 */
class WhatsAppService {
    private $config;
    
    public function __construct() {
        // Configuración por defecto
        $this->config = [
            'api_url' => 'https://graph.facebook.com/v18.0/',
            'phone_number_id' => '',
            'access_token' => WHATSAPP_API_TOKEN,
            'verify_token' => '',
            'webhook_url' => '',
            'timeout' => 30
        ];
        
        // Cargar configuración desde base de datos
        $this->loadConfig();
    }
    
    /**
     * Cargar configuración desde base de datos
     */
    private function loadConfig() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT clave, valor FROM configuraciones WHERE clave LIKE 'whatsapp_%'");
            $configuraciones = $stmt->fetchAll();
            
            foreach ($configuraciones as $config) {
                $key = str_replace('whatsapp_', '', $config['clave']);
                if (isset($this->config[$key])) {
                    $this->config[$key] = $config['valor'];
                }
            }
        } catch (Exception $e) {
            // Si no existe la tabla o hay error, usar configuración por defecto
        }
    }
    
    /**
     * Enviar mensaje de texto
     */
    public function enviarMensaje($numeroDestino, $mensaje) {
        if (empty($this->config['access_token']) || empty($this->config['phone_number_id'])) {
            error_log("WhatsApp: Configuración incompleta");
            return false;
        }
        
        $numeroDestino = $this->limpiarNumero($numeroDestino);
        
        $url = $this->config['api_url'] . $this->config['phone_number_id'] . '/messages';
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $numeroDestino,
            'type' => 'text',
            'text' => [
                'body' => $mensaje
            ]
        ];
        
        return $this->enviarPeticionAPI($url, $data);
    }
    
    /**
     * Enviar mensaje con plantilla
     */
    public function enviarPlantilla($numeroDestino, $nombrePlantilla, $parametros = []) {
        if (empty($this->config['access_token']) || empty($this->config['phone_number_id'])) {
            return false;
        }
        
        $numeroDestino = $this->limpiarNumero($numeroDestino);
        
        $url = $this->config['api_url'] . $this->config['phone_number_id'] . '/messages';
        
        $componentes = [];
        if (!empty($parametros)) {
            $componentes[] = [
                'type' => 'body',
                'parameters' => array_map(function($param) {
                    return ['type' => 'text', 'text' => $param];
                }, $parametros)
            ];
        }
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $numeroDestino,
            'type' => 'template',
            'template' => [
                'name' => $nombrePlantilla,
                'language' => ['code' => 'es_MX']
            ]
        ];
        
        if (!empty($componentes)) {
            $data['template']['components'] = $componentes;
        }
        
        return $this->enviarPeticionAPI($url, $data);
    }
    
    /**
     * Enviar petición a la API
     */
    private function enviarPeticionAPI($url, $data) {
        $headers = [
            'Authorization: Bearer ' . $this->config['access_token'],
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config['timeout'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'SistemaID-WhatsApp/1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            error_log("WhatsApp cURL Error: " . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === 200 && isset($responseData['messages'])) {
            return [
                'success' => true,
                'message_id' => $responseData['messages'][0]['id'] ?? null,
                'response' => $responseData
            ];
        } else {
            error_log("WhatsApp API Error: " . $response);
            return [
                'success' => false,
                'error' => $responseData['error']['message'] ?? 'Error desconocido',
                'response' => $responseData
            ];
        }
    }
    
    /**
     * Limpiar número de teléfono
     */
    private function limpiarNumero($numero) {
        // Remover todos los caracteres no numéricos excepto +
        $numero = preg_replace('/[^0-9+]/', '', $numero);
        
        // Si no empieza con +, agregar código de país México
        if (!str_starts_with($numero, '+')) {
            // Si empieza con 52, asumir que ya tiene código de país
            if (str_starts_with($numero, '52') && strlen($numero) >= 12) {
                $numero = '+' . $numero;
            } else {
                $numero = '+52' . $numero;
            }
        }
        
        return $numero;
    }
    
    /**
     * Verificar estado del mensaje
     */
    public function verificarEstadoMensaje($messageId) {
        // Esta funcionalidad requiere webhook configurado
        // Por ahora retornamos estado desconocido
        return 'unknown';
    }
    
    /**
     * Obtener plantillas disponibles
     */
    public function obtenerPlantillas() {
        if (empty($this->config['access_token'])) {
            return false;
        }
        
        // Necesitaríamos el WABA ID para esto, por simplicidad retornamos plantillas básicas
        return [
            'vencimiento_recordatorio' => [
                'name' => 'vencimiento_recordatorio',
                'language' => 'es_MX',
                'status' => 'APPROVED',
                'components' => [
                    [
                        'type' => 'BODY',
                        'text' => 'Hola {{1}}, tu servicio {{2}} vence en {{3}} días. Monto: ${{4}}. ¡Renueva ahora!'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Configurar webhook
     */
    public function configurarWebhook($webhookUrl, $verifyToken) {
        // Esta configuración se hace principalmente desde la consola de Facebook
        // Aquí solo guardamos la configuración localmente
        return $this->actualizarConfig([
            'webhook_url' => $webhookUrl,
            'verify_token' => $verifyToken
        ]);
    }
    
    /**
     * Procesar webhook
     */
    public function procesarWebhook($data) {
        // Procesar notificaciones de estado de mensajes
        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                if (isset($entry['changes'])) {
                    foreach ($entry['changes'] as $change) {
                        if ($change['field'] === 'messages') {
                            $this->procesarCambioMensaje($change['value']);
                        }
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * Procesar cambio en mensaje
     */
    private function procesarCambioMensaje($value) {
        // Procesar estados de mensajes (delivered, read, failed, etc.)
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $messageId = $status['id'];
                $estado = $status['status'];
                $timestamp = $status['timestamp'];
                
                // Aquí podrías actualizar el estado en tu base de datos
                $this->actualizarEstadoMensaje($messageId, $estado, $timestamp);
            }
        }
        
        // Procesar mensajes entrantes si es necesario
        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                // Procesar mensajes recibidos
                $this->procesarMensajeEntrante($message);
            }
        }
    }
    
    /**
     * Actualizar estado de mensaje en base de datos
     */
    private function actualizarEstadoMensaje($messageId, $estado, $timestamp) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Buscar la notificación por algún identificador
            // Esto requeriría guardar el message_id cuando se envía
            $stmt = $db->prepare("
                UPDATE notificaciones 
                SET estado = CASE 
                    WHEN ? = 'delivered' THEN 'enviado'
                    WHEN ? = 'failed' THEN 'fallido'
                    ELSE estado 
                END
                WHERE tipo = 'whatsapp' 
                AND JSON_EXTRACT(datos_adicionales, '$.message_id') = ?
            ");
            
            $stmt->execute([$estado, $estado, $messageId]);
        } catch (Exception $e) {
            error_log("Error actualizando estado de mensaje WhatsApp: " . $e->getMessage());
        }
    }
    
    /**
     * Procesar mensaje entrante
     */
    private function procesarMensajeEntrante($message) {
        // Procesar respuestas automáticas si es necesario
        $from = $message['from'];
        $text = $message['text']['body'] ?? '';
        
        // Aquí podrías implementar respuestas automáticas
        // Por ejemplo, si alguien responde "INFO" enviar información de contacto
    }
    
    /**
     * Actualizar configuración
     */
    public function actualizarConfig($nuevaConfig) {
        try {
            $db = Database::getInstance()->getConnection();
            
            foreach ($nuevaConfig as $key => $value) {
                if (isset($this->config[$key])) {
                    $clave = 'whatsapp_' . $key;
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
            error_log("Error actualizando configuración de WhatsApp: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener descripción de configuración
     */
    private function getDescripcionConfig($key) {
        $descripciones = [
            'api_url' => 'URL base de la API de WhatsApp',
            'phone_number_id' => 'ID del número de teléfono de WhatsApp Business',
            'access_token' => 'Token de acceso de WhatsApp Business API',
            'verify_token' => 'Token de verificación para webhook',
            'webhook_url' => 'URL del webhook para recibir notificaciones',
            'timeout' => 'Timeout para peticiones API (segundos)'
        ];
        
        return $descripciones[$key] ?? '';
    }
    
    /**
     * Probar configuración
     */
    public function probarConfiguracion($numeroDestino) {
        $mensaje = "🔧 *Prueba de configuración*\n\n";
        $mensaje .= "Este es un mensaje de prueba desde " . SITE_NAME . ".\n\n";
        $mensaje .= "Si recibe este mensaje, la configuración de WhatsApp es correcta.\n\n";
        $mensaje .= "📅 Fecha: " . date('d/m/Y H:i:s');
        
        return $this->enviarMensaje($numeroDestino, $mensaje);
    }
    
    /**
     * Validar número de teléfono
     */
    public function validarNumero($numero) {
        $numeroLimpio = $this->limpiarNumero($numero);
        
        // Validar formato básico (debe empezar con + y tener al menos 10 dígitos)
        return preg_match('/^\+\d{10,15}$/', $numeroLimpio);
    }
}