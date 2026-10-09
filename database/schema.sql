PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'aluno',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_progress (
  user_id INTEGER PRIMARY KEY,
  stars INTEGER NOT NULL DEFAULT 0,
  xp INTEGER NOT NULL DEFAULT 0,
  level INTEGER NOT NULL DEFAULT 1,
  avatar_face TEXT NOT NULL DEFAULT 'rosto1',
  avatar_color TEXT NOT NULL DEFAULT '#ffcf4a',
  updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS game_history (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  game_name TEXT NOT NULL,
  result TEXT NOT NULL,
  correct_answers INTEGER NOT NULL DEFAULT 0,
  wrong_answers INTEGER NOT NULL DEFAULT 0,
  stars_earned INTEGER NOT NULL DEFAULT 0,
  duration_seconds INTEGER NOT NULL DEFAULT 0,
  game_run_id TEXT,
  played_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS scores (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  game_name TEXT NOT NULL,
  points INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS achievements (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  description TEXT NOT NULL,
  icon TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS user_achievements (
  user_id INTEGER NOT NULL,
  achievement_id INTEGER NOT NULL,
  unlocked_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, achievement_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS catalog_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  type TEXT NOT NULL,
  key_name TEXT NOT NULL,
  label TEXT NOT NULL,
  color TEXT,
  price INTEGER NOT NULL DEFAULT 0,
  is_free INTEGER NOT NULL DEFAULT 0,
  default_equipped INTEGER NOT NULL DEFAULT 0,
  asset_path TEXT,
  UNIQUE(type, key_name)
);

CREATE TABLE IF NOT EXISTS user_inventory (
  user_id INTEGER NOT NULL,
  item_id INTEGER NOT NULL,
  purchased_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  equipped INTEGER NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id, item_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (item_id) REFERENCES catalog_items(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_settings (
  user_id INTEGER PRIMARY KEY,
  theme TEXT NOT NULL DEFAULT 'light',
  font_scale REAL NOT NULL DEFAULT 1.0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT OR IGNORE INTO achievements (id, name, description, icon) VALUES
  (1, 'Primeiros passos', 'Complete sua primeira partida.', '⭐'),
  (2, 'Selo de matemática', 'Acumule 100 estrelas.', '🏆'),
  (3, 'Treinador', 'Jogue 5 partidas.', '🎯');

INSERT OR IGNORE INTO catalog_items (id, type, key_name, label, color, price, is_free, default_equipped, asset_path) VALUES
  (1, 'color', 'amarelo', 'Amarelo', '#ffcf4a', 0, 1, 1, NULL),
  (2, 'color', 'verde', 'Verde', '#c1cf35', 30, 0, 0, NULL),
  (3, 'color', 'azul', 'Azul', '#48bce1', 30, 0, 0, NULL),
  (4, 'color', 'rosa', 'Rosa', '#ff6ca0', 30, 0, 0, NULL),
  (5, 'color', 'roxo', 'Roxo', '#cb6ce6', 30, 0, 0, NULL),
  (6, 'face', 'rosto1', 'Rosto 1', NULL, 0, 1, 1, 'rosto1.svg'),
  (7, 'face', 'rosto2', 'Rosto 2', NULL, 50, 0, 0, 'rosto2.svg'),
  (8, 'face', 'rosto3', 'Rosto 3', NULL, 50, 0, 0, 'rosto3.svg'),
  (9, 'face', 'rosto4', 'Rosto 4', NULL, 50, 0, 0, 'rosto4.svg'),
  (10, 'face', 'rosto5', 'Rosto 5', NULL, 50, 0, 0, 'rosto5.svg');
