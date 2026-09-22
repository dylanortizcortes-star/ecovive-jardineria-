<?php
// logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vaciar arreglo de sesión
$_SESSION = array();

// Borrar la cookie de sesión del navegador si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión en el servidor
session_destroy();

header("Location: login.php?mensaje=sesion_cerrada");
exit();