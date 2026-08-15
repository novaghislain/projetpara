<?php
$files = [
    'resources/views/gel-secretary/documents/index.blade.php',
    'resources/views/gel-secretary/documents/folder.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Fix contextmenu calls
    $content = preg_replace('/showFolderContextMenu\(event,\s*\{\{\s*(\$folder->id)\s*\}\},/', "showFolderContextMenu(event, '{{ $1 }}',", $content);
    $content = preg_replace('/showContextMenu\(event,\s*([^,]+),\s*\{\{\s*(\$sub->id)\s*\}\},/', "showContextMenu(event, $1, '{{ $2 }}',", $content);
    $content = preg_replace('/showContextMenu\(event,\s*([^,]+),\s*\{\{\s*(\$doc->id)\s*\}\},/', "showContextMenu(event, $1, '{{ $2 }}',", $content);
    
    // Fix onclick calls
    $content = preg_replace('/requestVaultAccess\(\s*\{\{\s*(\$doc->id)\s*\}\},/', "requestVaultAccess('{{ $1 }}',", $content);
    $content = preg_replace('/SecScanner\.open\(\s*\{\{\s*(\$folder->id)\s*\}\},/', "SecScanner.open('{{ $1 }}',", $content);
    $content = preg_replace('/showHistoryModal\(\s*\{\{\s*(\$doc->id)\s*\}\}\)/', "showHistoryModal('{{ $1 }}')", $content);
    $content = preg_replace('/showMetadataModal\(\s*\{\{\s*(\$doc->id)\s*\}\},/', "showMetadataModal('{{ $1 }}',", $content);
    $content = preg_replace('/showVersionModal\(\s*\{\{\s*(\$doc->id)\s*\}\}\)/', "showVersionModal('{{ $1 }}')", $content);
    
    file_put_contents($file, $content);
}
echo "OK\n";
