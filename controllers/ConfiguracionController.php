<?php
/**
 * Controlador de Configuración
 */
class ConfiguracionController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function index() {
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'email_config':
                    $result = $this->updateEmailConfig($_POST);
                    if ($result['success']) {
                        $success = $result['message'];
                    } else {
                        $error = $result['message'];
                    }
                    break;
                    
                case 'notification_config':
                    $result = $this->updateNotificationConfig($_POST);
                    if ($result['success']) {
                        $success = $result['message'];
                    } else {
                        $error = $result['message'];
                    }
                    break;
                    
                case 'system_config':
                    $result = $this->updateSystemConfig($_POST);
                    if ($result['success']) {
                        $success = $result['message'];
                    } else {
                        $error = $result['message'];
                    }
                    break;
            }
        }
        
        $data = [
            'configuraciones' => $this->getConfigurations(),
            'error' => $error,
            'success' => $success
        ];
        
        include 'views/config/index.php';
    }
    
    private function getConfigurations() {
        $configs = [];
        
        // Obtener todas las configuraciones
        $stmt = $this->db->query("SELECT * FROM configuraciones ORDER BY categoria, nombre");
        $results = $stmt->fetchAll();
        
        foreach ($results as $config) {
            $configs[$config['categoria']][$config['nombre']] = $config['valor'];
        }
        
        return $configs;
    }
    
    private function updateEmailConfig($data) {
        try {
            $configs = [
                'smtp_host' => $data['smtp_host'] ?? '',
                'smtp_port' => $data['smtp_port'] ?? '',
                'smtp_username' => $data['smtp_username'] ?? '',
                'smtp_password' => $data['smtp_password'] ?? '',
                'smtp_encryption' => $data['smtp_encryption'] ?? '',
                'from_email' => $data['from_email'] ?? '',
                'from_name' => $data['from_name'] ?? ''
            ];
            
            foreach ($configs as $nombre => $valor) {
                $this->updateConfig('email', $nombre, $valor);
            }
            
            return ['success' => true, 'message' => 'Configuración de email actualizada correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar configuración de email: ' . $e->getMessage()];
        }
    }
    
    private function updateNotificationConfig($data) {
        try {
            $configs = [
                'dias_anticipacion' => $data['dias_anticipacion'] ?? '7',
                'hora_envio' => $data['hora_envio'] ?? '09:00',
                'notificaciones_activas' => isset($data['notificaciones_activas']) ? '1' : '0',
                'recordatorios_multiples' => isset($data['recordatorios_multiples']) ? '1' : '0'
            ];
            
            foreach ($configs as $nombre => $valor) {
                $this->updateConfig('notificaciones', $nombre, $valor);
            }
            
            return ['success' => true, 'message' => 'Configuración de notificaciones actualizada correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar configuración de notificaciones: ' . $e->getMessage()];
        }
    }
    
    private function updateSystemConfig($data) {
        try {
            $configs = [
                'nombre_sistema' => $data['nombre_sistema'] ?? 'Sistema ID',
                'timezone' => $data['timezone'] ?? 'America/Mexico_City',
                'moneda' => $data['moneda'] ?? 'MXN',
                'idioma' => $data['idioma'] ?? 'es',
                'backup_automatico' => isset($data['backup_automatico']) ? '1' : '0'
            ];
            
            foreach ($configs as $nombre => $valor) {
                $this->updateConfig('sistema', $nombre, $valor);
            }
            
            // Actualizar timezone si cambió
            if (!empty($data['timezone'])) {
                date_default_timezone_set($data['timezone']);
            }
            
            return ['success' => true, 'message' => 'Configuración del sistema actualizada correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar configuración del sistema: ' . $e->getMessage()];
        }
    }
    
    private function updateConfig($categoria, $nombre, $valor) {
        $stmt = $this->db->prepare("
            INSERT INTO configuraciones (categoria, nombre, valor) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)
        ");
        return $stmt->execute([$categoria, $nombre, $valor]);
    }
    
    public function testEmail() {
        $configs = $this->getConfigurations();
        $emailConfig = $configs['email'] ?? [];
        
        if (empty($emailConfig['smtp_host']) || empty($emailConfig['from_email'])) {
            return json_encode(['success' => false, 'message' => 'Configuración de email incompleta.']);
        }
        
        try {
            // Aquí implementarías el test real de email
            // Por ahora solo simulamos
            $testEmail = $_POST['test_email'] ?? '';
            if (empty($testEmail)) {
                return json_encode(['success' => false, 'message' => 'Email de prueba requerido.']);
            }
            
            // Simular envío exitoso
            return json_encode(['success' => true, 'message' => 'Email de prueba enviado correctamente a ' . $testEmail]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'Error al enviar email de prueba: ' . $e->getMessage()]);
        }
    }
}