<?php
// includes/navbar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="ml-header">
    <div class="ml-navbar-container">
        <!-- LOGO (Arriba a la Izquierda) -->
        <div class="ml-logo">
            <a href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                <img src="assets/img/logo.png" alt="Ecovive" style="height: 40px; width: auto;" onerror="this.style.display='none'; document.getElementById('logo-fallback').style.display='inline';">
                <span id="logo-fallback" class="logo-text" style="display: none; color: #ffffff; font-weight: bold; font-size: 1.4rem;">🌱 Ecovive Jardinería</span>
            </a>
        </div>

        <!-- BARRA DE NAVEGACIÓN (Centro) -->
        <nav class="ml-nav-center">
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="catalogo.php">Catálogo</a></li>
                <li><a href="favoritos.php" class="btn-fav-nav">❤️ Favoritos</a></li>
                <li><a href="consejos.php">Consejos Ecológicos</a></li>
            </ul>
        </nav>

        <!-- ACCIONES DE USUARIO (Derecha) -->
        <div class="ml-user-actions">
            <?php if (estaAutenticado()): ?>
                <?php $user = obtenerUsuarioAutenticado(); ?>
                <span class="user-greeting">Hola, <?php echo htmlspecialchars($user['nombre']); ?></span>
                <a href="logout.php" class="ml-btn-link">Salir</a>
            <?php else: ?>
                <a href="register.php" class="ml-btn-link">Crea tu cuenta</a>
                <a href="login.php" class="ml-btn-link">Ingresa</a>
            <?php endif; ?>
        </div>
    </div>
</header>