<?php
class ImageUploader
{
  private $uploadDir = '../upload/';
  private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
  private $maxFileSize = 5 * 1024 * 1024;

  public function __construct()
  {
    if (!file_exists($this->uploadDir)) {
      mkdir($this->uploadDir, 0777, true);
    }
  }

  public function upload()
  {
    header('Content-Type: application/json');

    if (!isset($_FILES['images'])) {
      echo json_encode(['success' => false, 'message' => 'Файлы не были отправлены']);
      return;
    }

    $uploadedFiles = $_FILES['images'];
    $results = [];

    for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
      if ($uploadedFiles['error'][$i] !== UPLOAD_ERR_OK) {
        $results[] = [
          'success' => false,
          'filename' => $uploadedFiles['name'][$i],
          'message' => $this->getUploadError($uploadedFiles['error'][$i])
        ];
        continue;
      }

      $fileTmpName = $uploadedFiles['tmp_name'][$i];
      $fileName = $uploadedFiles['name'][$i];
      $fileSize = $uploadedFiles['size'][$i];
      $fileType = $uploadedFiles['type'][$i];

      if (!in_array($fileType, $this->allowedTypes)) {
        $results[] = [
          'success' => false,
          'filename' => $fileName,
          'message' => 'Недопустимый тип файла. Разрешены только JPEG, PNG, GIF и WebP.'
        ];
        continue;
      }

      if ($fileSize > $this->maxFileSize) {
        $results[] = [
          'success' => false,
          'filename' => $fileName,
          'message' => 'Файл слишком большой. Максимальный размер: 5MB.'
        ];
        continue;
      }

      $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
      $newFileName = $this->generateUniqueFileName($fileExtension);
      $destination = $this->uploadDir . $newFileName;

      if (move_uploaded_file($fileTmpName, $destination)) {
        $results[] = [
          'success' => true,
          'filename' => $newFileName,
          'original_name' => $fileName,
          'message' => 'Файл успешно загружен'
        ];
      } else {
        $results[] = [
          'success' => false,
          'filename' => $fileName,
          'message' => 'Ошибка при сохранении файла'
        ];
      }
    }

    echo json_encode(['results' => $results]);
  }

  private function getUploadError($errorCode)
  {
    switch ($errorCode) {
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        return 'Файл слишком большой';
      case UPLOAD_ERR_PARTIAL:
        return 'Файл был загружен только частично';
      case UPLOAD_ERR_NO_FILE:
        return 'Файл не был загружен';
      case UPLOAD_ERR_NO_TMP_DIR:
        return 'Отсутствует временная папка';
      case UPLOAD_ERR_CANT_WRITE:
        return 'Не удалось записать файл на диск';
      case UPLOAD_ERR_EXTENSION:
        return 'Расширение PHP остановило загрузку файла';
      default:
        return 'Неизвестная ошибка загрузки';
    }
  }

  private function generateUniqueFileName($extension)
  {
    $timestamp = time();
    $randomString = bin2hex(random_bytes(8));
    return $timestamp . '_' . $randomString . '.' . $extension;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $uploader = new ImageUploader();
  $uploader->upload();
}
