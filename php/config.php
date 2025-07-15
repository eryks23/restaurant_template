<?php
// config.php - Database and reCAPTCHA configuration

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access not allowed.');
}

define('DB_HOST',   'localhost');
define('DB_USER',   'your_db_username');
define('DB_PASS',   'your_db_password');
define('DB_NAME',   'your_db_name');

define('RECAPTCHA_SECRET',  'your_recaptcha_secret');
