<?php
/**
 * Колор — REST API для мобильного приложения
 * Таблицы создаются вручную через phpMyAdmin (SQL в README)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/includes/db.php';

$db = getDB();

$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!$input) { json_error('Invalid JSON'); }

$action = $input['action'] ?? '';

switch ($action) {
    case 'login':               handleLogin($db, $input);         break;
    case 'register':            handleRegister($db, $input);      break;
    case 'get_contacts':        handleGetContacts($db, $input);   break;
    case 'get_messages':        handleGetMessages($db, $input);   break;
    case 'send_message':        handleSendMessage($db, $input);   break;
    case 'get_orders':          handleGetOrders($db, $input);     break;
    case 'update_order_status': handleUpdateStatus($db, $input);  break;
    default: json_error('Unknown action');
}

// ── HANDLERS ──────────────────────────────────────────────────────────

function handleLogin($db, $input) {
    $email = strtolower(trim($input['email'] ?? ''));
    $pass  = $input['password'] ?? '';
    if (!$email || !$pass) json_error('Email and password required');

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password'])) {
        json_error('Invalid credentials', 401);
    }

    $token = bin2hex(random_bytes(32));
    $db->prepare("DELETE FROM api_tokens WHERE user_id = ?")->execute([$user['id']]);
    $db->prepare("INSERT INTO api_tokens (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))")
       ->execute([$user['id'], $token]);

    json_ok(['token' => $token, 'user' => userPublic($user)]);
}

function handleRegister($db, $input) {
    $name  = trim($input['name']     ?? '');
    $email = strtolower(trim($input['email'] ?? ''));
    $phone = trim($input['phone']    ?? '');
    $pass  = $input['password']      ?? '';

    if (!$name || !$email || !$pass) json_error('Name, email and password required');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_error('Invalid email');
    if (strlen($pass) < 6) json_error('Password too short');

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) json_error('Email already registered', 409);

    $db->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?,?,?,?,?)")
       ->execute([$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT), 'customer']);

    json_ok(['message' => 'Registered successfully']);
}

function handleGetContacts($db, $input) {
    $user = requireAuth($db, $input);

    if (in_array($user['role'], ['admin','designer','secretary','installer'])) {
        $stmt = $db->query("SELECT * FROM users ORDER BY role, name");
    } else {
        $stmt = $db->query("SELECT * FROM users WHERE role IN ('admin','designer','secretary','installer') ORDER BY name");
    }

    $contacts = array_values(array_filter(
        array_map('userPublic', $stmt->fetchAll()),
        function($c) use ($user) { return $c['id'] != $user['id']; }
    ));

    json_ok(['contacts' => $contacts]);
}

function handleGetMessages($db, $input) {
    $user       = requireAuth($db, $input);
    $withUserId = (int)($input['with_user_id'] ?? 0);
    if (!$withUserId) json_error('with_user_id required');

    $stmt = $db->prepare("
        SELECT * FROM chat_messages
        WHERE (from_user_id = ? AND to_user_id = ?)
           OR (from_user_id = ? AND to_user_id = ?)
        ORDER BY created_at ASC
        LIMIT 200
    ");
    $stmt->execute([$user['id'], $withUserId, $withUserId, $user['id']]);
    $msgs = $stmt->fetchAll();

    $db->prepare("
        UPDATE chat_messages SET is_read = 1
        WHERE to_user_id = ? AND from_user_id = ? AND is_read = 0
    ")->execute([$user['id'], $withUserId]);

    json_ok(['messages' => $msgs]);
}

function handleSendMessage($db, $input) {
    $user     = requireAuth($db, $input);
    $toUserId = (int)($input['to_user_id'] ?? 0);
    $text     = trim($input['text'] ?? '');
    if (!$toUserId || !$text) json_error('to_user_id and text required');

    $r = $db->prepare("SELECT id FROM users WHERE id = ?");
    $r->execute([$toUserId]);
    if (!$r->fetch()) json_error('Recipient not found', 404);

    $db->prepare("INSERT INTO chat_messages (from_user_id, to_user_id, text) VALUES (?,?,?)")
       ->execute([$user['id'], $toUserId, $text]);

    json_ok(['message' => 'Sent']);
}

function handleGetOrders($db, $input) {
    $user = requireAuth($db, $input);

    if (in_array($user['role'], ['admin','designer','secretary','installer'])) {
        $stmt = $db->query("
            SELECT o.*, u.name as user_name, u.email as user_email
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
        ");
    } else {
        $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['id']]);
    }

    json_ok(['orders' => $stmt->fetchAll()]);
}

function handleUpdateStatus($db, $input) {
    $user = requireAuth($db, $input);
    if (!in_array($user['role'], ['admin','designer','secretary','installer']))
        json_error('Not authorized', 403);

    $orderId = (int)($input['order_id'] ?? 0);
    $status  = $input['status'] ?? '';
    $allowed = ['new','processing','done','measure','prod','ready'];
    if (!$orderId || !in_array($status, $allowed)) json_error('Invalid params');

    $db->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $orderId]);
    json_ok(['message' => 'Status updated']);
}

// ── HELPERS ───────────────────────────────────────────────────────────

function requireAuth($db, $input) {
    $token = $input['token'] ?? '';
    if (!$token) json_error('Token required', 401);

    $stmt = $db->prepare("
        SELECT u.* FROM users u
        JOIN api_tokens t ON t.user_id = u.id
        WHERE t.token = ? AND t.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if (!$user) json_error('Invalid or expired token', 401);
    return $user;
}

function userPublic($u) {
    return [
        'id'    => (int)$u['id'],
        'name'  => $u['name'],
        'email' => $u['email'],
        'phone' => $u['phone'] ?? '',
        'role'  => $u['role'],
    ];
}

function json_ok($data) {
    echo json_encode(array_merge(['ok' => true], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}
?>