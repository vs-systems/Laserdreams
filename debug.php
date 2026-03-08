<?php
// Mostrar todos los errores de PHP en pantalla para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Requerir el archivo que está fallando (catalogo.php) para ver el error real
require __DIR__ . '/catalogo.php';
?>