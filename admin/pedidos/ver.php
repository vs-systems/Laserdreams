<?php
require __DIR__ . '/../../includes/db.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->execute([$id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: index.php');
    exit;
}

$items = json_decode($pedido['carrito'], true) ?: [];

$adminTitle = 'Detalle de Consulta #' . $id;
require __DIR__ . '/../includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
    <!-- Información del Cliente -->
    <div class="lg:col-span-1 space-y-8">
        <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm space-y-8">
            <h2 class="text-xl font-black text-gray-900 tracking-tight flex items-center gap-3">
                <span class="text-2xl">👤</span> Datos del Cliente
            </h2>

            <div class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Nombre y
                        Apellido</label>
                    <p class="font-black text-gray-900 text-xl leading-tight"><?= htmlspecialchars($pedido['nombre']) ?>
                    </p>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Email de
                        Contacto</label>
                    <p class="font-bold text-gray-600"><?= htmlspecialchars($pedido['email'] ?: 'No proporcionado') ?>
                    </p>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Localidad /
                        Ciudad</label>
                    <p class="font-black text-gray-900 text-lg leading-tight">
                        <?= htmlspecialchars($pedido['localidad'] ?: 'No proporcionada') ?>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Fecha y
                        Hora</label>
                    <p class="font-bold text-gray-600"><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></p>
                </div>

                <div class="pt-4">
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', get_ajuste('whatsapp_nro')) ?>?text=Hola%20<?= urlencode($pedido['nombre']) ?>%2C%20te%20contacto%20desde%20Laserdreams%20por%20tu%20consulta%20del%20<?= date('d/m', strtotime($pedido['created_at'])) ?>"
                        target="_blank"
                        class="w-full bg-green-500 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-green-600 transition-all shadow-xl shadow-green-500/20 flex items-center justify-center gap-3">
                        <span class="text-lg">💬</span> Contactar por WhatsApp
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 p-10 rounded-[40px] shadow-2xl space-y-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-10 text-6xl">💰</div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 relative z-10">Presupuesto
                Estimado</p>
            <div class="flex flex-col relative z-10">
                <span
                    class="text-4xl font-black text-violet-500 tracking-tighter">$<?= number_format($pedido['total'], 0, ',', '.') ?></span>
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mt-2">Sujeto a cambios</span>
            </div>
        </div>

        <div class="pt-4">
            <a href="eliminar.php?id=<?= $pedido['id'] ?>"
                onclick="return confirm('¿Eliminar esta consulta permanentemente?')"
                class="w-full bg-red-50 text-red-500 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all text-center block">
                ⚠️ Eliminar Registro
            </a>
        </div>
    </div>

    <!-- Desglose de Productos -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-[40px] border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-8 py-6 bg-gray-50/50 border-b border-gray-100">
                <h2 class="text-lg font-black text-gray-900 tracking-tight uppercase">Productos en la Consulta</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <?php foreach ($items as $i): ?>
                    <div class="p-8 flex items-center gap-8 hover:bg-gray-50/50 transition-colors group">
                        <div
                            class="w-24 h-24 rounded-2xl overflow-hidden shadow-md border border-gray-100 group-hover:scale-105 transition-transform shrink-0">
                            <img src="<?= htmlspecialchars($i['imagen']) ?>" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-grow">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-black text-gray-900 text-lg"><?= htmlspecialchars($i['titulo']) ?></h3>
                                <span class="text-xs font-bold text-gray-400">x<?= $i['cantidad'] ?></span>
                            </div>
                            <div class="flex gap-4">
                                <?php if (!empty($i['color'])): ?>
                                    <span
                                        class="text-[10px] font-black uppercase tracking-widest text-violet-600 bg-violet-50 px-3 py-1 rounded-lg border border-violet-100">C:
                                        <?= htmlspecialchars($i['color']) ?></span>
                                <?php endif; ?>
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-gray-400 bg-gray-50 px-3 py-1 rounded-lg border border-gray-100">Precio
                                    Ref: $<?= number_format($i['precio'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>