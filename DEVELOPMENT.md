# Prozone Development Guide

## Development Server Scripts

Untuk memudahkan development lokal, project ini menyediakan script otomatis yang:
- ✅ Mendeteksi folder root project secara otomatis
- ✅ Navigate ke folder yang benar jika Anda menjalankan dari subfolder
- ✅ Menjalankan PHP built-in server dari lokasi yang tepat
- ✅ Menampilkan URL development server

### Quick Start

#### Windows (Command Prompt)
```bash
run-dev.bat
```

#### Windows (PowerShell)
```powershell
.\run-dev.ps1
```

Atau dengan double-click `run-dev.bat` dari File Explorer.

#### macOS/Linux (Bash)
```bash
chmod +x run-dev.sh
./run-dev.sh
```

### Apa yang dilakukan script?

1. **Deteksi Lokasi**
   - Cek apakah script dijalankan dari folder yang benar
   - Jika tidak, cari ke folder parent atau dua level up
   - Folder yang benar adalah yang memiliki `package.json` dengan `"name": "prozone-1"`

2. **Navigate**
   - Ubah working directory ke folder root yang benar

3. **Jalankan Server**
   - Eksekusi `npm run web` (alias untuk `php -S localhost:8000`)
   - Server akan melayani halaman dari folder yang benar

4. **URL**
   - Buka `http://localhost:8000` di browser Anda

### Mengapa script ini penting?

Masalah yang sering terjadi:
- ❌ Menjalankan `npm run web` dari subfolder → server melayani CSS lama
- ❌ Edit file CSS di satu folder, tapi server melayani dari folder lain
- ❌ Perubahan tidak terlihat karena server melayani duplikat folder

Script ini **memastikan tidak terjadi confusion** antara folder mana yang sedang digunakan.

### Manual Alternative (Tidak Direkomendasikan)

Jika tidak ingin menggunakan script:

```bash
cd c:\laragon\www\Prozone-main\Prozone  # Sesuaikan path Anda
npm run web
```

**PENTING:** Pastikan Anda selalu `cd` ke folder root project sebelum menjalankan `npm run web`.

---

## Folder Structure Reminder

```
c:\laragon\www\Prozone-main\
├── Prozone/              ← ROOT PROJECT (gunakan ini)
│   ├── package.json
│   ├── run-dev.bat       ← Script Windows
│   ├── run-dev.ps1       ← Script PowerShell
│   ├── run-dev.sh        ← Script Bash
│   ├── assets/
│   │   └── css/
│   └── ... (file lainnya)
└── Prozone-main/         ← Duplikat (jangan gunakan, bisa dihapus)
```

---

## CSS Development Notes

### File CSS Utama (Dimuat di head.php)
```
assets/css/
├── tokens.css           # CSS variables & tokens
├── light.css            # Light theme
├── dark.css             # Dark theme
├── base.css             # Base styles
├── animations.css       # Animations
└── (halaman spesifik)
```

### Perubahan CSS Yang Sudah Ada
Jika Anda memodifikasi:
- `navbar.css` → Sudah update dengan `:root` variables
- `sidebar-island.css` → Imported dari navbar.css

### Verifikasi Perubahan CSS

1. Jalankan script: `run-dev.bat` (atau sesuai OS Anda)
2. Buka `http://localhost:8000` di browser
3. Buka DevTools (F12) → Network tab
4. Clear cache & reload halaman (Ctrl+Shift+R)
5. Cek file `navbar.css` → pastikan memiliki `:root` variables
6. Lihat halaman → perubahan CSS harus terlihat

---

## Troubleshooting

### "ERROR: Could not find Prozone root folder!"

**Penyebab:** Script tidak menemukan folder root.

**Solusi:**
1. Verifikasi Anda memiliki `package.json` di folder yang benar
2. Pastikan `package.json` memiliki `"name": "prozone-1"`
3. Jalankan script dari folder `Prozone` atau subfolder-nya

### CSS tidak berubah setelah edit

**Penyebab:** Browser cache atau server melayani dari folder yang salah.

**Solusi:**
1. Verifikasi script melayani dari folder yang benar (lihat output terminal)
2. Clear browser cache (DevTools → Network → Disable cache)
3. Hard reload halaman (Ctrl+Shift+R atau Cmd+Shift+R)

### Perubahan CSS tampil di satu halaman tapi tidak di halaman lain

**Penyebab:** Ada CSS file lain yang mengoverride perubahan Anda.

**Solusi:**
1. Periksa `$page_css` di halaman yang bermasalah
2. Lihat file CSS mana yang dimuat
3. Modifikasi file CSS yang tepat

---

## Next Steps

- ✅ Duplikat folder `Prozone/Prozone` bisa dihapus (tidak digunakan)
- ✅ Gunakan script `run-dev.*` untuk development
- ✅ Edit CSS dari folder root project saja

Selamat mengembangkan! 🚀
