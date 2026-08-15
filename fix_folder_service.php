<?php
$f = 'app/Services/FolderStructureService.php';
$content = file_get_contents($f);
$content = str_replace('?int', '?string', $content);
file_put_contents($f, $content);
echo "OK\n";
