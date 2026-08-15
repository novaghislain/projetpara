<?php
$file = __DIR__ . '/resources/js/app.js';
$lines = file($file);

$importsToRemove = [];
$componentsToRemove = [];

foreach ($lines as $i => $line) {
    if (preg_match("/import\s+([A-Za-z0-9_]+)\s+from\s+'\.\/(.*?)'/", $line, $matches)) {
        $varName = $matches[1];
        $path = $matches[2];
        
        $fullPath = __DIR__ . '/resources/js/' . $path;
        // Append .vue if it doesn't have an extension
        if (!str_ends_with($fullPath, '.vue') && !str_ends_with($fullPath, '.js') && !str_ends_with($fullPath, '.css')) {
             $fullPath .= '.vue'; // usually .vue or .js in this context
        }

        if (!file_exists($fullPath)) {
            echo "Missing: $path\n";
            $importsToRemove[] = trim($line);
            $componentsToRemove[] = $varName;
            $lines[$i] = "// " . $line; // comment out import
        }
    }
}

// Second pass: remove app.component calls
foreach ($lines as $i => $line) {
    if (preg_match("/app\.component\('.*?',\s*([A-Za-z0-9_]+)\)/", $line, $matches)) {
        $varName = $matches[1];
        if (in_array($varName, $componentsToRemove)) {
            $lines[$i] = "// " . $line; // comment out app.component
        }
    }
}

file_put_contents($file, implode("", $lines));
echo "Cleaned up app.js\n";
