<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/db.php';

function e($v)
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header('Location: index.php');
    exit;
}

$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll();

$adminTitle = 'Editar: ' . $producto['titulo'];
require __DIR__ . '/../includes/header.php';

$cotizacion_js = $GLOBALS['cotizacion_aplicada'] ?? 0;
?>

<form action="actualizar.php" method="post" enctype="multipart/form-data"
    class="grid grid-cols-1 lg:grid-cols-3 gap-10">
    <input type="hidden" name="id" value="<?= (int) $producto['id'] ?>">

    <!-- Columna Principal: Datos Teóricos -->
    <div class="lg:col-span-2 space-y-8">
        <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Código
                        Interno</label>
                    <input type="text" name="codigo" value="<?= e($producto['codigo']) ?>" required
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-violet-600">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Tipo
                        Bulto</label>
                    <select name="tipo_bulto"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900 appearance-none cursor-pointer">
                        <option value="Caja de Cartón" <?= $producto['tipo_bulto'] === 'Caja de Cartón' ? 'selected' : '' ?>>Caja de Cartón</option>
                        <option value="Anvil Flight Case" <?= $producto['tipo_bulto'] === 'Anvil Flight Case' ? 'selected' : '' ?>>Anvil Flight Case</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Unidades x
                        Bulto</label>
                    <input type="number" name="unidades_por_bulto" required min="1"
                        value="<?= (int) $producto['unidades_por_bulto'] ?>"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Título</label>
                <input type="text" name="titulo" value="<?= e($producto['titulo']) ?>" required
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900 text-lg">
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Marca</label>
                <input type="text" name="marca" value="<?= e($producto['marca'] ?? '') ?>"
                    placeholder="Ej. BEAM, SANYI, genérico..."
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900 text-lg">
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Descripción
                    Larga Profesional</label>
                <textarea name="descripcion" rows="8"
                    class="w-full px-6 py-5 rounded-3xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-medium text-gray-600 leading-relaxed"><?= e($producto['descripcion']) ?></textarea>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Manual Técnico
                    (PDF) - Opcional</label>
                <?php if (!empty($producto['manual_tecnico'])): ?>
                    <div class="mb-4 p-4 bg-red-50 rounded-2xl border border-red-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📄</span>
                            <div>
                                <p class="text-sm font-bold text-red-900">PDF Actual Cargado</p>
                                <a href="/uploads/productos/<?= e($producto['manual_tecnico']) ?>" target="_blank"
                                    class="text-xs text-red-600 hover:underline">Ver archivo</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" name="manual_tecnico" accept="application/pdf"
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900">
                <p class="text-[10px] text-gray-400 mt-2 uppercase tracking-widest">Subir un nuevo archivo reemplazará
                    al anterior.</p>
            </div>
        </div>

        <!-- Precios y Calculadora -->
        <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm space-y-8">
            <h3 class="text-lg font-black text-gray-900 tracking-tight border-b pb-4">Lógica Financiera</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Costo
                        Compra (USD)</label>
                    <input type="number" name="costo_compra" id="i_costo" step="0.01" min="0"
                        value="<?= (float) $producto['costo_compra'] ?>" required
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Margen
                        (%)</label>
                    <input type="number" name="margen_porcentaje" id="i_margen" step="0.01" min="0"
                        value="<?= (float) $producto['margen_porcentaje'] ?>" required
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Precio
                        Venta (USD)</label>
                    <input type="number" name="precio_venta_usd" id="i_venta" step="0.01" min="0"
                        value="<?= (float) $producto['precio_venta_usd'] ?>" required
                        class="w-full px-6 py-4 rounded-2xl bg-violet-100 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-violet-900">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 bg-gray-50 p-6 rounded-[24px]">
                <div>
                    <span class="block text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Coti.
                        Aplicada</span>
                    <!-- Este valor es estático visualmente aquí, pero se calcula con JS también -->
                    <span class="text-lg font-black text-blue-600"
                        id="l_coti">$<?= number_format($cotizacion_js, 2, ',', '.') ?></span>
                </div>
                <div>
                    <span class="block text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Subtotal
                        (ARS)</span>
                    <span class="text-lg font-black text-gray-700" id="l_sub">$0,00</span>
                </div>
                <div>
                    <span class="block text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Recargo 5%
                        (ARS)</span>
                    <span class="text-lg font-black text-gray-700" id="l_rec">$0,00</span>
                </div>
                <div>
                    <span class="block text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Val. Neto
                        Final (ARS)</span>
                    <span class="text-xl font-black text-green-600" id="l_neto">$0,00</span>
                </div>
            </div>
        </div>

        <!-- Galería Multimedia -->
        <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Galería Multimedia y Manuales</h2>
                <span class="text-[10px] font-black uppercase tracking-widest text-violet-500">Mover para ordenar</span>
            </div>

            <div id="dropzone"
                class="border-4 border-dashed border-gray-100 rounded-[32px] p-12 text-center group hover:border-violet-500/30 transition-all cursor-pointer bg-gray-50/50 mb-10">
                <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">📸</div>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">Arrastrá imágenes, videos o PDF, o
                    hacé click para subir</p>
                <!-- Archivos PDF, MP4, y fotos permitidos -->
                <input type="file" id="fileInput" accept="image/*,video/mp4,application/pdf" multiple hidden>
            </div>

            <div id="galeria" class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Se carga vía AJAX -->
            </div>
        </div>
    </div>

    <!-- Columna Lateral: Clasificación y Acciones -->
    <div class="space-y-8">
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm space-y-10">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Familia /
                    Categoría</label>
                <select name="categoria_id"
                    class="w-full px-5 py-4 rounded-2xl bg-gray-50 font-black text-xs uppercase tracking-widest outline-none border-none ring-0">
                    <option value="">(Ninguna)</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $producto['categoria_id'] ? 'selected' : '' ?>>
                            <?= e($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label
                    class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-6 border-b border-gray-50 pb-2">Etiquetas
                    de Estado</label>
                <div class="space-y-4">
                    <label class="flex items-center gap-4 cursor-pointer group">
                        <input type="checkbox" name="activo" value="1" <?= !empty($producto['activo']) ? 'checked' : '' ?>
                            class="w-6 h-6 rounded-lg border-2 border-gray-100 checked:bg-green-500 checked:border-green-500 transition-all outline-none appearance-none cursor-pointer">
                        <span
                            class="text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-green-500 transition-colors">✅
                            Activo (Visible)</span>
                    </label>
                    <label class="flex items-center gap-4 cursor-pointer group">
                        <input type="checkbox" name="es_oferta" value="1" <?= !empty($producto['es_oferta']) ? 'checked' : '' ?>
                            class="w-6 h-6 rounded-lg border-2 border-gray-100 checked:bg-violet-500 checked:border-violet-500 transition-all outline-none appearance-none cursor-pointer">
                        <span
                            class="text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-violet-500 transition-colors">🔥
                            Oferta Especial</span>
                    </label>
                    <label class="flex items-center gap-4 cursor-pointer group">
                        <input type="checkbox" name="es_nuevo" value="1" <?= !empty($producto['es_nuevo']) ? 'checked' : '' ?>
                            class="w-6 h-6 rounded-lg border-2 border-gray-100 checked:bg-blue-500 checked:border-blue-500 transition-all outline-none appearance-none cursor-pointer">
                        <span
                            class="text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-blue-500 transition-colors">✨
                            Nuevo Ingreso</span>
                    </label>
                    <label class="flex items-center gap-4 cursor-pointer group">
                        <input type="checkbox" name="es_destacado" value="1" <?= !empty($producto['es_destacado']) ? 'checked' : '' ?>
                            class="w-6 h-6 rounded-lg border-2 border-gray-100 checked:bg-orange-500 checked:border-orange-500 transition-all outline-none appearance-none cursor-pointer">
                        <span
                            class="text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-orange-500 transition-colors">💎
                            Destacado</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 p-8 rounded-[32px] shadow-2xl space-y-4 sticky top-32">
            <button type="submit"
                class="w-full bg-violet-500 text-black py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-white transition-all shadow-xl shadow-violet-500/20 active:scale-95 flex items-center justify-center gap-3">
                <span>💾</span> Guardar Cambios
            </button>
            <a href="index.php"
                class="w-full bg-white/10 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-white/20 transition-all flex items-center justify-center gap-3">
                Volver
            </a>
        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    const cotizacion = <?= json_encode($cotizacion_js) ?>;
    const iCosto = document.getElementById('i_costo');
    const iMargen = document.getElementById('i_margen');
    const iVenta = document.getElementById('i_venta');

    const lSub = document.getElementById('l_sub');
    const lRec = document.getElementById('l_rec');
    const lNeto = document.getElementById('l_neto');

    function formatPesos(val) {
        return '$' + val.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalcularPreciosPesos() {
        let pv = parseFloat(iVenta.value) || 0;
        let subtotal = pv * cotizacion;
        let recargo = subtotal * 0.05;
        let neto = subtotal + recargo;

        lSub.textContent = formatPesos(subtotal);
        lRec.textContent = formatPesos(recargo);
        lNeto.textContent = formatPesos(neto);
    }

    function onCostoMargenChange() {
        let c = parseFloat(iCosto.value) || 0;
        let m = parseFloat(iMargen.value) || 0;
        let pv = c * (1 + (m / 100));
        iVenta.value = pv.toFixed(2);
        recalcularPreciosPesos();
    }

    function onVentaChange() {
        let c = parseFloat(iCosto.value) || 0;
        let pv = parseFloat(iVenta.value) || 0;
        if (c > 0) {
            let m = ((pv / c) - 1) * 100;
            iMargen.value = m.toFixed(2);
        } else {
            iMargen.value = "0.00";
        }
        recalcularPreciosPesos();
    }

    iCosto.addEventListener('input', onCostoMargenChange);
    iMargen.addEventListener('input', onCostoMargenChange);
    iVenta.addEventListener('input', onVentaChange);

    // Inicializar visualmente en edición
    recalcularPreciosPesos();

    // Galería
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const galeria = document.getElementById('galeria');
    const productoId = <?= (int) $producto['id'] ?>;

    dropzone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', uploadFiles);

    dropzone.addEventListener('dragover', e => {
        e.preventDefault();
        dropzone.classList.add('border-violet-500', 'bg-violet-50/50');
    });

    dropzone.addEventListener('dragleave', e => {
        dropzone.classList.remove('border-violet-500', 'bg-violet-50/50');
    });

    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('border-violet-500', 'bg-violet-50/50');
        fileInput.files = e.dataTransfer.files;
        uploadFiles();
    });

    async function uploadFiles() {
        const files = Array.from(fileInput.files);
        for (let file of files) {
            let formData = new FormData();
            formData.append('file', file);
            formData.append('producto_id', productoId);

            try {
                const r = await fetch('subir_media.php', { method: 'POST', body: formData });
                const data = await r.json();
                if (data.success) loadGaleria();
            } catch (e) { console.error("Error al subir", e); }
        }
    }

    function loadGaleria() {
        fetch('obtener_media.php?id=' + productoId)
            .then(r => r.text())
            .then(html => galeria.innerHTML = html);
    }

    new Sortable(galeria, {
        animation: 250,
        ghostClass: 'bg-violet-50',
        onEnd: function () {
            let orden = [];
            galeria.querySelectorAll('.card-media').forEach((el) => {
                orden.push(el.dataset.id);
            });

            fetch('ordenar_media.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orden })
            });
        }
    });

    loadGaleria();

    async function eliminarMedia(columna) {
        if (!confirm("¿Eliminar este archivo?")) return;
        const r = await fetch('eliminar_media.php?id=' + productoId + '&columna=' + columna);
        const d = await r.json();
        if (d.success) loadGaleria();
    }

    // Confirmar antes de salir si hay cambios
    let formCambiado = false;
    document.querySelector('form').addEventListener('change', () => formCambiado = true);
    window.addEventListener('beforeunload', (e) => {
        if (formCambiado) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    document.querySelector('form').addEventListener('submit', () => formCambiado = false);

</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>