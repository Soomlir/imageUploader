<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="css/styles.css" rel="stylesheet">
  <title>Загрузчик изображений</title>
</head>

<body>
  <div class="container">
    <header>
      <h1>Загрузчик изображений</h1>
      <p class="subtitle">Загружайте, храните и просматривайте ваши фотографии в красивом и удобном интерфейсе</p>
    </header>

    <section class="upload-section">
      <form class="upload-form" id="uploadForm" enctype="multipart/form-data" method="post" action="vendor/ImageUploader.php">
        <div class="file-input-wrapper">
          <div class="file-input">
            <span id="fileInputText">Выберите файл для загрузки</span>
            <input type="file" id="fileInput" name="images[]" accept="image/*" multiple>
          </div>
        </div>
        <button type="submit" class="upload-btn">Загрузить изображения</button>
      </form>
    </section>

    <section class="gallery-section">
      <h2 class="section-title">Ваши фотографии</h2>
      <div class="gallery" id="imageGallery">
        <div class="empty-gallery">Пока нет загруженных изображений</div>
      </div>
    </section>
  </div>

  <script>
    document.getElementById('fileInput').addEventListener('change', function(e) {
      const fileInputText = document.getElementById('fileInputText');
      if (this.files.length > 0) {
        if (this.files.length === 1) {
          fileInputText.textContent = this.files[0].name;
        } else {
          fileInputText.textContent = `Выбрано файлов: ${this.files.length}`;
        }
      } else {
        fileInputText.textContent = 'Выберите файл для загрузки';
      }
    });

    document.getElementById('uploadForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch(this.action, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.results) {
            let hasErrors = false;
            let successCount = 0;

            data.results.forEach(result => {
              if (result.success) {
                successCount++;
              } else {
                hasErrors = true;
                console.error(`Ошибка загрузки ${result.filename}: ${result.message}`);
              }
            });

            if (successCount > 0) {
              alert(`Успешно загружено ${successCount} файлов`);
              loadGallery(); //
            }

            if (hasErrors) {
              alert('Некоторые файлы не были загружены. Проверьте консоль для подробностей.');
            }
          }
        })
        .catch(error => {
          console.error('Ошибка:', error);
          alert('Произошла ошибка при загрузке файлов');
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
      loadGallery();
    });

    function loadGallery() {
      fetch('vendor/get_images.php')
        .then(response => response.json())
        .then(images => {
          const gallery = document.getElementById('imageGallery');

          if (images.length === 0) {
            gallery.innerHTML = '<div class="empty-gallery">Пока нет загруженных изображений</div>';
            return;
          }

          gallery.innerHTML = '';

          images.forEach(image => {
            const imageCard = document.createElement('div');
            imageCard.className = 'image-card';

            imageCard.innerHTML = `
                                  <div class="image-container">
                                    <img src="upload/${image.name}" alt="${image.name}">
                                  </div>
                                  <div class="image-info">
                                  <div class="image-title">${image.name}</div>
                                  <div class="image-size">Размер: ${image.size}</div>
                                  <div class="image-date">Загружено: ${image.date}</div>
                                  <button class="delete-btn" onclick="deleteImage('${image.name}')">Удалить</button>
                                  </div>
                                  `;

            gallery.appendChild(imageCard);
          });
        })
        .catch(error => {
          console.error('Ошибка загрузки галереи:', error);
        });
    }

    function deleteImage(filename) {
      if (confirm('Вы уверены, что хотите удалить это изображение?')) {
        const formData = new FormData();
        formData.append('filename', filename);

        fetch('vendor/delete_image.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(result => {
            if (result.success) {
              loadGallery();
            } else {
              alert('Ошибка при удалении изображения: ' + result.message);
            }
          })
          .catch(error => {
            console.error('Ошибка:', error);
            alert('Ошибка при удалении изображения');
          });
      }
    }
  </script>
</body>

</html>
