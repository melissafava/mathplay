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
          <h1>Olá, <?= htmlspecialchars($user['name']) ?>!</h1>
        </div>

        <div class="avatar-box">
          <div class="avatar-figure" style="--avatar-color:<?= htmlspecialchars($avatar['color']['color'] ?? '#ffcf4a') ?>;">
            <span class="avatar-star" aria-hidden="true"></span>
            <img class="avatar-face" src="<?= htmlspecialchars($avatar['face']['asset_path'] ?? 'rosto1.svg') ?>" alt="Rosto do avatar" />
          </div>
        </div>

        <div class="stars-box">
          <strong><span class="star-count-icon" aria-hidden="true"></span>Você possui <?= (int)($progress['stars'] ?? 0) ?> estrelas!</strong>
          <span>Junte estrelas para personalizar o seu avatar!</span>
        </div>
      </section>

      <aside class="dashboard-card history-panel">
        <h2>Histórico</h2>
        <div class="history-stats">
          <div class="history-stat">
            <small>Acertos</small>
            <strong><?= $totals['correct'] ?></strong>
          </div>
          <div class="history-stat" style="background:#ff6ca0;">
            <small>Erros</small>
            <strong><?= $totals['wrong'] ?></strong>
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
                    <?= (int)$entry['correct_answers'] ?> acertos · <?= (int)$entry['wrong_answers'] ?> erros<br />
                    <strong><?= (int)$entry['stars_earned'] ?> ⭐</strong>
                  </span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="quick-actions">
          <a class="action-btn" href="jogos.php">Jogos</a>
          <a class="action-btn secondary" href="loja.php">Loja</a>
        </div>
      </aside>
    </div>
  </main>
</body>
</html>
