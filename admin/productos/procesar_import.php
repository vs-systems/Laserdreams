<?php
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['archivo_csv'])) {
    header('Location: index.php');
    exit;
}

try {
    ini_set('auto_detect_line_endings', true);
    $file = $_FILES['archivo_csv']['tmp_name'];

    // Auto-detectar delimitador
    $handle = fopen($file, "r");
    $firstLine = fgets($handle);
    $separator = (str_contains($firstLine, ';')) ? ';' : ',';
    rewind($handle);

    // Ignorar cabecera
    fgetcsv($handle, 1000, $separator);

    $pdo->beginTransaction();

    $importedCount = 0;
    $errors = [];
    $lineNum = 1;

    while (($data = fgetcsv($handle, 2000, $separator)) !== FALSE) {
        $lineNum++;
        if (count($data) < 11) {
            $errors[] = "Línea $lineNum: No tiene suficientes columnas (encontradas: " . count($data) . ")";
            continue;
        }

        $codigo = strtoupper(trim($data[0]));
        $tipo_bulto = trim($data[1]) ?: 'Caja de Cartón';
        $unidades = (int) $data[2] ?: 1;
        $cat_nombre = trim($data[3]);
        $titulo = trim($data[4]);
        $marca_nombre = trim($data[5]);
        $descripcion = trim($data[6]);
        $tagsStr = strtolower(trim($data[7]));
        $costo_compra = (float) $data[8];
        $precio_venta_usd = (float) $data[9];
        $stockStr = strtolower(trim($data[10]));

        if (empty($titulo))
            continue;

        // 1. Manejar Categoría (Crear si no existe)
        $cat_id = null;
        if (!empty($cat_nombre)) {
            $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nombre = ?");
            $stmt->execute([$cat_nombre]);
            $cat_id = $stmt->fetchColumn();
            if (!$cat_id) {
                $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (?)");
                $stmt->execute([$cat_nombre]);
                $cat_id = $pdo->lastInsertId();
            }
        }

        // 2. Manejar Marca (Crear si no existe)
        $marca_id = null;
        if (!empty($marca_nombre)) {
            $stmt = $pdo->prepare("SELECT id FROM marcas WHERE nombre = ?");
            $stmt->execute([$marca_nombre]);
            $marca_id = $stmt->fetchColumn();
            if (!$marca_id) {
                $stmt = $pdo->prepare("INSERT INTO marcas (nombre) VALUES (?)");
                $stmt->execute([$marca_nombre]);
                $marca_id = $pdo->lastInsertId();
            }
        }

        // 3. Procesar Tags
        $nuevo = str_contains($tagsStr, 'nuevo') ? 1 : 0;
        $oferta = str_contains($tagsStr, 'oferta') ? 1 : 0;
        $destacado = str_contains($tagsStr, 'destacado') ? 1 : 0;
        $usado = str_contains($tagsStr, 'usado') ? 1 : 0;

        // 4. Margen
        $margen = 0;
        if ($costo_compra > 0) {
            $margen = (($precio_venta_usd - $costo_compra) / $costo_compra) * 100;
        }

        // 5. Stock
        $en_stock = ($stockStr === 'si' || $stockStr === 'sí' || $stockStr === 'yes') ? 1 : 0;

        // 6. Insertar
        $sql = "INSERT INTO productos 
                (codigo, titulo, marca_id, descripcion, categoria_id, activo, en_stock, es_oferta, es_nuevo, es_destacado, es_usado, 
                 tipo_bulto, unidades_por_bulto, costo_compra, margen_porcentaje, precio_venta_usd)
                VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                titulo=VALUES(titulo), marca_id=VALUES(marca_id), descripcion=VALUES(descripcion), categoria_id=VALUES(categoria_id), 
                en_stock=VALUES(en_stock), es_oferta=VALUES(es_oferta), es_nuevo=VALUES(es_nuevo), es_destacado=VALUES(es_destacado), 
                es_usado=VALUES(es_usado), tipo_bulto=VALUES(tipo_bulto), unidades_por_bulto=VALUES(unidades_por_bulto), 
                costo_compra=VALUES(costo_compra), margen_porcentaje=VALUES(margen_porcentaje), precio_venta_usd=VALUES(precio_venta_usd)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $codigo,
            $titulo,
            $marca_id,
            $descripcion,
            $cat_id,
            $en_stock,
            $oferta,
            $nuevo,
            $destacado,
            $usado,
            $tipo_bulto,
            $unidades,
            $costo_compra,
            $margen,
            $precio_venta_usd
        ]);

        $importedCount++;
    }

    $pdo->commit();
    fclose($handle);

    header('Location: index.php?ok=importados&count=' . $importedCount . '&errors=' . count($errors));
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    die("Error al importar: " . $e->getMessage());
}
