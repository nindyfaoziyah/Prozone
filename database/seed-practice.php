<?php
/** Seed practice lessons with starter code, solution, and challenge data from JSON */
$db = new PDO('mysql:host=localhost;dbname=prozone', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$json = json_decode(file_get_contents(__DIR__ . '/practice-data.json'), true);
if (!$json) { die("Failed to load practice-data.json\n"); }

// Get practice lessons
$stmt = $db->query("SELECT l.id, l.course_id, c.judul_course FROM lessons l JOIN courses c ON l.course_id = c.id WHERE l.tipe = 'practice'");
$practices = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = 0;
foreach ($practices as $p) {
    $cid = (int)$p['course_id'];
    $lid = (int)$p['id'];
    $found = null;
    foreach ($json as $item) {
        if ((int)$item['course_id'] === $cid) { $found = $item; break; }
    }
    if (!$found) { echo "  Skipping course_id=$cid (no data)\n"; continue; }
    $upd = $db->prepare("UPDATE lessons SET kode_contoh = :code, kode_solusi = :sol, instruksi = :inst WHERE id = :id");
    $upd->execute([':code' => $found['starter'], ':sol' => $found['solution'], ':inst' => $found['challenge'], ':id' => $lid]);
    echo "  Updated lesson_id=$lid ({$p['judul_course']})\n";
    $count++;
}

echo "\nDone! $count practice lessons updated.\n";
