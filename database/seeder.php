<?php
/**
 * Prozone Database Seeder
 * Run once: php database/seeder.php
 * Run with RESET=1 to truncate first: php database/seeder.php
 * Safe to re-run (uses INSERT IGNORE for courses)
 */

$reset = in_array('RESET=1', $argv ?? []);

try {
    $db = new PDO('mysql:host=localhost;dbname=prozone;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

if ($reset) {
    echo "Resetting data...\n";
    $db->exec('SET FOREIGN_KEY_CHECKS = 0');
    $db->exec('TRUNCATE lessons');
    $db->exec('TRUNCATE enrollments');
    $db->exec('TRUNCATE user_progress');
    $db->exec('DELETE FROM courses');
    $db->exec('ALTER TABLE courses AUTO_INCREMENT = 1');
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');
}

// ============================================================
// COURSES
// ============================================================
$admin_id = 1; // admin user

$courses = [
    // HTML & CSS (cat 1)
    ['kode_course' => 'HTML001', 'judul_course' => 'HTML & CSS Fundamentals', 'slug' => 'html-css-fundamentals', 'kategori_id' => 1, 'level' => 'beginner', 'deskripsi' => 'Kuasai dasar-dasar HTML5 dan CSS3 dari nol. Cocok untuk pemula yang ingin memulai karir sebagai web developer. Belajar semantic HTML, flexbox, grid, dan responsive design.', 'durasi_jam' => 24, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 500, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.8],
    ['kode_course' => 'HTML002', 'judul_course' => 'CSS Layout Mastery', 'slug' => 'css-layout-mastery', 'kategori_id' => 1, 'level' => 'intermediate', 'deskripsi' => 'Pelajari Flexbox, CSS Grid, dan teknik layout modern lainnya. Cocok untuk developer yang sudah mengenal CSS dasar dan ingin menguasai layout profesional.', 'durasi_jam' => 18, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 600, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.7],

    // JavaScript (cat 2)
    ['kode_course' => 'JS001', 'judul_course' => 'JavaScript Dasar', 'slug' => 'javascript-dasar', 'kategori_id' => 2, 'level' => 'beginner', 'deskripsi' => 'Belajar JavaScript dari fundamental hingga mahir. Materi mencakup variabel, fungsi, DOM manipulation, event handling, dan ES6+ modern JavaScript.', 'durasi_jam' => 30, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 700, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.9],
    ['kode_course' => 'JS002', 'judul_course' => 'DOM & ES6+ Modern JS', 'slug' => 'dom-es6-modern-js', 'kategori_id' => 2, 'level' => 'intermediate', 'deskripsi' => 'Kuasai DOM manipulation, async/await, promises, dan fitur ES6+ modern. Proyek nyata untuk membangun aplikasi web interaktif.', 'durasi_jam' => 22, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 650, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.7],

    // PHP (cat 3)
    ['kode_course' => 'PHP001', 'judul_course' => 'PHP Dasar', 'slug' => 'php-dasar', 'kategori_id' => 3, 'level' => 'beginner', 'deskripsi' => 'Pelajari PHP dari dasar hingga siap membuat website dinamis. Sintaks dasar, form handling, session, database MySQL, dan keamanan web.', 'durasi_jam' => 28, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 550, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.6],

    // Python (cat 4)
    ['kode_course' => 'PY001', 'judul_course' => 'Python untuk Pemula', 'slug' => 'python-untuk-pemula', 'kategori_id' => 4, 'level' => 'beginner', 'deskripsi' => 'Mulai perjalanan Python-mu dengan kurikulum interaktif. Dari print hello world hingga membuat program CLI sederhana dengan Python 3.', 'durasi_jam' => 20, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 450, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.8],

    // Database (cat 5)
    ['kode_course' => 'DB001', 'judul_course' => 'SQL & Database Design', 'slug' => 'sql-database-design', 'kategori_id' => 5, 'level' => 'intermediate', 'deskripsi' => 'Kuasai SQL dan perancangan database relasional. Belajar ERD, normalisasi, JOIN, indexing, dan query optimization untuk aplikasi skala besar.', 'durasi_jam' => 26, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 700, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.5],

    // Framework (cat 6)
    ['kode_course' => 'FW001', 'judul_course' => 'React.js Modern', 'slug' => 'reactjs-modern', 'kategori_id' => 6, 'level' => 'intermediate', 'deskripsi' => 'Bangun UI modern dengan React 18. Belajar komponen, hooks, state management, routing, dan best practices pengembangan frontend modern.', 'durasi_jam' => 35, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 800, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.9],
    ['kode_course' => 'FW002', 'judul_course' => 'Laravel 11 Fundamentals', 'slug' => 'laravel-11-fundamentals', 'kategori_id' => 6, 'level' => 'intermediate', 'deskripsi' => 'Pelajari Laravel 11 dari dasar: routing, Eloquent ORM, Blade templating, authentication, REST API, dan deployment. Proyek aplikasi task management.', 'durasi_jam' => 32, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 750, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.7],

    // Backend Development (cat 7)
    ['kode_course' => 'BE001', 'judul_course' => 'RESTful API Development', 'slug' => 'restful-api-development', 'kategori_id' => 7, 'level' => 'advanced', 'deskripsi' => 'Bangun REST API production-ready dengan PHP dan Slim Framework. Pelajari JWT authentication, rate limiting, dokumentasi Swagger, dan testing.', 'durasi_jam' => 28, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 800, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.6],

    // Python Programming (cat 8)
    ['kode_course' => 'PY101', 'judul_course' => 'Data Science with Python', 'slug' => 'data-science-python', 'kategori_id' => 8, 'level' => 'advanced', 'deskripsi' => 'Eksplorasi data science menggunakan Python: NumPy, Pandas, Matplotlib, dan Scikit-learn. Belajar data cleaning, visualisasi, dan machine learning dasar.', 'durasi_jam' => 40, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 1000, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.8],

    // Java Programming (cat 10)
    ['kode_course' => 'JAVA001', 'judul_course' => 'Java Fundamentals', 'slug' => 'java-fundamentals', 'kategori_id' => 10, 'level' => 'beginner', 'deskripsi' => 'Mulai coding dengan Java dari nol. Pelajari OOP, exception handling, collections, dan build tools. Siap untuk sertifikasi Java SE.', 'durasi_jam' => 35, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 600, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.5],

    // C++ Programming (cat 11)
    ['kode_course' => 'CPP101', 'judul_course' => 'C++ Programming Dasar', 'slug' => 'cpp-programming-dasar', 'kategori_id' => 11, 'level' => 'intermediate', 'deskripsi' => 'Kuasai C++ untuk competitive programming dan pengembangan aplikasi. Belajar pointer, STL, memory management, dan algoritma klasik.', 'durasi_jam' => 30, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 650, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.4],
];

// Insert courses
$course_ids = [];
$insert_course = $db->prepare("INSERT IGNORE INTO courses (kode_course, judul_course, slug, kategori_id, admin_id, deskripsi, level, durasi_jam, harga, is_free, is_published, xp_reward, total_lessons, total_students, rating) VALUES (:kode, :judul, :slug, :kat, :admin, :desk, :level, :durasi, :harga, :free, :pub, :xp, :tlesson, :tstudent, :rating)");

foreach ($courses as $c) {
    $insert_course->execute([
        ':kode' => $c['kode_course'],
        ':judul' => $c['judul_course'],
        ':slug' => $c['slug'],
        ':kat' => $c['kategori_id'],
        ':admin' => $admin_id,
        ':desk' => $c['deskripsi'],
        ':level' => $c['level'],
        ':durasi' => $c['durasi_jam'],
        ':harga' => $c['harga'],
        ':free' => $c['is_free'],
        ':pub' => $c['is_published'],
        ':xp' => $c['xp_reward'],
        ':tlesson' => $c['total_lessons'],
        ':tstudent' => $c['total_students'],
        ':rating' => $c['rating'],
    ]);
    $id = $db->lastInsertId();
    $course_ids[$c['kode_course']] = $id;
    echo "  Course created: {$c['judul_course']} (ID=$id)\n";
}

// ============================================================
// LESSONS
// ============================================================
// Helper: generate lesson content
function theoryContent($title, $body_paragraphs, $code_example = null) {
    $html = "";
    foreach ($body_paragraphs as $p) {
        $html .= "<p>$p</p>\n";
    }
    if ($code_example) {
        $html .= "<pre><code>" . htmlspecialchars($code_example) . "</code></pre>\n";
    }
    return $html;
}

function practiceContent($title, $instructions, $hint = null, $expected = null) {
    $html = "<p><strong>📝 Instruksi:</strong></p>\n<ul>\n";
    foreach ($instructions as $i) {
        $html .= "<li>$i</li>\n";
    }
    $html .= "</ul>\n";
    if ($hint) {
        $html .= "<blockquote><strong>💡 Hint:</strong> $hint</blockquote>\n";
    }
    if ($expected) {
        $html .= "<p><strong>Output yang diharapkan:</strong></p>\n<pre><code>" . htmlspecialchars($expected) . "</code></pre>\n";
    }
    return $html;
}

function quizContent($questions) {
    return json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

$lessons_data = [];

// ---- HTML001: HTML & CSS Fundamentals ----
$cid = $course_ids['HTML001'];
$lessons_data[] = [$cid, 1, 'Pengenalan HTML & Struktur Dasar', 'theory', 15, theoryContent(
    'Pengenalan HTML & Struktur Dasar',
    [
        'HTML (HyperText Markup Language) adalah bahasa markup standar untuk membuat halaman web. Setiap halaman web yang kamu lihat di browser dibangun menggunakan HTML.',
        'HTML menggunakan <strong>tag</strong> untuk memberi struktur pada konten. Setiap tag biasanya memiliki pasangan pembuka dan penutup, seperti <code>&lt;p&gt;...&lt;/p&gt;</code> untuk paragraf.',
        'Struktur dasar HTML terdiri dari <code>&lt;!DOCTYPE html&gt;</code>, <code>&lt;html&gt;</code>, <code>&lt;head&gt;</code>, dan <code>&lt;body&gt;</code>. Bagian head berisi metadata, sedangkan body berisi konten yang tampil.',
    ],
    '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Halaman Pertamaku</title>
</head>
<body>
    <h1>Halo, Dunia!</h1>
    <p>Ini adalah halaman HTML pertamaku.</p>
</body>
</html>'
)];

$lessons_data[] = [$cid, 2, 'Text Formatting & Semantic HTML', 'theory', 20, theoryContent(
    'Text Formatting & Semantic HTML',
    [
        'HTML menyediakan berbagai tag untuk memformat teks: <code>&lt;strong&gt;</code> untuk teks tebal (penting), <code>&lt;em&gt;</code> untuk teks miring (penekanan), <code>&lt;u&gt;</code> untuk underline, dan <code>&lt;mark&gt;</code> untuk sorotan.',
        'Semantic HTML adalah penggunaan tag yang memiliki makna, bukan sekadar gaya. Contoh: <code>&lt;header&gt;</code>, <code>&lt;nav&gt;</code>, <code>&lt;main&gt;</code>, <code>&lt;article&gt;</code>, <code>&lt;section&gt;</code>, dan <code>&lt;footer&gt;</code>.',
        'Menggunakan semantic HTML penting untuk SEO dan aksesibilitas. Screen reader dan mesin pencari dapat memahami struktur halaman dengan lebih baik.',
    ],
    '<article>
    <header>
        <h1>Belajar Semantic HTML</h1>
        <p>Dipublikasikan: <time>2025-01-15</time></p>
    </header>
    <section>
        <h2>Apa itu Semantic HTML?</h2>
        <p>Semantic HTML adalah cara menulis kode HTML yang <strong>bermakna</strong>.</p>
    </section>
    <footer>
        <p>Ditulis oleh Tim Prozone</p>
    </footer>
</article>'
)];

$lessons_data[] = [$cid, 3, 'CSS Dasar: Selektor & Property', 'theory', 20, theoryContent(
    'CSS Dasar: Selektor & Property',
    [
        'CSS (Cascading Style Sheets) digunakan untuk mempercantik tampilan HTML. Dengan CSS, kamu bisa mengubah warna, font, layout, dan animasi.',
        'Selektor CSS menentukan elemen mana yang akan di-style. Ada selektor tag (<code>p { }</code>), class (<code>.nama-class { }</code>), dan id (<code>#nama-id { }</code>).',
        'Property CSS seperti <code>color</code>, <code>background</code>, <code>font-size</code>, <code>margin</code>, dan <code>padding</code> digunakan untuk mengatur tampilan. Gunakan <code>margin</code> untuk jarak luar dan <code>padding</code> untuk jarak dalam.',
    ],
    '/* CSS Reset dasar */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Segoe UI", sans-serif;
    background: #F8FAFC;
    color: #0F172A;
}

.card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.card h2 {
    color: #4F46E5;
    font-size: 1.25rem;
    margin-bottom: 8px;
}'
)];

$lessons_data[] = [$cid, 4, 'Membangun Halaman Profil', 'practice', 25, practiceContent(
    'Membangun Halaman Profil',
    [
        'Buat file HTML baru dengan struktur dasar yang lengkap.',
        'Buat sebuah "profile card" yang berisi: foto profil (gunakan placeholder), nama, bio singkat, dan daftar skill.',
        'Gunakan semantic HTML: <code>&lt;header&gt;</code>, <code>&lt;main&gt;</code>, <code>&lt;section&gt;</code>.',
        'Beri style CSS: card dengan bayangan halus, foto profil berbentuk lingkaran, dan skill ditampilkan sebagai badge.',
    ],
    'Gunakan border-radius: 50% untuk foto profil lingkaran. Flexbox untuk menyusun layout card.',
    null
)];

$lessons_data[] = [$cid, 5, 'HTML & CSS Fundamentals', 'quiz', 10, quizContent([
    ['question' => 'Tag HTML apa yang digunakan untuk membuat paragraf?', 'options' => ['&lt;paragraph&gt;', '&lt;p&gt;', '&lt;text&gt;', '&lt;pg&gt;'], 'correct' => 1],
    ['question' => 'Atribut apa pada tag &lt;img&gt; untuk menentukan sumber gambar?', 'options' => ['href', 'src', 'link', 'source'], 'correct' => 1],
    ['question' => 'Tag HTML mana yang digunakan untuk membuat heading terbesar?', 'options' => ['&lt;heading&gt;', '&lt;h6&gt;', '&lt;h1&gt;', '&lt;head&gt;'], 'correct' => 2],
    ['question' => 'CSS selector .judul digunakan untuk memilih elemen berdasarkan apa?', 'options' => ['ID', 'Class', 'Tag', 'Atribut'], 'correct' => 1],
    ['question' => 'Properti CSS mana yang mengubah warna teks?', 'options' => ['background-color', 'text-color', 'color', 'font-color'], 'correct' => 2],
    ['question' => 'Tag HTML untuk membuat list bersarang (bertingkat) adalah?', 'options' => ['&lt;ul&gt;', '&lt;ol&gt;', '&lt;li&gt;', '&lt;dl&gt;'], 'correct' => 2],
    ['question' => 'Apa kepanjangan dari CSS?', 'options' => ['Computer Style Sheets', 'Cascading Style Sheets', 'Creative Style System', 'Colorful Style Sheets'], 'correct' => 1],
    ['question' => 'Properti CSS mana yang membuat teks menjadi tebal?', 'options' => ['font-style', 'font-weight', 'text-decoration', 'font-size'], 'correct' => 1],
    ['question' => 'Tag &lt;div&gt; di HTML termasuk jenis tag?', 'options' => ['Inline', 'Block', 'Inline-block', 'Semantic'], 'correct' => 1],
    ['question' => 'DOCTYPE html digunakan untuk apa?', 'options' => ['Mendefinisikan tipe dokumen', 'Membuat style', 'Menambah script', 'Mengatur layout'], 'correct' => 0],
])];

// ---- HTML002: CSS Layout Mastery ----
$cid = $course_ids['HTML002'];
$lessons_data[] = [$cid, 1, 'Pengenalan CSS Layout', 'theory', 15, theoryContent(
    'Pengenalan CSS Layout',
    [
        'CSS Layout adalah cara mengatur posisi dan ukuran elemen di halaman web. Teknik layout tradisional menggunakan <code>float</code> dan <code>position</code>, tapi sekarang kita punya Flexbox dan CSS Grid.',
        'Flexbox ideal untuk layout satu dimensi (baris atau kolom). CSS Grid untuk layout dua dimensi (baris dan kolom sekaligus).',
        'Pertimbangkan kebutuhanmu: untuk navbar, card row, atau centering — pakai Flexbox. Untuk halaman penuh dengan header, sidebar, main, footer — pakai Grid.',
    ],
    '.container {
    display: flex;         /* Flexbox */
    justify-content: center;
    align-items: center;
    gap: 16px;
}

.grid-page {
    display: grid;
    grid-template-columns: 250px 1fr;
    grid-template-rows: auto 1fr auto;
    gap: 0;
}'
)];

$lessons_data[] = [$cid, 2, 'Flexbox Deep Dive', 'theory', 25, theoryContent(
    'Flexbox Deep Dive',
    [
        'Flexbox memiliki properti untuk container (parent) dan item (child). Properti container: <code>display: flex</code>, <code>flex-direction</code>, <code>justify-content</code>, <code>align-items</code>, <code>flex-wrap</code>, <code>gap</code>.',
        '<code>flex-direction</code> menentukan sumbu utama: <code>row</code> (default, kiri-ke-kanan) atau <code>column</code> (atas-ke-bawah).',
        '<code>justify-content</code> mengatur posisi item di sumbu utama: <code>flex-start</code>, <code>center</code>, <code>space-between</code>, <code>space-around</code>.',
        '<code>align-items</code> mengatur posisi item di sumbu silang: <code>stretch</code> (default), <code>center</code>, <code>flex-start</code>, <code>flex-end</code>.',
    ],
    '.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 24px;
    background: #1E293B;
    color: white;
}

.nav-links {
    display: flex;
    gap: 20px;
    list-style: none;
}

.card-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.card-grid > * {
    flex: 1 1 280px; /* grow, shrink, basis */
}'
)];

$lessons_data[] = [$cid, 3, 'CSS Grid Mastery', 'theory', 25, theoryContent(
    'CSS Grid Mastery',
    [
        'CSS Grid adalah sistem layout dua dimensi paling powerful di CSS. Dengan Grid, kamu bisa membuat layout kompleks tanpa ribet.',
        'Gunakan <code>grid-template-columns</code> dan <code>grid-template-rows</code> untuk menentukan jumlah dan ukuran baris/kolom. Unit <code>fr</code> (fraction) membagi ruang tersisa secara proporsional.',
        'Fitur keren Grid: <code>grid-template-areas</code> untuk memberi nama area, <code>gap</code> untuk jarak antar cell, dan <code>auto-fit</code>/<code>auto-fill</code> untuk layout responsif otomatis.',
    ],
    '.dashboard {
    display: grid;
    grid-template-columns: 240px 1fr;
    grid-template-rows: 64px 1fr;
    grid-template-areas:
        "sidebar header"
        "sidebar main";
    min-height: 100vh;
}

.dashboard-header { grid-area: header; }
.dashboard-sidebar { grid-area: sidebar; }
.dashboard-main { grid-area: main; }

/* Responsive card grid */
.card-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}'
)];

$lessons_data[] = [$cid, 4, 'Membangun Dashboard Layout', 'practice', 30, practiceContent(
    'Membangun Dashboard Layout',
    [
        'Buat layout dashboard dengan sidebar di kiri, header di atas, dan main content area.',
        'Sidebar harus fixed-width 240px dengan daftar menu vertikal.',
        'Header berisi logo, search bar, dan avatar user.',
        'Main content area berisi grid card 3 kolom (responsif menjadi 2 lalu 1 kolom).',
        'Gunakan CSS Grid untuk outer layout dan Flexbox untuk inner components.',
    ],
    'Gunakan CSS Grid untuk layout utama dengan grid-template-areas. Untuk kartu di main content, gunakan grid dengan auto-fit + minmax().',
    null
)];

$lessons_data[] = [$cid, 5, 'CSS Layout Knowledge Check', 'quiz', 10, quizContent([
    ['question' => 'Properti CSS apa yang membuat elemen menjadi Flex container?', 'options' => ['display: block', 'display: flex', 'position: relative', 'float: left'], 'correct' => 1],
    ['question' => 'Apa fungsi dari justify-content: center pada Flexbox?', 'options' => ['Meratakan secara vertikal', 'Meratakan secara horizontal di sumbu utama', 'Memberi jarak antar item', 'Membungkus item'], 'correct' => 1],
    ['question' => 'Properti CSS Grid apa yang mendefinisikan kolom?', 'options' => ['grid-template-rows', 'grid-template-columns', 'grid-gap', 'grid-area'], 'correct' => 1],
    ['question' => 'Apa perbedaan auto-fit dan auto-fill di CSS Grid?', 'options' => ['Tidak ada perbedaan', 'auto-fit menghancurkan track kosong', 'auto-fill lebih cepat', 'auto-fit untuk kolom'], 'correct' => 1],
    ['question' => 'Nilai default dari flex-direction adalah?', 'options' => ['column', 'row', 'row-reverse', 'column-reverse'], 'correct' => 1],
    ['question' => 'Properti CSS mana yang mengatur urutan item Flexbox?', 'options' => ['flex-flow', 'order', 'flex-wrap', 'align-content'], 'correct' => 1],
    ['question' => 'Unit fr di CSS Grid berarti?', 'options' => ['free', 'fraction', 'frame', 'flexible ratio'], 'correct' => 1],
    ['question' => 'Apa fungsi dari align-items: center di Flexbox?', 'options' => ['Merata di sumbu utama', 'Merata di sumbu silang', 'Merata di kedua sumbu', 'Memberi jarak'], 'correct' => 1],
    ['question' => 'Properti grid-template-areas digunakan untuk?', 'options' => ['Mendefinisikan ukuran', 'Memberi nama area grid', 'Mengatur jarak', 'Membuat responsive'], 'correct' => 1],
    ['question' => 'CSS Grid termasuk layout?', 'options' => ['Satu dimensi', 'Dua dimensi', 'Tiga dimensi', 'Flexibel'], 'correct' => 1],
])];

// ---- JS001: JavaScript Dasar ----
$cid = $course_ids['JS001'];
$lessons_data[] = [$cid, 1, 'Variabel & Tipe Data', 'theory', 15, theoryContent(
    'Variabel & Tipe Data di JavaScript',
    [
        'JavaScript memiliki 3 cara mendeklarasikan variabel: <code>var</code> (lama), <code>let</code> (modern, bisa diubah), dan <code>const</code> (tidak bisa diubah setelah assignment).',
        'Tipe data dasar JS: <code>string</code> (teks), <code>number</code> (angka), <code>boolean</code> (true/false), <code>null</code>, <code>undefined</code>, <code>object</code>, dan <code>symbol</code>.',
        'Gunakan <code>typeof</code> untuk mengecek tipe data. JS adalah bahasa yang <em>dynamically typed</em> — kamu tidak perlu menyebutkan tipe data saat deklarasi.',
    ],
    '// Deklarasi variabel
let nama = "Budi";
const umur = 25;
var kota = "Jakarta"; // hindari penggunaan var

// Tipe data
let isActive = true;        // boolean
let total = 100;            // number
let harga = 99.999;         // number (desimal)
let pesan = "Halo Dunia";   // string
let data = null;            // null
let belumDiisi;             // undefined

console.log(typeof nama);   // "string"
console.log(typeof total);  // "number"
console.log(typeof isActive); // "boolean"'
)];

$lessons_data[] = [$cid, 2, 'Fungsi & Arrow Function', 'theory', 20, theoryContent(
    'Fungsi & Arrow Function',
    [
        'Fungsi adalah blok kode yang dapat dipanggil berulang kali. Di JS ada beberapa cara membuat fungsi: function declaration, function expression, dan arrow function (ES6).',
        'Function declaration: <code>function namaFungsi(params) { ... }</code>. Bisa dipanggil sebelum deklarasi (hoisting).',
        'Arrow function: <code>const namaFungsi = (params) => { ... }</code>. Lebih singkat, tidak memiliki <code>this</code> sendiri, dan tidak bisa di-hoist.',
    ],
    '// Function declaration
function sapa(nama) {
    return "Halo, " + nama + "!";
}
console.log(sapa("Budi")); // "Halo, Budi!"

// Function expression
const salam = function(nama) {
    return `Halo, ${nama}!`;
};

// Arrow function (ES6+)
const sapaArrow = (nama) => {
    return `Halo, ${nama}!`;
};

// Arrow function satu baris (implisit return)
const kaliDua = (x) => x * 2;

console.log(sapaArrow("Ani")); // "Halo, Ani!"
console.log(kaliDua(5));       // 10'
)];

$lessons_data[] = [$cid, 3, 'DOM Manipulation', 'theory', 25, theoryContent(
    'DOM Manipulation',
    [
        'DOM (Document Object Model) adalah representasi struktur HTML yang bisa dimanipulasi dengan JavaScript. Browser mengubah HTML menjadi objek-objek yang bisa diakses melalui <code>document</code>.',
        'Method penting: <code>document.getElementById()</code>, <code>document.querySelector()</code>, <code>document.querySelectorAll()</code>, <code>element.textContent</code>, <code>element.innerHTML</code>.',
        'Event handling: gunakan <code>element.addEventListener("click", handler)</code> untuk merespon interaksi user.',
    ],
    `// Mengakses elemen
const judul = document.getElementById("judul");
const tombol = document.querySelector(".btn-submit");
const semuaCard = document.querySelectorAll(".card");

// Mengubah konten
judul.textContent = "Judul Baru!";
judul.style.color = "#4F46E5";

// Membuat elemen baru
const div = document.createElement("div");
div.className = "alert";
div.textContent = "Berhasil!";
document.body.appendChild(div);

// Event handling
tombol.addEventListener("click", function() {
    alert("Tombol diklik!");
});`
)];

$lessons_data[] = [$cid, 4, 'Membuat Counter Interaktif', 'practice', 20, practiceContent(
    'Membuat Counter Interaktif',
    [
        'Buat halaman dengan elemen: sebuah angka (mulai dari 0), tombol "+" untuk menambah, tombol "-" untuk mengurangi, dan tombol "Reset" untuk mengembalikan ke 0.',
        'Gunakan JavaScript untuk menangani klik tombol dan memperbarui tampilan angka.',
        'Tambahkan validasi: angka tidak boleh kurang dari 0.',
        'Tampilkan pesan peringatan jika user mencoba mengurangi di angka 0.',
    ],
    'Gunakan textContent untuk mengubah tampilan angka. Simpan nilai di variabel terpisah, jangan baca dari DOM.',
    null
)];

$lessons_data[] = [$cid, 5, 'JavaScript Fundamentals Quiz', 'quiz', 10, quizContent([
    ['question' => 'Keyword untuk variabel yang tidak bisa diubah?', 'options' => ['var', 'let', 'const', 'static'], 'correct' => 2],
    ['question' => 'Tipe data untuk nilai true/false?', 'options' => ['string', 'number', 'boolean', 'object'], 'correct' => 2],
    ['question' => 'Method menambah elemen ke akhir array?', 'options' => ['push()', 'pop()', 'shift()', 'unshift()'], 'correct' => 0],
    ['question' => 'Output dari typeof 42?', 'options' => ['string', 'number', 'object', 'undefined'], 'correct' => 1],
    ['question' => 'Function declaration vs expression: mana yang di-hoist?', 'options' => ['Expression', 'Declaration', 'Keduanya', 'Tidak ada'], 'correct' => 1],
    ['question' => 'Fungsi querySelectorAll()?', 'options' => ['Memilih satu elemen', 'Memilih banyak elemen', 'Membuat elemen', 'Menghapus elemen'], 'correct' => 1],
    ['question' => 'Event listener ditambahkan dengan method?', 'options' => ['onClick()', 'addEventListener()', 'listen()', 'bindEvent()'], 'correct' => 1],
    ['question' => 'Nilai dari [] == ![]?', 'options' => ['true', 'false', 'undefined', 'Error'], 'correct' => 0],
    ['question' => 'Strict equality operator?', 'options' => ['=', '==', '===', '!='], 'correct' => 2],
    ['question' => 'Apa itu closure?', 'options' => ['Fungsi dalam fungsi', 'Object method', 'Array method', 'Loop'], 'correct' => 0],
])];

// ---- JS002: DOM & ES6+ Modern JS ----
$cid = $course_ids['JS002'];
$lessons_data[] = [$cid, 1, 'Template Literals & Destructuring', 'theory', 15, theoryContent(
    'Template Literals & Destructuring',
    [
        'Template literals menggunakan backtick (<code>`</code>) dan memungkinkan <em>string interpolation</em> dengan <code>${expression}</code>. Juga mendukung multi-line string.',
        'Destructuring assignment memudahkan ekstraksi nilai dari array atau objek ke variabel terpisah dalam satu baris.',
        'Contoh: <code>const { nama, umur } = user</code> akan membuat variabel <code>nama</code> dan <code>umur</code> dari properti objek <code>user</code>.',
    ],
    '// Template literals
const nama = "Budi";
const umur = 25;
console.log(\`Halo, nama saya \${nama} dan saya \${umur} tahun.\`);

// Multi-line string
const html = \`
<div class="card">
    <h2>\${nama}</h2>
    <p>Umur: \${umur}</p>
</div>\`;

// Object destructuring
const user = { id: 1, nama: "Ani", email: "ani@mail.com" };
const { id, nama: userName, email } = user;
console.log(userName); // "Ani"

// Array destructuring
const colors = ["merah", "kuning", "hijau"];
const [pertama, kedua, ketiga] = colors;
console.log(pertama); // "merah"

// Default value
const { kota = "Jakarta" } = user;'
)];

$lessons_data[] = [$cid, 2, 'Async/Await & Fetch API', 'theory', 25, theoryContent(
    'Async/Await & Fetch API',
    [
        'Asynchronous programming memungkinkan JS menjalankan operasi lambat (seperti request API) tanpa memblokir thread utama. Ini penting untuk UX yang responsif.',
        'Fetch API adalah cara modern untuk melakukan HTTP request dari browser. <code>fetch()</code> mengembalikan Promise.',
        'Async/await (ES2017) membuat kode asynchronous terlihat seperti synchronous. Gunakan <code>async function</code> lalu <code>await</code> di depan Promise.',
    ],
    '// Fetch API dengan async/await
async function getUserData(userId) {
    try {
        const response = await fetch(
            \`https://api.example.com/users/\${userId}\`
        );

        if (!response.ok) {
            throw new Error(\`HTTP error! status: \${response.status}\`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error("Gagal mengambil data:", error);
        throw error;
    }
}

// Promise.all untuk request paralel
async function getMultipleUsers(ids) {
    const promises = ids.map(id =>
        fetch(\`https://api.example.com/users/\${id}\`)
            .then(res => res.json())
    );
    const users = await Promise.all(promises);
    return users;
}'
)];

$lessons_data[] = [$cid, 3, 'Array Methods Modern', 'theory', 20, theoryContent(
    'Array Methods Modern',
    [
        'ES6+ memperkenalkan method-method array yang powerful: <code>map()</code>, <code>filter()</code>, <code>reduce()</code>, <code>find()</code>, <code>some()</code>, <code>every()</code>. Method ini immutable — tidak mengubah array asli.',
        '<code>map()</code> mentransformasi setiap elemen dan mengembalikan array baru. <code>filter()</code> menyaring elemen berdasarkan kondisi. <code>reduce()</code> mengakumulasi nilai dari seluruh elemen.',
        'Method chaining: gabungkan beberapa method untuk pipeline data yang bersih dan readable.',
    ],
    'const students = [
    { name: "Budi", score: 85 },
    { name: "Ani", score: 92 },
    { name: "Citra", score: 78 },
    { name: "Deni", score: 95 },
];

// map - ambil semua nama
const names = students.map(s => s.name);
// ["Budi", "Ani", "Citra", "Deni"]

// filter - yang lulus (score >= 80)
const passed = students.filter(s => s.score >= 80);
// [{name:"Budi",score:85}, {name:"Ani",score:92}, ...]

// reduce - total skor
const totalScore = students.reduce((sum, s) => sum + s.score, 0);
// 350

// chaining
const topStudents = students
    .filter(s => s.score >= 80)
    .map(s => s.name)
    .sort();
// ["Ani", "Budi", "Deni"]'
)];

$lessons_data[] = [$cid, 4, 'Fetch & Tampilkan Data API', 'practice', 30, practiceContent(
    'Fetch & Tampilkan Data API',
    [
        'Gunakan Fetch API untuk mengambil data dari JSONPlaceholder (<code>https://jsonplaceholder.typicode.com/posts</code>).',
        'Tampilkan data dalam bentuk daftar kartu (card) yang rapi.',
        'Tambahkan loading state (spinner/teks "Memuat...") saat data sedang di-fetch.',
        'Tambahkan error handling — tampilkan pesan error jika request gagal.',
        'Buat fungsi pencarian (search) yang memfilter postingan berdasarkan judul secara real-time.',
    ],
    'Gunakan async/await. Bungkus fetch dalam try/catch. Untuk loading state, setel state sebelum fetch dan setelah selesai.',
    null
)];

$lessons_data[] = [$cid, 5, 'Modern JS Comprehension', 'quiz', 10, quizContent([
    ['question' => 'Method array untuk transformasi setiap elemen?', 'options' => ['filter()', 'reduce()', 'map()', 'forEach()'], 'correct' => 2],
    ['question' => 'Template literals menggunakan tanda?', 'options' => ['Kutip satu', 'Kutip dua', 'Backtick', 'Kurung'], 'correct' => 2],
    ['question' => 'Destructuring objek menggunakan?', 'options' => ['[]', '{}', '()', '<>'], 'correct' => 1],
    ['question' => 'Async/await adalah syntax untuk?', 'options' => ['Looping', 'Asynchronous', 'Conditional', 'Array'], 'correct' => 1],
    ['question' => 'Method array untuk menyaring elemen?', 'options' => ['map()', 'filter()', 'reduce()', 'find()'], 'correct' => 1],
    ['question' => 'Fungsi Promise.all()?', 'options' => ['Menjalankan promise berurutan', 'Menjalankan promise paralel', 'Membatalkan promise', 'Mengulang promise'], 'correct' => 1],
    ['question' => 'Spread operator dilambangkan?', 'options' => ['...', '..', '**', '++'], 'correct' => 0],
    ['question' => 'Arrow function tidak memiliki?', 'options' => ['Parameter', 'Return', 'Own this', 'Body'], 'correct' => 2],
    ['question' => 'Method mengambil data dari API?', 'options' => ['get()', 'request()', 'fetch()', 'load()'], 'correct' => 2],
    ['question' => 'Optional chaining operator?', 'options' => ['?.', '??', '||', '&&'], 'correct' => 0],
])];

// ---- PHP001: PHP Dasar ----
$cid = $course_ids['PHP001'];
$lessons_data[] = [$cid, 1, 'Sintaks Dasar & Variabel', 'theory', 15, theoryContent(
    'Sintaks Dasar & Variabel PHP',
    [
        'PHP (Hypertext Preprocessor) adalah bahasa scripting server-side yang khusus dirancang untuk pengembangan web. Kode PHP dieksekusi di server dan menghasilkan HTML yang dikirim ke browser.',
        'Tag PHP diawali dengan <code>&lt;?php</code> dan diakhiri dengan <code>?&gt;</code>. Di luar tag tersebut, apapun akan dianggap sebagai HTML biasa.',
        'Variabel di PHP diawali dengan <code>$</code>. PHP bersifat <em>loosely typed</em> — kamu tidak perlu mendeklarasikan tipe data. String bisa menggunakan kutip tunggal atau ganda.',
    ],
    '<?php
// Ini adalah komentar satu baris

/* Ini komentar
   multi-baris */

// Variabel
$nama = "Budi";
$umur = 25;
$tinggi = 175.5;
$isStudent = true;

// String interpolation (kutip dua)
echo "Halo, nama saya $nama";

// Concatenation (kutip satu)
echo \'Halo, nama saya \' . $nama;

// Array
$buah = ["apel", "mangga", "jeruk"];
echo $buah[0]; // "apel"

// Associative array
$user = [
    "nama" => "Budi",
    "email" => "budi@mail.com"
];
echo $user["nama"]; // "Budi"
?>'
)];

$lessons_data[] = [$cid, 2, 'Control Flow & Loops', 'theory', 20, theoryContent(
    'Control Flow & Loops',
    [
        'PHP mendukung semua control flow standar: <code>if</code>, <code>else</code>, <code>elseif</code>, <code>switch</code>, dan ternary operator <code>? :</code>.',
        'Loop di PHP: <code>for</code>, <code>while</code>, <code>do...while</code>, dan <code>foreach</code> (khusus untuk array). <code>foreach</code> adalah yang paling sering digunakan untuk iterasi array.',
        'Gunakan <code>break</code> untuk menghentikan loop dan <code>continue</code> untuk melompat ke iterasi berikutnya.',
    ],
    '<?php
$nilai = 85;

// If-else
if ($nilai >= 90) {
    $grade = "A";
} elseif ($nilai >= 80) {
    $grade = "B";
} elseif ($nilai >= 70) {
    $grade = "C";
} else {
    $grade = "D";
}

// Switch
switch ($grade) {
    case "A":
        echo "Luar biasa!";
        break;
    case "B":
        echo "Bagus!";
        break;
    default:
        echo "Tingkatkan lagi!";
}

// Foreach loop
$products = [
    ["nama" => "Laptop", "harga" => 15000000],
    ["nama" => "Mouse", "harga" => 250000],
];

foreach ($products as $product) {
    echo $product["nama"] . ": Rp " .
         number_format($product["harga"]) . "\n";
}

// For loop
for ($i = 1; $i <= 5; $i++) {
    echo "Iterasi ke-$i\n";
}
?>'
)];

$lessons_data[] = [$cid, 3, 'Form Handling & Superglobals', 'theory', 25, theoryContent(
    'Form Handling & Superglobals',
    [
        'PHP memiliki variable superglobal yang bisa diakses dari mana saja: <code>$_GET</code>, <code>$_POST</code>, <code>$_SESSION</code>, <code>$_SERVER</code>, <code>$_COOKIE</code>, dan lainnya.',
        '<code>$_GET</code> digunakan untuk data yang dikirim via URL (query string). <code>$_POST</code> untuk data yang dikirim via form method POST (lebih aman untuk data sensitif).',
        'Session digunakan untuk menyimpan data antar halaman. Panggil <code>session_start()</code> di awal setiap halaman yang perlu mengakses session.',
    ],
    '<?php
session_start();

// Menangkap data dari form POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = htmlspecialchars($_POST["nama"] ?? "");
    $email = filter_input(INPUT_POST, "email",
             FILTER_SANITIZE_EMAIL);

    if (empty($nama)) {
        $error = "Nama wajib diisi!";
    } else {
        // Simpan ke session
        $_SESSION["user_nama"] = $nama;
        $_SESSION["login_time"] = time();
    }
}
?>

<form method="POST">
    <input type="text" name="nama"
           placeholder="Nama Anda">
    <input type="email" name="email"
           placeholder="Email">
    <button type="submit">Kirim</button>
</form>

<?php if (isset($error)): ?>
    <p style="color:red"><?= $error ?></p>
<?php endif; ?>'
)];

$lessons_data[] = [$cid, 4, 'Form Login Sederhana', 'practice', 25, practiceContent(
    'Form Login Sederhana',
    [
        'Buat halaman login dengan form: username dan password.',
        'Validasi server-side: kedua field tidak boleh kosong.',
        'Cocokkan dengan kredensial hardcoded: username = "admin", password = "rahasia".',
        'Jika login berhasil, simpan username ke session dan redirect ke halaman dashboard.',
        'Jika gagal, tampilkan pesan error tanpa menghapus input username.',
    ],
    'Gunakan htmlspecialchars() untuk mencegah XSS. Bandingkan password dengan hash (gunakan password_hash/password_verify untuk production).',
    null
)];

$lessons_data[] = [$cid, 5, 'PHP Dasar Quiz', 'quiz', 10, quizContent([
    ['question' => 'Superglobal untuk data URL query string?', 'options' => ['$_POST', '$_GET', '$_SESSION', '$_REQUEST'], 'correct' => 1],
    ['question' => 'Tag PHP diawali dengan?', 'options' => ['<?php', '<?', '<script php>', '<%'], 'correct' => 0],
    ['question' => 'Function mencetak teks di PHP?', 'options' => ['print()', 'echo', 'write()', 'output()'], 'correct' => 1],
    ['question' => 'Array asosiatif menggunakan?', 'options' => ['=>', '->', '::', '='], 'correct' => 0],
    ['question' => 'Session dimulai dengan fungsi?', 'options' => ['session_open()', 'session_start()', 'session_begin()', 'start_session()'], 'correct' => 1],
    ['question' => 'Operator concatenation PHP?', 'options' => ['+', '.', ',', '&'], 'correct' => 1],
    ['question' => 'Loop untuk iterasi array?', 'options' => ['for', 'while', 'foreach', 'do-while'], 'correct' => 2],
    ['question' => 'Function membersihkan input XSS?', 'options' => ['strip_tags()', 'htmlspecialchars()', 'trim()', 'filter_var()'], 'correct' => 1],
    ['question' => 'Superglobal data session?', 'options' => ['$_GET', '$_POST', '$_SESSION', '$_COOKIE'], 'correct' => 2],
    ['question' => 'Ekstensi file PHP?', 'options' => ['.html', '.php', '.phtml', '.js'], 'correct' => 1],
])];

// ---- PY001: Python untuk Pemula ----
$cid = $course_ids['PY001'];
$lessons_data[] = [$cid, 1, 'Pengenalan Python & Setup', 'theory', 10, theoryContent(
    'Pengenalan Python & Setup',
    [
        'Python adalah bahasa pemrograman tingkat tinggi yang dikenal dengan sintaksnya yang bersih dan mudah dibaca. Diciptakan oleh Guido van Rossum pada tahun 1991.',
        'Python menggunakan indentasi (spasi/tab) untuk mendefinisikan blok kode, bukan kurung kurawal. Konsistensi indentasi sangat penting — campuran spasi dan tab akan menyebabkan error.',
        'Python cocok untuk: web development (Django/Flask), data science, machine learning, automation scripting, dan prototyping cepat.',
    ],
    '# Ini adalah komentar di Python
print("Halo, Dunia!")  # Output: Halo, Dunia!

# Variabel - no declaration needed
nama = "Budi"
umur = 25
tinggi = 175.5
is_student = True
hobi = ["membaca", "coding", "game"]

# Type checking
print(type(nama))      # <class "str">
print(type(umur))      # <class "int">
print(type(tinggi))    # <class "float">
print(type(is_student)) # <class "bool">

# String formatting (f-string)
print(f"Halo, nama saya {nama} dan saya {umur} tahun.")'
)];

$lessons_data[] = [$cid, 2, 'List, Tuple & Dictionary', 'theory', 20, theoryContent(
    'List, Tuple & Dictionary',
    [
        'Python memiliki beberapa tipe data koleksi: <code>list</code> ([]), <code>tuple</code> (()), <code>dict</code> ({}), dan <code>set</code> ({}).',
        'List bersifat mutable (bisa diubah) dan ordered. Tuple bersifat immutable (tidak bisa diubah). Dictionary menyimpan pasangan key-value.',
        'List comprehension adalah cara singkat dan Pythonic untuk membuat list berdasarkan iterable yang sudah ada.',
    ],
    '# List
buah = ["apel", "mangga", "jeruk"]
buah.append("durian")     # Tambah item
buah.remove("apel")       # Hapus item
print(buah[0])            # "mangga"

# Tuple (immutable)
point = (10, 20)
x, y = point  # unpacking

# Dictionary
user = {
    "nama": "Budi",
    "umur": 25,
    "skills": ["Python", "JS"]
}
print(user["nama"])        # "Budi"
print(user.get("email", "tidak ada"))

# List comprehension
squares = [x**2 for x in range(10)]
# [0, 1, 4, 9, 16, 25, 36, 49, 64, 81]

even_squares = [x**2 for x in range(10) if x % 2 == 0]
# [0, 4, 16, 36, 64]'
)];

$lessons_data[] = [$cid, 3, 'Fungsi & Exception Handling', 'theory', 15, theoryContent(
    'Fungsi & Exception Handling',
    [
        'Fungsi di Python didefinisikan dengan kata kunci <code>def</code>. Bisa menerima parameter, mengembalikan nilai dengan <code>return</code>, dan memiliki <em>docstring</em> untuk dokumentasi.',
        'Python menggunakan try/except untuk menangani error (exception handling). Selalu tangkap exception spesifik, bukan semua exception.',
        'Gunakan <code>finally</code> untuk kode yang harus tetap dijalankan (seperti menutup file), baik terjadi error maupun tidak.',
    ],
    '# Fungsi dengan docstring
def hitung_bmi(berat, tinggi):
    """Hitung Body Mass Index.

    Args:
        berat: Berat badan dalam kg
        tinggi: Tinggi badan dalam meter

    Returns:
        float: Nilai BMI
    """
    return berat / (tinggi ** 2)

# Fungsi dengan default parameter
def sapa(nama, salam="Halo"):
    return f"{salam}, {nama}!"

# Exception handling
def bagi(a, b):
    try:
        hasil = a / b
    except ZeroDivisionError:
        return "Error: Tidak bisa membagi dengan nol!"
    except TypeError:
        return "Error: Masukkan angka!"
    else:
        return f"Hasil: {hasil}"
    finally:
        print("Operasi selesai.")

print(bagi(10, 2))  # Hasil: 5.0
print(bagi(10, 0))  # Error: Tidak bisa membagi dengan nol!'
)];

$lessons_data[] = [$cid, 4, 'Program Konversi Suhu', 'practice', 20, practiceContent(
    'Program Konversi Suhu',
    [
        'Buat program Python yang mengkonversi suhu dari Celcius ke Fahrenheit dan Reamur.',
        'Program meminta input suhu dalam Celcius dari user.',
        'Tampilkan hasil konversi ke Fahrenheit dan Reamur dengan 2 angka desimal.',
        'Tambahkan error handling: jika user memasukkan input yang bukan angka, tampilkan pesan error dan minta input lagi.',
    ],
    'Rumus: F = (C * 9/5) + 32, R = C * 4/5. Gunakan float() untuk konversi string ke angka, bungkus dalam try/except ValueError.',
    null
)];

$lessons_data[] = [$cid, 5, 'Python Basics Quiz', 'quiz', 10, quizContent([
    ['question' => 'Method menambah elemen ke akhir list Python?', 'options' => ['add()', 'push()', 'append()', 'insert()'], 'correct' => 2],
    ['question' => 'Tipe data tuple bersifat?', 'options' => ['Mutable', 'Immutable', 'Dinamis', 'Statis'], 'correct' => 1],
    ['question' => 'Komentar di Python diawali?', 'options' => ['//', '#', '/*', '--'], 'correct' => 1],
    ['question' => 'Fungsi mencetak output?', 'options' => ['print()', 'echo()', 'console.log()', 'output()'], 'correct' => 0],
    ['question' => 'List comprehension digunakan untuk?', 'options' => ['Membuat list singkat', 'Menghapus list', 'Mengurutkan list', 'Menggabungkan list'], 'correct' => 0],
    ['question' => 'Except block menangani?', 'options' => ['Error', 'Warning', 'Info', 'Debug'], 'correct' => 0],
    ['question' => 'Dictionary menggunakan?', 'options' => ['[]', '{}', '()', '<>'], 'correct' => 1],
    ['question' => 'Fungsi cek tipe data?', 'options' => ['type()', 'typeof()', 'gettype()', 'check()'], 'correct' => 0],
    ['question' => 'F-string diawali huruf?', 'options' => ['f', 's', 't', 'r'], 'correct' => 0],
    ['question' => 'Method menghapus elemen list?', 'options' => ['delete()', 'remove()', 'pop()', 'del()'], 'correct' => 2],
])];

// ---- DB001: SQL & Database Design ----
$cid = $course_ids['DB001'];
$lessons_data[] = [$cid, 1, 'Pengenalan Database Relasional', 'theory', 15, theoryContent(
    'Pengenalan Database Relasional',
    [
        'Database relasional menyimpan data dalam bentuk tabel yang saling berhubungan. Setiap tabel memiliki baris (record) dan kolom (field).',
        'Primary Key (PK) adalah kolom unik yang mengidentifikasi setiap baris. Foreign Key (FK) menghubungkan tabel satu dengan yang lain.',
        'SQL (Structured Query Language) adalah bahasa standar untuk mengelola database relasional. 4 operasi dasar: SELECT, INSERT, UPDATE, DELETE (CRUD).',
    ],
    '-- Membuat tabel
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(200) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    konten TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);

-- INSERT
INSERT INTO users (nama, email)
VALUES ("Budi", "budi@mail.com");

-- SELECT
SELECT * FROM users;
SELECT nama, email FROM users WHERE id = 1;'
)];

$lessons_data[] = [$cid, 2, 'SELECT Queries & Filtering', 'theory', 20, theoryContent(
    'SELECT Queries & Filtering',
    [
        'SELECT adalah query paling sering digunakan. Bisa memilih kolom spesifik, menggunakan WHERE untuk filtering, ORDER BY untuk sorting, dan LIMIT untuk membatasi hasil.',
        'Operator di WHERE: <code>=</code>, <code>!=</code>, <code>&gt;</code>, <code>&lt;</code>, <code>LIKE</code> (pencocokan pola), <code>IN</code>, <code>BETWEEN</code>, <code>IS NULL</code>.',
        'Gunakan <code>LIKE</code> dengan wildcard: <code>%</code> untuk banyak karakter, <code>_</code> untuk satu karakter.',
    ],
    '-- Filtering dasar
SELECT nama, email FROM users
WHERE created_at >= "2025-01-01"
ORDER BY nama ASC
LIMIT 10;

-- LIKE
SELECT * FROM products
WHERE nama LIKE "%laptop%";

-- BETWEEN
SELECT * FROM orders
WHERE total BETWEEN 10000 AND 50000;

-- IN
SELECT * FROM students
WHERE status IN ("active", "graduated");

-- Aggregate functions
SELECT
    COUNT(*) AS total_users,
    AVG(age) AS avg_age,
    MAX(age) AS oldest,
    MIN(age) AS youngest
FROM users;'
)];

$lessons_data[] = [$cid, 3, 'JOIN & Relasi Tabel', 'theory', 25, theoryContent(
    'JOIN & Relasi Tabel',
    [
        'JOIN digunakan untuk menggabungkan data dari dua atau lebih tabel berdasarkan kolom yang berhubungan.',
        'Jenis JOIN: <code>INNER JOIN</code> (hanya data yang cocok), <code>LEFT JOIN</code> (semua data dari tabel kiri), <code>RIGHT JOIN</code> (semua data dari tabel kanan).',
        'Normalisasi database adalah proses memecah data menjadi tabel-tabel kecil untuk mengurangi redundansi. 3 bentuk normal (1NF, 2NF, 3NF) yang umum digunakan.',
    ],
    '-- INNER JOIN
SELECT orders.id, users.nama, orders.total
FROM orders
INNER JOIN users ON orders.user_id = users.id;

-- LEFT JOIN (semua user, meski tanpa order)
SELECT users.nama, COUNT(orders.id) AS total_order
FROM users
LEFT JOIN orders ON users.id = orders.user_id
GROUP BY users.id, users.nama
ORDER BY total_order DESC;

-- JOIN 3 tabel
SELECT
    e.nama AS employee,
    d.nama_departemen,
    p.nama_proyek
FROM employees e
JOIN departemen d ON e.dept_id = d.id
JOIN proyek_assignments pa ON e.id = pa.employee_id
JOIN proyek p ON pa.proyek_id = p.id;'
)];

$lessons_data[] = [$cid, 4, 'Buat Database Rencana Studi', 'practice', 30, practiceContent(
    'Buat Database Rencana Studi',
    [
        'Rancang database untuk sistem rencana studi mahasiswa dengan tabel: mahasiswa, mata_kuliah, dan rencana_studi.',
        'Tabel mahasiswa: id, nama, nim (unique), jurusan, angkatan.',
        'Tabel mata_kuliah: id, kode_mk (unique), nama_mk, sks, semester.',
        'Tabel rencana_studi: id, mahasiswa_id (FK), mk_id (FK), status (enum: "diambil"/"lulus"/"batal").',
        'Buat query untuk menampilkan rencana studi seorang mahasiswa beserta total SKS yang diambil.',
    ],
    'Gunakan INT AUTO_INCREMENT PRIMARY KEY untuk setiap tabel. Gunakan UNIQUE constraint untuk nim dan kode_mk. Foreign Key dengan ON DELETE CASCADE.',
    null
)];

$lessons_data[] = [$cid, 5, 'SQL Fundamentals Quiz', 'quiz', 10, quizContent([
    ['question' => 'Semua baris tabel kiri + cocok tabel kanan?', 'options' => ['INNER JOIN', 'RIGHT JOIN', 'LEFT JOIN', 'FULL JOIN'], 'correct' => 2],
    ['question' => 'Perintah SQL mengambil data?', 'options' => ['GET', 'SELECT', 'FETCH', 'QUERY'], 'correct' => 1],
    ['question' => 'Constraint nilai unik?', 'options' => ['PRIMARY KEY', 'UNIQUE', 'NOT NULL', 'FOREIGN KEY'], 'correct' => 1],
    ['question' => 'Agregasi menghitung jumlah baris?', 'options' => ['SUM()', 'COUNT()', 'AVG()', 'TOTAL()'], 'correct' => 1],
    ['question' => 'Perintah menghapus tabel?', 'options' => ['DELETE TABLE', 'DROP TABLE', 'REMOVE TABLE', 'CLEAR TABLE'], 'correct' => 1],
    ['question' => 'Klausa menyaring data?', 'options' => ['HAVING', 'WHERE', 'FILTER', 'MATCH'], 'correct' => 1],
    ['question' => 'JOIN tanpa kondisi menghasilkan?', 'options' => ['INNER JOIN', 'CROSS JOIN', 'LEFT JOIN', 'SELF JOIN'], 'correct' => 1],
    ['question' => 'Normalisasi 1NF melarang?', 'options' => ['Duplikasi', 'Nilai berulang dalam kolom', 'NULL', 'Foreign Key'], 'correct' => 1],
    ['question' => 'Function mencari rata-rata?', 'options' => ['COUNT()', 'SUM()', 'AVG()', 'MEDIAN()'], 'correct' => 2],
    ['question' => 'Index digunakan untuk?', 'options' => ['Mempercepat pencarian', 'Mengamankan data', 'Mengurangi ukuran', 'Enkripsi'], 'correct' => 0],
])];

// ---- FW001: React.js Modern ----
$cid = $course_ids['FW001'];
$lessons_data[] = [$cid, 1, 'Pengenalan React & JSX', 'theory', 15, theoryContent(
    'Pengenalan React & JSX',
    [
        'React adalah library JavaScript untuk membangun user interface (UI). Dikembangkan oleh Facebook (Meta). React menggunakan konsep komponen — unit UI yang reusable dan independent.',
        'JSX adalah extension syntax JavaScript yang memungkinkan kamu menulis HTML di dalam JS. JSX bukan HTML — ia di-compile menjadi <code>React.createElement()</code> calls.',
        'Setiap komponen React adalah fungsi JavaScript yang mengembalikan JSX. Komponen bisa menerima <code>props</code> (properties) sebagai argumen.',
    ],
    `import React from "react";
import ReactDOM from "react-dom/client";

// Functional Component
function Welcome({ name, age }) {
    return (
        <div className="welcome-card">
            <h1>Halo, {name}!</h1>
            {age >= 17 && <p>Kamu sudah dewasa</p>}
        </div>
    );
}

// Menggunakan komponen
function App() {
    return (
        <div>
            <Welcome name="Budi" age={25} />
            <Welcome name="Ani" age={16} />
        </div>
    );
}

const root = ReactDOM.createRoot(
    document.getElementById("root")
);
root.render(<App />);`
)];

$lessons_data[] = [$cid, 2, 'State & Hooks (useState)', 'theory', 20, theoryContent(
    'State & Hooks (useState)',
    [
        'State adalah data yang dikelola di dalam komponen. Ketika state berubah, komponen akan <em>re-render</em> secara otomatis untuk menampilkan data terbaru.',
        'useState adalah Hook dasar React. Return-nya adalah array dengan dua elemen: nilai state saat ini dan fungsi untuk mengubahnya. Contoh: <code>const [count, setCount] = useState(0)</code>.',
        'Hooks (dimulai dengan "use") hanya bisa dipanggil di dalam functional component atau custom hooks. Jangan panggil hooks di dalam loop, kondisi, atau nested functions.',
    ],
    `import React, { useState } from "react";

function Counter() {
    const [count, setCount] = useState(0);
    const [step, setStep] = useState(1);

    return (
        <div className="counter">
            <h2>Counter: {count}</h2>

            <div className="btn-group">
                <button onClick={() =>
                    setCount(c => c - step)}>
                    Kurang
                </button>
                <button onClick={() =>
                    setCount(c => c + step)}>
                    Tambah
                </button>
                <button onClick={() => setCount(0)}>
                    Reset
                </button>
            </div>

            <label>
                Step:
                <input type="number" value={step}
                    onChange={e =>
                        setStep(Number(e.target.value))}
                    min="1" max="10" />
            </label>
        </div>
    );
}`
)];

$lessons_data[] = [$cid, 3, 'useEffect & Data Fetching', 'theory', 25, theoryContent(
    'useEffect & Data Fetching',
    [
        'useEffect adalah Hook untuk melakukan <em>side effects</em> di komponen React: fetching data, subscription, timer, atau memanipulasi DOM langsung.',
        'useEffect menerima dua argumen: callback function dan dependency array. Jika dependency array kosong <code>[]</code>, efek hanya berjalan sekali (saat mount).',
        'Return function di dalam useEffect adalah <em>cleanup</em> yang dijalankan saat komponen unmount — berguna untuk membatalkan subscription atau timer.',
    ],
    `import React, { useState, useEffect } from "react";

function UserList() {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        async function fetchUsers() {
            try {
                setLoading(true);
                const res = await fetch(
                    "https://api.example.com/users"
                );
                if (!res.ok) throw new Error("Gagal fetch");
                const data = await res.json();
                setUsers(data);
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        }

        fetchUsers();
    }, []); // dependency kosong = sekali saat mount

    if (loading) return <div className="spinner" />;
    if (error) return <div className="error">{error}</div>;

    return (
        <div className="user-grid">
            {users.map(user => (
                <div key={user.id} className="user-card">
                    <h3>{user.name}</h3>
                    <p>{user.email}</p>
                </div>
            ))}
        </div>
    );
}`
)];

$lessons_data[] = [$cid, 4, 'Buat Aplikasi Todo List', 'practice', 35, practiceContent(
    'Buat Aplikasi Todo List dengan React',
    [
        'Buat komponen TodoApp dengan state: daftar todo (array of objects dengan id, text, completed).',
        'Fitur: tambah todo baru (form input + button), tandai selesai (checkbox), hapus todo (button delete).',
        'Tampilkan jumlah todo yang belum selesai. Kelompokkan todo menjadi "Aktif" dan "Selesai".',
        'Gunakan localStorage untuk menyimpan data agar tidak hilang saat refresh (useEffect + localStorage).',
        'Tambah filter: "Semua", "Aktif", "Selesai".',
    ],
    'Gunakan useReducer jika state sudah kompleks. Untuk localStorage: simpan di useEffect dengan dependency [todos], baca di initial state useState(() => JSON.parse(localStorage.getItem("todos")) || []).',
    null
)];

$lessons_data[] = [$cid, 5, 'React Fundamentals Quiz', 'quiz', 10, quizContent([
    ['question' => 'Hook untuk state di functional component?', 'options' => ['useEffect', 'useState', 'useReducer', 'useContext'], 'correct' => 1],
    ['question' => 'JSX adalah extension syntax untuk?', 'options' => ['CSS', 'JavaScript', 'HTML', 'PHP'], 'correct' => 1],
    ['question' => 'Props pada React bersifat?', 'options' => ['Mutable', 'Immutable', 'Dinamis', 'Global'], 'correct' => 1],
    ['question' => 'useEffect dengan [] dependency?', 'options' => ['Setiap render', 'Sekali saat mount', 'Setiap state berubah', 'Tidak pernah'], 'correct' => 1],
    ['question' => 'Virtual DOM digunakan untuk?', 'options' => ['Menyimpan data', 'Optimasi rendering', 'Styling', 'Routing'], 'correct' => 1],
    ['question' => 'Functional component berupa?', 'options' => ['Class', 'Function', 'Object', 'Array'], 'correct' => 1],
    ['question' => 'Key prop digunakan untuk?', 'options' => ['Styling', 'Identifikasi unik elemen list', 'Event handler', 'Data binding'], 'correct' => 1],
    ['question' => 'Hooks tidak boleh dipanggil di?', 'options' => ['Component', 'Loop / kondisi', 'Custom hook', 'Function'], 'correct' => 1],
    ['question' => 'State lift up berarti?', 'options' => ['State dihapus', 'State dipindah ke parent', 'State di-duplicate', 'State di-enkripsi'], 'correct' => 1],
    ['question' => 'React developer oleh?', 'options' => ['Google', 'Meta (Facebook)', 'Microsoft', 'Twitter'], 'correct' => 1],
])];

// ---- FW002: Laravel 11 Fundamentals ----
$cid = $course_ids['FW002'];
$lessons_data[] = [$cid, 1, 'Arsitektur Laravel & MVC', 'theory', 15, theoryContent(
    'Arsitektur Laravel & MVC',
    [
        'Laravel adalah framework PHP paling populer yang mengikuti pola arsitektur MVC (Model-View-Controller). Memisahkan logika bisnis (Model), tampilan (View), dan kontrol alur (Controller).',
        'Routing di Laravel: file <code>routes/web.php</code> untuk web routes dan <code>routes/api.php</code> untuk API routes. Route bisa mengembalikan view, redirect, atau response JSON.',
        'Artisan adalah CLI tool Laravel untuk berbagai tasks: membuat file, migrasi database, caching, dan banyak lagi. Contoh: <code>php artisan make:controller UserController</code>.',
    ],
    '// routes/web.php
use App\\Http\\Controllers\\CourseController;

Route::get("/", function () {
    return view("welcome");
});

Route::get("/courses", [CourseController::class, "index"])
    ->name("courses.index");

Route::middleware(["auth"])->group(function () {
    Route::resource("/dashboard", DashboardController::class);
    Route::post("/enroll/{course}", [EnrollController::class,
        "store"])->name("enroll.store");
});

// php artisan make:controller CourseController --resource
// php artisan make:model Course -m
// php artisan migrate'
)];

$lessons_data[] = [$cid, 2, 'Eloquent ORM', 'theory', 25, theoryContent(
    'Eloquent ORM',
    [
        'Eloquent ORM adalah Active Record implementation di Laravel. Setiap tabel database memiliki Model yang sesuai untuk berinteraksi dengan tabel tersebut.',
        'Eloquent membuat query database menjadi intuitif dan ekspresif. Contoh: <code>User::where("active", true)->orderBy("name")->get()</code>.',
        'Relasi di Eloquent: <code>belongsTo()</code>, <code>hasMany()</code>, <code>belongsToMany()</code>, <code>hasOne()</code>. Eager loading dengan <code>with()</code> untuk mengatasi N+1 problem.',
    ],
    '// Model: app/Models/Course.php
class Course extends Model
{
    protected $fillable = [
        "judul_course", "slug", "deskripsi",
        "level", "harga", "is_free"
    ];

    // Relasi
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class,
            "kategori_id");
    }

    public function enrolledUsers()
    {
        return $this->belongsToMany(User::class,
            "enrollments")
            ->withPivot("progress_percent", "status")
            ->withTimestamps();
    }
}

// Query examples
$courses = Course::with("category", "lessons")
    ->where("is_published", true)
    ->where("level", "beginner")
    ->orderBy("total_students", "desc")
    ->paginate(12);'
)];

$lessons_data[] = [$cid, 3, 'Blade Templating', 'theory', 20, theoryContent(
    'Blade Templating',
    [
        'Blade adalah template engine Laravel yang powerful. File Blade menggunakan ekstensi <code>.blade.php</code> dan mendukung template inheritance, sections, components, dan directives.',
        'Blade directives: <code>@if</code>, <code>@foreach</code>, <code>@while</code>, <code>@isset</code>, <code>@empty</code>. Juga ada <code>@auth</code> dan <code>@guest</code> untuk autentikasi.',
        'Layout dengan Blade: gunakan <code>@extends("layouts.app")</code> dan <code>@section("content")</code> / <code>@yield("content")</code> untuk template inheritance.',
    ],
    '<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>@yield("title", "Prozone")</title>
    @vite(["resources/css/app.css"])
</head>
<body>
    <nav>@include("partials.navbar")</nav>

    <main class="container">
        @yield("content")
    </main>
</body>
</html>

<!-- resources/views/courses/index.blade.php -->
@extends("layouts.app")

@section("title", "Learning Hub - Prozone")

@section("content")
    <div class="courses-grid">
        @forelse($courses as $course)
            <div class="course-card">
                <h2>{{ $course->judul_course }}</h2>
                <p>{{ Str::limit($course->deskripsi, 120) }}</p>
                <span class="badge">{{ $course->level }}</span>
            </div>
        @empty
            <p class="empty">Belum ada course tersedia.</p>
        @endforelse
    </div>

    {{ $courses->links() }}
@endsection'
)];

$lessons_data[] = [$cid, 4, 'Buat CRUD Course dengan Laravel', 'practice', 35, practiceContent(
    'Buat CRUD Course dengan Laravel',
    [
        'Buat resource controller <code>CourseController</code> dengan perintah Artisan.',
        'Implementasi method: index (tampilkan semua course), create (form tambah), store (simpan), show (detail), edit (form edit), update, destroy.',
        'Gunakan Form Request Validation untuk validasi input.',
        'Buat blade views untuk setiap method dengan layout yang rapi.',
        'Tambahkan fitur upload thumbnail course dengan Storage.',
    ],
    'Gunakan --resource flag saat membuat controller. Untuk upload: validate dengan "image|mimes:jpg,png|max:2048", simpan dengan $path = $request->file("thumbnail")->store("thumbnails", "public").',
    null
)];

$lessons_data[] = [$cid, 5, 'Laravel Concepts Quiz', 'quiz', 10, quizContent([
    ['question' => 'Template engine Laravel?', 'options' => ['Twig', 'Smarty', 'Blade', 'Mustache'], 'correct' => 2],
    ['question' => 'Arsitektur Laravel menggunakan pola?', 'options' => ['MVP', 'MVC', 'MVVM', 'MVW'], 'correct' => 1],
    ['question' => 'Perintah Artisan membuat controller?', 'options' => ['new controller', 'make:controller', 'create controller', 'generate:controller'], 'correct' => 1],
    ['question' => 'Eloquent ORM menggunakan pola?', 'options' => ['Data Mapper', 'Active Record', 'Repository', 'Factory'], 'correct' => 1],
    ['question' => 'Method eager loading di Eloquent?', 'options' => ['load()', 'with()', 'join()', 'include()'], 'correct' => 1],
    ['question' => 'Blade directive untuk if?', 'options' => ['@if', '#if', '%if', '<?php if'], 'correct' => 0],
    ['question' => 'Migration digunakan untuk?', 'options' => ['Backup data', 'Version control database', 'Seeding data', 'Optimasi query'], 'correct' => 1],
    ['question' => 'Route HTTP POST didefinisikan?', 'options' => ['Route::get()', 'Route::post()', 'Route::put()', 'Route::delete()'], 'correct' => 1],
    ['question' => 'Middleware digunakan untuk?', 'options' => ['Styling', 'Filtering HTTP request', 'Database query', 'Template rendering'], 'correct' => 1],
    ['question' => 'Env file Laravel bernama?', 'options' => ['.env', '.config', 'env.php', 'settings.ini'], 'correct' => 0],
])];

// ---- BE001: RESTful API Development ----
$cid = $course_ids['BE001'];
$lessons_data[] = [$cid, 1, 'Prinsip REST API', 'theory', 15, theoryContent(
    'Prinsip REST API',
    [
        'REST (Representational State Transfer) adalah arsitektur API yang menggunakan metode HTTP untuk operasi CRUD: GET (baca), POST (buat), PUT/PATCH (ubah), DELETE (hapus).',
        'Endpoint REST yang baik: menggunakan noun (bukan verb), plural, dan hierarchical. Contoh: <code>GET /api/courses</code>, <code>GET /api/courses/1/lessons</code>.',
        'HTTP status codes yang benar: 200 (OK), 201 (Created), 400 (Bad Request), 401 (Unauthorized), 404 (Not Found), 422 (Validation Error), 500 (Server Error).',
    ],
    '// Contoh response API yang konsisten
{
    "success": true,
    "data": {
        "id": 1,
        "judul_course": "HTML & CSS Fundamentals",
        "level": "beginner",
        "lessons_count": 12
    },
    "meta": {
        "page": 1,
        "per_page": 20,
        "total": 50
    }
}

// Error response
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "email": ["Email sudah terdaftar"],
        "password": ["Minimal 8 karakter"]
    }
}'
)];

$lessons_data[] = [$cid, 2, 'JWT Authentication', 'theory', 25, theoryContent(
    'JWT Authentication',
    [
        'JWT (JSON Web Token) adalah metode autentikasi stateless yang populer untuk REST API. Token berisi payload yang di-encode dan ditandatangani secara digital.',
        'Struktur JWT: <code>header.payload.signature</code>. Header berisi algoritma, payload berisi claims (seperti user_id, exp), signature memverifikasi keaslian.',
        'Flow autentikasi: login → server buat JWT → client simpan (localStorage/HttpOnly cookie) → kirim di header <code>Authorization: Bearer &lt;token&gt;</code> → server verifikasi.',
    ],
    '// PHP JWT implementation (firebase/php-jwt)
use Firebase\\JWT\\JWT;
use Firebase\\JWT\\Key;

class AuthController
{
    private $key = "your-secret-key-change-this";

    public function login(Request $request)
    {
        $user = User::where("email",
            $request->email)->first();

        if (!$user || !password_verify(
            $request->password, $user->password))
        {
            return response()->json([
                "success" => false,
                "message" => "Kredensial tidak valid"
            ], 401);
        }

        $payload = [
            "sub" => $user->id,
            "email" => $user->email,
            "iat" => time(),
            "exp" => time() + 3600 // 1 jam
        ];

        $token = JWT::encode($payload, $this->key, "HS256");

        return response()->json([
            "success" => true,
            "token" => $token,
            "user" => $user->only(["id", "nama", "email"])
        ]);
    }
}'
)];

$lessons_data[] = [$cid, 3, 'API Documentation dengan OpenAPI', 'theory', 20, theoryContent(
    'API Documentation dengan OpenAPI',
    [
        'OpenAPI (sebelumnya Swagger) adalah standar untuk mendokumentasikan REST API. Dokumen OpenAPI bisa ditulis dalam YAML atau JSON.',
        'Dokumen OpenAPI mendefinisikan: endpoints, method, parameters, request body, response schema, dan authentication method.',
        'Tools populer: Swagger UI (visual documentation), Swagger Editor, dan code generators untuk client SDK.',
    ],
    'openapi: 3.0.0
info:
  title: Prozone API
  description: API untuk platform belajar Prozone
  version: 1.0.0

servers:
  - url: https://api.prozone.test/v1

paths:
  /courses:
    get:
      summary: Daftar semua course
      parameters:
        - name: page
          in: query
          schema:
            type: integer
            default: 1
        - name: kategori
          in: query
          schema:
            type: string
      responses:
        "200":
          description: Daftar course
          content:
            application/json:
              schema:
                type: array
                items:
                  $ref: "#/components/schemas/Course"

    post:
      summary: Buat course baru
      security:
        - bearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: "#/components/schemas/CourseInput"
      responses:
        "201":
          description: Course berhasil dibuat'
)];

$lessons_data[] = [$cid, 4, 'Buat REST API untuk Courses', 'practice', 30, practiceContent(
    'Buat REST API untuk Courses',
    [
        'Buat REST API endpoint CRUD untuk courses menggunakan Slim Framework atau PHP native.',
        'Endpoint: GET /api/courses, GET /api/courses/{id}, POST /api/courses, PUT /api/courses/{id}, DELETE /api/courses/{id}.',
        'Implementasikan pagination, sorting, dan filter untuk GET /api/courses.',
        'Tambahkan middleware autentikasi JWT untuk protected endpoints (POST, PUT, DELETE).',
        'Validasi input dengan regex dan custom validator.',
    ],
    'Gunakan json_encode() dengan JSON_PRETTY_PRINT untuk debugging. Set header Content-Type: application/json. Handle OPTIONS preflight request untuk CORS.',
    null
)];

$lessons_data[] = [$cid, 5, 'API Development Quiz', 'quiz', 10, quizContent([
    ['question' => 'HTTP method untuk update sebagian data?', 'options' => ['PUT', 'PATCH', 'POST', 'UPDATE'], 'correct' => 1],
    ['question' => 'HTTP status code sukses create?', 'options' => ['200', '201', '204', '301'], 'correct' => 1],
    ['question' => 'Autentikasi REST API populer?', 'options' => ['OAuth', 'JWT', 'Basic Auth', 'API Key'], 'correct' => 1],
    ['question' => 'REST endpoint baik menggunakan?', 'options' => ['Verb', 'Noun (plural)', 'Adjective', 'Random'], 'correct' => 1],
    ['question' => 'Method GET digunakan untuk?', 'options' => ['Membuat data', 'Membaca data', 'Update data', 'Hapus data'], 'correct' => 1],
    ['question' => 'CORS adalah mekanisme?', 'options' => ['Enkripsi', 'Keamanan cross-origin', 'Caching', 'Kompresi'], 'correct' => 1],
    ['question' => 'Rate limiting untuk?', 'options' => ['Mempercepat', 'Mencegah abuse', 'Menghemat biaya', 'SEO'], 'correct' => 1],
    ['question' => 'API versioning yang umum?', 'options' => ['v1/ di URL', 'Header', 'Parameter', 'Semua benar'], 'correct' => 3],
    ['question' => 'OpenAPI dulu dikenal sebagai?', 'options' => ['Swagger', 'RAML', 'API Blueprint', 'GraphQL'], 'correct' => 0],
    ['question' => 'Response JSON baik menyertakan?', 'options' => ['Hanya data', 'Metadata + data', 'HTML', 'XML'], 'correct' => 1],
])];

// ---- PY101: Data Science with Python ----
$cid = $course_ids['PY101'];
$lessons_data[] = [$cid, 1, 'Pengenalan Data Science & Tools', 'theory', 15, theoryContent(
    'Pengenalan Data Science & Tools',
    [
        'Data Science adalah ilmu mengekstrak insight dan pengetahuan dari data. Melibatkan statistik, machine learning, visualisasi, dan domain expertise.',
        'Ekosistem Python untuk Data Science: NumPy (komputasi numerik), Pandas (manipulasi data), Matplotlib (visualisasi), Scikit-learn (machine learning).',
        'Alur Data Science: 1) Kumpulkan data, 2) Bersihkan & transformasi, 3) Eksplorasi & visualisasi, 4) Model ML, 5) Evaluasi & deploy.',
    ],
    '# Import library
import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns

# Set style
sns.set_theme(style="whitegrid")
plt.rcParams["figure.figsize"] = (10, 6)

# Load dataset
df = pd.read_csv("data.csv")

# Eksplorasi awal
print(df.head())         # 5 baris pertama
print(df.info())         # info kolom & tipe data
print(df.describe())     # statistik deskriptif
print(df.isnull().sum()) # jumlah missing values'
)];

$lessons_data[] = [$cid, 2, 'Data Manipulation with Pandas', 'theory', 25, theoryContent(
    'Data Manipulation with Pandas',
    [
        'Pandas adalah library utama untuk manipulasi data di Python. Dua struktur data utama: Series (1D) dan DataFrame (2D, mirip tabel/spreadsheet).',
        'Operasi umum: filtering, grouping, merging, handling missing values, dan membuat kolom baru. Pandas sangat optimal untuk data tabular.',
        'Method chaining di Pandas memungkinkan pipeline data yang bersih: <code>df.dropna().groupby("kategori")["harga"].mean()</code>.',
    ],
    '# Membaca & menulis data
df = pd.read_csv("courses.csv")
df.to_excel("courses.xlsx", index=False)

# Filtering
aktif = df[df["status"] == "active"]
populer = df[df["total_students"] > 100]
range_harga = df[(df["harga"] >= 0) & (df["harga"] <= 500000)]

# Grouping & aggregasi
per_kategori = df.groupby("kategori").agg({
    "total_students": ["sum", "mean"],
    "harga": "mean",
    "judul_course": "count"
}).round(2)

# Handling missing values
df["rating"] = df["rating"].fillna(df["rating"].median())
df = df.dropna(subset=["judul_course"])

# Kolom baru
df["is_popular"] = df["total_students"] > 500
df["harga_diskon"] = df["harga"] * 0.8

# Merge dua dataframe
df_gabung = pd.merge(courses, categories,
    on="kategori_id", how="left")'
)];

$lessons_data[] = [$cid, 3, 'Visualisasi Data', 'theory', 20, theoryContent(
    'Visualisasi Data dengan Matplotlib & Seaborn',
    [
        'Visualisasi data adalah komponen kunci Data Science — membantu memahami pola, outlier, dan tren dalam data secara visual.',
        'Matplotlib adalah library visualisasi dasar. Seaborn dibangun di atas Matplotlib dengan API yang lebih mudah dan tema yang lebih baik.',
        'Jenis plot penting: bar plot (perbandingan), line plot (tren), scatter plot (korelasi), histogram (distribusi), heatmap (matriks korelasi), box plot (outlier).',
    ],
    '# Bar plot: rata-rata harga per kategori
plt.figure(figsize=(10, 6))
df.groupby("kategori")["harga"].mean().plot(
    kind="bar", color="#4F46E5")
plt.title("Rata-rata Harga per Kategori")
plt.xlabel("Kategori")
plt.ylabel("Rata-rata Harga (Rp)")
plt.xticks(rotation=45)
plt.tight_layout()
plt.show()

# Scatter plot: korelasi students vs rating
plt.figure(figsize=(8, 6))
sns.scatterplot(data=df,
    x="total_students", y="rating",
    hue="level", size="total_lessons",
    alpha=0.7, palette="viridis")
plt.title("Korelasi Jumlah Siswa vs Rating")
plt.show()

# Heatmap korelasi
plt.figure(figsize=(10, 8))
numeric_cols = df.select_dtypes(
    include=[np.number]).columns
sns.heatmap(df[numeric_cols].corr(),
    annot=True, cmap="coolwarm", fmt=".2f")
plt.title("Matriks Korelasi")
plt.show()'
)];

$lessons_data[] = [$cid, 4, 'Analisis Dataset Pembelajaran', 'practice', 40, practiceContent(
    'Analisis Dataset Pembelajaran',
    [
        'Gunakan Pandas untuk membaca dataset CSV yang berisi data pembelajaran online (buat sendiri: kolom student_id, course, kategori, durasi, skor, selesai).',
        'Lakukan data cleaning: cek dan tangani missing values, hapus duplikat, konversi tipe data.',
        'Analisis: rata-rata skor per kategori, korelasi antara durasi belajar dan skor, course terpopuler.',
        'Buat 3 visualisasi: bar plot (skor per kategori), scatter plot (durasi vs skor), dan pie chart (persentase penyelesaian).',
    ],
    'Gunakan np.random.seed(42) untuk reproducibility. Gunakan pd.DataFrame() untuk membuat dataset sendiri. Seaborn pairplot() untuk eksplorasi cepat semua korelasi.',
    null
)];

$lessons_data[] = [$cid, 5, 'Data Science Quiz', 'quiz', 10, quizContent([
    ['question' => 'Library Python untuk analisis data tabular?', 'options' => ['NumPy', 'Pandas', 'Matplotlib', 'Scikit-learn'], 'correct' => 1],
    ['question' => 'Library untuk visualisasi data?', 'options' => ['NumPy', 'Pandas', 'Matplotlib', 'Scipy'], 'correct' => 2],
    ['question' => 'DataFrame adalah struktur data?', 'options' => ['1D', '2D', '3D', '4D'], 'correct' => 1],
    ['question' => 'Method baca file CSV di Pandas?', 'options' => ['read_csv()', 'load_csv()', 'open_csv()', 'import_csv()'], 'correct' => 0],
    ['question' => 'Library ML di Python?', 'options' => ['Pandas', 'NumPy', 'Scikit-learn', 'Flask'], 'correct' => 2],
    ['question' => 'Method handle missing values?', 'options' => ['dropna()', 'removena()', 'deletena()', 'clearna()'], 'correct' => 0],
    ['question' => 'Scatter plot melihat?', 'options' => ['Distribusi', 'Korelasi', 'Frekuensi', 'Proporsi'], 'correct' => 1],
    ['question' => 'NumPy fokus pada?', 'options' => ['Visualisasi', 'Komputasi numerik', 'Machine learning', 'Web'], 'correct' => 1],
    ['question' => 'Heatmap digunakan untuk?', 'options' => ['Bar chart', 'Matriks korelasi', 'Line chart', 'Pie chart'], 'correct' => 1],
    ['question' => 'Seaborn berbasis?', 'options' => ['Pandas', 'NumPy', 'Matplotlib', 'Plotly'], 'correct' => 2],
])];

// ---- JAVA001: Java Fundamentals ----
$cid = $course_ids['JAVA001'];
$lessons_data[] = [$cid, 1, 'Pengenalan Java & OOP', 'theory', 15, theoryContent(
    'Pengenalan Java & OOP',
    [
        'Java adalah bahasa pemrograman berorientasi objek (OOP) yang "write once, run anywhere" — kode Java di-compile menjadi bytecode yang berjalan di JVM (Java Virtual Machine).',
        'Konsep dasar OOP: Encapsulation (membungkus data), Inheritance (pewarisan), Polymorphism (banyak bentuk), dan Abstraction (menyembunyikan kompleksitas).',
        'Struktur program Java: semua kode berada di dalam <code>class</code>. Method <code>main()</code> adalah entry point program.',
    ],
    'public class HelloWorld {
    public static void main(String[] args) {
        // Ini komentar satu baris

        /* Komentar multi-baris */

        String nama = "Budi";
        int umur = 25;
        double tinggi = 175.5;
        boolean isStudent = true;

        System.out.println("Halo, " + nama + "!");
        System.out.printf("Umur: %d, Tinggi: %.1f%n",
            umur, tinggi);

        // Array
        String[] buah = {"apel", "mangga", "jeruk"};
        for (String b : buah) {
            System.out.println(b);
        }
    }
}'
)];

$lessons_data[] = [$cid, 2, 'Class & Object', 'theory', 20, theoryContent(
    'Class & Object di Java',
    [
        'Class adalah blueprint untuk membuat objek. Berisi fields (atribut) dan methods (perilaku). Objek adalah instance dari class — dibuat dengan keyword <code>new</code>.',
        'Access modifiers: <code>public</code> (diakses dari mana saja), <code>private</code> (hanya dalam class yang sama), <code>protected</code> (package + subclass).',
        'Constructor adalah method spesial yang dipanggil saat objek dibuat. Bisa memiliki multiple constructor (overloading). Getter dan setter digunakan untuk mengakses private fields.',
    ],
    'public class Student {
    // Fields (private - encapsulation)
    private String name;
    private int age;
    private double gpa;

    // Constructor
    public Student(String name, int age) {
        this.name = name;
        this.age = age;
        this.gpa = 0.0;
    }

    // Constructor overloading
    public Student(String name) {
        this(name, 0); // panggil constructor lain
    }

    // Getter
    public String getName() { return name; }
    public int getAge() { return age; }
    public double getGpa() { return gpa; }

    // Setter
    public void setGpa(double gpa) {
        if (gpa >= 0.0 && gpa <= 4.0) {
            this.gpa = gpa;
        }
    }

    // Method
    public String getGrade() {
        if (gpa >= 3.5) return "A";
        else if (gpa >= 3.0) return "B";
        else if (gpa >= 2.0) return "C";
        else return "D";
    }
}

// Menggunakan class
public class Main {
    public static void main(String[] args) {
        Student s = new Student("Budi", 20);
        s.setGpa(3.7);
        System.out.println(s.getName() + ": " +
            s.getGrade()); // Budi: A
    }
}'
)];

$lessons_data[] = [$cid, 3, 'Inheritance & Polymorphism', 'theory', 25, theoryContent(
    'Inheritance & Polymorphism',
    [
        'Inheritance memungkinkan sebuah class mewarisi fields dan methods dari class lain. Gunakan keyword <code>extends</code>. Java hanya mendukung single inheritance (satu parent class).',
        'Polymorphism: method dengan nama sama bisa memiliki perilaku berbeda. <em>Method overriding</em> (subclass mengimplementasi ulang method parent) dan <em>method overloading</em> (method dengan parameter berbeda).',
        'Keyword <code>super</code> digunakan untuk mengakses anggota parent class. <code>@Override</code> annotation menandai method overriding.',
    ],
    '// Parent class
public class Animal {
    protected String name;

    public Animal(String name) {
        this.name = name;
    }

    public void makeSound() {
        System.out.println("Some sound...");
    }
}

// Child class
public class Dog extends Animal {
    public Dog(String name) {
        super(name); // panggil constructor parent
    }

    @Override
    public void makeSound() {
        System.out.println(name + ": Woof! Woof!");
    }

    public void fetch() {
        System.out.println(name + " is fetching!");
    }
}

// Polymorphism
public class Main {
    public static void main(String[] args) {
        Animal myDog = new Dog("Buddy");
        myDog.makeSound(); // Buddy: Woof! Woof!
        // myDog.fetch(); // ERROR! Animal tidak punya fetch

        // instanceof check
        if (myDog instanceof Dog) {
            ((Dog) myDog).fetch();
        }
    }
}'
)];

$lessons_data[] = [$cid, 4, 'Sistem Manajemen Perpustakaan', 'practice', 35, practiceContent(
    'Sistem Manajemen Perpustakaan',
    [
        'Buat sistem sederhana dengan class: Book, Member, dan Library.',
        'Book: judul, penulis, isbn, status (dipinjam/tersedia). Encapsulation dengan getter/setter.',
        'Member: nama, id, daftar buku yang dipinjam (max 3 buku). Method pinjamBuku() dan kembalikanBuku().',
        'Library: daftar buku dan member. Method: tambahBuku(), daftarBukuTersedia(), cariBuku().',
        'Implementasikan inheritance: buat subclass DigitalBook (tambah field fileSize, format) yang extends Book.',
    ],
    'Gunakan ArrayList untuk menyimpan koleksi. Override toString() untuk representasi objek yang rapi. Gunakan equals() untuk membandingkan objek.',
    null
)];

$lessons_data[] = [$cid, 5, 'Java OOP Quiz', 'quiz', 10, quizContent([
    ['question' => 'Keyword inheritance di Java?', 'options' => ['implements', 'inherits', 'extends', 'parent'], 'correct' => 2],
    ['question' => 'Prinsip OOP membungkus data?', 'options' => ['Inheritance', 'Polymorphism', 'Encapsulation', 'Abstraction'], 'correct' => 2],
    ['question' => 'Keyword panggil constructor parent?', 'options' => ['this', 'super', 'parent', 'base'], 'correct' => 1],
    ['question' => 'Access modifier paling ketat?', 'options' => ['public', 'protected', 'private', 'default'], 'correct' => 2],
    ['question' => 'Slogan Java?', 'options' => ['Code once, run anywhere', 'Write once, run anywhere', 'Build once, deploy anywhere', 'Create once, use anywhere'], 'correct' => 1],
    ['question' => 'Method dipanggil otomatis saat objek dibuat?', 'options' => ['Destructor', 'Constructor', 'Initializer', 'Builder'], 'correct' => 1],
    ['question' => 'Polymorphism memungkinkan?', 'options' => ['Satu method banyak bentuk', 'Banyak method satu nama', 'Method overloading only', 'Method overriding only'], 'correct' => 0],
    ['question' => 'JVM singkatan?', 'options' => ['Java Virtual Method', 'Java Virtual Machine', 'Java Variable Manager', 'Java Visual Module'], 'correct' => 1],
    ['question' => 'Interface menggunakan keyword?', 'options' => ['class', 'abstract', 'interface', 'implements'], 'correct' => 2],
    ['question' => 'Garbage collector berfungsi?', 'options' => ['Membersihkan kode', 'Mengelola memori otomatis', 'Mengcompile kode', 'Debugging'], 'correct' => 1],
])];

// ---- CPP101: C++ Programming Dasar ----
$cid = $course_ids['CPP101'];
$lessons_data[] = [$cid, 1, 'Pengenalan C++ & Sintaks Dasar', 'theory', 15, theoryContent(
    'Pengenalan C++ & Sintaks Dasar',
    [
        'C++ adalah bahasa pemrograman general-purpose yang dikembangkan oleh Bjarne Stroustrup sebagai pengembangan dari C. C++ menggabungkan pemrograman prosedural dan OOP.',
        'C++ dikenal dengan performa tinggi dan kontrol memori yang presisi. Banyak digunakan untuk: game development, sistem operasi, embedded systems, dan competitive programming.',
        'Program C++ minimal: <code>#include &lt;iostream&gt;</code> untuk input/output, <code>using namespace std</code>, <code>int main()</code> sebagai entry point.',
    ],
    '// Ini komentar satu baris

#include <iostream>
using namespace std;

int main() {
    // Deklarasi variabel
    string nama = "Budi";
    int umur = 25;
    double tinggi = 175.5;
    bool isStudent = true;

    // Output
    cout << "Halo, " << nama << "!" << endl;
    cout << "Umur: " << umur << endl;

    // Input
    int angka;
    cout << "Masukkan angka: ";
    cin >> angka;
    cout << "Angka yang dimasukkan: " << angka << endl;

    // Array
    int nilai[] = {85, 90, 78, 92};
    for (int i = 0; i < 4; i++) {
        cout << "Nilai-" << i+1 << ": " << nilai[i] << endl;
    }

    return 0;
}'
)];

$lessons_data[] = [$cid, 2, 'Pointer & Memory Management', 'theory', 25, theoryContent(
    'Pointer & Memory Management',
    [
        'Pointer adalah variabel yang menyimpan alamat memori dari variabel lain. Deklarasi: <code>int* ptr = &variabel</code>. Operator <code>&amp;</code> untuk alamat, <code>*</code> untuk dereference.',
        'Memory management: <code>new</code> untuk alokasi dinamis, <code>delete</code> untuk dealokasi. <code>new[]</code> dan <code>delete[]</code> untuk array.',
        'Memory leak terjadi ketika memory dialokasikan dengan <code>new</code> tapi tidak di-<code>delete</code>. Selalu gunakan <code>delete</code> setelah selesai menggunakan memori yang dialokasikan.',
    ],
    '#include <iostream>
using namespace std;

int main() {
    int x = 42;
    int* ptr = &x;  // pointer ke alamat x

    cout << "Nilai x: " << x << endl;       // 42
    cout << "Alamat x: " << &x << endl;
    cout << "Nilai ptr: " << ptr << endl;   // alamat x
    cout << "Dereference: " << *ptr << endl; // 42

    // Ubah nilai melalui pointer
    *ptr = 100;
    cout << "x sekarang: " << x << endl;    // 100

    // Alokasi dinamis
    int* arr = new int[5];
    for (int i = 0; i < 5; i++) {
        arr[i] = i * 10;
    }

    for (int i = 0; i < 5; i++) {
        cout << arr[i] << " ";
    }
    cout << endl;

    // Dealokasi
    delete[] arr;

    return 0;
}'
)];

$lessons_data[] = [$cid, 3, 'STL: Vector & Algorithm', 'theory', 20, theoryContent(
    'STL: Vector & Algorithm',
    [
        'STL (Standard Template Library) adalah library powerful C++ yang menyediakan container, algorithm, dan iterator. STL adalah senjata utama competitive programmer.',
        'Vector adalah dynamic array — ukurannya bisa berubah otomatis. Method penting: <code>push_back()</code>, <code>pop_back()</code>, <code>size()</code>, <code>empty()</code>, <code>at()</code>.',
        'Algorithm STL: <code>sort()</code>, <code>find()</code>, <code>binary_search()</code>, <code>reverse()</code>, <code>min_element()</code>, <code>max_element()</code>, <code>accumulate()</code>.',
    ],
    '#include <iostream>
#include <vector>
#include <algorithm>
#include <numeric> // accumulate
using namespace std;

int main() {
    // Vector
    vector<int> v = {5, 2, 8, 1, 9};

    // Menambah elemen
    v.push_back(3);
    v.push_back(7);

    // Iterasi
    cout << "Vector: ";
    for (int x : v) cout << x << " ";
    cout << endl;

    // Sort ascending
    sort(v.begin(), v.end());
    cout << "Sorted: ";
    for (int x : v) cout << x << " ";
    cout << endl;

    // Binary search (harus sorted)
    bool found = binary_search(v.begin(), v.end(), 8);
    cout << "8 ditemukan: " << (found ? "Ya" : "Tidak") << endl;

    // Reverse
    reverse(v.begin(), v.end());
    cout << "Reversed: ";
    for (int x : v) cout << x << " ";
    cout << endl;

    // Min, Max, Sum
    auto minIt = min_element(v.begin(), v.end());
    auto maxIt = max_element(v.begin(), v.end());
    int sum = accumulate(v.begin(), v.end(), 0);

    cout << "Min: " << *minIt << endl;
    cout << "Max: " << *maxIt << endl;
    cout << "Sum: " << sum << endl;

    // Hapus duplikat
    sort(v.begin(), v.end());
    auto last = unique(v.begin(), v.end());
    v.erase(last, v.end());

    return 0;
}'
)];

$lessons_data[] = [$cid, 4, 'Competitive Programming Challenge', 'practice', 30, practiceContent(
    'Competitive Programming Challenge',
    [
        'Buat program C++ untuk menyelesaikan soal berikut: Diberikan array N bilangan bulat, cari pasangan bilangan yang jumlahnya sama dengan target T.',
        'Input format: baris pertama berisi N dan T. Baris kedua berisi N bilangan bulat dipisah spasi.',
        'Output format: dua index (1-based) dari pasangan yang ditemukan, atau "Tidak ada" jika tidak ditemukan.',
        'Constraints: 1 <= N <= 100000, bilangan bulat dalam range -10^9 sampai 10^9.',
        'Gunakan pendekatan two-pointer atau hash map untuk solusi O(N).',
    ],
    'Gunakan unordered_map untuk hash map O(1). Solusi O(N^2) tidak akan lolos untuk N=100000. Cek index 1-based untuk output.',
    null
)];

$lessons_data[] = [$cid, 5, 'C++ Programming Quiz', 'quiz', 10, quizContent([
    ['question' => 'Header file untuk I/O stream di C++?', 'options' => ['<cstdio>', '<iostream>', '<fstream>', '<stream>'], 'correct' => 1],
    ['question' => 'Namespace std digunakan untuk?', 'options' => ['Standard library', 'String', 'Stream', 'System'], 'correct' => 0],
    ['question' => 'Keyword untuk pointer di C++?', 'options' => ['ref', 'ptr', '*', '&'], 'correct' => 2],
    ['question' => 'Fitur OOP diperkenalkan di C++?', 'options' => ['Struct', 'Class', 'Union', 'Enum'], 'correct' => 1],
    ['question' => 'Operator untuk alamat memori?', 'options' => ['*', '&', '->', '::'], 'correct' => 1],
    ['question' => 'Vector adalah container?', 'options' => ['Statis', 'Dinamis', 'Fixed', 'Constant'], 'correct' => 1],
    ['question' => 'Fungsi main() mengembalikan tipe?', 'options' => ['void', 'int', 'bool', 'string'], 'correct' => 1],
    ['question' => 'Reference (&) vs pointer (*): reference tidak bisa?', 'options' => ['Diubah nilainya', 'Null', 'Di-reassign', 'Diakses'], 'correct' => 1],
    ['question' => 'Polymorphism dicapai dengan?', 'options' => ['Function overloading', 'Virtual function', 'Template', 'Semua benar'], 'correct' => 3],
    ['question' => 'C++ dikembangkan oleh?', 'options' => ['Dennis Ritchie', 'Bjarne Stroustrup', 'James Gosling', 'Guido van Rossum'], 'correct' => 1],
])];

// ============================================================
// INSERT LESSONS
// ============================================================
echo "\nCreating lessons...\n";
$insert_lesson = $db->prepare("INSERT INTO lessons (course_id, urutan, judul_lesson, slug, konten, tipe, durasi_menit, is_free, xp_reward) VALUES (:cid, :urutan, :judul, :slug, :konten, :tipe, :durasi, :free, :xp)");

$lesson_count = 0;
foreach ($lessons_data as $l) {
    $judul = $l[2];
    $slug = strtolower(trim(preg_replace('/[^a-z0-9-]/', '-', $judul)));
    $slug = trim(preg_replace('/-+/', '-', $slug), '-');

    $insert_lesson->execute([
        ':cid' => $l[0],
        ':urutan' => $l[1],
        ':judul' => $judul,
        ':slug' => $slug,
        ':konten' => $l[5],
        ':tipe' => $l[3],
        ':durasi' => $l[4],
        ':free' => 1,
        ':xp' => $l[3] === 'quiz' ? 15 : ($l[3] === 'practice' ? 30 : 10),
    ]);
    $lesson_count++;
}

// Update total_lessons count
$update_counts = $db->prepare("UPDATE courses c SET c.total_lessons = (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id)");
$update_counts->execute();

echo "  $lesson_count lessons created.\n";

// ============================================================
// USER_QUEST_PROGRESS TABLE
// ============================================================
echo "\nCreating user_quest_progress table if not exists...\n";
$db->exec("CREATE TABLE IF NOT EXISTS user_quest_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    level_id INT NOT NULL DEFAULT 0,
    quest_idx INT NOT NULL DEFAULT 0,
    course_id INT NOT NULL DEFAULT 0,
    status ENUM('not_started','in_progress','completed') DEFAULT 'not_started',
    completed_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_progress (user_id, level_id, quest_idx, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "  Done.\n";

// ============================================================
// SAMPLE ENROLLMENTS
// ============================================================
echo "\nCreating sample enrollments for student1...\n";
try {
    // Check if student1 exists
    $student = $db->query("SELECT id FROM users WHERE username = 'student1'")->fetch(PDO::FETCH_ASSOC);
    if ($student) {
        $student_id = $student['id'];

        // Enroll in HTML & CSS and JavaScript Dasar
        $demo_courses = ['HTML001', 'JS001', 'PHP001', 'PY001'];
        $insert_enroll = $db->prepare("INSERT IGNORE INTO enrollments (user_id, course_id, status, progress_percent, completed_lessons) VALUES (:uid, :cid, 'enrolled', :progress, :completed)");

        foreach ($demo_courses as $kc) {
            if (isset($course_ids[$kc])) {
                $insert_enroll->execute([
                    ':uid' => $student_id,
                    ':cid' => $course_ids[$kc],
                    ':progress' => $kc === 'HTML001' ? 40.00 : 0.00,
                    ':completed' => $kc === 'HTML001' ? 2 : 0,
                ]);
                echo "  Enrolled student1 in $kc\n";
            }
        }

        // Update total_students
        $db->exec("UPDATE courses c SET c.total_students = (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id)");
    } else {
        echo "  student1 not found, skipping enrollments.\n";
    }
} catch (Exception $e) {
    echo "  Enrollment error: " . $e->getMessage() . "\n";
}

echo "\n========================================\n";
echo "  SEEDER COMPLETE!\n";
echo "  Courses: " . count($courses) . "\n";
echo "  Lessons: $lesson_count\n";
echo "========================================\n";
