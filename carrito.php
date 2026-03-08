<?php
require __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-12">

    <div class="mb-10 text-center md:text-left">
        <h1 class="text-3xl font-black text-gray-900 mb-2">Tu Carrito de Consultas</h1>
        <p class="text-gray-500 font-medium">Revisa tus productos antes de enviar la consulta por WhatsApp.</p>
    </div>

    <div id="carrito-contenido" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <!-- El contenido se carga dinámicamente vía JavaScript -->
    </div>

    <div id="carrito-acciones" class="hidden">
        <div class="bg-gray-50 p-8 rounded-3xl border border-gray-100 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <div id="carrito-total" class="text-3xl font-black text-gray-900 mb-2"></div>
                    <div class="space-y-1 text-sm font-bold text-gray-500 uppercase tracking-widest mt-2">
                        <p>* Los valores no incluyen IVA.</p>
                        <p>⚠️ NO INCLUYE FLETES</p>
                    </div>
                </div>

                <div class="w-full md:w-auto">
                    <button id="btn-abrir-checkout"
                        class="w-full md:w-auto inline-flex items-center justify-center gap-3 bg-[#25D366] text-white px-8 py-4 rounded-2xl font-bold text-lg hover:bg-[#20bd5a] transition-all transform active:scale-95 shadow-lg shadow-green-200">
                        <span>💬</span> Consultar Disponibilidad
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-center px-4">
        <a href="/catalogo.php"
            class="text-gray-500 font-bold hover:text-violet-600 transition-colors flex items-center gap-2">
            <span>←</span> Volver al catálogo
        </a>
    </div>

</div>

<!-- Modal de Checkout -->
<div id="modal-checkout"
    class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-300">
    <div
        class="bg-white w-full max-w-md rounded-[40px] shadow-2xl overflow-hidden transform animate-in slide-in-from-bottom-8 duration-500">
        <div class="bg-gray-900 p-8 text-white relative">
            <button onclick="cerrarCheckout()"
                class="absolute top-6 right-6 text-gray-400 hover:text-white transition-colors">✕</button>
            <h2 class="text-2xl font-black tracking-tight mb-2">Último paso</h2>
            <p class="text-gray-400 text-sm font-medium">Completa tus datos para enviarte la cotización oficial.</p>
        </div>

        <form id="form-checkout" class="p-8 space-y-6">
            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Nombre Completo</label>
                <input type="text" id="checkout-nombre" required placeholder="Ej: Juan Pérez"
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-violet-500 focus:bg-white transition-all outline-none font-bold text-gray-900">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Localidad / Ciudad</label>
                <input type="text" id="checkout-localidad" required placeholder="Ej: Mar del Plata"
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-violet-500 focus:bg-white transition-all outline-none font-bold text-gray-900">
            </div>

            <button type="submit" id="btn-finalizar"
                class="w-full bg-violet-500 text-black py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-gray-900 hover:text-white transition-all shadow-xl shadow-violet-500/20 active:scale-95 flex items-center justify-center gap-3">
                <span>💬</span> Confirmar y enviar WhatsApp
            </button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('modal-checkout');
    const form = document.getElementById('form-checkout');

    function abrirCheckout() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function cerrarCheckout() {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.getElementById('btn-abrir-checkout').addEventListener('click', abrirCheckout);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('btn-finalizar');
        const nombre = document.getElementById('checkout-nombre').value.trim();
        const localidad = document.getElementById('checkout-localidad').value.trim();
        const carritoData = window.MGCarrito.get();

        if (!nombre || !localidad) return alert('Por favor, completa tu nombre y localidad.');

        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span> Procesando...';

        try {
            // Recalcular total del carrito por seguridad
            let totalVal = 0;
            carritoData.items.forEach(item => {
                totalVal += (parseFloat(item.precio) || 0) * (parseInt(item.cantidad) || 1);
            });

            // 1. Guardar en Base de Datos vía API
            const response = await fetch('/api/guardar_pedido.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nombre,
                    localidad,
                    email: 'vía WhatsApp',
                    carrito: carritoData.items,
                    total: totalVal
                })
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.error || 'Error al procesar el pedido');
            }

            // 2. Generar mensaje de WhatsApp - Normalizando caracteres
            let mensaje = `Hola! 👋 Soy *${nombre}* de *${localidad}* y me interesa una cotizacion por:%0A%0A`;

            carritoData.items.forEach(item => {
                const precioTexto = item.precio > 0 ? `$${parseFloat(item.precio).toLocaleString('es-AR')}` : 'Consultar';
                mensaje += `🛋️ *${item.titulo}*%0A`;
                mensaje += `   - Cantidad: ${item.cantidad}%0A`;
                mensaje += `   - Color: ${item.color || 'A confirmar'}%0A`;
                mensaje += `   - Precio: ${precioTexto}%0A`;
                mensaje += `   - Link: ${item.url}%0A%0A`;
            });

            mensaje += `💰 *TOTAL APROXIMADO: $${totalVal.toLocaleString('es-AR')}*%0A`;
            mensaje += `%0A_Precios sujetos a cambios. No incluyen IVA._`;

            // 3. Cerrar modal y Redirigir
            cerrarCheckout();

            const whatsappUrl = `https://wa.me/5492235772165?text=${mensaje}`;
            window.MGCarrito.clear(); // Limpiar carrito
            window.location.href = whatsappUrl;

        } catch (error) {
            alert(error.message);
            btn.disabled = false;
            btn.innerHTML = '<span>💬</span> Confirmar y enviar WhatsApp';
        }
    });

    function renderCarrito() {
        const contenedor = document.getElementById('carrito-contenido');
        const accionesEl = document.getElementById('carrito-acciones');
        const totalEl = document.getElementById('carrito-total');

        const carrito = window.MGCarrito.get();
        contenedor.innerHTML = '';

        if (!carrito.items.length) {
            contenedor.innerHTML = `
            <div class="py-20 text-center">
                <div class="text-6xl mb-4">🛒</div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Tu carrito está vacío</h2>
                <p class="text-gray-500 mb-8">Aún no has agregado productos para consultar.</p>
                <a href="/catalogo.php" class="inline-block bg-gray-900 text-white px-8 py-3 rounded-xl font-bold hover:bg-violet-500 hover:text-black transition-all">Explorar productos</a>
            </div>
        `;
            accionesEl.classList.add('hidden');
            return;
        }

        accionesEl.classList.remove('hidden');

        carrito.items.forEach(item => {
            const subtotal = item.precio * item.cantidad;
            const colorText = item.color ? `Color: ${item.color}` : 'Color: A confirmar';

            const itemDiv = document.createElement('div');
            itemDiv.className = 'flex flex-col p-6 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors';
            itemDiv.innerHTML = `
            <div class="flex flex-col sm:flex-row items-center gap-6 w-full text-center sm:text-left">
                <div class="w-24 h-24 flex-shrink-0 rounded-2xl overflow-hidden shadow-sm border border-gray-100">
                    <img src="${item.imagen || 'https://via.placeholder.com/300?text=Sin+Imagen'}" class="w-full h-full object-cover">
                </div>
                
                <div class="flex-grow">
                    <h3 class="font-bold text-gray-900 text-lg mb-1">${item.titulo}</h3>
                    <p class="text-gray-400 text-sm font-medium mb-1">${colorText}</p>
                    <p class="text-violet-600 font-bold">$${item.precio.toLocaleString('es-AR')}</p>
                </div>

                <div class="flex items-center gap-4 bg-white p-2 rounded-2xl shadow-sm border border-gray-100 mx-auto sm:mx-0">
                    <button onclick="MGCarrito.updateCantidad(${item.id}, ${item.cantidad - 1}, '${item.color}')" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-100 transition-colors text-xl font-bold text-gray-400">−</button>
                    <span class="w-8 text-center font-black text-gray-900 text-lg">${item.cantidad}</span>
                    <button onclick="MGCarrito.updateCantidad(${item.id}, ${item.cantidad + 1}, '${item.color}')" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-gray-100 transition-colors text-xl font-bold text-gray-400">+</button>
                </div>

                <div class="sm:text-right min-w-[120px]">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Subtotal</p>
                    <p class="text-xl font-black text-gray-900">$${subtotal.toLocaleString('es-AR')}</p>
                </div>

                <button onclick="MGCarrito.remove(${item.id}, '${item.color}')" class="text-gray-300 hover:text-red-500 transition-colors p-2 sm:ml-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        `;
            contenedor.appendChild(itemDiv);
        });

        totalEl.innerHTML = `Total: <span class="text-violet-600">$${carrito.total.toLocaleString('es-AR')}</span>`;
    }

    document.addEventListener('DOMContentLoaded', renderCarrito);
    document.addEventListener('mg:carrito:update', renderCarrito);
</script>

<?php
require __DIR__ . '/includes/footer.php';
?>