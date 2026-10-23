CREATE TABLE IF NOT EXISTS users (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  email VARCHAR(254) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'aluno',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_progress (
  user_id INT NOT NULL PRIMARY KEY,
  stars INT NOT NULL DEFAULT 0,
  xp INT NOT NULL DEFAULT 0,
  level INT NOT NULL DEFAULT 1,
  avatar_face VARCHAR(32) NOT NULL DEFAULT 'rosto1',
  avatar_color VARCHAR(32) NOT NULL DEFAULT '#ffcf4a',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_progress_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_history (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  game_name VARCHAR(100) NOT NULL,
  result VARCHAR(100) NOT NULL,
  correct_answers INT NOT NULL DEFAULT 0,
  wrong_answers INT NOT NULL DEFAULT 0,
  stars_earned INT NOT NULL DEFAULT 0,
  duration_seconds INT NOT NULL DEFAULT 0,
  game_run_id CHAR(32) NULL,
  played_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_game_history_user_played (user_id, played_at),
  UNIQUE KEY idx_game_history_user_run (user_id, game_run_id),
  CONSTRAINT fk_game_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS scores (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  game_name VARCHAR(100) NOT NULL,
  points INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_scores_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS achievements (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  icon VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_achievements (
  user_id INT NOT NULL,
  achievement_id INT NOT NULL,
  unlocked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, achievement_id),
  CONSTRAINT fk_user_achievements_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_achievements_achievement FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS catalog_items (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(20) NOT NULL,
  key_name VARCHAR(40) NOT NULL,
  label VARCHAR(80) NOT NULL,
  color VARCHAR(32) NULL,
  price INT NOT NULL DEFAULT 0,
  is_free TINYINT NOT NULL DEFAULT 0,
  default_equipped TINYINT NOT NULL DEFAULT 0,
  asset_path VARCHAR(255) NULL,
  UNIQUE KEY uq_catalog_type_key (`type`, key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_inventory (
  user_id INT NOT NULL,
  item_id INT NOT NULL,
  purchased_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  equipped TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id, item_id),
  CONSTRAINT fk_user_inventory_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_inventory_item FOREIGN KEY (item_id) REFERENCES catalog_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_settings (
  user_id INT NOT NULL PRIMARY KEY,
  theme VARCHAR(20) NOT NULL DEFAULT 'light',
  font_scale DOUBLE NOT NULL DEFAULT 1.0,
  CONSTRAINT fk_user_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO achievements (id, name, description, icon) VALUES
  (1, 'Primeiros passos', 'Complete sua primeira partida.', '⭐'),
  (2, 'Selo de matemática', 'Acumule 100 estrelas.', '🏆'),
  (3, 'Treinador', 'Jogue 5 partidas.', '🎯');

INSERT IGNORE INTO catalog_items (id, `type`, key_name, label, color, price, is_free, default_equipped, asset_path) VALUES
  (1, 'color', 'amarelo', 'Amarelo', '#ffcf4a', 0, 1, 1, NULL),
  (2, 'color', 'verde', 'Verde', '#c1cf35', 60, 0, 0, NULL),
  (3, 'color', 'azul', 'Azul', '#48bce1', 60, 0, 0, NULL),
  (4, 'color', 'rosa', 'Rosa', '#ff6ca0', 60, 0, 0, NULL),
  (5, 'color', 'roxo', 'Roxo', '#cb6ce6', 60, 0, 0, NULL),
  (6, 'face', 'rosto1', 'Rosto 1', NULL, 0, 1, 1, 'rosto1.svg'),
  (7, 'face', 'rosto2', 'Rosto 2', NULL, 100, 0, 0, 'rosto2.svg'),
  (8, 'face', 'rosto3', 'Rosto 3', NULL, 100, 0, 0, 'rosto3.svg'),
  (9, 'face', 'rosto4', 'Rosto 4', NULL, 100, 0, 0, 'rosto4.svg'),
  (10, 'face', 'rosto5', 'Rosto 5', NULL, 100, 0, 0, 'rosto5.svg');

UPDATE catalog_items SET price = 60 WHERE `type` = 'color' AND is_free = 0;
UPDATE catalog_items SET price = 100 WHERE `type` = 'face' AND is_free = 0;
