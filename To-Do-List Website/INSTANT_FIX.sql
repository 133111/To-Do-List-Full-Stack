-- COPY & PASTE LANGSUNG KE phpMyAdmin SQL TAB
-- Database: todo_list

-- ===== STEP 1: CREATE CATEGORIES TABLE =====
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

-- ===== STEP 2: OPTIONAL - ADD category_id TO TODOS (HANYA JIKA BELUM ADA) =====
-- Uncomment (hapus tanda -- ) jika di phpMyAdmin > todos > Structure belum ada column category_id
-- ALTER TABLE todos ADD COLUMN category_id INT AFTER user_id;
-- ALTER TABLE todos ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE;

-- ===== STEP 3: ADD INDEXES FOR PERFORMANCE =====
CREATE INDEX idx_user_categories ON categories(user_id);
CREATE INDEX idx_category_todos ON todos(category_id);

-- SELESAI! Kategori table sudah siap.
