<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/header.php';

function e($v)
{
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id=? AND p.activo=1");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
    echo "<div class='container mx-auto px-4 py-20 text-center'><h1 class='text-2xl font-bold'>Producto no encontrado</h1><a href='/catalogo.php' class='text-violet-600 font-bold mt-4 inline-block'>Volver al catálogo</a></div>";
    require __DIR__ . '/includes/footer.php';
    exit;
}

$mediaItems = [];
if (!empty($p['video']))
    $mediaItems[] = ['archivo' => $p['video'], 'tipo' => 'video'];
if (!empty($p['foto_principal']))
    $mediaItems[] = ['archivo' => $p['foto_principal'], 'tipo' => 'imagen'];
if (!empty($p['foto_2']))
    $mediaItems[] = ['archivo' => $p['foto_2'], 'tipo' => 'imagen'];
if (!empty($p['foto_3']))
    $mediaItems[] = ['archivo' => $p['foto_3'], 'tipo' => 'imagen'];
if (!empty($p['foto_4']))
    $mediaItems[] = ['archivo' => $p['foto_4'], 'tipo' => 'imagen'];

$cotizacion = $GLOBALS['cotizacion_aplicada'] ?? 1000;
$subtotal = ((float) $p['precio_venta_usd']) * $cotizacion;
$recargo = $subtotal * 0.05;
$precio_final = $subtotal + $recargo;

?>

<div class="max-w-7xl mx-auto px-4 py-12">

    <nav class="flex mb-8 text-sm font-medium text-gray-500" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li><a href="/catalogo.php" class="hover:text-violet-600 transition-colors">Catálogo</a></li>
            <li><span class="mx-2">/</span></li>
            <li><span class="text-gray-900"><?= e(ucwords(strtolower($p['categoria_nombre'] ?? 'General'))) ?></span>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

        <!-- Galería de Medios -->
        <div class="space-y-4">
            <div id="mainMediaContainer"
                class="aspect-[4/5] bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 relative group flex items-center justify-center">
                <?php
                $firstMedia = $mediaItems[0] ?? ['archivo' => '', 'tipo' => 'imagen'];
                $isMainVideo = ($firstMedia['tipo'] === 'video');

                if ($isMainVideo && !empty($firstMedia['archivo'])): ?>
                    <video id="mainVideo" autoplay muted loop playsinline controls class="w-full h-full object-cover">
                        <source src="/uploads/productos/<?= e($firstMedia['archivo']) ?>" type="video/mp4">
                    </video>
                    <img id="mainImage" src="" class="hidden w-full h-full object-cover">
                <?php else: ?>
                    <img id="mainImage"
                        src="<?= !empty($firstMedia['archivo']) ? '/uploads/productos/' . e($firstMedia['archivo']) : 'https://via.placeholder.com/800x1000?text=Sin+Imagen' ?>"
                        alt="<?= e($p['titulo']) ?>"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <video id="mainVideo" controls class="hidden w-full h-full object-cover"></video>
                <?php endif; ?>

                <div class="absolute top-4 right-4 flex flex-col gap-2 items-end">
                    <?php if (!empty($p['es_nuevo'])): ?>
                        <span
                            class="bg-blue-600 text-white px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm">Nuevo
                            Ingreso</span>
                    <?php endif; ?>
                    <?php if (!empty($p['es_oferta'])): ?>
                        <span
                            class="bg-red-600 text-white px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm">Oferta</span>
                    <?php endif; ?>
                    <?php if (!empty($p['es_destacado'])): ?>
                        <span
                            class="bg-violet-500 text-black px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-sm">Destacado</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (count($mediaItems) > 1): ?>
                <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-hide py-2">
                    <?php foreach ($mediaItems as $index => $item): ?>
                        <button onclick="changeMainMedia('<?= e($item['archivo']) ?>', '<?= $item['tipo'] ?>', this)"
                            class="w-24 h-24 flex-shrink-0 rounded-2xl overflow-hidden border-2 transition-all relative <?= $index === 0 ? 'border-violet-500 shadow-md ring-2 ring-violet-500/20' : 'border-transparent hover:border-gray-200' ?>">
                            <?php if ($item['tipo'] === 'video'): ?>
                                <video class="w-full h-full object-cover pointer-events-none">
                                    <source src="/uploads/productos/<?= e($item['archivo']) ?>" type="video/mp4">
                                </video>
                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-black/20 group-hover:bg-black/40 transition-colors">
                                    <span class="text-white text-xl">▶️</span>
                                </div>
                            <?php else: ?>
                                <img src="/uploads/productos/<?= e($item['archivo']) ?>" class="w-full h-full object-cover">
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- Información del Producto -->
        <div class="lg:sticky lg:top-32">
            <div class="mb-6">
                <div class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-2">COD:
                    <?= e($p['codigo']) ?>
                </div>
                <h1 class="text-4xl font-black text-gray-900 mb-2 leading-tight">
                    <?= e(ucwords(strtolower($p['titulo']))) ?>
                </h1>

                <?php if (!empty($p['marca'])): ?>
                    <div class="mb-4">
                        <span
                            class="bg-gray-100 text-gray-800 text-xs font-black px-3 py-1 rounded-full uppercase tracking-widest border border-gray-200">
                            <?= e($p['marca']) ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="flex items-center gap-4">
                    <span
                        class="text-4xl font-black text-green-700">$<?= number_format($precio_final, 0, ',', '.') ?></span>
                </div>
            </div>

            <div class="prose prose-violet max-w-none text-gray-600 mb-8 leading-relaxed">
                <?= nl2br(e($p['descripcion'])) ?>
            </div>

            <div class="space-y-6 bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">

                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">Tipo Envase:</div>
                    <div class="text-sm font-black text-gray-900 uppercase"><?= e($p['tipo_bulto']) ?></div>
                </div>
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">Unidades / Pack:</div>
                    <div class="text-sm font-black text-gray-900"><?= (int) $p['unidades_por_bulto'] ?> u.</div>
                </div>

                <div class="pt-2 flex gap-3">
                    <?php if (!empty($p['manual_tecnico'])): ?>
                        <a href="/uploads/productos/<?= e($p['manual_tecnico']) ?>" target="_blank"
                            class="w-1/2 bg-red-900 text-white py-4 rounded-xl font-bold hover:bg-red-950 transition-all flex items-center justify-center gap-2 text-sm text-center shadow-lg">
                            📄 Manual Técnico
                        </a>
                    <?php endif; ?>
                    <button id="btnAddCart"
                        class="<?= !empty($p['manual_tecnico']) ? 'w-1/2' : 'w-full' ?> bg-red-600 text-white py-4 rounded-xl font-bold text-sm hover:bg-red-500 transition-all transform active:scale-95 shadow-lg flex items-center justify-center gap-2 group"
                        data-id="<?= (int) $p['id'] ?>" data-titulo="<?= e(ucwords(strtolower($p['titulo']))) ?>"
                        data-precio="<?= $precio_final ?>"
                        data-imagen="<?= !empty($p['foto_principal']) ? '/uploads/productos/' . e($p['foto_principal']) : '' ?>"
                        data-url="https://laserdreams.com.ar/producto.php?id=<?= (int) $p['id'] ?>"
                        onclick="addToCartProduct(this)">
                        <span class="text-lg group-hover:rotate-12 transition-transform">🛒</span> Agregar al carrito
                    </button>
                </div>
                <p
                    class="text-center mt-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center justify-center gap-2">
                    NO INCLUYE FLETES
                </p>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-center gap-8 py-6 border-t border-gray-100">
            <div class="text-center">
                <div class="text-2xl mb-1">🛡️</div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Calidad Garantizada</p>
            </div>
            <div class="text-center text-gray-100 font-thin italic">|</div>
            <div class="text-center">
                <div class="text-2xl mb-1">🚚</div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Envíos al país</p>
            </div>
        </div>
    </div>

</div>
</div>

<script>
    function changeMainMedia(archivo, tipo, btn) {
        const mainImg = document.getElementById('mainImage');
        const mainVid = document.getElementById('mainVideo');
        const path = '/uploads/productos/' + archivo;

        if (tipo === 'video') {
            mainImg.classList.add('hidden');
            mainVid.classList.remove('hidden');
            mainVid.src = path;
            mainVid.play();
        } else {
            mainVid.classList.add('hidden');
            mainVid.pause();
            mainImg.classList.remove('hidden');
            mainImg.src = path;
        }

        // Update active thumb
        document.querySelectorAll('button[onclick^="changeMainMedia"]').forEach(b => {
            b.classList.remove('border-violet-500', 'shadow-md', 'ring-2', 'ring-violet-500/20');
            b.classList.add('border-transparent');
        });
        btn.classList.add('border-violet-500', 'shadow-md', 'ring-2', 'ring-violet-500/20');
    }

    function addToCartProduct(btn) {
        addToCartFromButton(btn);

        // Feedback visual
        const textOriginal = btn.innerHTML;
        btn.innerHTML = '✅ Agregado al carrito';
        btn.classList.replace('bg-gray-900', 'bg-green-500');
        setTimeout(() => {
            btn.innerHTML = textOriginal;
            btn.classList.replace('bg-green-500', 'bg-gray-900');
        }, 2000);
    }
</script>

<style>
    #mainImage {
        transition: opacity 0.3s ease-in-out;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<?php require __DIR__ . '/includes/footer.php'; ?>