# DOKUMENTASI: FITUR KATEGORI DINAMIS TO-DO LIST

## 📋 RINGKASAN PERUBAHAN

Sistem To-Do List telah diupdate dengan fitur **kategori dinamis** yang memungkinkan pengguna untuk:
- ✅ Membuat kategori/workspace baru
- ✅ Mengedit nama dan emoji kategori
- ✅ Menghapus kategori (beserta semua task di dalamnya)
- ✅ Interface yang responsif dan estetis
- ✅ Modular dan mudah diperluas

---

## 🎯 FITUR UTAMA

### 1. **Tambah Kategori**
- Klik tombol **"+ Tambah Kategori"** di bagian atas halaman To-Do List
- Modal akan membuka untuk input nama kategori dan pilih emoji
- Setiap kategori dapat diidentifikasi dengan emoji yang berbeda-beda

### 2. **Edit Kategori**
- Klik tombol **"✏️ Edit"** pada setiap kategori
- Ubah nama dan emoji kategori sesuai kebutuhan
- Perubahan akan disimpan ke database

### 3. **Hapus Kategori**
- Klik tombol **"🗑️ Hapus"** pada setiap kategori
- Konfirmasi penghapusan akan ditampilkan
- **Semua task dalam kategori akan dihapus**

### 4. **Kelola Task dalam Kategori**
- Setiap kategori memiliki input field untuk menambah task baru
- Sama seperti sebelumnya: checkbox untuk menyelesaikan, tombol hapus untuk menghapus task
- Task otomatis tersimpan ke database dengan kategori yang sesuai

---

## 🗄️ STRUKTUR DATABASE

### Tabel Baru: `categories`
```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    emoji VARCHAR(10) DEFAULT '📝',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_category_per_user (user_id, name)
);
```

### Modifikasi Tabel: `todos`
- Kolom lama `category` (VARCHAR) dihapus
- Kolom baru `category_id` (INT) ditambahkan
- Relationship dengan tabel `categories` diatur

---

## 🔧 SETUP DATABASE

### Opsi 1: Menggunakan File SQL Baru (REKOMENDASI)
1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Login dengan credentials MySQL Anda
3. Pilih database `todo_list`
4. Klik tab **SQL**
5. Import file: `db_setup_updated.sql`

### Opsi 2: Manual Migration
Jika database sudah ada, jalankan query berikut:

```sql
-- Buat tabel categories
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    emoji VARCHAR(10) DEFAULT '📝',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_per_user (user_id, name)
);

-- Modifikasi tabel todos
ALTER TABLE todos ADD COLUMN category_id INT AFTER user_id;
ALTER TABLE todos ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE;

-- Tambah indexes
CREATE INDEX idx_user_categories ON categories(user_id);
CREATE INDEX idx_category_todos ON todos(category_id);
```

---

## 📱 INTERFACE & USER EXPERIENCE

### Design Principles
✨ **Modular** - Setiap kategori adalah komponen terpisah yang dapat dikelola independen
✨ **Responsive** - Bekerja sempurna di desktop, tablet, dan mobile
✨ **Estetis** - Gradient header, animated modal, smooth transitions
✨ **Intuitif** - Tombol aksi jelas dengan icon emoji

### Komponen UI Baru

#### 1. **Category Header**
```
┌─────────────────────────────────────────────┐
│  📚 Kategori To-Do List Saya  [+ Tambah]    │
└─────────────────────────────────────────────┘
```

#### 2. **Category Segment**
```
┌──────────────────────────────────────────────┐
│ 📝 Nama Kategori    [✏️ Edit] [🗑️ Hapus]     │
├──────────────────────────────────────────────┤
│ [Input Task] [Tambah]                        │
├──────────────────────────────────────────────┤
│ ☑ Task 1                              [Hapus]│
│ ☐ Task 2                              [Hapus]│
└──────────────────────────────────────────────┘
```

#### 3. **Add/Edit Category Modal**
```
┌──────────────────────────────────────────────┐
│ Tambah Kategori Baru                    [✕]  │
├──────────────────────────────────────────────┤
│ [Masukkan nama kategori...]                  │
│ [Emoji atau icon]                            │
│                      [Batal] [Simpan]        │
└──────────────────────────────────────────────┘
```

---

## 📝 JAVASCRIPT FUNCTIONS

### Category Management
```javascript
// Load semua kategori dari server
loadCategories()

// Render kategori ke halaman
renderCategories()

// Tampilkan modal tambah kategori
openAddCategoryModal()

// Tampilkan modal edit kategori
openEditCategoryModal(id, name, emoji)

// Tutup modal
closeCategoryModal()

// Simpan kategori (create/update)
saveCategory()

// Hapus kategori
deleteCategory(categoryId)
```

### Task Management (Updated)
```javascript
// Load tasks untuk kategori tertentu
loadTasksForCategory(categoryId)

// Tambah task ke kategori
addTask(categoryId)

// Toggle status task
toggleComplete(checkbox, todoId)

// Hapus task
deleteTask(deleteBtn, todoId)
```

### Utility Functions
```javascript
// Update dropdown pilihan kategori di form log
updateLogSegmentOptions()
```

---

## 🔌 API ENDPOINTS (Backend)

### Kategori Endpoints

#### GET `/api.php?action=get_categories`
Mengambil semua kategori pengguna
```json
{
  "success": true,
  "categories": [
    { "id": 1, "name": "Pelajaran", "emoji": "📚", "created_at": "..." },
    { "id": 2, "name": "Kerja", "emoji": "💼", "created_at": "..." }
  ]
}
```

#### POST `/api.php?action=add_category`
Menambah kategori baru
```json
{
  "name": "Nama Kategori",
  "emoji": "📝"
}
```

#### POST `/api.php?action=update_category`
Mengupdate kategori
```json
{
  "id": 1,
  "name": "Nama Baru",
  "emoji": "📚"
}
```

#### POST `/api.php?action=delete_category`
Menghapus kategori
```json
{
  "id": 1
}
```

#### GET `/api.php?action=get_todos&category_id=1`
Mengambil tasks untuk kategori tertentu
```json
{
  "success": true,
  "todos": [
    { "id": 1, "title": "Task 1", "completed": 0, ... }
  ]
}
```

---

## 🚀 CARA MENGGUNAKAN

### Pertama Kali Setup
1. Update database menggunakan file `db_setup_updated.sql`
2. Refresh halaman index.html
3. Login dengan akun Anda
4. Klik tombol **"+ Tambah Kategori"**

### Contoh Workflow
1. **Buat Kategori Baru**: "Coding" dengan emoji 💻
2. **Tambah Task**: "Buat login page"
3. **Edit Kategori**: Ubah emoji jadi 🖥️
4. **Tandai Selesai**: Click checkbox
5. **Lihat Dashboard**: Monitoring progress di tab Dashboard

---

## 🎨 CSS CLASSES BARU

- `.category-header` - Header dengan tombol tambah
- `.add-category-btn` - Tombol tambah kategori
- `.modal` - Modal container
- `.modal-content` - Isi modal
- `.modal-header` - Header modal
- `.modal-input` - Input dalam modal
- `.modal-buttons` - Buttons dalam modal
- `.save-btn`, `.cancel-btn` - Action buttons
- `.segment-header` - Header dalam segment
- `.category-actions` - Aksi edit/hapus
- `.edit-category-btn`, `.delete-category-btn` - Aksi buttons
- `.empty-state` - State kosong

---

## ⚙️ KONFIGURASI

### API URL
Default: `http://localhost/To-Do-List Website/api.php`

Jika berbeda, update di index.html:
```javascript
const API_URL = 'http://your-server/api.php';
```

### Emoji Default
Default emoji untuk kategori baru: `📝`

Ubah di modal input atau fungsi JavaScript.

---

## 🐛 TROUBLESHOOTING

### Kategori tidak muncul
- ✅ Pastikan database sudah dimigrasikan
- ✅ Refresh halaman browser
- ✅ Check console untuk error messages

### Tidak bisa tambah kategori
- ✅ Check apakah sudah login
- ✅ Pastikan token valid
- ✅ Check browser console untuk error

### Task tidak tersimpan
- ✅ Pastikan kategori sudah dipilih
- ✅ Check network requests di F12 DevTools
- ✅ Pastikan API URL benar

---

## 📈 FUTURE IMPROVEMENTS

Fitur yang bisa ditambahkan:
- [ ] Sorting kategori (drag & drop)
- [ ] Color coding untuk kategori
- [ ] Category templates (preset)
- [ ] Share kategori dengan user lain
- [ ] Archive kategori
- [ ] Bulk operations
- [ ] Category statistics

---

## 📞 SUPPORT

Untuk bantuan atau masalah:
1. Check file ini terlebih dahulu
2. Lihat console browser untuk error messages
3. Check bahwa database sudah ter-setup dengan benar

---

**Selamat menggunakan fitur kategori dinamis! 🎉**

Version: 2.0 (With Dynamic Categories)
Last Updated: February 27, 2026
