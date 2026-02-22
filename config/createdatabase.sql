DROP TABLE IF EXISTS friends;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS profiles;

CREATE TABLE profiles (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  email         VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  bio           TEXT NULL,
  avatar_url    VARCHAR(255) NULL,
  date_of_birth DATE NULL ,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE friends (
  user_id       BIGINT UNSIGNED NOT NULL,
  dest_id       BIGINT UNSIGNED NOT NULL,
  relationship  ENUM('pending', 'friends', 'blocked') NOT NULL DEFAULT 'pending',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, dest_id),
  CONSTRAINT fk_friends_user FOREIGN KEY (user_id) REFERENCES profiles(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_friends_dest FOREIGN KEY (dest_id) REFERENCES profiles(id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_friends_dest (dest_id),
  INDEX idx_friends_relationship (relationship)
) ENGINE=InnoDB;

CREATE TABLE posts (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  profile_id  BIGINT UNSIGNED NOT NULL,
  title       VARCHAR(200) NOT NULL,
  content     LONGTEXT NOT NULL,
  img_url     VARCHAR(255) NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_posts_profile
    FOREIGN KEY (profile_id) REFERENCES profiles(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_posts_profile_id (profile_id),
  INDEX idx_posts_created_at (created_at)
) ENGINE=InnoDB;

CREATE TABLE comments (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id     BIGINT UNSIGNED NOT NULL,
  profile_id  BIGINT UNSIGNED NOT NULL,
  content     TEXT NOT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_comments_post
    FOREIGN KEY (post_id) REFERENCES posts(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_comments_profile
    FOREIGN KEY (profile_id) REFERENCES profiles(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_comments_post_id (post_id),
  INDEX idx_comments_profile_id (profile_id),
  INDEX idx_comments_created_at (created_at)
) ENGINE=InnoDB;

CREATE TABLE messages (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_id    BIGINT UNSIGNED NOT NULL,
  receiver_id  BIGINT UNSIGNED NOT NULL, 
  content      LONGTEXT NOT NULL,
  img_url      VARCHAR(255) NULL,
  send_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  
  CONSTRAINT fk_messages_sender
    FOREIGN KEY (sender_id) REFERENCES profiles(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_messages_receiver                            
    FOREIGN KEY (receiver_id) REFERENCES profiles(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_messages_sender (sender_id),
  INDEX idx_messages_receiver (receiver_id),
  INDEX idx_messages_send_at (send_at)
) ENGINE=InnoDB;