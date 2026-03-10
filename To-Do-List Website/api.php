<?php
/**
 * To-Do List Backend API
 * PHP + MySQL - Simplified & Reliable
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/api_errors.log');

// Database config
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'todo_list';

// Connect
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'DB Error: ' . $conn->connect_error]));
}
$conn->set_charset('utf8mb4');

// ===== AUTO CREATE CATEGORIES TABLE IF NOT EXISTS =====
$create_categories_table = "
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

if (!$conn->query($create_categories_table)) {
    error_log('Error creating categories table: ' . $conn->error);
}

// ===== ADD category_id TO TODOS IF NOT EXISTS =====
$check_column = $conn->query("SHOW COLUMNS FROM todos LIKE 'category_id'");
if (!($check_column && $check_column->num_rows > 0)) {
    $add_column = "ALTER TABLE todos ADD COLUMN category_id INT AFTER user_id";
    if ($conn->query($add_column)) {
        $add_fk = "ALTER TABLE todos ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE";
        $conn->query($add_fk);
    }
}

// ===== CREATE INDEXES IF NOT EXISTS =====
$check_idx1 = $conn->query("SHOW INDEX FROM categories WHERE Key_name = 'idx_user_categories'");
if (!($check_idx1 && $check_idx1->num_rows > 0)) {
    $conn->query("CREATE INDEX idx_user_categories ON categories(user_id)");
}

$check_idx2 = $conn->query("SHOW INDEX FROM todos WHERE Key_name = 'idx_category_todos'");
if (!($check_idx2 && $check_idx2->num_rows > 0)) {
    $conn->query("CREATE INDEX idx_category_todos ON todos(category_id)");
}

// ===== AUTO CREATE LEARNING_PROGRESS TABLE IF NOT EXISTS =====
$create_learning_table = "
CREATE TABLE IF NOT EXISTS learning_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    language VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    progress_percentage INT DEFAULT 0,
    topics_learned JSON DEFAULT '[]',
    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_language_per_user (user_id, language)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
";

if (!$conn->query($create_learning_table)) {
    error_log('Error creating learning_progress table: ' . $conn->error);
}

$check_idx3 = $conn->query("SHOW INDEX FROM learning_progress WHERE Key_name = 'idx_user_learning'");
if (!($check_idx3 && $check_idx3->num_rows > 0)) {
    $conn->query("CREATE INDEX idx_user_learning ON learning_progress(user_id)");
}

// Get action
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Get token
$token = '';
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

// Jika tidak ada di HTTP_AUTHORIZATION, cari di getallheaders()
if (!$authHeader && function_exists('getallheaders')) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
}

if ($authHeader) {
    $token = str_replace('Bearer ', '', $authHeader);
}

// ===== MAIN LOGIC =====

if ($action === 'register') {
    register($conn, $input);
} 
elseif ($action === 'login') {
    login($conn, $input);
} 
elseif ($action === 'delete_log') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    deleteLog($conn, $input, $userId);
}
elseif ($action === 'add_category') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    addCategory($conn, $input, $userId);
}
elseif ($action === 'get_categories') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    getCategories($conn, $userId);
}
elseif ($action === 'update_category') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    updateCategory($conn, $input, $userId);
}
elseif ($action === 'delete_category') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    deleteCategory($conn, $input, $userId);
}
elseif ($action === 'get_todos') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    getTodos($conn, $userId);
}
elseif ($action === 'update_todo') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    updateTodo($conn, $input, $userId);
}
elseif ($action === 'delete_todo') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    deleteTodo($conn, $input, $userId);
}
elseif ($action === 'add_log') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    addLog($conn, $input, $userId);
}
elseif ($action === 'get_logs') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    getLogs($conn, $userId);
}
elseif ($action === 'delete_log') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    deleteLog($conn, $input, $userId);
}
elseif ($action === 'get_learning_progress') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    getLearningProgress($conn, $userId);
}
elseif ($action === 'update_learning_progress') {
    if (!$token) { sendRes(['success' => false, 'message' => 'Token required'], 401); }
    $userId = getTokenUser($conn, $token);
    if (!$userId) { sendRes(['success' => false, 'message' => 'Invalid token'], 401); }
    updateLearningProgress($conn, $input, $userId);
}
else {
    sendRes(['success' => false, 'message' => 'Invalid action'], 400);
}

$conn->close();

// ===== FUNCTIONS =====

function register($conn, $input) {
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (strlen($username) < 3) {
        return sendRes(['success' => false, 'message' => 'Username min 3 karakter']);
    }
    if (strlen($password) < 6) {
        return sendRes(['success' => false, 'message' => 'Password min 6 karakter']);
    }

    // Check exists
    $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return sendRes(['success' => false, 'message' => 'Username sudah terdaftar']);
    }
    $stmt->close();

    // Create
    $pass_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $stmt = $conn->prepare('INSERT INTO users (username, password, created_at) VALUES (?, ?, NOW())');
    $stmt->bind_param('ss', $username, $pass_hash);
    
    if ($stmt->execute()) {
        $stmt->close();
        sendRes(['success' => true, 'message' => 'Registrasi berhasil', 'user_id' => $stmt->insert_id]);
    } else {
        $stmt->close();
        sendRes(['success' => false, 'message' => 'Registrasi gagal']);
    }
}

function login($conn, $input) {
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (!$username || !$password) {
        return sendRes(['success' => false, 'message' => 'Username dan password required']);
    }

    // Get user
    $stmt = $conn->prepare('SELECT id, password FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return sendRes(['success' => false, 'message' => 'Username tidak ditemukan']);
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Check password
    if (!password_verify($password, $user['password'])) {
        return sendRes(['success' => false, 'message' => 'Password salah']);
    }

    // Create token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 604800);

    $stmt = $conn->prepare('INSERT INTO sessions (user_id, token, created_at, expires_at) VALUES (?, ?, NOW(), ?)');
    $stmt->bind_param('iss', $user['id'], $token, $expires);
    $stmt->execute();
    $stmt->close();

    sendRes([
        'success' => true,
        'message' => 'Login berhasil',
        'token' => $token,
        'user_id' => $user['id'],
        'username' => $username
    ]);
}

function getTokenUser($conn, $token) {
    if (!$token || strlen($token) < 32) return null;
    
    $stmt = $conn->prepare('SELECT user_id FROM sessions WHERE token = ? AND (expires_at IS NULL OR expires_at > NOW())');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if ($result->num_rows === 0) return null;
    $row = $result->fetch_assoc();
    return $row['user_id'] ?? null;
}

function addTodo($conn, $input, $userId) {
    $title = trim($input['title'] ?? '');
    $category_id = (int)($input['category_id'] ?? 0);
    $description = trim($input['description'] ?? '');

    if (!$title) {
        return sendRes(['success' => false, 'message' => 'Title required']);
    }

    if (!$category_id) {
        return sendRes(['success' => false, 'message' => 'Category ID required']);
    }

    // Verify category belongs to user
    $stmt = $conn->prepare('SELECT id FROM categories WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $category_id, $userId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $stmt->close();
        return sendRes(['success' => false, 'message' => 'Invalid category']);
    }
    $stmt->close();

    $stmt = $conn->prepare('INSERT INTO todos (user_id, category_id, title, description, completed, created_at, updated_at) VALUES (?, ?, ?, ?, 0, NOW(), NOW())');
    $stmt->bind_param('iiss', $userId, $category_id, $title, $description);
    
    if ($stmt->execute()) {
        $todo_id = $stmt->insert_id;
        $stmt->close();
        sendRes(['success' => true, 'message' => 'Todo ditambahkan', 'todo_id' => $todo_id]);
    } else {
        $stmt->close();
        sendRes(['success' => false, 'message' => 'Gagal tambah todo']);
    }
}

function getTodos($conn, $userId) {
    // Get category_id from query string
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
    
    if ($category_id) {
        $stmt = $conn->prepare('SELECT id, title, description, completed, created_at FROM todos WHERE user_id = ? AND category_id = ? ORDER BY created_at DESC LIMIT 100');
        $stmt->bind_param('ii', $userId, $category_id);
    } else {
        $stmt = $conn->prepare('SELECT id, title, description, completed, created_at FROM todos WHERE user_id = ? ORDER BY created_at DESC LIMIT 100');
        $stmt->bind_param('i', $userId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $todos = [];
    while ($row = $result->fetch_assoc()) {
        $todos[] = $row;
    }
    $stmt->close();
    
    sendRes(['success' => true, 'todos' => $todos]);
}

function updateTodo($conn, $input, $userId) {
    $id = (int)($input['id'] ?? 0);
    $completed = (int)($input['completed'] ?? 0);
    $title = trim($input['title'] ?? '');

    if (!$id) {
        return sendRes(['success' => false, 'message' => 'Todo ID required']);
    }

    if ($completed !== 0 && $completed !== 1) $completed = 0;

    if ($title) {
        $stmt = $conn->prepare('UPDATE todos SET completed = ?, title = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->bind_param('isii', $completed, $title, $id, $userId);
    } else {
        $stmt = $conn->prepare('UPDATE todos SET completed = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->bind_param('iii', $completed, $id, $userId);
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        sendRes(['success' => true, 'message' => 'Todo diupdate']);
    } else {
        $stmt->close();
        sendRes(['success' => false, 'message' => 'Gagal update todo']);
    }
}

function deleteTodo($conn, $input, $userId) {
    $id = (int)($input['id'] ?? 0);

    if (!$id) {
        return sendRes(['success' => false, 'message' => 'Todo ID required']);
    }

    $stmt = $conn->prepare('DELETE FROM todos WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $userId);
    
    if ($stmt->execute()) {
        $stmt->close();
        sendRes(['success' => true, 'message' => 'Todo dihapus']);
    } else {
        $stmt->close();
        sendRes(['success' => false, 'message' => 'Gagal hapus todo']);
    }
}

function addLog($conn, $input, $userId) {
    $log_date = trim($input['log_date'] ?? '');
    $log_time = trim($input['log_time'] ?? '');
    $category = trim($input['category'] ?? '');

    if (!$log_date || !$log_time || !$category) {
        return sendRes(['success' => false, 'message' => 'Semua field required']);
    }

    $stmt = $conn->prepare('INSERT INTO activity_logs (user_id, log_date, log_time, category, created_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->bind_param('isss', $userId, $log_date, $log_time, $category);
    
    if ($stmt->execute()) {
        $log_id = $stmt->insert_id;
        $stmt->close();
        sendRes(['success' => true, 'message' => 'Log ditambahkan', 'log_id' => $log_id]);
    } else {
        $stmt->close();
        sendRes(['success' => false, 'message' => 'Gagal tambah log']);
    }
}

function getLogs($conn, $userId) {
    $stmt = $conn->prepare('SELECT id, log_date, log_time, category, created_at FROM activity_logs WHERE user_id = ? ORDER BY log_date DESC, log_time DESC LIMIT 500');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    $stmt->close();
    
    sendRes(['success' => true, 'logs' => $logs]);
}

function deleteLog($conn, $input, $userId) {
    $id = (int)($input['id'] ?? 0);

    if (!$id) {
        return sendRes(['success' => false, 'message' => 'Log ID required']);
    }

    $stmt = $conn->prepare('DELETE FROM activity_logs WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $userId);
    
    if ($stmt->execute()) {
        $stmt->close();
        sendRes(['success' => true, 'message' => 'Log dihapus']);
    } else {
        $stmt->close();
        sendRes(['success' => false, 'message' => 'Gagal hapus log']);
    }
}

// ===== CATEGORY FUNCTIONS =====

function addCategory($conn, $input, $userId) {
    $name = trim($input['name'] ?? '');
    $emoji = trim($input['emoji'] ?? '📝');

    if (!$name) {
        return sendRes(['success' => false, 'message' => 'Category name required']);
    }

    // Check if category name already exists for this user
    $stmt = $conn->prepare('SELECT id FROM categories WHERE user_id = ? AND name = ?');
    $stmt->bind_param('is', $userId, $name);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return sendRes(['success' => false, 'message' => 'Kategori dengan nama ini sudah ada']);
    }
    $stmt->close();

    $stmt = $conn->prepare('INSERT INTO categories (user_id, name, emoji, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('iss', $userId, $name, $emoji);
    
    if ($stmt->execute()) {
        $category_id = $stmt->insert_id;
        $stmt->close();
        sendRes(['success' => true, 'message' => 'Kategori ditambahkan', 'category_id' => $category_id]);
    } else {
        $stmt->close();
        sendRes(['success' => false, 'message' => 'Gagal tambah kategori']);
    }
}

function getCategories($conn, $userId) {
    $stmt = $conn->prepare('SELECT id, name, emoji, created_at FROM categories WHERE user_id = ? ORDER BY created_at ASC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $stmt->close();
    
    sendRes(['success' => true, 'categories' => $categories]);
}

function updateCategory($conn, $input, $userId) {
    $id = (int)($input['id'] ?? 0);
    $name = trim($input['name'] ?? '');
    $emoji = trim($input['emoji'] ?? '📝');

    if (!$id || !$name) {
        return sendRes(['success' => false, 'message' => 'Category ID and name required']);
    }

    // Check if updated name already exists for another category
    $stmt = $conn->prepare('SELECT id FROM categories WHERE user_id = ? AND name = ? AND id != ?');
    $stmt->bind_param('isi', $userId, $name, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return sendRes(['success' => false, 'message' => 'Kategori dengan nama ini sudah ada']);
    }
    $stmt->close();

    $stmt = $conn->prepare('UPDATE categories SET name = ?, emoji = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ssii', $name, $emoji, $id, $userId);
    
    if ($stmt->execute()) {
        $stmt->close();
        sendRes(['success' => true, 'message' => 'Kategori diupdate']);
    } else {
        $stmt->close();
        sendRes(['success' => false, 'message' => 'Gagal update kategori']);
    }
}

function deleteCategory($conn, $input, $userId) {
    $id = (int)($input['id'] ?? 0);

    if (!$id) {
        return sendRes(['success' => false, 'message' => 'Category ID required']);
    }

    // Delete all todos in this category
    $stmt = $conn->prepare('DELETE FROM todos WHERE category_id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $userId);
    $stmt->execute();
    $stmt->close();

    // Delete the category
    $stmt = $conn->prepare('DELETE FROM categories WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $userId);
    
    if ($stmt->execute()) {
        $stmt->close();
        sendRes(['success' => true, 'message' => 'Kategori dihapus']);
    } else {
        $stmt->close();
        sendRes(['success' => false, 'message' => 'Gagal hapus kategori']);
    }
}

// ===== LEARNING PROGRESS FUNCTIONS =====

// AI-based topic detection dari pembelajaran
function detectLearnedTopics($language, $input_text) {
    $text = strtolower($input_text);
    
    // Database materi per bahasa/kategori
    $topics_db = [
        'PHP' => [
            'Variables & Types' => ['variable', 'string', 'integer', 'array', 'bool', '$var', 'typeof'],
            'Functions' => ['function', 'def ', 'return', 'parameter', 'argument', 'call'],
            'Control Flow' => ['if', 'else', 'switch', 'case', 'for', 'while', 'loop', 'condition'],
            'OOP' => ['class', 'object', 'method', 'property', 'inheritance', 'extends', 'parent', 'constructor'],
            'Database' => ['sql', 'mysql', 'query', 'database', 'table', 'select', 'insert', 'update', 'delete'],
            'APIs' => ['api', 'rest', 'endpoint', 'request', 'response', 'json', 'xml', 'http'],
            'Error Handling' => ['try', 'catch', 'exception', 'throw', 'error', 'warning', 'fatal'],
            'Sessions & Cookies' => ['session', 'cookie', 'auth', 'login', 'logout', 'token'],
            'File Operations' => ['file', 'read', 'write', 'upload', 'directory', 'fopen', 'fclose'],
            'Security' => ['sql injection', 'xss', 'csrf', 'hash', 'encrypt', 'sanitize', 'validate']
        ],
        'JavaScript' => [
            'Variables & Types' => ['var', 'let', 'const', 'string', 'number', 'boolean', 'object', 'array'],
            'Functions' => ['function', 'arrow function', '=>', 'return', 'parameter', 'callback'],
            'DOM Manipulation' => ['document', 'queryselector', 'getelementby', 'innerhtml', 'textcontent', 'addeventlistener'],
            'Events' => ['click', 'change', 'submit', 'keydown', 'mouseover', 'event', 'listener'],
            'Async Programming' => ['promise', 'async', 'await', 'then', 'catch', 'settimeout', 'callback'],
            'APIs & Fetch' => ['fetch', 'api', 'request', 'response', 'json', 'httprequest'],
            'ES6+' => ['class', 'import', 'export', 'destructuring', 'spread', 'template literal', '${'],
            'Frameworks' => ['react', 'vue', 'angular', 'component', 'state', 'props'],
            'Debugging' => ['console.log', 'debugger', 'error', 'warning', 'breakpoint'],
            'Storage' => ['localstorage', 'sessionstorage', 'indexeddb', 'cookie']
        ],
        'HTML' => [
            'Basic Tags' => ['html', 'head', 'body', 'meta', 'title', 'link', 'script'],
            'Content Tags' => ['p', 'div', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'section', 'article'],
            'Semantic HTML' => ['header', 'footer', 'nav', 'main', 'aside', 'figure', 'figcaption'],
            'Forms' => ['form', 'input', 'label', 'textarea', 'select', 'button', 'placeholder', 'required'],
            'Media' => ['img', 'video', 'audio', 'source', 'iframe', 'embed'],
            'Lists' => ['ul', 'ol', 'li', 'dl', 'dt', 'dd'],
            'Tables' => ['table', 'tr', 'td', 'th', 'thead', 'tbody', 'tfoot', 'colspan', 'rowspan'],
            'Attributes' => ['id', 'class', 'style', 'data-', 'aria-', 'href', 'src', 'alt'],
            'Links' => ['a', 'href', 'target', 'rel', 'anchor'],
            'Accessibility' => ['alt text', 'aria', 'semantic', 'label', 'role']
        ],
        'CSS' => [
            'Selectors' => ['selector', '.class', '#id', '[attribute]', '>', '+', '~', ':hover', ':focus'],
            'Properties' => ['color', 'background', 'font', 'size', 'padding', 'margin', 'border', 'width', 'height'],
            'Box Model' => ['margin', 'padding', 'border', 'content', 'box-sizing', 'outline'],
            'Layout' => ['flexbox', 'flex', 'grid', 'display', 'position', 'float', 'clear'],
            'Positioning' => ['static', 'relative', 'absolute', 'fixed', 'sticky', 'top', 'left', 'right', 'bottom', 'z-index'],
            'Typography' => ['font-family', 'font-size', 'font-weight', 'line-height', 'letter-spacing', 'text-align'],
            'Colors & Backgrounds' => ['color', 'background-color', 'background-image', 'gradient', 'rgba', 'hex'],
            'Animations' => ['animation', 'transition', 'keyframes', '@keyframes', 'ease', 'duration'],
            'Responsive' => ['media query', '@media', 'breakpoint', 'mobile', 'tablet', 'desktop', 'viewport'],
            'CSS Grid' => ['grid', 'grid-template', 'gap', 'grid-area', 'fr', 'repeat']
        ],
        'Laravel' => [
            'Basics' => ['laravel', 'framework', 'mvc', 'model', 'view', 'controller', 'route'],
            'Routing' => ['route', 'get', 'post', 'put', 'delete', 'resource', 'group', 'middleware'],
            'Models' => ['eloquent', 'model', 'migration', 'schema', 'attribute', 'relationship'],
            'Controllers' => ['controller', 'action', 'method', 'request', 'response', 'middleware'],
            'Views & Blade' => ['blade', 'template', '@if', '@foreach', '@for', '@while', 'component'],
            'Database' => ['migration', 'seeder', 'factory', 'eloquent', 'query builder', 'db::'],
            'Authentication' => ['auth', 'login', 'register', 'middleware', 'guard', 'password'],
            'API Development' => ['api route', 'resource', 'json response', 'token', 'authorization'],
            'Testing' => ['test', 'unittest', 'feature test', 'phpunit', 'mock', 'assert'],
            'Deployment' => ['deploy', 'production', 'env', 'config', 'cache', 'artisan']
        ]
    ];
    
    $detected = [];
    
    if (isset($topics_db[$language])) {
        foreach ($topics_db[$language] as $topic => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, strtolower($keyword)) !== false) {
                    if (!in_array($topic, $detected)) {
                        $detected[] = $topic;
                    }
                    break;
                }
            }
        }
    }
    
    return $detected;
}

// Get learning progress
function getLearningProgress($conn, $userId) {
    $stmt = $conn->prepare('SELECT * FROM learning_progress WHERE user_id = ? ORDER BY language ASC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $progress = [];
    while ($row = $result->fetch_assoc()) {
        $row['topics_learned'] = json_decode($row['topics_learned'], true) ?? [];
        $progress[] = $row;
    }
    $stmt->close();
    
    sendRes(['success' => true, 'progress' => $progress]);
}

// Update learning progress
function updateLearningProgress($conn, $input, $userId) {
    $language = trim($input['language'] ?? '');
    $category = trim($input['category'] ?? '');
    $notes = trim($input['notes'] ?? '');
    $percentage = (int)($input['percentage'] ?? 0);
    
    if (!$language || !$category) {
        return sendRes(['success' => false, 'message' => 'Language dan category harus diisi']);
    }
    
    if ($percentage < 0 || $percentage > 100) {
        return sendRes(['success' => false, 'message' => 'Persentase harus antara 0-100']);
    }
    
    // Detect topics dari notes
    $new_topics = detectLearnedTopics($language, $notes);
    
    // Check if record exists
    $stmt = $conn->prepare('SELECT id, topics_learned FROM learning_progress WHERE user_id = ? AND language = ?');
    $stmt->bind_param('is', $userId, $language);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $existing_topics = json_decode($row['topics_learned'], true) ?? [];
        $merged_topics = array_unique(array_merge($existing_topics, $new_topics));
        $topics_json = json_encode(array_values($merged_topics));
        
        // Update existing
        $stmt2 = $conn->prepare('UPDATE learning_progress SET progress_percentage = ?, topics_learned = ?, category = ?, last_update = NOW() WHERE user_id = ? AND language = ?');
        $stmt2->bind_param('issii', $percentage, $topics_json, $category, $userId, $language);
        
        if ($stmt2->execute()) {
            $stmt->close();
            $stmt2->close();
            sendRes(['success' => true, 'message' => 'Progress diupdate', 'topics_detected' => $new_topics]);
        } else {
            $stmt->close();
            $stmt2->close();
            sendRes(['success' => false, 'message' => 'Gagal update progress']);
        }
    } else {
        // Insert new
        $stmt->close();
        $topics_json = json_encode($new_topics);
        
        $stmt = $conn->prepare('INSERT INTO learning_progress (user_id, language, category, progress_percentage, topics_learned) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('issis', $userId, $language, $category, $percentage, $topics_json);
        
        if ($stmt->execute()) {
            $stmt->close();
            sendRes(['success' => true, 'message' => 'Progress ditambahkan', 'topics_detected' => $new_topics]);
        } else {
            $stmt->close();
            sendRes(['success' => false, 'message' => 'Gagal tambah progress']);
        }
    }
}

function sendRes($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
