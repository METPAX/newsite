<?php
require_once '../includes/functions.php';
if (!isAdmin()) { flash('Доступ запрещён', 'error'); redirect('../login.php'); }
$db = getDB();
$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? 'new';
$allowed = ['new', 'processing', 'done'];
if ($id && in_array($status, $allowed)) {
    $db->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $id]);
    flash('Статус заказа обновлён');
}
$redirect = $_POST['redirect'] ?? 'orders.php';
redirect($redirect);
