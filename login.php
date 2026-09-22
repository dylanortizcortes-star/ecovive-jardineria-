<?php
// login.php
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_rol'] = $usuario['rol_id'] ?? 2;

            header("Location: index.php");
            exit();
        } else {
            $error = "Correo o contraseña incorrectos.";
        }
    } else {
        $error = "Por favor completa todos los campos.";
    }
}

$page_title = "Ingresa - Ecovive Jardinería";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div style="max-width: 400px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: var(--sombra-tarjeta, 0 2px 8px rgba(0,0,0,0.1));">
    <h2 style="text-align: center; margin-bottom: 20px; color: var(--color-principal, #2e7d32);">¡Hola! Ingresa tu e-mail y clave</h2>
    
    <?php if (!empty($error)): ?>
        <p style="color: red; text-align: center; margin-bottom: 15px; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div style="margin-bottom: 15px;">
            <label style="display:block; margin-bottom: 5px; font-weight: 500;">E-mail</label>
            <input type="email" name="email" required style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display:block; margin-bottom: 5px; font-weight: 500;">Clave</label>
            <input type="password" name="password" required style="width:100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
        </div>

        <button type="submit" class="btn" style="width: 100%; background: var(--color-principal, #2e7d32); color: #fff; padding: 10px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Ingresar</button>
    </form>

    <p style="text-align: center; margin-top: 20px; font-size: 0.9rem;">
        ¿No tienes cuenta? <a href="register.php" style="color: var(--color-principal, #2e7d32); text-decoration: none; font-weight: 600;">Crea tu cuenta</a>
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>