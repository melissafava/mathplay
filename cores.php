<?php
require __DIR__ . '/includes/app.php';
requireAuth();

$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) {
        $_SESSION['catalog_notice'] = catalogActionMessage('csrf');
    } else {
        $status = handleCatalogAction(
            (int)$user['id'],
            (int)($_POST['item_id'] ?? 0),
            'color',
            (string)($_POST['action'] ?? '')
        );
        $_SESSION['catalog_notice'] = catalogActionMessage($status);
    }
    redirect('cores.php');
}

$avatar = renderAvatarDisplay((int)$user['id']);
$items = getCatalogItems('color');
$notice = (string)($_SESSION['catalog_notice'] ?? '');
unset($_SESSION['catalog_notice']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MathPlay | Cores</title>
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
          <div class="catalog-actions">
            <button type="button" class="secondary-btn" data-preview-item disabled><span class="standard-only">Experimentar</span><span class="emoji-only">👉👕❔</span></button>
            <form method="post" data-catalog-form>
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>" />
              <input type="hidden" name="item_id" value="" data-selected-item />
              <input type="hidden" name="action" value="purchase" data-selected-action />
              <button type="submit" class="primary-btn" data-item-action disabled><span class="standard-only" data-item-action-label>Adicionar</span><span class="emoji-only" data-item-action-emoji>💸👕❕</span></button>
            </form>
          </div>
        </div>
      </aside>

      <section class="section-box catalog-section">
        <h2 class="section-title catalog-header">Cores</h2>
        <div class="catalog-grid">
          <?php foreach ($items as $item): ?>
            <button type="button" class="catalog-card catalog-option" data-catalog-option
              data-item-id="<?= (int)$item['id'] ?>"
              data-item-color="<?= htmlspecialchars($item['color'] ?? '#ffcf4a') ?>"
              data-item-face=""
              data-owned="<?= !empty($item['owned_user_id']) ? 'true' : 'false' ?>"
              aria-pressed="false">
              <span class="swatch" style="background:<?= htmlspecialchars($item['color'] ?? '#ffcf4a') ?>;" aria-hidden="true"></span>
              <span class="label"><?= htmlspecialchars($item['label']) ?></span>
              <span class="value"><span class="standard-only"><?= (int)$item['price'] ?> estrelas</span><span class="emoji-only"><?= (int)$item['price'] ?> <img class="icon-image" src="estrela.svg" alt="" aria-hidden="true"></span></span>
            </button>
          <?php endforeach; ?>
        </div>
      </section>
    </div>
  </main>
  <?php if ($notice !== ''): ?>
    <div hidden data-alert-message="<?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?>"></div>
  <?php endif; ?>
</body>
</html>
