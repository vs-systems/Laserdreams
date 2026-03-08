<?php
require __DIR__ . '/../../includes/db.php';

// ==========================
// Filtros
// ==========================
$q = trim($_GET['q'] ?? '');
$categoria_id = $_GET['categoria_id'] ?? '';
$marca_id = $_GET['marca_id'] ?? '';

$params = [];
$where = " WHERE 1=1 ";

if ($q !== '') {
    $where .= " AND (p.titulo LIKE ? OR p.codigo LIKE ? OR c.nombre LIKE ? OR m.nombre LIKE ?) ";
    $like = "%$q%";
    $params = [$like, $like, $like, $like];
}

if ($categoria_id !== '') {
    $where .= " AND p.categoria_id = ? ";
    $params[] = $categoria_id;
}

if ($marca_id !== '') {
    $where .= " AND p.marca_id = ? ";
    $params[] = $marca_id;
}

// ==========================
// Traer productos + relaciones
// ==========================
$sql = "
SELECT 
    p.id,
    p.codigo,
    p.titulo,
    c.nombre AS categoria,
    m.nombre AS marca,
    p.precio_venta_usd,
    p.tipo_bulto,
    p.unidades_por_bulto,
    p.es_oferta,
    p.es_nuevo,
    p.es_destacado,
    p.activo
FROM productos p
LEFT JOIN categorias c ON c.id = p.categoria_id
LEFT JOIN marcas m ON m.id = p.marca_id
$where
ORDER BY p.activo DESC, p.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll();
$marcas = $pdo->query("SELECT id, nombre FROM marcas ORDER BY nombre")->fetchAll();

$adminTitle = 'Catálogo de Productos';
require __DIR__ . '/../includes/header.php';
?>

<div
    class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-6 rounded-[32px] border border-gray-100 shadow-sm">
    <form action="index.php" method="GET" id="filterForm" class="flex-grow flex flex-col md:flex-row gap-4 w-full">
        <div class="relative flex-grow">
            <span
                class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-violet-500 transition-colors">🔍</span>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                placeholder="Buscar por código, título, categoría o marca..."
                class="w-full pl-14 pr-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-all outline-none font-bold text-gray-900">
        </div>

        <select name="marca_id" onchange="this.form.submit()"
            class="px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 outline-none font-bold text-gray-700 appearance-none cursor-pointer">
            <option value="">Todas las Marcas</option>
            <?php foreach ($marcas as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $marca_id == $m['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="categoria_id" onchange="this.form.submit()"
            class="px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 outline-none font-bold text-gray-700 appearance-none cursor-pointer">
            <option value="">Todas las Categorías</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $categoria_id == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="flex gap-4 w-full md:w-auto justify-end" id="admin_actions_injector">
        <a href="importar.php"
            class="bg-gray-100 text-gray-600 px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-900 hover:text-white transition-all flex items-center gap-2">
            <span>📥</span> Importar CSV
        </a>
        <a href="crear.php"
            class="bg-violet-500 text-black px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-900 hover:text-white transition-all shadow-xl shadow-violet-500/20 flex items-center gap-2">
            <span>➕</span> Nuevo Producto
        </a>
    </div>
</div>

<?php if (isset($_GET['ok'])): ?>
    <div
        class="bg-green-50 text-green-600 p-5 rounded-3xl text-sm font-bold mb-8 border border-green-100 flex items-center gap-3 animate-pulse">
        <span>✅</span> Producto gestionado correctamente.
        <?php if (isset($_GET['count'])): ?>
            (<?= (int) $_GET['count'] ?> importados
            <?php if (isset($_GET['errors']) && $_GET['errors'] > 0): ?>
                , <span class="text-red-500"><?= (int) $_GET['errors'] ?> con errores</span>
            <?php endif; ?>)
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Código</th>
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Producto</th>
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Marca</th>
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Categoría</th>
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Datos Venta
                    </th>
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Etiquetas</th>
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">
                        Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="6" class="px-8 py-32 text-center">
                            <span class="text-6xl mb-6 block">🪑</span>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-widest">No hay productos en el
                                catálogo</p>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($productos as $p): ?>
                    <tr class="hover:bg-gray-50/30 transition-colors group">
                        <td class="px-8 py-6">
                            <span
                                class="font-mono text-xs font-bold text-violet-600 bg-violet-50 px-3 py-1.5 rounded-lg border border-violet-100">
                                <?= htmlspecialchars($p['codigo'] ?: 'S/C') ?>
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span
                                    class="font-black text-gray-900 tracking-tight text-base group-hover:text-violet-600 transition-colors">
                                    <?= htmlspecialchars($p['titulo']) ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span
                                class="text-xs font-black text-gray-900"><?= htmlspecialchars($p['marca'] ?: 'S/D') ?></span>
                        </td>
                        <td class="px-8 py-6">
                            <span
                                class="text-xs font-bold text-gray-500 italic"><?= htmlspecialchars($p['categoria'] ?: 'Sin categoría') ?></span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col gap-1">
                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">
                                    <span class="text-violet-500">USD:</span>
                                    $<?= number_format($p['precio_venta_usd'], 2) ?>
                                </div>
                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">
                                    <span class="text-blue-500">PACK:</span>
                                    <?= htmlspecialchars($p['unidades_por_bulto']) ?>u. en
                                    <?= htmlspecialchars($p['tipo_bulto']) ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-wrap gap-2">
                                <?php if ($p['es_oferta']): ?>
                                    <span
                                        class="bg-red-500 text-white text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-md">Oferta</span>
                                <?php endif; ?>
                                <?php if ($p['es_nuevo']): ?>
                                    <span
                                        class="bg-blue-500 text-white text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-md">Nuevo</span>
                                <?php endif; ?>
                                <?php if ($p['es_destacado']): ?>
                                    <span
                                        class="bg-violet-500 text-black text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-md">Elite</span>
                                <?php endif; ?>
                                <?php if (!$p['activo']): ?>
                                    <span
                                        class="bg-gray-300 text-gray-700 text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-md">Oculto</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div
                                class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <a href="editar.php?id=<?= $p['id'] ?>"
                                    class="p-2.5 bg-gray-50 hover:bg-violet-500 hover:text-black text-gray-400 rounded-xl transition-all shadow-sm flex items-center gap-2 text-[10px] font-black uppercase tracking-widest"
                                    title="Editar producto">
                                    <span>✏️</span> <span class="hidden xl:inline">Editar</span>
                                </a>

                                <?php if ($p['activo']): ?>
                                    <a href="eliminar.php?id=<?= $p['id'] ?>&modo=ocultar"
                                        class="p-2.5 bg-gray-50 hover:bg-gray-900 hover:text-white text-gray-400 rounded-xl transition-all shadow-sm flex items-center gap-2 text-[10px] font-black uppercase tracking-widest"
                                        title="Ocultar de la web">
                                        <span>👁️</span> <span class="hidden xl:inline">Ocultar</span>
                                    </a>
                                <?php else: ?>
                                    <a href="activar.php?id=<?= $p['id'] ?>"
                                        class="p-2.5 bg-green-50 hover:bg-green-600 hover:text-white text-green-400 rounded-xl transition-all shadow-sm flex items-center gap-2 text-[10px] font-black uppercase tracking-widest"
                                        title="Mostrar en la web">
                                        <span>✨</span> <span class="hidden xl:inline">Mostrar</span>
                                    </a>
                                <?php endif; ?>

                                <a href="eliminar.php?id=<?= $p['id'] ?>&modo=fisico"
                                    onclick="return confirm('⚠️ ATENCIÓN: Se eliminará para siempre junto con todas sus fotos. ¿Continuar?')"
                                    class="p-2.5 bg-gray-50 hover:bg-red-600 hover:text-white text-gray-400 rounded-xl transition-all shadow-sm flex items-center gap-2 text-[10px] font-black uppercase tracking-widest"
                                    title="Eliminar PERMANENTE">
                                    <span>🗑️</span> <span class="hidden xl:inline">Eliminar</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const actionsContainer = document.getElementById('admin_actions');
    const injector = document.getElementById('admin_actions_injector');
    if (actionsContainer && injector) {
        // Mover todos los botones del inyector al contenedor de acciones del header
        const buttons = injector.querySelectorAll('a');
        buttons.forEach(btn => {
            actionsContainer.appendChild(btn.cloneNode(true));
        });
        injector.remove();
    }
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>