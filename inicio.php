<?php
require __DIR__ . '/includes/app.php';
requireAuth();

$user = currentUser();
$progress = getUserProgress((int)$user['id']);
$history = getRecentHistory((int)$user['id'], 5);
$totals = getHistoryTotals((int)$user['id']);
$avatar = renderAvatarDisplay((int)$user['id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MathPlay | Início</title>
  <link rel="icon" type="image/svg+xml" href="logo.svg" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <script defer src="assets/js/app.js"></script>
</head>
<body>
  <?php renderTopBar('inicio'); ?>

  <main class="page-shell">
    <div class="dashboard-grid">
      <section class="welcome-panel">
        <div>
          <span style="font-size:0.95rem; opacity:0.9;">Bem-vindo(a)</span>
          <h1>Olá, <?= htmlspecialchars($user['name']) ?>!<span class="emoji-only"> 👋</span></h1>
        </div>

        <div class="avatar-box">
          <div class="avatar-figure" style="--avatar-color:<?= htmlspecialchars($avatar['color']['color'] ?? '#ffcf4a') ?>;">
            <span class="avatar-star" aria-hidden="true"></span>
            <img class="avatar-face" src="<?= htmlspecialchars($avatar['face']['asset_path'] ?? 'rosto1.svg') ?>" alt="Rosto do avatar" />
          </div>
        </div>

        <div class="stars-box">
          <strong>
            <span class="standard-only"><span class="star-count-icon" aria-hidden="true"></span>Você possui <?= (int)($progress['stars'] ?? 0) ?> estrelas!</span>
            <span class="emoji-only"><?= (int)($progress['stars'] ?? 0) ?><img class="icon-image" src="estrela.svg" alt="" aria-hidden="true">!</span>
          </strong>
          <span class="standard-only">Junte estrelas para personalizar o seu avatar!</span>
        </div>
      </section>

      <aside class="dashboard-card history-panel">
        <h2><span class="standard-only">Histórico</span><span class="emoji-only">⌛</span></h2>
        <div class="history-stats">
          <div class="history-stat">
            <small class="standard-only">Acertos</small>
            <strong><span class="standard-only"><?= $totals['correct'] ?></span><span class="emoji-only"><?= $totals['correct'] ?> ✅</span></strong>
          </div>
          <div class="history-stat" style="background:#ff6ca0;">
            <small class="standard-only">Erros</small>
            <strong><span class="standard-only"><?= $totals['wrong'] ?></span><span class="emoji-only"><?= $totals['wrong'] ?> ❌</span></strong>
          </div>
        </div>

        <div class="recent-history">
          <h3>Partidas recentes</h3>
          <?php if (!$history): ?>
            <p class="history-empty">Suas partidas aparecerão aqui.</p>
          <?php else: ?>
            <ul class="history-list">
              <?php foreach ($history as $entry): ?>
                <li>
                  <span>
                    <strong><?= htmlspecialchars($entry['game_name']) ?></strong>
                    <small><?= htmlspecialchars(date('d/m/Y H:i', strtotime($entry['played_at']))) ?></small>
                  </span>
                  <span class="history-result">
                    <span class="standard-only"><?= (int)$entry['correct_answers'] ?> acertos · <?= (int)$entry['wrong_answers'] ?> erros</span>
                    <span class="emoji-only"><?= (int)$entry['correct_answers'] ?> ✅ · <?= (int)$entry['wrong_answers'] ?> ❌</span><br />
                    <strong><span class="standard-only"><?= (int)$entry['stars_earned'] ?> ⭐</span><span class="emoji-only"><?= (int)$entry['stars_earned'] ?> <img class="icon-image" src="estrela.svg" alt="" aria-hidden="true"></span></strong>
                  </span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="quick-actions">
          <a class="action-btn" href="jogos.php"><span class="standard-only">Jogos</span><span class="emoji-only">🎮</span></a>
          <a class="action-btn secondary" href="loja.php"><span class="standard-only">Loja</span><span class="emoji-only">👕</span></a>
        </div>
      </aside>
    </div>
  </main>
</body>
</html>
