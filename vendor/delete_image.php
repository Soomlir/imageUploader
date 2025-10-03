<?php
header('Content-Type: application/json');

function isImage($filePath)
{
  $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
  $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
  return in_array($extension, $allowedExtensions);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Неверный метод запроса']);
  exit;
}

if (!isset($_POST['filename']) || empty($_POST['filename'])) {
  echo json_encode(['success' => false, 'message' => 'Имя файла не указано']);
  exit;
}

$uploadDir = '../upload/';
$filename = basename($_POST['filename']);
$filePath = $uploadDir . $filename;

if (!file_exists($filePath) || !is_file($filePath)) {
  echo json_encode(['success' => false, 'message' => 'Файл не найден']);
  exit;
}

if (!isImage($filePath)) {
  echo json_encode(['success' => false, 'message' => 'Файл не является изображением']);
  exit;
}

if (unlink($filePath)) {
  echo json_encode(['success' => true, 'message' => 'Файл успешно удален']);
} else {
  echo json_encode(['success' => false, 'message' => 'Ошибка при удалении файла']);
}
