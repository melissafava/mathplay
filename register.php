<?php
require __DIR__ . '/includes/app.php';

if (isLoggedIn()) {
    redirect('inicio.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role = in_array((string)($_POST['role'] ?? 'aluno'), ['aluno', 'professor'], true) ? $_POST['role'] : 'aluno';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'Preencha nome, e-mail e senha para continuar.';
    } elseif (!isNameLengthValid($name)) {
        $errors[] = 'O nome deve ter no máximo 15 caracteres.';
    } elseif (!isPasswordLengthValid($password)) {
        $errors[] = 'A senha deve ter entre 5 e 20 caracteres.';
    } else {
        $exists = db()->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $exists->execute(['email' => $email]);
        if ($exists->fetch()) {
            $errors[] = 'Este e-mail já está cadastrado.';
        } else {
            $insert = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
            $insert->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
            ]);
            $userId = (int)db()->lastInsertId();
            ensureUserProgress($userId);
            $_SESSION['new_account_defaults'] = true;
            redirect('login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="auth-page">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MathPlay | Cadastro</title>
  <link rel="icon" type="image/svg+xml" href="logo.svg" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <script defer src="assets/js/app.js"></script>
</head>
<body class="auth-page">
  <main class="auth-shell">
    <div class="auth-card">
      <div class="auth-illustration">
        <img src="login.svg" alt="Ilustração de crianças estudando" />
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
            <label for="name">Nome</label>
            <input id="name" name="name" type="text" placeholder="Seu nome" maxlength="15" required />
          </div>

          <div class="field-group">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" placeholder="seuemail@email.com" required />
          </div>

          <div class="field-group">
            <label for="password">Senha</label>
            <input id="password" name="password" type="password" placeholder="Crie uma senha" minlength="5" maxlength="20" data-password-length-check required aria-describedby="password-length-hint" />
            <small class="field-hint" id="password-length-hint" data-password-length-hint aria-live="polite">Use de 5 a 20 caracteres.</small>
          </div>

          <div class="field-group">
            <label>Tipo de usuário</label>
            <div class="role-choice">
              <label>
                <input type="radio" name="role" value="aluno" checked />
                <span>Aluno</span>
              </label>
              <label>
                <input type="radio" name="role" value="professor" />
                <span>Professor</span>
              </label>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="primary-btn">Cadastrar</button>
          </div>
        </form>

        <div class="switch-link">Já tem uma conta? <a href="login.php">Fazer login</a></div>
      </div>
    </div>
  </main>
</body>
</html>
