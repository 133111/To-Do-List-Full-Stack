# 📝 To-Do List Website dengan Autentikasi

## ✨ Fitur Utama

✅ **Autentikasi Lengkap**
- Login & Register
- Password hashing (base64)
- Token-based authentication
- Auto logout & redirect

✅ **Dashboard To-Do List**
- Kelola tugas dengan kategori
- Mark as complete/incomplete
- Delete tasks
- Activity logging

✅ **Database**
- Local Storage Browser-based
- Tidak perlu PHP atau server
- Data persisten (sampai clear cache)

✅ **Responsive Design**
- Mobile friendly
- Sidebar collapsible
- Modern UI dengan gradient

---

## 🚀 Cara Memulai

### Metode 1: Langsung Buka File (Paling Mudah)
```
1. Navigasi ke folder project
2. Buka file: login.html
3. Selesai! Website langsung jalan
```

### Metode 2: Buka via Browser
```
1. Buka browser (Chrome, Firefox, Edge, Safari)
2. Tekan Ctrl+O (atau Cmd+O di Mac)
3. Pilih file: login.html
4. Selesai!
```

### Metode 3: Drag & Drop
```
1. Drag file login.html ke browser
2. Selesai!
```

---

## 🔐 Login Credentials

### Demo Account
```
Username: demo
Password: demo123
```

### Membuat Akun Baru
```
1. Klik tab "Register" di halaman login
2. Masukkan username (min. 3 karakter)
3. Masukkan password (min. 6 karakter)
4. Konfirmasi password
5. Klik "Register"
6. Login dengan akun baru Anda
```

---

## 📁 Struktur File

```
To-Do-List Website/
├── login.html           ← Halaman Login & Register
├── index.html           ← Dashboard To-Do List
├── config.php           ← Konfigurasi (untuk future backend)
├── auth.php             ← Template API (untuk future backend)
├── api_tester.html      ← Tool untuk test API
├── README.md            ← Dokumentasi ini
└── SETUP.txt           ← Quick start guide
```

---

## 💾 Penyimpanan Data

### Local Storage
Semua data disimpan di **Browser Local Storage**:

**Users Table**
```
id              → Unique identifier
username        → Nama akun
password        → Password (hashed)
createdAt       → Waktu pembuatan
```

**Todos Table**
```
id              → Unique identifier
username        → User pemilik
title           → Judul to-do
description     → Deskripsi (optional)
completed       → Status (true/false)
category        → Kategori
priority        → Prioritas (low/medium/high)
due_date        → Tanggal deadline
created_at      → Waktu pembuatan
updated_at      → Waktu update terakhir
```

---

## 🔧 API Endpoints (JavaScript)

### Authentication Object
```javascript
db.registerUser(username, password)
db.getUserByUsername(username)
db.login(username, password)
db.logout()
db.isAuthenticated()
```

### Contoh Penggunaan
```javascript
// Login
const result = db.login('demo', 'demo123');
if (result.success) {
    console.log('Login berhasil');
    console.log('Token:', result.token);
}

// Register
const reg = db.registerUser('user123', 'pass123');
if (reg.success) {
    console.log('Registrasi berhasil');
}

// Logout
db.logout();

// Check authentication
if (db.isAuthenticated()) {
    console.log('User sudah login');
}
```

---

## 🎨 UI Components

### Halaman Login
- Email & Password input
- Tab Login/Register
- Error & Success messages
- Demo credentials info
- Loading spinner

### Dashboard
- Sidebar navigation
- User info & Logout button
- Greeting dengan waktu
- To-Do categories
  - Pelajaran Sekolah
  - Pemrograman Website
  - Linux & Jaringan
  - Desain Grafis
- Activity logging
- Dashboard dengan grafik

---

## 📊 Fitur Dashboard

### Tab To-Do List
- Add tasks per kategori
- Toggle complete status
- Delete tasks
- Log aktivitas
- Timestamps

### Tab Dashboard
- Tabel activity log
- Grafik jumlah to-do per kategori
- Statistik penggunaan

---

## 🛡️ Security

✅ **Password Hashing**
- Menggunakan base64 encoding
- Untuk production: upgrade ke bcrypt

✅ **Token Security**
- Random token generation
- Stored in Local Storage
- Validated on each request

✅ **User Isolation**
- Setiap user hanya lihat data miliknya
- Token verification di setiap action

⚠️ **Catatan Security**
- Local Storage bukan enkripsi penuh
- Untuk production:
  - Gunakan HTTPS
  - Implement bcrypt hashing
  - Server-side authentication
  - CSRF protection
  - Rate limiting

---

## 🔄 Browser Support

✅ Didukung:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Opera 76+

---

## 💡 Tips & Tricks

### Menjaga Data
```javascript
// Backup data ke localStorage
const backup = localStorage.getItem('users');
console.log(backup);

// Export users sebagai JSON
const users = JSON.parse(localStorage.getItem('users') || '[]');
console.log(JSON.stringify(users, null, 2));
```

### Clear All Data
```javascript
// Clear semua data (HATI-HATI!)
localStorage.clear();
// Refresh page
location.reload();
```

### Lihat Data
```javascript
// Di Browser Console (F12)
console.log(JSON.parse(localStorage.getItem('users')));
console.log(JSON.parse(localStorage.getItem('authToken')));
```

---

## 🚀 Upgrade ke Production

### Option 1: Node.js + Express + MongoDB
```
Backend: Express.js REST API
Database: MongoDB Atlas
Security: JWT tokens + bcrypt
Deployment: Heroku / Vercel
```

### Option 2: PHP + MySQL
```
Backend: PHP REST API
Database: MySQL / MariaDB
Security: Session tokens + bcrypt
Deployment: Shared Hosting / VPS
```

### Option 3: Python + Flask + PostgreSQL
```
Backend: Flask REST API
Database: PostgreSQL
Security: JWT + bcrypt
Deployment: PythonAnywhere / Digital Ocean
```

---

## 🐛 Troubleshooting

### Data Hilang Setelah Refresh
**Penyebab:** Browser cache di-clear atau Local Storage terbatas
**Solusi:** Gunakan backend server untuk persistent storage

### Tidak Bisa Login
**Penyebab:** Username/password salah atau browser cache
**Solusi:**
1. Pastikan credentials benar
2. Clear browser cache (Ctrl+Shift+Delete)
3. Register akun baru

### Website Blank / Tidak Muncul
**Penyebab:** File tidak ditemukan atau browser tidak support
**Solusi:**
1. Pastikan semua file ada di folder yang benar
2. Gunakan browser modern (Chrome/Firefox/Edge)
3. Buka developer console (F12) untuk melihat error

### Sidebar Tidak Responsive
**Penyebab:** JavaScript disabled
**Solusi:** Enable JavaScript di browser settings

---

## 📞 Support & Contact

**Issues atau bugs?**
1. Check browser console (F12)
2. Verify file structure
3. Clear cache & try again
4. Check README.md for more info

**Feature Requests?**
- Create issue dengan deskripsi jelas
- Include screenshot jika perlu

---

## 📝 Changelog

### v1.0.0 (Feb 6, 2026)
- Initial release
- Login/Register functionality
- To-Do CRUD operations
- Dashboard dengan grafik
- Responsive design
- Local Storage database

---

## 📄 License

This project is open source and available under the MIT License.

---

## 👨‍💻 Developer

Created with ❤️ for efficient task management

**Version:** 1.0.0
**Last Updated:** February 6, 2026
**Database:** Local Storage (Browser-based)
**Technology:** HTML5 + CSS3 + JavaScript (Vanilla)

---

**Ready to use! No installation needed. Just open login.html in your browser.**

