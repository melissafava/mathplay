<?php
require __DIR__ . '/includes/app.php';
requireAuth();

$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MathPlay | Fracionando</title>
  <link rel="icon" type="image/svg+xml" href="logo.svg" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <script defer src="assets/js/app.js"></script>
</head>
<body>
  <header class="topbar game-topbar">
    <div class="topbar__brand">
      <img src="logobranca.svg" alt="Logo MathPlay" />
    </div>
    <div class="topbar__actions">
      <a href="inicio.php" class="topbar__logout" data-exit-game aria-label="Sair do jogo"><span class="standard-only">Sair do Jogo</span><img class="emoji-only icon-image" src="sair.svg" alt="" aria-hidden="true"></a>
    </div>
  </header>

  <main class="game-shell" data-game="fracionando" data-game-name="FRACIONANDO" data-csrf-token="<?= htmlspecialchars($csrf) ?>">
    <div class="game-main-grid">
      <section class="game-play-area">
        <div class="game-panel game-panel--fraction">
          <div class="game-panel__top">
            <div class="game-panel__title">QUAL FRAÇÃO É?</div>
          </div>
          <div class="game-panel__body">
            <div class="fraction-chart" id="fractionChart" role="img" aria-label="Gráfico de fração"></div>
            <div class="result-box" id="resultBox" aria-live="polite"></div>
            <div class="game-score-row">
              <span><span class="standard-only">Acertos: </span><span class="emoji-only">✅ </span><strong id="scoreValue">0</strong></span>
              <span><span class="standard-only">Estrelas: </span><img class="emoji-only icon-image" src="estrela.svg" alt="" aria-hidden="true"><strong id="earnedStarsValue">0</strong></span>
            </div>
          </div>
        </div>

        <div class="game-timer">
          <div class="game-time-label"><span class="standard-only">TEMPO: </span><span class="emoji-only">⏱️ </span><strong><span id="timeValue">30</span><span class="standard-only"> segundos</span></strong></div>
          <div class="timer-track" role="progressbar" id="timerBar" aria-label="Tempo restante" aria-valuemin="0" aria-valuemax="30" aria-valuenow="30">
            <span class="timer-track__fill" id="timerFill"></span>
          </div>
          <div class="timer-stars" aria-label="Estrelas disponíveis nesta questão">
            <?php for ($star = 0; $star < 3; $star++): ?>
              <img src="estrela.svg" alt="" aria-hidden="true" data-timer-star />
            <?php endfor; ?>
          </div>
        </div>
      </section>

      <section class="fraction-options" id="fractionOptions" aria-label="Escolha a fração correta"></section>
    </div>
  </main>

  <section class="game-over" id="gameOver" hidden role="dialog" aria-modal="true" aria-labelledby="gameOverTitle">
    <div class="game-over__card">
      <h2 id="gameOverTitle">Fim de Jogo</h2>
      <p id="gameOverFeedback" class="game-over__feedback" hidden></p>
      <p id="gameOverSummary"><span class="standard-only" data-game-summary-standard></span><span class="emoji-only"><span data-game-summary-correct></span> ✅ · <span data-game-summary-stars></span> <img class="icon-image" src="estrela.svg" alt="" aria-hidden="true"></span></p>
      <p id="saveStatus" class="save-status" aria-live="polite">Salvando sua partida...</p>
      <button type="button" class="secondary-btn" id="retrySave" hidden>Tentar salvar novamente</button>
      <div class="game-over__actions">
        <button type="button" class="primary-btn" id="restartGame" disabled>Reiniciar</button>
        <button type="button" class="danger-btn" id="homeAfterGame" disabled>Página Inicial</button>
      </div>
    </div>
  </section>
</body>
</html>
