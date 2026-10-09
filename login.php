<?php
require __DIR__ . '/includes/app.php';

if (isLoggedIn()) {
    redirect('inicio.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Informe e-mail e senha para entrar.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = (int)$user['id'];
            ensureUserProgress((int)$user['id']);
            redirect('inicio.php');
        }

        $errors[] = 'E-mail ou senha inválidos. Tente novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MathPlay | Login</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <main class="auth-shell">
    <div class="auth-card">
      <div class="auth-illustration">
        <img src="login.svg" alt="Ilustração de calculadora com símbolos matemáticos" />
      </div>
      <div class="auth-form-side">
        <div class="auth-brand">
          <img src="logocor.svg" alt="Logo MathPlay" />
        </div>

        <?php if ($errors): ?>
          <div class="alert-box" style="background:#ffe2e2;color:#9b1c1c;padding:12px 14px;border-radius:12px;margin-bottom:12px;">
            <?= htmlspecialchars($errors[0]) ?>
          </div>
        <?php endif; ?>

        <form class="auth-form" method="post">
          <div class="field-group">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" placeholder="seuemail@email.com" required />
          </div>

          <div class="field-group">
            <label for="password">Senha</label>
            <input id="password" name="password" type="password" placeholder="Sua senha" required />
          </div>

          <div class="form-actions">
            <button type="submit" class="primary-btn">Entrar</button>
          </div>
        </form>

        <div class="switch-link">Não tem uma conta? <a href="register.php">Cadastrar</a></div>
      </div>
    </div>
  </main>
</body>
</html>
