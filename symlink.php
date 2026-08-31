<?php

$targetFolder = $_SERVER['DOCUMENT_ROOT'].'/laravel/storage/app/public';
$linkFolder = $_SERVER['DOCUMENT_ROOT'].'/storage';

// Cek apakah symlink sudah ada
if (is_link($linkFolder)) {
    echo 'Symlink already exists: ' . $linkFolder;
} else {
    // Cek apakah folder atau file yang sama sudah ada
    if (file_exists($linkFolder)) {
        echo 'A file or folder already exists at: ' . $linkFolder;
    } else {
        // Buat symlink
        $result = symlink($targetFolder, $linkFolder);

        if ($result) {
            echo 'Symlink created successfully';
        } else {
            echo 'Failed to create symlink';
            error_log('Failed to create symlink from ' . $targetFolder . ' to ' . $linkFolder);
        }
    }
}

echo '<br>';
echo 'Target folder: ' . $targetFolder;
echo '<br>';
echo 'Link folder: ' . $linkFolder;
