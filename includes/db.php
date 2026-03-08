<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    // Return a visible error for debugging if needed, but safe for production
    die('Error de conexión a la base de datos. Por favor, verifica las credenciales en config.php. Detalles ocultos por seguridad.');
}

/**
 * Obtener un ajuste dinámico de la tabla 'ajustes'
 */
function get_ajuste($clave, $default = '')
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT valor FROM ajustes WHERE clave = ?");
        $stmt->execute([$clave]);
        return $stmt->fetchColumn() ?: $default;
    } catch (Exception $e) {
        return $default;
    }
}

function set_ajuste($clave, $valor, $descripcion = '')
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO ajustes (clave, valor, descripcion) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt->execute([$clave, $valor, $descripcion, $valor]);
    } catch (Exception $e) {
    }
}

function actualizar_cotizaciones_api()
{
    $last_time = (int) get_ajuste('usd_last_time', 0);
    $blue_val = (float) get_ajuste('usd_blue_val', 0);
    $oficial_val = (float) get_ajuste('usd_oficial_val', 0);

    // Fetch every 30 minutes (1800 seconds)
    if (time() - $last_time > 1800) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // Fetch Blue
            curl_setopt($ch, CURLOPT_URL, "https://dolarapi.com/v1/dolares/blue");
            $json_blue = curl_exec($ch);
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200 && $json_blue) {
                $data = json_decode($json_blue, true);
                if (isset($data['venta'])) {
                    $blue_val = (float) $data['venta'];
                    set_ajuste('usd_blue_val', $blue_val, 'Valor Dolar Blue Venta');
                }
            }

            // Fetch Oficial
            curl_setopt($ch, CURLOPT_URL, "https://dolarapi.com/v1/dolares/oficial");
            $json_oficial = curl_exec($ch);
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200 && $json_oficial) {
                $data = json_decode($json_oficial, true);
                if (isset($data['venta'])) {
                    $oficial_val = (float) $data['venta'];
                    set_ajuste('usd_oficial_val', $oficial_val, 'Valor Dolar Oficial Venta');
                }
            }

            curl_close($ch);
            set_ajuste('usd_last_time', time(), 'Ultima actualizacion api dolar');
        } catch (Exception $e) {
            // Silently fail and use last known values
        }
    }

    return ['blue' => $blue_val, 'oficial' => $oficial_val];
}

$cotizaciones = actualizar_cotizaciones_api();
$GLOBALS['dolar_blue_base'] = $cotizaciones['blue'];
$GLOBALS['dolar_oficial_base'] = $cotizaciones['oficial'];

// Mantengo esta variable para retrocompatibilidad temporal, usando el default de blue + 15
$GLOBALS['cotizacion_aplicada'] = $cotizaciones['blue'] > 0 ? $cotizaciones['blue'] + 15 : 0;

/**
 * Calcula el precio final de un producto basado en su configuración de marca
 */
function calcular_precio_final($precio_usd, $tipo_dolar = 'blue', $recargo_pesos = 0, $recargo_porcentaje = 0)
{
    $precio_usd = (float) $precio_usd;
    $recargo_pesos = (float) $recargo_pesos;
    $recargo_porcentaje = (float) $recargo_porcentaje;

    $coti_base = ($tipo_dolar === 'oficial') ? $GLOBALS['dolar_oficial_base'] : $GLOBALS['dolar_blue_base'];
    if ($coti_base <= 0) {
        return 0; // Prevenir cálculos con cotización 0
    }

    $coti_aplicada = $coti_base + $recargo_pesos;
    $subtotal_pesos = $precio_usd * $coti_aplicada;

    $monto_recargo_porc = 0;
    if ($recargo_porcentaje > 0) {
        $monto_recargo_porc = $subtotal_pesos * ($recargo_porcentaje / 100);
    }

    return $subtotal_pesos + $monto_recargo_porc;
}

