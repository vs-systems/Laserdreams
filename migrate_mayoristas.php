<?php
require __DIR__ . '/includes/db.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `descargas_listas` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `nombre` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `whatsapp` VARCHAR(255) NOT NULL,
            `fecha` DATETIME NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `solicitudes_mayoristas` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `nombre` VARCHAR(255) NOT NULL,
            `localidad` VARCHAR(255) NOT NULL,
            `telefono` VARCHAR(255) NOT NULL,
            `tipo_cliente` VARCHAR(255) NOT NULL,
            `tipo_cliente_otro` VARCHAR(255) DEFAULT '',
            `productos_interes` TEXT,
            `estado` ENUM('Pendiente', 'Contactado', 'Cerrado') DEFAULT 'Pendiente',
            `fecha` DATETIME NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "Migración completada con éxito.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
