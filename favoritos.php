<?php
// favoritos.php
$page_title = "Mis Plantas Favoritas";
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

// Proteger la vista: solo usuarios logueados
requerirAutenticacion();

$db = Database::getConnection();
$usuario = obtenerUsuarioAutenticado();

// Consulta SQL ajustada para evitar errores de columnas inexistentes
$sql = "SELECT p.*, c.nombre AS categoria_nombre 
        FROM favoritos f 
        JOIN plantas p ON f.planta_id = p.id 
        JOIN categorias c ON p.categoria_id = c.id 
        WHERE f.usuario_id = :usuario_id 
        ORDER BY p.nombre_comun ASC";

$stmt = $db->prepare($sql);
$stmt->execute([':usuario_id' => $usuario['id']]);
$favoritos = $stmt->fetchAll();

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="catalog-header">
    <div class="catalog-title">
        <h1>❤️ Mis Plantas Favoritas</h1>
        <p>Aquí tienes el catálogo personalizado con las plantas que has guardado.</p>
    </div>
</div>

<section class="plant-grid">
    <?php if (empty($favoritos)): ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: #fff; border-radius: 8px;">
            <h3>Aún no has guardado plantas en tus favoritos</h3>
            <p style="color: #666; margin: 10px 0 20px;">Explora el catálogo y presiona el corazón en las especies que más te gusten.</p>
            <a href="catalogo.php" class="btn">Ir al Catálogo</a>
        </div>
    <?php else: ?>
        <?php foreach ($favoritos as $planta): ?>
            <div class="plant-card">
                <div class="card-image">
                    <?php 
                        $img_path = !empty($planta['imagen']) && file_exists("uploads/" . $planta['imagen']) 
                            ? "uploads/" . $planta['imagen'] 
                            : "assets/img/default_plant.jpg";
                    ?>
                    <img src="<?php echo htmlspecialchars($img_path); ?>" alt="<?php echo htmlspecialchars($planta['nombre_comun']); ?>">
                    <span class="badge-cat"><?php echo htmlspecialchars($planta['categoria_nombre']); ?></span>
                    
                    <a href="favorito_toggle.php?planta_id=<?php echo $planta['id']; ?>" class="btn-heart active" title="Quitar de favoritos">
                        ❤️
                    </a>
                </div>
                
                <div class="card-body">
                    <h3><?php echo htmlspecialchars($planta['nombre_comun']); ?></h3>
                    <p class="scientific-name"><em><?php echo htmlspecialchars($planta['nombre_cientifico'] ?? ''); ?></em></p>
                    
                    <ul class="plant-specs">
                        <li><strong>🚿 Riego:</strong> <?php echo htmlspecialchars($planta['tipo_riego']); ?></li>
                        <li><strong>☀️ Luz:</strong> <?php echo htmlspecialchars($planta['tipo_iluminacion']); ?></li>
                        <li><strong>📏 Tamaño:</strong> <?php echo htmlspecialchars($planta['tamano'] ?? 'No especificado'); ?></li>
                        <li><strong>🪴 Tierra:</strong> <?php echo htmlspecialchars($planta['tipo_tierra'] ?? 'No especificada'); ?></li>
                        <li><strong>🍎 Frutos:</strong> <?php echo htmlspecialchars($planta['da_frutos'] ?? 'No'); ?></li>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>