<?php
// ===============================
// CONFIG GLOBAL (SEGURO)
// ===============================

// Evitar doble carga
if (defined('APP_CONFIG_LOADED')) {
    return;
}
define('APP_CONFIG_LOADED', true);

// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Iniciar sesión SOLO si no existe
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// === CONFIG DB ===
define('DB_HOST', 'localhost');
define('DB_NAME', 'u499089589_laserdreams');
define('DB_USER', 'u499089589_Javier');
define('DB_PASS', 'Andrea1910@!!');
