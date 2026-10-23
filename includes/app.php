<?php
require_once __DIR__ . '/../config.php';

session_start();

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $pdo = createDatabaseConnection();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = ON;');
            $schemaPath = __DIR__ . '/../database/schema.sql';
        } else {
            $schemaPath = __DIR__ . '/../database/schema.mysql.sql';
        }

        $sql = file_get_contents($schemaPath);
        if ($sql === false) {
            throw new RuntimeException('Não foi possível carregar o esquema do banco de dados.');
        }

        if ($driver === 'mysql') {
            foreach (explode(';', $sql) as $statement) {
                if (trim($statement) !== '') {
                    $pdo->exec($statement);
                }
            }
            $historyColumns = array_column($pdo->query('SHOW COLUMNS FROM game_history')->fetchAll(), 'Field');
        } else {
            $pdo->exec($sql);
            $historyColumns = array_column($pdo->query('PRAGMA table_info(game_history)')->fetchAll(), 'name');
        }

        if (!in_array('stars_earned', $historyColumns, true)) {
            $pdo->exec('ALTER TABLE game_history ADD COLUMN stars_earned INTEGER NOT NULL DEFAULT 0');
        }
        if (!in_array('duration_seconds', $historyColumns, true)) {
            $pdo->exec('ALTER TABLE game_history ADD COLUMN duration_seconds INTEGER NOT NULL DEFAULT 0');
        }
        if (!in_array('game_run_id', $historyColumns, true)) {
            $runIdType = $driver === 'mysql' ? 'CHAR(32)' : 'TEXT';
            $pdo->exec('ALTER TABLE game_history ADD COLUMN game_run_id ' . $runIdType);
        }

        if ($driver === 'sqlite') {
            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_game_history_user_played ON game_history (user_id, played_at)');
            $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_game_history_user_run ON game_history (user_id, game_run_id)');
        } else {
            $indexes = array_column($pdo->query('SHOW INDEX FROM game_history')->fetchAll(), 'Key_name');
            if (!in_array('idx_game_history_user_played', $indexes, true)) {
                $pdo->exec('CREATE INDEX idx_game_history_user_played ON game_history (user_id, played_at)');
            }
            if (!in_array('idx_game_history_user_run', $indexes, true)) {
                $pdo->exec('CREATE UNIQUE INDEX idx_game_history_user_run ON game_history (user_id, game_run_id)');
            }
        }
    }

    return $pdo;
}

function insertIgnorePrefix(): string
{
    return db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? 'INSERT IGNORE INTO' : 'INSERT OR IGNORE INTO';
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool
{
    return !empty($_SESSION['csrf_token']) && hash_equals((string)$_SESSION['csrf_token'], $token);
}

function isNameLengthValid(string $name): bool
{
    $matchedCharacters = preg_match_all('/./us', $name, $characters);
    return $matchedCharacters !== false && $matchedCharacters <= 15;
}

function isPasswordLengthValid(string $password, int $minimumLength = 5, int $maximumLength = 20): bool
{
    $matchedCharacters = preg_match_all('/./us', $password);
    return $matchedCharacters !== false
        && $matchedCharacters >= $minimumLength
        && $matchedCharacters <= $maximumLength;
}

function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }

    return $user;
}

function requireAuth(): void
{
    if (currentUser() === null) {
        redirect('login.php');
    }
}

function ensureUserProgress(int $userId): void
{
    $stmt = db()->prepare('SELECT 1 FROM user_progress WHERE user_id = :userId');
    $stmt->execute(['userId' => $userId]);
    if ($stmt->fetch()) {
        return;
    }

    $insert = db()->prepare('INSERT INTO user_progress (user_id, stars, xp, level, avatar_face, avatar_color) VALUES (:userId, 0, 0, 1, :face, :color)');
    $insert->execute([
        'userId' => $userId,
        'face' => 'rosto1',
        'color' => '#ffcf4a',
    ]);

    $settings = db()->prepare(insertIgnorePrefix() . ' user_settings (user_id, theme, font_scale) VALUES (:userId, :theme, :scale)');
    $settings->execute([
        'userId' => $userId,
        'theme' => 'light',
        'scale' => 1.0,
    ]);

    $defaultColorId = db()->prepare('SELECT id FROM catalog_items WHERE `type` = :type AND key_name = :key');
    $defaultColorId->execute(['type' => 'color', 'key' => 'amarelo']);
    $colorInfo = $defaultColorId->fetch();
    if ($colorInfo) {
        $defaultColorPurchase = db()->prepare(insertIgnorePrefix() . ' user_inventory (user_id, item_id, purchased_at, equipped) VALUES (:userId, :itemId, CURRENT_TIMESTAMP, 1)');
        $defaultColorPurchase->execute(['userId' => $userId, 'itemId' => $colorInfo['id']]);
    }

    $defaultFaceId = db()->prepare('SELECT id FROM catalog_items WHERE `type` = :type AND key_name = :key');
    $defaultFaceId->execute(['type' => 'face', 'key' => 'rosto1']);
    $faceInfo = $defaultFaceId->fetch();
    if ($faceInfo) {
        $defaultFacePurchase = db()->prepare(insertIgnorePrefix() . ' user_inventory (user_id, item_id, purchased_at, equipped) VALUES (:userId, :itemId, CURRENT_TIMESTAMP, 1)');
        $defaultFacePurchase->execute(['userId' => $userId, 'itemId' => $faceInfo['id']]);
    }
}

function getUserProgress(int $userId): array
{
    ensureUserProgress($userId);
    $stmt = db()->prepare('SELECT * FROM user_progress WHERE user_id = :userId');
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetch() ?: ['stars' => 0, 'xp' => 0, 'level' => 1, 'avatar_face' => 'rosto1', 'avatar_color' => '#ffcf4a'];
}

function getUserSettings(int $userId): array
{
    $stmt = db()->prepare('SELECT * FROM user_settings WHERE user_id = :userId');
    $stmt->execute(['userId' => $userId]);
    $settings = $stmt->fetch();
    if (!$settings) {
        $insert = db()->prepare('INSERT INTO user_settings (user_id, theme, font_scale) VALUES (:userId, :theme, :scale)');
        $insert->execute(['userId' => $userId, 'theme' => 'light', 'scale' => 1.0]);
        return ['theme' => 'light', 'font_scale' => 1.0];
    }

    return $settings;
}

function getCatalogItems(string $type): array
{
    $stmt = db()->prepare('SELECT c.*, i.equipped, i.user_id as owned_user_id FROM catalog_items c LEFT JOIN user_inventory i ON i.item_id = c.id AND i.user_id = :userId WHERE c.`type` = :type ORDER BY c.price ASC, c.label ASC');
    $userId = $_SESSION['user_id'] ?? 0;
    $stmt->execute(['userId' => $userId, 'type' => $type]);
    return $stmt->fetchAll();
}

function getInventoryForUser(int $userId, string $type): array
{
    $stmt = db()->prepare(
        'SELECT c.*, ui.equipped, ui.purchased_at, ui.user_id FROM catalog_items c INNER JOIN user_inventory ui ON ui.item_id = c.id WHERE c.`type` = :type AND ui.user_id = :userId ORDER BY c.price ASC, c.label ASC'
    );
    $stmt->execute(['type' => $type, 'userId' => $userId]);
    return $stmt->fetchAll();
}

function getEquippedFace(int $userId): array
{
    $stmt = db()->prepare('SELECT c.* FROM catalog_items c INNER JOIN user_inventory ui ON ui.item_id = c.id WHERE c.`type` = :type AND ui.user_id = :userId AND ui.equipped = 1 LIMIT 1');
    $stmt->execute(['type' => 'face', 'userId' => $userId]);
    $face = $stmt->fetch();
    return $face ?: ['asset_path' => 'rosto1.svg', 'label' => 'Rosto 1'];
}

function getEquippedColor(int $userId): array
{
    $stmt = db()->prepare('SELECT c.* FROM catalog_items c INNER JOIN user_inventory ui ON ui.item_id = c.id WHERE c.`type` = :type AND ui.user_id = :userId AND ui.equipped = 1 LIMIT 1');
    $stmt->execute(['type' => 'color', 'userId' => $userId]);
    $color = $stmt->fetch();
    return $color ?: ['color' => '#ffcf4a', 'label' => 'Amarelo'];
}

function handleCatalogAction(int $userId, int $itemId, string $type, string $action): string
{
    if (!in_array($type, ['color', 'face'], true) || !in_array($action, ['purchase', 'equip'], true)) {
        return 'invalid';
    }

    $catalog = db()->prepare('SELECT * FROM catalog_items WHERE id = :itemId AND `type` = :type');
    $catalog->execute(['itemId' => $itemId, 'type' => $type]);
    $item = $catalog->fetch();
    if (!$item) {
        return 'invalid';
    }

    $database = db();
    $database->beginTransaction();

    $inventory = $database->prepare('SELECT 1 FROM user_inventory WHERE user_id = :userId AND item_id = :itemId');
    $inventory->execute(['userId' => $userId, 'itemId' => $itemId]);
    $isOwned = (bool)$inventory->fetchColumn();

    if ($action === 'purchase') {
        if ($isOwned) {
            $database->commit();
            return 'owned';
        }

        $deduct = $database->prepare(
            'UPDATE user_progress SET stars = stars - :price
             WHERE user_id = :userId AND stars >= :price'
        );
        $deduct->execute(['price' => (int)$item['price'], 'userId' => $userId]);
        if ($deduct->rowCount() !== 1) {
            $database->rollBack();
            return 'insufficient';
        }

        $insert = $database->prepare(
            'INSERT INTO user_inventory (user_id, item_id, purchased_at, equipped)
             VALUES (:userId, :itemId, CURRENT_TIMESTAMP, 0)'
        );
        $insert->execute(['userId' => $userId, 'itemId' => $itemId]);
        $database->commit();
        return 'purchased';
    }

    if (!$isOwned) {
        $database->rollBack();
        return 'not_owned';
    }

    $unequip = $database->prepare(
        'UPDATE user_inventory SET equipped = 0
         WHERE user_id = :userId
           AND item_id IN (SELECT id FROM catalog_items WHERE `type` = :type)'
    );
    $unequip->execute(['userId' => $userId, 'type' => $type]);

    $equip = $database->prepare('UPDATE user_inventory SET equipped = 1 WHERE user_id = :userId AND item_id = :itemId');
    $equip->execute(['userId' => $userId, 'itemId' => $itemId]);

    if ($type === 'color') {
        $updateProgress = $database->prepare('UPDATE user_progress SET avatar_color = :value WHERE user_id = :userId');
        $updateProgress->execute(['value' => $item['color'], 'userId' => $userId]);
    } else {
        $updateProgress = $database->prepare('UPDATE user_progress SET avatar_face = :value WHERE user_id = :userId');
        $updateProgress->execute(['value' => $item['key_name'], 'userId' => $userId]);
    }

    $database->commit();
    return 'equipped';
}

function catalogActionMessage(string $status): string
{
    return match ($status) {
        'purchased' => 'Item comprado! Ele já está disponível no seu avatar :D',
        'equipped' => 'Item vestido no seu avatar!',
        'owned' => 'Este item já foi comprado. Escolha Vestir para equipá-lo.',
        'insufficient' => 'quantidade de estrelas insuficiente...',
        'not_owned' => 'Compre este item antes de vestir.',
        'invalid' => 'Selecione um item válido.',
        'csrf' => 'Sua sessão expirou. Atualize a página e tente novamente.',
        default => '',
    };
}

function updateUserSettingsFromRequest(int $userId): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (!isset($_POST['settings_update'])) {
        return;
    }

    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $role = in_array((string)($_POST['role'] ?? 'aluno'), ['aluno', 'professor'], true) ? $_POST['role'] : 'aluno';

    if ($name !== '') {
        $stmt = db()->prepare('UPDATE users SET name = :name, email = :email, role = :role WHERE id = :userId');
        if ($password !== '') {
            $stmt = db()->prepare('UPDATE users SET name = :name, email = :email, password_hash = :hash, role = :role WHERE id = :userId');
            $stmt->execute(['name' => $name, 'email' => $email, 'hash' => password_hash($password), 'role' => $role, 'userId' => $userId]);
        } else {
            $stmt->execute(['name' => $name, 'email' => $email, 'role' => $role, 'userId' => $userId]);
        }
    }
}

function renderTopBar(string $page = 'inicio', bool $showHome = true, bool $showSettings = true): void
{
    $user = currentUser();
    $progress = $user ? getUserProgress((int)$user['id']) : ['stars' => 0];
    ?>
    <header class="topbar">
        <div class="topbar__brand">
            <img src="logobranca.svg" alt="Logo MathPlay" />
        </div>
        <div class="topbar__actions">
            <?php if ($user): ?>
                <?php if ($showHome): ?>
                    <a class="topbar__icon-link <?= $page === 'inicio' ? 'active' : '' ?>" href="inicio.php" aria-label="Página inicial">
                        <span>⌂</span>
                    </a>
                <?php endif; ?>
                <?php if ($showSettings): ?>
                    <a class="topbar__icon-link <?= $page === 'configuracoes' ? 'active' : '' ?>" href="configuracoes.php" aria-label="Configurações">
                        <span>⚙</span>
                    </a>
                <?php endif; ?>
                <button type="button" class="topbar__emoji" id="emojiToggle" aria-label="Ativar ou desativar emojis" aria-pressed="false">🔠</button>
                <button type="button" class="topbar__toggle" id="themeToggle" aria-label="Alternar tema">☼</button>
                <button type="button" class="topbar__font" data-font="decrease" aria-label="Diminuir fonte">A−</button>
                <button type="button" class="topbar__font" data-font="increase" aria-label="Aumentar fonte">A+</button>
                <a class="topbar__logout" href="logout.php" aria-label="Sair">
                    <span class="standard-only">Sair</span>
                    <img class="emoji-only icon-image" src="sair.svg" alt="" aria-hidden="true" />
                </a>
            <?php endif; ?>
        </div>
    </header>
    <?php
}

function renderAvatarDisplay(int $userId): array
{
    $progress = getUserProgress($userId);
    $face = getEquippedFace($userId);
    $color = getEquippedColor($userId);
    return ['progress' => $progress, 'face' => $face, 'color' => $color];
}

function ensureDefaultCatalogPurchases(int $userId): void
{
    $defaultItems = db()->query('SELECT id FROM catalog_items WHERE is_free = 1')->fetchAll();
    foreach ($defaultItems as $item) {
        $check = db()->prepare('SELECT 1 FROM user_inventory WHERE user_id = :userId AND item_id = :itemId');
        $check->execute(['userId' => $userId, 'itemId' => $item['id']]);
        if (!$check->fetch()) {
            $insert = db()->prepare('INSERT INTO user_inventory (user_id, item_id, purchased_at, equipped) VALUES (:userId, :itemId, CURRENT_TIMESTAMP, 1)');
            $insert->execute(['userId' => $userId, 'itemId' => $item['id']]);
        }
    }
}

function getRecentHistory(int $userId, int $limit = 5): array
{
    $stmt = db()->prepare('SELECT * FROM game_history WHERE user_id = :userId ORDER BY played_at DESC LIMIT :limit');
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getHistoryTotals(int $userId): array
{
    $stmt = db()->prepare(
        'SELECT COALESCE(SUM(correct_answers), 0) AS correct, COALESCE(SUM(wrong_answers), 0) AS wrong
         FROM game_history WHERE user_id = :userId'
    );
    $stmt->execute(['userId' => $userId]);
    $totals = $stmt->fetch();

    return [
        'correct' => (int)($totals['correct'] ?? 0),
        'wrong' => (int)($totals['wrong'] ?? 0),
    ];
}

function getUserAchievements(int $userId): array
{
    $stmt = db()->prepare('SELECT a.* FROM achievements a INNER JOIN user_achievements ua ON ua.achievement_id = a.id WHERE ua.user_id = :userId ORDER BY a.id');
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetchAll();
}

function unlockAchievement(int $userId, int $achievementId): void
{
    $check = db()->prepare('SELECT 1 FROM user_achievements WHERE user_id = :userId AND achievement_id = :achievementId');
    $check->execute(['userId' => $userId, 'achievementId' => $achievementId]);
    if ($check->fetch()) {
        return;
    }

    $insert = db()->prepare('INSERT INTO user_achievements (user_id, achievement_id) VALUES (:userId, :achievementId)');
    $insert->execute(['userId' => $userId, 'achievementId' => $achievementId]);
}

function recordGameResult(
    int $userId,
    string $gameName,
    int $correctCount,
    int $wrongCount,
    int $starsEarned,
    int $durationSeconds,
    string $gameRunId
): void
{
    ensureUserProgress($userId);
    $database = db();
    $database->beginTransaction();

    $history = $database->prepare(
        insertIgnorePrefix() . ' game_history
         (user_id, game_name, result, correct_answers, wrong_answers, stars_earned, duration_seconds, game_run_id)
         VALUES (:userId, :gameName, :result, :correct, :wrong, :stars, :duration, :runId)'
    );
    $history->execute([
        'userId' => $userId,
        'gameName' => $gameName,
        'result' => 'Fim de jogo',
        'correct' => $correctCount,
        'wrong' => $wrongCount,
        'stars' => $starsEarned,
        'duration' => $durationSeconds,
        'runId' => $gameRunId,
    ]);

    if ($history->rowCount() === 0) {
        $database->commit();
        return;
    }

    $score = $database->prepare('INSERT INTO scores (user_id, game_name, points) VALUES (:userId, :gameName, :points)');
    $score->execute(['userId' => $userId, 'gameName' => $gameName, 'points' => $starsEarned]);

    $levelCast = $database->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
        ? 'CAST((xp + :starsForLevel) / 100 AS SIGNED)'
        : 'CAST((xp + :starsForLevel) / 100 AS INTEGER)';
    $progress = $database->prepare(
        'UPDATE user_progress
         SET stars = stars + :starsForBalance,
             xp = xp + :starsForXp,
             level = ' . $levelCast . ' + 1,
             updated_at = CURRENT_TIMESTAMP
         WHERE user_id = :userId'
    );
    $progress->execute([
        'starsForBalance' => $starsEarned,
        'starsForXp' => $starsEarned,
        'starsForLevel' => $starsEarned,
        'userId' => $userId,
    ]);

    if ($correctCount > 0) {
        unlockAchievement($userId, 1);
    }

    $updatedProgress = getUserProgress($userId);
    if ((int)$updatedProgress['stars'] >= 100) {
        unlockAchievement($userId, 2);
    }

    $gameCount = $database->prepare('SELECT COUNT(*) FROM game_history WHERE user_id = :userId');
    $gameCount->execute(['userId' => $userId]);
    if ((int)$gameCount->fetchColumn() >= 5) {
        unlockAchievement($userId, 3);
    }

    $database->commit();
}
