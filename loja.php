<?php
require __DIR__ . '/includes/app.php';
requireAuth();

$user = currentUser();
$avatar = renderAvatarDisplay((int)$user['id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MathPlay | Loja</title>
  <link rel="icon" type="image/svg+xml" href="logo.svg" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <script defer src="assets/js/app.js"></script>
</head>
<body>
  <?php renderTopBar('loja'); ?>

  <main class="page-shell">
    <div class="store-layout">
      <aside class="store-panel">
        <div class="store-panel__profile">
          <a href="inicio.php" class="primary-btn store-back" aria-label="Voltar"><span class="standard-only">Voltar</span><span class="emoji-only" aria-hidden="true">←</span></a>
          <div class="store-panel__avatar avatar-stage" data-avatar-stage style="--avatar-color:<?= htmlspecialchars($avatar['color']['color'] ?? '#ffcf4a') ?>;">
            <span class="avatar-star" aria-hidden="true"></span>
            <img class="avatar-face" src="<?= htmlspecialchars($avatar['face']['asset_path'] ?? 'rosto1.svg') ?>" alt="Rosto do avatar" />
          </div>
        </div>
      </aside>

      <section class="section-box" style="padding: 1.5rem;">
        <h2 class="section-title store-header">Loja</h2>
        <div class="store-category-grid">
          <a href="cores.php" class="store-category-card">
            <span class="store-category-card__swatch" aria-hidden="true"></span>
            <strong>Cores</strong>
            <span class="standard-only">Escolha uma cor para sua estrela!</span>
          </a>
          <a href="rostos.php" class="store-category-card">
            <img src="rosto1.svg" alt="" />
            <strong>Rostos</strong>
            <span class="standard-only">Personalize o rosto do seu avatar!</span>
          </a>
        </div>
      </section>
    </div>
  </main>
</body>
</html>
