<?php
$files = ['student/course-viewer.php', 'student/learning-path.php', 'student/course-detail.php', 'lesson.php'];
foreach ($files as $f) {
    if (!file_exists($f)) { echo "$f not found\n"; continue; }
    $c = file_get_contents($f);
    // Find all array access patterns without ??
    preg_match_all("/\\\$(GET|POST|SESSION|REQUEST|SERVER)\[['\"]?([a-z_]+)['\"]?\]/i", $c, $m);
    foreach ($m[0] as $i => $match) {
        $pos = strpos($c, $match);
        $next = substr($c, $pos + strlen($match), 6);
        if (strpos($next, '??') === false && strpos($next, 'isset') === false) {
            echo "$f: $match at pos $pos - no ??\n";
        }
    }
}
