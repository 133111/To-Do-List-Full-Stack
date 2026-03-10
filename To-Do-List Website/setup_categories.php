<?php
/**
 * AUTO SETUP DATABASE - Categories Table Creator
 * 
 * File ini akan otomatis membuat tabel "categories" jika belum ada
 * 
 * CARA PAKAI:
 * 1. Akses di browser: http://localhost/To-Do-List Website/setup_categories.php
 * 2. Page akan otomatis membuat tabel categories
 * 3. Refresh halaman index.html
 * 4. Test fitur tambah kategori
 */

header('Content-Type: application/json; charset=utf-8');

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'todo_list';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');

// ===== CREATE CATEGORIES TABLE =====
$sql_categories = "
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
";

if ($conn->query($sql_categories)) {
    $message_categories = "✅ Tabel 'categories' berhasil dibuat";
} else {
    die(json_encode([
        'success' => false,
        'message' => 'Error creating categories table: ' . $conn->error
    ]));
}

// ===== ADD category_id COLUMN TO todos (IF NOT EXISTS) =====
$check_column = $conn->query("SHOW COLUMNS FROM todos LIKE 'category_id'");
$column_exists = $check_column && $check_column->num_rows > 0;

$message_todo_column = '';
if (!$column_exists) {
    $sql_alter_todos = "ALTER TABLE todos ADD COLUMN category_id INT AFTER user_id";
    if ($conn->query($sql_alter_todos)) {
        $message_todo_column = "✅ Column 'category_id' ditambahkan ke tabel 'todos'";
        
        // Add foreign key
        $sql_fk = "ALTER TABLE todos ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE";
        $conn->query($sql_fk);
    } else {
        die(json_encode([
            'success' => false,
            'message' => 'Error adding category_id column: ' . $conn->error
        ]));
    }
} else {
    $message_todo_column = "✅ Column 'category_id' sudah ada di tabel 'todos'";
}

// ===== CREATE INDEXES =====
$indexes_created = 0;

$check_idx1 = $conn->query("SHOW INDEX FROM categories WHERE Key_name = 'idx_user_categories'");
if (!($check_idx1 && $check_idx1->num_rows > 0)) {
    if ($conn->query("CREATE INDEX idx_user_categories ON categories(user_id)")) {
        $indexes_created++;
    }
} else {
    $indexes_created++;
}

$check_idx2 = $conn->query("SHOW INDEX FROM todos WHERE Key_name = 'idx_category_todos'");
if (!($check_idx2 && $check_idx2->num_rows > 0)) {
    if ($conn->query("CREATE INDEX idx_category_todos ON todos(category_id)")) {
        $indexes_created++;
    }
} else {
    $indexes_created++;
}

$message_indexes = "✅ Indexes dibuat/sudah ada ($indexes_created/2)";

$conn->close();

echo json_encode([
    'success' => true,
    'message' => 'Database setup complete!',
    'details' => [
        $message_categories,
        $message_todo_column,
        $message_indexes
    ],
    'next_step' => 'Refresh halaman index.html dan test fitur kategori'
]);
?>
