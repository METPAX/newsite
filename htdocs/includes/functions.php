<?php
require_once __DIR__ . '/db.php';
session_start();

// ============================================================
//  Хеширование и проверка паролей
// ============================================================

function verifyPassword(string $password, string $stored): bool
{
    // Прямое сравнение (если пароль сохранен как простой текст)
    if ($password === $stored) {
        return true;
    }
    return password_verify($password, $stored);
}

// ============================================================
//  Авторизация (PostgreSQL)
// ============================================================

function isLoggedIn() { return isset($_SESSION['user_id']); }
function isAdmin()    { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }
function currentUser(){ return $_SESSION ?? null; }

function login($email, $password) {
    $email = trim($email);
    $password = trim($password);
    
    if (empty($email) || empty($password)) return false;
    
    $db = getDB();
    $stmt = $db->prepare("
        SELECT u.id, u.email, u.encrypted_password, p.name, p.phone, p.role
        FROM auth.users u
        LEFT JOIN public.profiles p ON u.id = p.id
        WHERE u.email = ? LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && verifyPassword($password, $user['encrypted_password'])) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['name'] = $user['name'] ?? 'Пользователь';
        $_SESSION['role'] = $user['role'] ?? 'customer';
        return true;
    }
    return false;
}

function register($name, $email, $phone, $password) {
    $name = trim($name);
    $email = trim($email);
    $phone = trim($phone);
    
    if (empty($name) || empty($email) || empty($password)) return false;
    
    $db = getDB();
    
    // Проверка существования пользователя
    $stmt = $db->prepare("SELECT id FROM auth.users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false;
    }
    
    // Хешируем пароль (bcrypt)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        // Создаем пользователя в auth.users
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
        $stmtInsertAuth->execute([$email, $hashedPassword, $rawUserMetaData]);
        $newId = $stmtInsertAuth->fetchColumn();
        
        if ($newId) {
            // Сохраняем дополнительные данные в profiles
            $db->prepare("
                INSERT INTO public.profiles (id, name, phone, role) 
                VALUES (?, ?, ?, 'customer')
                ON CONFLICT (id) DO NOTHING
            ")->execute([$newId, $name, $phone]);
            
            // Авторизуем пользователя
            if (session_status() === PHP_SESSION_NONE) session_start();
            
            $_SESSION['user_id'] = $newId;
            $_SESSION['user_email'] = $email;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = 'customer';
            return true;
        }
    } catch (Exception $e) {
        // Ошибка вставки
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

// ============================================================
//  Корзина (Синтаксис обновлен под PostgreSQL)
// ============================================================

function getCartCount() {
    if (!isLoggedIn()) return 0;
    $stmt = getDB()->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return (int)$stmt->fetchColumn();
}

function getCartItems() {
    if (!isLoggedIn()) return [];
    $stmt = getDB()->prepare("
        SELECT c.id, c.quantity,
               p.id AS product_id, p.name, p.price, p.image
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll();
}

function addToCart($product_id, $qty = 1) {
    if (!isLoggedIn()) return false;
    // Используем правильный синтаксис конфликтов PostgreSQL вместо ON DUPLICATE KEY MySQL
    getDB()->prepare("
        INSERT INTO cart (user_id, product_id, quantity)
        VALUES (?, ?, ?)
        ON CONFLICT (user_id, product_id) 
        DO UPDATE SET quantity = cart.quantity + EXCLUDED.quantity
    ")->execute([$_SESSION['user_id'], $product_id, $qty]);
    return true;
}

function removeFromCart($cart_id) {
    if (!isLoggedIn()) return;
    getDB()->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?")
           ->execute([$cart_id, $_SESSION['user_id']]);
}

function updateCartQty($cart_id, $qty) {
    if (!isLoggedIn()) return;
    if ($qty < 1) { removeFromCart($cart_id); return; }
    getDB()->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?")
           ->execute([$qty, $cart_id, $_SESSION['user_id']]);
}

// ============================================================
//  Товары (Без обратных кавычек)
// ============================================================

function getProducts($filters = []) {
    $db     = getDB();
    $where  = ['1=1'];
    $params = [];

    if (!empty($filters['category'])) {
        $where[]  = 'c.slug = ?';
        $params[] = $filters['category'];
    }
    if (!empty($filters['featured'])) {
        $where[] = 'p.featured = true';
    }
    if (!empty($filters['search'])) {
        $where[]  = '(p.name ILIKE ? OR p.description ILIKE ?)'; // ILIKE - регистронезависимый поиск в Postgres
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }

    $limit = isset($filters['limit']) ? 'LIMIT ' . (int)$filters['limit'] : '';
    $sql   = "
        SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY p.featured DESC, p.id DESC
        $limit
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProduct($id) {
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT p.*, c.name AS cat_name, c.slug AS cat_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getCategories() {
    return getDB()->query("SELECT * FROM categories ORDER BY id")->fetchAll();
}

// ============================================================
//  Вспомогательные
// ============================================================

function formatPrice($price) {
    return number_format((float)$price, 0, '.', ' ') . ' ₸';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function getFlash() {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// ============================================================
//  Настройки сайта (Динамическое управление)
// ============================================================

function getSetting($key, $default = '') {
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        try {
            $db = getDB();
            $stmt = $db->query("SELECT setting_key, setting_value FROM public.settings");
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            // Если таблица отсутствует, инициализируем её
            initSettingsTable();
            try {
                $stmt = getDB()->query("SELECT setting_key, setting_value FROM public.settings");
                while ($row = $stmt->fetch()) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            } catch (PDOException $ex) {
                // Если не получилось, оставляем пустым
            }
        }
    }
    return $settings[$key] ?? $default;
}

function initSettingsTable() {
    try {
        $db = getDB();
        $db->exec("
            CREATE TABLE IF NOT EXISTS public.settings (
                setting_key VARCHAR(100) PRIMARY KEY,
                setting_value TEXT
            )
        ");
        
        $defaults = [
            'site_name' => 'ТОО «Колор»',
            'site_logo_text' => 'КОЛОР <em>мебель</em>',
            'site_phone1' => '+7 (722) 200-00-00',
            'site_phone2' => '+7 (700) 200-00-00',
            'site_email1' => 'info@kolor.kz',
            'site_email2' => 'order@kolor.kz',
            'site_working_hours' => "Пн–Пт: 9:00–18:00\nСуббота: 10:00–16:00\nВоскресенье: выходной",
            'site_working_hours_short' => 'Пн–Сб: 9:00–18:00',
            'site_address' => 'г. Алтай, ул. Промышленная, 15',
            'site_address_region' => 'Восточно-Казахстанская обл.',
            'app_title' => 'Мобильное приложение КОЛОР',
            'app_description' => 'Наше мобильное приложение поможет вам просматривать полный каталог мебельных изделий, делать быстрые заказы, отслеживать статус сборки/доставки и связываться с менеджером в один клик. Скачайте приложение прямо сейчас!',
            'app_version' => '1.0.0',
            'app_features' => "Каталог мебели в кармане\nБыстрый и удобный заказ\nУведомления о статусе сборки и доставки\nКалькулятор стоимости индивидуальных размеров\nПрямая связь с поддержкой",
            'app_download_link' => 'https://drive.google.com/file/d/1kajcHIaRO3TlFz9vOYUJZ3I_dml8t2YB/view?usp=sharing'
        ];
        
        $stmt = $db->prepare("INSERT INTO public.settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT (setting_key) DO NOTHING");
        foreach ($defaults as $k => $v) {
            $stmt->execute([$k, $v]);
        }
    } catch (Exception $e) {
        // Подавление ошибок для избежания прерывания работы сайта
    }
}

function getGoogleDriveDirectLink($url) {
    if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
    }
    if (preg_match('/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
    }
    return $url;
}
?>