<?php
require __DIR__ . '/includes/app.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Sua sessão expirou. Entre novamente para salvar a partida.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

$requestToken = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (!verifyCsrfToken($requestToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'A sessão de jogo expirou. Atualize a página e tente novamente.']);
    exit;
}

$result = json_decode((string)file_get_contents('php://input'), true);
$validGames = ['CALCULANDO', 'FRACIONANDO'];
$invalidFields = [];
if (!is_array($result)) {
    $invalidFields[] = 'body';
} else {
    if (!isset($result['game']) || !in_array($result['game'], $validGames, true)) {
        $invalidFields[] = 'game';
    }
    foreach (['correct', 'wrong', 'stars', 'duration'] as $field) {
        if (!isset($result[$field]) || !is_int($result[$field])) {
            $invalidFields[] = $field;
        }
    }
    if (!isset($result['runId']) || !is_string($result['runId']) || preg_match('/^[a-f0-9]{32}$/', $result['runId']) !== 1) {
        $invalidFields[] = 'runId';
    }
    if (is_int($result['correct'] ?? null) && $result['correct'] < 0) {
        $invalidFields[] = 'correct';
    }
    if (is_int($result['wrong'] ?? null) && ($result['wrong'] < 0 || $result['wrong'] > 1)) {
        $invalidFields[] = 'wrong';
    }
    if (is_int($result['stars'] ?? null)
        && is_int($result['correct'] ?? null)
        && ($result['stars'] < 0 || $result['stars'] > $result['correct'] * 3)) {
        $invalidFields[] = 'stars';
    }
    if (is_int($result['duration'] ?? null) && ($result['duration'] < 0 || $result['duration'] > 86400)) {
        $invalidFields[] = 'duration';
    }
}

if ($invalidFields) {
    http_response_code(400);
    echo json_encode(['error' => 'Os dados da partida são inválidos.', 'invalid_fields' => array_values(array_unique($invalidFields))]);
    exit;
}

try {
    recordGameResult(
        (int)$_SESSION['user_id'],
        $result['game'],
        $result['correct'],
        $result['wrong'],
        $result['stars'],
        $result['duration'],
        $result['runId']
    );
    echo json_encode(['saved' => true]);
} catch (Throwable $error) {
    if (db()->inTransaction()) {
        db()->rollBack();
    }
    error_log('MathPlay game result save failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Não foi possível salvar esta partida. Tente novamente.']);
}
