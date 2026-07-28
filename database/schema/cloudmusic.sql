-- CloudMusic MySQL 8 schema reference
-- IMPORTANT: Laravel migrations are the source of truth.
-- Use this file only for review or a clean manual import. Do not import it and then run the same migrations.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS play_events;
DROP TABLE IF EXISTS listening_histories;
DROP TABLE IF EXISTS follows;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS song_likes;
DROP TABLE IF EXISTS playlist_song;
DROP TABLE IF EXISTS playlists;
DROP TABLE IF EXISTS songs;
DROP TABLE IF EXISTS albums;
DROP TABLE IF EXISTS genres;
DROP TABLE IF EXISTS failed_jobs;
DROP TABLE IF EXISTS job_batches;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS cache_locks;
DROP TABLE IF EXISTS cache;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'listener',
  avatar_path VARCHAR(255) NULL,
  cover_path VARCHAR(255) NULL,
  bio TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at TIMESTAMP NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX users_role_index (role),
  INDEX users_is_active_index (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
  email VARCHAR(255) PRIMARY KEY,
  token VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
  id VARCHAR(255) PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  payload LONGTEXT NOT NULL,
  last_activity INT NOT NULL,
  INDEX sessions_user_id_index (user_id),
  INDEX sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache (
  `key` VARCHAR(255) PRIMARY KEY,
  value MEDIUMTEXT NOT NULL,
  expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
  `key` VARCHAR(255) PRIMARY KEY,
  owner VARCHAR(255) NOT NULL,
  expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jobs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  queue VARCHAR(255) NOT NULL,
  payload LONGTEXT NOT NULL,
  attempts TINYINT UNSIGNED NOT NULL,
  reserved_at INT UNSIGNED NULL,
  available_at INT UNSIGNED NOT NULL,
  created_at INT UNSIGNED NOT NULL,
  INDEX jobs_queue_index (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_batches (
  id VARCHAR(255) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  total_jobs INT NOT NULL,
  pending_jobs INT NOT NULL,
  failed_jobs INT NOT NULL,
  failed_job_ids LONGTEXT NOT NULL,
  options MEDIUMTEXT NULL,
  cancelled_at INT NULL,
  created_at INT NOT NULL,
  finished_at INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE failed_jobs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid VARCHAR(255) NOT NULL UNIQUE,
  connection TEXT NOT NULL,
  queue TEXT NOT NULL,
  payload LONGTEXT NOT NULL,
  exception LONGTEXT NOT NULL,
  failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE genres (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  slug VARCHAR(100) NOT NULL UNIQUE,
  description TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX genres_is_active_index (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE albums (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  slug VARCHAR(190) NOT NULL,
  description TEXT NULL,
  cover_path VARCHAR(255) NULL,
  release_date DATE NULL,
  visibility VARCHAR(20) NOT NULL DEFAULT 'public',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT albums_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY albums_user_slug_uq (user_id, slug),
  INDEX albums_visibility_index (visibility)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE songs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  genre_id BIGINT UNSIGNED NULL,
  album_id BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  slug VARCHAR(190) NOT NULL,
  description TEXT NULL,
  audio_path VARCHAR(255) NOT NULL,
  audio_mime VARCHAR(100) NOT NULL DEFAULT 'audio/mpeg',
  cover_path VARCHAR(255) NULL,
  duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
  track_number SMALLINT UNSIGNED NULL,
  release_date DATE NULL,
  visibility VARCHAR(20) NOT NULL DEFAULT 'public',
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  play_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
  like_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
  comment_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT songs_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT songs_genre_fk FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE SET NULL,
  CONSTRAINT songs_album_fk FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE SET NULL,
  UNIQUE KEY songs_user_slug_uq (user_id, slug),
  INDEX songs_status_index (status),
  INDEX songs_visibility_index (visibility),
  INDEX songs_play_count_index (play_count),
  INDEX songs_publish_idx (status, visibility, release_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE playlists (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(190) NOT NULL,
  description TEXT NULL,
  cover_path VARCHAR(255) NULL,
  visibility VARCHAR(20) NOT NULL DEFAULT 'public',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT playlists_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY playlists_user_slug_uq (user_id, slug),
  INDEX playlists_visibility_index (visibility)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE playlist_song (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  playlist_id BIGINT UNSIGNED NOT NULL,
  song_id BIGINT UNSIGNED NOT NULL,
  position INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT playlist_song_playlist_fk FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
  CONSTRAINT playlist_song_song_fk FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
  UNIQUE KEY playlist_song_uq (playlist_id, song_id),
  INDEX playlist_position_idx (playlist_id, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE song_likes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  song_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT song_likes_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT song_likes_song_fk FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
  UNIQUE KEY song_likes_uq (user_id, song_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  song_id BIGINT UNSIGNED NOT NULL,
  parent_id BIGINT UNSIGNED NULL,
  content TEXT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'visible',
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT comments_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT comments_song_fk FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
  CONSTRAINT comments_parent_fk FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
  INDEX comments_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE follows (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  follower_id BIGINT UNSIGNED NOT NULL,
  following_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT follows_follower_fk FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT follows_following_fk FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY follows_pair_uq (follower_id, following_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE listening_histories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  song_id BIGINT UNSIGNED NOT NULL,
  last_position_seconds INT UNSIGNED NOT NULL DEFAULT 0,
  listened_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT history_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT history_song_fk FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
  UNIQUE KEY history_user_song_uq (user_id, song_id),
  INDEX history_user_time_idx (user_id, listened_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE play_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  song_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  session_key VARCHAR(100) NULL,
  ip_hash VARCHAR(64) NULL,
  user_agent VARCHAR(500) NULL,
  played_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT plays_song_fk FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
  CONSTRAINT plays_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX plays_song_time_idx (song_id, played_at),
  INDEX plays_user_time_idx (user_id, played_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reports (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  reportable_type VARCHAR(255) NOT NULL,
  reportable_id BIGINT UNSIGNED NOT NULL,
  reason VARCHAR(100) NOT NULL,
  details TEXT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'open',
  admin_note TEXT NULL,
  resolved_by BIGINT UNSIGNED NULL,
  resolved_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT reports_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT reports_resolver_fk FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX reports_reportable_idx (reportable_type, reportable_id),
  INDEX reports_status_index (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
