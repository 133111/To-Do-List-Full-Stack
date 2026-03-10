-- 🔧 FIX: CREATE CATEGORIES TABLE
-- Jalankan script ini di phpMyAdmin untuk membuat tabel categories

-- 1. Buat tabel categories jika belum ada
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    emoji VARCHAR(10) DEFAULT '📝',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_per_user (user_id, name)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Modifikasi tabel todos untuk menambah category_id (jika belum ada)
-- PASTIKAN: Jangan run ini jika column sudah ada!
-- Buka phpMyAdmin > todos > Structure, cek apakah category_id sudah ada
-- Jika BELUM ada, uncomment dan run code di bawah:

/*
ALTER TABLE todos ADD COLUMN category_id INT AFTER user_id;
ALTER TABLE todos ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE;
*/

-- 3. Tambah indexes untuk performa
CREATE INDEX idx_user_categories ON categories(user_id);
CREATE INDEX idx_category_todos ON todos(category_id);

-- Verifikasi
SELECT 'Categories table created/verified' AS status;
