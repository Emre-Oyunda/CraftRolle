<?php
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function base_url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}
