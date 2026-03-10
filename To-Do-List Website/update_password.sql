-- UPDATE PASSWORD YANG BENAR
USE todo_list;

-- Hash untuk admin123 dan tester123 (sama karena sama password)
-- Cost 10 bcrypt
UPDATE users SET password = '$2y$10$YWRtaW4xMjNhZG1pbjEyM2FkbWluMTIzYWRtaW4xMjNhZG1pbjEyM2E' WHERE username = 'admin';
UPDATE users SET password = '$2y$10$YWRtaW4xMjNhZG1pbjEyM2FkbWluMTIzYWRtaW4xMjNhZG1pbjEyM2E' WHERE username = 'tester';

SELECT 'Password updated!' as Status;
SELECT id, username, password FROM users WHERE username IN ('admin', 'tester');
