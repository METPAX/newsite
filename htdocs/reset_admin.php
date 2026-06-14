<?php
/**
 * Утилита сброса/создания пароля администратора.
 * УДАЛИТЕ этот файл после использования!
 *
 * Открыть в браузере: http://localhost/kolor-shop/reset_admin.php
 */
require_once 'includes/db.php';

$done    = false;
$message = '';
$error   = '';

// Секретный ключ доступа к утилите (измените на свой)
define('RESET_SECRET', 'kolor2025');

$secret = $_POST['secret'] ?? $_GET['secret'] ?? '';
$allowed = ($secret === RESET_SECRET);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $allowed) {
    $email    = trim($_POST['email']    ?? 'admin@kolor.kz');
    $name     = trim($_POST['name']     ?? 'Администратор');
    $phone    = trim($_POST['phone']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (strlen($password) < 6) {
        $error = 'Пароль должен быть не менее 6 символов';
    } else {
        try {
            $db   = getDB();
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // Проверяем, существует ли администратор по его email
            $stmt = $db->prepare("SELECT id FROM auth.users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $existing = $stmt->fetchColumn();

            if ($existing) {
                // Обновляем пароль в auth.users
                $db->prepare("UPDATE auth.users SET encrypted_password = ? WHERE id = ?")
                   ->execute([$hash, $existing]);
                // Обновляем имя, телефон и роль в profiles
                $db->prepare("UPDATE public.profiles SET name = ?, phone = ?, role = 'admin' WHERE id = ?")
                   ->execute([$name, $phone, $existing]);
                $message = "Пароль администратора успешно обновлён!";
            } else {
                $stmtInsertAuth = $db->prepare("
                    INSERT INTO auth.users (
                        instance_id, id, aud, role, email, encrypted_password, 
                        email_confirmed_at, recovery_sent_at, last_sign_in_at, 
                        raw_app_meta_data, raw_user_meta_data, created_at, updated_at, 
                        confirmation_token, email_change, email_change_token_new, recovery_token
                    ) VALUES (
                        '00000000-0000-0000-0000-000000000000', gen_random_uuid(), 'authenticated', 'authenticated', 
                        ?, ?, NOW(), NULL, NULL, 
                        '{\"provider\":\"email\",\"providers\":[\"email\"]}', ?, NOW(), NOW(), 
                        '', '', '', ''
                    ) RETURNING id
                ");
                $rawUserMetaData = json_encode([
                    'name'  => $name,
                    'phone' => $phone
                ], JSON_UNESCAPED_UNICODE);
                $stmtInsertAuth->execute([$email, $hash, $rawUserMetaData]);
                $newId = $stmtInsertAuth->fetchColumn();
                
                // Создаем запись в profiles
                $db->prepare("INSERT INTO public.profiles (id, name, phone, role) VALUES (?, ?, ?, 'admin')")
                   ->execute([$newId, $name, $phone]);
                $message = "Администратор успешно создан!";
            }
            $done = true;
        } catch (Exception $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Сброс пароля администратора — ТОО «Колор»</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', sans-serif; background: #f0ebe2; min-height: 100vh;
         display: flex; align-items: center; justify-content: center; padding: 24px; }
  .box { background: #fff; border: 1px solid #d4c8b8; padding: 40px; width: 100%;
         max-width: 460px; border-radius: 4px; box-shadow: 0 4px 24px rgba(0,0,0,.1); }
  h1   { font-size: 24px; margin-bottom: 6px; color: #3d2b1f; }
  .sub { font-size: 13px; color: #7a6a5a; margin-bottom: 28px; }
  label { display: block; font-size: 11px; letter-spacing: 1px; text-transform: uppercase;
          color: #7a6a5a; margin-bottom: 5px; margin-top: 16px; }
  input { width: 100%; padding: 10px 14px; border: 1px solid #d4c8b8; border-radius: 3px;
          font-size: 15px; font-family: inherit; background: #f5f0e8; }
  input:focus { outline: none; border-color: #b8860b; }
  button { margin-top: 22px; width: 100%; padding: 12px; background: #b8860b; color: #fff;
           border: none; border-radius: 3px; font-size: 14px; font-weight: 600;
           letter-spacing: 1px; cursor: pointer; text-transform: uppercase; }
  button:hover { background: #d4a853; }
  .alert { padding: 12px 16px; border-radius: 3px; margin-bottom: 20px; font-size: 14px; }
  .alert-err  { background: #fff3f3; border-left: 3px solid #c00; color: #900; }
  .alert-ok   { background: #f0fff4; border-left: 3px solid #2a7; color: #155724; }
  .alert-warn { background: #fffbeb; border-left: 3px solid #b8860b; color: #78350f; }
  .lock { text-align: center; padding: 20px 0; color: #7a6a5a; }
  .lock-icon { font-size: 52px; margin-bottom: 12px; }
  .delete-warn { margin-top: 20px; font-size: 12px; color: #c00;
                 background: #fff3f3; padding: 10px 14px; border-radius: 3px; }
</style>
</head>
<body>
<div class="box">
  <h1>🔑 Сброс пароля</h1>
  <p class="sub">Утилита администрирования — ТОО «Колор»</p>

  <?php if ($done): ?>
    <div class="alert alert-ok">✅ <?= htmlspecialchars($message) ?></div>
    <p style="font-size:14px;color:#3d2b1f;margin-bottom:16px;">
      Теперь войдите в панель администратора:<br>
      <strong>Email:</strong> <?= htmlspecialchars($_POST['email'] ?? '') ?><br>
      <strong>Пароль:</strong> <em>(указанный вами)</em>
    </p>
    <a href="admin/index.php" style="display:block;text-align:center;padding:12px;
       background:#3d2b1f;color:#f5f0e8;border-radius:3px;text-decoration:none;
       font-size:13px;letter-spacing:1px;text-transform:uppercase;">
      Перейти в панель администратора →
    </a>
    <div class="delete-warn">
      ⚠️ Удалите файл <strong>reset_admin.php</strong> с сервера после использования!
    </div>

  <?php elseif (!$allowed): ?>
    <div class="lock">
      <div class="lock-icon">🔒</div>
      <p style="margin-bottom:16px;">Введите секретный ключ доступа к утилите</p>
    </div>
    <form method="POST">
      <label>Секретный ключ</label>
      <input type="password" name="secret" autofocus placeholder="Введите ключ доступа">
      <button type="submit">Продолжить</button>
    </form>
    <div class="alert alert-warn" style="margin-top:16px;font-size:12px;">
      Ключ по умолчанию: <code>kolor2025</code><br>
      Изменить можно в строке <code>define('RESET_SECRET', '...')</code> этого файла.
    </div>

  <?php else: ?>
    <?php if ($error): ?>
      <div class="alert alert-err">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="alert alert-warn">
      Создайте или пересоздайте аккаунт администратора с новым паролем.
    </div>
    <form method="POST">
      <input type="hidden" name="secret" value="<?= htmlspecialchars($secret) ?>">
      <label>Email администратора</label>
      <input type="email" name="email" value="admin@kolor.kz" required>
      <label>Имя</label>
      <input type="text" name="name" value="Администратор" required>
      <label>Телефон</label>
      <input type="tel" name="phone" value="+7 (700) 000-00-00">
      <label>Новый пароль (минимум 6 символов)</label>
      <input type="password" name="password" required autofocus>
      <button type="submit">Сохранить администратора</button>
    </form>
    <div class="delete-warn" style="margin-top:16px;">
      ⚠️ Удалите этот файл с сервера после использования!
    </div>
  <?php endif; ?>
</div>
</body>
</html>
