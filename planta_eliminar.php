<?php
// planta_eliminar.php
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

// Validar que el usuario esté logueado
requerirAutenticacion();

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($id) {
    try {
        $db = Database::getConnection();

        // Obtener imagen para borrarla del servidor si existe
        $stmtImg = $db->prepare("SELECT imagen FROM plantas WHERE id = :id LIMIT 1");
        $stmtImg->execute([':id' => $id]);
        $planta = $stmtImg->fetch();

        if ($planta && !empty($planta['imagen']) && file_exists("uploads/" . $planta['imagen'])) {
            unlink("uploads/" . $planta['imagen']);
        }

        // Eliminar registro
        $stmtDelete = $db->prepare("DELETE FROM plantas WHERE id = :id");
        $stmtDelete->execute([':id' => $id]);

        header("Location: catalogo.php?mensaje=eliminado");
        exit();

    } catch (PDOException $e) {
        die("Error al eliminar la planta: " . $e->getMessage());
    }
} else {
    header("Location: catalogo.php");
    exit();
}