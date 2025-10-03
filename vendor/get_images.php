<?php
header('Content-Type: application/json');

function isImage($filePath)
{
  $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
  $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
  return in_array($extension, $allowedExtensions);
}

function formatFileSize($bytes)
{
  if ($bytes >= 1048576) {
    return round($bytes / 1048576, 2) . ' MB';
  } elseif ($bytes >= 1024) {
    return round($bytes / 1024, 2) . ' KB';
  } else {
    return $bytes . ' bytes';
  }
}

$uploadDir = '../upload/';
$images = [];

if (file_exists($uploadDir) && is_dir($uploadDir)) {
  $files = scandir($uploadDir);

  foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
      $filePath = $uploadDir . $file;

      if (is_file($filePath) && isImage($filePath)) {
        $fileInfo = pathinfo($filePath);
        $fileSize = filesize($filePath);
        $uploadTime = filemtime($filePath);

        $images[] = [
          'name' => $file,
          'size' => formatFileSize($fileSize),
          'date' => date('d.m.Y H:i', $uploadTime)
        ];
      }
    }
  }
}

echo json_encode($images);
