<?php
/**
 * Controlador de Perfil de Usuario
 */
class PerfilController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new Usuario();
    }
    
    public function index() {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        $error = '';
        $success = '';
        
        // Update last connection timestamp
        $this->userModel->updateLastConnection($userId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'update_profile') {
                $data = [
                    'nombre' => $_POST['nombre'] ?? '',
                    'email' => $_POST['email'] ?? '',
                ];
                
                if (empty($data['nombre']) || empty($data['email'])) {
                    $error = 'El nombre y email son obligatorios.';
                } else {
                    if ($this->userModel->updateProfile($userId, $data)) {
                        $_SESSION['user_name'] = $data['nombre'];
                        $_SESSION['user_email'] = $data['email'];
                        $success = 'Perfil actualizado correctamente.';
                        $user = $this->userModel->findById($userId); // Reload user data
                    } else {
                        $error = 'Error al actualizar el perfil.';
                    }
                }
            } elseif ($action === 'change_password') {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                    $error = 'Todos los campos de contraseña son obligatorios.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Las nuevas contraseñas no coinciden.';
                } elseif (strlen($newPassword) < 6) {
                    $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
                } else {
                    if ($this->userModel->changePassword($userId, $currentPassword, $newPassword)) {
                        $success = 'Contraseña actualizada correctamente.';
                    } else {
                        $error = 'La contraseña actual es incorrecta.';
                    }
                }
            }
        }
        
        $data = [
            'user' => $user,
            'error' => $error,
            'success' => $success
        ];
        
        include 'views/profile/index.php';
    }
}