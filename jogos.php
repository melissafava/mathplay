<?php
require __DIR__ . '/includes/app.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MathPlay | Jogos</title>
  <link rel="icon" type="image/svg+xml" href="logo.svg" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <script defer src="assets/js/app.js"></script>
</head>
<body>
  <?php renderTopBar('jogos'); ?>

  <main class="page-shell">
    <div class="section-banner"><span class="standard-only">JOGOS</span><span class="emoji-only">🎮</span></div>

    <div class="game-grid">
      <a href="jogo-calculando.php" class="game-card">
        <img src="calculando.png" alt="" />
        <span><span class="standard-only">CALCULANDO</span><span class="emoji-only">CALCULANDO 🎮</span></span>
      </a>
      <a href="jogo-fracionando.php" class="game-card">
        <img src="fracionando.png" alt="" />
        <span><span class="standard-only">FRACIONANDO</span><span class="emoji-only">FRACIONANDO 🎮</span></span>
      </a>
    </div>
  </main>
</body>
</html>
