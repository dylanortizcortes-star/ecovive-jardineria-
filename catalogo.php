<?php
// catalogo.php
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

$page_title = "Catálogo de Plantas - Ecovive Jardinería";

$db = Database::getConnection();

// 1. Obtener categorías para el menú desplegable
$stmtCat = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$categorias = $stmtCat->fetchAll();

// 2. Procesar filtros de búsqueda
$busqueda = trim($_GET['buscar'] ?? '');
$categoria_id = filter_var($_GET['categoria'] ?? '', FILTER_VALIDATE_INT);

$sql = "SELECT p.*, c.nombre AS categoria_nombre 
        FROM plantas p 
        JOIN categorias c ON p.categoria_id = c.id 
        WHERE 1=1";
$params = [];

if (!empty($busqueda)) {
    $sql .= " AND (p.nombre_comun LIKE :busqueda OR p.nombre_cientifico LIKE :busqueda)";
    $params[':busqueda'] = "%{$busqueda}%";
}

if ($categoria_id) {
    $sql .= " AND p.categoria_id = :categoria_id";
    $params[':categoria_id'] = $categoria_id;
}

$sql .= " ORDER BY p.nombre_comun ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$plantas = $stmt->fetchAll();

// 3. Obtener lista de favoritos del usuario si está logueado
$mis_favoritos_ids = [];
if (estaAutenticado()) {
    $usuario = obtenerUsuarioAutenticado();
    $stmtFavs = $db->prepare("SELECT planta_id FROM favoritos WHERE usuario_id = :u_id");
    $stmtFavs->execute([':u_id' => $usuario['id']]);
    $mis_favoritos_ids = $stmtFavs->fetchAll(PDO::FETCH_COLUMN);
}

// 4. Incluir estructura global de la página
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="main-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="section-title" style="margin-bottom: 0;">🌿 Catálogo de Plantas</h2>
        <?php if (estaAutenticado()): ?>
            <a href="planta_form.php" class="btn">+ Publicar Nueva Planta</a>
        <?php endif; ?>
    </div>

    <!-- BARRA DE BÚSQUEDA Y FILTROS -->
    <div style="background: #ffffff; padding: 20px; border-radius: var(--radio-borde); box-shadow: var(--sombra-tarjeta); margin-bottom: 25px;">
        <form method="GET" action="catalogo.php" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 200px;">
                <input type="text" name="buscar" placeholder="Buscar por nombre común o científico..." value="<?php echo htmlspecialchars($busqueda); ?>" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
            </div>
            
            <div style="min-width: 180px;">
                <select name="categoria" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; background: #fff;">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($categoria_id == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn">Buscar</button>

            <?php if (!empty($busqueda) || $categoria_id): ?>
                <a href="catalogo.php" style="color: #666; text-decoration: none; font-size: 0.9rem;">Limpiar filtros</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- GRILLA DE PLANTAS ESTILO MERCADO LIBRE -->
    <section class="plant-grid">
        <?php if (empty($plantas)): ?>
            <p style="grid-column: 1/-1; text-align: center; color: #666; padding: 30px;">No se encontraron plantas que coincidan con la búsqueda.</p>
        <?php else: ?>
            <?php foreach ($plantas as $planta): ?>
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
                        
                        <!-- BOTÓN DE FAVORITOS -->
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
                            <li><strong>📏 Tamaño:</strong> <?php echo htmlspecialchars($planta['tamano'] ?? 'N/A'); ?></li>
                        </ul>
                    </div>

                    <?php if (estaAutenticado()): ?>
                        <div class="card-footer">
                            <div class="card-actions">
                                <a href="planta_form.php?id=<?php echo $planta['id']; ?>" class="btn-edit">✏️ Editar</a>
                                <a href="planta_eliminar.php?id=<?php echo $planta['id']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar esta planta?');">🗑️ Eliminar</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>