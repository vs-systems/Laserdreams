<?php
require __DIR__ . '/../../includes/db.php';

$adminTitle = 'Nueva Marca';
require __DIR__ . '/../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm">
        <form action="guardar.php" method="POST" class="space-y-8">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Nombre de la
                    Marca</label>
                <input type="text" name="nombre" required placeholder="Ej. BEAM, SANYI..."
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-all outline-none font-black text-gray-900 text-lg">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Tipo de
                        Dólar Base</label>
                    <select name="tipo_dolar"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-all outline-none font-bold text-gray-900 cursor-pointer appearance-none">
                        <option value="blue">Blue Venta</option>
                        <option value="oficial">Oficial BNA</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Recargo
                        Dólar ($)</label>
                    <input type="number" step="0.01" min="0" name="recargo_dolar_pesos" value="0.00"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-all outline-none font-bold text-gray-900">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Recargo
                        Bco. (%)</label>
                    <input type="number" step="0.01" min="0" name="recargo_bancario_porcentaje" value="0.00"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 border border-transparent focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 transition-all outline-none font-bold text-gray-900">
                </div>
            </div>

            <div class="pt-4 flex gap-4">
                <button type="submit"
                    class="flex-grow bg-violet-500 text-black py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-violet-600 transition-all shadow-xl shadow-violet-500/20 active:scale-95">
                    Guardar Marca
                </button>
                <a href="index.php"
                    class="px-8 bg-gray-100 text-gray-500 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition-all text-center flex items-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>