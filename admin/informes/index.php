<?php
require __DIR__ . '/../../includes/db.php';

// --- CONSULTAS PARA MÉTRICAS ---
$totalProductos = $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1")->fetchColumn();
$totalConsultas = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
$pedidosConfirmados = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'Confirmado'")->fetchColumn();
$totalVisitas = $pdo->query("SELECT SUM(visitas) FROM productos")->fetchColumn() ?: 0;
$productosVisitas = $pdo->query("SELECT titulo, visitas FROM productos WHERE activo = 1 ORDER BY visitas DESC LIMIT 5")->fetchAll();

// 2. Estados de Pedido (Anillo/Doughnut)
$orderStats = $pdo->query("
    SELECT estado, COUNT(*) as total 
    FROM pedidos 
    GROUP BY estado
")->fetchAll();

// 3. Productos por Categoría (Doughnut)
$catStats = $pdo->query("
    SELECT c.nombre, COUNT(p.id) as total 
    FROM categorias c
    LEFT JOIN productos p ON c.id = p.categoria_id AND p.activo = 1
    GROUP BY c.id
    HAVING total > 0
")->fetchAll();

// 4. Productos por Marca (NUEVO - Anillo)
$marcaStats = $pdo->query("
    SELECT m.nombre, COUNT(p.id) as total
    FROM marcas m
    LEFT JOIN productos p ON m.id = p.marca_id AND p.activo = 1
    GROUP BY m.id
    HAVING total > 0
")->fetchAll();

$adminTitle = '📊 Informes y Estadísticas';
require __DIR__ . '/../includes/header.php';

// Obtener la cotización aplicada para mostrar en esta pantalla
$cot_sanyi = $GLOBALS['cotizacion_aplicada'] ?? 0;
$cot_bigdipper = $GLOBALS['dolar_oficial_base'] ?? 0;
?>

<!-- Cotizaciones de Dólares -->
<div class="mb-8 flex flex-col sm:flex-row gap-4">
    <div
        class="bg-blue-900 border border-blue-800 text-white p-4 rounded-2xl flex-1 shadow-sm flex items-center justify-between">
        <div>
            <h3 class="text-[10px] font-black uppercase tracking-widest text-blue-300">Dólar SANYI</h3>
            <p class="text-xs text-blue-200 mt-0.5">Dólar Blue (Venta) + $15</p>
        </div>
        <div class="text-2xl font-black">$<?= number_format($cot_sanyi, 2, ',', '.') ?></div>
    </div>

    <div
        class="bg-emerald-900 border border-emerald-800 text-white p-4 rounded-2xl flex-1 shadow-sm flex items-center justify-between">
        <div>
            <h3 class="text-[10px] font-black uppercase tracking-widest text-emerald-300">Dólar Bigdipper</h3>
            <p class="text-xs text-emerald-200 mt-0.5">Dólar Oficial del BNA (Venta)</p>
        </div>
        <div class="text-2xl font-black">$<?= number_format($cot_bigdipper, 2, ',', '.') ?></div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- ... (Grid de métricas se mantiene igual) ... -->

<!-- Gráficos -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
    <!-- Distribución por Categoría -->
    <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm">
        <h3 class="text-sm font-black uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-violet-500"></span>
            Productos por Categoría
        </h3>
        <div class="aspect-square max-w-[300px] mx-auto">
            <canvas id="chartCategorias"></canvas>
        </div>
    </div>

    <!-- Estado de Pedidos (NUEVO) -->
    <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm">
        <h3 class="text-sm font-black uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
            Estado de Pedidos
        </h3>
        <div class="aspect-square max-w-[300px] mx-auto">
            <canvas id="chartEstados"></canvas>
        </div>
    </div>

    <!-- Productos por Marca (NUEVO) -->
    <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm">
        <h3 class="text-sm font-black uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-pink-500"></span>
            Productos por Marca
        </h3>
        <div class="aspect-square max-w-[300px] mx-auto">
            <canvas id="chartMarcas"></canvas>
        </div>
    </div>

    <!-- Ranking de Visitas (Curva) -->
    <div class="bg-white p-10 rounded-[40px] border border-gray-100 shadow-sm lg:col-span-2">
        <h3 class="text-sm font-black uppercase tracking-widest text-gray-400 mb-8 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            Tendencia de Visitas por Producto
        </h3>
        <div class="h-[300px]">
            <canvas id="chartVisitas"></canvas>
        </div>
    </div>
</div>

<script>
    // Configuración de Gráfico de Categorías (Donut)
    const ctxCat = document.getElementById('chartCategorias').getContext('2d');
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($catStats, 'nombre')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($catStats, 'total')) ?>,
                backgroundColor: ['#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#6366F1'],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, font: { weight: 'bold', size: 10 } } }
            }
        }
    });

    // Gráfico de Marcas (Anillo)
    const ctxMar = document.getElementById('chartMarcas').getContext('2d');
    new Chart(ctxMar, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($marcaStats, 'nombre')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($marcaStats, 'total')) ?>,
                backgroundColor: ['#EC4899', '#8B5CF6', '#3B82F6', '#10B981', '#F59E0B', '#EF4444'],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, font: { weight: 'bold', size: 10 } } }
            }
        }
    });

    // Gráfico de Estados de Pedido (Doughnut)
    const ctxEst = document.getElementById('chartEstados').getContext('2d');
    const estadoColores = {
        'Nuevo': '#FACC15',       // Amarillo
        'Cotizado': '#F97316',    // Naranja
        'Confirmado': '#10B981',  // Verde
        'En Producción': '#FB923C', // Naranja Fluo/Llamat
        'Enviado': '#059669',     // Verde oscuro
        'Cancelado': '#EF4444'    // Rojo
    };

    const estadosData = <?= json_encode($orderStats) ?>;
    new Chart(ctxEst, {
        type: 'doughnut',
        data: {
            labels: estadosData.map(d => d.estado),
            datasets: [{
                data: estadosData.map(d => d.total),
                backgroundColor: estadosData.map(d => estadoColores[d.estado] || '#CBD5E1'),
                borderWidth: 0,
                cutout: '65%'
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, font: { weight: 'bold', size: 10 } } }
            }
        }
    });

    // Gráfico de Visitas (Línea / Curva)
    const ctxVis = document.getElementById('chartVisitas').getContext('2d');
    new Chart(ctxVis, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($productosVisitas, 'titulo')) ?>,
            datasets: [{
                label: 'Visitas',
                data: <?= json_encode(array_column($productosVisitas, 'visitas')) ?>,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#3B82F6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>