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
  <link rel="stylesheet" href="assets/css/style.css" />
  <script defer src="assets/js/app.js"></script>
</head>
<body>
  <?php renderTopBar('jogos'); ?>

  <main class="page-shell">
    <div class="section-banner">JOGOS</div>

    <div class="game-grid">
      <a href="jogo-calculando.php" class="game-card">
        <img src="calculando.png" alt="" />
        <span>CALCULANDO</span>
      </a>
      <a href="jogo-fracionando.php" class="game-card">
        <img src="fracionando.png" alt="" />
        <span>FRACIONANDO</span>
      </a>
    </div>
  </main>
</body>
</html>
