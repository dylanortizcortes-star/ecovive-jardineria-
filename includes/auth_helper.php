<?php
// includes/auth_helper.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaAutenticado() {
    return isset($_SESSION['usuario_id']);
}

function esAdministrador() {
    return estaAutenticado() && isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 1;
}

function obtenerUsuarioAutenticado() {
    if (!estaAutenticado()) {
        return null;
    }
    return [
        'id'     => $_SESSION['usuario_id'] ?? null,
        'nombre' => $_SESSION['usuario_nombre'] ?? 'Usuario',
        'email'  => $_SESSION['usuario_email'] ?? '',
        'rol_id' => $_SESSION['usuario_rol'] ?? 2
    ];
}

function requerirAutenticacion() {
    if (!estaAutenticado()) {
        header("Location: login.php?mensaje=debes_iniciar_sesion");
        exit();
    }
}

function requerirAdmin() {
    requerirAutenticacion();
    if (!esAdministrador()) {
        header("Location: index.php?error=acceso_denegado");
        exit();
    }
}