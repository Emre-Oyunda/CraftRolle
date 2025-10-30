-- CraftRolle Database Schema

CREATE DATABASE IF NOT EXISTS craftrolle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE craftrolle;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Books Table
CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  content LONGTEXT,
  cover_path VARCHAR(500),
  visibility ENUM('public', 'private') DEFAULT 'private',
  is_deleted TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id),
  INDEX idx_visibility (visibility),
  INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notes Table
CREATE TABLE IF NOT EXISTS notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT,
  title VARCHAR(255) NOT NULL,
  content TEXT,
  is_deleted TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE SET NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_book_id (book_id),
  INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO users (username, email, password) VALUES
('demo', 'demo@craftrolle.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

INSERT INTO books (user_id, title, content, visibility) VALUES
(1, 'Örnek Kitap - 3D Görüntüleyici', 'Bu bir örnek kitaptır. 3D görüntüleyici ile gerçekçi bir şekilde sayfa çevirebilirsiniz.\n\nSağ ve sol ok tuşları veya butonlar ile sayfalar arasında gezinebilirsiniz.\n\nMobil cihazlarda kaydırma (swipe) hareketleri ile sayfa çevirebilirsiniz.\n\nKitap görüntüleyici, gerçek bir kitabı taklit eden animasyonlar ve gölgeler içerir.\n\nDaha fazla içerik ekledikçe sayfalar otomatik olarak oluşturulacaktır.\n\nİyi okumalar!', 'public');
