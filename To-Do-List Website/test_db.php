<?php
// Quick Database Test Script
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'todo_list';

echo "Connecting to MySQL...\n";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo "❌ Connection Failed: " . $conn->connect_error . "\n";
    exit;
}

echo "✅ Connected to database!\n\n";

// Test 1: Check users table
echo "📋 Checking users table...\n";
$result = $conn->query("SELECT * FROM users");
if (!$result) {
    echo "❌ Error: " . $conn->error . "\n";
} else {
    echo "✅ Users table exists. Records: " . $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        echo "   - ID: {$row['id']}, Username: {$row['username']}\n";
    }
}

// Test 2: Insert test user
echo "\n📝 Trying to insert test user...\n";
$stmt = $conn->prepare("INSERT INTO users (username, password, created_at) VALUES (?, ?, NOW())");
$username = "testuser_" . time();
$password = password_hash('testpass123', PASSWORD_BCRYPT);

$stmt->bind_param("ss", $username, $password);
if ($stmt->execute()) {
    echo "✅ Insert successful! New user: $username\n";
} else {
    echo "❌ Insert failed: " . $stmt->error . "\n";
}

// Test 3: Show all users again
echo "\n📋 Current users in database:\n";
$result = $conn->query("SELECT id, username, created_at FROM users ORDER BY id DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "   - ID: {$row['id']}, Username: {$row['username']}, Created: {$row['created_at']}\n";
}

$conn->close();
echo "\n✅ Tests completed!\n";
?>
