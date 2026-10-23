document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const isAuthPage = body.classList.contains('auth-page');
  const themeToggle = document.getElementById('themeToggle');
  const emojiToggle = document.getElementById('emojiToggle');
  const fontButtons = document.querySelectorAll('[data-font]');
  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const input = document.getElementById(button.dataset.passwordToggle);
    if (!input) return;

    button.addEventListener('click', () => {
      const showPassword = input.type === 'password';
      input.type = showPassword ? 'text' : 'password';
      button.setAttribute('aria-label', showPassword ? 'Ocultar senha' : 'Mostrar senha');
      button.setAttribute('aria-pressed', String(showPassword));
    });
  });

  document.querySelectorAll('[data-password-length-check]').forEach((input) => {
    const hint = input.closest('.field-group')?.querySelector('[data-password-length-hint]');
    const minimumLength = Number(input.dataset.minLength || 5);
    const maximumLength = Number(input.maxLength || 20);
    const updatePasswordLength = () => {
      const length = Array.from(input.value).length;
      const valid = length >= minimumLength && length <= maximumLength;
      input.setCustomValidity(input.value !== '' && !valid
        ? `A senha deve ter entre ${minimumLength} e ${maximumLength} caracteres.`
        : '');
      if (hint) {
        if (input.value === '' && minimumLength === 6) {
          hint.textContent = 'Deixe em branco para manter a senha atual. A nova senha deve ter de 6 a 20 caracteres.';
        } else if (input.value === '') {
          hint.textContent = `Use de ${minimumLength} a ${maximumLength} caracteres.`;
        } else {
          hint.textContent = `${length}/${maximumLength} caracteres${valid ? '' : ` — a senha deve ter entre ${minimumLength} e ${maximumLength} caracteres.`}`;
        }
      }
    };
    input.addEventListener('input', updatePasswordLength);
    updatePasswordLength();
  });

  const applyEmojiMode = (enabled) => {
    body.classList.toggle('emoji-mode', enabled);
    localStorage.setItem('mathplay-emoji-mode', String(enabled));
    if (emojiToggle) emojiToggle.setAttribute('aria-pressed', String(enabled));
  };

  if (body.dataset.resetDefaults === 'true') {
    localStorage.setItem('mathplay-emoji-mode', 'false');
  }

  applyEmojiMode(localStorage.getItem('mathplay-emoji-mode') === 'true');

  if (emojiToggle) {
    emojiToggle.addEventListener('click', () => {
      applyEmojiMode(!body.classList.contains('emoji-mode'));
    });
  }

  const applyTheme = (theme) => {
    body.classList.toggle('theme-dark', theme === 'dark');
    localStorage.setItem('mathplay-theme', theme);
    if (themeToggle) {
      themeToggle.textContent = theme === 'dark' ? '☾' : '☼';
    }
  };

  if (body.dataset.resetDefaults === 'true') {
    localStorage.setItem('mathplay-theme', 'light');
    localStorage.setItem('mathplay-font-scale', '1');
  }

  if (isAuthPage) {
    body.classList.remove('theme-dark');
  } else {
    applyTheme(localStorage.getItem('mathplay-theme') || 'light');
  }

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      applyTheme(body.classList.contains('theme-dark') ? 'light' : 'dark');
    });
  }

  const applyFontScale = (scale, persist = true) => {
    document.documentElement.style.fontSize = `${scale * 100}%`;
    if (persist) localStorage.setItem('mathplay-font-scale', String(scale));
  };

  const savedScale = Number(localStorage.getItem('mathplay-font-scale') || '1');
  const fontScale = isAuthPage
    ? 1
    : (Number.isFinite(savedScale) ? Math.min(1.7, Math.max(0.8, savedScale)) : 1);
  applyFontScale(fontScale, !isAuthPage);

  fontButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const current = Number(localStorage.getItem('mathplay-font-scale') || '1');
      const next = button.dataset.font === 'increase'
        ? Math.min(current + 0.15, 1.7)
        : Math.max(current - 0.15, 0.8);
      applyFontScale(Number(next.toFixed(2)));
    });
  });

  const modal = document.createElement('div');
  modal.className = 'message-modal';
  modal.hidden = true;
  modal.innerHTML = `
    <div class="message-modal__card" role="dialog" aria-modal="true" aria-labelledby="messageModalTitle" aria-describedby="messageModalText">
      <h2 id="messageModalTitle">Aviso</h2>
      <p id="messageModalText"></p>
      <div class="message-modal__actions">
        <button type="button" class="secondary-btn" data-modal-cancel hidden>Cancelar</button>
        <button type="button" class="primary-btn" data-modal-confirm>Entendi</button>
      </div>
    </div>
  `;
  body.append(modal);

  const modalCard = modal.querySelector('.message-modal__card');
  const modalTitle = modal.querySelector('#messageModalTitle');
  const modalText = modal.querySelector('#messageModalText');
  const modalCancel = modal.querySelector('[data-modal-cancel]');
  const modalConfirm = modal.querySelector('[data-modal-confirm]');
  let modalCloseTimer = null;
  let modalReturnFocus = null;
  let modalConfirmAction = null;

  const closeModal = (confirmed = false) => {
    if (modal.hidden) return;
    modal.classList.remove('is-visible');
    const confirmAction = modalConfirmAction;
    modalConfirmAction = null;
    modalCloseTimer = setTimeout(() => {
      modal.hidden = true;
      if (modalReturnFocus instanceof HTMLElement) modalReturnFocus.focus();
      modalReturnFocus = null;
    }, 180);
    if (confirmed && confirmAction) confirmAction();
  };

  const showModal = (message, { confirmation = false, onConfirm = null } = {}) => {
    clearTimeout(modalCloseTimer);
    modalReturnFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    modalConfirmAction = onConfirm;
    modalTitle.textContent = confirmation ? 'Confirmar ação' : 'Aviso';
    modalText.textContent = message;
    modalCancel.hidden = !confirmation;
    modalConfirm.textContent = confirmation ? 'Confirmar' : 'Entendi';
    modal.hidden = false;
    modal.classList.remove('is-visible');
    requestAnimationFrame(() => modal.classList.add('is-visible'));
    modalConfirm.focus();
  };

  modalConfirm.addEventListener('click', () => closeModal(true));
  modalCancel.addEventListener('click', () => closeModal());
  modal.addEventListener('click', (event) => {
    if (event.target === modal) closeModal();
  });
  document.addEventListener('keydown', (event) => {
    if (modal.hidden) return;
    if (event.key === 'Escape') {
      event.preventDefault();
      closeModal();
    } else if (event.key === 'Tab') {
      const focusable = [...modalCard.querySelectorAll('button:not([hidden])')];
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    }
  });

  document.querySelectorAll('[data-confirm-message]').forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      const form = button.form;
      if (!form) return;
      showModal(button.dataset.confirmMessage, {
        confirmation: true,
        onConfirm: () => form.requestSubmit(button),
      });
    });
  });

  const alertMessage = document.querySelector('[data-alert-message]');
  if (alertMessage?.dataset.alertMessage) {
    showModal(alertMessage.dataset.alertMessage);
  }

  const catalogOptions = [...document.querySelectorAll('[data-catalog-option]')];
  if (catalogOptions.length > 0) {
    const stage = document.querySelector('[data-avatar-stage]');
    const face = stage?.querySelector('.avatar-face');
    const selectedItem = document.querySelector('[data-selected-item]');
    const selectedAction = document.querySelector('[data-selected-action]');
    const actionButton = document.querySelector('[data-item-action]');
    const previewButton = document.querySelector('[data-preview-item]');
    let selection = null;

    const selectOption = (option) => {
      selection = selection === option ? null : option;
      catalogOptions.forEach((candidate) => {
        const isSelected = candidate === selection;
        candidate.classList.toggle('selected', isSelected);
        candidate.setAttribute('aria-pressed', String(isSelected));
      });

      const hasSelection = Boolean(selection);
      if (selectedItem) selectedItem.value = hasSelection ? selection.dataset.itemId : '';
      if (actionButton) {
        actionButton.disabled = !hasSelection;
        const label = actionButton.querySelector('[data-item-action-label]');
        const emojiLabel = actionButton.querySelector('[data-item-action-emoji]');
        if (label) label.textContent = hasSelection && selection.dataset.owned === 'true' ? 'Vestir' : 'Adicionar';
        if (emojiLabel) emojiLabel.textContent = hasSelection && selection.dataset.owned === 'true' ? '👕❕' : '💸👕❕';
      }
      if (selectedAction) {
        selectedAction.value = hasSelection && selection.dataset.owned === 'true' ? 'equip' : 'purchase';
      }
      if (previewButton) previewButton.disabled = !hasSelection;
    };

    catalogOptions.forEach((option) => {
      option.addEventListener('click', () => selectOption(option));
    });

    previewButton?.addEventListener('click', () => {
      if (!selection || !stage) return;

      const previewColor = selection.dataset.itemColor;
      const previewFace = selection.dataset.itemFace;
      if (previewColor) stage.style.setProperty('--avatar-color', previewColor);
      if (previewFace && face) face.src = previewFace;
    });

    document.querySelector('[data-catalog-form]')?.addEventListener('submit', (event) => {
      if (!selection) event.preventDefault();
    });
  }

  const gameRoot = document.querySelector('[data-game]');
  if (!gameRoot) return;

  const gameType = gameRoot.dataset.game;
  const gameName = gameRoot.dataset.gameName;
  const csrfToken = gameRoot.dataset.csrfToken;
  const answerInput = document.getElementById('answerInput');
  const resultBox = document.getElementById('resultBox');
  const exercise = document.getElementById('exerciseText');
  const fractionChart = document.getElementById('fractionChart');
  const fractionOptions = document.getElementById('fractionOptions');
  const scoreValue = document.getElementById('scoreValue');
  const earnedStarsValue = document.getElementById('earnedStarsValue');
  const timeValue = document.getElementById('timeValue');
  const timerFill = document.getElementById('timerFill');
  const timerBar = document.getElementById('timerBar');
  const timerStars = [...document.querySelectorAll('[data-timer-star]')];
  const gameOver = document.getElementById('gameOver');
  const gameOverFeedback = document.getElementById('gameOverFeedback');
  const gameOverSummary = document.getElementById('gameOverSummary');
  const standardGameSummary = gameOverSummary?.querySelector('[data-game-summary-standard]');
  const emojiGameSummaryCorrect = gameOverSummary?.querySelector('[data-game-summary-correct]');
  const emojiGameSummaryStars = gameOverSummary?.querySelector('[data-game-summary-stars]');
  const saveStatus = document.getElementById('saveStatus');
  const retrySave = document.getElementById('retrySave');
  const restartButton = document.getElementById('restartGame');
  const homeButton = document.getElementById('homeAfterGame');
  const keypad = document.querySelector('.number-pad');
  const exitLink = document.querySelector('[data-exit-game]');

  let correctCount = 0;
  let wrongCount = 0;
  let starsEarned = 0;
  let durationSeconds = 0;
  let remainingSeconds = 30;
  let questionStartedAt = Date.now();
  let currentAnswer = null;
  let currentFraction = null;
  let timerId = null;
  let nextQuestionId = null;
  let gameEnded = false;
  let resolvingAnswer = false;
  let currentRunId = '';
  let finalPayload = null;
  let exitingToHome = false;

  const newRunId = () => [...crypto.getRandomValues(new Uint8Array(16))]
    .map((value) => value.toString(16).padStart(2, '0'))
    .join('');

  const availableStars = (seconds) => {
    if (seconds >= 20) return 3;
    if (seconds >= 10) return 2;
    return seconds > 0 ? 1 : 0;
  };

  const renderTimer = () => {
    const seconds = Math.max(0, Math.ceil(remainingSeconds));
    const stars = availableStars(seconds);
    if (timeValue) timeValue.textContent = String(seconds);
    if (timerFill) timerFill.style.width = `${Math.max(0, remainingSeconds / 30) * 100}%`;
    if (timerBar) timerBar.setAttribute('aria-valuenow', String(seconds));
    timerStars.forEach((star, index) => {
      star.classList.toggle('is-inactive', index >= stars);
    });
  };

  const updateScore = () => {
    if (scoreValue) scoreValue.textContent = String(correctCount);
    if (earnedStarsValue) earnedStarsValue.textContent = String(starsEarned);
  };

  const makeCalculation = () => {
    const stage = correctCount < 7 ? 0 : (correctCount < 14 ? 1 : 2);
    const operation = ['+', '-', '×', '÷'][correctCount % 4];
    let first;
    let second;
    let answer;

    if (operation === '+') {
      const ranges = [[1, 20, 1, 20], [30, 99, 20, 99], [100, 199, 50, 150]];
      const range = ranges[stage];
      first = randomBetween(range[0], range[1]);
      second = randomBetween(range[2], range[3]);
      answer = first + second;
    } else if (operation === '-') {
      const ranges = [[5, 25, 1, 20], [50, 99, 10, 65], [150, 300, 50, 160]];
      const range = ranges[stage];
      first = randomBetween(range[0], range[1]);
      second = randomBetween(range[2], Math.min(range[3], first));
      answer = first - second;
    } else if (operation === '×') {
      const ranges = [[2, 9, 2, 9], [8, 12, 10, 20], [11, 19, 20, 30]];
      const range = ranges[stage];
      first = randomBetween(range[0], range[1]);
      second = randomBetween(range[2], range[3]);
      answer = first * second;
    } else {
      const ranges = [[2, 9, 2, 9], [4, 12, 5, 20], [8, 19, 10, 30]];
      const range = ranges[stage];
      answer = randomBetween(range[0], range[1]);
      second = randomBetween(range[2], range[3]);
      first = answer * second;
    }

    return { text: `${first} ${operation} ${second} =`, answer };
  };

  const fractionPool = [
    [1, 2], [1, 3], [1, 4], [2, 3], [3, 4],
    [2, 5], [3, 5], [4, 5], [1, 6], [5, 6],
  ];

  const renderPieChart = (numerator, denominator) => {
    if (!fractionChart) return;
    const center = 100;
    const radius = 84;
    const sectors = [];

    for (let index = 0; index < denominator; index += 1) {
      const start = (-90 + index * 360 / denominator) * Math.PI / 180;
      const end = (-90 + (index + 1) * 360 / denominator) * Math.PI / 180;
      const startX = center + radius * Math.cos(start);
      const startY = center + radius * Math.sin(start);
      const endX = center + radius * Math.cos(end);
      const endY = center + radius * Math.sin(end);
      const largeArc = 360 / denominator > 180 ? 1 : 0;
      const fill = index < numerator ? '#ff914d' : '#ffffff';
      sectors.push(
        `<path d="M ${center} ${center} L ${startX} ${startY} A ${radius} ${radius} 0 ${largeArc} 1 ${endX} ${endY} Z" fill="${fill}" stroke="#111111" stroke-width="2"/>`
      );
    }

    fractionChart.innerHTML = `<svg viewBox="0 0 200 200" aria-hidden="true">${sectors.join('')}</svg>`;
    fractionChart.setAttribute('aria-label', `Gráfico com ${numerator} de ${denominator} partes pintadas`);
  };

  const renderFractionQuestion = () => {
    const selectedIndex = Math.floor(Math.random() * fractionPool.length);
    currentFraction = fractionPool[selectedIndex];
    currentAnswer = 1;
    renderPieChart(currentFraction[0], currentFraction[1]);

    const choices = [currentFraction];
    while (choices.length < 4) {
      const candidate = fractionPool[Math.floor(Math.random() * fractionPool.length)];
      if (!choices.some(([n, d]) => n === candidate[0] && d === candidate[1])) {
        choices.push(candidate);
      }
    }
    choices.sort(() => Math.random() - 0.5);

    fractionOptions.innerHTML = choices.map(([numerator, denominator]) => (
      `<button type="button" class="fraction-choice" data-numerator="${numerator}" data-denominator="${denominator}" aria-label="${numerator} sobre ${denominator}">
        <span>${numerator}</span><span class="fraction-rule"></span><span>${denominator}</span>
      </button>`
    )).join('');
  };

  const renderQuestion = () => {
    resolvingAnswer = false;
    remainingSeconds = 30;
    questionStartedAt = Date.now();
    if (resultBox) {
      resultBox.textContent = '';
      resultBox.classList.remove('result-box--success');
    }

    if (gameType === 'calculando') {
      const question = makeCalculation();
      currentAnswer = question.answer;
      if (exercise) exercise.textContent = question.text;
      if (answerInput) {
        answerInput.value = '';
        answerInput.disabled = false;
      }
      keypad?.querySelectorAll('button').forEach((button) => { button.disabled = false; });
      answerInput?.focus();
    } else {
      renderFractionQuestion();
    }

    renderTimer();
    clearInterval(timerId);
    timerId = setInterval(() => {
      if (gameEnded || resolvingAnswer) return;
      remainingSeconds = Math.max(0, 30 - (Date.now() - questionStartedAt) / 1000);
      renderTimer();
      if (remainingSeconds <= 0) {
        durationSeconds += 30;
        wrongCount = 1;
        finishGame('time');
      }
    }, 100);
  };

  const disableGameControls = () => {
    if (answerInput) answerInput.disabled = true;
    keypad?.querySelectorAll('button').forEach((button) => { button.disabled = true; });
    fractionOptions?.querySelectorAll('button').forEach((button) => { button.disabled = true; });
  };

  const saveResult = async () => {
    if (!finalPayload || !saveStatus) return false;
    saveStatus.textContent = 'Salvando sua partida...';
    retrySave.hidden = true;

    try {
      const response = await fetch('save-game.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken,
        },
        body: JSON.stringify(finalPayload),
      });
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'Não foi possível salvar esta partida.');

      saveStatus.textContent = 'Partida salva! Suas estrelas e seu histórico foram atualizados.';
      restartButton.disabled = false;
      homeButton.disabled = false;
      if (exitingToHome) window.location.href = 'inicio.php';
      return true;
    } catch (error) {
      saveStatus.textContent = error instanceof Error ? error.message : 'Não foi possível salvar esta partida.';
      retrySave.hidden = false;
      restartButton.disabled = false;
      homeButton.disabled = false;
      if (exitingToHome) {
        gameOverSummary.textContent = 'A partida não pôde ser salva ao sair. Tente novamente ou escolha uma opção abaixo.';
        gameOver.hidden = false;
      }
      return false;
    }
  };

  const finishGame = (reason, exit = false) => {
    if (gameEnded) return;
    gameEnded = true;
    exitingToHome = exit;
    clearInterval(timerId);
    clearTimeout(nextQuestionId);
    disableGameControls();
    remainingSeconds = reason === 'time' ? 0 : remainingSeconds;
    renderTimer();

    finalPayload = {
      game: gameName,
      correct: correctCount,
      wrong: wrongCount,
      stars: starsEarned,
      duration: Math.max(0, Math.round(durationSeconds)),
      runId: currentRunId,
    };

    if (exit) {
      saveResult();
      return;
    }

    const feedbackMessage = reason === 'wrong'
      ? `Essa não! A resposta era ${gameType === 'fracionando' ? `${currentFraction[0]}/${currentFraction[1]}` : currentAnswer}. Mais sorte da próxima vez!`
      : reason === 'time'
        ? `O tempo acabou! A resposta era ${gameType === 'fracionando' ? `${currentFraction[0]}/${currentFraction[1]}` : currentAnswer}. Mais sorte da próxima vez!`
        : '';
    if (feedbackMessage && resultBox) resultBox.textContent = feedbackMessage;
    gameOverFeedback.textContent = feedbackMessage;
    gameOverFeedback.hidden = !feedbackMessage;
    if (standardGameSummary) standardGameSummary.textContent = `${correctCount} acertos e ${starsEarned} estrelas conquistadas nesta partida.`;
    if (emojiGameSummaryCorrect) emojiGameSummaryCorrect.textContent = String(correctCount);
    if (emojiGameSummaryStars) emojiGameSummaryStars.textContent = String(starsEarned);
    gameOver.hidden = false;
    restartButton.disabled = true;
    homeButton.disabled = true;
    saveResult();
  };

  const submitAnswer = (answer) => {
    if (gameEnded || resolvingAnswer || remainingSeconds <= 0) return;
    const elapsed = Math.min(30, (Date.now() - questionStartedAt) / 1000);
    remainingSeconds = Math.max(0, 30 - elapsed);
    renderTimer();
    if (remainingSeconds <= 0) {
      durationSeconds += 30;
      wrongCount = 1;
      finishGame('time');
      return;
    }
    durationSeconds += elapsed;
    clearInterval(timerId);
    resolvingAnswer = true;

    if (Number(answer) === Number(currentAnswer)) {
      correctCount += 1;
      starsEarned += availableStars(Math.ceil(remainingSeconds));
      updateScore();
      if (resultBox) {
        resultBox.textContent = 'Correto!';
        resultBox.classList.add('result-box--success');
      }
      nextQuestionId = setTimeout(renderQuestion, 350);
      return;
    }

    wrongCount = 1;
    finishGame('wrong');
  };

  if (answerInput) {
    answerInput.addEventListener('input', () => {
      answerInput.value = answerInput.value.replace(/\D/g, '');
    });
    answerInput.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        submitAnswer(answerInput.value);
      }
    });
  }

  keypad?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-key]');
    if (!button || !answerInput || gameEnded || resolvingAnswer) return;
    const key = button.dataset.key;
    if (key === 'equals') {
      submitAnswer(answerInput.value);
    } else if (key === 'backspace') {
      answerInput.value = answerInput.value.slice(0, -1);
      answerInput.focus();
    } else if (/^\d$/.test(key)) {
      answerInput.value += key;
      answerInput.focus();
    }
  });

  fractionOptions?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-numerator]');
    if (!button || gameEnded || resolvingAnswer) return;
    const isCorrect = Number(button.dataset.numerator) === currentFraction[0]
      && Number(button.dataset.denominator) === currentFraction[1];
    submitAnswer(isCorrect ? 1 : 0);
  });

  retrySave?.addEventListener('click', saveResult);

  restartButton?.addEventListener('click', () => {
    clearTimeout(nextQuestionId);
    correctCount = 0;
    wrongCount = 0;
    starsEarned = 0;
    durationSeconds = 0;
    remainingSeconds = 30;
    gameEnded = false;
    resolvingAnswer = false;
    finalPayload = null;
    exitingToHome = false;
    currentRunId = newRunId();
    gameOver.hidden = true;
    updateScore();
    renderQuestion();
  });

  homeButton?.addEventListener('click', () => {
    window.location.href = 'inicio.php';
  });

  exitLink?.addEventListener('click', (event) => {
    event.preventDefault();
    if (gameEnded) return;
    durationSeconds += Math.min(30, (Date.now() - questionStartedAt) / 1000);
    finishGame('leave', true);
  });

  const randomBetween = (minimum, maximum) => (
    Math.floor(Math.random() * (maximum - minimum + 1)) + minimum
  );

  currentRunId = newRunId();
  updateScore();
  renderQuestion();
});
