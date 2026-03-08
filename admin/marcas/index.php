<?php
require __DIR__ . '/../../includes/db.php';

$marcas = $pdo->query("SELECT * FROM marcas ORDER BY nombre ASC")->fetchAll();

$adminTitle = 'Gestionar Marcas';
require __DIR__ . '/../includes/header.php';
?>

<div class="mb-6 flex justify-between items-center bg-white p-6 rounded-[32px] border border-gray-100 shadow-sm">
    <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Administra las marcas de tus productos</p>
    <a href="crear.php"
        class="bg-violet-500 text-black px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-violet-600 transition-all shadow-xl shadow-violet-500/20 active:scale-95 flex items-center gap-2">
        <span>➕</span> Nueva Marca
    </a>
</div>

<div class="bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50/50 border-b border-gray-100">
                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">ID</th>
                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Nombre</th>
                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400">Fecha Creado</th>
                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Acciones
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php if (empty($marcas)): ?>
                <tr>
                    <td colspan="4" class="px-8 py-20 text-center">
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest">No hay marcas registradas</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($marcas as $m): ?>
                    <tr class="hover:bg-gray-50/30 transition-colors group">
                        <td class="px-8 py-6 font-black text-gray-400 text-xs">#
                            <?= $m['id'] ?>
                        </td>
                        <td class="px-8 py-6 font-black text-gray-900">
                            <?= htmlspecialchars($m['nombre']) ?>
                        </td>
                        <td class="px-8 py-6 text-xs text-gray-400">
                            <?= date('d/m/Y', strtotime($m['created_at'])) ?>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="editar.php?id=<?= $m['id'] ?>"
                                    class="p-2.5 bg-violet-50 text-violet-600 hover:bg-violet-500 hover:text-white rounded-xl transition-all"
                                    title="Editar">✏️</a>
                                <a href="eliminar.php?id=<?= $m['id'] ?>"
                                    onclick="return confirm('¿Seguro quieres eliminar esta marca?')"
                                    class="p-2.5 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-xl transition-all"
                                    title="Eliminar">🗑️</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>