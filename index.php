<?php
// index.php
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

$page_title = "Inicio - Ecovive Jardinería";

$db = Database::getConnection();

// Obtener las 4 plantas más vistas para la sección destacada
$stmt = $db->query("SELECT p.*, c.nombre AS categoria_nombre 
                    FROM plantas p 
                    JOIN categorias c ON p.categoria_id = c.id 
                    ORDER BY p.vistas DESC, p.id DESC 
                    LIMIT 4");
$destacadas = $stmt->fetchAll();

// Obtener IDs de favoritos si el usuario inició sesión
$mis_favoritos_ids = [];
if (estaAutenticado()) {
    $usuario = obtenerUsuarioAutenticado();
    $stmtFavs = $db->prepare("SELECT planta_id FROM favoritos WHERE usuario_id = :u_id");
    $stmtFavs->execute([':u_id' => $usuario['id']]);
    $mis_favoritos_ids = $stmtFavs->fetchAll(PDO::FETCH_COLUMN);
}

// Cargar la cabecera y la barra de navegación unificada
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="main-container">
    <!-- BANNER PRINCIPAL ESTILO MERCADO LIBRE -->
    <div class="banner-principal">
        <h1>🌱 Descubre la Planta Ideal para tu Hogar</h1>
        <p>Explora especies destacadas, guías de riego y guarda tus preferidas en tu lista personal.</p>
        <a href="catalogo.php" class="btn" style="margin-top: 15px; background: #ffffff; color: var(--color-principal);">Ver Catálogo Completo</a>
    </div>

    <!-- SECCIÓN: PLANTAS MÁS POPULARES -->
    <h2 class="section-title">🔥 Las plantas más populares</h2>

    <section class="plant-grid">
        <?php if (empty($destacadas)): ?>
            <p>No hay plantas destacadas disponibles por el momento.</p>
        <?php else: ?>
            <?php foreach ($destacadas as $planta): ?>
                <?php $es_fav = in_array($planta['id'], $mis_favoritos_ids); ?>
                <div class="plant-card">
                    <div class="card-image">
                        <?php 
                            $img_path = !empty($planta['imagen']) && file_exists("uploads/" . $planta['imagen']) 
                                ? "uploads/" . $planta['imagen'] 
                                : "assets/img/default_plant.jpg";
                        ?>
                        <img src="<?php echo htmlspecialchars($img_path); ?>" alt="<?php echo htmlspecialchars($planta['nombre_comun']); ?>">
                        <span class="badge-cat"><?php echo htmlspecialchars($planta['categoria_nombre']); ?></span>
                        
                        <!-- BOTÓN DE FAVORITOS (Corazón) -->
                        <?php if (estaAutenticado()): ?>
                            <a href="favorito_toggle.php?planta_id=<?php echo $planta['id']; ?>" class="btn-heart" title="<?php echo $es_fav ? 'Quitar de favoritos' : 'Agregar a favoritos'; ?>">
                                <?php echo $es_fav ? '❤️' : '🤍'; ?>
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn-heart" title="Inicia sesión para agregar a favoritos">🤍</a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($planta['nombre_comun']); ?></h3>
                        <p class="scientific-name"><em><?php echo htmlspecialchars($planta['nombre_cientifico'] ?? ''); ?></em></p>
                        
                        <ul class="plant-specs">
                            <li><strong>🚿 Riego:</strong> <?php echo htmlspecialchars($planta['tipo_riego']); ?></li>
                            <li><strong>☀️ Luz:</strong> <?php echo htmlspecialchars($planta['tipo_iluminacion']); ?></li>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>