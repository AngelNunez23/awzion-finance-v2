<?php
declare(strict_types=1);
require_once 'config.php';
redirect(isLoggedIn() ? 'dashboard.php' : 'login.php');
