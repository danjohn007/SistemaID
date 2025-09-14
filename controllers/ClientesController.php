<?php
/**
 * Controlador de Clientes
 */
class ClientesController {
    private $clienteModel;
    
    public function __construct() {
        $this->clienteModel = new Cliente();
    }
    
    public function index() {
        $search = $_GET['search'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        if ($search) {
            $clientes = $this->clienteModel->search($search);
            $totalClientes = count($clientes);
        } else {
            $clientes = $this->clienteModel->findAll($limit, $offset);
            $totalClientes = $this->clienteModel->count();
        }
        
        $totalPages = ceil($totalClientes / $limit);
        
        $data = [
            'clientes' => $clientes,
            'search' => $search,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_clientes' => $totalClientes
        ];
        
        include 'views/clients/index.php';
    }
    
    public function nuevo() {
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre_razon_social' => $_POST['nombre_razon_social'] ?? '',
                'rfc' => $_POST['rfc'] ?? '',
                'contacto' => $_POST['contacto'] ?? '',
                'email' => $_POST['email'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'direccion' => $_POST['direccion'] ?? ''
            ];
            
            // Validaciones básicas
            if (empty($data['nombre_razon_social']) || empty($data['email'])) {
                $error = 'El nombre/razón social y email son obligatorios.';
            } else {
                if ($this->clienteModel->create($data)) {
                    header('Location: ' . BASE_URL . 'clientes?success=Cliente creado exitosamente');
                    exit();
                } else {
                    $error = 'Error al crear el cliente.';
                }
            }
        }
        
        include 'views/clients/form.php';
    }
    
    public function editar() {
        $id = $_GET['id'] ?? 0;
        $cliente = $this->clienteModel->findById($id);
        
        if (!$cliente) {
            header('Location: ' . BASE_URL . 'clientes?error=Cliente no encontrado');
            exit();
        }
        
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre_razon_social' => $_POST['nombre_razon_social'] ?? '',
                'rfc' => $_POST['rfc'] ?? '',
                'contacto' => $_POST['contacto'] ?? '',
                'email' => $_POST['email'] ?? '',
                'telefono' => $_POST['telefono'] ?? '',
                'direccion' => $_POST['direccion'] ?? ''
            ];
            
            if (empty($data['nombre_razon_social']) || empty($data['email'])) {
                $error = 'El nombre/razón social y email son obligatorios.';
            } else {
                if ($this->clienteModel->update($id, $data)) {
                    header('Location: ' . BASE_URL . 'clientes?success=Cliente actualizado exitosamente');
                    exit();
                } else {
                    $error = 'Error al actualizar el cliente.';
                }
            }
        }
        
        include 'views/clients/form.php';
    }
    
    public function ver() {
        $id = $_GET['id'] ?? 0;
        $cliente = $this->clienteModel->findById($id);
        
        if (!$cliente) {
            header('Location: ' . BASE_URL . 'clientes?error=Cliente no encontrado');
            exit();
        }
        
        // Obtener servicios del cliente
        $servicioModel = new Servicio();
        $servicios = $servicioModel->findAll($id);
        
        $data = [
            'cliente' => $cliente,
            'servicios' => $servicios
        ];
        
        include 'views/clients/view.php';
    }
    
    public function eliminar() {
        $id = $_GET['id'] ?? 0;
        
        if ($this->clienteModel->delete($id)) {
            header('Location: ' . BASE_URL . 'clientes?success=Cliente eliminado exitosamente');
        } else {
            header('Location: ' . BASE_URL . 'clientes?error=Error al eliminar el cliente');
        }
        exit();
    }
}