<?php
/**
 * Modelo Usuario
 */
class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function authenticate($email, $password) {
        $stmt = $this->db->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Actualizar última conexión
            $this->updateLastLogin($user['id']);
            return $user;
        }
        
        return false;
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        return $stmt->execute([
            $data['nombre'],
            $data['email'],
            $hashedPassword,
            $data['rol'] ?? 'cliente'
        ]);
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findAll() {
        $stmt = $this->db->query("SELECT * FROM usuarios WHERE activo = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ?";
        $params = [$data['nombre'], $data['email'], $data['rol']];
        
        if (!empty($data['password'])) {
            $sql .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    private function updateLastLogin($id) {
        $stmt = $this->db->prepare("UPDATE usuarios SET ultima_conexion = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    public function updateLastConnection($id) {
        $stmt = $this->db->prepare("UPDATE usuarios SET ultima_conexion = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function updateProfile($id, $data) {
        $stmt = $this->db->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
        return $stmt->execute([
            $data['nombre'],
            $data['email'],
            $id
        ]);
    }
    
    public function changePassword($id, $currentPassword, $newPassword) {
        // Verificar contraseña actual
        $user = $this->findById($id);
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }
        
        // Actualizar contraseña
        $stmt = $this->db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        return $stmt->execute([
            password_hash($newPassword, PASSWORD_DEFAULT),
            $id
        ]);
    }
}