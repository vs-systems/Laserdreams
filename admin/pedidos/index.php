<?php
require __DIR__ . '/../../includes/db.php';

$pedidos = $pdo->query("SELECT * FROM pedidos ORDER BY created_at DESC")->fetchAll();

$adminTitle = 'Consultas y Pedidos';
require __DIR__ . '/../includes/header.php';
?>

<div class="bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Fecha /
                        Referencia</th>
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Cliente y
                        Contacto</th>
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Resumen de
                        Productos</th>
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Presupuesto
                        Est.</th>
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Estado de
                        Gestión</th>
                    <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">
                        Detalle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php if (empty($pedidos)): ?>
                    <tr>
                        <td colspan="6" class="px-8 py-32 text-center">
                            <span class="text-6xl mb-6 block">📩</span>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-widest">No hay consultas
                                registradas aún</p>
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($pedidos as $p):
                    $items = json_decode($p['carrito'], true) ?: [];
                    $fecha = date('d/m/Y H:i', strtotime($p['created_at']));
                    ?>
                    <tr class="hover:bg-gray-50/30 transition-colors group">
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-violet-600 mb-1">REF:
                                    #<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                <span class="text-xs font-bold text-gray-400"><?= $fecha ?></span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span
                                    class="font-black text-gray-900 tracking-tight transition-colors group-hover:text-violet-600"><?= htmlspecialchars($p['nombre']) ?></span>
                                <span
                                    class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"><?= htmlspecialchars($p['localidad'] ?? 'S/D') ?></span>
                                <span
                                    class="text-[10px] text-gray-400 font-bold uppercase tracking-widest"><?= htmlspecialchars($p['email'] ?? 'WhatsApp Link') ?></span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col gap-1.5">
                                <?php foreach (array_slice($items, 0, 2) as $item): ?>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-5 h-5 flex items-center justify-center bg-gray-900 text-[10px] font-black text-white rounded-md"><?= $item['cantidad'] ?></span>
                                        <span
                                            class="text-[10px] font-bold text-gray-700 uppercase tracking-tight line-clamp-1"><?= htmlspecialchars($item['titulo']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($items) > 2): ?>
                                    <span class="text-[9px] font-black text-violet-600 uppercase tracking-widest">+
                                        <?= count($items) - 2 ?> productos más</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="font-black text-gray-900">$<?= number_format($p['total'], 0, ',', '.') ?></span>
                        </td>
                        <td class="px-8 py-6">
                            <form method="post" action="update_estado.php">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <select name="estado" onchange="this.form.submit()"
                                    class="text-[9px] font-black uppercase tracking-widest px-4 py-2 rounded-xl border-none outline-none focus:ring-4 focus:ring-violet-500/10 shadow-sm cursor-pointer transition-all
                                    <?= $p['estado'] === 'Nuevo' ? 'bg-yellow-100 text-yellow-700' :
                                        ($p['estado'] === 'Cotizado' ? 'bg-orange-500 text-white' :
                                            ($p['estado'] === 'Confirmado' ? 'bg-green-100 text-green-700' :
                                                ($p['estado'] === 'Enviado' ? 'bg-green-600 text-white' :
                                                    ($p['estado'] === 'Cancelado' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600')))) ?>">
                                    <?php foreach (['Nuevo', 'Cotizado', 'Confirmado', 'Enviado', 'Cancelado'] as $e): ?>
                                        <option value="<?= $e ?>" <?= $p['estado'] == $e ? 'selected' : '' ?>><?= $e ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="ver.php?id=<?= $p['id'] ?>"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-violet-500 hover:text-black transition-all shadow-xl shadow-gray-200">
                                    Detalle <span>→</span>
                                </a>
                                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Sistemas'): ?>
                                    <a href="eliminar.php?id=<?= $p['id'] ?>"
                                        onclick="return confirm('¿Eliminar esta consulta? Esta acción no se puede deshacer.')"
                                        class="p-2.5 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-xl transition-all"
                                        title="Eliminar">
                                        🗑️
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>