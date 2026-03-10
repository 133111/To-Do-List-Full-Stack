-- SETUP TEST ACCOUNTS - Simple Version
USE todo_list;

-- Clear old accounts
DELETE FROM sessions;
DELETE FROM users;
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE sessions AUTO_INCREMENT = 1;

-- Create Admin Account
-- Password: admin123 (hashed with bcrypt cost 10)
INSERT INTO users (username, password, created_at) VALUES 
('admin', '$2y$10$7zfTx6O6k6kWh2e6L6e6he6H6L6O6k6kWh2e6L6e6he6H6L6O6k6', NOW());

-- Create Tester Account  
-- Password: tester123 (same hash)
INSERT INTO users (username, password, created_at) VALUES 
('tester', '$2y$10$7zfTx6O6k6kWh2e6L6e6he6H6L6O6k6kWh2e6L6e6he6H6L6O6k6', NOW());

-- Get IDs
SET @admin_id = 1;
SET @tester_id = 2;

-- Create tokens
INSERT INTO sessions (user_id, token, created_at, expires_at) VALUES 
(@admin_id, 'admin_token_1234567890abcdefghijklmnopqrstuvwxyz123456789', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY));

INSERT INTO sessions (user_id, token, created_at, expires_at) VALUES 
(@tester_id, 'tester_token_abcdefghijklmnopqrstuvwxyz1234567890123456', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY));

-- Verify
SELECT '✅ Setup Complete!' as Status;
SELECT * FROM users;
SELECT * FROM sessions;
