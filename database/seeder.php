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
$admin_id = 2; // admin/instructor user

$courses = [
    ['kode_course' => 'HTML001', 'judul_course' => 'HTML & CSS Fundamentals', 'slug' => 'html-css-fundamentals', 'kategori_id' => 1, 'level' => 'beginner', 'deskripsi' => 'Pelajari dasar-dasar HTML dan CSS untuk membuat website yang menarik dan responsif. Cocok untuk pemula yang ingin menjadi Web Developer.', 'durasi_jam' => 15, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 500, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 15, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'PY001', 'judul_course' => 'Python Programming', 'slug' => 'python-programming', 'kategori_id' => 4, 'level' => 'beginner', 'deskripsi' => 'Kuasai bahasa pemrograman paling populer di dunia. Dari syntax dasar hingga Object Oriented Programming.', 'durasi_jam' => 20, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 600, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 15, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'PHP001', 'judul_course' => 'PHP Web Development', 'slug' => 'php-web-development', 'kategori_id' => 3, 'level' => 'beginner', 'deskripsi' => 'Belajar bahasa server-side paling populer untuk membuat website dinamis dan berinteraksi dengan database.', 'durasi_jam' => 20, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 600, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 15, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'JS001', 'judul_course' => 'JavaScript Programming', 'slug' => 'javascript-programming', 'kategori_id' => 2, 'level' => 'beginner', 'deskripsi' => 'Bahasa pemrograman wajib untuk Front-End Developer. Membuat website menjadi interaktif dan hidup.', 'durasi_jam' => 20, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 600, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 15, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'JAVA001', 'judul_course' => 'Java Programming Basics', 'slug' => 'java-programming-basics', 'kategori_id' => 10, 'level' => 'beginner', 'deskripsi' => 'Pelajari Java, bahasa pemrograman berorientasi objek yang kuat, aman, dan portabel.', 'durasi_jam' => 20, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 600, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 15, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'PY101', 'judul_course' => 'Python untuk Pemula', 'slug' => 'python-untuk-pemula', 'kategori_id' => 8, 'level' => 'beginner', 'deskripsi' => 'Pelajari dasar-dasar Python programming dari variabel, tipe data, hingga function dan class.', 'durasi_jam' => 10, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 200, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 8, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'PY102', 'judul_course' => 'Python Intermediate', 'slug' => 'python-intermediate', 'kategori_id' => 8, 'level' => 'intermediate', 'deskripsi' => 'Tingkatkan skill Python dengan OOP, file handling, error handling, dan module.', 'durasi_jam' => 15, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 300, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 8, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'JS101', 'judul_course' => 'JavaScript Fundamentals', 'slug' => 'javascript-fundamentals', 'kategori_id' => 2, 'level' => 'beginner', 'deskripsi' => 'Kuasai dasar JavaScript: variabel, function, array, object, dan DOM manipulation.', 'durasi_jam' => 12, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 250, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 5, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'JS102', 'judul_course' => 'JavaScript Modern (ES6+)', 'slug' => 'javascript-modern-es6', 'kategori_id' => 2, 'level' => 'intermediate', 'deskripsi' => 'Pelajari fitur modern JavaScript: arrow functions, destructuring, promises, async/await, dan modules.', 'durasi_jam' => 10, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 280, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 10, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'PHP101', 'judul_course' => 'PHP untuk Web Development', 'slug' => 'php-web-development-course', 'kategori_id' => 9, 'level' => 'beginner', 'deskripsi' => 'Belajar PHP dari dasar: syntax, variabel, array, function, dan form handling.', 'durasi_jam' => 12, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 250, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 4, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'PHP102', 'judul_course' => 'PHP & MySQL Database', 'slug' => 'php-mysql-database', 'kategori_id' => 9, 'level' => 'intermediate', 'deskripsi' => 'Kuasai koneksi database, CRUD operations, prepared statements, dan best practices keamanan.', 'durasi_jam' => 14, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 300, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 8, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'JAVA102', 'judul_course' => 'Java Object-Oriented Programming', 'slug' => 'java-oop', 'kategori_id' => 10, 'level' => 'intermediate', 'deskripsi' => 'Deep dive ke OOP: classes, inheritance, polymorphism, interfaces, dan exception handling.', 'durasi_jam' => 18, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 350, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 8, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'JAVA101', 'judul_course' => 'Java Programming Basics', 'slug' => 'java-programming-basics-advanced', 'kategori_id' => 10, 'level' => 'beginner', 'deskripsi' => 'Mulai perjalanan programming dengan Java: variabel, control flow, methods, dan introduction to OOP.', 'durasi_jam' => 15, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 280, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 10, 'total_students' => 0, 'rating' => 4.5],
    ['kode_course' => 'CPP101', 'judul_course' => 'C++ untuk Pemula', 'slug' => 'cpp-untuk-pemula', 'kategori_id' => 11, 'level' => 'beginner', 'deskripsi' => 'Pelajari dasar C++: syntax, variabel, pointers, dan memory management.', 'durasi_jam' => 14, 'is_free' => 1, 'is_published' => 1, 'xp_reward' => 260, 'harga' => 0, 'thumbnail' => null, 'total_lessons' => 10, 'total_students' => 0, 'rating' => 4.5],
];

// Insert courses
$course_ids = [];
$insert_course = $db->prepare("INSERT INTO courses (kode_course, judul_course, slug, kategori_id, admin_id, deskripsi, level, durasi_jam, harga, is_free, is_published, xp_reward, total_lessons, total_students, rating) VALUES (:kode, :judul, :slug, :kat, :admin, :desk, :level, :durasi, :harga, :free, :pub, :xp, :tlesson, :tstudent, :rating) ON DUPLICATE KEY UPDATE judul_course=VALUES(judul_course), slug=VALUES(slug), kategori_id=VALUES(kategori_id), admin_id=VALUES(admin_id), deskripsi=VALUES(deskripsi), level=VALUES(level), durasi_jam=VALUES(durasi_jam), harga=VALUES(harga), is_free=VALUES(is_free), is_published=VALUES(is_published), xp_reward=VALUES(xp_reward), total_lessons=VALUES(total_lessons), rating=VALUES(rating)");

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

// ---- HTML002: (removed) ----

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

// ---- JS002: (removed) ----

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

// ---- DB001: (removed) ----

// ---- FW001: (removed) ----

// ---- FW002: (removed) ----

// ---- BE001: (removed) ----

// ---- PY101: Python untuk Pemula ----
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
