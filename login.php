<?php
require __DIR__ . '/includes/app.php';

if (isLoggedIn()) {
    redirect('inicio.php');
}

$resetDefaultsForNewAccount = !empty($_SESSION['new_account_defaults']);
unset($_SESSION['new_account_defaults']);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Informe e-mail e senha para entrar.';
    } elseif (!isPasswordLengthValid($password)) {
        $errors[] = 'A senha deve ter entre 5 e 20 caracteres.';
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
<html lang="pt-BR" class="auth-page">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MathPlay | Login</title>
  <link rel="icon" type="image/svg+xml" href="logo.svg" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <script defer src="assets/js/app.js"></script>
</head>
<body class="auth-page"<?= $resetDefaultsForNewAccount ? ' data-reset-defaults="true"' : '' ?>>
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
          <div hidden data-alert-message="<?= htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8') ?>"></div>
        <?php endif; ?>

        <form class="auth-form" method="post">
          <div class="field-group">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" placeholder="seuemail@email.com" required />
          </div>

          <div class="field-group">
            <label for="password">Senha</label>
            <input id="password" name="password" type="password" placeholder="Sua senha" minlength="5" maxlength="20" required />
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
