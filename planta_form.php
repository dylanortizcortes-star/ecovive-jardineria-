<?php
// planta_form.php
$page_title = "Gestionar Planta";
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

// Restringir acceso solo a usuarios registrados
requerirAutenticacion();

$db = Database::getConnection();

// 1. Obtener categorías para el menú desplegable
$stmtCat = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$categorias = $stmtCat->fetchAll();

// 2. Determinar si es Edición o Creación
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$es_edicion = $id ? true : false;

$planta = [
    'nombre_comun' => '',
    'nombre_cientifico' => '',
    'categoria_id' => '',
    'tipo_riego' => 'Moderado',
    'tipo_iluminacion' => 'Sombra Parcial',
    'dificultad' => 'Fácil',
    'tamano' => '',
    'tipo_tierra' => '',
    'da_frutos' => 'No',
    'descripcion' => '',
    'imagen' => ''
];

$errores = [];

if ($es_edicion) {
    $stmt = $db->prepare("SELECT * FROM plantas WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $planta_db = $stmt->fetch();

    if ($planta_db) {
        $planta = $planta_db;
    } else {
        header("Location: catalogo.php?error=planta_no_encontrada");
        exit();
    }
}

// 3. Procesar el Formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_comun     = trim($_POST['nombre_comun'] ?? '');
    $nombre_cientifico = trim($_POST['nombre_cientifico'] ?? '');
    $categoria_id      = filter_var($_POST['categoria_id'] ?? '', FILTER_VALIDATE_INT);
    $tipo_riego        = $_POST['tipo_riego'] ?? 'Moderado';
    $tipo_iluminacion  = $_POST['tipo_iluminacion'] ?? 'Sombra Parcial';
    $dificultad        = $_POST['dificultad'] ?? 'Fácil';
    $tamano            = trim($_POST['tamano'] ?? '');
    $tipo_tierra       = trim($_POST['tipo_tierra'] ?? '');
    $da_frutos         = $_POST['da_frutos'] ?? 'No';
    $descripcion       = trim($_POST['descripcion'] ?? '');

    // Validaciones
    if (empty($nombre_comun)) $errores[] = "El nombre común es obligatorio.";
    if (!$categoria_id) $errores[] = "Selecciona una categoría válida.";
    if (empty($descripcion)) $errores[] = "La descripción es obligatoria.";

    // Procesamiento de Imagen
    $nombre_imagen = $planta['imagen'];

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagen']['tmp_name'];
        $fileName    = $_FILES['imagen']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $extensiones_permitidas)) {
            // Renombrar archivo para evitar duplicados
            $nombre_imagen = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = 'uploads/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            move_uploaded_file($fileTmpPath, $uploadFileDir . $nombre_imagen);
        } else {
            $errores[] = "Formato de imagen no permitido. Usa JPG, PNG o WEBP.";
        }
    }

    // Guardar en la Base de Datos si no hay errores
    if (empty($errores)) {
        try {
            if ($es_edicion) {
                $sql = "UPDATE plantas SET 
                            nombre_comun = :nombre_comun,
                            nombre_cientifico = :nombre_cientifico,
                            categoria_id = :categoria_id,
                            tipo_riego = :tipo_riego,
                            tipo_iluminacion = :tipo_iluminacion,
                            dificultad = :dificultad,
                            tamano = :tamano,
                            tipo_tierra = :tipo_tierra,
                            da_frutos = :da_frutos,
                            descripcion = :descripcion,
                            imagen = :imagen
                        WHERE id = :id";
                $params = [
                    ':nombre_comun'     => $nombre_comun,
                    ':nombre_cientifico' => $nombre_cientifico,
                    ':categoria_id'      => $categoria_id,
                    ':tipo_riego'        => $tipo_riego,
                    ':tipo_iluminacion'  => $tipo_iluminacion,
                    ':dificultad'        => $dificultad,
                    ':tamano'            => $tamano,
                    ':tipo_tierra'       => $tipo_tierra,
                    ':da_frutos'         => $da_frutos,
                    ':descripcion'       => $descripcion,
                    ':imagen'            => $nombre_imagen,
                    ':id'                => $id
                ];
            } else {
                $sql = "INSERT INTO plantas (nombre_comun, nombre_cientifico, categoria_id, tipo_riego, tipo_iluminacion, dificultad, tamano, tipo_tierra, da_frutos, descripcion, imagen) 
                        VALUES (:nombre_comun, :nombre_cientifico, :categoria_id, :tipo_riego, :tipo_iluminacion, :dificultad, :tamano, :tipo_tierra, :da_frutos, :descripcion, :imagen)";
                $params = [
                    ':nombre_comun'     => $nombre_comun,
                    ':nombre_cientifico' => $nombre_cientifico,
                    ':categoria_id'      => $categoria_id,
                    ':tipo_riego'        => $tipo_riego,
                    ':tipo_iluminacion'  => $tipo_iluminacion,
                    ':dificultad'        => $dificultad,
                    ':tamano'            => $tamano,
                    ':tipo_tierra'       => $tipo_tierra,
                    ':da_frutos'         => $da_frutos,
                    ':descripcion'       => $descripcion,
                    ':imagen'            => $nombre_imagen
                ];
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            header("Location: catalogo.php?mensaje=guardado");
            exit();

        } catch (PDOException $e) {
            $errores[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="auth-card" style="max-width: 650px;">
    <h2><?php echo $es_edicion ? 'Editar Planta' : 'Agregar Nueva Planta'; ?></h2>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errores as $err): ?>
                    <li><?php echo htmlspecialchars($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="planta_form.php<?php echo $es_edicion ? '?id='.$id : ''; ?>" method="POST" enctype="multipart/form-data" class="form-auth">
        
        <div class="form-group">
            <label>Nombre Común *</label>
            <input type="text" name="nombre_comun" value="<?php echo htmlspecialchars($planta['nombre_comun']); ?>" required>
        </div>

        <div class="form-group">
            <label>Nombre Científico</label>
            <input type="text" name="nombre_cientifico" value="<?php echo htmlspecialchars($planta['nombre_cientifico']); ?>">
        </div>

        <div class="form-group">
            <label>Categoría *</label>
            <select name="categoria_id" required style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                <option value="">Selecciona una categoría</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($planta['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Tipo de Riego</label>
                <select name="tipo_riego" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="Bajo" <?php echo ($planta['tipo_riego'] == 'Bajo') ? 'selected' : ''; ?>>Bajo</option>
                    <option value="Moderado" <?php echo ($planta['tipo_riego'] == 'Moderado') ? 'selected' : ''; ?>>Moderado</option>
                    <option value="Frecuente" <?php echo ($planta['tipo_riego'] == 'Frecuente') ? 'selected' : ''; ?>>Frecuente</option>
                </select>
            </div>

            <div class="form-group" style="flex: 1;">
                <label>Iluminación</label>
                <select name="tipo_iluminacion" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="Sombra" <?php echo ($planta['tipo_iluminacion'] == 'Sombra') ? 'selected' : ''; ?>>Sombra</option>
                    <option value="Sombra Parcial" <?php echo ($planta['tipo_iluminacion'] == 'Sombra Parcial') ? 'selected' : ''; ?>>Sombra Parcial</option>
                    <option value="Luz Directa" <?php echo ($planta['tipo_iluminacion'] == 'Luz Directa') ? 'selected' : ''; ?>>Luz Directa</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
                <label>Tamaño Aprox.</label>
                <input type="text" name="tamano" placeholder="Ej: Mediano (1m)" value="<?php echo htmlspecialchars($planta['tamano']); ?>">
            </div>

            <div class="form-group" style="flex: 1;">
                <label>Tipo de Tierra</label>
                <input type="text" name="tipo_tierra" placeholder="Ej: Sustrato drenado" value="<?php echo htmlspecialchars($planta['tipo_tierra']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>¿Da Frutos?</label>
            <select name="da_frutos" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;">
                <option value="No" <?php echo ($planta['da_frutos'] == 'No') ? 'selected' : ''; ?>>No</option>
                <option value="Sí" <?php echo ($planta['da_frutos'] == 'Sí') ? 'selected' : ''; ?>>Sí</option>
            </select>
        </div>

        <div class="form-group">
            <label>Descripción / Cuidados *</label>
            <textarea name="descripcion" rows="4" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;" required><?php echo htmlspecialchars($planta['descripcion']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Fotografía de la Planta</label>
            <input type="file" name="imagen" accept="image/*">
            <?php if (!empty($planta['imagen'])): ?>
                <p style="font-size: 0.85rem; color: #666; margin-top: 5px;">Imagen actual: <?php echo htmlspecialchars($planta['imagen']); ?></p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-block"><?php echo $es_edicion ? 'Actualizar Planta' : 'Guardar Planta'; ?></button>
        <a href="catalogo.php" style="display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none;">Cancelar</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>