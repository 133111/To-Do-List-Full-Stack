UPDATE users SET password = '$2y$10$8ls9/5KLCBm3zlpYhj9f8Oeh1AFEUgqpl3oLCFWEB0hDOvXZ.6a4q' WHERE username = 'admin';
UPDATE users SET password = '$2y$10$8ls9/5KLCBm3zlpYhj9f8Oeh1AFEUgqpl3oLCFWEB0hDOvXZ.6a4q' WHERE username = 'tester';
SELECT 'Password updated successfully!' as Status;
