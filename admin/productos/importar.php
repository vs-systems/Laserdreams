<?php
require __DIR__ . '/../../includes/db.php';

$adminTitle = 'Importar Productos';
require __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm space-y-8">
        <div>
            <h2 class="text-2xl font-black text-gray-900 tracking-tight mb-4">Importar desde CSV</h2>
            <p class="text-gray-500 font-medium leading-relaxed">
                Puedes cargar múltiples productos a la vez subiendo un archivo CSV.
                El archivo debe tener exactamente las siguientes columnas en este orden:
            </p>
        </div>

        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100">
            <code class="text-[10px] font-black uppercase tracking-widest text-violet-600 break-all">
                CODIGO INTERNO, TIPO BULTO, UNIDADES X BULTO, FAMILIA / CATEGORIA, TITULO DEL PRODUCTO, MARCA, DESCRIPCION, ETIQUETAS DE ESTADO, COSTO COMPRA, COSTO VENTA, STOCK si/no
            </code>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs font-medium text-gray-400 leading-relaxed italic">
            <ul class="space-y-2 list-disc pl-4">
                <li><strong>Tipo Bulto:</strong> "Caja de Cartón" o "Anvil Flight Case"</li>
                <li><strong>Familia/Categoría:</strong> Si no existe, se creará automáticamente.</li>
                <li><strong>Marca:</strong> Si no existe, se creará automáticamente.</li>
                <li><strong>Etiquetas de Estado:</strong> "nuevo, oferta, destacado" (separadas por coma).</li>
            </ul>
            <ul class="space-y-2 list-disc pl-4">
                <li><strong>Costo Venta:</strong> Se usará como Precio Final USD. El sistema calculará el % de margen.
                </li>
                <li><strong>Stock:</strong> Usa "si" o "no".</li>
                <li><strong>Formato:</strong> CSV delimitado por comas (sep: ,).</li>
            </ul>
        </div>

        <form action="procesar_import.php" method="POST" enctype="multipart/form-data"
            class="pt-6 border-t border-gray-50">
            <div class="mb-10">
                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">Seleccionar
                    Archivo .CSV</label>
                <div class="relative group">
                    <input type="file" name="archivo_csv" accept=".csv" required
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div
                        class="w-full px-8 py-10 rounded-[32px] border-2 border-dashed border-gray-200 group-hover:border-violet-500 group-hover:bg-violet-50/30 transition-all flex flex-col items-center justify-center gap-4">
                        <span class="text-4xl">📊</span>
                        <span
                            class="text-sm font-bold text-gray-400 group-hover:text-violet-600 transition-colors">Arrastra
                            tu archivo aquí o haz clic para buscar</span>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit"
                    class="flex-grow bg-violet-500 text-black py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-gray-900 hover:text-white transition-all shadow-xl shadow-violet-500/20 active:scale-95">
                    🚀 Iniciar Importación
                </button>
                <a href="index.php"
                    class="px-8 bg-gray-100 text-gray-500 py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-gray-200 transition-all flex items-center">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>