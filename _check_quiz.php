<?php
$db = new PDO('mysql:host=localhost;dbname=prozone', 'root', '');
$stmt = $db->query('SELECT konten FROM lessons WHERE id=5');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$data = json_decode($row['konten'], true);
echo 'Types: ';
foreach ($data as $q) { echo $q['type'] . ' '; }
echo "\nTotal: " . count($data) . " questions\n";
