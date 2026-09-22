<?php
// favorito_toggle.php
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

if (!estaAutenticado()) {
    header("Location: login.php?msj=requiere_registro");
    exit();
}

$planta_id = filter_var($_GET['planta_id'] ?? null, FILTER_VALIDATE_INT);
$usuario = obtenerUsuarioAutenticado();
$usuario_id = $usuario['id'];

if ($planta_id) {
    $db = Database::getConnection();

    // Verificar si ya está en favoritos
    $stmtCheck = $db->prepare("SELECT id FROM favoritos WHERE usuario_id = :u_id AND planta_id = :p_id");
    $stmtCheck->execute([':u_id' => $usuario_id, ':p_id' => $planta_id]);
    $favorito = $stmtCheck->fetch();

    if ($favorito) {
        // Si ya existe, lo quitamos
        $stmtDel = $db->prepare("DELETE FROM favoritos WHERE id = :fav_id");
        $stmtDel->execute([':fav_id' => $favorito['id']]);
    } else {
        // Si no existe, lo agregamos
        $stmtAdd = $db->prepare("INSERT INTO favoritos (usuario_id, planta_id) VALUES (:u_id, :p_id)");
        $stmtAdd->execute([':u_id' => $usuario_id, ':p_id' => $planta_id]);
    }
}

// Redirigir de vuelta a la página donde estaba el usuario
$redirect = $_SERVER['HTTP_REFERER'] ?? 'catalogo.php';
header("Location: " . $redirect);
exit();