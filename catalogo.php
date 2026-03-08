<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/header.php';

/* ========================= */
/* Helper seguro PHP 8       */
/* ========================= */
function e($v)
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}

/* ========================= */
/* FILTROS Y PAGINACIÓN      */
/* ========================= */

$busqueda = $_GET['q'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$marca = $_GET['marca'] ?? '';

$porPagina = 12;
$pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($pagina < 1)
    $pagina = 1;

$offset = ($pagina - 1) * $porPagina;

/* ========================= */
/* CONTAR TOTAL PRODUCTOS    */
/* ========================= */

$sqlCount = "SELECT COUNT(*) FROM productos p WHERE p.activo = 1";
$paramsCount = [];

if ($busqueda !== '') {
    $sqlCount .= " AND (p.titulo LIKE ? OR p.descripcion LIKE ? OR p.codigo LIKE ?)";
    $like = "%$busqueda%";
    $paramsCount = [$like, $like, $like];
}

if ($categoria !== '') {
    $sqlCount .= " AND p.categoria_id = ?";
    $paramsCount[] = $categoria;
}

if ($marca !== '') {
    $sqlCount .= " AND p.marca_id = ?";
    $paramsCount[] = $marca;
}

$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($paramsCount);
$totalProductos = $stmtCount->fetchColumn();
$totalPaginas = ceil($totalProductos / $porPagina);

/* ========================= */
/* CONSULTA PRINCIPAL        */
/* ========================= */

$sql = "
SELECT p.*, p.foto_principal AS imagen_principal, c.nombre as categoria_nombre, m.nombre as marca_nombre,
       m.tipo_dolar, m.recargo_dolar_pesos, m.recargo_bancario_porcentaje
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN marcas m ON p.marca_id = m.id
WHERE p.activo = 1
";

$params = [];

if ($busqueda !== '') {
    $sql .= " AND (p.titulo LIKE ? OR p.descripcion LIKE ? OR p.codigo LIKE ?)";
    $like = "%$busqueda%";
    $params = [$like, $like, $like];
}

if ($categoria !== '') {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoria;
}

if ($marca !== '') {
    $sql .= " AND p.marca_id = ?";
    $params[] = $marca;
}

$sql .= " ORDER BY p.es_nuevo DESC, p.created_at DESC LIMIT $porPagina OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll();
$marcas = $pdo->query("SELECT id, nombre FROM marcas ORDER BY nombre")->fetchAll();
?>


<?php if (isset($is_home) && $is_home && empty($busqueda) && empty($categoria) && $pagina == 1): ?>
    <!-- Sección Hero con Video de Fondo (Estilo Sanyi) -->
    <div class="relative w-full h-[70vh] md:h-[80vh] overflow-hidden bg-black flex items-center justify-center">
        <!-- El video debe existir en /assets/bg-home.mp4 o reemplazarse por una URL final -->
        <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover opacity-40">
            <source src="/assets/bg-home.mp4" type="video/mp4">
        </video>

        <div class="relative z-10 text-center px-6 max-w-4xl mx-auto flex flex-col items-center gap-6">
            <h2 class="text-sm font-black text-red-600 uppercase tracking-[0.4em] mb-2 animate-pulse">BIENVENIDO A</h2>
            <h1 class="text-5xl md:text-8xl font-black text-white tracking-tighter drop-shadow-2xl">
                LASER<span class="text-red-600">DREAMS</span>
            </h1>
            <p
                class="text-lg md:text-xl text-gray-300 font-medium tracking-wide mt-4 max-w-2xl text-center leading-relaxed">
                Equipamiento técnico e iluminación profesional para DJs, Eventos y Teatros.
            </p>
            <div class="mt-8 flex gap-4">
                <a href="#catalogo-container"
                    class="bg-red-600 hover:bg-white hover:text-red-600 text-white px-8 py-4 rounded-full font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-red-600/20 active:scale-95">
                    Ver Catálogo
                </a>
                <a href="https://wa.me/5492235772165" target="_blank"
                    class="bg-white/10 backdrop-blur border border-white/20 text-white hover:bg-white/20 px-8 py-4 rounded-full font-black text-xs uppercase tracking-widest transition-all active:scale-95">
                    Asesoría
                </a>
            </div>
        </div>

        <!-- Gradiente inferior para transición suave -->
        <div
            class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-[#fbfbfb] to-transparent pointer-events-none">
        </div>
    </div>
<?php endif; ?>

<div id="catalogo-container" class="max-w-7xl mx-auto px-4 py-12">

    <!-- Header Sección -->
    <div class="mb-12 text-center">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Nuestro Catálogo</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-6">Bienvenidos a Laserdreams, consulta por descuentos por
            compra mayorista. <a href="https://wa.me/5492235772165" target="_blank"
                class="text-violet-600 font-bold hover:underline">+5492235772165</a>.</p>
        <a href="/api/descargar_lista_precios.php" target="_blank"
            class="inline-flex items-center gap-2 bg-gray-900 text-white px-8 py-4 rounded-full font-black text-xs uppercase tracking-widest hover:bg-violet-600 transition-all shadow-xl shadow-gray-900/20 active:scale-95">
            📄 Descargar Lista de Precios
        </a>
    </div>

    <!-- Filtros -->
    <div
        class="flex flex-col md:flex-row gap-4 mb-10 items-center justify-between bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="relative w-full md:w-96">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
            <input type="text" id="buscador"
                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all outline-none"
                placeholder="¿Qué estás buscando?..." value="<?= e($busqueda) ?>">
        </div>

        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
            <div class="w-full md:w-48">
                <select id="filtroMarca"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-violet-500 transition-all outline-none appearance-none bg-no-repeat bg-[right_1rem_center] bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')]">
                    <option value="">Todas las marcas</option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= $marca == $m['id'] ? 'selected' : '' ?>>
                            <?= e($m['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="w-full md:w-64">
                <select id="filtroCategoria"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-violet-500 transition-all outline-none appearance-none bg-no-repeat bg-[right_1rem_center] bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')]">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $categoria == $c['id'] ? 'selected' : '' ?>>
                            <?= e($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Grid de Productos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">

        <?php if (empty($productos)): ?>
            <div class="col-span-full py-20 text-center">
                <div class="text-6xl mb-4">🪑</div>
                <p class="text-xl text-gray-500">No se encontraron productos que coincidan con tu búsqueda.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($productos as $p): ?>

            <div
                class="group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 flex flex-col h-full <?= empty($p['en_stock']) && isset($p['en_stock']) ? 'opacity-70 grayscale-[50%]' : '' ?>">

                <div class="aspect-[4/5] overflow-hidden relative">
                    <?php if (!empty($p['imagen_principal'])): ?>
                        <img src="/uploads/productos/<?= e($p['imagen_principal']) ?>" alt="<?= e($p['titulo']) ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    <?php else: ?>
                        <div class="w-full h-full bg-gray-50 flex items-center justify-center p-8">
                            <img src="/assets/img/logo.png" class="max-w-full max-h-full object-contain opacity-20">
                        </div>
                    <?php endif; ?>

                    <div class="absolute inset-0 bg-black/5 group-hover:bg-black/0 transition-colors"></div>
                </div>

                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex flex-wrap gap-1 mb-2">
                        <?php if (isset($p['en_stock']) && empty($p['en_stock'])): ?>
                            <span
                                class="bg-gray-800 text-white text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded shadow-sm">Sin
                                Stock</span>
                        <?php else: ?>
                            <span
                                class="bg-green-600 text-white text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded shadow-sm">En
                                Stock</span>
                        <?php endif; ?>

                        <?php if (!empty($p['es_usado'])): ?>
                            <span
                                class="bg-amber-600 text-white text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded shadow-sm">Usado</span>
                        <?php endif; ?>

                        <?php if (!empty($p['es_novedad'])): ?>
                            <span
                                class="bg-blue-600 text-white text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded shadow-sm">Nuevo</span>
                        <?php endif; ?>
                        <?php if (!empty($p['es_oferta'])): ?>
                            <span
                                class="bg-red-600 text-white text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded shadow-sm">Oferta</span>
                        <?php endif; ?>
                        <?php if (!empty($p['es_destacado'])): ?>
                            <span
                                class="bg-violet-500 text-black text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded shadow-sm">Destacado</span>
                        <?php endif; ?>
                        <?php if (!empty($p['es_pocas_unidades'])): ?>
                            <span
                                class="bg-gray-900 text-white text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded shadow-sm">Pocas
                                Unidades</span>
                        <?php endif; ?>
                    </div>
                    <h3
                        class="text-lg font-bold text-gray-900 group-hover:text-violet-600 transition-colors mb-2 line-clamp-2">
                        <?= e($p['titulo']) ?>
                    </h3>

                    <div class="mt-auto">
                        <?php
                        $precio_final = calcular_precio_final(
                            $p['precio_venta_usd'],
                            $p['tipo_dolar'] ?? 'blue',
                            $p['recargo_dolar_pesos'] ?? 0,
                            $p['recargo_bancario_porcentaje'] ?? 0
                        );
                        ?>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 truncate">
                            <?= htmlspecialchars($p['marca_nombre'] ?? 'Genérico') ?>
                        </div>
                        <div class="text-2xl font-black text-green-700 mb-4">
                            $<?= number_format($precio_final, 0, ',', '.') ?>
                        </div>

                        <div class="flex gap-2">
                            <a href="/producto.php?id=<?= (int) $p['id'] ?>"
                                class="w-1/2 bg-red-900 text-white py-2.5 rounded-xl font-bold hover:bg-red-950 transition-all text-xs flex items-center justify-center shadow-md">
                                + Info
                            </a>
                            <button
                                class="w-1/2 bg-red-600 text-white py-2.5 rounded-xl font-bold hover:bg-red-500 transition-all duration-300 transform active:scale-95 flex items-center justify-center gap-1 text-xs shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                                <?= (isset($p['en_stock']) && empty($p['en_stock'])) ? 'disabled' : '' ?>
                                onclick="event.preventDefault(); event.stopPropagation(); addToCartFromButton(this);"
                                data-id="<?= (int) $p['id'] ?>" data-titulo="<?= e($p['titulo']) ?>"
                                data-precio="<?= $precio_final ?>"
                                data-imagen="<?= !empty($p['imagen_principal']) ? '/uploads/productos/' . $p['imagen_principal'] : '' ?>"
                                data-url="https://laserdreams.com.ar/producto.php?id=<?= (int) $p['id'] ?>">
                                🛒 Agregar
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        <?php endforeach; ?>

    </div>

    <!-- Paginación -->
    <?php if ($totalPaginas > 1): ?>
        <div class="mt-16 flex justify-center items-center gap-4">
            <?php if ($pagina > 1): ?>
                <a href="?page=<?= $pagina - 1 ?>&q=<?= urlencode($busqueda) ?>&categoria=<?= urlencode($categoria) ?>&marca=<?= urlencode($marca) ?>"
                    class="p-3 rounded-full bg-white border border-gray-200 hover:bg-gray-50 transition-all shadow-sm">
                    <span class="block w-6 h-6">←</span>
                </a>
            <?php endif; ?>

            <div class="bg-white px-6 py-3 rounded-2xl border border-gray-200 shadow-sm font-medium">
                Página <span class="text-violet-600"><?= $pagina ?></span> de <?= $totalPaginas ?>
            </div>

            <?php if ($pagina < $totalPaginas): ?>
                <a href="?page=<?= $pagina + 1 ?>&q=<?= urlencode($busqueda) ?>&categoria=<?= urlencode($categoria) ?>&marca=<?= urlencode($marca) ?>"
                    class="p-3 rounded-full bg-white border border-gray-200 hover:bg-gray-50 transition-all shadow-sm">
                    <span class="block w-6 h-6">→</span>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<script>
    function refreshFilters() {
        const cat = document.getElementById('filtroCategoria').value;
        const marc = document.getElementById('filtroMarca').value;
        const busq = document.getElementById('buscador').value;
        window.location.href = `?categoria=${cat}&marca=${marc}&q=${busq}`;
    }

    document.getElementById('filtroCategoria').addEventListener('change', refreshFilters);
    document.getElementById('filtroMarca').addEventListener('change', refreshFilters);

    let searchTimeout;
    document.getElementById('buscador').addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(refreshFilters, 500);
    });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>