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

$adminTitle = '📊 Informes y Estadísticas';
require __DIR__ . '/../includes/header.php';
?>

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