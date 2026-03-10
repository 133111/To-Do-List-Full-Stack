# 🔧 FIX: ERROR "Unexpected end of JSON input"

## 🐛 MASALAH

Ketika mencoba menambah kategori, muncul error:
```
Error menyimpan kategori: Failed to execute 'json' on 'Response': 
Unexpected end of JSON input
```

## 🔍 PENYEBAB

Tabel `categories` **belum ada** di database MySQL. Ketika JavaScript mengirim request ke API untuk membuat kategori, API mencoba INSERT ke tabel yang tidak ada, menghasilkan error database, dan mengembalikan response yang bukan JSON valid.

## ✅ SOLUSI

### Step 1: Buat Tabel Categories

1. Buka **phpMyAdmin**: http://localhost/phpmyadmin
2. Pilih database **"todo_list"**
3. Klik tab **"SQL"**
4. Copy-paste code berikut:

```sql
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
```

5. Klik **"Go"** (atau tombol Execute)
6. Tunggu sampai selesai. Harus muncul pesan: ✅ "1 row affected" atau similar

### Step 2: Modifikasi Tabel Todos (OPTIONAL - hanya jika belum ada)

1. Di phpMyAdmin, pilih table **"todos"**
2. Klik tab **"Structure"**
3. **CARI apakah sudah ada column "category_id"**
   - Jika **SUDAH ada** → Skip step ini
   - Jika **BELUM ada** → Lanjut ke step 4

4. Jika belum ada, kembali ke tab SQL dan run:

```sql
ALTER TABLE todos ADD COLUMN category_id INT AFTER user_id;
ALTER TABLE todos ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE;
```

### Step 3: Tambah Indexes

Masih di tab SQL, run:

```sql
CREATE INDEX idx_user_categories ON categories(user_id);
CREATE INDEX idx_category_todos ON todos(category_id);
```

### Step 4: VERIFIKASI

1. Di phpMyAdmin, pilih database **"todo_list"**
2. Lihat di sidebar daftar tables:
   - ✅ **categories** ← Harus ada (table baru)
   - ✅ **todos** ← Harus ada dengan column baru

3. Klik table **"categories"**
4. Klik **"Structure"** tab
5. Pastikan columns ada:
   - id
   - user_id (FK ke users)
   - name
   - emoji
   - created_at
   - updated_at

## 🚀 SETELAH FIX

1. **Refresh halaman index.html** di browser
2. **Clear cache** (Ctrl + Shift + Delete)
3. Login dengan akun Anda
4. Klik tombol **"+ Tambah Kategori"**
5. Isi nama kategori: "Test"
6. Klik **"Simpan"**
7. **SEHARUSNYA BERHASIL!** ✅

## 📋 TROUBLESHOOTING

### Error: "Column 'user_id' doesn't exist"
**Penyebab:** Tabel users tidak ada atau tidak ter-setup  
**Solusi:** Jalankan `db_setup.sql` terlebih dahulu

### Error: "Table 'todo_list.categories' doesn't exist"
**Penyebab:** CREATE TABLE query tidak jalan  
**Solusi:**
- Copy-paste query lagi
- Pastikan di database "todo_list"
- Check error message di phpMyAdmin

### Masih error "Unexpected end of JSON input"
**Penyebab:** Database setup belum complete  
**Solusi:**
1. Buka browser F12 → Network tab
2. Click "+ Tambah Kategori"
3. Lihat request ke "api.php"
4. Check response status code:
   - 200 OK = Database OK, beda error
   - 500 = Database error, check phpMyAdmin
   - 401 = Token error, login lagi

### Response 500 Internal Server Error
**Penyebab:** Database error  
**Solusi:**
1. Check file: `api_errors.log`
2. Baca error message
3. Usually: "Unknown column" atau "Table doesn't exist"
4. Jalankan CREATE TABLE query lagi

## 📝 QUICK CHECKLIST

Sebelum test lagi:

```
☑ categories table dibuat
☑ todos.category_id column ada (atau tidak perlu jika tidak di-reference)
☑ Indexes dibuat
☑ Verify di phpMyAdmin
☑ Refresh browser
☑ Login lagi
☑ Test tambah kategori
```

## 🎯 EXPECTED RESULT

Setelah fix, flow yang benar adalah:

```
1. User klik "+ Tambah Kategori"
   ↓
2. Modal dialog muncul
   ↓
3. User isi nama & emoji
   ↓
4. User klik "Simpan"
   ↓
5. JavaScript kirim request ke api.php?action=add_category
   ↓
6. API INSERT ke table categories
   ↓
7. API return JSON: { success: true, category_id: 1 }
   ↓
8. JavaScript reload categories
   ↓
9. Kategori baru muncul di halaman ✅
```

---

**Jika masih error setelah semua ini, lakukan:**

1. Check api_errors.log untuk error message spesifik
2. Screenshot error message
3. Verify database connection di config.php
4. Test koneksi MySQL di phpMyAdmin
5. Jika perlu, reset database dan run db_setup_updated.sql

Semoga berhasil! 🚀
