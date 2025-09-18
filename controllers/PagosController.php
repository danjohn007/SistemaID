<?php
/**
 * Controlador de Pagos
 */
class PagosController {
    private $pagoModel;
    private $servicioModel;
    
    public function __construct() {
        $this->pagoModel = new Pago();
        $this->servicioModel = new Servicio();
    }
    
    public function index() {
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $pagos = $this->pagoModel->findAll($limit, $offset);
        $pagosPendientes = $this->pagoModel->getPagosPendientes();
        
        $data = [
            'pagos' => $pagos,
            'pagos_pendientes' => $pagosPendientes,
            'current_page' => $page
        ];
        
        include 'views/payments/index.php';
    }
    
    public function nuevo() {
        $error = '';
        $servicioId = $_GET['servicio_id'] ?? '';
        
        // Si se especifica un servicio, obtener sus datos
        $servicio = null;
        if ($servicioId) {
            $servicio = $this->servicioModel->findById($servicioId);
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'servicio_id' => $_POST['servicio_id'] ?? '',
                'monto' => $_POST['monto'] ?? '',
                'requiere_factura' => isset($_POST['requiere_factura']) ? 1 : 0,
                'fecha_pago' => $_POST['fecha_pago'] ?? date('Y-m-d'),
                'metodo_pago' => $_POST['metodo_pago'] ?? '',
                'referencia' => $_POST['referencia'] ?? '',
                'notas' => $_POST['notas'] ?? ''
            ];
            
            if (empty($data['servicio_id']) || empty($data['monto'])) {
                $error = 'El servicio y monto son obligatorios.';
            } else {
                if ($this->pagoModel->registrarPago(
                    $data['servicio_id'], 
                    $data['monto'], 
                    $data['metodo_pago'], 
                    $data['referencia'],
                    $data['requiere_factura']
                )) {
                    header('Location: ' . BASE_URL . 'pagos?success=Pago registrado exitosamente');
                    exit();
                } else {
                    $error = 'Error al registrar el pago.';
                }
            }
        }
        
        // Obtener servicios activos
        $servicios = $this->servicioModel->findAll();
        
        $data = [
            'servicios' => $servicios,
            'servicio_seleccionado' => $servicio,
            'error' => $error
        ];
        
        include 'views/payments/form.php';
    }
    
    public function editar() {
        $id = $_GET['id'] ?? 0;
        $pago = $this->pagoModel->findById($id);
        
        if (!$pago) {
            header('Location: ' . BASE_URL . 'pagos?error=Pago no encontrado');
            exit();
        }
        
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'monto' => $_POST['monto'] ?? '',
                'requiere_factura' => isset($_POST['requiere_factura']) ? 1 : 0,
                'fecha_pago' => $_POST['fecha_pago'] ?? '',
                'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? '',
                'estado' => $_POST['estado'] ?? '',
                'metodo_pago' => $_POST['metodo_pago'] ?? '',
                'referencia' => $_POST['referencia'] ?? '',
                'notas' => $_POST['notas'] ?? ''
            ];
            
            if (empty($data['monto'])) {
                $error = 'El monto es obligatorio.';
            } else {
                if ($this->pagoModel->update($id, $data)) {
                    header('Location: ' . BASE_URL . 'pagos?success=Pago actualizado exitosamente');
                    exit();
                } else {
                    $error = 'Error al actualizar el pago.';
                }
            }
        }
        
        $data = [
            'pago' => $pago,
            'error' => $error
        ];
        
        include 'views/payments/form.php';
    }
}