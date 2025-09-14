<?php
/**
 * Controlador de Autenticación
 */
class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new Usuario();
    }
    
    public function login() {
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                $error = 'Por favor, complete todos los campos.';
            } else {
                $user = $this->userModel->authenticate($email, $password);
                
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['rol'];
                    
                    header('Location: ' . BASE_URL . 'dashboard');
                    exit();
                } else {
                    $error = 'Credenciales incorrectas.';
                }
            }
        }
        
        include 'views/auth/login.php';
    }
    
    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . 'login');
        exit();
    }
}