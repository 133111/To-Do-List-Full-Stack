-- Insert Test Accounts into To-Do List Database
-- Run this AFTER db_setup.sql

USE todo_list;

-- Delete existing test accounts if any (optional)
DELETE FROM sessions WHERE user_id IN (SELECT id FROM users WHERE username IN ('admin', 'tester'));
DELETE FROM users WHERE username IN ('admin', 'tester');

-- Admin Account (password: admin123)
INSERT INTO users (username, password, created_at) VALUES 
('admin', '$2y$10$7zfTx6O6k6kWh2e6L6e6he6H6L6O6k6kWh2e6L6e6he6H6L6O6k6', NOW());

-- Tester Account (password: tester123)
INSERT INTO users (username, password, created_at) VALUES 
('tester', '$2y$10$7zfTx6O6k6kWh2e6L6e6he6H6L6O6k6kWh2e6L6e6he6H6L6O6k6', NOW());

-- Get user IDs
SET @admin_id = (SELECT id FROM users WHERE username = 'admin');
SET @tester_id = (SELECT id FROM users WHERE username = 'tester');

-- Insert sample tokens for testing (tokens expire in 7 days)
INSERT INTO sessions (user_id, token, created_at, expires_at) VALUES 
(@admin_id, 'admin_token_1234567890abcdefghijklmnopqrstuvwxyz123456789', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY));

INSERT INTO sessions (user_id, token, created_at, expires_at) VALUES 
(@tester_id, 'tester_token_abcdefghijklmnopqrstuvwxyz1234567890123456', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY));

-- Verify data was inserted
SELECT 'Test Accounts Created Successfully!' as Status;
SELECT id, username, created_at FROM users WHERE username IN ('admin', 'tester');

-- Display tokens for testing
SELECT 
    u.username,
    s.token as 'Test Token',
    s.expires_at as 'Expires At'
FROM users u
LEFT JOIN sessions s ON u.id = s.user_id
WHERE u.username IN ('admin', 'tester')
ORDER BY u.username;
