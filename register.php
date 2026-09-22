<?php
// register.php
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($nombre) && !empty($email) && !empty($password)) {
        $db = Database::getConnection();
        
        // Verificar si el correo ya existe
        $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmtCheck->execute([':email' => $email]);
        
        if ($stmtCheck->fetch()) {
            $error = "El correo electrónico ya está registrado.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmtInsert = $db->prepare("INSERT INTO usuarios (nombre, email, password, rol_id) VALUES (:nombre, :email, :pass, 2)");
            
            if ($stmtInsert->execute([':nombre' => $nombre, ':email' => $email, ':pass' => $hash])) {
                header("Location: login.php?msj=registro_exitoso");
                exit();
            } else {
                $error = "Ocurrió un error al crear la cuenta.";
            }
        }
    } else {
        $error = "Por favor completa todos los campos.";
    }
}

$page_title = "Crea tu cuenta - Ecovive Jardinería";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div style="max-width: 400px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: var(--sombra-tarjeta, 0 2px 8px rgba(0,0,0,0.1));">
    <h2 style="text-align: center; margin-bottom: 20px; color: var(--color-principal, #2e7d32);">Completa tus datos para crear tu cuenta</h2>
    
    <?php if (!empty($error)): ?>
        <p style="color: red; text-align: center; margin-bottom: 15px; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div style="margin-bottom: 15px;">
            <label style="display:block; margin-bottom: 5px; font-weight: 500;">Nombre completo</label>
            <input type="text" name="nombre" required style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display:block; margin-bottom: 5px; font-weight: 500;">E-mail</label>
            <input type="email" name="email" required style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display:block; margin-bottom: 5px; font-weight: 500;">Clave</label>
            <input type="password" name="password" required style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
        </div>

        <button type="submit" class="btn" style="width: 100%; background: var(--color-principal, #2e7d32); color: #fff; padding: 10px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Crear cuenta</button>
    </form>

    <p style="text-align: center; margin-top: 20px; font-size: 0.9rem;">
        ¿Ya tienes cuenta? <a href="login.php" style="color: var(--color-principal, #2e7d32); text-decoration: none; font-weight: 600;">Ingresa</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>