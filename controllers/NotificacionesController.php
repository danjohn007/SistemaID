<?php
/**
 * Controlador de Notificaciones
 */
class NotificacionesController {
    private $notificacionModel;
    private $emailService;
    private $whatsappService;
    private $scheduler;
    
    public function __construct() {
        $this->notificacionModel = new Notificacion();
        $this->emailService = new EmailService();
        $this->whatsappService = new WhatsAppService();
        $this->scheduler = new NotificationScheduler();
    }
    
    /**
     * Vista principal de notificaciones
     */
    public function index() {
        $filtros = [
            'tipo' => $_GET['tipo'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'desde' => $_GET['desde'] ?? '',
            'hasta' => $_GET['hasta'] ?? '',
            'limit' => 50,
            'offset' => ($_GET['page'] ?? 1 - 1) * 50
        ];
        
        $notificaciones = $this->notificacionModel->findAll($filtros);
        $estadisticas = $this->notificacionModel->getEstadisticas();
        $proximasNotificaciones = $this->scheduler->getProximasNotificaciones(5);
        
        $data = [
            'notificaciones' => $notificaciones,
            'estadisticas' => $estadisticas,
            'proximas' => $proximasNotificaciones,
            'filtros' => $filtros
        ];
        
        include 'views/notifications/index.php';
    }
    
    /**
     * Vista de configuración de notificaciones
     */
    public function configuracion() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardarConfiguracion();
        }
        
        $configuracion = $this->obtenerConfiguracion();
        
        $data = [
            'config' => $configuracion
        ];
        
        include 'views/notifications/config.php';
    }
    
    /**
     * Procesar notificaciones pendientes (llamada AJAX/Cron)
     */
    public function procesar() {
        requireAdmin();
        
        $resultado = $this->scheduler->procesarNotificacionesPendientes();
        
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $resultado
            ]);
            exit;
        }
        
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => "Procesadas: {$resultado['procesadas']}, Exitosas: {$resultado['exitosas']}, Fallidas: {$resultado['fallidas']}"
        ];
        
        header('Location: ' . BASE_URL . 'notificaciones');
        exit;
    }
    
    /**
     * Programar notificaciones automáticas
     */
    public function programar() {
        requireAdmin();
        
        $programadas = $this->scheduler->programarNotificacionesVencimiento();
        
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'programadas' => $programadas
            ]);
            exit;
        }
        
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => "Se programaron {$programadas} notificaciones automáticas"
        ];
        
        header('Location: ' . BASE_URL . 'notificaciones');
        exit;
    }
    
    /**
     * Enviar notificación de prueba
     */
    public function probar() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'notificaciones/configuracion');
            exit;
        }
        
        $tipo = $_POST['tipo'] ?? '';
        $destinatario = $_POST['destinatario'] ?? '';
        
        $resultado = false;
        $mensaje = '';
        
        switch ($tipo) {
            case 'email':
                if (!$this->emailService->validarEmail($destinatario)) {
                    $mensaje = 'Email inválido';
                } else {
                    $resultado = $this->emailService->probarConfiguracion($destinatario);
                    $mensaje = $resultado ? 'Email de prueba enviado correctamente' : 'Error al enviar email de prueba';
                }
                break;
                
            case 'whatsapp':
                if (!$this->whatsappService->validarNumero($destinatario)) {
                    $mensaje = 'Número de teléfono inválido';
                } else {
                    $respuesta = $this->whatsappService->probarConfiguracion($destinatario);
                    $resultado = $respuesta['success'] ?? false;
                    $mensaje = $resultado ? 'Mensaje de WhatsApp enviado correctamente' : 'Error al enviar mensaje de WhatsApp: ' . ($respuesta['error'] ?? 'Error desconocido');
                }
                break;
                
            default:
                $mensaje = 'Tipo de notificación no válido';
        }
        
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $resultado,
                'message' => $mensaje
            ]);
            exit;
        }
        
        $_SESSION['flash_message'] = [
            'type' => $resultado ? 'success' : 'danger',
            'message' => $mensaje
        ];
        
        header('Location: ' . BASE_URL . 'notificaciones/configuracion');
        exit;
    }
    
    /**
     * Ver detalle de una notificación
     */
    public function ver() {
        $id = $_GET['id'] ?? 0;
        $notificacion = $this->notificacionModel->findById($id);
        
        if (!$notificacion) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => 'Notificación no encontrada'
            ];
            header('Location: ' . BASE_URL . 'notificaciones');
            exit;
        }
        
        $data = [
            'notificacion' => $notificacion
        ];
        
        include 'views/notifications/view.php';
    }
    
    /**
     * Reenviar notificación fallida
     */
    public function reenviar() {
        requireAdmin();
        
        $id = $_POST['id'] ?? 0;
        $notificacion = $this->notificacionModel->findById($id);
        
        if (!$notificacion || $notificacion['estado'] !== 'fallido') {
            $resultado = ['success' => false, 'message' => 'Notificación no válida para reenvío'];
        } else {
            // Resetear estado y fecha programada
            $stmt = Database::getInstance()->getConnection()->prepare("
                UPDATE notificaciones 
                SET estado = 'pendiente', fecha_programada = NOW(), intentos = 0 
                WHERE id = ?
            ");
            
            if ($stmt->execute([$id])) {
                $resultado = ['success' => true, 'message' => 'Notificación reprogramada para reenvío'];
            } else {
                $resultado = ['success' => false, 'message' => 'Error al reprogramar notificación'];
            }
        }
        
        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode($resultado);
            exit;
        }
        
        $_SESSION['flash_message'] = [
            'type' => $resultado['success'] ? 'success' : 'danger',
            'message' => $resultado['message']
        ];
        
        header('Location: ' . BASE_URL . 'notificaciones');
        exit;
    }
    
    /**
     * Obtener configuración actual
     */
    private function obtenerConfiguracion() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT clave, valor FROM configuraciones WHERE clave LIKE 'email_%' OR clave LIKE 'whatsapp_%'");
        $configs = $stmt->fetchAll();
        
        $configuracion = [];
        foreach ($configs as $config) {
            $configuracion[$config['clave']] = $config['valor'];
        }
        
        return $configuracion;
    }
    
    /**
     * Guardar configuración
     */
    private function guardarConfiguracion() {
        $emailConfig = [
            'method' => $_POST['email_method'] ?? 'mail',
            'smtp_host' => $_POST['email_smtp_host'] ?? '',
            'smtp_port' => $_POST['email_smtp_port'] ?? 587,
            'smtp_username' => $_POST['email_smtp_username'] ?? '',
            'smtp_password' => $_POST['email_smtp_password'] ?? '',
            'smtp_secure' => $_POST['email_smtp_secure'] ?? 'tls',
            'from_email' => $_POST['email_from_email'] ?? MAIL_FROM,
            'from_name' => $_POST['email_from_name'] ?? MAIL_FROM_NAME,
            'reply_to' => $_POST['email_reply_to'] ?? MAIL_FROM
        ];
        
        $whatsappConfig = [
            'phone_number_id' => $_POST['whatsapp_phone_number_id'] ?? '',
            'access_token' => $_POST['whatsapp_access_token'] ?? '',
            'verify_token' => $_POST['whatsapp_verify_token'] ?? '',
            'webhook_url' => $_POST['whatsapp_webhook_url'] ?? ''
        ];
        
        $emailSuccess = $this->emailService->actualizarConfig($emailConfig);
        $whatsappSuccess = $this->whatsappService->actualizarConfig($whatsappConfig);
        
        $mensaje = '';
        $tipo = 'success';
        
        if ($emailSuccess && $whatsappSuccess) {
            $mensaje = 'Configuración guardada correctamente';
        } elseif ($emailSuccess) {
            $mensaje = 'Configuración de email guardada. Error en configuración de WhatsApp';
            $tipo = 'warning';
        } elseif ($whatsappSuccess) {
            $mensaje = 'Configuración de WhatsApp guardada. Error en configuración de email';
            $tipo = 'warning';
        } else {
            $mensaje = 'Error al guardar la configuración';
            $tipo = 'danger';
        }
        
        $_SESSION['flash_message'] = [
            'type' => $tipo,
            'message' => $mensaje
        ];
    }
    
    /**
     * API endpoint para estadísticas (para dashboard)
     */
    public function estadisticas() {
        $estadisticas = $this->notificacionModel->getEstadisticas();
        $resumenDiario = $this->scheduler->getResumenDiario();
        
        header('Content-Type: application/json');
        echo json_encode([
            'estadisticas' => $estadisticas,
            'resumen_diario' => $resumenDiario
        ]);
        exit;
    }
    
    /**
     * Webhook para WhatsApp
     */
    public function webhook() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Verificación del webhook
            $verify_token = $_GET['hub_verify_token'] ?? '';
            $challenge = $_GET['hub_challenge'] ?? '';
            
            // Obtener token de verificación de la configuración
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT valor FROM configuraciones WHERE clave = 'whatsapp_verify_token'");
            $stmt->execute();
            $config = $stmt->fetch();
            
            if ($config && $verify_token === $config['valor']) {
                echo $challenge;
                exit;
            } else {
                http_response_code(403);
                exit;
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar webhook
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if ($data) {
                $this->whatsappService->procesarWebhook($data);
            }
            
            http_response_code(200);
            echo 'OK';
            exit;
        }
        
        http_response_code(405);
        exit;
    }
}