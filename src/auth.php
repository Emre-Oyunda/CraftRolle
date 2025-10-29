<?php
function current_user() {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $st = db()->prepare("SELECT * FROM users WHERE id = ?");
    $st->execute([$_SESSION['user_id']]);
    return $st->fetch(PDO::FETCH_ASSOC);
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        redirect(base_url('login.php'));
    }
}
