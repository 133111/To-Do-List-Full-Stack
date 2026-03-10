/**
 * To-Do List Backend Server
 * Node.js + Express + SQLite
 * 
 * Setup:
 * 1. npm install express sqlite3 bcryptjs cors dotenv body-parser
 * 2. node server.js
 * 3. Akses: http://localhost:3000
 */

const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const bcrypt = require('bcryptjs');
const cors = require('cors');
const bodyParser = require('body-parser');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(express.static(__dirname));

// Database setup
const dbPath = path.join(__dirname, 'todo_database.db');
const db = new sqlite3.Database(dbPath, (err) => {
    if (err) {
        console.error('Database connection error:', err);
    } else {
        console.log('✅ Connected to SQLite database');
        initializeDatabase();
    }
});

// Initialize database tables
function initializeDatabase() {
    db.serialize(() => {
        // Create users table
        db.run(`
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        `, (err) => {
            if (err) console.error('Users table error:', err);
            else console.log('✅ Users table ready');
        });

        // Create todos table
        db.run(`
            CREATE TABLE IF NOT EXISTS todos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                description TEXT,
                completed INTEGER DEFAULT 0,
                category TEXT,
                priority TEXT DEFAULT 'medium',
                due_date TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        `, (err) => {
            if (err) console.error('Todos table error:', err);
            else console.log('✅ Todos table ready');
        });

        // Create logs table
        db.run(`
            CREATE TABLE IF NOT EXISTS activity_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                log_date TEXT NOT NULL,
                log_time TEXT NOT NULL,
                category TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        `, (err) => {
            if (err) console.error('Logs table error:', err);
            else console.log('✅ Logs table ready');
        });

        // Insert demo user if not exists
        db.run(`
            INSERT OR IGNORE INTO users (username, password)
            VALUES (?, ?)
        `, ['demo', bcrypt.hashSync('demo123', 10)], (err) => {
            if (err) console.error('Demo user error:', err);
            else console.log('✅ Demo account ready (demo/demo123)');
        });
    });
}

// ===== AUTHENTICATION ROUTES =====

// Register
app.post('/api/auth/register', (req, res) => {
    const { username, password, passwordConfirm } = req.body;

    if (!username || !password) {
        return res.json({ success: false, message: 'Username dan password wajib diisi' });
    }

    if (username.length < 3) {
        return res.json({ success: false, message: 'Username minimal 3 karakter' });
    }

    if (password.length < 6) {
        return res.json({ success: false, message: 'Password minimal 6 karakter' });
    }

    if (password !== passwordConfirm) {
        return res.json({ success: false, message: 'Password tidak cocok' });
    }

    const hashedPassword = bcrypt.hashSync(password, 10);

    db.run(
        'INSERT INTO users (username, password) VALUES (?, ?)',
        [username, hashedPassword],
        function(err) {
            if (err) {
                if (err.message.includes('UNIQUE')) {
                    return res.json({ success: false, message: 'Username sudah terdaftar' });
                }
                return res.json({ success: false, message: 'Error: ' + err.message });
            }
            res.json({ 
                success: true, 
                message: 'Registrasi berhasil',
                userId: this.lastID 
            });
        }
    );
});

// Login
app.post('/api/auth/login', (req, res) => {
    const { username, password } = req.body;

    if (!username || !password) {
        return res.json({ success: false, message: 'Username dan password wajib diisi' });
    }

    db.get(
        'SELECT id, username, password FROM users WHERE username = ?',
        [username],
        (err, user) => {
            if (err) {
                return res.json({ success: false, message: 'Database error' });
            }

            if (!user) {
                return res.json({ success: false, message: 'Username tidak ditemukan' });
            }

            const passwordMatch = bcrypt.compareSync(password, user.password);
            if (!passwordMatch) {
                return res.json({ success: false, message: 'Password salah' });
            }

            const token = generateToken();
            res.json({
                success: true,
                message: 'Login berhasil',
                token: token,
                username: user.username,
                userId: user.id
            });
        }
    );
});

// ===== TO-DO ROUTES =====

// Get all todos for user
app.post('/api/todos/get', (req, res) => {
    const { userId } = req.body;

    if (!userId) {
        return res.json({ success: false, message: 'User ID required' });
    }

    db.all(
        'SELECT * FROM todos WHERE user_id = ? ORDER BY created_at DESC',
        [userId],
        (err, rows) => {
            if (err) {
                return res.json({ success: false, message: 'Database error' });
            }
            res.json({ success: true, todos: rows });
        }
    );
});

// Add todo
app.post('/api/todos/add', (req, res) => {
    const { userId, title, description, category, priority, dueDate } = req.body;

    if (!userId || !title) {
        return res.json({ success: false, message: 'User ID dan title wajib diisi' });
    }

    db.run(
        `INSERT INTO todos (user_id, title, description, category, priority, due_date)
         VALUES (?, ?, ?, ?, ?, ?)`,
        [userId, title, description || '', category || '', priority || 'medium', dueDate || null],
        function(err) {
            if (err) {
                return res.json({ success: false, message: 'Database error' });
            }
            res.json({ 
                success: true, 
                message: 'Todo berhasil ditambahkan',
                id: this.lastID 
            });
        }
    );
});

// Update todo
app.post('/api/todos/update', (req, res) => {
    const { userId, id, title, description, completed, category, priority, dueDate } = req.body;

    if (!userId || !id) {
        return res.json({ success: false, message: 'User ID dan todo ID wajib diisi' });
    }

    db.run(
        `UPDATE todos 
         SET title = ?, description = ?, completed = ?, category = ?, priority = ?, due_date = ?, updated_at = CURRENT_TIMESTAMP
         WHERE id = ? AND user_id = ?`,
        [title || '', description || '', completed || 0, category || '', priority || 'medium', dueDate || null, id, userId],
        function(err) {
            if (err) {
                return res.json({ success: false, message: 'Database error' });
            }
            if (this.changes === 0) {
                return res.json({ success: false, message: 'Todo tidak ditemukan' });
            }
            res.json({ success: true, message: 'Todo berhasil diupdate' });
        }
    );
});

// Delete todo
app.post('/api/todos/delete', (req, res) => {
    const { userId, id } = req.body;

    if (!userId || !id) {
        return res.json({ success: false, message: 'User ID dan todo ID wajib diisi' });
    }

    db.run(
        'DELETE FROM todos WHERE id = ? AND user_id = ?',
        [id, userId],
        function(err) {
            if (err) {
                return res.json({ success: false, message: 'Database error' });
            }
            if (this.changes === 0) {
                return res.json({ success: false, message: 'Todo tidak ditemukan' });
            }
            res.json({ success: true, message: 'Todo berhasil dihapus' });
        }
    );
});

// ===== ACTIVITY LOG ROUTES =====

// Get all logs for user
app.post('/api/logs/get', (req, res) => {
    const { userId } = req.body;

    if (!userId) {
        return res.json({ success: false, message: 'User ID required' });
    }

    db.all(
        'SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC',
        [userId],
        (err, rows) => {
            if (err) {
                return res.json({ success: false, message: 'Database error' });
            }
            res.json({ success: true, logs: rows });
        }
    );
});

// Add log
app.post('/api/logs/add', (req, res) => {
    const { userId, logDate, logTime, category } = req.body;

    if (!userId || !logDate || !logTime || !category) {
        return res.json({ success: false, message: 'Semua field wajib diisi' });
    }

    db.run(
        `INSERT INTO activity_logs (user_id, log_date, log_time, category)
         VALUES (?, ?, ?, ?)`,
        [userId, logDate, logTime, category],
        function(err) {
            if (err) {
                return res.json({ success: false, message: 'Database error' });
            }
            res.json({ 
                success: true, 
                message: 'Log berhasil disimpan',
                id: this.lastID 
            });
        }
    );
});

// Delete log
app.post('/api/logs/delete', (req, res) => {
    const { userId, id } = req.body;

    if (!userId || !id) {
        return res.json({ success: false, message: 'User ID dan log ID wajib diisi' });
    }

    db.run(
        'DELETE FROM activity_logs WHERE id = ? AND user_id = ?',
        [id, userId],
        function(err) {
            if (err) {
                return res.json({ success: false, message: 'Database error' });
            }
            res.json({ success: true, message: 'Log berhasil dihapus' });
        }
    );
});

// ===== UTILITY FUNCTIONS =====

function generateToken() {
    return 'token_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
}

// Start server
app.listen(PORT, () => {
    console.log('\n═══════════════════════════════════════════════════');
    console.log('  📝 To-Do List Server Started');
    console.log('═══════════════════════════════════════════════════');
    console.log(`  ✅ Server running at http://localhost:${PORT}`);
    console.log(`  📁 Database: ${dbPath}`);
    console.log('═══════════════════════════════════════════════════\n');
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n\n🛑 Shutting down server...');
    db.close((err) => {
        if (err) console.error(err);
        console.log('✅ Database connection closed');
        process.exit(0);
    });
});
