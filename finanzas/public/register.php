<?php
session_start();
require_once __DIR__ . '/../app/helpers.php';
include __DIR__ . '/../views/header.php';

$error = flash('error');
$success = flash('success');
?>
<div class="container mt-4">
    <a href="login.php" class="btn btn-sm btn-outline-secondary mb-3">← Ir a iniciar sesión</a>
    <h3>Registro</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <form method="post" action="register_action.php">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="password_confirm" class="form-control" required>
        </div>
        <button class="btn btn-primary" type="submit">Crear cuenta</button>
    </form>
</div>
<?php include __DIR__ . '/../views/footer.php'; ?>
