<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/auth.php';
// Database is already included in auth.php usually, but auth.php includes db.php, so we have access to them.
$dolar_blue_venta = $GLOBALS['dolar_blue_base'] ?? 0;
$cotizacion_aplicada = $GLOBALS['cotizacion_aplicada'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Laserdreams</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="bg-[#fbfbfb] antialiased min-h-screen flex flex-col">

    <header class="bg-gray-900 text-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="/admin/dashboard.php" class="flex items-center gap-2 group">
                    <span class="text-xl font-black tracking-tight">Laser<span class="text-violet-500">dreams</span>
                        Admin</span>
                </a>
                <span
                    class="hidden md:inline text-[10px] font-black uppercase tracking-widest text-gray-400 bg-white/5 px-3 py-1 rounded-full">Gestión
                    Oficial</span>
            </div>

            <div class="flex items-center gap-4 hidden md:flex">
                <div
                    class="bg-blue-900/40 border border-blue-500/30 text-blue-300 px-3 py-1.5 rounded-lg flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-widest opacity-80">Dólar Blue (V. +
                        $15)</span>
                    <span
                        class="text-sm font-black">$<?php echo number_format($cotizacion_aplicada, 2, ',', '.'); ?></span>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <nav class="hidden lg:flex items-center gap-6">
                    <a href="/admin/dashboard.php"
                        class="text-xs font-black uppercase tracking-widest <?= str_contains($_SERVER['PHP_SELF'], 'dashboard') ? 'text-violet-500' : 'text-gray-400 hover:text-white' ?>">Inicio</a>
                    <a href="/admin/productos/index.php"
                        class="text-xs font-black uppercase tracking-widest <?= str_contains($_SERVER['PHP_SELF'], 'productos') ? 'text-violet-500' : 'text-gray-400 hover:text-white' ?>">Productos</a>
                    <a href="/admin/categorias/index.php"
                        class="text-xs font-black uppercase tracking-widest <?= str_contains($_SERVER['PHP_SELF'], 'categorias') ? 'text-violet-500' : 'text-gray-400 hover:text-white' ?>">Categorías</a>
                    <a href="/admin/marcas/index.php"
                        class="text-xs font-black uppercase tracking-widest <?= str_contains($_SERVER['PHP_SELF'], 'marcas') ? 'text-violet-500' : 'text-gray-400 hover:text-white' ?>">Marcas</a>
                    <a href="/admin/pedidos/index.php"
                        class="text-xs font-black uppercase tracking-widest <?= str_contains($_SERVER['PHP_SELF'], 'pedidos') ? 'text-violet-500' : 'text-gray-400 hover:text-white' ?>">Consultas</a>
                    <a href="/admin/informes/index.php"
                        class="text-xs font-black uppercase tracking-widest <?= str_contains($_SERVER['PHP_SELF'], 'informes') ? 'text-violet-500' : 'text-gray-400 hover:text-white' ?>">Informes</a>
                    <a href="/admin/usuarios/index.php"
                        class="text-xs font-black uppercase tracking-widest <?= str_contains($_SERVER['PHP_SELF'], 'usuarios') ? 'text-violet-500' : 'text-gray-400 hover:text-white' ?>">Usuarios</a>
                </nav>
                <div class="h-6 w-px bg-white/10 hidden lg:block"></div>
                <a href="/" target="_blank"
                    class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-white transition-colors">Web
                    ↗</a>
                <a href="/admin/logout.php"
                    class="bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Salir</a>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-7xl w-full mx-auto px-4 py-10">
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <a href="/admin/dashboard.php"
                    class="text-violet-500 font-bold text-xs uppercase tracking-widest hover:text-violet-600 transition-colors flex items-center gap-2 mb-2">
                    <span>←</span> Volver al Menú
                </a>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">
                    <?php echo $adminTitle ?? 'Administración'; ?>
                </h1>
            </div>
            <div id="admin_actions">
                <!-- Espacio para botones de acción como 'Nuevo' -->
            </div>
        </div>