<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';

session_destroy();
redirect(base_url('index.php'));
