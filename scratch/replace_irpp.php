<?php
$directory = __DIR__ . '/../';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$replacements = 0;

foreach ($files as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['php', 'vue', 'blade', 'js', 'json'])) {
        $path = $file->getRealPath();
        
        // Exclure vendor et node_modules, storage, .git
        if (str_contains($path, '\\vendor\\') || str_contains($path, '\\node_modules\\') || str_contains($path, '\\storage\\') || str_contains($path, '\\.git\\')) {
            continue;
        }

        $content = file_get_contents($path);
        
        // Cas sensibles
        $newContent = str_replace(['ITS', 'its', 'Its'], ['ITS', 'its', 'Its'], $content);

        if ($content !== $newContent) {
            file_put_contents($path, $newContent);
            echo "Replaced in " . $file->getFilename() . "\n";
            $replacements++;
        }
    }
}
echo "Total files changed: $replacements\n";

// Rename ItsCalculator.php -> ItsCalculator.php
$oldFile = __DIR__ . '/../app/Services/Paie/ItsCalculator.php';
$newFile = __DIR__ . '/../app/Services/Paie/ItsCalculator.php';
if (file_exists($oldFile)) {
    rename($oldFile, $newFile);
    echo "Renamed ItsCalculator.php to ItsCalculator.php\n";
}
