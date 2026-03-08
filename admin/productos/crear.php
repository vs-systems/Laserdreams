<?php
require __DIR__ . '/../../includes/db.php';

/* Datos auxiliares para las selecciones */
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll();
$marcas = $pdo->query("SELECT id, nombre, tipo_dolar, recargo_dolar_pesos, recargo_bancario_porcentaje FROM marcas ORDER BY nombre")->fetchAll();

$adminTitle = 'Nuevo Producto';
require __DIR__ . '/../includes/header.php';

// Pasar a JS la cotización base (ambas)
$dolar_blue = $GLOBALS['dolar_blue_base'] ?? 0;
$dolar_oficial = $GLOBALS['dolar_oficial_base'] ?? 0;
?>

<form action="guardar.php" method="post" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-10">

    <!-- Columna Principal -->
    <div class="lg:col-span-2 space-y-8">
        <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Código
                        Interno</label>
                    <input type="text" name="codigo" required
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-violet-600">
                </div>
                <!-- Tipo de Bulto -->
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Tipo
                        Bulto</label>
                    <select name="tipo_bulto"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900 appearance-none cursor-pointer">
                        <option value="Caja de Cartón">Caja de Cartón</option>
                        <option value="Anvil Flight Case">Anvil Flight Case</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Unidades x
                        Bulto</label>
                    <input type="number" name="unidades_por_bulto" required min="1" value="1"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900">
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Título del
                    Producto</label>
                <input type="text" name="titulo" required
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900 text-lg">
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Marca</label>
                <select name="marca_id" id="marca_select"
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900 text-lg appearance-none cursor-pointer">
                    <option value="" data-tipo="blue" data-pesos="15" data-porc="5">(Sin Marca) - Usa Blue + $15 + 5%
                    </option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?= $m['id'] ?>" data-tipo="<?= htmlspecialchars($m['tipo_dolar']) ?>"
                            data-pesos="<?= htmlspecialchars($m['recargo_dolar_pesos']) ?>"
                            data-porc="<?= htmlspecialchars($m['recargo_bancario_porcentaje']) ?>">
                            <?= htmlspecialchars($m['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Descripción
                    Larga Profesional</label>
                <textarea name="descripcion" rows="8"
                    class="w-full px-6 py-5 rounded-3xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-medium text-gray-600 leading-relaxed"></textarea>
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Subir Manual
                    Técnico (PDF) - Opcional</label>
                <input type="file" name="manual_tecnico" accept="application/pdf"
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900">
            </div>

            <div class="bg-violet-50 p-6 rounded-3xl border border-violet-100 flex items-center gap-4">
                <span class="text-2xl">📸</span>
                <p class="text-xs font-bold text-violet-800 leading-tight uppercase tracking-tight">
                    Podrás subir las fotos y videos del producto en el siguiente paso.
                </p>
            </div>
        </div>

        <!-- Precios y Calculadora -->
        <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm space-y-8">
            <h3 class="text-lg font-black text-gray-900 tracking-tight border-b pb-4">Lógica Financiera</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Costo
                        Compra (USD)</label>
                    <input type="number" name="costo_compra" id="i_costo" step="0.01" min="0" value="0.00" required
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Margen
                        (%)</label>
                    <input type="number" name="margen_porcentaje" id="i_margen" step="0.01" min="0" value="0.00"
                        required
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-3">Precio
                        Venta (USD)</label>
                    <input type="number" name="precio_venta_usd" id="i_venta" step="0.01" min="0" value="0.00" required
                        class="w-full px-6 py-4 rounded-2xl bg-violet-100 border border-transparent focus:border-violet-500 focus:ring-2 focus:ring-violet-500/10 transition-all outline-none font-black text-violet-900">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 bg-gray-50 p-6 rounded-[24px]">
                <div>
                    <span class="block text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Coti.
                        Aplicada</span>
                    <!-- Este valor es estático visualmente aquí, pero se calcula con JS también -->
                    <span class="text-lg font-black text-blue-600" id="l_coti">$0,00</span>
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
    </div>

    <!-- Columna Lateral -->
    <div class="space-y-8">
        <div class="bg-white p-8 rounded-[32px] border border-gray-100 shadow-sm space-y-10">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Familia /
                    Categoría</label>
                <select name="categoria_id"
                    class="w-full px-5 py-4 rounded-2xl bg-gray-50 font-black text-xs uppercase tracking-widest outline-none border-none ring-0 appearance-none cursor-pointer">
                    <option value="">(Ninguna)</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label
                    class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-6 border-b border-gray-50 pb-2">Etiquetas
                    de Estado</label>
                <div class="space-y-4">
                    <label class="flex items-center gap-4 cursor-pointer group">
                        <input type="checkbox" name="activo" value="1" checked
                            class="w-6 h-6 rounded-lg border-2 border-gray-100 checked:bg-green-500 checked:border-green-500 transition-all outline-none appearance-none cursor-pointer">
                        <span
                            class="text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-green-500 transition-colors">✅
                            Activo (Visible)</span>
                    </label>
                    <label class="flex items-center gap-4 cursor-pointer group">
                        <input type="checkbox" name="en_stock" value="1" checked
                            class="w-6 h-6 rounded-lg border-2 border-gray-100 checked:bg-emerald-500 checked:border-emerald-500 transition-all outline-none appearance-none cursor-pointer">
                        <span
                            class="text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-emerald-500 transition-colors">📦
                            En Stock</span>
                    </label>
                    <label class="flex items-center gap-4 cursor-pointer group">
                        <input type="checkbox" name="es_oferta" value="1"
                            class="w-6 h-6 rounded-lg border-2 border-gray-100 checked:bg-violet-500 checked:border-violet-500 transition-all outline-none appearance-none cursor-pointer">
                        <span
                            class="text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-violet-500 transition-colors">🔥
                            Oferta</span>
                    </label>
                    <label class="flex items-center gap-4 cursor-pointer group">
                        <input type="checkbox" name="es_nuevo" value="1"
                            class="w-6 h-6 rounded-lg border-2 border-gray-100 checked:bg-blue-500 checked:border-blue-500 transition-all outline-none appearance-none cursor-pointer">
                        <span
                            class="text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-blue-500 transition-colors">✨
                            Nuevo Ingreso</span>
                    </label>
                    <label class="flex items-center gap-4 cursor-pointer group">
                        <input type="checkbox" name="es_usado" value="1"
                            class="w-6 h-6 rounded-lg border-2 border-gray-100 checked:bg-amber-600 checked:border-amber-600 transition-all outline-none appearance-none cursor-pointer">
                        <span
                            class="text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-amber-600 transition-colors">♻️
                            Usado</span>
                    </label>
                    <label class="flex items-center gap-4 cursor-pointer group">
                        <input type="checkbox" name="es_destacado" value="1"
                            class="w-6 h-6 rounded-lg border-2 border-gray-100 checked:bg-orange-500 checked:border-orange-500 transition-all outline-none appearance-none cursor-pointer">
                        <span
                            class="text-xs font-black uppercase tracking-widest text-gray-600 group-hover:text-orange-500 transition-colors">💎
                            Destacado</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="bg-gray-900 p-8 rounded-[32px] shadow-2xl space-y-4 sticky top-32">
            <button type="submit"
                class="w-full bg-violet-500 text-black py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-white transition-all shadow-xl shadow-violet-500/20 active:scale-95 flex items-center justify-center gap-3">
                <span>➕</span> Guardar y Subir Media
            </button>
            <a href="index.php"
                class="w-full bg-white/10 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-white/20 transition-all flex items-center justify-center gap-3">Cancelar</a>
        </div>
    </div>
</form>

<script>
    const dolarBlue = <?= json_encode($dolar_blue) ?>;
    const dolarOficial = <?= json_encode($dolar_oficial) ?>;

    const iCosto = document.getElementById('i_costo');
    const iMargen = document.getElementById('i_margen');
    const iVenta = document.getElementById('i_venta');
    const sMarca = document.getElementById('marca_select');

    const lCoti = document.getElementById('l_coti');
    const lSub = document.getElementById('l_sub');
    const lRec = document.getElementById('l_rec');
    const lNeto = document.getElementById('l_neto');

    function formatPesos(val) {
        return '$' + val.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalcularPreciosPesos() {
        let pv = parseFloat(iVenta.value) || 0;

        // Obtener datos de la marca seleccionada
        let opt = sMarca.options[sMarca.selectedIndex];
        let tipoDolar = opt.getAttribute('data-tipo') || 'blue';
        let recargoPesos = parseFloat(opt.getAttribute('data-pesos')) || 0;
        let recargoPorc = parseFloat(opt.getAttribute('data-porc')) || 0;

        let cotiBase = (tipoDolar === 'oficial') ? dolarOficial : dolarBlue;
        let cotiAplicada = cotiBase + recargoPesos;

        let subtotal = pv * cotiAplicada;
        let recargo = subtotal * (recargoPorc / 100);
        let neto = subtotal + recargo;

        lCoti.textContent = formatPesos(cotiAplicada);
        lSub.textContent = formatPesos(subtotal);
        lRec.textContent = formatPesos(recargo);
        lNeto.textContent = formatPesos(neto);

        // Update labels to show percentage
        lRec.previousElementSibling.textContent = `Recargo ${recargoPorc}% (ARS)`;
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
    sMarca.addEventListener('change', recalcularPreciosPesos);

    // Inicializar
    onCostoMargenChange();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>