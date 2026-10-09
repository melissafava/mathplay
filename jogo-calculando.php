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
  <title>MathPlay | Calculando</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <script defer src="assets/js/app.js"></script>
</head>
<body>
  <header class="topbar game-topbar">
    <div class="topbar__brand">
      <img src="logobranca.svg" alt="Logo MathPlay" />
    </div>
    <div class="topbar__actions">
      <a href="inicio.php" class="topbar__logout" data-exit-game>Sair do Jogo</a>
    </div>
  </header>

  <main class="game-shell" data-game="calculando" data-game-name="CALCULANDO" data-csrf-token="<?= htmlspecialchars($csrf) ?>">
    <div class="game-main-grid">
      <section class="game-play-area">
        <div class="game-panel">
          <div class="game-panel__top">
            <div class="game-panel__title">CALCULANDO</div>
          </div>
          <div class="game-panel__body">
            <div class="equation" id="exerciseText" aria-live="polite">8 × 12 =</div>
            <label class="visually-hidden" for="answerInput">Sua resposta</label>
            <input class="answer-input" id="answerInput" type="text" inputmode="numeric" autocomplete="off" />
            <div class="result-box" id="resultBox" aria-live="polite"></div>
            <div class="game-score-row">
              <span>Acertos: <strong id="scoreValue">0</strong></span>
              <span>Estrelas: <strong id="earnedStarsValue">0</strong></span>
            </div>
          </div>
        </div>

        <div class="game-timer">
          <div class="game-time-label">TEMPO: <strong><span id="timeValue">30</span> segundos</strong></div>
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

      <section class="number-pad" aria-label="Teclado numérico">
        <?php foreach (['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'backspace', 'equals'] as $key): ?>
          <button type="button" class="keypad-key <?= $key === 'equals' ? 'keypad-key--action' : '' ?>" data-key="<?= htmlspecialchars($key) ?>"
            aria-label="<?= $key === 'backspace' ? 'Apagar último número' : ($key === 'equals' ? 'Confirmar resposta' : $key) ?>">
            <?= $key === 'backspace' ? '⌫' : ($key === 'equals' ? '=' : $key) ?>
          </button>
        <?php endforeach; ?>
      </section>
    </div>
  </main>

  <section class="game-over" id="gameOver" hidden role="dialog" aria-modal="true" aria-labelledby="gameOverTitle">
    <div class="game-over__card">
      <h2 id="gameOverTitle">Fim de Jogo</h2>
      <p id="gameOverSummary"></p>
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
