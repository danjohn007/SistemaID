<?php
/**
 * Modelo TipoServicio
 */
class TipoServicio {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findAll() {
        $stmt = $this->db->query("SELECT * FROM tipos_servicios WHERE activo = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tipos_servicios WHERE id = ? AND activo = 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO tipos_servicios (nombre, descripcion) VALUES (?, ?)");
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?? null
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE tipos_servicios SET nombre = ?, descripcion = ? WHERE id = ?");
        return $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?? null,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE tipos_servicios SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}