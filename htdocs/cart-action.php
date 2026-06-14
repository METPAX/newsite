<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    flash('Войдите в аккаунт, чтобы добавить товар в корзину', 'error');
    redirect('login.php?redirect=' . urlencode($_POST['redirect'] ?? 'cart.php'));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        if ($pid) {
            addToCart($pid, $qty);
            flash('Товар добавлен в корзину!');
        }
        redirect($_POST['redirect'] ?? 'cart.php');
        break;

    case 'remove':
        $cid = (int)($_GET['cart_id'] ?? 0);
        if ($cid) removeFromCart($cid);
        flash('Товар удалён из корзины');
        redirect('cart.php');
        break;

    case 'update':
        $cid = (int)($_POST['cart_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);
        if ($cid) updateCartQty($cid, $qty);
        redirect('cart.php');
        break;

    case 'clear':
        if (isLoggedIn()) {
            $db = getDB();
            $db->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);
        }
        redirect('cart.php');
        break;
}

redirect('cart.php');
?>
