<?php
/**
 * Seed new quiz question types for all courses.
 * Run: php database/seed-quiz-v2.php
 */
$db = new PDO('mysql:host=localhost;dbname=prozone', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function makeQuiz($courseId, $courseName, $language) {
    $questions = [];
    $lower = strtolower($courseName);

    // ─── DRAG & DROP ───
    if (strpos($lower, 'html') !== false) {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Susun struktur HTML5 yang benar secara berurutan:',
            'items' => ['<!DOCTYPE html>', '<html>', '<head>', '<title>Judul</title>', '</head>', '<body>', '<h1>Halo</h1>', '</body>', '</html>'],
            'correctOrder' => [0,1,2,3,4,5,6,7,8],
            'xp' => 15
        ];
    } elseif (strpos($lower, 'css') !== false) {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Urutkan prioritas CSS selector dari yang tertinggi ke terendah:',
            'items' => ['!important', 'Inline style', 'ID selector', 'Class selector', 'Element selector'],
            'correctOrder' => [0,1,2,3,4],
            'xp' => 15
        ];
    } elseif (strpos($lower, 'javascript') !== false || strpos($lower, 'js') !== false) {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Urutkan tahapan execution context JavaScript:',
            'items' => ['Creation Phase', 'Hoisting', 'Execution Phase', 'Scope Chain'],
            'correctOrder' => [0,1,2,3],
            'xp' => 20
        ];
    } elseif (strpos($lower, 'php') !== false) {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Urutkan struktur dasar PHP untuk menampilkan data dari MySQL:',
            'items' => ['Koneksi Database', 'Query SQL', 'fetch()', 'Tampilkan Data', 'Tutup Koneksi'],
            'correctOrder' => [0,1,2,3,4],
            'xp' => 20
        ];
    } elseif (strpos($lower, 'python') !== false || strpos($lower, 'data') !== false) {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Urutkan langkah analisis data yang benar:',
            'items' => ['Pengumpulan Data', 'Data Cleaning', 'Exploratory Analysis', 'Visualisasi', 'Kesimpulan'],
            'correctOrder' => [0,1,2,3,4],
            'xp' => 15
        ];
    } elseif (strpos($lower, 'sql') !== false || strpos($lower, 'database') !== false) {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Urutkan klausa SQL SELECT yang benar:',
            'items' => ['SELECT', 'FROM', 'WHERE', 'GROUP BY', 'HAVING', 'ORDER BY'],
            'correctOrder' => [0,1,2,3,4,5],
            'xp' => 20
        ];
    } elseif (strpos($lower, 'react') !== false) {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Urutkan siklus hidup komponen React (mounting):',
            'items' => ['constructor()', 'render()', 'componentDidMount()'],
            'correctOrder' => [0,1,2],
            'xp' => 20
        ];
    } elseif (strpos($lower, 'laravel') !== false) {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Urutkan alur request MVC Laravel:',
            'items' => ['Request', 'Route', 'Controller', 'Model', 'View', 'Response'],
            'correctOrder' => [0,1,2,3,4,5],
            'xp' => 20
        ];
    } elseif (strpos($lower, 'api') !== false || strpos($lower, 'rest') !== false) {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Urutkan tingkatan成熟 API maturity model (Richardson):',
            'items' => ['Level 0: Swamp of POX', 'Level 1: Resources', 'Level 2: HTTP Verbs', 'Level 3: Hypermedia'],
            'correctOrder' => [0,1,2,3],
            'xp' => 25
        ];
    } elseif (strpos($lower, 'java') !== false) {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Urutkan konsep OOP dari yang paling dasar:',
            'items' => ['Class & Object', 'Encapsulation', 'Inheritance', 'Polymorphism', 'Abstraction'],
            'correctOrder' => [0,1,2,3,4],
            'xp' => 20
        ];
    } elseif (strpos($lower, 'c++') !== false || strpos($lower, 'cpp') !== false) {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Urutkan proses kompilasi C++:',
            'items' => ['Preprocessing', 'Compilation', 'Assembly', 'Linking'],
            'correctOrder' => [0,1,2,3],
            'xp' => 20
        ];
    } else {
        $questions[] = [
            'type' => 'drag-drop',
            'question' => 'Susun langkah解决问题 yang benar:',
            'items' => ['Identifikasi Masalah', 'Analisis', 'Implementasi', 'Testing', 'Evaluasi'],
            'correctOrder' => [0,1,2,3,4],
            'xp' => 15
        ];
    }

    // ─── FILL IN THE BLANK ───
    $fillBlanks = [
        'html' => [
            'code' => "<form action=\"/submit\" ____=\"POST\">\n  <input type=\"text\" name=\"nama\">\n</form>",
            'blank' => '____',
            'answer' => ['method', 'method="POST"']
        ],
        'css' => [
            'code' => ".container {\n  display: ____;\n  justify-content: center;\n}",
            'blank' => '____',
            'answer' => ['flex', 'flexbox']
        ],
        'javascript' => [
            'code' => "const greet = (name) => {\n  return `Hello, ____!`;\n}",
            'blank' => '____',
            'answer' => ['${name}', '\${name}', 'name']
        ],
        'python' => [
            'code' => "def hitung_rata_rata(____):\n    total = sum(angka)\n    return total / len(angka)",
            'blank' => '____',
            'answer' => ['*angka', 'angka']
        ],
        'php' => [
            'code' => 'if (isset(____[\'submit\'])) {' . "\n" . '    $nama = $_POST[\'nama\'];' . "\n" . '}',
            'blank' => '____',
            'answer' => ['$_POST', '$_REQUEST', '$_GET']
        ],
        'sql' => [
            'code' => "SELECT nama, email FROM users ____ status = 'aktif'",
            'blank' => '____',
            'answer' => ['WHERE', 'where']
        ],
        'react' => [
            'code' => "const [count, ____] = useState(0);",
            'blank' => '____',
            'answer' => ['setCount']
        ],
        'java' => [
            'code' => "public ____ void main(String[] args) {\n    System.out.println(\"Hello\");\n}",
            'blank' => '____',
            'answer' => ['static']
        ],
        'laravel' => [
            'code' => "Route::get('/users', [UserController::class, '____']);",
            'blank' => '____',
            'answer' => ['index']
        ],
        'api' => [
            'code' => "app.get('/api/users', (req, res) => {\n  res.____({ data: users });\n});",
            'blank' => '____',
            'answer' => ['json']
        ],
        'c++' => [
            'code' => "#include <____>\nusing namespace std;",
            'blank' => '____',
            'answer' => ['iostream']
        ]
    ];
    $foundFill = null;
    foreach ($fillBlanks as $key => $fb) {
        if (strpos($lower, $key) !== false) { $foundFill = $fb; break; }
    }
    if (!$foundFill) {
        $foundFill = [
            'code' => "function ____() {\n  return 'Hello';\n}",
            'blank' => '____',
            'answer' => ['getName', 'hello', 'main']
        ];
    }
    $questions[] = array_merge(['type' => 'fill-blank', 'question' => 'Lengkapi kode berikut:' ], $foundFill, ['xp' => 20]);

    // ─── CODE ARRANGE ───
    $arranges = [
        'html' => ['blocks' => ['<ul>', '<li>Item 1</li>', '<li>Item 2</li>', '</ul>'], 'correctOrder' => [0,1,2,3]],
        'css' => ['blocks' => ['.card {', '  background: white;', '  padding: 20px;', '  border-radius: 8px;', '}'], 'correctOrder' => [0,1,2,3,4]],
        'javascript' => ['blocks' => ['const numbers = [1, 2, 3, 4];', 'const doubled = numbers.map(n => n * 2);', 'console.log(doubled);'], 'correctOrder' => [0,1,2]],
        'python' => ['blocks' => ['data = [1, 2, 3, 4, 5]', 'for item in data:', '    print(item)'], 'correctOrder' => [0,1,2]],
        'php' => ['blocks' => ['<?php', '$conn = new mysqli($host, $user, $pass);', '$result = $conn->query("SELECT * FROM users");', 'while($row = $result->fetch_assoc()) {', '  echo $row["nama"];', '}', '?>'], 'correctOrder' => [0,1,2,3,4,5,6]],
        'sql' => ['blocks' => ['SELECT', '  p.nama,', '  COUNT(t.id) as total', 'FROM', '  penulis p', 'LEFT JOIN', '  buku b ON p.id = b.penulis_id', 'GROUP BY', '  p.id'], 'correctOrder' => [0,1,2,3,4,5,6,7,8]],
        'react' => ['blocks' => ['function Welcome({ name }) {', '  return <h1>Hello {name}</h1>;', '}', '<Welcome name="Budi" />'], 'correctOrder' => [0,1,2,3]],
        'java' => ['blocks' => ['public class Main {', '  public static void main(String[] args) {', '    System.out.println("Hello");', '  }', '}'], 'correctOrder' => [0,1,2,3,4]],
        'laravel' => ['blocks' => ['Route::get("/items", function () {', '  $items = Item::all();', '  return view("items.index", compact("items"));', '});'], 'correctOrder' => [0,1,2,3]],
        'api' => ['blocks' => ['app.get("/api/hello", (req, res) => {', '  res.json({ message: "Hello World" });', '});'], 'correctOrder' => [0,1,2]],
    ];
    $foundArrange = null;
    foreach ($arranges as $key => $a) {
        if (strpos($lower, $key) !== false) { $foundArrange = $a; break; }
    }
    if (!$foundArrange) {
        $foundArrange = ['blocks' => ['function greet(name) {', '  return "Hello, " + name;', '}', 'console.log(greet("Budi"));'], 'correctOrder' => [0,1,2,3]];
    }
    $questions[] = array_merge(['type' => 'code-arrange', 'question' => 'Urutkan potongan kode berikut agar berjalan dengan benar:'], $foundArrange, ['xp' => 20]);

    // ─── ERROR DETECTION ───
    $errors = [
        'html' => ['code' => "<html>\n<head>\n  <title>Page\n</head>\n<body>\n  <h1>Hello</h1>\n</html>", 'bugs' => [['line' => 2, 'msg' => 'Tag <title> tidak ditutup', 'fixHint' => 'Tambah </title> sebelum </head>']]],
        'css' => ['code' => ".card {\n  background-colour: white;\n  padding: 20px\n  border-radius: 8px;\n}", 'bugs' => [['line' => 1, 'msg' => 'Properti "background-colour" salah, seharusnya "background-color"', 'fixHint' => 'Ganti "background-colour" dengan "background-color"'], ['line' => 2, 'msg' => 'Baris padding 20px tidak diakhiri titik koma (;)', 'fixHint' => 'Tambah titik koma setelah "20px"']]],
        'javascript' => ['code' => "const x = 10\nif (x = 5) {\n  console.log(\"x is 5\");\n}", 'bugs' => [['line' => 1, 'msg' => 'Menggunakan assignment (=) bukan comparison (===)', 'fixHint' => 'Ganti "=" dengan "===" untuk perbandingan']]],
        'python' => ['code' => "numbers = [1, 2, 3]\nfor i in range(len(numbers)):\n    print(number[i])", 'bugs' => [['line' => 2, 'msg' => 'Variabel "number" tidak didefinisikan, seharusnya "numbers"', 'fixHint' => 'Ganti "number[i]" dengan "numbers[i]"']]],
        'php' => ['code' => "<?php\necho \"Hello\";\nECHO \"World\";\n?>", 'bugs' => [['line' => 2, 'msg' => 'Fungsi "ECHO" tidak dikenal, PHP case-sensitive untuk nama fungsi', 'fixHint' => 'Ganti "ECHO" dengan "echo" lowercase']]],
        'sql' => ['code' => "SELECT *\nFROM users\nWHER email = \"test@test.com\"", 'bugs' => [['line' => 2, 'msg' => 'Klausa "WHER" salah, seharusnya "WHERE"', 'fixHint' => 'Perbaiki "WHER" menjadi "WHERE"']]],
    ];
    $foundError = null;
    foreach ($errors as $key => $e) {
        if (strpos($lower, $key) !== false) { $foundError = $e; break; }
    }
    if (!$foundError) {
        $foundError = ['code' => "function greet(name) {\n  return \"Hello \" + name;\n}\n\ngreet()", 'bugs' => [['line' => 3, 'msg' => 'Fungsi greet() dipanggil tanpa parameter name', 'fixHint' => 'Panggil greet("nama") dengan parameter']]];
    }
    $questions[] = array_merge(['type' => 'error-detect', 'question' => 'Cari dan perbaiki bug pada kode berikut:'], $foundError, ['xp' => 25]);

    // ─── PREDICT OUTPUT ───
    $predicts = [
        'html' => ['code' => '<p>Hello <b>World</b></p>', 'options' => ['Hello World (bold on World)', 'Hello World (all bold)', 'Hello <b>World</b> (as text)', 'Error'], 'correct' => 0],
        'css' => ['code' => 'div { width: 100px; padding: 20px; box-sizing: border-box; }', 'question' => 'Berapa lebar total elemen div?', 'options' => ['100px', '120px', '140px', '80px'], 'correct' => 0],
        'javascript' => ['code' => 'console.log(1 + "2" + 3);', 'options' => ['6', '"123"', '123', '15'], 'correct' => 2],
        'python' => ['code' => 'print(type([]) is list)', 'options' => ['True', 'False', 'list', 'Error'], 'correct' => 0],
        'php' => ['code' => 'echo 10 + "5 apples";', 'options' => ['15', '"15 apples"', '10 + "5 apples"', 'Error'], 'correct' => 0],
        'sql' => ['code' => 'SELECT COUNT(*) FROM users;', 'question' => 'Apa tipe data dari hasil query ini?', 'options' => ['Integer', 'String', 'Boolean', 'Array'], 'correct' => 0],
        'java' => ['code' => 'System.out.println(10 / 3);', 'options' => ['3', '3.333', '3.0', 'Error'], 'correct' => 0],
    ];
    $foundPredict = null;
    foreach ($predicts as $key => $p) {
        if (strpos($lower, $key) !== false) { $foundPredict = $p; break; }
    }
    if (!$foundPredict) {
        $foundPredict = ['code' => 'console.log(typeof "hello");', 'options' => ['string', 'object', 'undefined', 'number'], 'correct' => 0];
    }
    $questions[] = array_merge(['type' => 'predict-output', 'question' => 'Apa output dari kode berikut?'], $foundPredict, ['xp' => 15]);

    // ─── MATCH PAIR ───
    $matches = [
        'html' => ['left' => ['<h1>', '<a>', '<img>', '<ul>'], 'right' => ['Heading', 'Link', 'Gambar', 'List'], 'pairs' => [[0,0],[1,1],[2,2],[3,3]]],
        'css' => ['left' => ['display: flex', 'position: absolute', 'float: left', 'overflow: hidden'], 'right' => ['Flexbox layout', 'Posisi absolut', 'Mengapung ke kiri', 'Potong kelebihan'], 'pairs' => [[0,0],[1,1],[2,2],[3,3]]],
        'javascript' => ['left' => ['map()', 'filter()', 'reduce()', 'forEach()'], 'right' => ['Transform array', 'Filter array', 'Akumulasi nilai', 'Iterasi array'], 'pairs' => [[0,0],[1,1],[2,2],[3,3]]],
        'php' => ['left' => ['$_GET', '$_POST', '$_SESSION', '$_COOKIE'], 'right' => ['Data URL', 'Data form', 'Data session', 'Data cookie'], 'pairs' => [[0,0],[1,1],[2,2],[3,3]]],
        'python' => ['left' => ['list', 'dict', 'tuple', 'set'], 'right' => ['Mutable terurut', 'Key-value', 'Immutable', 'Unik tidak terurut'], 'pairs' => [[0,0],[1,1],[2,2],[3,3]]],
        'sql' => ['left' => ['INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'FULL JOIN'], 'right' => ['Irisan', 'Semua kiri', 'Semua kanan', 'Semua data'], 'pairs' => [[0,0],[1,1],[2,2],[3,3]]],
    ];
    $foundMatch = null;
    foreach ($matches as $key => $m) {
        if (strpos($lower, $key) !== false) { $foundMatch = $m; break; }
    }
    if (!$foundMatch) {
        $foundMatch = ['left' => ['Class', 'Object', 'Method', 'Property'], 'right' => ['Blueprint', 'Instance', 'Fungsi', 'Atribut'], 'pairs' => [[0,0],[1,1],[2,2],[3,3]]];
    }
    $questions[] = array_merge(['type' => 'match-pair', 'question' => 'Pasangkan konsep berikut dengan fungsinya yang tepat:'], $foundMatch, ['xp' => 20]);

    // ─── INTERACTIVE SCENARIO ───
    $scenarios = [
        'html' => ['scenario' => 'Kamu ingin membuat halaman web yang menampilkan gambar dengan teks alternatif jika gambar gagal dimuat. Tag HTML mana yang harus kamu gunakan?', 'options' => ['<img src="foto.jpg" alt="Foto Saya">', '<image src="foto.jpg">', '<picture src="foto.jpg" fallback="Foto Saya">', '<img href="foto.jpg">'], 'correct' => 0, 'explanation' => 'Tag <img> dengan atribut "alt" akan menampilkan teks alternatif jika gambar gagal dimuat.'],
        'css' => ['scenario' => 'Kamu ingin membuat layout 3 kolom yang rapi dengan jarak yang sama di antara kolom. CSS properti apa yang paling tepat?', 'options' => ['display: grid dengan gap', 'display: table dengan border-spacing', 'float: left dengan margin', 'position: absolute dengan left'], 'correct' => 0, 'explanation' => 'CSS Grid dengan properti "gap" adalah cara modern dan paling rapi untuk membuat layout multi-kolom.'],
        'javascript' => ['scenario' => 'Kamu ingin mengambil data dari server dan menampilkannya di halaman tanpa reload. Teknik apa yang paling tepat?', 'options' => ['Menggunakan fetch() API', 'Menggunakan <iframe>', 'Membuka tab baru', 'Menggunakan form submit'], 'correct' => 0, 'explanation' => 'fetch() API adalah cara modern untuk melakukan HTTP request asynchronous dari browser.'],
        'php' => ['scenario' => 'Kamu perlu menyimpan data login user di server agar tetap login meskipun pindah halaman. Mana solusi terbaik?', 'options' => ['$_SESSION', '$_COOKIE langsung', 'Hidden input form', 'localStorage'], 'correct' => 0, 'explanation' => 'Session di server (via $_SESSION) adalah cara teraman untuk menyimpan data login karena data tidak bisa dimanipulasi oleh client.'],
        'python' => ['scenario' => 'Kamu ingin memproses 1 juta data angka secepat mungkin. Struktur data mana yang paling efisien?', 'options' => ['List dengan for loop', 'NumPy array', 'Dictionary', 'Set'], 'correct' => 1, 'explanation' => 'NumPy array menggunakan C di backend dan jauh lebih cepat untuk operasi numerik skala besar.'],
        'sql' => ['scenario' => 'Kamu memiliki tabel orders dengan 1 juta baris dan sering query berdasarkan customer_id. Apa yang harus kamu lakukan?', 'options' => ['Buat index pada customer_id', 'Buat index pada id', 'Partisi tabel per bulan', 'Simpan di file berbeda'], 'correct' => 0, 'explanation' => 'Index pada kolom yang sering di-query akan mempercepat pencarian secara dramatis.'],
    ];
    $foundScenario = null;
    foreach ($scenarios as $key => $s) {
        if (strpos($lower, $key) !== false) { $foundScenario = $s; break; }
    }
    if (!$foundScenario) {
        $foundScenario = ['scenario' => 'Kamu perlu memilih antara dua algoritma: O(n log n) yang memakan waktu 5 detik, atau O(n²) yang memakan waktu 30 detik untuk 1000 data. Mana yang kamu pilih?', 'options' => ['O(n log n) - lebih cepat', 'O(n²) - lebih sederhana', 'Tidak masalah', 'Buat algoritma sendiri'], 'correct' => 0, 'explanation' => 'O(n log n) jauh lebih efisien untuk data dalam jumlah besar.'];
    }
    $questions[] = array_merge(['type' => 'scenario', 'question' => 'Pilih solusi terbaik untuk kasus berikut:'], $foundScenario, ['xp' => 25]);

    return json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// Update all quiz lessons
$stmt = $db->query("SELECT l.id, l.course_id, c.judul_course FROM lessons l JOIN courses c ON l.course_id = c.id WHERE l.tipe = 'quiz'");
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;
foreach ($quizzes as $q) {
    $cid = (int)$q['course_id'];
    $lid = (int)$q['id'];
    $courseName = $q['judul_course'];
    $quizData = makeQuiz($cid, $courseName, '');
    $upd = $db->prepare("UPDATE lessons SET konten = :konten, xp_reward = 25 WHERE id = :id");
    $upd->execute([':konten' => $quizData, ':id' => $lid]);
    echo "  Updated quiz lesson_id=$lid ($courseName)\n";
    $count++;
}

echo "\nDone! $count quizzes updated with new question types.\n";
