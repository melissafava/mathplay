<?php
require __DIR__ . '/includes/app.php';
requireAuth();

$user = currentUser();
$settingsError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings_update'])) {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $role = in_array((string)($_POST['role'] ?? 'aluno'), ['aluno', 'professor'], true) ? $_POST['role'] : 'aluno';
    $password = (string)($_POST['password'] ?? '');

    if (!isNameLengthValid($name)) {
        $settingsError = 'O nome deve ter no máximo 15 caracteres.';
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
  <link rel="stylesheet" href="assets/css/style.css" />
  <script defer src="assets/js/app.js"></script>
</head>
<body>
  <?php renderTopBar('configuracoes'); ?>

  <main class="page-shell">
    <?php if ($settingsError !== ''): ?>
      <div hidden data-alert-message="<?= htmlspecialchars($settingsError, ENT_QUOTES, 'UTF-8') ?>"></div>
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
          <input id="password" name="password" type="password" placeholder="Nova senha" />
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
