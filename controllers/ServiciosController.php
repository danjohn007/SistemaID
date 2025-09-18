<?php
/**
 * Controlador de Servicios
 */
class ServiciosController {
    private $servicioModel;
    private $clienteModel;
    private $tipoServicioModel;
    private $notificacionModel;
    
    public function __construct() {
        $this->servicioModel = new Servicio();
        $this->clienteModel = new Cliente();
        $this->tipoServicioModel = new TipoServicio();
        $this->notificacionModel = new Notificacion();
    }
    
    public function index() {
        $clienteId = $_GET['cliente_id'] ?? null;
        $searchTerm = $_GET['search'] ?? null;
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $servicios = $this->servicioModel->findAll($clienteId, $limit, $offset, $searchTerm);
        $clientes = $this->clienteModel->findAll();
        
        $data = [
            'servicios' => $servicios,
            'clientes' => $clientes,
            'cliente_selected' => $clienteId,
            'search_term' => $searchTerm,
            'current_page' => $page
        ];
        
        include 'views/services/index.php';
    }
    
    public function nuevo() {
        $error = '';
        $clientes = $this->clienteModel->findAll();
        $tiposServicio = $this->tipoServicioModel->findAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'cliente_id' => $_POST['cliente_id'] ?? '',
                'tipo_servicio_id' => $_POST['tipo_servicio_id'] ?? '',
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'dominio' => $_POST['dominio'] ?? '',
                'monto' => $_POST['monto'] ?? '',
                'periodo_vencimiento' => $_POST['periodo_vencimiento'] ?? 'anual',
                'fecha_inicio' => $_POST['fecha_inicio'] ?? date('Y-m-d')
            ];
            
            // Validaciones
            if (empty($data['cliente_id']) || empty($data['tipo_servicio_id']) || 
                empty($data['nombre']) || empty($data['monto'])) {
                $error = 'Todos los campos obligatorios deben ser completados.';
            } else {
                if ($this->servicioModel->create($data)) {
                    // Obtener el ID del servicio recién creado
                    $servicioId = Database::getInstance()->getConnection()->lastInsertId();
                    
                    // Programar notificaciones automáticas para el nuevo servicio
                    $this->programarNotificacionesServicio($servicioId);
                    
                    header('Location: ' . BASE_URL . 'servicios?success=Servicio creado exitosamente');
                    exit();
                } else {
                    $error = 'Error al crear el servicio.';
                }
            }
        }
        
        $data = [
            'clientes' => $clientes,
            'tipos_servicio' => $tiposServicio,
            'error' => $error
        ];
        
        include 'views/services/form.php';
    }
    
    public function editar() {
        $id = $_GET['id'] ?? 0;
        $servicio = $this->servicioModel->findById($id);
        
        if (!$servicio) {
            header('Location: ' . BASE_URL . 'servicios?error=Servicio no encontrado');
            exit();
        }
        
        $error = '';
        $clientes = $this->clienteModel->findAll();
        $tiposServicio = $this->tipoServicioModel->findAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'cliente_id' => $_POST['cliente_id'] ?? '',
                'tipo_servicio_id' => $_POST['tipo_servicio_id'] ?? '',
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'dominio' => $_POST['dominio'] ?? '',
                'monto' => $_POST['monto'] ?? '',
                'periodo_vencimiento' => $_POST['periodo_vencimiento'] ?? 'anual',
                'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
                'estado' => $_POST['estado'] ?? 'activo'
            ];
            
            if (empty($data['cliente_id']) || empty($data['tipo_servicio_id']) || 
                empty($data['nombre']) || empty($data['monto'])) {
                $error = 'Todos los campos obligatorios deben ser completados.';
            } else {
                if ($this->servicioModel->update($id, $data)) {
                    // Reprogramar notificaciones si hay cambios en fechas o estado
                    $this->reprogramarNotificacionesServicio($id);
                    
                    header('Location: ' . BASE_URL . 'servicios?success=Servicio actualizado exitosamente');
                    exit();
                } else {
                    $error = 'Error al actualizar el servicio.';
                }
            }
        }
        
        $data = [
            'servicio' => $servicio,
            'clientes' => $clientes,
            'tipos_servicio' => $tiposServicio,
            'error' => $error
        ];
        
        include 'views/services/form.php';
    }
    
    public function ver() {
        $id = $_GET['id'] ?? 0;
        $servicio = $this->servicioModel->findById($id);
        
        if (!$servicio) {
            header('Location: ' . BASE_URL . 'servicios?error=Servicio no encontrado');
            exit();
        }
        
        // Obtener historial de pagos
        $pagoModel = new Pago();
        $pagos = $pagoModel->findByServicio($id);
        
        $data = [
            'servicio' => $servicio,
            'pagos' => $pagos ?? []
        ];
        
        include 'views/services/view.php';
    }
    
    public function eliminar() {
        $id = $_GET['id'] ?? 0;
        
        if ($this->servicioModel->delete($id)) {
            // Cancelar notificaciones pendientes del servicio
            $this->cancelarNotificacionesServicio($id);
            
            header('Location: ' . BASE_URL . 'servicios?success=Servicio cancelado exitosamente');
        } else {
            header('Location: ' . BASE_URL . 'servicios?error=Error al cancelar el servicio');
        }
        exit();
    }
    
    /**
     * Programar notificaciones automáticas para un servicio nuevo
     */
    private function programarNotificacionesServicio($servicioId) {
        try {
            $this->notificacionModel->programarNotificacionesVencimiento($servicioId);
        } catch (Exception $e) {
            error_log("Error programando notificaciones para servicio $servicioId: " . $e->getMessage());
        }
    }
    
    /**
     * Reprogramar notificaciones para un servicio actualizado
     */
    private function reprogramarNotificacionesServicio($servicioId) {
        try {
            // Cancelar notificaciones pendientes existentes
            $this->cancelarNotificacionesServicio($servicioId, false);
            
            // Programar nuevas notificaciones
            $this->notificacionModel->programarNotificacionesVencimiento($servicioId);
        } catch (Exception $e) {
            error_log("Error reprogramando notificaciones para servicio $servicioId: " . $e->getMessage());
        }
    }
    
    /**
     * Cancelar notificaciones pendientes de un servicio
     */
    private function cancelarNotificacionesServicio($servicioId, $eliminarTodas = true) {
        try {
            $db = Database::getInstance()->getConnection();
            
            if ($eliminarTodas) {
                // Eliminar todas las notificaciones del servicio
                $stmt = $db->prepare("DELETE FROM notificaciones WHERE servicio_id = ?");
                $stmt->execute([$servicioId]);
            } else {
                // Solo cancelar las pendientes
                $stmt = $db->prepare("
                    UPDATE notificaciones 
                    SET estado = 'fallido' 
                    WHERE servicio_id = ? AND estado = 'pendiente'
                ");
                $stmt->execute([$servicioId]);
            }
        } catch (Exception $e) {
            error_log("Error cancelando notificaciones para servicio $servicioId: " . $e->getMessage());
        }
    }
}