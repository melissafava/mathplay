<?php
require __DIR__ . '/includes/app.php';
requireAuth();

$user = currentUser();
$settingsError = '';
$settingsMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings_update'])) {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $role = in_array((string)($_POST['role'] ?? 'aluno'), ['aluno', 'professor'], true) ? $_POST['role'] : 'aluno';
    $password = (string)($_POST['password'] ?? '');

    if (!isNameLengthValid($name)) {
        $settingsError = 'O nome deve ter no máximo 15 caracteres.';
    } elseif ($password !== '' && !isPasswordLengthValid($password, 6, 20)) {
        $settingsError = 'A nova senha deve ter entre 6 e 20 caracteres.';
    } elseif ($name !== '' && $email !== '') {
        if ($password !== '') {
            $stmt = db()->prepare('UPDATE users SET name = :name, email = :email, password_hash = :hash, role = :role WHERE id = :userId');
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'userId' => $user['id'],
            ]);
        } else {
            $stmt = db()->prepare('UPDATE users SET name = :name, email = :email, role = :role WHERE id = :userId');
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'userId' => $user['id'],
            ]);
        }
        $settingsMessage = 'Configurações atualizadas com sucesso!';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $delete = db()->prepare('DELETE FROM users WHERE id = :userId');
    $delete->execute(['userId' => $user['id']]);
    session_unset();
    session_destroy();
    redirect('login.php');
}

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MathPlay | Configurações</title>
  <link rel="icon" type="image/svg+xml" href="logo.svg" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <script defer src="assets/js/app.js"></script>
</head>
<body>
  <?php renderTopBar('configuracoes'); ?>

  <main class="page-shell">
    <?php if ($settingsError !== ''): ?>
      <div hidden data-alert-message="<?= htmlspecialchars($settingsError, ENT_QUOTES, 'UTF-8') ?>"></div>
    <?php endif; ?>
    <?php if ($settingsMessage !== ''): ?>
      <div hidden data-alert-message="<?= htmlspecialchars($settingsMessage, ENT_QUOTES, 'UTF-8') ?>"></div>
    <?php endif; ?>
    <div class="settings-box">
      <div class="settings-header">Configurações</div>

      <form class="settings-form" method="post">
        <div class="field-group full">
          <label for="email">E-mail</label>
          <input id="email" name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" required />
        </div>

        <div class="field-group">
          <label for="name">Nome</label>
          <input id="name" name="name" type="text" maxlength="15" value="<?= htmlspecialchars($user['name']) ?>" required />
        </div>

        <div class="field-group">
          <label for="password">Senha</label>
          <div class="password-input-wrap">
            <input id="password" name="password" type="password" placeholder="Nova senha" minlength="6" maxlength="20" data-password-length-check data-min-length="6" aria-describedby="password-length-hint" />
            <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Mostrar senha" aria-pressed="false">
              <svg class="password-toggle__show" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="password-toggle__hide" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a15 15 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10 10 0 0 0 4-.8"/></svg>
            </button>
          </div>
          <small class="field-hint" id="password-length-hint" data-password-length-hint aria-live="polite">Deixe em branco para manter a senha atual. A nova senha deve ter de 6 a 20 caracteres.</small>
        </div>

        <div class="field-group full">
          <label>Tipo de usuário</label>
          <div class="role-choice">
            <label>
              <input type="radio" name="role" value="aluno" <?= $user['role'] === 'aluno' ? 'checked' : '' ?> />
              <span>Aluno</span>
            </label>
            <label>
              <input type="radio" name="role" value="professor" <?= $user['role'] === 'professor' ? 'checked' : '' ?> />
              <span>Professor</span>
            </label>
          </div>
        </div>

        <div class="field-group full settings-actions">
          <button type="submit" name="settings_update" value="1" class="primary-btn">Salvar edições</button>
          <button type="submit" name="delete_account" value="1" class="danger-btn" data-confirm-message="Tem certeza que deseja excluir permanentemente sua conta?">Excluir conta</button>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
