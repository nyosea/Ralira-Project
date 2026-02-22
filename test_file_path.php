<?php
/**
 * Test file path
 */

echo "Test file path resolution\n\n";

$path = './';
$filename = 'result_6_1768789664.pdf';
$file_path = $path . 'uploads/results/' . $filename;

echo "path: " . $path . "\n";
echo "filename: " . $filename . "\n";
echo "Constructed file_path: " . $file_path . "\n\n";

echo "Absolute path: " . realpath($file_path) . "\n\n";

echo "file_exists(): " . (file_exists($file_path) ? "YES" : "NO") . "\n";
echo "is_file(): " . (is_file($file_path) ? "YES" : "NO") . "\n";
echo "is_readable(): " . (is_readable($file_path) ? "YES" : "NO") . "\n";

if (file_exists($file_path)) {
    echo "filesize(): " . filesize($file_path) . " bytes\n";
}

echo "\nDirectory listing:\n";
$dir = './uploads/results';
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            echo "  - " . $f . "\n";
        }
    }
} else {
    echo "Directory not found: " . $dir . "\n";
}
?>
