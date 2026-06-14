<?php
require_once '../includes/functions.php';
if (!isAdmin()) { flash('Доступ запрещён', 'error'); redirect('../login.php'); }

$pageTitle = 'Товары — Администрирование';
$db        = getDB();

// Папка для загрузки изображений
define('UPLOAD_DIR', dirname(__DIR__) . '/assets/img/products/');
define('UPLOAD_URL', '../assets/img/products/');

// Создаём папку если не существует
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

/* -------------------------------------------------------
   Обработка загрузки изображения
   ------------------------------------------------------- */
function handleImageUpload($fieldName, $oldImage = '') {
    if (empty($_FILES[$fieldName]['name'])) {
        return $oldImage ?: 'default.jpg'; // файл не выбран — оставляем старый
    }

    $file     = $_FILES[$fieldName];
    $allowed  = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize  = 5 * 1024 * 1024; // 5 МБ

    // Проверки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        flash('Ошибка загрузки файла (код ' . $file['error'] . ')', 'error');
        return $oldImage ?: 'default.jpg';
    }
    if (!in_array($file['type'], $allowed)) {
        flash('Допустимые форматы: JPG, PNG, WEBP, GIF', 'error');
        return $oldImage ?: 'default.jpg';
    }
    if ($file['size'] > $maxSize) {
        flash('Файл слишком большой. Максимум 5 МБ', 'error');
        return $oldImage ?: 'default.jpg';
    }

    // Генерируем уникальное имя
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newName  = 'product_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
    $destPath = UPLOAD_DIR . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        flash('Не удалось сохранить файл. Проверьте права папки assets/img/products/', 'error');
        return $oldImage ?: 'default.jpg';
    }

    // Удаляем старое изображение (если не дефолтное)
    if ($oldImage && $oldImage !== 'default.jpg') {
        $oldPath = UPLOAD_DIR . $oldImage;
        if (file_exists($oldPath)) {
            @unlink($oldPath);
        }
    }

    return $newName;
}

/* -------------------------------------------------------
   Обработка POST-запросов
   ------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id   = (int)$_POST['id'];
        $stmt = $db->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $row  = $stmt->fetch();
        // Удаляем файл
        if ($row && $row['image'] && $row['image'] !== 'default.jpg') {
            @unlink(UPLOAD_DIR . $row['image']);
        }
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        flash('Товар удалён');
        redirect('products.php');

    } elseif ($action === 'toggle_stock') {
        $db->prepare("UPDATE products SET in_stock = NOT in_stock WHERE id = ?")->execute([(int)$_POST['id']]);
        redirect('products.php');

    } elseif ($action === 'toggle_featured') {
        $db->prepare("UPDATE products SET featured = NOT featured WHERE id = ?")->execute([(int)$_POST['id']]);
        redirect('products.php');

    } elseif ($action === 'add' || $action === 'edit') {
        $id       = (int)($_POST['id'] ?? 0);
        $oldImage = '';

        if ($action === 'edit' && $id) {
            $s = $db->prepare("SELECT image FROM products WHERE id = ?");
            $s->execute([$id]);
            $row      = $s->fetch();
            $oldImage = $row['image'] ?? '';
        }

        // Загружаем новое изображение (или оставляем старое)
        $imageName = handleImageUpload('image_file', $oldImage);

        $data = [
            (int)$_POST['category_id'],
            trim($_POST['name']),
            trim($_POST['description']),
            (float)$_POST['price'],
            $_POST['old_price'] !== '' ? (float)$_POST['old_price'] : null,
            $imageName,
            trim($_POST['material']),
            trim($_POST['dimensions']),
            trim($_POST['color']),
            isset($_POST['in_stock'])  ? 1 : 0,
            isset($_POST['featured']) ? 1 : 0,
        ];

        if ($action === 'add') {
            $db->prepare("
                INSERT INTO products
                    (category_id, name, description, price, old_price, image,
                     material, dimensions, color, in_stock, featured)
                VALUES (?,?,?,?,?,?,?,?,?,?,?)
            ")->execute($data);
            flash('Товар успешно добавлен');
        } else {
            $data[] = $id;
            $db->prepare("
                UPDATE products SET
                    category_id=?, name=?, description=?, price=?, old_price=?,
                    image=?, material=?, dimensions=?, color=?, in_stock=?, featured=?
                WHERE id=?
            ")->execute($data);
            flash('Товар успешно обновлён');
        }
        redirect('products.php');
    }
}

/* -------------------------------------------------------
   Данные для страницы
   ------------------------------------------------------- */
$products = $db->query("
    SELECT p.*, c.name AS cat_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
")->fetchAll();

$categories  = getCategories();
$editId      = (int)($_GET['edit'] ?? 0);
$editProduct = null;
if ($editId) {
    $s = $db->prepare("SELECT * FROM products WHERE id = ?");
    $s->execute([$editId]);
    $editProduct = $s->fetch();
}

include '../includes/header.php';
?>

<style>
/* ---- дополнительные стили для загрузки изображения ---- */
.img-upload-wrap { display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap; }
.img-preview-box {
    width:120px; height:100px; flex-shrink:0;
    border:2px dashed var(--border); border-radius:4px;
    display:flex; align-items:center; justify-content:center;
    overflow:hidden; background:var(--cream-dark);
    font-size:36px; cursor:pointer; transition:border-color .2s;
    position:relative;
}
.img-preview-box:hover { border-color:var(--gold); }
.img-preview-box img { width:100%; height:100%; object-fit:cover; }
.img-preview-box .img-hint {
    position:absolute; inset:0; background:rgba(61,43,31,.55);
    color:#fff; font-size:11px; letter-spacing:1px; text-transform:uppercase;
    display:flex; align-items:center; justify-content:center; text-align:center;
    padding:6px; opacity:0; transition:opacity .2s;
}
.img-preview-box:hover .img-hint { opacity:1; }
.img-upload-info { flex:1; min-width:200px; }
.img-upload-info label { display:block; font-size:12px; letter-spacing:1.5px;
    text-transform:uppercase; color:var(--text-muted); margin-bottom:6px; }
.file-drop {
    border:2px dashed var(--border); border-radius:4px; padding:14px 16px;
    text-align:center; cursor:pointer; transition:all .2s; background:var(--cream);
    font-size:13px; color:var(--text-muted);
}
.file-drop:hover, .file-drop.drag-over { border-color:var(--gold); background:var(--cream-dark); color:var(--text); }
.file-drop input[type=file] { display:none; }
.file-drop-btn { color:var(--gold); font-weight:600; text-decoration:underline; cursor:pointer; }
.file-drop-note { font-size:11px; color:var(--text-muted); margin-top:4px; }
.img-fname { margin-top:6px; font-size:12px; color:var(--text-muted); word-break:break-all; }
</style>

<div class="admin-layout">
  <nav class="admin-sidebar">
    <div style="padding:0 24px 24px;color:rgba(245,240,232,.4);font-size:11px;letter-spacing:2px;text-transform:uppercase;">Панель управления</div>
    <a href="index.php">📊 Дашборд</a>
    <a href="products.php" class="active">📦 Товары</a>
    <a href="orders.php">🧾 Заказы</a>
    <a href="users.php">👥 Клиенты</a>
    <a href="settings.php">⚙️ Настройки</a>
    <a href="../index.php">← На сайт</a>
  </nav>

  <div class="admin-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
      <h1 style="margin:0;">Товары (<?= count($products) ?>)</h1>
      <button class="btn" onclick="toggleForm(true)">+ Добавить товар</button>
    </div>

    <!-- ==================== ФОРМА ДОБАВЛЕНИЯ / РЕДАКТИРОВАНИЯ ==================== -->
    <div id="add-form" style="display:<?= $editId ? 'block' : 'none' ?>;background:var(--white);border:1px solid var(--border);padding:32px;border-radius:2px;margin-bottom:28px;">
      <h2 style="font-family:var(--font-display);font-size:24px;margin-bottom:20px;">
        <?= $editId ? '✏️ Редактировать товар' : '➕ Добавить новый товар' ?>
      </h2>

      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $editId ? 'edit' : 'add' ?>">
        <?php if($editId): ?><input type="hidden" name="id" value="<?= $editId ?>"><?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <!-- Название -->
          <div class="form-group">
            <label>Название *</label>
            <input type="text" name="name" required value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>">
          </div>
          <!-- Категория -->
          <div class="form-group">
            <label>Категория</label>
            <select name="category_id">
              <?php foreach($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($editProduct['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                <?= $c['icon'] ?> <?= htmlspecialchars($c['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Цена -->
          <div class="form-group">
            <label>Цена (₸) *</label>
            <input type="number" name="price" required value="<?= $editProduct['price'] ?? '' ?>" step="100" min="0">
          </div>
          <!-- Старая цена -->
          <div class="form-group">
            <label>Старая цена (₸) <span style="font-weight:300;text-transform:none;">(необязательно)</span></label>
            <input type="number" name="old_price" value="<?= $editProduct['old_price'] ?? '' ?>" step="100" min="0">
          </div>
          <!-- Материал -->
          <div class="form-group">
            <label>Материал</label>
            <input type="text" name="material" value="<?= htmlspecialchars($editProduct['material'] ?? '') ?>">
          </div>
          <!-- Размеры -->
          <div class="form-group">
            <label>Размеры</label>
            <input type="text" name="dimensions" value="<?= htmlspecialchars($editProduct['dimensions'] ?? '') ?>">
          </div>
          <!-- Цвет -->
          <div class="form-group">
            <label>Цвет</label>
            <input type="text" name="color" value="<?= htmlspecialchars($editProduct['color'] ?? '') ?>">
          </div>
        </div>

        <!-- Описание -->
        <div class="form-group">
          <label>Описание</label>
          <textarea name="description"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
        </div>

        <!-- ======== ЗАГРУЗКА ИЗОБРАЖЕНИЯ ======== -->
        <div class="form-group">
          <label style="display:block;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px;">
            Изображение товара
          </label>
          <div class="img-upload-wrap">

            <!-- Превью -->
            <div class="img-preview-box" id="previewBox" onclick="document.getElementById('imgFileInput').click()">
              <?php
                $curImg = $editProduct['image'] ?? '';
                $curPath = '../assets/img/products/' . $curImg;
                if ($curImg && $curImg !== 'default.jpg' && file_exists(dirname(__DIR__) . '/assets/img/products/' . $curImg)):
              ?>
                <img id="previewImg" src="<?= htmlspecialchars($curPath) ?>" alt="">
              <?php else: ?>
                <span id="previewPlaceholder" style="font-size:36px;">🖼️</span>
                <img id="previewImg" src="" alt="" style="display:none;">
              <?php endif; ?>
              <div class="img-hint">Нажмите<br>для замены</div>
            </div>

            <!-- Зона загрузки -->
            <div class="img-upload-info">
              <div class="file-drop" id="fileDrop" onclick="document.getElementById('imgFileInput').click()">
                <input type="file" name="image_file" id="imgFileInput"
                       accept="image/jpeg,image/png,image/webp,image/gif"
                       onchange="handleFileSelect(this)">
                <div>📁 <span class="file-drop-btn">Выберите файл</span> или перетащите сюда</div>
                <div class="file-drop-note">JPG, PNG, WEBP, GIF — максимум 5 МБ</div>
              </div>
              <div class="img-fname" id="imgFname">
                <?php if($curImg && $curImg !== 'default.jpg'): ?>
                  Текущий файл: <strong><?= htmlspecialchars($curImg) ?></strong>
                <?php else: ?>
                  Файл не выбран
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <!-- ======== /ЗАГРУЗКА ИЗОБРАЖЕНИЯ ======== -->

        <!-- Чекбоксы -->
        <div style="display:flex;gap:24px;margin-bottom:20px;margin-top:4px;">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
            <input type="checkbox" name="in_stock" <?= ($editProduct['in_stock'] ?? 1) ? 'checked' : '' ?>>
            В наличии
          </label>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
            <input type="checkbox" name="featured" <?= ($editProduct['featured'] ?? 0) ? 'checked' : '' ?>>
            ⭐ Хит продаж
          </label>
        </div>

        <div style="display:flex;gap:12px;">
          <button type="submit" class="btn"><?= $editId ? '💾 Сохранить изменения' : '➕ Добавить товар' ?></button>
          <a href="products.php" class="btn btn--ghost btn--dark">Отмена</a>
        </div>
      </form>
    </div>
    <!-- ==================== /ФОРМА ==================== -->

    <!-- ТАБЛИЦА ТОВАРОВ -->
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Фото</th>
          <th>Название</th>
          <th>Категория</th>
          <th>Цена</th>
          <th>Наличие</th>
          <th>Хит</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <?php
          $imgPath = dirname(__DIR__) . '/assets/img/products/' . $p['image'];
          $imgUrl  = '../assets/img/products/' . $p['image'];
          $hasImg  = $p['image'] && $p['image'] !== 'default.jpg' && file_exists($imgPath);
          $icons   = ['sofa'=>'🛋️','bed'=>'🛏️','kitchen'=>'🍽️','wardrobe'=>'🗄️','table'=>'🪑','chair'=>'🪑','shelf'=>'📚','armchair'=>'💺','kids'=>'🧸'];
          $icon    = '🛋️';
          foreach($icons as $k=>$v) if(strpos($p['image'],$k)!==false){$icon=$v;break;}
        ?>
        <tr>
          <td style="color:var(--text-muted);font-size:12px;">#<?= $p['id'] ?></td>
          <td>
            <div style="width:56px;height:44px;border-radius:3px;overflow:hidden;background:var(--cream-dark);
                        display:flex;align-items:center;justify-content:center;font-size:22px;border:1px solid var(--border);">
              <?php if($hasImg): ?>
                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <?= $icon ?>
              <?php endif; ?>
            </div>
          </td>
          <td>
            <strong><?= htmlspecialchars($p['name']) ?></strong>
            <?php if($p['old_price']): ?>
              <br><span style="font-size:11px;color:var(--gold);">Акция</span>
            <?php endif; ?>
          </td>
          <td style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($p['cat_name'] ?? '—') ?></td>
          <td>
            <strong><?= formatPrice($p['price']) ?></strong>
            <?php if($p['old_price']): ?>
              <br><span style="text-decoration:line-through;font-size:12px;color:var(--text-muted);"><?= formatPrice($p['old_price']) ?></span>
            <?php endif; ?>
          </td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="action" value="toggle_stock">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button type="submit"
                class="status-badge <?= $p['in_stock'] ? 'status-done' : 'status-new' ?>"
                style="border:none;cursor:pointer;font-family:var(--font-body);">
                <?= $p['in_stock'] ? '✅ В наличии' : '❌ Нет' ?>
              </button>
            </form>
          </td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="action" value="toggle_featured">
              <input type="hidden" name="id" value="<?= $p['id'] ?>">
              <button type="submit" style="background:none;border:none;cursor:pointer;font-size:20px;" title="Переключить хит">
                <?= $p['featured'] ? '⭐' : '☆' ?>
              </button>
            </form>
          </td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
              <a href="products.php?edit=<?= $p['id'] ?>#add-form" class="btn btn--sm btn--ghost btn--dark">✏️ Ред.</a>
              <a href="../product.php?id=<?= $p['id'] ?>" class="btn btn--sm btn--ghost btn--dark" target="_blank" title="Просмотр на сайте">👁️</a>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить товар «<?= htmlspecialchars(addslashes($p['name'])) ?>»?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn--sm" style="background:#8B2020;" title="Удалить">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div><!-- /admin-content -->
</div><!-- /admin-layout -->

<script>
// Показать/скрыть форму
function toggleForm(show) {
    const f = document.getElementById('add-form');
    f.style.display = show ? 'block' : 'none';
    if (show) f.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// При выборе файла — показать превью
function handleFileSelect(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    // Проверка размера
    if (file.size > 5 * 1024 * 1024) {
        alert('Файл слишком большой. Максимальный размер — 5 МБ.');
        input.value = '';
        return;
    }

    // Превью
    const reader = new FileReader();
    reader.onload = function(e) {
        const img         = document.getElementById('previewImg');
        const placeholder = document.getElementById('previewPlaceholder');
        img.src           = e.target.result;
        img.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);

    // Имя файла
    document.getElementById('imgFname').innerHTML =
        'Выбран: <strong>' + file.name + '</strong> (' + (file.size / 1024).toFixed(0) + ' КБ)';

    // Подсветка зоны
    document.getElementById('fileDrop').style.borderColor = 'var(--gold)';
}

// Drag & Drop
const dropZone = document.getElementById('fileDrop');
if (dropZone) {
    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const input = document.getElementById('imgFileInput');
        if (e.dataTransfer.files.length) {
            // Передаём файл в input через DataTransfer
            const dt = new DataTransfer();
            dt.items.add(e.dataTransfer.files[0]);
            input.files = dt.files;
            handleFileSelect(input);
        }
    });
}

// Если открыли страницу с ?edit= — сразу показываем форму
<?php if ($editId): ?>
document.addEventListener('DOMContentLoaded', () => {
    const f = document.getElementById('add-form');
    if (f) f.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
