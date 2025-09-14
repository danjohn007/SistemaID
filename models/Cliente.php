<?php
/**
 * Modelo Cliente
 */
class Cliente {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO clientes (usuario_id, nombre_razon_social, rfc, contacto, email, telefono, direccion) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['usuario_id'] ?? null,
            $data['nombre_razon_social'],
            $data['rfc'] ?? null,
            $data['contacto'] ?? null,
            $data['email'],
            $data['telefono'] ?? null,
            $data['direccion'] ?? null
        ]);
    }
    
    public function findAll($limit = null, $offset = 0) {
        $sql = "SELECT c.*, u.nombre as usuario_nombre 
                FROM clientes c 
                LEFT JOIN usuarios u ON c.usuario_id = u.id 
                WHERE c.activo = 1 
                ORDER BY c.nombre_razon_social ASC";
        
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.nombre as usuario_nombre 
            FROM clientes c 
            LEFT JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.id = ? AND c.activo = 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE clientes 
            SET usuario_id = ?, nombre_razon_social = ?, rfc = ?, contacto = ?, 
                email = ?, telefono = ?, direccion = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['usuario_id'] ?? null,
            $data['nombre_razon_social'],
            $data['rfc'] ?? null,
            $data['contacto'] ?? null,
            $data['email'],
            $data['telefono'] ?? null,
            $data['direccion'] ?? null,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE clientes SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function count() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
        return $stmt->fetch()['total'];
    }
    
    public function search($term) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.nombre as usuario_nombre 
            FROM clientes c 
            LEFT JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.activo = 1 
            AND (c.nombre_razon_social LIKE ? OR c.email LIKE ? OR c.rfc LIKE ?) 
            ORDER BY c.nombre_razon_social ASC
        ");
        $searchTerm = "%$term%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    public function getClientesConServicios() {
        $stmt = $this->db->query("
            SELECT c.*, 
                   COUNT(s.id) as total_servicios,
                   SUM(CASE WHEN s.estado = 'activo' THEN 1 ELSE 0 END) as servicios_activos
            FROM clientes c 
            LEFT JOIN servicios s ON c.id = s.cliente_id 
            WHERE c.activo = 1 
            GROUP BY c.id 
            ORDER BY c.nombre_razon_social ASC
        ");
        return $stmt->fetchAll();
    }
}