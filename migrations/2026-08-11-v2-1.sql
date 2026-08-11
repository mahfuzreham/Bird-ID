USE bird_id;

CREATE TABLE IF NOT EXISTS favorites (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  common_name VARCHAR(190) NOT NULL,
  scientific_name VARCHAR(190) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_favorite (user_id, common_name),
  CONSTRAINT fk_favorite_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS corrections (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  scan_id BIGINT UNSIGNED NULL,
  suggested_name VARCHAR(190) NOT NULL,
  note TEXT NULL,
  status ENUM('pending','reviewed','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_at TIMESTAMP NULL,
  INDEX(status),
  CONSTRAINT fk_correction_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_correction_scan FOREIGN KEY (scan_id) REFERENCES scan_logs(id) ON DELETE SET NULL
);
