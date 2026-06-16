<?php
$files = ['student/course-viewer.php', 'student/learning-path.php', 'lesson.php', 'includes/head.php'];
foreach ($files as $f) {
    if (!file_exists($f)) { echo "$f not found\n"; continue; }
    $c = file_get_contents($f);
    preg_match_all('/[Ww]arning/', $c, $m);
    if (count($m[0]) > 0) {
        echo "$f: " . count($m[0]) . " matches\n";
    }
}
echo "Done\n";
